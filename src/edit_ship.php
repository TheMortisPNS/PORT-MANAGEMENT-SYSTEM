<?php
include 'db_connect.php';
include 'helpers.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit(); }

$stmt = $conn->prepare('SELECT * FROM arrivals WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$ship = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$ship) { header('Location: index.php'); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ship_name      = trim($_POST['ship_name'] ?? '');
    $imo_number     = trim($_POST['imo_number'] ?? '');
    $cargo_type     = trim($_POST['cargo_type'] ?? 'Άλλο');
    $port           = trim($_POST['port'] ?? '');
    $arrival_time   = $ship['arrival_time'] ?? null;
    $departure_date = $ship['departure_date'] ?? null;
    $status         = trim($_POST['status'] ?? 'Active');

    $port_charges = parse_money($_POST['port_charges'] ?? 0);
    $pilotage     = parse_money($_POST['pilotage'] ?? 0);
    $towage       = parse_money($_POST['towage'] ?? 0);
    $berth_dues   = parse_money($_POST['berth_dues'] ?? 0);
    $services     = parse_money($_POST['services'] ?? 0);
    $garbage      = parse_money($_POST['garbage'] ?? 0);
    $crew_changes = parse_money($_POST['crew_changes'] ?? 0);
    $cargo_ops    = parse_money($_POST['cargo_ops'] ?? 0);
    $customs_docs = parse_money($_POST['customs_docs'] ?? 0);
    $agency_fee   = parse_money($_POST['agency_fee'] ?? 0);

    if ($ship_name === '' || $arrival_time === null) {
        header('Location: edit_ship.php?id=' . $id . '&error=missing_required');
        exit();
    }

    $updateStmt = $conn->prepare(
        "UPDATE arrivals SET
            ship_name=?, imo_number=?, cargo_type=?, arrival_time=?,
            departure_date=?, port=?, status=?,
            port_charges=?, pilotage=?, towage=?, berth_dues=?,
            services=?, garbage=?, crew_changes=?, cargo_ops=?,
            customs_docs=?, agency_fee=?
         WHERE id=?"
    );
    $updateStmt->bind_param(
        'sssssssddddddddddi',
        $ship_name, $imo_number, $cargo_type, $arrival_time,
        $departure_date, $port, $status,
        $port_charges, $pilotage, $towage, $berth_dues,
        $services, $garbage, $crew_changes, $cargo_ops,
        $customs_docs, $agency_fee, $id
    );

    if ($updateStmt->execute()) { header('Location: index.php?updated=1'); exit(); }
    $error_message = $updateStmt->error;
    $updateStmt->close();
}

