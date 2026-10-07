<?php

use App\Models\Booking;
use App\Models\BookingType;
use App\Models\Environment;
use App\Models\Material;
use App\Models\MaterialGroup;
use App\Models\MaterialRental;
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

test('students technicians and professors can request bookings without a teacher', function () {
    Mail::fake();
    $environment = useCaseEnvironment();
    $type = useCaseType();
    useCaseRules();
    $date = today()->addDay()->toDateString();

    foreach (['aluno', 'tecnico', 'professor'] as $profile) {
        $requester = User::factory()->create(['profile' => $profile]);

        $this->actingAs($requester)->get(route('bookings.calendar'))
            ->assertOk()
            ->assertSee(route('bookings.create'))
            ->assertSee('Solicitar agendamento');
        $this->actingAs($requester)->get(route('bookings.create'))
            ->assertOk()
            ->assertSee('Sem docente responsável');
        $this->post(route('bookings.store'), [
            'teacher_id' => null,
            'environment_id' => $environment->id,
            'booking_type_id' => $type->id,
            'reason' => 'Solicitação de teste para '.$profile,
            'booking_date' => $date,
            'starts_at' => '10:00',
            'ends_at' => '11:00',
        ])->assertRedirect(route('bookings.calendar'));

        $booking = Booking::query()->latest('id')->firstOrFail();
        expect($booking->requester_user_id)->toBe($requester->id)
            ->and($booking->teacher_id)->toBeNull();
    }
});

test('booking requests accept custom teacher environment and type values without catalog links', function () {
    Mail::fake();
    $requester = User::factory()->create(['profile' => 'tecnico']);
    $admin = useCaseAdministrator();
    useCaseRules();

    $this->actingAs($requester)->post(route('bookings.store'), [
        'teacher_id' => 'other',
        'teacher_other' => 'Docente convidado',
        'environment_id' => 'other',
        'environment_other' => 'Laboratório temporário',
        'booking_type_id' => 'other',
        'booking_type_other' => 'Demonstração',
        'reason' => 'Teste com valores livres',
        'booking_date' => today()->addDay()->toDateString(),
        'starts_at' => '10:00',
        'ends_at' => '11:00',
    ])->assertRedirect(route('bookings.calendar'));

    $booking = Booking::query()->sole();
    expect($booking->teacher_id)->toBeNull()
        ->and($booking->environment_id)->toBeNull()
        ->and($booking->booking_type_id)->toBeNull()
        ->and($booking->teacher_other)->toBe('Docente convidado')
        ->and($booking->environment_other)->toBe('Laboratório temporário')
        ->and($booking->booking_type_other)->toBe('Demonstração');

    $this->actingAs($admin)->get(route('bookings.show', $booking))
        ->assertOk()
        ->assertSee('Docente convidado')
        ->assertSee('Laboratório temporário')
        ->assertSee('Demonstração');
    $this->post(route('bookings.approve', $booking))->assertRedirect();
    expect($booking->fresh()->status)->toBe('approved');
});

test('material rental requests reserve quantity for overlapping periods and require approval', function () {
    Mail::fake();
    $requester = User::factory()->create(['profile' => 'aluno']);
    $otherRequester = User::factory()->create(['profile' => 'professor']);
    $admin = useCaseAdministrator();
    $group = MaterialGroup::query()->create([
        'name' => 'Audiovisual',
        'notification_emails' => ['responsavel@example.com'],
        'active' => true,
    ]);
    $material = Material::query()->create([
        'material_group_id' => $group->id,
        'name' => 'Projetor',
        'code' => 'PROJ-01',
        'quantity' => 3,
        'active' => true,
    ]);
    $start = today()->addDay()->toDateString();
    $end = today()->addDays(3)->toDateString();

    $this->actingAs($requester)->get(route('materials.index'))
        ->assertOk()
        ->assertSee('Projetor')
        ->assertSee('Alugar material');
    $this->actingAs($requester)->post(route('material-rentals.store'), [
        'material_id' => $material->id,
        'quantity' => 2,
        'starts_on' => $start,
        'ends_on' => $end,
        'reason' => 'Apresentação acadêmica',
    ])->assertRedirect(route('material-rentals.index'));

    $pending = MaterialRental::query()->sole();
    expect($pending->status)->toBe('pending')
        ->and($pending->requester_user_id)->toBe($requester->id);

    $this->actingAs($otherRequester)->from(route('material-rentals.create'))
        ->post(route('material-rentals.store'), [
            'material_id' => $material->id,
            'quantity' => 2,
            'starts_on' => $start,
            'ends_on' => $end,
            'reason' => 'Outro evento',
        ])->assertSessionHasErrors('quantity');
    expect(MaterialRental::query()->count())->toBe(1);

    $this->actingAs($admin)->post(route('material-rentals.approve', $pending))
        ->assertRedirect();
    expect($pending->fresh()->status)->toBe('approved');

    $this->actingAs($admin)->put(route('materials.update', $material), [
        'name' => $material->name,
        'code' => $material->code,
        'quantity' => 1,
        'material_group_id' => $group->id,
    ])->assertStatus(422);
    expect($material->fresh()->quantity)->toBe(3);
});

test('material stock checks allow separate reservations within the same longer period', function () {
    Mail::fake();
    $requester = User::factory()->create();
    $admin = useCaseAdministrator();
    $material = Material::query()->create([
        'name' => 'Câmera',
        'code' => 'CAM-01',
        'quantity' => 2,
        'active' => true,
    ]);
    $firstDay = today()->addDays(2);
    $lastDay = today()->addDays(4);
    foreach ([$firstDay, $lastDay] as $day) {
        MaterialRental::query()->create([
            'material_id' => $material->id,
            'requester_user_id' => $admin->id,
            'quantity' => 1,
            'starts_on' => $day->toDateString(),
            'ends_on' => $day->toDateString(),
            'reason' => 'Reserva em dia separado',
            'status' => 'approved',
        ]);
    }

    $this->actingAs($requester)->post(route('material-rentals.store'), [
        'material_id' => $material->id,
        'quantity' => 1,
        'starts_on' => $firstDay->toDateString(),
        'ends_on' => $lastDay->toDateString(),
        'reason' => 'Uso em todo o período',
    ])->assertRedirect(route('material-rentals.index'));
    expect(MaterialRental::query()->where('requester_user_id', $requester->id)->sole()->status)
        ->toBe('pending');
});

test('administrators can manage materials and material groups', function () {
    $admin = useCaseAdministrator();
    $this->actingAs($admin)->post(route('material-groups.store'), [
        'name' => 'Informática',
        'notification_emails_text' => "equipe@example.com\nsuporte@example.com",
    ])->assertRedirect(route('material-groups.index'));
    $group = MaterialGroup::query()->sole();
    expect($group->notification_emails)->toBe(['equipe@example.com', 'suporte@example.com']);

    $this->post(route('materials.store'), [
        'name' => 'Notebook',
        'code' => 'NOTE-01',
        'description' => 'Equipamento para empréstimo',
        'quantity' => 4,
        'material_group_id' => $group->id,
    ])->assertRedirect(route('materials.index'));
    $material = Material::query()->sole();
    expect($material->quantity)->toBe(4)
        ->and($material->material_group_id)->toBe($group->id);

    $this->patch(route('material-groups.toggle', $group))->assertRedirect();
    expect($group->fresh()->active)->toBeFalse()
        ->and($material->fresh()->material_group_id)->toBeNull();
});
