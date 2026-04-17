<?php include 'db_connect.php'; ?>
<?php
$month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$year  = isset($_GET['year'])  ? intval($_GET['year'])  : intval(date('Y'));

$first_day     = mktime(0, 0, 0, $month, 1, $year);
$days_in_month = date('t', $first_day);
$start_weekday = date('N', $first_day);

$prev_month = $month == 1 ? 12 : $month - 1;
$prev_year  = $month == 1 ? $year - 1 : $year;
$next_month = $month == 12 ? 1 : $month + 1;
$next_year  = $month == 12 ? $year + 1 : $year;

// Αφίξεις του μήνα
$result = $conn->query("SELECT * FROM arrivals WHERE MONTH(arrival_time) = $month AND YEAR(arrival_time) = $year ORDER BY arrival_time ASC");
$arrivals_by_day = [];
while ($row = $result->fetch_assoc()) {
    $day = intval(date('j', strtotime($row['arrival_time'])));
    $arrivals_by_day[$day][] = $row;
}

// Αναχωρήσεις του μήνα (ξεχωριστό array)
$result2 = $conn->query("SELECT * FROM arrivals WHERE departure_date IS NOT NULL AND MONTH(departure_date) = $month AND YEAR(departure_date) = $year ORDER BY departure_date ASC");
$departures_by_day = [];
while ($row = $result2->fetch_assoc()) {
    $day = intval(date('j', strtotime($row['departure_date'])));
    $departures_by_day[$day][] = $row;
}

$month_names = [
    1=>'Ιανουάριος',2=>'Φεβρουάριος',3=>'Μάρτιος',4=>'Απρίλιος',
    5=>'Μάιος',6=>'Ιούνιος',7=>'Ιούλιος',8=>'Αύγουστος',
    9=>'Σεπτέμβριος',10=>'Οκτώβριος',11=>'Νοέμβριος',12=>'Δεκέμβριος'
];

