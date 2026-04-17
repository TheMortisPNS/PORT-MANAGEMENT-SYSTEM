<?php

function safe_h(?string $value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function normalize_datetime_local(?string $value): ?string {
    if ($value === null) {
        return null;
    }
    $value = trim($value);
    if ($value === '') {
        return null;
    }
    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return null;
    }
    return date('Y-m-d H:i:s', $timestamp);
}

function format_datetime_local_input(?string $value): string {
    if (empty($value)) {
        return '';
    }
    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return '';
    }
    return date('Y-m-d\TH:i', $timestamp);
}

function format_datetime_greek(?string $value): string {
    if (empty($value)) {
        return '—';
    }
    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return '—';
    }
    return date('d/m/Y H:i', $timestamp);
}

function parse_money($value): float {
    if ($value === null) {
        return 0.0;
    }
    $normalized = str_replace(',', '.', trim((string)$value));
    if ($normalized === '' || !is_numeric($normalized)) {
        return 0.0;
    }
    return round((float)$normalized, 2);
}

function calc_trip_type(?string $arrival, ?string $departure): string {
    $hasArrival = !empty($arrival);
    $hasDeparture = !empty($departure);

    if ($hasArrival && !$hasDeparture) {
        return 'Άφιξη';
    }
    if (!$hasArrival && $hasDeparture) {
        return 'Αναχώρηση';
    }
    if ($hasArrival && $hasDeparture) {
        $arrivalDate = date('Y-m-d', strtotime($arrival));
        $departureDate = date('Y-m-d', strtotime($departure));
        return $arrivalDate === $departureDate ? 'Διέλευση' : 'Παραμονή';
    }

    return '—';
}

function calc_duration_days(?string $arrival, ?string $departure): ?int {
    if (empty($arrival) || empty($departure)) {
        return null;
    }

    $arrivalTs = strtotime($arrival);
    $departureTs = strtotime($departure);

    if ($arrivalTs === false || $departureTs === false || $departureTs < $arrivalTs) {
        return null;
    }

    $diffSeconds = $departureTs - $arrivalTs;
    $days = (int)ceil($diffSeconds / 86400);
    return max($days, 1);
}

function trip_badge_class(string $tripType): string {
    return match ($tripType) {
        'Άφιξη' => 'badge-trip-arrival',
        'Αναχώρηση' => 'badge-trip-departure',
        'Διέλευση' => 'badge-trip-passage',
        'Παραμονή' => 'badge-trip-stay',
        default => 'badge-trip-default',
    };
}

function pda_total(array $row): float {
    $fields = [
        'port_charges', 'pilotage', 'towage', 'berth_dues', 'services',
        'garbage', 'crew_changes', 'cargo_ops', 'customs_docs', 'agency_fee'
    ];

    $total = 0.0;
    foreach ($fields as $field) {
        $total += (float)($row[$field] ?? 0);
    }
    return round($total, 2);
}
