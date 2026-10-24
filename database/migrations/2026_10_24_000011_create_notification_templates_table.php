<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();

            // Matches the type codes used in notifications and email_delivery_logs.
            $table->string('type')->unique();

            $table->string('subject');

            // Supports placeholder tokens, e.g. {{patron_name}}, {{book_title}}, {{due_date}}.
            $table->text('body_template');

            $table->foreignId('updated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
        });

        // Seed default message templates.
        $now = now();
        DB::table('notification_templates')->insert([
            [
                'type'          => 'due_soon',
                'subject'       => 'Reminder: Your borrowed book is due soon',
                'body_template' => "Dear {{patron_name}},\n\nThis is a reminder that \"{{book_title}}\" is due on {{due_date}}.\n\nPlease return it on time to avoid your borrowing privileges being affected.\n\nThank you,\nKaAklat Library",
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'type'          => 'overdue',
                'subject'       => 'Notice: Your borrowed book is overdue',
                'body_template' => "Dear {{patron_name}},\n\n\"{{book_title}}\" was due on {{due_date}} and has not yet been returned.\n\nPlease return it as soon as possible. Continued non-return may result in suspension of your borrowing privileges.\n\nThank you,\nKaAklat Library",
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'type'          => 'severely_overdue',
                'subject'       => 'Urgent: Severely overdue book — immediate return required',
                'body_template' => "Dear {{patron_name}},\n\n\"{{book_title}}\" is now severely overdue (due date: {{due_date}}).\n\nYour borrowing privileges may be suspended until this copy is returned.\n\nThank you,\nKaAklat Library",
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'type'          => 'hold_ready',
                'subject'       => 'Your hold is ready for pickup',
                'body_template' => "Dear {{patron_name}},\n\n\"{{book_title}}\" is now available and has been reserved for you.\n\nPlease collect it by {{pickup_deadline}}. After this date the reservation will expire.\n\nThank you,\nKaAklat Library",
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'type'          => 'request_approved',
                'subject'       => 'Your borrow request has been approved',
                'body_template' => "Dear {{patron_name}},\n\nYour request to borrow \"{{book_title}}\" has been approved. Please collect the book by {{pickup_deadline}}.\n\nThank you,\nKaAklat Library",
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'type'          => 'request_rejected',
                'subject'       => 'Your borrow request was not approved',
                'body_template' => "Dear {{patron_name}},\n\nYour request to borrow \"{{book_title}}\" was not approved.\n\nReason: {{rejection_reason}}\n\nIf you have questions, please contact library staff.\n\nThank you,\nKaAklat Library",
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'type'          => 'request_expired',
                'subject'       => 'Your borrow request has expired',
                'body_template' => "Dear {{patron_name}},\n\nYour request to borrow \"{{book_title}}\" has expired because it was not acted on within the required window.\n\nYou may submit a new request if the title is still available.\n\nThank you,\nKaAklat Library",
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'type'          => 'suspended',
                'subject'       => 'Your borrowing privileges have been suspended',
                'body_template' => "Dear {{patron_name}},\n\nYour borrowing privileges have been suspended.\n\nReason: {{suspension_reason}}\n\nPlease contact library staff for further information.\n\nThank you,\nKaAklat Library",
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'type'          => 'reinstated',
                'subject'       => 'Your borrowing privileges have been reinstated',
                'body_template' => "Dear {{patron_name}},\n\nYour borrowing privileges have been reinstated and you may borrow books again.\n\nThank you,\nKaAklat Library",
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'type'          => 'loan_returned',
                'subject'       => 'Book return confirmed',
                'body_template' => "Dear {{patron_name}},\n\n\"{{book_title}}\" has been successfully returned on {{returned_at}}.\n\nThank you for using KaAklat Library.",
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