$badge_map = [
    'Πετρέλαιο'    => ['color'=>'#ff6b7a','bg'=>'rgba(220,53,69,0.25)','icon'=>'&#128722;'],
    'Containers'   => ['color'=>'#6ea8fe','bg'=>'rgba(13,110,253,0.25)','icon'=>'&#128230;'],
    'Επιβάτες'     => ['color'=>'#75b798','bg'=>'rgba(25,135,84,0.25)','icon'=>'&#128101;'],
    'Χύδην Φορτίο' => ['color'=>'#ffda6a','bg'=>'rgba(255,193,7,0.25)','icon'=>'&#9875;'],
    'Άλλο'         => ['color'=>'#adb5bd','bg'=>'rgba(108,117,125,0.25)','icon'=>'&#128674;'],
];
?>
<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<title>ATLAS GROUP | Ημερολόγιο</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Raleway:wght@300;400;600&display=swap" rel="stylesheet">
<style>
:root{--navy:#0a1628;--gold:#c9a84c;--gold-light:#e8c96d;--white:#f0f4f8;}
*{margin:0;padding:0;box-sizing:border-box;}
body{background:linear-gradient(135deg,#0a1628,#0d2137,#0a2a4a);min-height:100vh;font-family:'Raleway',sans-serif;color:var(--white);}
.navbar-atlas{background:rgba(10,22,40,0.95);border-bottom:1px solid rgba(201,168,76,0.3);padding:12px 0;}
.navbar-brand-atlas{display:flex;align-items:center;gap:12px;text-decoration:none;}
.logo-img{width:55px;height:55px;border-radius:50%;border:2px solid var(--gold);object-fit:cover;}
.brand-name{font-family:'Cinzel',serif;font-size:1.2rem;color:var(--gold);letter-spacing:3px;font-weight:700;}
.brand-sub{font-size:0.65rem;color:rgba(240,244,248,0.6);letter-spacing:2px;}
.nav-btn{background:transparent;border:1px solid rgba(201,168,76,0.4);color:var(--white)!important;border-radius:4px;padding:6px 14px;font-size:0.8rem;text-decoration:none;margin-left:8px;transition:all 0.3s;}
.nav-btn:hover,.nav-btn.active{background:var(--gold);color:var(--navy)!important;}
.cal-header{display:flex;align-items:center;justify-content:space-between;background:rgba(255,255,255,0.03);border:1px solid rgba(201,168,76,0.2);border-radius:12px;padding:20px 28px;margin:30px 0 20px;}
.cal-title{font-family:'Cinzel',serif;font-size:1.4rem;color:var(--gold);letter-spacing:3px;}
.cal-nav-btn{background:transparent;border:1px solid rgba(201,168,76,0.4);color:var(--gold);border-radius:8px;padding:8px 18px;font-size:0.85rem;text-decoration:none;transition:all 0.3s;}
.cal-nav-btn:hover{background:var(--gold);color:var(--navy);}
.cal-weekdays{display:grid;grid-template-columns:repeat(7,1fr);gap:4px;margin-bottom:4px;}
.cal-weekday{text-align:center;font-size:0.65rem;letter-spacing:2px;text-transform:uppercase;color:rgba(240,244,248,0.4);padding:8px 0;}
.cal-weekday.weekend{color:rgba(201,168,76,0.5);}
.cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:4px;}
.cal-cell{background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.05);border-radius:10px;min-height:110px;padding:8px;transition:all 0.2s;}
.cal-cell:hover{background:rgba(255,255,255,0.05);border-color:rgba(201,168,76,0.2);}
.cal-cell.empty{background:transparent;border-color:transparent;}
.cal-cell.today{border-color:rgba(201,168,76,0.5);background:rgba(201,168,76,0.06);}
.cal-cell.has-arrivals{border-color:rgba(201,168,76,0.3);}
.day-num{font-family:'Cinzel',serif;font-size:0.85rem;color:rgba(240,244,248,0.5);margin-bottom:6px;display:flex;align-items:center;justify-content:space-between;}
.cal-cell.today .day-num{color:var(--gold);}
.day-count-badge{background:var(--gold);color:var(--navy);border-radius:50%;width:18px;height:18px;font-size:0.6rem;font-weight:700;display:flex;align-items:center;justify-content:center;}
.ship-chip{border-radius:6px;padding:3px 7px;margin-bottom:3px;font-size:0.68rem;font-weight:600;cursor:pointer;transition:all 0.2s;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;border:1px solid transparent;}
.ship-chip:hover{filter:brightness(1.2);}
.legend{display:flex;gap:16px;flex-wrap:wrap;background:rgba(255,255,255,0.02);border:1px solid rgba(201,168,76,0.15);border-radius:10px;padding:14px 20px;margin-bottom:20px;}
.legend-item{display:flex;align-items:center;gap:6px;font-size:0.75rem;color:rgba(240,244,248,0.6);}
.legend-dot{width:10px;height:10px;border-radius:3px;}
.modal-atlas .modal-content{background:#0d2137;border:1px solid rgba(201,168,76,0.3);border-radius:16px;color:var(--white);}
.modal-atlas .modal-header{border-bottom:1px solid rgba(201,168,76,0.2);padding:20px 24px;}
.modal-atlas .modal-title{font-family:'Cinzel',serif;color:var(--gold);letter-spacing:2px;}
.modal-atlas .btn-close{filter:invert(1) opacity(0.5);}
.modal-atlas .modal-body{padding:24px;}
.modal-atlas .modal-footer{border-top:1px solid rgba(201,168,76,0.15);padding:16px 24px;}
.ship-detail-card{background:rgba(255,255,255,0.04);border:1px solid rgba(201,168,76,0.15);border-radius:10px;padding:16px;margin-bottom:12px;position:relative;overflow:hidden;}
.ship-detail-card::before{content:'';position:absolute;left:0;top:0;width:3px;height:100%;background:var(--gold);}
.ship-detail-name{font-weight:700;font-size:1rem;margin-bottom:4px;}
.atlas-footer{border-top:1px solid rgba(201,168,76,0.15);padding:20px 0;text-align:center;font-size:0.75rem;color:rgba(240,244,248,0.3);letter-spacing:2px;margin-top:40px;}
.atlas-footer span{color:var(--gold);}
</style>
</head>
<body>

<nav class="navbar-atlas">
  <div class="container d-flex justify-content-between align-items-center">
    <a href="index.php" class="navbar-brand-atlas">
      <img src="https://cdn.abacus.ai/images/2740de7f-a8ff-4f15-a162-2b34e6333d3f.png" class="logo-img" alt="Logo">
      <div>
        <div class="brand-name">ATLAS GROUP</div>
        <div class="brand-sub">Port Management System</div>
      </div>
    </a>
    <div>
      <a href="index.php" class="nav-btn">&#127968; Αρχική</a>
      <a href="add_ship.php" class="nav-btn">&#10133; Νέα Άφιξη</a>
      <a href="search.php" class="nav-btn">&#128269; Αναζήτηση</a>
      <a href="calendar.php" class="nav-btn active">&#128197; Ημερολόγιο</a>
      <a href="port_status.php" class="nav-btn">🗺️ Port Status</a>
      <a href="statistics.php" class="nav-btn">📊 Στατιστικά</a>
            </div>
    </div>
  </div>
</nav>

<div class="container">

  <div class="cal-header">
    <a href="calendar.php?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>" class="cal-nav-btn">&larr; Προηγ.</a>
    <div class="cal-title">&#128197; <?php echo $month_names[$month]; ?> <?php echo $year; ?></div>
    <a href="calendar.php?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>" class="cal-nav-btn">Επόμ. &rarr;</a>
  </div>

  <div class="legend">
    <?php foreach($badge_map as $type => $style): ?>
    <div class="legend-item">
      <div class="legend-dot" style="background:<?php echo $style['bg']; ?>;border:1px solid <?php echo $style['color']; ?>;"></div>
      <?php echo $style['icon']; ?> <?php echo $type; ?>
    </div>
    <?php endforeach; ?>
    <div class="legend-item">
      <div class="legend-dot" style="background:rgba(220,53,69,0.25);border:1px solid #ff6b7a;"></div>
      &#8593; Αναχώρηση
    </div>
    <div class="legend-item" style="margin-left:auto;">
      <div class="legend-dot" style="border:1px solid rgba(201,168,76,0.5);background:rgba(201,168,76,0.06);"></div>
      Σήμερα
    </div>
  </div>

  <div class="cal-weekdays">
    <div class="cal-weekday">Δευ</div>
    <div class="cal-weekday">Τρι</div>
    <div class="cal-weekday">Τετ</div>
    <div class="cal-weekday">Πεμ</div>
    <div class="cal-weekday">Παρ</div>
    <div class="cal-weekday weekend">Σαβ</div>
    <div class="cal-weekday weekend">Κυρ</div>
  </div>

  <div class="cal-grid">
  <?php
  $today_day   = intval(date('j'));
  $today_month = intval(date('m'));
  $today_year  = intval(date('Y'));

  for ($e = 1; $e < $start_weekday; $e++) {
      echo '<div class="cal-cell empty"></div>';
  }

  for ($d = 1; $d <= $days_in_month; $d++) {
      $is_today    = ($d==$today_day && $month==$today_month && $year==$today_year);
      $has_arr     = isset($arrivals_by_day[$d]);
      $has_dep     = isset($departures_by_day[$d]);
      $wday        = (($start_weekday + $d - 2) % 7) + 1;
      $is_weekend  = ($wday >= 6);

      $cls = 'cal-cell';
      if ($is_today)            $cls .= ' today';
      if ($has_arr || $has_dep) $cls .= ' has-arrivals';
      if ($is_weekend)          $cls .= ' weekend-cell';

      $total_count = ($has_arr ? count($arrivals_by_day[$d]) : 0) + ($has_dep ? count($departures_by_day[$d]) : 0);

      echo '<div class="' . $cls . '">';
      echo '<div class="day-num"><span>' . $d . '</span>';
      if ($total_count > 0) {
          echo '<span class="day-count-badge">' . $total_count . '</span>';
      }
      echo '</div>';

      // Αφίξεις (πράσινο/χρωματιστό chip με βελάκι κάτω)
      if ($has_arr) {
          foreach ($arrivals_by_day[$d] as $arr) {
              $st  = isset($badge_map[$arr['cargo_type']]) ? $badge_map[$arr['cargo_type']] : $badge_map['Άλλο'];
              $t   = date('H:i', strtotime($arr['arrival_time']));
              $enc = htmlspecialchars(json_encode($arr), ENT_QUOTES);
              $nm  = htmlspecialchars($arr['ship_name'], ENT_QUOTES);
              echo '<span class="ship-chip" style="background:' . $st['bg'] . ';color:' . $st['color'] . ';border-color:' . $st['color'] . '40;" onclick=\'showModal(' . $enc . ')\' title="Αφιξη: ' . $nm . '">';
              echo '&#8595; ' . $t . ' ' . $nm;
              echo '</span>';
          }
      }

      // Αναχωρήσεις (κόκκινο chip με βελάκι πάνω) - στη σωστή ημέρα!
      if ($has_dep) {
          foreach ($departures_by_day[$d] as $arr) {
              $dep_t = date('H:i', strtotime($arr['departure_date']));
              $enc   = htmlspecialchars(json_encode($arr), ENT_QUOTES);
              $nm    = htmlspecialchars($arr['ship_name'], ENT_QUOTES);
              echo '<span class="ship-chip" style="background:rgba(220,53,69,0.2);color:#ff6b7a;border-color:#ff6b7a40;" onclick=\'showModal(' . $enc . ')\' title="Αναχωρηση: ' . $nm . '">';
              echo '&#8593; ' . $dep_t . ' ' . $nm;
              echo '</span>';
          }
      }

      echo '</div>';
  }

  $total_cells = $start_weekday - 1 + $days_in_month;
  $remaining   = (7 - ($total_cells % 7)) % 7;
  for ($r = 0; $r < $remaining; $r++) {
      echo '<div class="cal-cell empty"></div>';
  }
  ?>
  </div>

</div>

<footer class="atlas-footer">
  <span>ATLAS GROUP</span> &middot; Port Management System &middot; Πανεπιστήμιο Δυτικής Αττικής &copy; 2025
</footer>

<div class="modal fade modal-atlas" id="shipModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">&#128674; Στοιχεία Πλοίου</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modalBody"></div>
      <div class="modal-footer">
        <a id="modalEditBtn" href="#" style="background:var(--gold);color:var(--navy);border:none;border-radius:8px;padding:8px 20px;font-family:'Cinzel',serif;font-size:0.8rem;letter-spacing:1px;font-weight:700;text-decoration:none;">&#9999; ΕΠΕΞΕΡΓΑΣΙΑ</a>
        <button type="button" data-bs-dismiss="modal" style="background:transparent;border:1px solid rgba(240,244,248,0.2);color:rgba(240,244,248,0.5);border-radius:8px;padding:8px 20px;font-size:0.8rem;cursor:pointer;">Κλείσιμο</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const badgeMap = {
    '\u03a0\u03b5\u03c4\u03c1\u03ad\u03bb\u03b1\u03b9\u03bf': {color:'#ff6b7a', icon:'&#128722;'},
    'Containers':   {color:'#6ea8fe', icon:'&#128230;'},
    '\u0395\u03c0\u03b9\u03b2\u03ac\u03c4\u03b5\u03c2':     {color:'#75b798', icon:'&#128101;'},
    '\u03a7\u03cd\u03b4\u03b7\u03bd \u03a6\u03bf\u03c1\u03c4\u03af\u03bf': {color:'#ffda6a', icon:'&#9875;'},
    '\u0386\u03bb\u03bb\u03bf':         {color:'#adb5bd', icon:'&#128674;'},
};

function showModal(data) {
    const ship  = (typeof data === 'string') ? JSON.parse(data) : data;
    const style = badgeMap[ship.cargo_type] || badgeMap['\u0386\u03bb\u03bb\u03bf'];
    const dt    = new Date(ship.arrival_time);
    const dateStr = dt.toLocaleDateString('el-GR', {weekday:'long',year:'numeric',month:'long',day:'numeric'});
    const timeStr = dt.toLocaleTimeString('el-GR', {hour:'2-digit',minute:'2-digit'});
    const depStr  = ship.departure_date ? new Date(ship.departure_date).toLocaleString('el-GR', {day:'2-digit',month:'long',year:'numeric',hour:'2-digit',minute:'2-digit'}) : '\u2014';

    document.getElementById('modalBody').innerHTML =
        '<div class="ship-detail-card">' +
            '<div class="ship-detail-name">' + style.icon + ' ' + ship.ship_name + '</div>' +
            '<div style="font-size:0.8rem;color:rgba(240,244,248,0.5);">IMO: <b style="color:#f0f4f8;">' + (ship.imo_number || '\u2014') + '</b></div>' +
        '</div>' +
        '<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:4px;">' +
            '<div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:8px;padding:14px;">' +
                '<div style="font-size:0.65rem;letter-spacing:2px;color:rgba(240,244,248,0.4);text-transform:uppercase;margin-bottom:4px;">\u03a4\u03cd\u03c0\u03bf\u03c2 \u03a6\u03bf\u03c1\u03c4\u03af\u03bf\u03c5</div>' +
                '<div style="color:' + style.color + ';font-weight:600;">' + style.icon + ' ' + ship.cargo_type + '</div>' +
            '</div>' +
            '<div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:8px;padding:14px;">' +
                '<div style="font-size:0.65rem;letter-spacing:2px;color:rgba(240,244,248,0.4);text-transform:uppercase;margin-bottom:4px;">\u038f\u03c1\u03b1 \u0391\u03c6\u03b9\u03be\u03b7\u03c2</div>' +
                '<div style="color:#e8c96d;font-weight:600;">&#8595; ' + timeStr + '</div>' +
            '</div>' +
        '</div>' +
        '<div style="background:rgba(220,53,69,0.1);border:1px solid rgba(255,107,122,0.3);border-radius:8px;padding:14px;margin-top:12px;">' +
            '<div style="font-size:0.65rem;letter-spacing:2px;color:rgba(240,244,248,0.4);text-transform:uppercase;margin-bottom:4px;">\u0391\u03bd\u03b1\u03c7\u03ce\u03c1\u03b7\u03c3\u03b7</div>' +
            '<div style="color:#ff6b7a;font-weight:600;">&#8593; ' + depStr + '</div>' +
        '</div>' +
        '<div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:8px;padding:14px;margin-top:12px;">' +
            '<div style="font-size:0.65rem;letter-spacing:2px;color:rgba(240,244,248,0.4);text-transform:uppercase;margin-bottom:4px;">\u0397\u03bc\u03b5\u03c1\u03bf\u03bc\u03b7\u03bd\u03af\u03b1 \u0391\u03c6\u03b9\u03be\u03b7\u03c2</div>' +
            '<div style="color:#f0f4f8;">&#128197; ' + dateStr + '</div>' +
        '</div>';

    document.getElementById('modalEditBtn').href = 'edit_ship.php?id=' + ship.id;
    new bootstrap.Modal(document.getElementById('shipModal')).show();
}
</script>
</body>
</html>
