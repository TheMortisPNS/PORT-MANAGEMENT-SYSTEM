<?php
header('Content-Type: application/json; charset=utf-8');
include 'db_connect.php';

$id     = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$action = $_POST['action'] ?? '';

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'invalid_id']);
    exit;
}

try {
    if ($action === 'arrived') {
        $now = date('Y-m-d H:i:s');
        $status = 'ΣΤΟ ΛΙΜΑΝΙ';
        $stmt = $conn->prepare("UPDATE arrivals SET status = ?, actual_arrival = COALESCE(actual_arrival, ?) WHERE id = ?");
        $stmt->bind_param('ssi', $status, $now, $id);
        $ok = $stmt->execute();
        echo json_encode(['success' => (bool)$ok, 'status' => $status, 'actual_arrival' => $now]);
        exit;
    }

    if ($action === 'departed') {
        $now = date('Y-m-d H:i:s');
        $status = 'ΑΝΑΧΩΡΗΣΕ';
        $stmt = $conn->prepare("UPDATE arrivals SET status = ?, actual_departure = ? WHERE id = ?");
        $stmt->bind_param('ssi', $status, $now, $id);
        $ok = $stmt->execute();
        echo json_encode(['success' => (bool)$ok, 'status' => $status, 'actual_departure' => $now]);
        exit;
    }

    if ($action === 'save_notes') {
        $notes = $_POST['notes'] ?? '';
        $stmt = $conn->prepare("UPDATE arrivals SET internal_notes = ? WHERE id = ?");
        $stmt->bind_param('si', $notes, $id);
        $ok = $stmt->execute();
        echo json_encode(['success' => (bool)$ok, 'internal_notes' => $notes]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'unknown_action']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
$conn->close();
