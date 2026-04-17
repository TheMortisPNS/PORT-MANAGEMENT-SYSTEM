<?php
include 'db_connect.php';
include 'helpers.php';
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATLAS GROUP | Νέα Κράτηση</title>
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
        .brand-name { font-family:'Cinzel',serif; font-size:1.2rem; color:var(--gold); letter-spacing:3px; font-weight:700; line-height:1.1; }
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
        .atlas-footer { border-top:1px solid rgba(201,168,76,.15); padding:20px 0; text-align:center; font-size:.75rem; color:rgba(240,244,248,.3); letter-spacing:2px; margin-top:50px; }
        .atlas-footer span { color:var(--gold); }
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
                <a href="add_ship.php" class="nav-btn active">➕ Νέα Άφιξη</a>
                <a href="search.php" class="nav-btn">🔍 Αναζήτηση</a>
                <a href="calendar.php" class="nav-btn">📅 Ημερολόγιο</a>
                <a href="port_status.php" class="nav-btn">🗺️ Port Status</a>
                <a href="statistics.php" class="nav-btn">📊 Στατιστικά</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="form-card">
            <div class="section-title">➕ Καταχώρηση Νέου Ταξιδιού</div>
            <form method="POST" action="insert.php">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Όνομα Πλοίου</label>
                        <input type="text" name="ship_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">IMO Number</label>
                        <input type="text" name="imo_number" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Λιμάνι</label>
                        <input type="text" name="port" class="form-control" placeholder="π.χ. Πειραιάς" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Τύπος Φορτίου</label>
                        <select name="cargo_type" class="form-select">
                            <option value="Containers">Containers</option>
                            <option value="Πετρέλαιο">Πετρέλαιο</option>
                            <option value="Χύδην Φορτίο">Χύδην Φορτίο</option>
                            <option value="Επιβάτες">Επιβάτες</option>
                            <option value="Άλλο">Άλλο</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Ημερομηνία / Ώρα Άφιξης</label>
                        <input type="datetime-local" name="arrival_time" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Ημερομηνία / Ώρα Αναχώρησης</label>
                        <input type="datetime-local" name="departure_date" class="form-control">
                    </div>
                </div>

                <div class="section-title mt-4">💰 PDA Κόστη</div>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Λιμενικά τέλη (⚓)</label><input type="number" step="0.01" min="0" name="port_charges" class="form-control" value="0"></div>
                    <div class="col-md-4"><label class="form-label">Πλοηγός (⚓)</label><input type="number" step="0.01" min="0" name="pilotage" class="form-control" value="0"></div>
                    <div class="col-md-4"><label class="form-label">Ρυμουλκά (⚓)</label><input type="number" step="0.01" min="0" name="towage" class="form-control" value="0"></div>
                    <div class="col-md-4"><label class="form-label">Θέση πρόσδεσης (⚓)</label><input type="number" step="0.01" min="0" name="berth_dues" class="form-control" value="0"></div>
                    <div class="col-md-4"><label class="form-label">Line handling (🛠️)</label><input type="number" step="0.01" min="0" name="services" class="form-control" value="0"></div>
                    <div class="col-md-4"><label class="form-label">Διαχείριση απορριμμάτων (🛠️)</label><input type="number" step="0.01" min="0" name="garbage" class="form-control" value="0"></div>
                    <div class="col-md-4"><label class="form-label">Μεταφορές πληρώματος (👨‍✈️)</label><input type="number" step="0.01" min="0" name="crew_changes" class="form-control" value="0"></div>
                    <div class="col-md-4"><label class="form-label">Φορτοεκφόρτωση (📦)</label><input type="number" step="0.01" min="0" name="cargo_ops" class="form-control" value="0"></div>
                    <div class="col-md-4"><label class="form-label">Τελωνεία / Χαρτιά (🧾)</label><input type="number" step="0.01" min="0" name="customs_docs" class="form-control" value="0"></div>
                    <div class="col-md-4"><label class="form-label">Αμοιβή πράκτορα (🏢)</label><input type="number" step="0.01" min="0" name="agency_fee" class="form-control" value="0"></div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn-atlas">⚓ ΑΠΟΘΗΚΕΥΣΗ</button>
                    <a href="index.php" class="btn-cancel">❌ Ακύρωση</a>
                </div>
            </form>
        </div>
    </div>

    <footer class="atlas-footer">
        <span>ATLAS GROUP</span> · Port Management System · Πανεπιστήμιο Δυτικής Αττικής &copy; 2026
    </footer>
</div>
</body>
</html>
