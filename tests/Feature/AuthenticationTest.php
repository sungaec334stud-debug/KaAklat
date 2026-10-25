<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use RuntimeException;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        $this->assertSafeTestDatabase();
    }

    protected function setUp(): void
    {
        $connection = getenv('DB_CONNECTION') ?: '';
        $database = strtolower(getenv('DB_DATABASE') ?: '');

        if ($connection !== 'mysql' || ! str_ends_with($database, '_test')) {
            throw new RuntimeException('Authentication tests require MySQL and a dedicated database whose name ends in _test.');
        }

        parent::setUp();
    }

    public function test_student_registration_ignores_submitted_role_and_sends_verification(): void
    {
        Notification::fake();

        $this->post(route('register.store'), [
            'name' => 'Aklat Student',
            'email' => 'STUDENT@UDD.EDU.PH',
            'student_id' => '2026-Student-X',
            'password' => 'a-long-test-password',
            'password_confirmation' => 'a-long-test-password',
            'role' => 'admin',
            'is_active' => true,
        ])->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'student@udd.edu.ph')->firstOrFail();
        $this->assertSame('student', $user->role);
        $this->assertSame('2026-Student-X', $user->student_id);
        $this->assertFalse($user->is_active);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_student_registration_requires_university_email_and_unique_identifiers(): void
    {
        $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Student',
            'email' => 'person@example.com',
            'student_id' => 'S-001',
            'password' => 'a-long-test-password',
            'password_confirmation' => 'a-long-test-password',
        ])->assertSessionHasErrors('email');

        User::factory()->create(['email' => 'existing@udd.edu.ph', 'student_id' => 'S-002']);

        $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Duplicate',
            'email' => 'existing@udd.edu.ph',
            'student_id' => 'S-002',
            'password' => 'a-long-test-password',
            'password_confirmation' => 'a-long-test-password',
        ])->assertSessionHasErrors(['email', 'student_id']);
    }

    public function test_duplicate_registration_keeps_one_account_and_offers_verification_recovery(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create([
            'email' => 'already-registered@udd.edu.ph',
            'student_id' => 'ALREADY-REGISTERED',
            'is_active' => false,
        ]);

        $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Another Attempt',
            'email' => $user->email,
            'student_id' => $user->student_id,
            'password' => 'a-long-test-password',
            'password_confirmation' => 'a-long-test-password',
        ])->assertRedirect(route('register'))->assertSessionHasErrors(['email', 'student_id']);

        $this->get(route('register'))->assertSee('Waiting for your verification email?')
            ->assertSee(route('verification.notice'), false);
        $this->assertSame(1, User::where('email', $user->email)->count());
        $this->assertFalse($user->fresh()->is_active);
        Notification::assertNothingSent();
    }

    public function test_registration_preserves_inactive_account_and_explains_mail_failure(): void
    {
        $this->failVerificationMail();

        $this->post(route('register.store'), [
            'name' => 'Delivery Test',
            'email' => 'delivery-test@udd.edu.ph',
            'student_id' => 'DELIVERY-TEST',
            'password' => 'a-long-test-password',
            'password_confirmation' => 'a-long-test-password',
        ])->assertRedirect(route('verification.notice'))
            ->assertSessionHas('status', 'registration-email-failed');

        $user = User::where('email', 'delivery-test@udd.edu.ph')->firstOrFail();
        $this->assertFalse($user->is_active);
        $this->assertNull($user->email_verified_at);
        $this->assertGuest();
        $this->get(route('verification.notice'))->assertSee('Your account was created')
            ->assertSee('Send verification link');

        Notification::fake();
        $this->from(route('verification.notice'))->post(route('verification.send'), [
            'email' => $user->email,
        ])->assertRedirect(route('verification.notice'))->assertSessionHas('status', 'verification-link-sent');
        Notification::assertSentTo($user, VerifyEmail::class);
        $this->assertSame(1, User::where('email', $user->email)->count());
    }

    public function test_resend_mail_failure_returns_a_safe_error_and_keeps_account_inactive(): void
    {
        $user = User::factory()->unverified()->create(['is_active' => false]);
        $this->failVerificationMail();

        $this->from(route('verification.notice'))->post(route('verification.send'), [
            'email' => $user->email,
        ])->assertRedirect(route('verification.notice'))->assertSessionHasErrors('email');

        $this->assertFalse($user->fresh()->is_active);
        $this->assertNull($user->fresh()->email_verified_at);
        $this->get(route('verification.notice'))->assertDontSee('Simulated SMTP outage');
    }

    public function test_multiple_students_can_register_and_verify_independently(): void
    {
        Notification::fake();
        $students = [];

        foreach (range(1, 4) as $number) {
            $email = "simulation-{$number}@udd.edu.ph";
            $this->post(route('register.store'), [
                'name' => "Simulation Student {$number}",
                'email' => $email,
                'student_id' => "SIMULATION-{$number}",
                'password' => 'a-long-test-password',
                'password_confirmation' => 'a-long-test-password',
            ])->assertRedirect(route('verification.notice'));

            $user = User::where('email', $email)->firstOrFail();
            $this->assertFalse($user->is_active);
            Notification::assertSentTo($user, VerifyEmail::class);
            $students[] = $user;
        }

        foreach ($students as $user) {
            $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(30), [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]);
            $this->get($url)->assertRedirect(route('login'));
            $this->assertTrue($user->fresh()->is_active);
            $this->assertNotNull($user->fresh()->email_verified_at);
        }
    }

    private function failVerificationMail(): void
    {
        Mail::mailer()->setSymfonyTransport(new class extends AbstractTransport
        {
            protected function doSend(SentMessage $message): void
            {
                throw new TransportException('Simulated SMTP outage');
            }

            public function __toString(): string
            {
                return 'failing-test-transport';
            }
        });
    }

    public function test_registration_rejects_array_fields_without_a_server_error(): void
    {
        foreach (['name', 'email', 'student_id'] as $field) {
            $data = ['name' => 'Validation Student', 'email' => 'validation@udd.edu.ph',
                'student_id' => 'VALIDATION', 'password' => 'a-long-test-password',
                'password_confirmation' => 'a-long-test-password'];
            $data[$field] = ['unexpected'];
            $this->from(route('register'))->post(route('register.store'), $data)
                ->assertRedirect(route('register'))->assertSessionHasErrors($field);
        }
        $this->assertDatabaseMissing('users', ['email' => 'validation@udd.edu.ph']);
    }

    public function test_login_rejects_array_email_without_a_server_error(): void
    {
        $this->from(route('login'))->post(route('login.store'), [
            'email' => ['unexpected'], 'password' => 'a-long-test-password',
        ])->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_staff_creation_rejects_array_fields_without_a_server_error(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        foreach (['name', 'email'] as $field) {
            $data = ['name' => 'Validation Staff', 'email' => 'validation-staff@udd.edu.ph',
                'role' => 'librarian', 'password' => 'a-long-test-password',
                'password_confirmation' => 'a-long-test-password'];
            $data[$field] = ['unexpected'];
            $this->actingAs($admin)->from(route('admin.staff.index'))->post(route('admin.staff.store'), $data)
                ->assertRedirect(route('admin.staff.index'))->assertSessionHasErrors($field);
        }
        $this->assertDatabaseMissing('users', ['email' => 'validation-staff@udd.edu.ph']);
    }

    public function test_signed_verification_activates_account(): void
    {
        $user = User::factory()->unverified()->create(['is_active' => false]);
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(30), [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]);

        $this->get($url)->assertRedirect(route('login'))->assertSessionHas('status', 'email-verified');

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertTrue($user->fresh()->is_active);
    }

    public function test_login_rejects_unverified_and_inactive_accounts(): void
    {
        $unverified = User::factory()->unverified()->create(['is_active' => false, 'password' => bcrypt('a-long-test-password')]);
        $inactive = User::factory()->create(['is_active' => false, 'password' => bcrypt('a-long-test-password')]);

        foreach ([$unverified, $inactive] as $user) {
            $this->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'a-long-test-password',
            ])->assertSessionHasErrors('email');
            $this->assertGuest();
        }
    }

    public function test_verified_active_user_can_login_and_logout(): void
    {
        $user = User::factory()->create(['is_active' => true, 'password' => bcrypt('a-long-test-password')]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'a-long-test-password',
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_only_active_verified_admin_can_create_staff_accounts(): void
    {
        $this->get(route('admin.staff.index'))->assertRedirect(route('login'));

        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);
        $this->actingAs($student)->get(route('admin.staff.index'))->assertForbidden();

        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin)->post(route('admin.staff.store'), [
            'name' => 'Library Staff',
            'email' => 'librarian@udd.edu.ph',
            'role' => 'librarian',
            'password' => 'a-long-test-password',
            'password_confirmation' => 'a-long-test-password',
        ])->assertRedirect(route('admin.staff.index'));

        $staff = User::where('email', 'librarian@udd.edu.ph')->firstOrFail();
        $this->assertSame('librarian', $staff->role);
        $this->assertFalse($staff->is_active);
        $this->assertNull($staff->email_verified_at);
        Notification::assertSentTo($staff, VerifyEmail::class);
    }

    public function test_first_admin_command_refuses_if_an_admin_already_exists(): void
    {
        User::factory()->create(['role' => 'admin']);

        $this->artisan('kaaklat:admin:create-first')->assertExitCode(1);
    }
}
