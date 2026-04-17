<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "port_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset('utf8mb4');

ensure_arrivals_schema($conn);

function ensure_arrivals_schema(mysqli $conn): void {
    $requiredColumns = [
        'departure_date' => "ADD COLUMN departure_date DATETIME NULL AFTER arrival_time",
        'port' => "ADD COLUMN port VARCHAR(100) NULL AFTER cargo_type",
        'port_charges' => "ADD COLUMN port_charges DECIMAL(10,2) NOT NULL DEFAULT 0.00",
        'pilotage' => "ADD COLUMN pilotage DECIMAL(10,2) NOT NULL DEFAULT 0.00",
        'towage' => "ADD COLUMN towage DECIMAL(10,2) NOT NULL DEFAULT 0.00",
        'berth_dues' => "ADD COLUMN berth_dues DECIMAL(10,2) NOT NULL DEFAULT 0.00",
        'services' => "ADD COLUMN services DECIMAL(10,2) NOT NULL DEFAULT 0.00",
        'garbage' => "ADD COLUMN garbage DECIMAL(10,2) NOT NULL DEFAULT 0.00",
        'crew_changes' => "ADD COLUMN crew_changes DECIMAL(10,2) NOT NULL DEFAULT 0.00",
        'cargo_ops' => "ADD COLUMN cargo_ops DECIMAL(10,2) NOT NULL DEFAULT 0.00",
        'customs_docs' => "ADD COLUMN customs_docs DECIMAL(10,2) NOT NULL DEFAULT 0.00",
        'agency_fee' => "ADD COLUMN agency_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00"
    ];

    $existing = [];
    $columnsResult = $conn->query("SHOW COLUMNS FROM arrivals");
    if (!$columnsResult) {
        return;
    }

    while ($column = $columnsResult->fetch_assoc()) {
        $existing[$column['Field']] = true;
    }

    $alterClauses = [];
    foreach ($requiredColumns as $column => $ddl) {
        if (!isset($existing[$column])) {
            $alterClauses[] = $ddl;
        }
    }

    if (!empty($alterClauses)) {
        $conn->query("ALTER TABLE arrivals " . implode(', ', $alterClauses));
    }
}
?>
