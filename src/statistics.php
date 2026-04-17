<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATLAS GROUP | Στατιστικά</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Raleway:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --navy: #0a1628;
            --deep-blue: #0d2137;
            --ocean: #1a4a6b;
            --gold: #c9a84c;
            --gold-light: #e8c96d;
            --white: #f0f4f8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: linear-gradient(135deg, #0a1628 0%, #0d2137 50%, #0a2a4a 100%);
            min-height: 100vh;
            font-family: 'Raleway', sans-serif;
            color: var(--white);
        }

        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background:
                radial-gradient(ellipse at 20% 50%, rgba(26,74,107,0.3) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 20%, rgba(201,168,76,0.05) 0%, transparent 40%);
            pointer-events: none;
            z-index: 0;
        }

        .content-wrapper { position: relative; z-index: 1; }

        /* NAVBAR */
        .navbar-atlas {
            background: rgba(10, 22, 40, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(201,168,76,0.3);
            padding: 12px 0;
        }
        .navbar-brand-atlas {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }
        .logo-img {
            width: 55px; height: 55px;
            border-radius: 50%;
            border: 2px solid var(--gold);
            object-fit: cover;
            box-shadow: 0 0 15px rgba(201,168,76,0.4);
        }
        .brand-text { line-height: 1.1; }
        .brand-name {
            font-family: 'Cinzel', serif;
            font-size: 1.2rem;
            color: var(--gold);
            letter-spacing: 3px;
            font-weight: 700;
        }
        .brand-sub {
            font-size: 0.65rem;
            color: rgba(240,244,248,0.6);
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .nav-btn {
            background: transparent;
            border: 1px solid rgba(201,168,76,0.4);
            color: var(--white) !important;
            border-radius: 4px;
            padding: 6px 14px;
            font-size: 0.8rem;
            letter-spacing: 1px;
            transition: all 0.3s;
            text-decoration: none;
            margin-left: 8px;
        }
        .nav-btn:hover { background: var(--gold); color: var(--navy) !important; border-color: var(--gold); }
        .nav-btn.active { background: var(--gold); color: var(--navy) !important; }

        /* STAT CARDS */
        .stat-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(201,168,76,0.2);
            border-radius: 12px;
            padding: 24px;
            backdrop-filter: blur(10px);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 3px; height: 100%;
            background: var(--gold);
        }
        .stat-card:hover {
            border-color: rgba(201,168,76,0.5);
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .stat-icon { font-size: 2rem; margin-bottom: 8px; }
        .stat-label {
            font-size: 0.7rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: rgba(240,244,248,0.5);
        }
        .stat-value {
            font-family: 'Cinzel', serif;
            font-size: 2rem;
            color: var(--gold);
            font-weight: 700;
        }
        .stat-sub {
            font-size: 0.75rem;
            color: rgba(240,244,248,0.4);
            margin-top: 4px;
        }

        /* CHART CARDS */
        .chart-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(201,168,76,0.15);
            border-radius: 12px;
            padding: 28px;
            backdrop-filter: blur(10px);
            height: 100%;
        }
        .section-title {
            font-family: 'Cinzel', serif;
            font-size: 0.95rem;
            letter-spacing: 3px;
            color: var(--gold);
            text-transform: uppercase;
            border-bottom: 1px solid rgba(201,168,76,0.2);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        /* TOP SHIPS TABLE */
        .mini-table { width: 100%; border-collapse: collapse; }
        .mini-table th {
            font-size: 0.65rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: rgba(240,244,248,0.4);
            padding: 8px 12px;
            border-bottom: 1px solid rgba(201,168,76,0.15);
            font-weight: 400;
        }
        .mini-table td {
            padding: 10px 12px;
            font-size: 0.85rem;
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }
        .mini-table tr:hover td { background: rgba(201,168,76,0.05); }
        .rank-badge {
            display: inline-block;
            width: 24px; height: 24px;
            border-radius: 50%;
            background: rgba(201,168,76,0.15);
            border: 1px solid rgba(201,168,76,0.3);
            color: var(--gold);
            font-size: 0.7rem;
            font-weight: 700;
            text-align: center;
            line-height: 24px;
        }
        .rank-badge.gold-rank   { background: rgba(201,168,76,0.3); border-color: var(--gold); }
        .rank-badge.silver-rank { background: rgba(192,192,192,0.2); border-color: silver; color: silver; }
        .rank-badge.bronze-rank { background: rgba(205,127,50,0.2); border-color: #cd7f32; color: #cd7f32; }

        /* PROGRESS BARS */
        .cargo-progress-item { margin-bottom: 16px; }
        .cargo-progress-label {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            margin-bottom: 6px;
        }
        .cargo-progress-label span:first-child { color: var(--white); }
        .cargo-progress-label span:last-child { color: var(--gold); font-weight: 600; }
        .progress-track {
            height: 8px;
            background: rgba(255,255,255,0.06);
            border-radius: 4px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 1s ease;
        }

        /* FOOTER */
        .atlas-footer {
            border-top: 1px solid rgba(201,168,76,0.15);
            padding: 20px 0;
            text-align: center;
            font-size: 0.75rem;
            color: rgba(240,244,248,0.3);
            letter-spacing: 2px;
            margin-top: 60px;
        }
        .atlas-footer span { color: var(--gold); }

        canvas { max-height: 280px; }
    </style>
</head>
<body>
<div class="content-wrapper">

    <!-- NAVBAR -->
    <nav class="navbar-atlas">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="index.php" class="navbar-brand-atlas">
                <img src="https://cdn.abacus.ai/images/2740de7f-a8ff-4f15-a162-2b34e6333d3f.png" class="logo-img" alt="Atlas Logo">
                <div class="brand-text">
                    <div class="brand-name">ATLAS GROUP</div>
                    <div class="brand-sub">Port Management System</div>
                </div>
            </a>
            <div>
                <a href="index.php" class="nav-btn">🏠 Αρχική</a>
                <a href="add_ship.php" class="nav-btn">➕ Νέα Άφιξη</a>
                <a href="search.php" class="nav-btn">🔍 Αναζήτηση</a>
                <a href="calendar.php" class="nav-btn">📅 Ημερολόγιο</a>
                <a href="port_status.php" class="nav-btn">🗺️ Port Status</a>
                <a href="statistics.php" class="nav-btn active">📊 Στατιστικά</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">

        <?php
        // ── Βασικά στατιστικά ──────────────────────────────────────────
        $total        = $conn->query("SELECT COUNT(*) as c FROM arrivals")->fetch_assoc()['c'];
        $total_types  = $conn->query("SELECT COUNT(DISTINCT cargo_type) as c FROM arrivals")->fetch_assoc()['c'];
        $total_ships  = $conn->query("SELECT COUNT(DISTINCT ship_name) as c FROM arrivals")->fetch_assoc()['c'];

        // Αφίξεις τρέχοντος μήνα
        $this_month   = $conn->query("SELECT COUNT(*) as c FROM arrivals WHERE MONTH(arrival_time)=MONTH(NOW()) AND YEAR(arrival_time)=YEAR(NOW())")->fetch_assoc()['c'];

        // ── Κατανομή ανά τύπο φορτίου ──────────────────────────────────
        $cargo_res = $conn->query("SELECT cargo_type, COUNT(*) as cnt FROM arrivals GROUP BY cargo_type ORDER BY cnt DESC");
        $cargo_labels = []; $cargo_counts = []; $cargo_colors = [];
        $color_map = [
            'Πετρέλαιο'    => ['rgba(220,53,69,0.8)',  'rgba(220,53,69,0.3)'],
            'Containers'   => ['rgba(13,110,253,0.8)', 'rgba(13,110,253,0.3)'],
            'Επιβάτες'     => ['rgba(25,135,84,0.8)',  'rgba(25,135,84,0.3)'],
            'Χύδην Φορτίο' => ['rgba(255,193,7,0.8)',  'rgba(255,193,7,0.3)'],
            'Άλλο'         => ['rgba(108,117,125,0.8)','rgba(108,117,125,0.3)'],
        ];
        $default_colors = ['rgba(201,168,76,0.8)', 'rgba(201,168,76,0.3)'];
        while ($row = $cargo_res->fetch_assoc()) {
            $cargo_labels[] = $row['cargo_type'];
            $cargo_counts[] = (int)$row['cnt'];
            $c = isset($color_map[$row['cargo_type']]) ? $color_map[$row['cargo_type']] : $default_colors;
            $cargo_colors[] = $c[0];
        }

        // ── Αφίξεις ανά μήνα (τελευταίοι 12 μήνες) ────────────────────
        $monthly_res = $conn->query("
            SELECT DATE_FORMAT(arrival_time,'%Y-%m') as ym,
                   DATE_FORMAT(arrival_time,'%b %Y') as label,
                   COUNT(*) as cnt
            FROM arrivals
            WHERE arrival_time >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY ym, label
            ORDER BY ym ASC
        ");
        $month_labels = []; $month_counts = [];
        while ($row = $monthly_res->fetch_assoc()) {
            $month_labels[] = $row['label'];
            $month_counts[] = (int)$row['cnt'];
        }

        // ── Top 5 πλοία (περισσότερες αφίξεις) ────────────────────────
        $top_ships_res = $conn->query("
            SELECT ship_name, imo_number, COUNT(*) as visits
            FROM arrivals
            GROUP BY ship_name, imo_number
            ORDER BY visits DESC
            LIMIT 5
        ");
        $top_ships = [];
        while ($row = $top_ships_res->fetch_assoc()) $top_ships[] = $row;

        // ── Αφίξεις ανά ημέρα εβδομάδας ───────────────────────────────
        $dow_res = $conn->query("
            SELECT DAYOFWEEK(arrival_time) as dow,
                   ELT(DAYOFWEEK(arrival_time),
                       'Κυρ','Δευ','Τρί','Τετ','Πέμ','Παρ','Σάβ') as day_name,
                   COUNT(*) as cnt
            FROM arrivals
            GROUP BY dow, day_name
            ORDER BY dow ASC
        ");
        $dow_labels = []; $dow_counts = [];
        while ($row = $dow_res->fetch_assoc()) {
            $dow_labels[] = $row['day_name'];
            $dow_counts[] = (int)$row['cnt'];
        }

        $conn->close();
        ?>

        <!-- ── KPI CARDS ─────────────────────────────────────────────── -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">🚢</div>
                    <div class="stat-label">Συνολικές Αφίξεις</div>
                    <div class="stat-value"><?= $total ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">⚓</div>
                    <div class="stat-label">Μοναδικά Πλοία</div>
                    <div class="stat-value"><?= $total_ships ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-label">Τύποι Φορτίου</div>
                    <div class="stat-value"><?= $total_types ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <div class="stat-label">Αφίξεις Μήνα</div>
                    <div class="stat-value"><?= $this_month ?></div>
                    <div class="stat-sub">τρέχων μήνας</div>
                </div>
            </div>
        </div>

        <!-- ── ROW 1: Doughnut + Monthly Line ────────────────────────── -->
        <div class="row g-3 mb-3">
            <!-- Doughnut: Κατανομή φορτίου -->
            <div class="col-md-5">
                <div class="chart-card">
                    <div class="section-title">🥧 Κατανομή Φορτίου</div>
                    <canvas id="cargoDonut"></canvas>
                </div>
            </div>
            <!-- Line: Μηνιαίες αφίξεις -->
            <div class="col-md-7">
                <div class="chart-card">
                    <div class="section-title">📈 Μηνιαίες Αφίξεις (12 μήνες)</div>
                    <canvas id="monthlyLine"></canvas>
                </div>
            </div>
        </div>

        <!-- ── ROW 2: Bar (ημέρα) + Progress bars + Top ships ─────────── -->
        <div class="row g-3 mb-3">
            <!-- Bar: Αφίξεις ανά ημέρα εβδομάδας -->
            <div class="col-md-5">
                <div class="chart-card">
                    <div class="section-title">📊 Αφίξεις ανά Ημέρα</div>
                    <canvas id="dowBar"></canvas>
                </div>
            </div>

            <!-- Progress bars: % ανά τύπο φορτίου -->
            <div class="col-md-3">
                <div class="chart-card">
                    <div class="section-title">📉 Ποσοστά Φορτίου</div>
                    <?php
                    $progress_colors = [
                        'Πετρέλαιο'    => '#ff6b7a',
                        'Containers'   => '#6ea8fe',
                        'Επιβάτες'     => '#75b798',
                        'Χύδην Φορτίο' => '#ffda6a',
                        'Άλλο'         => '#adb5bd',
                    ];
                    foreach ($cargo_labels as $i => $label):
                        $pct = $total > 0 ? round($cargo_counts[$i] / $total * 100) : 0;
                        $col = isset($progress_colors[$label]) ? $progress_colors[$label] : '#c9a84c';
                    ?>
                    <div class="cargo-progress-item">
                        <div class="cargo-progress-label">
                            <span><?= htmlspecialchars($label) ?></span>
                            <span><?= $pct ?>%</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width:<?= $pct ?>%; background:<?= $col ?>;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($cargo_labels)): ?>
                        <p style="color:rgba(240,244,248,0.3); font-size:0.85rem;">Δεν υπάρχουν δεδομένα.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Top 5 πλοία -->
            <div class="col-md-4">
                <div class="chart-card">
                    <div class="section-title">🏆 Top 5 Πλοία</div>
                    <?php if (!empty($top_ships)): ?>
                    <table class="mini-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Πλοίο</th>
                                <th>IMO</th>
                                <th>Αφίξεις</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($top_ships as $i => $ship):
                            $rank_class = $i === 0 ? 'gold-rank' : ($i === 1 ? 'silver-rank' : ($i === 2 ? 'bronze-rank' : ''));
                        ?>
                        <tr>
                            <td><span class="rank-badge <?= $rank_class ?>"><?= $i+1 ?></span></td>
                            <td style="color:var(--white); font-weight:600;"><?= htmlspecialchars($ship['ship_name']) ?></td>
                            <td style="color:rgba(240,244,248,0.4); font-size:0.75rem;"><?= htmlspecialchars($ship['imo_number']) ?></td>
                            <td style="color:var(--gold); font-weight:700;"><?= $ship['visits'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <p style="color:rgba(240,244,248,0.3); font-size:0.85rem;">Δεν υπάρχουν δεδομένα.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div><!-- /container -->

    <footer class="atlas-footer">
        <span>ATLAS GROUP</span> · Port Management System · Πανεπιστήμιο Δυτικής Αττικής &copy; 2025
    </footer>

</div><!-- /content-wrapper -->

<!-- ── CHART.JS SCRIPTS ──────────────────────────────────────────────── -->
<script>
const chartDefaults = {
    color: 'rgba(240,244,248,0.6)',
    font: { family: 'Raleway', size: 12 }
};
Chart.defaults.color = chartDefaults.color;
Chart.defaults.font  = chartDefaults.font;

// 1. Doughnut – Κατανομή φορτίου
new Chart(document.getElementById('cargoDonut'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($cargo_labels, JSON_UNESCAPED_UNICODE) ?>,
        datasets: [{
            data: <?= json_encode($cargo_counts) ?>,
            backgroundColor: <?= json_encode($cargo_colors) ?>,
            borderColor: 'rgba(10,22,40,0.8)',
            borderWidth: 3,
            hoverOffset: 8
        }]
    },
    options: {
        responsive: true,
        cutout: '65%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: { padding: 16, boxWidth: 12, color: 'rgba(240,244,248,0.7)' }
            },
            tooltip: {
                callbacks: {
                    label: ctx => {
                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                        const pct = total > 0 ? Math.round(ctx.parsed / total * 100) : 0;
                        return ` ${ctx.label}: ${ctx.parsed} αφίξεις (${pct}%)`;
                    }
                }
            }
        }
    }
});

// 2. Line – Μηνιαίες αφίξεις
new Chart(document.getElementById('monthlyLine'), {
    type: 'line',
    data: {
        labels: <?= json_encode($month_labels, JSON_UNESCAPED_UNICODE) ?>,
        datasets: [{
            label: 'Αφίξεις',
            data: <?= json_encode($month_counts) ?>,
            borderColor: '#c9a84c',
            backgroundColor: 'rgba(201,168,76,0.1)',
            borderWidth: 2.5,
            pointBackgroundColor: '#c9a84c',
            pointRadius: 5,
            pointHoverRadius: 7,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.y} αφίξεις` } }
        },
        scales: {
            x: {
                grid: { color: 'rgba(255,255,255,0.05)' },
                ticks: { color: 'rgba(240,244,248,0.5)', maxRotation: 45 }
            },
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(255,255,255,0.05)' },
                ticks: { color: 'rgba(240,244,248,0.5)', stepSize: 1 }
            }
        }
    }
});

// 3. Bar – Αφίξεις ανά ημέρα εβδομάδας
new Chart(document.getElementById('dowBar'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($dow_labels, JSON_UNESCAPED_UNICODE) ?>,
        datasets: [{
            label: 'Αφίξεις',
            data: <?= json_encode($dow_counts) ?>,
            backgroundColor: 'rgba(201,168,76,0.5)',
            borderColor: 'rgba(201,168,76,0.9)',
            borderWidth: 1.5,
            borderRadius: 6,
            hoverBackgroundColor: 'rgba(201,168,76,0.8)'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.y} αφίξεις` } }
        },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: 'rgba(240,244,248,0.5)' } },
            y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: 'rgba(240,244,248,0.5)', stepSize: 1 } }
        }
    }
});
</script>
</body>
</html> 
