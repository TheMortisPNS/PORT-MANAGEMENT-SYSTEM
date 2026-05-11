<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'db_connect.php';
include 'helpers.php';

// Fallback utilities: only ορίζουμε αν δεν υπάρχουν ήδη (για να μην σπάσει το site αν helpers.php έχει άλλες υλοποιήσεις)
if (!function_exists('safe_h')) {
    function safe_h($v) { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('format_datetime_greek')) {
    function format_datetime_greek($dt) {
        if (!$dt) return '—';
        $t = strtotime($dt);
        if (!$t) return '—';
        return date('d/m/Y H:i', $t);
    }
}
if (!function_exists('trip_badge_class')) {
    function trip_badge_class($trip) {
        $map = [
            'Αφίξη' => 'badge-trip-arrival',
            'Αναχώρηση' => 'badge-trip-departure',
            'Παραμονή' => 'badge-trip-stay',
            'Διέλευση' => 'badge-trip-passage'
        ];
        return $map[$trip] ?? 'badge-trip-default';
    }
}
if (!function_exists('calc_trip_type')) {
    function calc_trip_type($status = null, $actual_arrival = null, $actual_departure = null) {
        // Normalize status small/large / translit
        $s = trim((string)($status ?? ''));
        // common mappings (Greek + some English fallbacks)
        $map = [
            'ΑΝΑΜΕΝΟΜΕΝΟ' => 'Αφίξη',
            'ANAMENO'     => 'Αφίξη',
            'ΣΤΟ ΛΙΜΑΝΙ'  => 'Παραμονή',
            'STO LIMANI'  => 'Παραμονή',
            'IN_PORT'     => 'Παραμονή',
            'ΑΝΑΧΩΡΗΣΕ'   => 'Αναχώρηση',
            'ANAXORHSE'   => 'Αναχώρηση',
            'DEPARTED'    => 'Αναχώρηση',
            'ACTIVE'      => 'Αφίξη',
            'ARCHIVED'    => 'Αρχείο'
        ];
        $s_up = mb_strtoupper($s, 'UTF-8');
        if ($s_up !== '') {
            foreach ($map as $k => $v) {
                if ($s_up === mb_strtoupper($k, 'UTF-8')) return $v;
            }
        }

        // Fallback based on timestamps
        if (!empty($actual_departure)) return 'Αναχώρηση';
        if (!empty($actual_arrival)) return 'Παραμονή';
        return 'Αφίξη';
    }
}
if (!function_exists('calc_duration_days')) {
    function calc_duration_days($start = null, $end = null) {
        if (!$start || !$end) return null;
        $s = strtotime($start);
        $e = strtotime($end);
        if (!$s || !$e || $e <= $s) return null;
        $days = floor(($e - $s) / 86400);
        return $days > 0 ? $days : 0;
    }
}
if (!function_exists('map_status_display')) {
    function map_status_display($status = null) {
        $s = trim((string)($status ?? ''));
        $s_up = mb_strtoupper($s, 'UTF-8');
        $map = [
            'ΑΝΑΜΕΝΟΜΕΝΟ' => 'ΑΝΑΜΕΝΟΜΕΝΟ',
            'ANAMENO'     => 'ΑΝΑΜΕΝΟΜΕΝΟ',
            'ΣΤΟ ΛΙΜΑΝΙ'  => 'ΣΤΟ ΛΙΜΑΝΙ',
            'IN_PORT'     => 'ΣΤΟ ΛΙΜΑΝΙ',
            'ΑΝΑΧΩΡΗΣΕ'   => 'ΑΝΑΧΩΡΗΣΕ',
            'DEPARTED'    => 'ΑΝΑΧΩΡΗΣΕ',
            'ACTIVE'      => 'ΕΝΕΡΓΟ',
            'ARCHIVED'    => 'ΑΡΧΕΙΟ'
        ];
        foreach ($map as $k => $v) {
            if ($s_up === mb_strtoupper($k, 'UTF-8')) return $v;
        }
        return $s !== '' ? $s : 'ANAMENO';
    }
}

// main queries
$total = (int)($conn->query("SELECT COUNT(*) AS total FROM arrivals")->fetch_assoc()['total'] ?? 0);
$types = (int)($conn->query("SELECT COUNT(DISTINCT cargo_type) AS types FROM arrivals")->fetch_assoc()['types'] ?? 0);
$last = $conn->query("SELECT ship_name, arrival_time FROM arrivals ORDER BY COALESCE(actual_arrival, arrival_time, actual_departure, departure_date) DESC LIMIT 1")->fetch_assoc();

$badge_map = [
    'Πετρέλαιο'    => 'badge-oil',
    'Containers'   => 'badge-container',
    'Επιβάτες'     => 'badge-passenger',
    'Χύδην Φορτίο' => 'badge-bulk',
    'Άλλο'         => 'badge-other'
];

$result = $conn->query("SELECT * FROM arrivals ORDER BY COALESCE(actual_arrival, arrival_time, actual_departure, departure_date) DESC");
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
                <a href="index.php" class="nav-btn active">🏠 Αρχική</a>
                <a href="add_ship.php" class="nav-btn">➕ Νέα Άφιξη</a>
                <a href="search.php" class="nav-btn">🔍 Αναζήτηση</a>
                <a href="calendar.php" class="nav-btn">📅 Ημερολόγιο</a>
                <a href="port_status.php" class="nav-btn">🛰️ Port Status</a>
                <a href="statistics.php" class="nav-btn">📊 Στατιστικά</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row g-3 mb-4">
            <div class="col-md-4"><div class="stat-card"><div class="stat-icon">🧭</div><div class="stat-label">Καταχωρημένες Αφίξεις</div><div class="stat-value"><?= $total ?></div></div></div>
            <div class="col-md-4"><div class="stat-card"><div class="stat-icon">📦</div><div class="stat-label">Τύποι Φορτίου</div><div class="stat-value"><?= $types ?></div></div></div>
            <div class="col-md-4"><div class="stat-card"><div class="stat-icon">🕒</div><div class="stat-label">Τελευταίο Πλοίο</div><div style="font-weight:700;color:var(--gold-light);"><?= safe_h($last['ship_name'] ?? '—') ?></div><div style="font-size:.8rem;color:rgba(240,244,248,.5);"><?= format_datetime_greek($last['arrival_time'] ?? null) ?></div></div></div>
        </div>

        <?php if (isset($_GET['created'])): ?><div class="atlas-alert">✅ Η εγγραφή δημιουργήθηκε επιτυχώς.</div><?php endif; ?>
        <?php if (isset($_GET['updated'])): ?><div class="atlas-alert">✅ Η εγγραφή ενημερώθηκε επιτυχώς.</div><?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?><div class="atlas-alert">✅ Η εγγραφή διαγράφηκε επιτυχώς.</div><?php endif; ?>

        <div class="table-wrapper">
            <div class="section-title">⚓ Καταχωρησεις Πλοίων</div>
            <table class="atlas-table">
                <thead>
                <tr>
                    <th>#</th><th>Πλοίο</th><th>IMO</th><th>Λιμάνι</th><th>Φορτίο</th><th>Άφιξη</th><th>Αναχώρηση</th><th>Κατάσταση</th><th>Διάρκεια</th><th>Ενέργειες</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php
                        $badge    = $badge_map[$row['cargo_type']] ?? 'badge-other';

                        // Use actual timestamps if present, otherwise planned ones
                        $display_arrival_ts  = $row['actual_arrival'] ?? $row['arrival_time'] ?? null;
                        $display_depart_ts   = $row['actual_departure'] ?? $row['departure_date'] ?? null;

                        // compute trip type using status and actual timestamps (prevents "stuck" type)
                        $tripType = calc_trip_type($row['status'] ?? null, $row['actual_arrival'] ?? null, $row['actual_departure'] ?? null);

                        // duration in days (fallback to actual timestamps then planned)
                        $duration_days = calc_duration_days($row['actual_arrival'] ?? $row['arrival_time'] ?? null, $row['actual_departure'] ?? $row['departure_date'] ?? null);
                        $dur_str  = $duration_days !== null ? ($duration_days . ' ημ.') : '—';

                        // port display
                        $port_raw = trim((string)($row['port'] ?? ''));
                        $port_display = $port_raw !== '' ? $port_raw : '—';

                        // notes field: prefer internal_notes if present
                        $notes_field = $row['internal_notes'] ?? $row['notes'] ?? '';
                        $status_display = map_status_display($row['status'] ?? null);
                        $statusBadgeClass = trip_badge_class($tripType);
                        if ($status_display === 'ΑΝΑΧΩΡΗΣΕ') { $statusBadgeClass = 'badge-trip-departure'; }
                        elseif ($status_display === 'ΣΤΟ ΛΙΜΑΝΙ') { $statusBadgeClass = 'badge-trip-stay'; }
                        elseif ($status_display === 'ΑΝΑΜΕΝΟΜΕΝΟ') { $statusBadgeClass = 'badge-trip-arrival'; }
                        elseif ($status_display === 'ΑΡΧΕΙΟ') { $statusBadgeClass = 'badge-trip-default'; }
                        ?>
                        <tr>
                            <td style="color:rgba(240,244,248,.45);"><?= (int)$row['id'] ?></td>
                            <td style="font-weight:600;">
                                <a href="#" class="ship-link"
                                   data-id="<?php echo (int)$row['id']; ?>"
                                   data-name="<?php echo htmlspecialchars($row['ship_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                   data-imo="<?php echo htmlspecialchars($row['imo_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                   data-cargo="<?php echo htmlspecialchars($row['cargo_type'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                   data-arrival="<?php echo htmlspecialchars(format_datetime_greek($display_arrival_ts), ENT_QUOTES, 'UTF-8'); ?>"
                                   data-departure="<?php echo htmlspecialchars(format_datetime_greek($display_depart_ts), ENT_QUOTES, 'UTF-8'); ?>"
                                   data-duration="<?php echo htmlspecialchars($dur_str, ENT_QUOTES, 'UTF-8'); ?>"
                                   data-port="<?php echo htmlspecialchars($port_display, ENT_QUOTES, 'UTF-8'); ?>"
                                   data-status="<?php echo htmlspecialchars($status_display, ENT_QUOTES, 'UTF-8'); ?>"
                                   data-notes="<?php echo htmlspecialchars($notes_field, ENT_QUOTES, 'UTF-8'); ?>"
                                   style="text-decoration:none; color:inherit;">
                                    <?= safe_h($row['ship_name']) ?>
                                </a>
                            </td>
                            <td style="color:rgba(240,244,248,.6)"><?= safe_h($row['imo_number']) ?></td>
                            <td><?= safe_h($port_display) ?></td>
                            <td><span class="cargo-badge <?= $badge ?>"><?= safe_h($row['cargo_type']) ?></span></td>
                            <td><?= format_datetime_greek($display_arrival_ts) ?></td>
                            <td><?= format_datetime_greek($display_depart_ts) ?></td>
                            <td><span class="trip-badge <?= $statusBadgeClass ?>"><?= safe_h($status_display) ?></span></td>
                            <td><?= safe_h($dur_str) ?></td>
                            <td>
                                <a class="btn-edit" href="edit_ship.php?id=<?= (int)$row['id'] ?>">✏️ Edit</a>
                                <a class="btn-pda" href="pda.php?id=<?= (int)$row['id'] ?>">📄 PDA</a>
                                <a class="btn-delete" href="delete.php?id=<?= (int)$row['id'] ?>" onclick="return confirm('Delete?')">🗑️ Del</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="10" style="text-align:center;color:rgba(240,244,248,.45);padding:34px;">Δεν υπάρχουν καταχωρημένα ταξίδια.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="atlas-footer"><span>ATLAS GROUP</span> · Port Management System · Πανεπιστήμιο Δυτικής Αττικής © 2026</footer>
</div>

<!-- Modal -->
<div id="shipModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,.6); backdrop-filter:blur(4px);">
    <div id="shipPanel" style="position:absolute; right:0; top:0; height:100%; width:420px; max-width:95vw;
         background:linear-gradient(160deg,#0d1f35,#0a1628); border-left:1px solid rgba(201,168,76,.3);
         padding:30px 28px; overflow-y:auto; box-shadow:-10px 0 40px rgba(0,0,0,.5);">
        <button onclick="closeModal()" style="position:absolute;top:16px;right:18px;background:none;border:none;color:rgba(240,244,248,.5);font-size:1.4rem;cursor:pointer;">✕</button>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
            <span style="font-size:1.5rem;">🚢</span>
            <div id="m-name" style="font-family:'Cinzel',serif;font-size:1.3rem;color:var(--gold);letter-spacing:2px;"></div>
        </div>
        <div style="font-size:.65rem;letter-spacing:3px;color:rgba(240,244,248,.4);text-transform:uppercase;margin-bottom:24px;">VESSEL DETAILS</div>
        <div style="display:flex;flex-direction:column;gap:0;">
            <div class="m-row"><span class="m-label">IMO NUMBER</span><span id="m-imo" class="m-val"></span></div>
            <div class="m-row"><span class="m-label">ΤΥΠΟΣ ΦΟΡΤΙΟΥ</span><span id="m-cargo" class="m-val"></span></div>
            <div class="m-row"><span class="m-label">⚓ ΑΦΙΞΗ</span><span id="m-arrival" class="m-val"></span></div>
            <div class="m-row"><span class="m-label">ΑΝΑΧΩΡΗΣΗ</span><span id="m-departure" class="m-val"></span></div>
            <div class="m-row"><span class="m-label">ΠΑΡΑΜΟΝΗ</span><span id="m-duration" class="m-val"></span></div>
            <div class="m-row"><span class="m-label">ΛΙΜΑΝΙ</span><span id="m-port" class="m-val"></span></div>
            <div class="m-row">
                <span class="m-label">STATUS</span>
                <span id="m-status" style="border:1px solid var(--gold);color:var(--gold);padding:3px 12px;border-radius:20px;font-size:.75rem;font-weight:700;letter-spacing:1px;"></span>
            </div>
        </div>
        <div style="margin-top:24px;border-top:1px solid rgba(201,168,76,.15);padding-top:16px;">
            <div style="font-size:.65rem;letter-spacing:2px;color:rgba(240,244,248,.4);margin-bottom:8px;">Σημειώσεις</div>
            <div id="m-notes" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:12px;font-size:.85rem;color:rgba(240,244,248,.6);"></div>
        </div>
        <div style="margin-top:20px;display:flex;gap:8px;">
            <a id="m-edit-btn" href="#" class="btn-edit" style="flex:1;text-align:center;padding:10px;">✏️ Edit</a>
            <a id="m-pda-btn" href="#" class="btn-pda" style="flex:1;text-align:center;padding:10px;">📄 PDA</a>
        </div>
    </div>
</div>

<script>
function setModalText(id, value, fallback = '—') {
    const el = document.getElementById(id);
    if (!el) return;
    const clean = (value || '').toString().trim();
    el.textContent = clean !== '' ? clean : fallback;
}

// when clicking a ship row link, populate modal
document.addEventListener('click', function(e) {
    const link = e.target.closest && e.target.closest('.ship-link');
    if (!link) return;

    e.preventDefault();

    setModalText('m-name', link.getAttribute('data-name'));
    setModalText('m-imo', link.getAttribute('data-imo'));
    setModalText('m-cargo', link.getAttribute('data-cargo'));
    setModalText('m-arrival', link.getAttribute('data-arrival'));
    setModalText('m-departure', link.getAttribute('data-departure'));
    setModalText('m-duration', link.getAttribute('data-duration'));
    setModalText('m-port', link.getAttribute('data-port'));
    setModalText('m-status', link.getAttribute('data-status'), 'ANAMENO');

    const notes = (link.getAttribute('data-notes') || '').trim();
    document.getElementById('m-notes').textContent = notes;

    const id = link.getAttribute('data-id') || '';
    document.getElementById('m-edit-btn').href = 'edit_ship.php?id=' + encodeURIComponent(id);
    document.getElementById('m-pda-btn').href  = 'pda.php?id=' + encodeURIComponent(id);

    document.getElementById('shipModal').style.display = 'block';
});

function closeModal() {
    document.getElementById('shipModal').style.display = 'none';
}

document.getElementById('shipModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// helper to format server datetime (YYYY-MM-DD HH:MM:SS) to local greek format
function formatDTLocal(iso) {
    if (!iso) return '—';
    const s = iso.replace(' ', 'T');
    const d = new Date(s);
    if (isNaN(d)) return iso;
    return d.toLocaleDateString('el-GR') + ' ' + d.toLocaleTimeString('el-GR',{hour:'2-digit',minute:'2-digit'});
}

// apply update to the row and modal
function applyUpdateToRow(id, data) {
    const rowLink = document.querySelector('.ship-link[data-id="' + id + '"]');
    if (!rowLink) return;

    if (data.actual_arrival) {
        const a = formatDTLocal(data.actual_arrival);
        rowLink.setAttribute('data-arrival', a);
        const tr = rowLink.closest('tr');
        if (tr && tr.cells[5]) tr.cells[5].textContent = a; // Άφιξη
    }
    if (data.actual_departure) {
        const d = formatDTLocal(data.actual_departure);
        rowLink.setAttribute('data-departure', d);
        const tr = rowLink.closest('tr');
        if (tr && tr.cells[6]) tr.cells[6].textContent = d; // Αναχώρηση
    }
    if (data.status) {
        const human = (data.status === 'ΣΤΟ ΛΙΜΑΝΙ') ? 'Παραμονή' : (data.status === 'ΑΝΑΧΩΡΗΣΕ' ? 'Αναχώρηση' : data.status);
        rowLink.setAttribute('data-status', human);
        const tr = rowLink.closest('tr');
        if (tr && tr.cells[7]) {
            const badgeEl = tr.cells[7].querySelector('.trip-badge');
            if (badgeEl) {
                badgeEl.textContent = human;
                badgeEl.className = 'trip-badge ' + (human === 'Παραμονή' ? 'badge-trip-stay' : (human === 'Αναχώρηση' ? 'badge-trip-departure' : 'badge-trip-default'));
            }
        }
        const modalEditHref = document.getElementById('m-edit-btn')?.href || '';
        if (modalEditHref.indexOf('id=' + encodeURIComponent(id)) !== -1) {
            document.getElementById('m-status').textContent = human;
        }
    }
}

// perform update + broadcast to other tabs
function updateStatusAndBroadcast(id, action) {
    if (!confirm(action === 'arrived' ? 'Μαρκάρω ως άφιξη;' : 'Μαρκάρω ως αναχώρηση;')) return;
    fetch('update_status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + encodeURIComponent(id) + '&action=' + encodeURIComponent(action)
    }).then(r => r.json()).then(data => {
        if (!data.success) { alert('Σφάλμα: ' + (data.error || 'update failed')); return; }
        applyUpdateToRow(id, data);
        const msg = { id: id, status: data.status || null, actual_arrival: data.actual_arrival || null, actual_departure: data.actual_departure || null, ts: Date.now() };
        try { localStorage.setItem('ship_update_' + id, JSON.stringify(msg)); } catch(e) { console.warn('broadcast failed', e); }
    }).catch(err => { console.error(err); alert('Network error'); });
}

// listen for broadcasts from other tabs
window.addEventListener('storage', function(e) {
    if (!e.key) return;
    if (!e.key.startsWith('ship_update_')) return;
    try {
        const data = JSON.parse(e.newValue);
        if (!data || !data.id) return;
        applyUpdateToRow(data.id, data);
    } catch (err) {
        console.error('storage parse error', err);
    }
});
</script>
</body>
</html>
<?php $conn->close(); ?>
 
