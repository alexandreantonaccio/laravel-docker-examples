<?php

use App\Models\Booking;
use App\Models\BookingType;
use App\Models\Environment;
use App\Models\User;
use App\Models\UserProfileOption;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class)->beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
    $this->seed(PermissionSeeder::class);
});

function useCaseAdministrator(): User
{
    return User::factory()->create(['account_type' => config('users.administrator_account_type')]);
}

function useCaseEnvironment(): Environment
{
    return Environment::query()->create([
        'name' => 'Laboratório 1',
        'code' => 'LAB1',
        'chairs_count' => 20,
        'benches_count' => 4,
        'description' => 'Laboratório de testes',
        'active' => true,
    ]);
}

function useCaseType(): BookingType
{
    return BookingType::query()->create(['name' => 'Aula', 'color' => '#447766', 'active' => true]);
}

function useCaseRules(): void
{
    DB::table('booking_rules')->insert([
        'environment_id' => null,
        'weekdays' => json_encode([0, 1, 2, 3, 4, 5, 6]),
        'time_ranges' => json_encode([['starts_at' => '08:00', 'ends_at' => '20:00']]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

test('support catalog normalizes course names and preserves deactivate state', function () {
    $admin = useCaseAdministrator();

    $this->actingAs($admin)->post(route('catalogs.store', 'cursos'), ['value' => 'ciência da computação'])
        ->assertRedirect(route('catalogs.index', 'cursos'));
    $course = UserProfileOption::query()->where('type', 'course')->firstOrFail();
    expect($course->value)->toBe('CIÊNCIA DA COMPUTAÇÃO');

    $this->actingAs($admin)->patch(route('catalogs.toggle', ['cursos', $course->id]))
        ->assertRedirect();
    expect($course->fresh()->active)->toBeFalse();
});

test('booking requests are pending and only approved bookings block overlaps', function () {
    Mail::fake();
    $admin = useCaseAdministrator();
    $requester = User::factory()->create();
    $environment = useCaseEnvironment();
    $type = useCaseType();
    useCaseRules();
    $date = today()->addDay()->toDateString();

    $this->actingAs($admin)->post(route('bookings.store'), [
        'requester_user_id' => $requester->id,
        'environment_id' => $environment->id,
        'booking_type_id' => $type->id,
        'reason' => 'Aula de laboratório',
        'booking_date' => $date,
        'starts_at' => '10:00',
        'ends_at' => '11:00',
    ])->assertRedirect(route('bookings.calendar'));

    $pending = Booking::query()->where('status', 'pending')->firstOrFail();
    expect($pending->requester_user_id)->toBe($requester->id);

    Booking::query()->create([
        'requester_user_id' => $requester->id,
        'environment_id' => $environment->id,
        'booking_type_id' => $type->id,
        'reason' => 'Reserva confirmada',
        'booking_date' => $date,
        'starts_at' => '10:30',
        'ends_at' => '11:30',
        'status' => 'approved',
    ]);

    $this->post(route('bookings.approve', $pending))->assertStatus(422);
    expect($pending->fresh()->status)->toBe('pending');
});

test('any authenticated user can request a booking only for their own account', function () {
    Mail::fake();
    $requester = User::factory()->create();
    $otherUser = User::factory()->create();
    $environment = useCaseEnvironment();
    $type = useCaseType();
    useCaseRules();

    $this->actingAs($requester)->get(route('bookings.create'))->assertOk();
    $this->actingAs($requester)->post(route('bookings.store'), [
        'requester_user_id' => $otherUser->id,
        'environment_id' => $environment->id,
        'booking_type_id' => $type->id,
        'reason' => 'Solicitação própria',
        'booking_date' => today()->addDay()->toDateString(),
        'starts_at' => '10:00',
        'ends_at' => '11:00',
    ])->assertRedirect(route('bookings.calendar'));

    expect(Booking::query()->sole()->requester_user_id)->toBe($requester->id);
});
