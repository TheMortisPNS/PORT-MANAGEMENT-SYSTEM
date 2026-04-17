<?php
include 'db_connect.php';
include 'helpers.php';
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATLAS GROUP | Αναζήτηση</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Raleway:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --navy: #0a1628; --gold: #c9a84c; --gold-light: #e8c96d; --white: #f0f4f8; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: linear-gradient(135deg, #0a1628 0%, #0d2137 50%, #0a2a4a 100%); min-height: 100vh; font-family: 'Raleway', sans-serif; color: var(--white); }
        body::before { content:''; position:fixed; inset:0; background:radial-gradient(ellipse at 20% 50%, rgba(26,74,107,0.3) 0%, transparent 50%), radial-gradient(ellipse at 80% 20%, rgba(201,168,76,0.05) 0%, transparent 40%); pointer-events:none; z-index:0; }
        .content-wrapper { position: relative; z-index: 1; }
        .navbar-atlas { background: rgba(10,22,40,0.95); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(201,168,76,0.3); padding: 12px 0; }
        .navbar-brand-atlas { display:flex; align-items:center; gap:12px; text-decoration:none; }
        .logo-img { width:55px; height:55px; border-radius:50%; border:2px solid var(--gold); object-fit:cover; box-shadow:0 0 15px rgba(201,168,76,0.4); }
        .brand-name { font-family:'Cinzel',serif; font-size:1.2rem; color:var(--gold); letter-spacing:3px; font-weight:700; }
        .brand-sub { font-size:.65rem; color:rgba(240,244,248,.6); letter-spacing:2px; text-transform:uppercase; }
        .nav-btn { background:transparent; border:1px solid rgba(201,168,76,.4); color:var(--white)!important; border-radius:4px; padding:6px 14px; font-size:.8rem; letter-spacing:1px; text-decoration:none; margin-left:8px; }
        .nav-btn:hover,.nav-btn.active { background:var(--gold); color:var(--navy)!important; border-color:var(--gold); }
        .section-title { font-family:'Cinzel',serif; font-size:1rem; letter-spacing:3px; color:var(--gold); text-transform:uppercase; border-bottom:1px solid rgba(201,168,76,.2); padding-bottom:10px; margin-bottom:20px; }
        .search-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(201,168,76,0.2); border-radius: 16px; padding: 30px; backdrop-filter: blur(10px); margin: 40px 0 24px; }
        .form-control { background: rgba(255,255,255,0.05) !important; border: 1px solid rgba(201,168,76,0.2) !important; color: var(--white) !important; border-radius: 8px; padding: 12px 16px; }
        .btn-search { background: var(--gold); color: var(--navy); border: none; border-radius: 8px; padding: 12px 24px; font-family: 'Cinzel', serif; font-size: 0.8rem; letter-spacing: 2px; font-weight: 700; }
        .btn-clear { background: transparent; border: 1px solid rgba(240,244,248,0.2); color: rgba(240,244,248,0.5); border-radius: 8px; padding: 12px 20px; font-size: 0.8rem; text-decoration: none; }
        .results-info { background: rgba(201,168,76,0.08); border: 1px solid rgba(201,168,76,0.2); border-radius: 8px; padding: 12px 20px; margin-bottom: 20px; font-size: 0.85rem; color: rgba(240,244,248,0.7); }
        .results-info b { color: var(--gold); }
        .table-wrapper { background: rgba(255,255,255,0.02); border: 1px solid rgba(201,168,76,0.15); border-radius: 12px; padding: 24px; backdrop-filter: blur(10px); }
        .atlas-table { width:100%; border-collapse:separate; border-spacing:0 6px; }
        .atlas-table thead th { font-size:0.7rem; letter-spacing:2px; text-transform:uppercase; color:rgba(240,244,248,0.5); padding:10px 16px; border-bottom:1px solid rgba(201,168,76,0.2); font-weight:400; }
        .atlas-table tbody tr { background:rgba(255,255,255,0.03); }
        .atlas-table tbody td { padding:14px 16px; border-top:1px solid rgba(255,255,255,0.04); border-bottom:1px solid rgba(255,255,255,0.04); font-size:0.9rem; }
        .atlas-table tbody td:first-child { border-left:1px solid rgba(255,255,255,0.04); border-radius:8px 0 0 8px; }
        .atlas-table tbody td:last-child { border-right:1px solid rgba(255,255,255,0.04); border-radius:0 8px 8px 0; }
        .atlas-footer { border-top:1px solid rgba(201,168,76,0.15); padding:20px 0; text-align:center; font-size:0.75rem; color:rgba(240,244,248,0.3); letter-spacing:2px; margin-top:60px; }
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
                <a href="add_ship.php" class="nav-btn">➕ Νέα Άφιξη</a>
                <a href="search.php" class="nav-btn active">🔍 Αναζήτηση</a>
                <a href="calendar.php" class="nav-btn">📅 Ημερολόγιο</a>
                <a href="port_status.php" class="nav-btn">🗺️ Port Status</a>
                <a href="statistics.php" class="nav-btn">📊 Στατιστικά</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="search-card">
            <div class="section-title">🔍 Αναζήτηση Πλοίου</div>
            <form method="GET" action="search.php">
                <div class="d-flex gap-2">
                    <input type="text" name="keyword" id="searchInput" class="form-control form-control-lg" placeholder="Αναζήτηση με όνομα πλοίου, λιμάνι ή τύπο φορτίου...">
                    <button class="btn-search" type="submit">🔍 ΑΝΑΖΗΤΗΣΗ</button>
                    <a href="search.php" class="btn-clear">✖ Καθαρισμός</a>
                </div>
            </form>
        </div>

        <?php
        if (isset($_GET['keyword']) && trim($_GET['keyword']) !== '') {
            $keyword = '%' . trim($_GET['keyword']) . '%';
            $stmt = $conn->prepare("SELECT id, ship_name, imo_number, cargo_type, port, arrival_time FROM arrivals WHERE ship_name LIKE ? OR cargo_type LIKE ? OR port LIKE ? ORDER BY COALESCE(arrival_time, departure_date) DESC");
            $stmt->bind_param('sss', $keyword, $keyword, $keyword);
            $stmt->execute();
            $result = $stmt->get_result();

            echo "<div class='results-info'>Αποτελέσματα για: <b>" . safe_h($_GET['keyword']) . "</b> &nbsp;·&nbsp; <b>" . (int)$result->num_rows . "</b> εγγραφές</div>";
            echo "<div class='table-wrapper'><table class='atlas-table'><thead><tr><th>#</th><th>Πλοίο</th><th>IMO</th><th>Λιμάνι</th><th>Φορτίο</th><th>Άφιξη</th><th>PDA</th></tr></thead><tbody>";

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . (int)$row['id'] . "</td>
                            <td style='font-weight:600;'>" . safe_h($row['ship_name']) . "</td>
                            <td>" . safe_h($row['imo_number']) . "</td>
                            <td>" . safe_h($row['port']) . "</td>
                            <td>" . safe_h($row['cargo_type']) . "</td>
                            <td>" . format_datetime_greek($row['arrival_time']) . "</td>
                            <td><a href='pda.php?id=" . (int)$row['id'] . "' style='color:#8bb8ff;text-decoration:none;'>📄 PDA</a></td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='7' style='text-align:center; color:rgba(240,244,248,0.3); padding:40px;'>⚠️ Δεν βρέθηκαν αποτελέσματα</td></tr>";
            }

            echo "</tbody></table></div>";
            $stmt->close();
        }
        $conn->close();
        ?>
    </div>

    <footer class="atlas-footer"><span>ATLAS GROUP</span> · Port Management System · Πανεπιστήμιο Δυτικής Αττικής &copy; 2026</footer>
</div>

<script>
const params = new URLSearchParams(window.location.search);
if (params.get('keyword')) document.getElementById('searchInput').value = params.get('keyword');
</script>
</body>
</html>
