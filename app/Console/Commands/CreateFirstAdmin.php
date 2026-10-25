<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateFirstAdmin extends Command
{
    protected $signature = 'kaaklat:admin:create-first';

    protected $description = 'Interactively create the first KaAklat administrator';

    public function handle(): int
    {
        if (User::withTrashed()->where('role', 'admin')->exists()) {
            $this->components->error('An administrator already exists. This command can only be used once.');

            return self::FAILURE;
        }

        $name = trim((string) $this->ask('Administrator name'));
        $email = mb_strtolower(trim((string) $this->ask('Administrator email')));
        $password = (string) $this->secret('Password (at least 12 characters)');
        $confirmation = (string) $this->secret('Confirm password');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $confirmation,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'admin',
            'student_id' => null,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->components->info('The first administrator account was created.');

        return self::SUCCESS;
    }
}
