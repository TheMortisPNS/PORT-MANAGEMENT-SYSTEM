<?php
// get_ship.php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db_connect.php';
require 'helpers.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo json_encode(['ok'=>false,'error'=>'invalid_id']);
    exit;
}

$stmt = $conn->prepare("SELECT id, ship_name, imo_number, cargo_type, arrival_time, departure_date, port, status, notes FROM arrivals WHERE id = ?");
if (!$stmt) {
    echo json_encode(['ok'=>false,'error'=>'prepare_failed']);
    exit;
}
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
if (!$row) {
    echo json_encode(['ok'=>false,'error'=>'not_found']);
    exit;
}

// Προετοιμασία προς αποστολή στον client
$arrival_raw   = $row['arrival_time'] ?? null;
$departure_raw = $row['departure_date'] ?? null;

// Χρησιμοποιούμε helper functions (format_datetime_greek, calc_duration_days)
// Αν τα helpers δεν έχουν τις ακριβείς ονομασίες, προσαρμόστε ανάλογα.
$arrival_display   = format_datetime_greek($arrival_raw ?? null);
$departure_display = format_datetime_greek($departure_raw ?? null);

$duration_days = calc_duration_days($arrival_raw ?? null, $departure_raw ?? null);
$duration_display = $duration_days !== null ? $duration_days . ' ημ.' : '—';

$data = [
    'id' => (int)$row['id'],
    'ship_name' => $row['ship_name'] ?? '',
    'imo_number' => $row['imo_number'] ?? '',
    'cargo_type' => $row['cargo_type'] ?? '',
    'arrival_raw' => $arrival_raw,
    'departure_raw' => $departure_raw,
    'arrival_display' => $arrival_display,
    'departure_display' => $departure_display,
    'duration' => $duration_days,
    'duration_display' => $duration_display,
    'port' => $row['port'] ?? '',
    'status' => $row['status'] ?? '',
    'notes' => $row['notes'] ?? '',
];

echo json_encode(['ok'=>true, 'data'=>$data], JSON_UNESCAPED_UNICODE);
$conn->close();
