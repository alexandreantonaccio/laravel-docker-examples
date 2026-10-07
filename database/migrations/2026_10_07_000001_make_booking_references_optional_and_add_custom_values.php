<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropForeign(['environment_id']);
            $table->dropForeign(['booking_type_id']);
            $table->foreignId('environment_id')->nullable()->change();
            $table->foreignId('booking_type_id')->nullable()->change();
            $table->foreign('environment_id')->references('id')->on('environments')->restrictOnDelete();
            $table->foreign('booking_type_id')->references('id')->on('booking_types')->restrictOnDelete();
            $table->string('teacher_other')->nullable();
            $table->string('environment_other')->nullable();
            $table->string('booking_type_other')->nullable();
        });
    }

    public function down(): void
    {
        if (DB::table('bookings')->whereNull('environment_id')->orWhereNull('booking_type_id')->exists()) {
            throw new RuntimeException('Remove or reassign bookings using custom environments or types before rolling back this migration.');
        }

        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropForeign(['environment_id']);
            $table->dropForeign(['booking_type_id']);
            $table->foreignId('environment_id')->nullable(false)->change();
            $table->foreignId('booking_type_id')->nullable(false)->change();
            $table->foreign('environment_id')->references('id')->on('environments')->restrictOnDelete();
            $table->foreign('booking_type_id')->references('id')->on('booking_types')->restrictOnDelete();
            $table->dropColumn(['teacher_other', 'environment_other', 'booking_type_other']);
        });
    }
};
