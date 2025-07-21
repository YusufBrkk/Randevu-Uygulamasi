<?php
include_once './header.php';

if (!isset($_SESSION["yoneticiID"])) {
    header("Location: ./admin-login.php");
    exit();
}


include_once './includes/functions-inc.php';


// Toplam müşteri ve randevu
$totalUsers = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as cnt FROM musteriler"))["cnt"];
$totalAppts = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as cnt FROM randevular"))["cnt"];

// Haftalık randevu verisi (son 7 gün)
$weekLabels = [];
$weekData = [];
$today = new DateTime();
for ($i = 6; $i >= 0; $i--) {
    $date = clone $today;
    $date->modify("-$i day");
    $label = $date->format('d.m');
    $sqlDate = $date->format('Y-m-d');
    $count = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as cnt FROM randevular WHERE tarih='$sqlDate'"))["cnt"];
    $weekLabels[] = $label;
    $weekData[] = $count;
}

// Aylık randevu sayısı (son 12 ay)
$monthLabels = [];
$monthData = [];
for ($i = 11; $i >= 0; $i--) {
    $date = new DateTime("first day of -$i month");
    $label = $date->format('m.Y');
    $sqlMonth = $date->format('Y-m');
    $count = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as cnt FROM randevular WHERE DATE_FORMAT(tarih, '%Y-%m')='$sqlMonth'"))["cnt"];
    $monthLabels[] = $label;
    $monthData[] = $count;
}

// En yoğun saatler (tüm zamanlar)
$hourRows = mysqli_query($connection, "SELECT saat, COUNT(*) as cnt FROM randevular GROUP BY saat ORDER BY cnt DESC LIMIT 5");
$peakHours = [];
$peakHoursData = [];
while ($row = mysqli_fetch_assoc($hourRows)) {
    $peakHours[] = $row["saat"];
    $peakHoursData[] = $row["cnt"];
}

// En yoğun günler (tüm zamanlar)
$dayRows = mysqli_query($connection, "SELECT tarih, COUNT(*) as cnt FROM randevular GROUP BY tarih ORDER BY cnt DESC LIMIT 5");
$peakDays = [];
$peakDaysData = [];
while ($row = mysqli_fetch_assoc($dayRows)) {
    $peakDays[] = date("d.m.Y", strtotime($row["tarih"]));
    $peakDaysData[] = $row["cnt"];
}

// Kullanıcı başına ortalama randevu
$userAvgRows = mysqli_query($connection, "SELECT m.ad_soyad, COUNT(r.randevuID) as cnt FROM musteriler m LEFT JOIN randevular r ON m.musteriID = r.musteriID GROUP BY m.musteriID ORDER BY cnt DESC LIMIT 7");
$userAvgLabels = [];
$userAvgData = [];
while ($row = mysqli_fetch_assoc($userAvgRows)) {
    $userAvgLabels[] = $row["ad_soyad"];
    $userAvgData[] = $row["cnt"];
}

