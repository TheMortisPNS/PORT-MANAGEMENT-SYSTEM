<?php
// Ορισμός ζώνης ώρας για την PHP (Ελλάδα)
date_default_timezone_set('Europe/Athens');

// --- Ρύθμισε τα παρακάτω με τα στοιχεία που σου έδωσε το InfinityFree ---
$servername = "sql201.infinityfree.com";    // π.χ. sql123.infinityfree.com
$username   = "if0_41687406";               // όνομα χρήστη MySQL
$password   = "43AB21CD";                   // κωδικός MySQL
$dbname     = "if0_41687406_portdb";        // όνομα βάσης

// --- Σύνδεση ---
$conn = new mysqli($servername, $username, $password, $dbname);

// Έλεγχος σύνδεσης
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ορισμός ζώνης ώρας για την MySQL (GMT+3 για Ελλάδα, ισχύει σε θερινή ώρα)
$conn->query("SET time_zone = '+03:00'");

// Ορισμός charset για σωστή απεικόνιση ελληνικών
$conn->set_charset('utf8mb4');

// --- Δημιουργία βασικού πίνακα αν δεν υπάρχει (ασφαλές βήμα για first-deploy) ---
$createSql = "
CREATE TABLE IF NOT EXISTS arrivals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ship_name VARCHAR(100) NOT NULL,
    imo_number VARCHAR(20) DEFAULT NULL,
    cargo_type VARCHAR(50) DEFAULT NULL,
    arrival_time DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
if (!$conn->query($createSql)) {
    // δεν τερματίζουμε εδώ — προχωράμε αλλά γράφουμε error στο error log
    error_log('Could not ensure base arrivals table exists: ' . $conn->error);
}

// --- Εξασφάλιση επιπλέον στηλών (function που παρείχες, με μικρή βελτίωση) ---
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

    // Πρώτα ελέγχουμε αν ο πίνακας υπάρχει (προστασία)
    $res = $conn->query("SHOW TABLES LIKE 'arrivals'");
    if (!$res || $res->num_rows === 0) {
        // Ο πίνακας δεν υπάρχει — η base creation παραπάνω θα τον έχει δημιουργήσει συνήθως.
        return;
    }

    // Πάρε υπάρχουσες στήλες
    $existing = [];
    $columnsResult = $conn->query("SHOW COLUMNS FROM arrivals");
    if (!$columnsResult) {
        // αν αποτύχει, γράφουμε στο log και φεύγουμε
        error_log('SHOW COLUMNS failed: ' . $conn->error);
        return;
    }

    while ($column = $columnsResult->fetch_assoc()) {
        $existing[$column['Field']] = true;
    }

    // Συγκεντρώνουμε ALTER clauses μόνο για τις στήλες που λείπουν
    $alterClauses = [];
    foreach ($requiredColumns as $column => $ddl) {
        if (!isset($existing[$column])) {
            $alterClauses[] = $ddl;
        }
    }

    if (!empty($alterClauses)) {
        $sql = "ALTER TABLE arrivals " . implode(', ', $alterClauses);
        if (!$conn->query($sql)) {
            error_log('ALTER TABLE failed: ' . $conn->error . ' | SQL: ' . $sql);
        }
    }
}
?> 
