<?php include 'db_connect.php'; ?>
<?php
$today        = date('Y-m-d');
$selected_date = isset($_GET['date']) ? $_GET['date'] : $today;
$is_today      = ($selected_date === $today);

$prev_date = date('Y-m-d', strtotime($selected_date . ' -1 day'));
$next_date = date('Y-m-d', strtotime($selected_date . ' +1 day'));

$expected  = $conn->query("SELECT * FROM arrivals WHERE status='ΑΝΑΜΕΝΟΜΕΝΟ' AND DATE(arrival_time) = '$selected_date' ORDER BY arrival_time ASC");
$inport    = $conn->query("SELECT * FROM arrivals WHERE status='ΣΤΟ ΛΙΜΑΝΙ' ORDER BY actual_arrival DESC");
$departed  = $conn->query("SELECT * FROM arrivals WHERE status='ΑΝΑΧΩΡΗΣΕ' AND DATE(actual_departure) = '$selected_date' ORDER BY actual_departure DESC");
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATLAS GROUP | Port Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Raleway:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --navy:#0a1628; --deep-blue:#0d2137; --gold:#c9a84c; --gold-light:#e8c96d; --white:#f0f4f8; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { background:linear-gradient(135deg,#0a1628 0%,#0d2137 50%,#0a2a4a 100%); min-height:100vh; font-family:'Raleway',sans-serif; color:var(--white); }
        body::before { content:''; position:fixed; top:0; left:0; width:100%; height:100%; background:radial-gradient(ellipse at 20% 50%,rgba(26,74,107,0.3) 0%,transparent 50%),radial-gradient(ellipse at 80% 20%,rgba(201,168,76,0.05) 0%,transparent 40%); pointer-events:none; z-index:0; }
        .content-wrapper { position:relative; z-index:1; }
        .navbar-atlas { background:rgba(10,22,40,0.95); backdrop-filter:blur(10px); border-bottom:1px solid rgba(201,168,76,0.3); padding:12px 0; }
        .navbar-brand-atlas { display:flex; align-items:center; gap:12px; text-decoration:none; }
        .logo-img { width:55px; height:55px; border-radius:50%; border:2px solid var(--gold); object-fit:cover; box-shadow:0 0 15px rgba(201,168,76,0.4); }
        .brand-name { font-family:'Cinzel',serif; font-size:1.2rem; color:var(--gold); letter-spacing:3px; font-weight:700; line-height:1.1; }
        .brand-sub { font-size:0.65rem; color:rgba(240,244,248,0.6); letter-spacing:2px; text-transform:uppercase; }
        .nav-btn { background:transparent; border:1px solid rgba(201,168,76,0.4); color:var(--white)!important; border-radius:4px; padding:6px 14px; font-size:0.8rem; letter-spacing:1px; transition:all 0.3s; text-decoration:none; margin-left:8px; }
        .nav-btn:hover, .nav-btn.active { background:var(--gold); color:var(--navy)!important; border-color:var(--gold); }
        .page-header { padding:30px 0 20px; }
        .page-title { font-family:'Cinzel',serif; font-size:1.6rem; color:var(--gold); letter-spacing:4px; }
        .page-subtitle { font-size:0.75rem; color:rgba(240,244,248,0.4); letter-spacing:2px; margin-top:4px; }
        .live-badge { display:inline-flex; align-items:center; gap:6px; background:rgba(25,135,84,0.2); border:1px solid rgba(25,135,84,0.4); border-radius:20px; padding:4px 12px; font-size:0.7rem; color:#75b798; letter-spacing:1px; }
        .live-dot { width:7px; height:7px; border-radius:50%; background:#75b798; animation:pulse 1.5s infinite; }
        @keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:0.3;} }
        .status-col { background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.07); border-radius:14px; padding:0; overflow:hidden; height:fit-content; }
        .col-header { padding:16px 20px; display:flex; align-items:center; justify-content:space-between; }
        .col-header.expected { background:rgba(255,193,7,0.1); border-bottom:2px solid rgba(255,193,7,0.4); }
        .col-header.inport   { background:rgba(25,135,84,0.1); border-bottom:2px solid rgba(25,135,84,0.4); }
        .col-header.departed { background:rgba(108,117,125,0.1); border-bottom:2px solid rgba(108,117,125,0.3); }
        .col-title { font-family:'Cinzel',serif; font-size:0.85rem; letter-spacing:2px; }
        .col-title.expected { color:#ffda6a; }
        .col-title.inport   { color:#75b798; }
        .col-title.departed { color:#adb5bd; }
        .col-count { border-radius:50%; width:24px; height:24px; font-size:0.7rem; font-weight:700; display:flex; align-items:center; justify-content:center; }
        .col-count.expected { background:rgba(255,193,7,0.3); color:#ffda6a; }
        .col-count.inport   { background:rgba(25,135,84,0.3); color:#75b798; }
        .col-count.departed { background:rgba(108,117,125,0.3); color:#adb5bd; }
        .col-body { padding:12px; display:flex; flex-direction:column; gap:10px; }
        .ship-card { background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08); border-radius:10px; padding:14px; transition:all 0.2s; cursor:pointer; }
        .ship-card:hover { background:rgba(255,255,255,0.06); border-color:rgba(201,168,76,0.2); }
        .ship-card.expected-card { border-left:3px solid #ffda6a; }
        .ship-card.inport-card   { border-left:3px solid #75b798; }
        .ship-card.departed-card { border-left:3px solid #adb5bd; opacity:0.75; }
        .ship-name { font-weight:700; font-size:0.9rem; color:var(--white); margin-bottom:3px; }
        .ship-imo  { font-size:0.7rem; color:rgba(240,244,248,0.4); letter-spacing:1px; }
        .ship-meta { display:flex; gap:8px; flex-wrap:wrap; margin:8px 0; }
        .meta-tag  { font-size:0.65rem; padding:2px 8px; border-radius:4px; font-weight:600; letter-spacing:0.5px; }
        .meta-tag.cargo   { background:rgba(13,110,253,0.2); color:#6ea8fe; border:1px solid rgba(13,110,253,0.3); }
        .meta-tag.time    { background:rgba(201,168,76,0.15); color:#ffda6a; border:1px solid rgba(201,168,76,0.3); }
        .meta-tag.actual  { background:rgba(25,135,84,0.2); color:#75b798; border:1px solid rgba(25,135,84,0.3); }
        .meta-tag.depart  { background:rgba(108,117,125,0.2); color:#adb5bd; border:1px solid rgba(108,117,125,0.3); }
        .btn-arrived  { background:rgba(25,135,84,0.2); border:1px solid rgba(25,135,84,0.5); color:#75b798; border-radius:6px; padding:6px 14px; font-size:0.75rem; font-weight:600; cursor:pointer; transition:all 0.2s; width:100%; margin-top:8px; letter-spacing:0.5px; }
        .btn-arrived:hover  { background:rgba(25,135,84,0.4); color:#fff; }
        .btn-departed { background:rgba(108,117,125,0.2); border:1px solid rgba(108,117,125,0.5); color:#adb5bd; border-radius:6px; padding:6px 14px; font-size:0.75rem; font-weight:600; cursor:pointer; transition:all 0.2s; width:100%; margin-top:8px; letter-spacing:0.5px; }
        .btn-departed:hover { background:rgba(108,117,125,0.4); color:#fff; }
        .empty-state { text-align:center; padding:30px 20px; color:rgba(240,244,248,0.2); font-size:0.8rem; letter-spacing:1px; }
        .atlas-footer { border-top:1px solid rgba(201,168,76,0.15); padding:20px 0; text-align:center; font-size:0.75rem; color:rgba(240,244,248,0.3); letter-spacing:2px; margin-top:40px; }
        .atlas-footer span { color:var(--gold); }
        .date-nav-bar { display:flex; align-items:center; justify-content:center; gap:12px; background:rgba(255,255,255,0.03); border:1px solid rgba(201,168,76,0.2); border-radius:12px; padding:12px 20px; margin-bottom:24px; }
        .date-nav-btn { background:transparent; border:1px solid rgba(201,168,76,0.4); color:var(--gold); border-radius:8px; padding:7px 18px; font-size:0.85rem; text-decoration:none; transition:all 0.3s; }
        .date-nav-btn:hover { background:var(--gold); color:var(--navy); }
        .date-nav-today { background:rgba(201,168,76,0.15); border:1px solid rgba(201,168,76,0.5); color:var(--gold); border-radius:8px; padding:7px 18px; font-size:0.85rem; text-decoration:none; transition:all 0.3s; }
        .date-nav-today:hover { background:var(--gold); color:var(--navy); }
        .date-display { font-family:'Cinzel',serif; font-size:1rem; color:var(--white); letter-spacing:2px; min-width:180px; text-align:center; }
        .date-display.is-today { color:var(--gold); }
        .date-input-wrap input[type="date"] { background:rgba(0,0,0,0.3); border:1px solid rgba(201,168,76,0.3); color:var(--white); border-radius:8px; padding:6px 12px; font-size:0.8rem; cursor:pointer; }
        .date-input-wrap input[type="date"]::-webkit-calendar-picker-indicator { filter:invert(1) opacity(0.5); cursor:pointer; }
        /* CARD NOTES READ-ONLY */
        .card-notes-readonly { margin-top:10px; border-top:1px solid rgba(255,255,255,0.06); padding-top:8px; }
        .card-notes-readonly-label { font-size:0.6rem; letter-spacing:1.5px; color:rgba(240,244,248,0.3); text-transform:uppercase; margin-bottom:4px; }
        .card-notes-readonly-text { font-size:0.72rem; color:rgba(240,244,248,0.5); font-style:italic; background:rgba(0,0,0,0.15); border-radius:6px; padding:6px 10px; min-height:30px; border:1px solid rgba(255,255,255,0.05); }
        /* MODAL */
        .ship-modal-overlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center; }
        .ship-modal-overlay.active { display:flex; }
        .ship-modal { background:linear-gradient(135deg,#0d2137,#0a1628); border:1px solid rgba(201,168,76,0.4); border-radius:16px; padding:30px; width:90%; max-width:480px; position:relative; box-shadow:0 20px 60px rgba(0,0,0,0.6); animation:modalIn 0.25s ease; max-height:90vh; overflow-y:auto; }
        @keyframes modalIn { from{opacity:0;transform:scale(0.9);} to{opacity:1;transform:scale(1);} }
        .modal-close { position:absolute; top:14px; right:18px; background:transparent; border:none; color:rgba(240,244,248,0.4); font-size:1.4rem; cursor:pointer; transition:color 0.2s; }
        .modal-close:hover { color:var(--gold); }
        .modal-ship-name { font-family:'Cinzel',serif; font-size:1.3rem; color:var(--gold); margin-bottom:4px; }
        .modal-divider { border:none; border-top:1px solid rgba(201,168,76,0.2); margin:14px 0; }
        .modal-row { display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid rgba(255,255,255,0.04); font-size:0.85rem; }
        .modal-row:last-child { border-bottom:none; }
        .modal-label { color:rgba(240,244,248,0.4); font-size:0.72rem; letter-spacing:1.5px; text-transform:uppercase; }
        .modal-value { color:var(--white); font-weight:600; text-align:right; }
        .modal-status-badge { padding:4px 14px; border-radius:20px; font-size:0.75rem; font-weight:700; letter-spacing:1px; }
        .status-expected { background:rgba(255,193,7,0.2); color:#ffda6a; border:1px solid rgba(255,193,7,0.4); }
        .status-inport   { background:rgba(25,135,84,0.2);  color:#75b798; border:1px solid rgba(25,135,84,0.4); }
        .status-departed { background:rgba(108,117,125,0.2);color:#adb5bd; border:1px solid rgba(108,117,125,0.4); }
        .modal-notes-label { font-size:0.65rem; letter-spacing:1.5px; color:rgba(240,244,248,0.3); text-transform:uppercase; margin-bottom:6px; }
        /* MODAL NOTES EDITABLE */
        .modal-notes-textarea { width:100%; background:rgba(0,0,0,0.25); border:1px solid rgba(201,168,76,0.3); border-radius:8px; color:var(--white); font-size:0.82rem; padding:10px 12px; resize:vertical; font-family:'Raleway',sans-serif; transition:border-color 0.2s; min-height:80px; margin-top:4px; }
        .modal-notes-textarea:focus { outline:none; border-color:rgba(201,168,76,0.7); background:rgba(0,0,0,0.35); }
        .modal-btn-save { background:rgba(201,168,76,0.2); border:1px solid rgba(201,168,76,0.5); color:var(--gold); border-radius:6px; padding:7px 20px; font-size:0.78rem; cursor:pointer; transition:all 0.2s; margin-top:10px; letter-spacing:0.5px; font-weight:700; }
        .modal-btn-save:hover { background:rgba(201,168,76,0.45); color:#fff; }
        .modal-save-feedback { font-size:0.68rem; color:#75b798; margin-left:10px; display:none; font-weight:600; }
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
                <a href="index.php" class="nav-btn">🏠 Αρχική</a>
                <a href="add_ship.php" class="nav-btn">➕ Νέα Άφιξη</a>
                <a href="search.php" class="nav-btn">🔍 Αναζήτηση</a>
                <a href="calendar.php" class="nav-btn">📅 Ημερολόγιο</a>
                <a href="port_status.php" class="nav-btn active">🚢 Port Status</a>
                <a href="statistics.php" class="nav-btn">📊 Στατιστικά</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header d-flex justify-content-between align-items-start">
            <div>
                <div class="page-title">🗺️ PORT STATUS BOARD</div>
                <div class="page-subtitle">REAL-TIME VESSEL TRACKING · <?= date('d/m/Y', strtotime($selected_date)) ?></div>
            </div>
            <div class="live-badge">
                <div class="live-dot"></div>
                <?= $is_today ? 'LIVE · ' . date('H:i') : date('d/m/Y', strtotime($selected_date)) ?>
            </div>
        </div>

        <div class="date-nav-bar">
            <a href="port_status.php?date=<?= $prev_date ?>" class="date-nav-btn">&#8592; Προηγ.</a>
            <div class="date-display <?= $is_today ? 'is-today' : '' ?>">
                <?php
                $day_names = ['Monday'=>'Δευτέρα','Tuesday'=>'Τρίτη','Wednesday'=>'Τετάρτη','Thursday'=>'Πέμπτη','Friday'=>'Παρασκευή','Saturday'=>'Σάββατο','Sunday'=>'Κυριακή'];
                $month_names_gr = [1=>'Ιαν',2=>'Φεβ',3=>'Μαρ',4=>'Απρ',5=>'Μαΐ',6=>'Ιουν',7=>'Ιουλ',8=>'Αυγ',9=>'Σεπ',10=>'Οκτ',11=>'Νοε',12=>'Δεκ'];
                $dn = $day_names[date('l', strtotime($selected_date))];
                $mn = $month_names_gr[intval(date('n', strtotime($selected_date)))];
                echo $dn . ', ' . date('d', strtotime($selected_date)) . ' ' . $mn . ' ' . date('Y', strtotime($selected_date));
                if ($is_today) echo ' <span style="font-size:0.6rem;color:rgba(201,168,76,0.6);letter-spacing:2px;">(ΣΗΜΕΡΑ)</span>';
                ?>
            </div>
            <a href="port_status.php?date=<?= $next_date ?>" class="date-nav-btn">Επόμ. &#8594;</a>
            <?php if (!$is_today): ?>
            <a href="port_status.php" class="date-nav-today">&#128197; Σήμερα</a>
            <?php endif; ?>
            <div class="date-input-wrap">
                <input type="date" value="<​?= $selected_date ?>" onchange="window.location='port_status.php?date='+this.value">
            </div>
        </div>

        <div class="row g-4">

            <!-- ΑΝΑΜΕΝΟΜΕΝΑ -->
            <div class="col-md-4">
                <div class="status-col">
                    <div class="col-header expected">
                        <div class="col-title expected">⏳ ΑΝΑΜΕΝΟΜΕΝΑ</div>
                        <div class="col-count expected"><?= $expected->num_rows ?></div>
                    </div>
                    <div class="col-body">
                        <?php if ($expected->num_rows === 0): ?>
                            <div class="empty-state">Δεν υπάρχουν<br>αναμενόμενες αφίξεις</div>
                        <?php endif; ?>
                        <?php while ($s = $expected->fetch_assoc()): ?>
                        <div class="ship-card expected-card"
                             onclick="openModal(<?= $s['id'] ?>,'<?= addslashes(htmlspecialchars($s['ship_name'])) ?>','<?= addslashes(htmlspecialchars($s['imo_number'] ?? '—')) ?>','<?= addslashes(htmlspecialchars($s['cargo_type'])) ?>','<?= $s['arrival_time'] ?>','<?= $s['actual_arrival'] ?? '' ?>','<?= $s['actual_departure'] ?? '' ?>','<?= addslashes(htmlspecialchars($s['internal_notes'] ?? '')) ?>','<?= $s['status'] ?>')">
                            <div class="ship-name">🚢 <?= htmlspecialchars($s['ship_name']) ?></div>
                            <div class="ship-imo">IMO: <?= htmlspecialchars($s['imo_number'] ?? '—') ?></div>
                            <div class="ship-meta">
                                <span class="meta-tag cargo"><?= htmlspecialchars($s['cargo_type']) ?></span>
                                <span class="meta-tag time">ETA: <?= date('d/m H:i', strtotime($s['arrival_time'])) ?></span>
                            </div>
                            <button class="btn-arrived" onclick="event.stopPropagation(); updateStatus(<?= $s['id'] ?>, 'arrived')">
                                ✅ ΕΦΤΑΣΕ — ΑΓΚΥΡΟΒΟΛΗΣΕ
                            </button>
                            <?php if (!empty($s['internal_notes'])): ?>
                            <div class="card-notes-readonly">
                                <div class="card-notes-readonly-label">🔒 Εσωτερικά Σχόλια</div>
                                <div class="card-notes-readonly-text"><?= htmlspecialchars($s['internal_notes']) ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- ΣΤΟ ΛΙΜΑΝΙ -->
            <div class="col-md-4">
                <div class="status-col">
                    <div class="col-header inport">
                        <div class="col-title inport">⚓ ΣΤΟ ΛΙΜΑΝΙ</div>
                        <div class="col-count inport"><?= $inport->num_rows ?></div>
                    </div>
                    <div class="col-body">
                        <?php if ($inport->num_rows === 0): ?>
                            <div class="empty-state">Κανένα πλοίο<br>αγκυροβολημένο</div>
                        <?php endif; ?>
                        <?php while ($s = $inport->fetch_assoc()): ?>
                        <div class="ship-card inport-card"
                             onclick="openModal(<?= $s['id'] ?>,'<?= addslashes(htmlspecialchars($s['ship_name'])) ?>','<?= addslashes(htmlspecialchars($s['imo_number'] ?? '—')) ?>','<?= addslashes(htmlspecialchars($s['cargo_type'])) ?>','<?= $s['arrival_time'] ?>','<?= $s['actual_arrival'] ?? '' ?>','<?= $s['actual_departure'] ?? '' ?>','<?= addslashes(htmlspecialchars($s['internal_notes'] ?? '')) ?>','<?= $s['status'] ?>')">
                            <div class="ship-name">🚢 <?= htmlspecialchars($s['ship_name']) ?></div>
                            <div class="ship-imo">IMO: <?= htmlspecialchars($s['imo_number'] ?? '—') ?></div>
                            <div class="ship-meta">
                                <span class="meta-tag cargo"><?= htmlspecialchars($s['cargo_type']) ?></span>
                                <span class="meta-tag actual">⚓ <?= $s['actual_arrival'] ? date('d/m H:i', strtotime($s['actual_arrival'])) : '—' ?></span>
                            </div>
                            <?php
                            if ($s['actual_arrival']) {
                                $diff = time() - strtotime($s['actual_arrival']);
                                $hrs  = floor($diff / 3600);
                                $mins = floor(($diff % 3600) / 60);
                                echo "<div style='font-size:0.68rem; color:rgba(240,244,248,0.35); margin-bottom:4px;'>⏱️ Παραμονή: {$hrs}ω {$mins}λ</div>";
                            }
                            ?>
                            <button class="btn-departed" onclick="event.stopPropagation(); updateStatus(<?= $s['id'] ?>, 'departed')">
                                🚢 ΑΝΑΧΩΡΗΣΕ — ΕΦΥΓΕ
                            </button>
                            <?php if (!empty($s['internal_notes'])): ?>
                            <div class="card-notes-readonly">
                                <div class="card-notes-readonly-label">🔒 Εσωτερικά Σχόλια</div>
                                <div class="card-notes-readonly-text"><?= htmlspecialchars($s['internal_notes']) ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- ΑΝΑΧΩΡΗΣΕΙΣ -->
            <div class="col-md-4">
                <div class="status-col">
                    <div class="col-header departed">
                        <div class="col-title departed">🏁 ΑΝΑΧΩΡΗΣΕΙΣ ΣΗΜΕΡΑ</div>
                        <div class="col-count departed"><?= $departed->num_rows ?></div>
                    </div>
                    <div class="col-body">
                        <?php if ($departed->num_rows === 0): ?>
                            <div class="empty-state">Καμία αναχώρηση<br>σήμερα</div>
                        <?php endif; ?>
                        <?php while ($s = $departed->fetch_assoc()): ?>
                        <div class="ship-card departed-card"
                             onclick="openModal(<?= $s['id'] ?>,'<?= addslashes(htmlspecialchars($s['ship_name'])) ?>','<?= addslashes(htmlspecialchars($s['imo_number'] ?? '—')) ?>','<?= addslashes(htmlspecialchars($s['cargo_type'])) ?>','<?= $s['arrival_time'] ?>','<?= $s['actual_arrival'] ?? '' ?>','<?= $s['actual_departure'] ?? '' ?>','<?= addslashes(htmlspecialchars($s['internal_notes'] ?? '')) ?>','<?= $s['status'] ?>')">
                            <div class="ship-name">🚢 <?= htmlspecialchars($s['ship_name']) ?></div>
                            <div class="ship-imo">IMO: <?= htmlspecialchars($s['imo_number'] ?? '—') ?></div>
                            <div class="ship-meta">
                                <span class="meta-tag cargo"><?= htmlspecialchars($s['cargo_type']) ?></span>
                                <span class="meta-tag actual">⚓ <?= $s['actual_arrival'] ? date('H:i', strtotime($s['actual_arrival'])) : '—' ?></span>
                                <span class="meta-tag depart">🏁 <?= $s['actual_departure'] ? date('H:i', strtotime($s['actual_departure'])) : '—' ?></span>
                            </div>
                            <?php
                            if ($s['actual_arrival'] && $s['actual_departure']) {
                                $diff = strtotime($s['actual_departure']) - strtotime($s['actual_arrival']);
                                $hrs  = floor($diff / 3600);
                                $mins = floor(($diff % 3600) / 60);
                                echo "<div style='font-size:0.68rem; color:rgba(240,244,248,0.3);'>⏱️ Συνολική παραμονή: {$hrs}ω {$mins}λ</div>";
                            }
                            ?>
                            <?php if (!empty($s['internal_notes'])): ?>
                            <div class="card-notes-readonly">
                                <div class="card-notes-readonly-label">🔒 Εσωτερικά Σχόλια</div>
                                <div class="card-notes-readonly-text"><?= htmlspecialchars($s['internal_notes']) ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <footer class="atlas-footer">
        <span>ATLAS GROUP</span> · Port Management System · Πανεπιστήμιο Δυτικής Αττικής &copy; 2025
    </footer>
</div>

<!-- SHIP DETAIL MODAL -->
<div class="ship-modal-overlay" id="shipModal" onclick="closeModal(event)">
    <div class="ship-modal">
        <button class="modal-close" onclick="document.getElementById('shipModal').classList.remove('active')">✕</button>
        <div class="modal-ship-name">🚢 <span id="m-name"></span></div>
        <div style="font-size:0.7rem; color:rgba(240,244,248,0.35); letter-spacing:2px;">VESSEL DETAILS</div>
        <hr class="modal-divider">
        <div class="modal-row">
            <span class="modal-label">IMO Number</span>
            <span class="modal-value" id="m-imo"></span>
        </div>
        <div class="modal-row">
            <span class="modal-label">Τύπος Φορτίου</span>
            <span class="modal-value" id="m-cargo"></span>
        </div>
        <div class="modal-row">
            <span class="modal-label">ETA (Εκτιμώμενη Άφιξη)</span>
            <span class="modal-value" id="m-eta"></span>
        </div>
        <div class="modal-row">
            <span class="modal-label">⚓ Πραγματική Άφιξη</span>
            <span class="modal-value" id="m-actual-arrival"></span>
        </div>
        <div class="modal-row">
            <span class="modal-label">🏁 Αναχώρηση</span>
            <span class="modal-value" id="m-departure"></span>
        </div>
        <div class="modal-row">
            <span class="modal-label">⏱️ Παραμονή</span>
            <span class="modal-value" id="m-duration"></span>
        </div>
        <div class="modal-row">
            <span class="modal-label">Status</span>
            <span class="modal-value"><span class="modal-status-badge" id="m-status"></span></span>
        </div>
        <hr class="modal-divider">
        <div class="modal-notes-label">🔒 Εσωτερικά Σχόλια <span style="color:rgba(201,168,76,0.5);font-size:0.6rem;font-style:italic;">(επεξεργάσιμο)</span></div>
        <textarea class="modal-notes-textarea" id="m-notes" placeholder="Γράψε εσωτερικό σχόλιο..."></textarea>
        <div>
            <button class="modal-btn-save" onclick="saveModalNotes()">💾 Αποθήκευση Σχολίου</button>
            <span class="modal-save-feedback" id="modal-save-feedback">✓ Αποθηκεύτηκε!</span>
        </div>
        <input type="hidden" id="m-ship-id" value="">
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function updateStatus(id, action) {
    const label = action === 'arrived' ? 'ΕΦΤΑΣΕ' : 'ΑΝΑΧΩΡΗΣΕ';
    if (!confirm('Επιβεβαίωση: Το πλοίο ' + label + ';')) return;
    fetch('update_status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id + '&action=' + action
    })
    .then(r => r.json())
    .then(data => { if (data.success) location.reload(); });
}

function openModal(id, name, imo, cargo, eta, arrival, departure, notes, status) {
    document.getElementById('m-ship-id').value            = id;
    document.getElementById('m-name').textContent         = name;
    document.getElementById('m-imo').textContent          = imo || '—';
    document.getElementById('m-cargo').textContent        = cargo || '—';
    document.getElementById('m-eta').textContent          = eta ? formatDT(eta) : '—';
    document.getElementById('m-actual-arrival').textContent = arrival ? formatDT(arrival) : '—';
    document.getElementById('m-departure').textContent    = departure ? formatDT(departure) : '—';
    document.getElementById('m-notes').value              = notes || '';
    document.getElementById('modal-save-feedback').style.display = 'none';

    let dur = '—';
    if (arrival) {
        const start = new Date(arrival);
        const end   = departure ? new Date(departure) : new Date();
        const diff  = Math.floor((end - start) / 1000);
        const h     = Math.floor(diff / 3600);
        const m     = Math.floor((diff % 3600) / 60);
        dur = h + 'ω ' + m + 'λ' + (departure ? '' : ' (τρέχουσα)');
    }
    document.getElementById('m-duration').textContent = dur;

    const badge = document.getElementById('m-status');
    badge.textContent = status;
    badge.className = 'modal-status-badge';
    if (status === 'ΑΝΑΜΕΝΟΜΕΝΟ') badge.classList.add('status-expected');
    else if (status === 'ΣΤΟ ΛΙΜΑΝΙ') badge.classList.add('status-inport');
    else badge.classList.add('status-departed');

    document.getElementById('shipModal').classList.add('active');
}

function saveModalNotes() {
    const id    = document.getElementById('m-ship-id').value;
    const notes = document.getElementById('m-notes').value;
    fetch('update_status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id + '&action=save_notes&notes=' + encodeURIComponent(notes)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const fb = document.getElementById('modal-save-feedback');
            fb.style.display = 'inline';
            setTimeout(() => fb.style.display = 'none', 2500);
            // Update card read-only text live
            const cardEl = document.getElementById('card-' + id);
            if (cardEl) {
                let ro = cardEl.querySelector('.card-notes-readonly-text');
                if (notes.trim()) {
                    if (!ro) {
                        const sec = document.createElement('div');
                        sec.className = 'card-notes-readonly';
                        sec.innerHTML = '<div class="card-notes-readonly-label">🔒 Εσωτερικά Σχόλια</div><div class="card-notes-readonly-text"></div>';
                        cardEl.appendChild(sec);
                        ro = sec.querySelector('.card-notes-readonly-text');
                    }
                    ro.textContent = notes;
                } else if (ro) {
                    ro.closest('.card-notes-readonly').remove();
                }
            }
        }
    });
}

function closeModal(e) {
    if (e.target.id === 'shipModal')
        document.getElementById('shipModal').classList.remove('active');
}

function formatDT(dt) {
    if (!dt) return '—';
    const d = new Date(dt);
    return d.toLocaleDateString('el-GR') + ' ' + d.toLocaleTimeString('el-GR', {hour:'2-digit', minute:'2-digit'});
}
</script>
</body>
</html>