// En yoğun gün (tüm zamanlar)
$maxDayRow = mysqli_fetch_assoc(mysqli_query($connection, "
    SELECT tarih, COUNT(*) as cnt 
    FROM randevular 
    GROUP BY tarih 
    ORDER BY cnt DESC, tarih DESC 
    LIMIT 1
"));
$maxDay = $maxDayRow ? $maxDayRow["tarih"] : "-";
$maxDayCount = $maxDayRow ? $maxDayRow["cnt"] : 0;

// Durumlara göre dağılım
$onaylananCount = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as cnt FROM randevular WHERE durum='Onaylandı'"))["cnt"];
$bekleyenCount = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as cnt FROM randevular WHERE durum='Bekliyor'"))["cnt"];
$reddedilenCount = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as cnt FROM randevular WHERE durum='Reddedildi'"))["cnt"];

// Onaylanan randevular
$onaylananRandevular = [];
$res = mysqli_query($connection, "SELECT m.ad_soyad, r.tarih, r.saat FROM randevular r INNER JOIN musteriler m ON r.musteriID = m.musteriID WHERE r.durum='Onaylandı'");
while ($row = mysqli_fetch_assoc($res)) {
    $onaylananRandevular[] = $row;
}

// Reddedilen randevular
$reddedilenRandevular = [];
$res = mysqli_query($connection, "SELECT m.ad_soyad, r.tarih, r.saat FROM randevular r INNER JOIN musteriler m ON r.musteriID = m.musteriID WHERE r.durum='Reddedildi'");
while ($row = mysqli_fetch_assoc($res)) {
    $reddedilenRandevular[] = $row;
}

// Bekleyen randevular
$bekleyenRandevular = [];
$res = mysqli_query($connection, "SELECT m.ad_soyad, r.tarih, r.saat FROM randevular r INNER JOIN musteriler m ON r.musteriID = m.musteriID WHERE r.durum='Bekliyor'");
while ($row = mysqli_fetch_assoc($res)) {
    $bekleyenRandevular[] = $row;
}

// Kayıtlı müşteriler (ad soyad ve telefon)
$musteriler = [];
$res = mysqli_query($connection, "SELECT ad_soyad, telefon FROM musteriler");
while ($row = mysqli_fetch_assoc($res)) {
    $musteriler[] = $row;
}
?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const musterilerData = <?= json_encode($musteriler) ?>;

const randevuData = {
    onaylanan: <?= json_encode($onaylananRandevular) ?>,
    reddedilen: <?= json_encode($reddedilenRandevular) ?>,
    bekleyen: <?= json_encode($bekleyenRandevular) ?>
};
</script>

<style>
/* ... (mevcut CSS kodların burada kalacak, değişiklik yok) ... */
</style>

<!-- Sidebar Aç/Kapa Butonu (Mobil) -->
<button class="sidebar-mobile-toggle" id="sidebarMobileToggle" title="Menüyü Aç/Kapat">
    <i class="bi bi-list"></i>
</button>

<!-- Sidebar -->
<?php if (isset($_SESSION["yoneticiID"])): ?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <span class="sidebar-title">Admin Paneli</span>
        <button class="sidebar-toggle" id="sidebarToggle" title="Menüyü Daralt/Aç">
            <i class="bi bi-chevron-double-left"></i>
        </button>
    </div>
    <div class="user-section">
        <i class="bi bi-person-circle"></i>
        <span class="user-label">Yönetici</span>
    </div>
    <nav class="nav flex-column mt-2">
        <a href="admin.php" class="nav-link<?= basename($_SERVER['PHP_SELF']) == 'admin.php' ? ' active' : '' ?>">
            <i class="bi bi-bar-chart"></i>
            <span class="sidebar-link-text">Gösterge Paneli</span>
        </a>
        <a href="appts.php" class="nav-link<?= basename($_SERVER['PHP_SELF']) == 'appts.php' ? ' active' : '' ?>">
            <i class="bi bi-calendar2-week"></i>
            <span class="sidebar-link-text">Randevuları Yönet</span>
        </a>
        <a href="users.php" class="nav-link<?= basename($_SERVER['PHP_SELF']) == 'users.php' ? ' active' : '' ?>">
            <i class="bi bi-people"></i>
            <span class="sidebar-link-text">Müşterileri Görüntüle</span>
        </a>
        <a href="calisma_saatleri.php" class="nav-link<?= basename($_SERVER['PHP_SELF']) == 'calisma_saatleri.php' ? ' active' : '' ?>">
            <i class="bi bi-clock-history"></i>
            <span class="sidebar-link-text">Çalışma Saatleri & Tatil</span>
        </a>
    </nav>
    <div class="sidebar-footer mt-auto">
        <a href="./includes/signout-inc.php" class="nav-link text-danger">
            <i class="bi bi-box-arrow-right"></i>
            <span class="sidebar-link-text">Çıkış Yap</span>
        </a>
        <small>Yönetici olarak giriş yaptınız.</small>
    </div>
</div>
<?php endif; ?>

<div class="main-content">

<div class="main-content">
    <div class="dashboard-container">

        <!-- Üst Menü ve Kullanıcı -->
        <div class="dashboard-header">
            <div>
                <h2 style="font-weight:700;display:flex;align-items:center;gap:0.5rem;">
                    <i class="bi bi-bar-chart-line" style="color:#5b4ee6;font-size:2rem;"></i>
                    Yönetici Paneli
                </h2>
               
            </div>
            <div class="user-info">
                <div class="avatar"><i class="bi bi-person"></i></div>
                <div class="name">Merhaba, Admin</div>
            </div>
        </div>

        <!-- Tab Menü -->
        <div class="dashboard-tabs">
            <button class="tab-btn active">Bugün</button>
            <button class="tab-btn">Dün</button>
            <button class="tab-btn">Yarın</button>
            <button class="tab-btn">Bu hafta</button>
            <button class="tab-btn">Geçen hafta</button>
            <button class="tab-btn">Bu ay</button>
            <button class="tab-btn">Bu yıl</button>
            <button class="tab-btn">Özel</button>
        </div>
        <div class="dashboard-summary">
            <!-- ... (mevcut özet kutuların burada kalacak) ... -->
            <div class="summary-box" onclick="showModal('musteri')">
                <span class="icon"><i class="bi bi-people-fill"></i></span>
                <span class="value"><?= $totalUsers ?></span>
                <span class="label">Kayıtlı Müşteri</span>
            </div>
            <div class="summary-box onaylanan" onclick="showRandevuModal('onaylanan')">
    <span class="icon"><i class="bi bi-check-circle"></i></span>
    <span class="value"><?= $onaylananCount ?></span>
    <span class="label">Onaylanan</span>
</div>
            <div class="summary-box bekleyen" onclick="showRandevuModal('bekleyen')">
    <span class="icon"><i class="bi bi-hourglass-split"></i></span>
    <span class="value"><?= $bekleyenCount ?></span>
    <span class="label">Bekleyen</span>
</div>
            <div class="summary-box reddedilen" onclick="showRandevuModal('reddedilen')">
    <span class="icon"><i class="bi bi-x-circle"></i></span>
    <span class="value"><?= $reddedilenCount ?></span>
    <span class="label">Reddedilen</span>
</div>
            <div class="summary-box" style="border-left-color:#3b82f6;">
                <span class="icon"><i class="bi bi-calendar2-week"></i></span>
                <span class="value"><?= $totalAppts ?></span>
                <span class="label">Toplam Randevu</span>
            </div>
            <div class="summary-box busy" style="cursor:default;">
                <span class="icon"><i class="bi bi-calendar-event"></i></span>
                <span class="value"><?= $maxDayCount ?></span>
                <span class="label">En Yoğun Gün</span>
                <span class="sub-label"><?= $maxDay !== "-" ? date("d.m.Y", strtotime($maxDay)) : "-" ?></span>
            </div>
        </div>
        <!-- Kartlar -->
        <div class="dashboard-cards">
            <div class="dashboard-card" onclick="showModal('randevular')">
                <div class="icon bg1"><i class="bi bi-person"></i></div>
                <div class="info">
                    <div class="value"><?= $totalAppts ?></div>
                    <div class="label">RANDEVULAR</div>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="icon bg2"><i class="bi bi-clock-history"></i></div>
                <div class="info">
                    <div class="value"><?= $peakHoursData[0] ?? 0 ?></div>
                    <div class="label">SÜRELER</div>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="icon bg3"><i class="bi bi-currency-bitcoin"></i></div>
                <div class="info">
                    <div class="value">₺0.00</div>
                    <div class="label">GELİR</div>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="icon bg1"><i class="bi bi-person"></i></div>
                <div class="info">
                    <div class="value"><?= $totalUsers ?></div>
                    <div class="label">YENİ MÜŞTERİLER</div>
                </div>
            </div>
        </div>

        <!-- İstatistikler ve Grafik -->
        <div class="dashboard-stats-row">
            <div class="dashboard-stats">
                <div class="stats-title">RANDEVUNUN HIZLI İSTATİSTİKLERİ</div>
                <ul class="stats-list">
                    <li><span class="icon waiting"><i class="bi bi-hourglass-split"></i></span>Bekleyen <span style="margin-left:auto;"><?= $bekleyenCount ?></span></li>
                    <li><span class="icon approved"><i class="bi bi-check2-circle"></i></span>Onaylandı <span style="margin-left:auto;"><?= $onaylananCount ?></span></li>
                    <li><span class="icon cancel"><i class="bi bi-x-circle"></i></span>İptal Et <span style="margin-left:auto;">0</span></li>
                    <li><span class="icon rejected"><i class="bi bi-x-octagon"></i></span>Reddedildi <span style="margin-left:auto;"><?= $reddedilenCount ?></span></li>
                </ul>
            </div>
            <div class="dashboard-graph">
                <div class="graph-header">
                    <div class="graph-title">GRAFİK</div>
                    <div class="graph-tabs">
                        <button class="graph-tab active">Son 1 yıl</button>
                        <button class="graph-tab">2019</button>
                        <button class="graph-tab">2020</button>
                        <button class="graph-tab">2021</button>
                        <button class="graph-tab">2022</button>
                        <button class="graph-tab">2023</button>
                    </div>
                </div>
                <div class="graph-body">
                    <!-- Chart.js ile doldurulacak -->
                    <canvas id="monthlyChart" height="120"></canvas>
                </div>
                <div class="graph-labels">
                    <span>Mart</span><span>Nisan</span><span>Mayıs</span><span>Haz</span><span>Tem</span><span>Ağu</span><span>Eyl</span><span>Eki</span><span>Kas</span><span>Aralık</span><span>Ocak</span>
                </div>
            </div>
             <div class="dashboard-charts">
            <!-- ... (mevcut chart kutuların burada kalacak) ... -->
            <div class="chart-box">
                <div class="chart-title">Son 7 Günlük Randevu Dağılımı</div>
                <canvas id="weeklyChart" height="180"></canvas>
            </div>
            <div class="chart-box">
                <div class="chart-title">Randevu Durumlarına Göre Dağılım</div>
                <canvas id="statusChart" height="180"></canvas>
            </div>
            <div class="chart-box">
                <div class="chart-title">Aylık Randevu Sayısı (Son 12 Ay)</div>
                <canvas id="monthlyChart" height="180"></canvas>
            </div>
            <div class="chart-box">
                <div class="chart-title">En Yoğun Saatler (Top 5)</div>
                <canvas id="peakHourChart" height="180"></canvas>
            </div>
            <div class="chart-box">
                <div class="chart-title">En Yoğun Günler (Top 5)</div>
                <canvas id="peakDayChart" height="180"></canvas>
            </div>
            <div class="chart-box">
                <div class="chart-title">Kullanıcı Başına Randevu (Top 7)</div>
                <canvas id="userAvgChart" height="180"></canvas>
            </div>
        </div>
        <div class="text-center text-muted mt-4">
             </div>
        </div>
    </div>
</div>
  

        
       
    </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="summaryModal" tabindex="-1" aria-labelledby="summaryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-gradient-primary text-white" style="background: linear-gradient(90deg, #3b82f6 0%, #06b6d4 100%);">
        <h5 class="modal-title" id="summaryModalLabel">
          <i class="bi bi-people-fill"></i> Kayıtlı Müşteriler
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-striped align-middle mb-0 rounded-3 overflow-hidden">
            <thead class="table-light">
              <tr>
                <th scope="col"><i class="bi bi-person"></i> Ad Soyad</th>
                <th scope="col"><i class="bi bi-telephone"></i> Telefon</th>
              </tr>
            </thead>
            <tbody id="summaryModalList">
              <!-- JS ile doldurulacak -->
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-outline-primary w-100" data-bs-dismiss="modal">
          <i class="bi bi-x-circle"></i> Kapat
        </button>
      </div>
    </div>
  </div>
</div>
<script>

function showModal(type) {
    const modalTitle = document.getElementById('summaryModalLabel');
    const modalList = document.getElementById('summaryModalList');
    modalList.innerHTML = '';
    if (type === 'musteri') {
        modalTitle.innerHTML = '<i class="bi bi-people-fill"></i> Kayıtlı Müşteriler';
        if (musterilerData.length === 0) {
            modalList.innerHTML = '<tr><td colspan="2" class="text-center text-muted">Kayıtlı müşteri yok.</td></tr>';
        } else {
            musterilerData.forEach(function(item) {
                modalList.innerHTML += `<tr>
                    <td>${item.ad_soyad}</td>
                    <td>${item.telefon ?? '-'}</td>
                </tr>`;
            });
        }
    } else {
        modalTitle.innerHTML = '';
        modalList.innerHTML = '';
    }
    var modal = new bootstrap.Modal(document.getElementById('summaryModal'));
    modal.show();
}

function showRandevuModal(type) {
    const data = randevuData[type];
    const tbody = document.getElementById('randevuModalBody');
    tbody.innerHTML = '';
    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Kayıt yok</td></tr>';
    } else {
        data.forEach(function(item) {
            tbody.innerHTML += `<tr>
                <td>${item.ad_soyad}</td>
                <td>${item.tarih}</td>
                <td>${item.saat}</td>
            </tr>`;
        });
    }
    var modal = new bootstrap.Modal(document.getElementById('randevuModal'));
    modal.show();
}

const weekLabels = <?= json_encode($weekLabels) ?>;
const weekData = <?= json_encode($weekData) ?>;
const monthLabels = <?= json_encode($monthLabels) ?>;
const monthData = <?= json_encode($monthData) ?>;

new Chart(document.getElementById('weeklyChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: weekLabels,
        datasets: [{
            label: 'Randevu Sayısı',
            data: weekData,
            backgroundColor: '#5b4ee6',
            borderRadius: 8,
            maxBarThickness: 38
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

new Chart(document.getElementById('monthlyChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: monthLabels,
        datasets: [{
            label: 'Aylık Randevu',
            data: monthData,
            backgroundColor: '#1cc8c8',
            borderRadius: 8,
            maxBarThickness: 38
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

const sidebar = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebarMobileToggle = document.getElementById('sidebarMobileToggle');
let isCollapsed = false;

sidebarToggle.addEventListener('click', function () {
    isCollapsed = !isCollapsed;
    sidebar.classList.toggle('collapsed', isCollapsed);
    sidebarToggle.innerHTML = isCollapsed
        ? '<i class="bi bi-chevron-double-right"></i>'
        : '<i class="bi bi-chevron-double-left"></i>';
});

sidebarMobileToggle.addEventListener('click', function () {
    sidebar.classList.toggle('open');
});

document.addEventListener('click', function (e) {
    if (window.innerWidth <= 900 && sidebar.classList.contains('open')) {
        if (!sidebar.contains(e.target) && !sidebarMobileToggle.contains(e.target)) {
            sidebar.classList.remove('open');
        }
    }
});

document.querySelectorAll('.sidebar .nav-link').forEach(function(link) {
    if (window.location.pathname.endsWith(link.getAttribute('href'))) {
        link.classList.add('active');
    }
});
</script>
<div class="modal fade" id="randevuModal" tabindex="-1" aria-labelledby="randevuModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header bg-gradient-primary text-white" style="background: linear-gradient(90deg, #3b82f6 0%, #06b6d4 100%);">
        <h5 class="modal-title d-flex align-items-center gap-2" id="randevuModalLabel">
          <i class="bi bi-calendar2-week"></i> Randevu Detayları
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3 text-secondary" style="font-size:1.08rem;">
          Aşağıda seçtiğiniz kategoriye ait randevuların <b>Ad Soyad</b>, <b>Tarih</b> ve <b>Saat</b> bilgilerini görebilirsiniz.
        </div>
        <div class="table-responsive">
          <table class="table table-striped align-middle mb-0 rounded-3 overflow-hidden">
            <thead class="table-light">
              <tr>
                <th scope="col"><i class="bi bi-person"></i> Ad Soyad</th>
                <th scope="col"><i class="bi bi-calendar"></i> Tarih</th>
                <th scope="col"><i class="bi bi-clock"></i> Saat</th>
              </tr>
            </thead>
            <tbody id="randevuModalBody">
              <!-- JS ile doldurulacak -->
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-outline-primary w-100" data-bs-dismiss="modal">
          <i class="bi bi-x-circle"></i> Kapat
        </button>
      </div>
    </div>
  </div>
</div>




<?php
include_once './footer.php';
?>