$types = ['Containers', 'Πετρέλαιο', 'Χύδην Φορτίο', 'Επιβάτες', 'Άλλο'];
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATLAS GROUP | Επεξεργασία Πλοίου</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Raleway:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --navy:#0a1628; --deep-blue:#0d2137; --gold:#c9a84c; --gold-light:#e8c96d; --white:#f0f4f8; }
        body { background:linear-gradient(135deg,#0a1628 0%,#0d2137 50%,#0a2a4a 100%); min-height:100vh; font-family:'Raleway',sans-serif; color:var(--white); }
        body::before { content:''; position:fixed; inset:0; background:radial-gradient(ellipse at 20% 50%,rgba(26,74,107,0.3) 0%,transparent 50%),radial-gradient(ellipse at 80% 20%,rgba(201,168,76,0.06) 0%,transparent 40%); pointer-events:none; }
        .content-wrapper { position:relative; z-index:1; }
        .navbar-atlas { background:rgba(10,22,40,0.95); border-bottom:1px solid rgba(201,168,76,0.3); padding:12px 0; backdrop-filter:blur(10px); }
        .navbar-brand-atlas { display:flex; align-items:center; gap:12px; text-decoration:none; }
        .logo-img { width:55px; height:55px; border-radius:50%; border:2px solid var(--gold); object-fit:cover; box-shadow:0 0 15px rgba(201,168,76,0.4); }
        .brand-name { font-family:'Cinzel',serif; font-size:1.2rem; color:var(--gold); letter-spacing:3px; font-weight:700; }
        .brand-sub { font-size:.65rem; color:rgba(240,244,248,.6); letter-spacing:2px; text-transform:uppercase; }
        .nav-btn { background:transparent; border:1px solid rgba(201,168,76,.4); color:var(--white)!important; border-radius:4px; padding:6px 14px; font-size:.8rem; letter-spacing:1px; text-decoration:none; margin-left:8px; transition:.3s; }
        .nav-btn:hover,.nav-btn.active { background:var(--gold); color:var(--navy)!important; border-color:var(--gold); }
        .form-card { background:rgba(255,255,255,.03); border:1px solid rgba(201,168,76,.2); border-radius:16px; padding:36px; backdrop-filter:blur(10px); max-width:980px; margin:32px auto; }
        .section-title { font-family:'Cinzel',serif; font-size:1rem; letter-spacing:3px; color:var(--gold); text-transform:uppercase; border-bottom:1px solid rgba(201,168,76,.2); padding-bottom:10px; margin-bottom:22px; }
        .form-label { font-size:.74rem; letter-spacing:2px; text-transform:uppercase; color:rgba(240,244,248,.55); margin-bottom:6px; }
        .form-control,.form-select { background:rgba(255,255,255,.05)!important; border:1px solid rgba(201,168,76,.2)!important; color:var(--white)!important; border-radius:8px; padding:10px 14px; }
        .form-control:focus,.form-select:focus { border-color:var(--gold)!important; box-shadow:0 0 0 3px rgba(201,168,76,.15)!important; }
        .form-select option { background:#0d2137; color:var(--white); }
        .btn-atlas { background:var(--gold); color:var(--navy); border:none; border-radius:8px; padding:12px; font-family:'Cinzel',serif; letter-spacing:2px; font-weight:700; width:100%; }
        .btn-atlas:hover { background:var(--gold-light); }
        .btn-cancel { display:block; text-align:center; margin-top:10px; padding:12px; border-radius:8px; border:1px solid rgba(240,244,248,.25); color:rgba(240,244,248,.6); text-decoration:none; }
        .btn-cancel:hover { color:var(--white); border-color:rgba(240,244,248,.45); }
        .alert-danger-atlas { background:rgba(220,53,69,.2); color:#ffd1d7; border:1px solid rgba(220,53,69,.35); border-radius:8px; padding:10px 12px; margin-bottom:16px; }
        .atlas-footer { border-top:1px solid rgba(201,168,76,.15); padding:20px 0; text-align:center; font-size:.75rem; color:rgba(240,244,248,.3); letter-spacing:2px; margin-top:50px; }
        .atlas-footer span { color:var(--gold); }
        .status-section { border:1px solid rgba(201,168,76,.25); border-radius:12px; padding:20px; margin-top:24px; background:rgba(201,168,76,.04); }
        .status-section-title { font-size:.7rem; letter-spacing:2px; text-transform:uppercase; color:var(--gold); margin-bottom:14px; font-weight:600; }
        .status-btn { border:none; border-radius:8px; padding:10px 18px; font-size:.8rem; font-weight:700; letter-spacing:1px; cursor:pointer; transition:all .25s; flex:1; }
        .status-btn:hover { transform:translateY(-2px); box-shadow:0 6px 16px rgba(0,0,0,.3); }
        .status-btn.active-selected { outline:3px solid #fff; outline-offset:2px; }
        .btn-status-active    { background:rgba(25,135,84,.85);  color:#fff; }
        .btn-status-service   { background:rgba(255,193,7,.85);   color:#1a1a1a; }
        .btn-status-destroyed { background:rgba(220,53,69,.85);   color:#fff; }
        .btn-status-archived  { background:rgba(108,117,125,.7);  color:#fff; }
    </style>
</head>
<body>
<div class="content-wrapper">
    <nav class="navbar-atlas">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="index.php" class="navbar-brand-atlas">
                <img src="https://cdn.abacus.ai/images/2740de7f-a8ff-4f15-a162-2b34e6333d3f.png" class="logo-img" alt="Atlas Logo">
                <div>
                    <div class="brand-name">ATLAS GROUP</div>
                    <div class="brand-sub">Port Management System</div>
                </div>
            </a>
            <div>
                <a href="index.php" class="nav-btn">&#127968; &#913;&#961;&#967;&#953;&#954;&#942;</a>
                <a href="add_ship.php" class="nav-btn">&#10133; &#925;&#941;&#945; &#902;&#966;&#953;&#958;&#951;</a>
                <a href="search.php" class="nav-btn">&#128269; &#913;&#957;&#945;&#950;&#942;&#964;&#951;&#963;&#951;</a>
                <a href="calendar.php" class="nav-btn">&#128197; &#919;&#956;&#949;&#961;&#959;&#955;&#972;&#947;&#953;&#959;</a>
                <a href="statistics.php" class="nav-btn">&#128202; &#931;&#964;&#945;&#964;&#953;&#963;&#964;&#953;&#954;&#940;</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="form-card">
            <div class="section-title">&#9999;&#65039; &#917;&#960;&#949;&#958;&#949;&#961;&#947;&#945;&#963;&#943;&#945; &#928;&#955;&#959;&#943;&#959;&#965;</div>

            <?php if (!empty($error_message)): ?>
                <div class="alert-danger-atlas">&#931;&#966;&#940;&#955;&#956;&#945;: <?= safe_h($error_message) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">&#8199;&#927;&#957;&#959;&#956;&#945; &#928;&#955;&#959;&#943;&#959;&#965;</label>
                        <input type="text" name="ship_name" class="form-control" required
                               value="<?= safe_h($ship['ship_name']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">IMO Number</label>
                        <input type="text" name="imo_number" class="form-control"
                               value="<?= safe_h($ship['imo_number']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&#923;&#953;&#956;&#940;&#957;&#953;</label>
                        <input type="text" name="port" class="form-control" required
                               value="<?= safe_h($ship['port'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&#932;&#973;&#960;&#959;&#962; &#934;&#959;&#961;&#964;&#943;&#959;&#965;</label>
                        <select name="cargo_type" class="form-select">
                            <?php foreach ($types as $type): ?>
                                <option value="<?= safe_h($type) ?>"
                                    <?= (($ship['cargo_type'] ?? '') === $type) ? 'selected' : '' ?>>
                                    <?= safe_h($type) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&#919;&#956;&#949;&#961;&#959;&#956;&#951;&#957;&#943;&#945; / &#8199;&#937;&#961;&#945; &#902;&#966;&#953;&#958;&#951;&#962;</label>
                        <input type="datetime-local" name="arrival_time" class="form-control" required readonly
                               value="<?= safe_h(format_datetime_local_input($ship['arrival_time'] ?? null)) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&#919;&#956;&#949;&#961;&#959;&#956;&#951;&#957;&#943;&#945; / &#8199;&#937;&#961;&#945; &#913;&#957;&#945;&#967;&#974;&#961;&#951;&#963;&#951;&#962;</label>
                        <input type="datetime-local" name="departure_date" class="form-control" readonly
                               value="<?= safe_h(format_datetime_local_input($ship['departure_date'] ?? null)) ?>">
                    </div>
                </div>

                <div class="section-title mt-4">&#128176; PDA &#922;&#972;&#963;&#964;&#951;</div>
                <div class="row g-3">
                    <?php
                    $pda_fields = [
                        'port_charges'  => '&#923;&#953;&#956;&#949;&#957;&#953;&#954;&#940; &#964;&#941;&#955;&#951;',
                        'pilotage'      => '&#928;&#955;&#959;&#951;&#947;&#972;&#962;',
                        'towage'        => '&#929;&#965;&#956;&#959;&#965;&#955;&#954;&#940;',
                        'berth_dues'    => '&#920;&#941;&#963;&#951; &#960;&#961;&#972;&#963;&#948;&#949;&#963;&#951;&#962;',
                        'services'      => 'Line handling',
                        'garbage'       => '&#916;&#953;&#945;&#967;&#949;&#943;&#961;&#953;&#963;&#951; &#945;&#960;&#959;&#961;&#961;&#953;&#956;&#956;&#940;&#964;&#969;&#957;',
                        'crew_changes'  => '&#924;&#949;&#964;&#945;&#966;&#959;&#961;&#941;&#962; &#960;&#955;&#951;&#961;&#974;&#956;&#945;&#964;&#959;&#962;',
                        'cargo_ops'     => '&#934;&#959;&#961;&#964;&#959;&#949;&#954;&#966;&#972;&#961;&#964;&#969;&#963;&#951;',
                        'customs_docs'  => '&#932;&#949;&#955;&#969;&#957;&#949;&#943;&#945; / &#935;&#945;&#961;&#964;&#953;&#940;',
                        'agency_fee'    => '&#913;&#956;&#959;&#953;&#946;&#942; &#960;&#961;&#940;&#954;&#964;&#959;&#961;&#945;',
                    ];
                    foreach ($pda_fields as $fname => $flabel):
                    ?>
                    <div class="col-md-4">
                        <label class="form-label"><?= $flabel ?></label>
                        <input type="number" step="0.01" min="0" name="<?= $fname ?>" class="form-control"
                               value="<?= safe_h((string)($ship[$fname] ?? 0)) ?>">
                    </div>
                    <?php endforeach; ?>
                </div>

                <input type="hidden" name="status" id="status_input" value="<?= safe_h($ship['status'] ?? 'Active') ?>">
                <div class="status-section">
                    <div class="status-section-title">&#128678; &#922;&#945;&#964;&#940;&#963;&#964;&#945;&#963;&#951; &#928;&#955;&#959;&#943;&#959;&#965;</div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="status-btn btn-status-active <?= (($ship['status'] ?? 'Active') === 'Active') ? 'active-selected' : '' ?>"
                            onclick="setStatus('Active', this)">&#9989; Active</button>
                        <button type="button" class="status-btn btn-status-service <?= (($ship['status'] ?? '') === 'Service') ? 'active-selected' : '' ?>"
                            onclick="setStatus('Service', this)">&#128295; In Service</button>
                        <button type="button" class="status-btn btn-status-destroyed <?= (($ship['status'] ?? '') === 'Destroyed') ? 'active-selected' : '' ?>"
                            onclick="setStatus('Destroyed', this)">&#128165; Destroyed</button>
                        <button type="button" class="status-btn btn-status-archived <?= (($ship['status'] ?? '') === 'Archived') ? 'active-selected' : '' ?>"
                            onclick="setStatus('Archived', this)">&#128193; Archived</button>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn-atlas">&#128190; &#913;&#928;&#927;&#920;&#919;&#922;&#917;&#933;&#931;&#919; &#913;&#923;&#923;&#913;&#915;&#937;&#925;</button>
                    <a href="index.php" class="btn-cancel">&#10060; &#913;&#954;&#973;&#961;&#969;&#963;&#951;</a>
                </div>
            </form>
        </div>
    </div>

    <footer class="atlas-footer">
        <span>ATLAS GROUP</span> &middot; Port Management System &middot; &#928;&#945;&#957;&#949;&#960;&#953;&#963;&#964;&#942;&#956;&#953;&#959; &#916;&#965;&#964;&#953;&#954;&#942;&#962; &#913;&#964;&#964;&#953;&#954;&#942;&#962; &copy; 2026
    </footer>
</div>

<script>
function setStatus(val, btn) {
    document.getElementById('status_input').value = val;
    document.querySelectorAll('.status-btn').forEach(b => b.classList.remove('active-selected'));
    btn.classList.add('active-selected');
}
</script>
</body>
</html>
