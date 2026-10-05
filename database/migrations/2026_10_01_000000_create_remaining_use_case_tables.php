<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_domains', function (Blueprint $table): void {
            $table->boolean('is_default')->default(false)->after('active');
        });

        Schema::create('environment_groups', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->json('notification_emails');
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('environments', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('photo_path')->nullable();
            $table->unsignedInteger('chairs_count');
            $table->unsignedInteger('benches_count');
            $table->text('description');
            $table->foreignId('environment_group_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('booking_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('color', 7);
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('teachers', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('booking_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('environment_id')->nullable()->unique()->constrained()->cascadeOnDelete();
            $table->json('weekdays');
            $table->json('time_ranges');
            $table->timestamps();
        });

        Schema::create('booking_blocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('environment_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('block_type');
            $table->date('specific_date')->nullable();
            $table->unsignedTinyInteger('weekday')->nullable();
            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            $table->string('reason')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('booking_series', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->foreignId('requester_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('environment_id')->constrained()->restrictOnDelete();
            $table->foreignId('booking_type_id')->constrained()->restrictOnDelete();
            $table->text('reason');
            $table->json('weekdays');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('requester_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('environment_id')->constrained()->restrictOnDelete();
            $table->foreignId('booking_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('booking_series_id')->nullable()->constrained()->nullOnDelete();
            $table->text('reason');
            $table->date('booking_date');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->string('status', 20)->default('pending')->index();
            $table->text('decision_reason')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
            $table->index(['environment_id', 'booking_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('booking_series');
        Schema::dropIfExists('booking_blocks');
        Schema::dropIfExists('booking_rules');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('booking_types');
        Schema::dropIfExists('environments');
        Schema::dropIfExists('environment_groups');
        Schema::table('email_domains', function (Blueprint $table): void {
            $table->dropColumn('is_default');
        });
    }
};
