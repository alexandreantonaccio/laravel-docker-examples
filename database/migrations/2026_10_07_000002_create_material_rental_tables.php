<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_groups', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->json('notification_emails')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('materials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('material_group_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('material_rentals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('material_id')->constrained()->restrictOnDelete();
            $table->foreignId('requester_user_id')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->text('reason');
            $table->string('status', 20)->default('pending')->index();
            $table->text('decision_reason')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
            $table->index(['material_id', 'starts_on', 'ends_on', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_rentals');
        Schema::dropIfExists('materials');
        Schema::dropIfExists('material_groups');
    }
};
