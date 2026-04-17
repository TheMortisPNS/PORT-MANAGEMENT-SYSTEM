<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'db_connect.php';
include 'helpers.php';

$total = (int)($conn->query("SELECT COUNT(*) AS total FROM arrivals")->fetch_assoc()['total'] ?? 0);
$types = (int)($conn->query("SELECT COUNT(DISTINCT cargo_type) AS types FROM arrivals")->fetch_assoc()['types'] ?? 0);
$last = $conn->query("SELECT ship_name, arrival_time FROM arrivals ORDER BY COALESCE(arrival_time, departure_date) DESC LIMIT 1")->fetch_assoc();

$badge_map = [
    'Πετρέλαιο'    => 'badge-oil',
    'Containers'   => 'badge-container',
    'Επιβάτες'     => 'badge-passenger',
    'Χύδην Φορτίο' => 'badge-bulk',
    'Άλλο'         => 'badge-other'
];

$result = $conn->query("SELECT * FROM arrivals ORDER BY COALESCE(arrival_time, departure_date) DESC");
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATLAS GROUP | Port Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Raleway:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --navy:#0a1628; --deep-blue:#0d2137; --gold:#c9a84c; --gold-light:#e8c96d; --white:#f0f4f8; }
        body { background:linear-gradient(135deg,#0a1628 0%,#0d2137 50%,#0a2a4a 100%); min-height:100vh; font-family:'Raleway',sans-serif; color:var(--white); }
        body::before { content:''; position:fixed; inset:0; background:radial-gradient(ellipse at 20% 50%,rgba(26,74,107,0.3) 0%,transparent 50%),radial-gradient(ellipse at 80% 20%,rgba(201,168,76,0.06) 0%,transparent 40%); pointer-events:none; }
        .content-wrapper { position:relative; z-index:1; }
        .navbar-atlas { background:rgba(10,22,40,0.95); border-bottom:1px solid rgba(201,168,76,.3); padding:12px 0; backdrop-filter:blur(10px); }
        .navbar-brand-atlas { display:flex; align-items:center; gap:12px; text-decoration:none; }
        .logo-img { width:55px; height:55px; border-radius:50%; border:2px solid var(--gold); object-fit:cover; box-shadow:0 0 15px rgba(201,168,76,.4); }
        .brand-name { font-family:'Cinzel',serif; font-size:1.2rem; color:var(--gold); letter-spacing:3px; font-weight:700; }
        .brand-sub { font-size:.65rem; color:rgba(240,244,248,.6); letter-spacing:2px; text-transform:uppercase; }
        .nav-btn { background:transparent; border:1px solid rgba(201,168,76,.4); color:var(--white)!important; border-radius:4px; padding:6px 14px; font-size:.8rem; letter-spacing:1px; text-decoration:none; margin-left:8px; transition:.3s; }
        .nav-btn:hover,.nav-btn.active { background:var(--gold); color:var(--navy)!important; border-color:var(--gold); }
        .stat-card { background:rgba(255,255,255,.04); border:1px solid rgba(201,168,76,.2); border-radius:12px; padding:22px; position:relative; overflow:hidden; }
        .stat-card::before { content:''; position:absolute; left:0; top:0; width:3px; height:100%; background:var(--gold); }
        .stat-icon { font-size:1.8rem; }
        .stat-label { font-size:.7rem; letter-spacing:2px; color:rgba(240,244,248,.45); text-transform:uppercase; }
        .stat-value { font-family:'Cinzel',serif; font-size:2rem; color:var(--gold); }
        .section-title { font-family:'Cinzel',serif; font-size:1rem; letter-spacing:3px; color:var(--gold); border-bottom:1px solid rgba(201,168,76,.2); margin-bottom:14px; padding-bottom:10px; text-transform:uppercase; }
        .table-wrapper { background:rgba(255,255,255,.02); border:1px solid rgba(201,168,76,.15); border-radius:12px; padding:18px; }
        .atlas-table { width:100%; border-collapse:separate; border-spacing:0 6px; }
        .atlas-table thead th { font-size:.68rem; letter-spacing:1.8px; color:rgba(240,244,248,.5); text-transform:uppercase; padding:10px 12px; border-bottom:1px solid rgba(201,168,76,.2); }
        .atlas-table tbody tr { background:rgba(255,255,255,.03); }
        .atlas-table tbody tr:hover { background:rgba(201,168,76,.08); }
        .atlas-table td { padding:12px; border-top:1px solid rgba(255,255,255,.04); border-bottom:1px solid rgba(255,255,255,.04); font-size:.88rem; vertical-align:middle; }
        .atlas-table td:first-child { border-left:1px solid rgba(255,255,255,.04); border-radius:8px 0 0 8px; }
        .atlas-table td:last-child { border-right:1px solid rgba(255,255,255,.04); border-radius:0 8px 8px 0; }
        .cargo-badge,.trip-badge { padding:4px 10px; border-radius:20px; font-size:.72rem; font-weight:600; border:1px solid transparent; }
        .badge-oil { background:rgba(220,53,69,.2); color:#ff6b7a; border-color:rgba(220,53,69,.3); }
        .badge-container { background:rgba(13,110,253,.2); color:#6ea8fe; border-color:rgba(13,110,253,.3); }
        .badge-passenger { background:rgba(25,135,84,.2); color:#75b798; border-color:rgba(25,135,84,.3); }
        .badge-bulk { background:rgba(255,193,7,.2); color:#ffda6a; border-color:rgba(255,193,7,.3); }
        .badge-other { background:rgba(108,117,125,.2); color:#adb5bd; border-color:rgba(108,117,125,.3); }
        .badge-trip-arrival { background:rgba(25,135,84,.2); color:#8ee6b2; border-color:rgba(25,135,84,.4); }
        .badge-trip-departure { background:rgba(220,53,69,.2); color:#ff9aa6; border-color:rgba(220,53,69,.4); }
        .badge-trip-passage { background:rgba(13,110,253,.2); color:#9fc4ff; border-color:rgba(13,110,253,.4); }
        .badge-trip-stay { background:rgba(255,193,7,.2); color:#ffe38f; border-color:rgba(255,193,7,.4); }
        .badge-trip-default { background:rgba(108,117,125,.2); color:#adb5bd; border-color:rgba(108,117,125,.4); }
        .btn-edit,.btn-delete,.btn-pda { display:inline-block; text-decoration:none; font-size:.72rem; border-radius:4px; padding:5px 10px; margin:2px; }
        .btn-edit { border:1px solid rgba(201,168,76,.45); color:var(--gold); }
        .btn-edit:hover { background:var(--gold); color:var(--navy); }
        .btn-pda { border:1px solid rgba(13,110,253,.45); color:#8bb8ff; }
        .btn-pda:hover { background:rgba(13,110,253,.75); color:#fff; }
        .btn-delete { border:1px solid rgba(220,53,69,.45); color:#ff8d9a; }
        .btn-delete:hover { background:rgba(220,53,69,.8); color:#fff; }
        .atlas-alert { background:rgba(25,135,84,.15); border:1px solid rgba(25,135,84,.32); color:#94e6b7; border-radius:8px; padding:12px 16px; margin-bottom:15px; }
        .atlas-footer { border-top:1px solid rgba(201,168,76,.15); padding:20px 0; text-align:center; font-size:.75rem; color:rgba(240,244,248,.3); letter-spacing:2px; margin-top:50px; }
        .atlas-footer span { color:var(--gold); }
        .m-row { display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid rgba(255,255,255,.05); }
        .m-label { font-size:.68rem; letter-spacing:1.5px; color:rgba(240,244,248,.4); text-transform:uppercase; }
        .m-val { font-size:.88rem; color:var(--white); font-weight:600; text-align:right; }
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
                <a href="index.php" class="nav-btn active">&#127968; &#913;&#961;&#967;&#953;&#954;&#942;</a>
                <a href="add_ship.php" class="nav-btn">&#10133; &#925;&#941;&#945; &#902;&#966;&#953;&#958;&#951;</a>
                <a href="search.php" class="nav-btn">&#128269; &#913;&#957;&#945;&#950;&#942;&#964;&#951;&#963;&#951;</a>
                <a href="calendar.php" class="nav-btn">&#128197; &#919;&#956;&#949;&#961;&#959;&#955;&#972;&#947;&#953;&#959;</a>
                <a href="port_status.php" class="nav-btn">&#128506;&#65039; Port Status</a>
                <a href="statistics.php" class="nav-btn">&#128202; &#931;&#964;&#945;&#964;&#953;&#963;&#964;&#953;&#954;&#940;</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row g-3 mb-4">
            <div class="col-md-4"><div class="stat-card"><div class="stat-icon">&#128674;</div><div class="stat-label">&#931;&#965;&#957;&#959;&#955;&#953;&#954;&#941;&#962; &#917;&#947;&#947;&#961;&#945;&#966;&#941;&#962;</div><div class="stat-value"><?= $total ?></div></div></div>
            <div class="col-md-4"><div class="stat-card"><div class="stat-icon">&#128230;</div><div class="stat-label">&#932;&#973;&#960;&#959;&#953; &#934;&#959;&#961;&#964;&#943;&#959;&#965;</div><div class="stat-value"><?= $types ?></div></div></div>
            <div class="col-md-4"><div class="stat-card"><div class="stat-icon">&#128336;</div><div class="stat-label">&#932;&#949;&#955;&#949;&#965;&#964;&#945;&#943;&#945; &#922;&#943;&#957;&#951;&#963;&#951;</div><div style="font-weight:700;color:var(--gold-light);"><?= safe_h($last['ship_name'] ?? '&#8212;') ?></div><div style="font-size:.8rem;color:rgba(240,244,248,.5);"><?= format_datetime_greek($last['arrival_time'] ?? null) ?></div></div></div>
        </div>

        <?php if (isset($_GET['created'])): ?><div class="atlas-alert">&#9989; &#919; &#957;&#941;&#945; &#949;&#947;&#947;&#961;&#945;&#966;&#942; &#945;&#960;&#959;&#952;&#951;&#954;&#949;&#973;&#964;&#951;&#954;&#949;.</div><?php endif; ?>
        <?php if (isset($_GET['updated'])): ?><div class="atlas-alert">&#9989; &#919; &#949;&#947;&#947;&#961;&#945;&#966;&#942; &#949;&#957;&#951;&#956;&#949;&#961;&#974;&#952;&#951;&#954;&#949; &#949;&#960;&#953;&#964;&#965;&#967;&#974;&#962;.</div><?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?><div class="atlas-alert">&#9989; &#919; &#949;&#947;&#947;&#961;&#945;&#966;&#942; &#948;&#953;&#945;&#947;&#961;&#940;&#966;&#951;&#954;&#949; &#949;&#960;&#953;&#964;&#965;&#967;&#974;&#962;.</div><?php endif; ?>

        <div class="table-wrapper">
            <div class="section-title">&#9875; &#924;&#951;&#964;&#961;&#974;&#959; &#932;&#945;&#958;&#953;&#948;&#953;&#974;&#957;</div>
            <table class="atlas-table">
                <thead>
                <tr>
                    <th>#</th><th>&#928;&#955;&#959;&#943;&#959;</th><th>IMO</th><th>&#923;&#953;&#956;&#940;&#957;&#953;</th><th>&#934;&#959;&#961;&#964;&#943;&#959;</th><th>&#902;&#966;&#953;&#958;&#951;</th><th>&#913;&#957;&#945;&#967;&#974;&#961;&#951;&#963;&#951;</th><th>&#932;&#973;&#960;&#959;&#962;</th><th>&#916;&#953;&#940;&#961;&#954;&#949;&#953;&#945;</th><th>&#917;&#957;&#941;&#961;&#947;&#949;&#953;&#949;&#962;</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php
                        $badge    = $badge_map[$row['cargo_type']] ?? 'badge-other';
                        $tripType = calc_trip_type($row['arrival_time'] ?? null, $row['departure_date'] ?? null);
                        $duration = calc_duration_days($row['arrival_time'] ?? null, $row['departure_date'] ?? null);
                        $dur_str  = $duration !== null ? $duration . ' &#951;&#956;.' : '&#8212;';
                        ?>
                        <tr>
                            <td style="color:rgba(240,244,248,.45);"><?= (int)$row['id'] ?></td>
                            <td style="font-weight:600;">
                                <a href="#" class="ship-link"
                                   data-id="<?= (int)$row['id'] ?>"
                                   data-name="<?= safe_h($row['ship_name']) ?>"
                                   data-imo="<?= safe_h($row['imo_number']) ?>"
                                   data-cargo="<?= safe_h($row['cargo_type']) ?>"
                                   data-arrival="<?= safe_h(format_datetime_greek($row['arrival_time'] ?? null)) ?>"
                                   data-departure="<?= safe_h(format_datetime_greek($row['departure_date'] ?? null)) ?>"
                                   data-duration="<?= $dur_str ?>"
                                   data-port="<?= safe_h($row['port'] ?? '&#8212;') ?>"
                                   data-status="<?= safe_h($row['status'] ?? 'ANAMENO') ?>"
                                   data-notes="<?= safe_h($row['notes'] ?? '') ?>"
                                   style="text-decoration:none; color:inherit;">
                                    <?= safe_h($row['ship_name']) ?>
                                </a>
                            </td>
                            <td style="color:rgba(240,244,248,.6)"><?= safe_h($row['imo_number']) ?></td>
                            <td><?= safe_h($row['port'] ?? '&#8212;') ?></td>
                            <td><span class="cargo-badge <?= $badge ?>"><?= safe_h($row['cargo_type']) ?></span></td>
                            <td><?= format_datetime_greek($row['arrival_time'] ?? null) ?></td>
                            <td><?= format_datetime_greek($row['departure_date'] ?? null) ?></td>
                            <td><span class="trip-badge <?= trip_badge_class($tripType) ?>"><?= safe_h($tripType) ?></span></td>
                            <td><?= $dur_str ?></td>
                            <td>
                                <a class="btn-edit" href="edit_ship.php?id=<?= (int)$row['id'] ?>">&#9999;&#65039; Edit</a>
                                <a class="btn-pda" href="pda.php?id=<?= (int)$row['id'] ?>">&#128196; PDA</a>
                                <a class="btn-delete" href="delete.php?id=<?= (int)$row['id'] ?>" onclick="return confirm('Delete?')">&#128465;&#65039; Del</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="10" style="text-align:center;color:rgba(240,244,248,.45);padding:34px;">&#916;&#949;&#957; &#965;&#960;&#940;&#961;&#967;&#959;&#965;&#957; &#954;&#945;&#964;&#945;&#967;&#969;&#961;&#951;&#956;&#941;&#957;&#945; &#964;&#945;&#958;&#943;&#948;&#953;&#945;.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="atlas-footer"><span>ATLAS GROUP</span> &middot; Port Management System &middot; &#928;&#945;&#957;&#949;&#960;&#953;&#963;&#964;&#942;&#956;&#953;&#959; &#916;&#965;&#964;&#953;&#954;&#942;&#962; &#913;&#964;&#964;&#953;&#954;&#942;&#962; &copy; 2026</footer>
</div>

<div id="shipModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,.6); backdrop-filter:blur(4px);">
    <div id="shipPanel" style="position:absolute; right:0; top:0; height:100%; width:420px; max-width:95vw;
         background:linear-gradient(160deg,#0d1f35,#0a1628); border-left:1px solid rgba(201,168,76,.3);
         padding:30px 28px; overflow-y:auto; box-shadow:-10px 0 40px rgba(0,0,0,.5);">
        <button onclick="closeModal()" style="position:absolute;top:16px;right:18px;background:none;border:none;color:rgba(240,244,248,.5);font-size:1.4rem;cursor:pointer;">&#10005;</button>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
            <span style="font-size:1.5rem;">&#128674;</span>
            <div id="m-name" style="font-family:'Cinzel',serif;font-size:1.3rem;color:var(--gold);letter-spacing:2px;"></div>
        </div>
        <div style="font-size:.65rem;letter-spacing:3px;color:rgba(240,244,248,.4);text-transform:uppercase;margin-bottom:24px;">VESSEL DETAILS</div>
        <div style="display:flex;flex-direction:column;gap:0;">
            <div class="m-row"><span class="m-label">IMO NUMBER</span><span id="m-imo" class="m-val"></span></div>
            <div class="m-row"><span class="m-label">&#932;&#933;&#928;&#927;&#931; &#934;&#927;&#929;&#932;&#921;&#927;&#933;</span><span id="m-cargo" class="m-val"></span></div>
            <div class="m-row"><span class="m-label">&#9875; &#913;&#934;&#921;&#926;&#919;</span><span id="m-arrival" class="m-val"></span></div>
            <div class="m-row"><span class="m-label">&#913;&#925;&#913;&#935;&#937;&#929;&#919;&#931;&#919;</span><span id="m-departure" class="m-val"></span></div>
            <div class="m-row"><span class="m-label">&#928;&#913;&#929;&#913;&#924;&#927;&#925;&#919;</span><span id="m-duration" class="m-val"></span></div>
            <div class="m-row"><span class="m-label">&#923;&#921;&#924;&#913;&#925;&#921;</span><span id="m-port" class="m-val"></span></div>
            <div class="m-row">
                <span class="m-label">STATUS</span>
                <span id="m-status" style="border:1px solid var(--gold);color:var(--gold);padding:3px 12px;border-radius:20px;font-size:.75rem;font-weight:700;letter-spacing:1px;"></span>
            </div>
        </div>
        <div style="margin-top:24px;border-top:1px solid rgba(201,168,76,.15);padding-top:16px;">
            <div style="font-size:.65rem;letter-spacing:2px;color:rgba(240,244,248,.4);margin-bottom:8px;">&#931;&#935;&#927;&#923;&#921;&#913;</div>
            <div id="m-notes" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:12px;font-size:.85rem;color:rgba(240,244,248,.6);"></div>
        </div>
        <div style="margin-top:20px;display:flex;gap:8px;">
            <a id="m-edit-btn" href="#" class="btn-edit" style="flex:1;text-align:center;padding:10px;">&#9999;&#65039; Edit</a>
            <a id="m-pda-btn" href="#" class="btn-pda" style="flex:1;text-align:center;padding:10px;">&#128196; PDA</a>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.ship-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const d = this.dataset;
        document.getElementById('m-name').textContent      = d.name;
        document.getElementById('m-imo').textContent       = d.imo;
        document.getElementById('m-cargo').textContent     = d.cargo;
        document.getElementById('m-arrival').textContent   = d.arrival;
        document.getElementById('m-departure').textContent = d.departure;
        document.getElementById('m-duration').textContent  = d.duration;
        document.getElementById('m-port').textContent      = d.port;
        document.getElementById('m-status').textContent    = d.status;
        document.getElementById('m-notes').textContent     = d.notes;
        document.getElementById('m-edit-btn').href         = 'edit_ship.php?id=' + d.id;
        document.getElementById('m-pda-btn').href          = 'pda.php?id=' + d.id;
        document.getElementById('shipModal').style.display = 'block';
    });
});
function closeModal() {
    document.getElementById('shipModal').style.display = 'none';
}
document.getElementById('shipModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
</body>
</html>
<?php $conn->close(); ?>
