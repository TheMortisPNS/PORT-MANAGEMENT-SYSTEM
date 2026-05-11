<?php
include 'db_connect.php';
include 'helpers.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit();
}

function fetch_ship(mysqli $conn, int $id): ?array {
    $stmt = $conn->prepare('SELECT * FROM arrivals WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $ship = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $ship ?: null;
}

$ship = fetch_ship($conn, $id);
if (!$ship) {
    header('Location: index.php');
    exit();
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ship_name = trim($_POST['ship_name'] ?? '');
    $imo_number = trim($_POST['imo_number'] ?? '');
    $port = trim($_POST['port'] ?? '');
    $arrival_time = normalize_datetime_local($_POST['arrival_time'] ?? null);
    $departure_date = normalize_datetime_local($_POST['departure_date'] ?? null);

    $port_charges = parse_money($_POST['port_charges'] ?? 0);
    $pilotage = parse_money($_POST['pilotage'] ?? 0);
    $towage = parse_money($_POST['towage'] ?? 0);
    $berth_dues = parse_money($_POST['berth_dues'] ?? 0);
    $services = parse_money($_POST['services'] ?? 0);
    $garbage = parse_money($_POST['garbage'] ?? 0);
    $crew_changes = parse_money($_POST['crew_changes'] ?? 0);
    $cargo_ops = parse_money($_POST['cargo_ops'] ?? 0);
    $customs_docs = parse_money($_POST['customs_docs'] ?? 0);
    $agency_fee = parse_money($_POST['agency_fee'] ?? 0);

    if ($ship_name !== '' && $arrival_time !== null) {
        $stmt = $conn->prepare(
            "UPDATE arrivals SET
                ship_name = ?, imo_number = ?, port = ?, arrival_time = ?, departure_date = ?,
                port_charges = ?, pilotage = ?, towage = ?, berth_dues = ?, services = ?,
                garbage = ?, crew_changes = ?, cargo_ops = ?, customs_docs = ?, agency_fee = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            'sssssddddddddddi',
            $ship_name,
            $imo_number,
            $port,
            $arrival_time,
            $departure_date,
            $port_charges,
            $pilotage,
            $towage,
            $berth_dues,
            $services,
            $garbage,
            $crew_changes,
            $cargo_ops,
            $customs_docs,
            $agency_fee,
            $id
        );

        if ($stmt->execute()) {
            $message = '✅ Το PDA αποθηκεύτηκε επιτυχώς.';
            $ship = fetch_ship($conn, $id);
        } else {
            $message = '❌ Σφάλμα αποθήκευσης: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = '❌ Συμπλήρωσε τουλάχιστον όνομα πλοίου και ημερομηνία άφιξης.';
    }
}

$tripType = calc_trip_type($ship['arrival_time'] ?? null, $ship['departure_date'] ?? null);
$duration = calc_duration_days($ship['arrival_time'] ?? null, $ship['departure_date'] ?? null);
$totalCost = pda_total($ship);

$costRows = [
    ['label' => 'Λιμενικά τέλη', 'icon' => '⚓', 'field' => 'port_charges'],
    ['label' => 'Πλοηγός', 'icon' => '⚓', 'field' => 'pilotage'],
    ['label' => 'Ρυμουλκά', 'icon' => '⚓', 'field' => 'towage'],
    ['label' => 'Θέση πρόσδεσης', 'icon' => '⚓', 'field' => 'berth_dues'],
    ['label' => 'Line handling / Services', 'icon' => '🛠️', 'field' => 'services'],
    ['label' => 'Διαχείριση απορριμμάτων', 'icon' => '🛠️', 'field' => 'garbage'],
    ['label' => 'Μεταφορές πληρώματος', 'icon' => '👨‍✈️', 'field' => 'crew_changes'],
    ['label' => 'Φορτοεκφόρτωση', 'icon' => '📦', 'field' => 'cargo_ops'],
    ['label' => 'Τελωνεία / Χαρτιά', 'icon' => '🧾', 'field' => 'customs_docs'],
    ['label' => 'Αμοιβή πράκτορα', 'icon' => '🏢', 'field' => 'agency_fee'],
];
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATLAS GROUP | PDA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Raleway:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --navy:#0a1628; --deep-blue:#0d2137; --gold:#c9a84c; --gold-light:#e8c96d; --white:#f0f4f8; }
        body { background:linear-gradient(135deg,#0a1628 0%,#0d2137 50%,#0a2a4a 100%); min-height:100vh; font-family:'Raleway',sans-serif; color:var(--white); }
        body::before { content:''; position:fixed; inset:0; background:radial-gradient(ellipse at 20% 50%,rgba(26,74,107,0.3) 0%,transparent 50%),radial-gradient(ellipse at 80% 20%,rgba(201,168,76,0.05) 0%,transparent 40%); pointer-events:none; }
        .content-wrapper { position:relative; z-index:1; }
        .navbar-atlas { background:rgba(10,22,40,0.95); border-bottom:1px solid rgba(201,168,76,.3); padding:12px 0; backdrop-filter:blur(10px); }
        .navbar-brand-atlas { display:flex; align-items:center; gap:12px; text-decoration:none; }
        .logo-img { width:55px; height:55px; border-radius:50%; border:2px solid var(--gold); object-fit:cover; box-shadow:0 0 15px rgba(201,168,76,.4); }
        .brand-name { font-family:'Cinzel',serif; font-size:1.2rem; color:var(--gold); letter-spacing:3px; font-weight:700; }
        .brand-sub { font-size:.65rem; color:rgba(240,244,248,.6); letter-spacing:2px; text-transform:uppercase; }
        .nav-btn { background:transparent; border:1px solid rgba(201,168,76,.4); color:var(--white)!important; border-radius:4px; padding:6px 14px; font-size:.8rem; letter-spacing:1px; text-decoration:none; margin-left:8px; transition:.3s; }
        .nav-btn:hover,.nav-btn.active { background:var(--gold); color:var(--navy)!important; border-color:var(--gold); }
        .atlas-card { background:rgba(255,255,255,.03); border:1px solid rgba(201,168,76,.2); border-radius:14px; padding:24px; margin:24px 0; }
        .section-title { font-family:'Cinzel',serif; font-size:1rem; letter-spacing:3px; color:var(--gold); text-transform:uppercase; border-bottom:1px solid rgba(201,168,76,.2); padding-bottom:10px; margin-bottom:18px; }
        .form-label { font-size:.72rem; letter-spacing:2px; text-transform:uppercase; color:rgba(240,244,248,.5); }
        .form-control { background:rgba(255,255,255,.05)!important; border:1px solid rgba(201,168,76,.2)!important; color:var(--white)!important; border-radius:8px; }
        .form-control:focus { border-color:var(--gold)!important; box-shadow:0 0 0 3px rgba(201,168,76,.15)!important; }
        .btn-atlas { background:var(--gold); border:none; color:var(--navy); border-radius:8px; padding:10px 16px; font-family:'Cinzel',serif; letter-spacing:1px; font-size:.85rem; }
        .btn-atlas:hover { background:var(--gold-light); }
        .btn-print { background:transparent; border:1px solid rgba(240,244,248,.3); color:var(--white); border-radius:8px; padding:10px 16px; font-size:.85rem; }
        .btn-print:hover { border-color:var(--gold); color:var(--gold); }
        .info-box { background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.08); border-radius:10px; padding:12px; }
        .pda-table { width:100%; border-collapse:collapse; }
        .pda-table th,.pda-table td { border-bottom:1px solid rgba(255,255,255,.08); padding:11px 8px; }
        .pda-table th { font-size:.72rem; letter-spacing:2px; text-transform:uppercase; color:rgba(240,244,248,.55); }
        .pda-table tfoot td { border-top:2px solid rgba(201,168,76,.45); border-bottom:none; font-weight:700; color:var(--gold-light); font-size:1rem; }
        .msg { background:rgba(25,135,84,.15); border:1px solid rgba(25,135,84,.3); color:#91e5b5; border-radius:8px; padding:10px 12px; margin-bottom:10px; }
        .atlas-footer { border-top:1px solid rgba(201,168,76,.15); padding:20px 0; text-align:center; font-size:.75rem; color:rgba(240,244,248,.3); letter-spacing:2px; margin-top:30px; }
        .atlas-footer span { color:var(--gold); }

        @media print {
            body { background:#fff !important; color:#111 !important; }
            body::before, .navbar-atlas, .no-print, .atlas-footer { display:none !important; }
            .content-wrapper { color:#111 !important; }
            .atlas-card { border:1px solid #bbb !important; background:#fff !important; box-shadow:none !important; }
            .section-title { color:#111 !important; border-color:#bbb !important; }
            .pda-table th, .pda-table td { color:#111 !important; border-color:#ddd !important; }
        }
    </style>
</head>
<body>
<div class="content-wrapper">
    <nav class="navbar-atlas no-print">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="index.php" class="navbar-brand-atlas">
                <img src="https://cdn.abacus.ai/images/2740de7f-a8ff-4f15-a162-2b34e6333d3f.png" class="logo-img" alt="Atlas Logo">
                <div>
                    <div class="brand-name">ATLAS GROUP</div>
                    <div class="brand-sub">Port Management System</div>
                </div>
            </a>
            <div>
                <a href="index.php" class="nav-btn">🏠 Αρχική</a>
                <a href="add_ship.php" class="nav-btn">➕ Νέα Άφιξη</a>
                <a href="search.php" class="nav-btn">🔍 Αναζήτηση</a>
                <a href="calendar.php" class="nav-btn">📅 Ημερολόγιο</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="atlas-card no-print">
            <div class="section-title">🧾 Επεξεργασία PDA</div>
            <?php if ($message !== ''): ?><div class="msg"><?= safe_h($message) ?></div><?php endif; ?>
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Όνομα Πλοίου</label><input class="form-control" type="text" name="ship_name" required value="<?= safe_h($ship['ship_name']) ?>"></div>
                    <div class="col-md-4"><label class="form-label">IMO Number</label><input class="form-control" type="text" name="imo_number" value="<?= safe_h($ship['imo_number']) ?>"></div>
                    <div class="col-md-4"><label class="form-label">Λιμάνι</label><input class="form-control" type="text" name="port" value="<?= safe_h($ship['port'] ?? '') ?>"></div>
                    <div class="col-md-6"><label class="form-label">Ημερομηνία / Ώρα Άφιξης</label><input class="form-control" type="datetime-local" name="arrival_time" required value="<?= safe_h(format_datetime_local_input($ship['arrival_time'] ?? null)) ?>"></div>
                    <div class="col-md-6"><label class="form-label">Ημερομηνία / Ώρα Αναχώρησης</label><input class="form-control" type="datetime-local" name="departure_date" value="<?= safe_h(format_datetime_local_input($ship['departure_date'] ?? null)) ?>"></div>
                </div>

                <div class="row g-3 mt-2">
                    <?php foreach ($costRows as $row): ?>
                        <div class="col-md-4">
                            <label class="form-label"><?= safe_h($row['icon'] . ' ' . $row['label']) ?></label>
                            <input class="form-control" type="number" step="0.01" min="0" name="<?= safe_h($row['field']) ?>" value="<?= safe_h((string)($ship[$row['field']] ?? 0)) ?>">
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button class="btn-atlas" type="submit">💾 Αποθήκευση PDA</button>
                    <button class="btn-print" type="button" onclick="window.print()">🖨️ Print as PDF</button>
                </div>
            </form>
        </div>

        <div class="atlas-card" id="printArea">
            <div class="section-title">📄 Proforma Disbursement Account</div>
            <div class="row g-3 mb-3">
                <div class="col-md-4"><div class="info-box"><small>Πλοίο</small><div><b><?= safe_h($ship['ship_name']) ?></b></div></div></div>
                <div class="col-md-4"><div class="info-box"><small>IMO</small><div><b><?= safe_h($ship['imo_number']) ?></b></div></div></div>
                <div class="col-md-4"><div class="info-box"><small>Λιμάνι</small><div><b><?= safe_h($ship['port'] ?? '—') ?></b></div></div></div>
                <div class="col-md-4"><div class="info-box"><small>Άφιξη</small><div><b><?= format_datetime_greek($ship['arrival_time'] ?? null) ?></b></div></div></div>
                <div class="col-md-4"><div class="info-box"><small>Αναχώρηση</small><div><b><?= format_datetime_greek($ship['departure_date'] ?? null) ?></b></div></div></div>
                <div class="col-md-2"><div class="info-box"><small>Τύπος</small><div><b><?= safe_h($tripType) ?></b></div></div></div>
                <div class="col-md-2"><div class="info-box"><small>Διάρκεια</small><div><b><?= $duration !== null ? $duration . ' ημ.' : '—' ?></b></div></div></div>
            </div>

            <table class="pda-table">
                <thead>
                    <tr><th style="width:60px">Icon</th><th>Κατηγορία Κόστους</th><th style="text-align:right">Ποσό (€)</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($costRows as $row): ?>
                        <tr>
                            <td><?= safe_h($row['icon']) ?></td>
                            <td><?= safe_h($row['label']) ?></td>
                            <td style="text-align:right"><?= number_format((float)($ship[$row['field']] ?? 0), 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr><td colspan="2">Συνολικό Κόστος PDA</td><td style="text-align:right">€ <?= number_format($totalCost, 2) ?></td></tr>
                </tfoot>
            </table>
        </div>
    </div>

    <footer class="atlas-footer"><span>ATLAS GROUP</span> · Port Management System · Πανεπιστήμιο Δυτικής Αττικής &copy; 2026</footer>
</div>
</body>
</html>
<?php $conn->close(); ?>
 
