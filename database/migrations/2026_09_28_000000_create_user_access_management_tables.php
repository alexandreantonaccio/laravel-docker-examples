<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('functional_id')->nullable()->unique();
            $table->string('phone', 30)->nullable();
            $table->string('profile', 30)->nullable()->index();
            $table->string('course')->nullable();
            $table->string('job_title')->nullable();
            $table->string('employment_link')->nullable();
            $table->string('enrollment_proof_path')->nullable();
            $table->string('registration_status', 40)->default('pending_email')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('verification_sent_at')->nullable();
            $table->timestamp('documentation_validated_at')->nullable();
            $table->foreignId('documentation_validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('documentation_notes')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->foreignId('deactivated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('deactivation_reason')->nullable();
        });

        DB::table('users')->update([
            'registration_status' => 'approved',
            'email_verified_at' => DB::raw('COALESCE(email_verified_at, created_at)'),
        ]);

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('permission_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('group_permission', function (Blueprint $table) {
            $table->foreignId('permission_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->primary(['permission_group_id', 'permission_id']);
        });

        Schema::create('group_user', function (Blueprint $table) {
            $table->foreignId('permission_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['permission_group_id', 'user_id']);
        });

        Schema::create('user_permission_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->string('effect', 10);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'permission_id']);
        });

        Schema::create('user_profile_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('old_profile', 30);
            $table->string('new_profile', 30);
            $table->json('archived_fields')->nullable();
            $table->text('validation_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('user_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->json('details')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'action']);
        });

        Schema::create('email_domains', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->unique();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('user_profile_options', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30);
            $table->string('value');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->unique(['type', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profile_options');
        Schema::dropIfExists('email_domains');
        Schema::dropIfExists('user_audit_logs');
        Schema::dropIfExists('user_profile_history');
        Schema::dropIfExists('user_permission_adjustments');
        Schema::dropIfExists('group_user');
        Schema::dropIfExists('group_permission');
        Schema::dropIfExists('permission_groups');
        Schema::dropIfExists('permissions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['documentation_validated_by']);
            $table->dropForeign(['deactivated_by']);
            $table->dropUnique(['functional_id']);
            $table->dropIndex(['profile']);
            $table->dropIndex(['registration_status']);
            $table->dropIndex(['is_active']);
            $table->dropColumn([
                'functional_id', 'phone', 'profile', 'course', 'job_title', 'employment_link',
                'enrollment_proof_path', 'registration_status', 'is_active', 'verification_sent_at',
                'documentation_validated_at', 'documentation_validated_by', 'documentation_notes',
                'rejected_at', 'rejection_reason', 'deactivated_at', 'deactivated_by', 'deactivation_reason',
            ]);
        });
    }
};