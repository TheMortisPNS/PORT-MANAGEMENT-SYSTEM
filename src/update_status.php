<?php
include 'db_connect.php';

$id     = intval($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($id > 0) {
    if ($action === 'arrived') {
        $conn->query("UPDATE arrivals SET status='ΣΤΟ ΛΙΜΑΝΙ', actual_arrival=NOW() WHERE id=$id");
    } elseif ($action === 'departed') {
        $conn->query("UPDATE arrivals SET status='ΑΝΑΧΩΡΗΣΕ', actual_departure=NOW() WHERE id=$id");
    } elseif ($action === 'save_notes') {
        $notes = $conn->real_escape_string($_POST['notes'] ?? '');
        $conn->query("UPDATE arrivals SET internal_notes='$notes' WHERE id=$id");
    }
}

header('Content-Type: application/json');
echo json_encode(['success' => true]);
