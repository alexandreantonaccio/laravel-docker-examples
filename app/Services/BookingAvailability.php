<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\DB;

class BookingAvailability
{
    public function reason(int $environmentId, string $date, string $startsAt, string $endsAt, int|array|null $ignoreBookingIds = null): ?string
    {
        $weekday = (int) date('w', strtotime($date));
        $rules = DB::table('booking_rules')->where('environment_id', $environmentId)->first()
            ?? DB::table('booking_rules')->whereNull('environment_id')->first();
        if (! $rules) {
            return 'As regras de agendamento ainda não foram configuradas.';
        }
        $weekdays = json_decode($rules->weekdays, true) ?: [];
        if (! in_array($weekday, $weekdays, true)) {
            return 'O dia selecionado não está habilitado para agendamento.';
        }
        $insideRange = collect(json_decode($rules->time_ranges, true) ?: [])
            ->contains(fn (array $range): bool => $startsAt >= $range['starts_at'] && $endsAt <= $range['ends_at']);
        if (! $insideRange) {
            return 'O horário está fora das faixas permitidas.';
        }

        foreach (DB::table('booking_blocks')->where('active', true)
            ->where(fn ($query) => $query->whereNull('environment_id')->orWhere('environment_id', $environmentId))
            ->get() as $block) {
            $dateMatch = in_array($block->block_type, ['date', 'date_time'], true)
                ? $block->specific_date === $date
                : in_array($block->block_type, ['weekday', 'weekday_time'], true) && (int) $block->weekday === $weekday;
            $timeMatch = in_array($block->block_type, ['date_time', 'weekday_time'], true)
                && $block->starts_at < $endsAt && $block->ends_at > $startsAt;
            if ($dateMatch && (! in_array($block->block_type, ['date_time', 'weekday_time'], true) || $timeMatch)) {
                return $block->reason ?: 'O período está bloqueado para agendamentos.';
            }
        }

        $conflict = Booking::query()->where('environment_id', $environmentId)
            ->whereDate('booking_date', $date)->where('status', 'approved')
            ->where('starts_at', '<', $endsAt)->where('ends_at', '>', $startsAt)
            ->when(is_int($ignoreBookingIds), fn ($query) => $query->where('id', '!=', $ignoreBookingIds))
            ->when(is_array($ignoreBookingIds), fn ($query) => $query->whereNotIn('id', $ignoreBookingIds))->exists();
        if ($conflict) {
            return 'Já existe um agendamento aprovado neste horário e ambiente.';
        }

        return null;
    }
}
