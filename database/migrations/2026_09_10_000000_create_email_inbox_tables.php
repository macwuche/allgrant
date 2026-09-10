<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('email_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('label')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->string('resend_id')->nullable()->unique();
            $table->enum('direction', ['inbound', 'outbound']);
            $table->foreignId('email_address_id')->nullable()->constrained('email_addresses')->nullOnDelete();
            $table->string('from_address');
            $table->json('to_addresses');
            $table->json('cc_addresses')->nullable();
            $table->json('bcc_addresses')->nullable();
            $table->string('subject')->nullable();
            $table->longText('html_body')->nullable();
            $table->longText('text_body')->nullable();
            $table->string('snippet')->nullable();
            $table->string('message_id')->nullable()->index();
            $table->string('in_reply_to')->nullable()->index();
            $table->string('thread_key')->nullable()->index();
            $table->string('status')->default('received');
            $table->text('error')->nullable();
            $table->boolean('is_read')->default(false);
            $table->json('raw_headers')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->timestamps();
        });

        Schema::create('email_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_id')->constrained('emails')->cascadeOnDelete();
            $table->string('filename');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('disk_path');
            $table->string('content_id')->nullable();
            $table->boolean('is_inline')->default(false);
            $table->timestamps();
        });

        Schema::create('email_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('svix_id')->unique();
            $table->string('event_type')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_webhook_events');
        Schema::dropIfExists('email_attachments');
        Schema::dropIfExists('emails');
        Schema::dropIfExists('email_addresses');
    }
};
