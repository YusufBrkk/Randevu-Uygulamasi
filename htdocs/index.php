<?php

include_once './header.php';
include_once './includes/dbh-inc.php';

if (isset($_SESSION["yoneticiID"])) {
    header("Location: admin.php");
    exit();
}
?>

<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet" />

<style>
    body {
        background: linear-gradient(135deg, #e0e7ef 0%, #f8fafc 100%);
    }
    .main-card {
        max-width: 1200px;
        width: 100%;
        border: none;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
        background: rgba(255,255,255,0.95);
    }
    .main-card .btn {
        font-size: 1.15rem;
        padding: 0.9rem 1.2rem;
        display: flex;
        align-items: center;
        gap: 0.7rem;
        transition: background 0.2s, color 0.2s;
    }
    .main-card .btn i {
        font-size: 1.3em;
    }
    .main-card .btn:hover {
        filter: brightness(0.95);
        transform: translateY(-2px) scale(1.03);
    }
    .main-card h1 {
        letter-spacing: 1px;
    }
    .navbar-custom {
        background: rgba(255,255,255,0.98);
        box-shadow: 0 4px 16px 0 rgba(31, 38, 135, 0.10);
        border-bottom: 1px solid #e5e7eb;
        min-height: 64px;
    }
    .navbar-custom .navbar-brand {
        font-weight: 700;
        font-size: 1.35rem;
        letter-spacing: 1px;
        color: #2563eb !important;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .navbar-custom .dropdown-toggle {
        font-weight: 500;
        color: #222;
        background: none;
        border: none;
        box-shadow: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .navbar-custom .dropdown-toggle:after {
        margin-left: 0.4em;
    }
    .navbar-custom .dropdown-menu {
        min-width: 120px;
        border-radius: 0.7rem;
        box-shadow: 0 4px 16px 0 rgba(31, 38, 135, 0.10);
    }
    .navbar-custom .dropdown-item {
        font-size: 1.2rem;
        padding: 0.7rem 1.2rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .navbar-custom .dropdown-item i {
        margin-right: 0.5rem;
        font-size: 1.5em;
    }
    #calendar {
        max-width: 900px;
        margin: 40px auto;
        background: #fff;
        border-radius: 1rem;
        box-shadow: 0 4px 24px 0 rgba(31,38,135,0.10);
        padding: 1.5rem;
    }

    /* SIDEBAR */
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 270px;
        background: #181f2c;
        color: #fff;
        z-index: 1040;
        transition: width 0.25s, left 0.25s;
        box-shadow: 2px 0 16px 0 rgba(31,38,135,0.10);
        display: flex;
        flex-direction: column;
        padding: 0;
    }
    .sidebar.collapsed {
        width: 60px;
        min-width: 60px;
    }
    .sidebar .sidebar-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.1rem 1.1rem 0.7rem 1.1rem;
        border-bottom: 1px solid #232a3b;
        min-height: 60px;
    }
    .sidebar .sidebar-title {
        font-size: 1.13rem;
        font-weight: 700;
        color: #fff;
        letter-spacing: 0.5px;
        white-space: nowrap;
        transition: opacity 0.2s;
    }
    .sidebar.collapsed .sidebar-title {
        opacity: 0;
        width: 0;
        padding: 0;
    }
    .sidebar .sidebar-toggle {
        background: none;
        border: none;
        color: #7dd3fc;
        font-size: 1.4rem;
        cursor: pointer;
        margin-left: 0.5rem;
        transition: color 0.2s;
    }
    .sidebar .sidebar-toggle:hover {
        color: #38bdf8;
    }
    .sidebar .user-section {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        padding: 1rem 1.1rem 0.7rem 1.1rem;
        border-bottom: 1px solid #232a3b;
    }
    .sidebar .user-section i {
        font-size: 1.5rem;
        color: #38bdf8;
    }
    .sidebar .user-section .user-label {
        font-size: 1.01rem;
        font-weight: 500;
        color: #cbd5e1;
        white-space: nowrap;
        transition: opacity 0.2s;
    }
    .sidebar.collapsed .user-section .user-label {
        opacity: 0;
        width: 0;
        padding: 0;
    }
    .sidebar .nav {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
        padding: 1.1rem 0.3rem 0.7rem 0.3rem;
    }
    .sidebar .nav-link {
        color: #cbd5e1;
        background: none;
        border: none;
        border-radius: 0.5rem;
        padding: 0.8rem 1rem;
        font-size: 1.08rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: background 0.18s, color 0.18s;
        text-decoration: none;
        margin-bottom: 0.1rem;
        position: relative;
    }
    .sidebar .nav-link i {
        font-size: 1.25rem;
        min-width: 1.25rem;
        text-align: center;
        color: #38bdf8;
        transition: color 0.18s;
    }
    .sidebar .nav-link.active,
    .sidebar .nav-link:hover {
        background: #232a3b;
        color: #fff;
    }
    .sidebar .nav-link.active i,
    .sidebar .nav-link:hover i {
        color: #fff;
    }
    .sidebar .nav-link .sidebar-link-text {
        transition: opacity 0.2s, width 0.2s;
        white-space: nowrap;
    }
    .sidebar.collapsed .nav-link .sidebar-link-text {
        opacity: 0;
        width: 0;
        padding: 0;
    }
    .sidebar .nav-link.text-danger {
        color: #f87171 !important;
    }
    .sidebar .nav-link.text-danger i {
        color: #f87171 !important;
    }
    .sidebar .sidebar-footer {
        padding: 1rem 1.1rem 1rem 1.1rem;
        border-top: 1px solid #232a3b;
        text-align: center;
        font-size: 0.97rem;
        color: #64748b;
        background: #181f2c;
    }
    .sidebar.collapsed .sidebar-footer {
        display: none;
    }
    @media (max-width: 900px) {
        .sidebar {
            left: -270px;
            box-shadow: 0 0 0 0 transparent;
        }
        .sidebar.open {
            left: 0;
            box-shadow: 2px 0 16px 0 rgba(31,38,135,0.18);
        }
        .sidebar.collapsed {
            left: -60px;
        }
    }
    .sidebar-mobile-toggle {
        display: none;
        position: fixed;
        top: 18px;
        left: 18px;
        z-index: 1100;
        background: #181f2c;
        color: #38bdf8;
        border: none;
        border-radius: 50%;
        width: 44px;
        height: 44px;
        font-size: 1.7rem;
        box-shadow: 0 2px 8px rgba(31,38,135,0.13);
        align-items: center;
        justify-content: center;
        transition: background 0.2s, color 0.2s;
    }
    .sidebar-mobile-toggle:hover {
        background: #232a3b;
        color: #fff;
    }
    @media (max-width: 900px) {
        .sidebar-mobile-toggle {
            display: flex;
        }
    }
    @media (max-width: 800px) {
        .main-card {
            max-width: 98vw;
            padding: 1.2rem 0.5rem 1rem 0.5rem;
        }
    }

    /* MODAL TASARIMI */
    .custom-modal .modal-content {
        border-radius: 1.2rem;
        box-shadow: 0 8px 32px 0 rgba(31,38,135,0.18);
        border: none;
        background: linear-gradient(135deg, #f8fafc 0%, #e0e7ef 100%);
    }
    .custom-modal .modal-header {
        background: linear-gradient(90deg, #2563eb 0%, #38bdf8 100%);
        color: #fff;
        border-top-left-radius: 1.2rem;
        border-top-right-radius: 1.2rem;
        border-bottom: none;
        align-items: center;
    }
    .custom-modal .modal-title {
        font-weight: 700;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: 0.7rem;
    }
    .custom-modal .modal-header .bi {
        font-size: 1.7rem;
        filter: drop-shadow(0 2px 8px #2563eb55);
    }
    .custom-modal .btn-close {
        filter: invert(1) grayscale(1);
        opacity: 0.8;
    }
    .custom-modal .modal-body {
        background: #fff;
        border-bottom-left-radius: 1.2rem;
        border-bottom-right-radius: 1.2rem;
        padding: 1.5rem 1.2rem;
    }
    .custom-modal .list-group-item {
        border: none;
        border-radius: 0.6rem;
        margin-bottom: 0.5rem;
        background: #f1f5f9;
        font-size: 1.08rem;
        font-weight: 500;
        color: #2563eb;
        display: flex;
        align-items: center;
        gap: 0.7rem;
    }
    .custom-modal .list-group-item:last-child {
        margin-bottom: 0;
    }
    @media (max-width: 600px) {
        .custom-modal .modal-content {
            border-radius: 0.7rem;
        }
        .custom-modal .modal-header,
        .custom-modal .modal-body {
            padding-left: 0.7rem;
            padding-right: 0.7rem;
        }
    }

    .dashboard-charts {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(370px, 1fr));
        gap: 2rem;
        width: 100%;
        margin-bottom: 2.5rem;
    }
    .chart-box {
        min-height: 450px;
        padding: 2rem 1.2rem 1.2rem 1.2rem;
        background: #fff;
        border-radius: 1.1rem;
        box-shadow: 0 4px 24px 0 rgba(31,38,135,0.10);
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .chart-title {
        font-size: 1.13rem;
        font-weight: 600;
        color: #2563eb;
        margin-bottom: 1.2rem;
        text-align: center;
        letter-spacing: 0.2px;
    }
</style>

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<?php if (!isset($_SESSION["userID"]) && !isset($_SESSION["yoneticiID"])): ?>

<nav class="navbar navbar-expand-lg navbar-custom px-3">
    <div class="container-fluid">
        <a class="navbar-brand" href="./index.php">
            <i class="bi bi-calendar2-check-fill text-primary"></i> Randevu Sistemi
        </a>
        <div class="d-flex align-items-center ms-auto">
            <?php if (isset($_SESSION["userID"]) || isset($_SESSION["yoneticiID"])): ?>
                <div class="dropdown">
                    <button class="btn dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle text-primary"></i>
                        <?php
                            if (isset($_SESSION["ad_soyad"])) {
                                echo htmlspecialchars($_SESSION["ad_soyad"]);
                            } else if (isset($_SESSION["yoneticiID"])) {
                                echo "Yönetici";
                            }
                        ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item text-danger" href="./includes/signout-inc.php" title="Çıkış">
                                <i class="bi bi-box-arrow-right"></i> Çıkış Yap
                            </a>
                        </li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="signin.php" class="btn btn-outline-primary ms-2 me-2">
                    <i class="bi bi-box-arrow-in-right"></i> Giriş Yap / Kaydol
                </a>
                <a href="admin-login.php" class="btn btn-outline-danger">
                    <i class="bi bi-shield-lock"></i> Admin Girişi
                </a>
            <?php endif; ?>
        </div>
        <div class="ms-3">
            <a href="?lang=tr">TR</a> | <a href="?lang=en">EN</a>
        </div>
    </div>
</nav>
<?php endif; ?>
<?php if (!isset($_SESSION["yoneticiID"]) && isset($_SESSION["userID"])): ?>
    <!-- Mobilde aç/kapa için buton (sidebar'ın dışında!) -->
    <button class="sidebar-mobile-toggle" id="sidebarMobileToggle" title="Menüyü Aç/Kapat">
        <i class="bi bi-list"></i>
    </button>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <span class="sidebar-title">Müşteri Paneli</span>
            <button class="sidebar-toggle" id="sidebarToggle" title="Menüyü Daralt/Aç">
                <i class="bi bi-chevron-double-left"></i>
            </button>
        </div>
        <div class="user-section">
            <i class="bi bi-person-circle"></i>
            <span class="user-label">Müşteri</span>
        </div>
        <nav class="nav flex-column mt-2">
            <a href="index.php" class="nav-link<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? ' active' : '' ?>">
                <i class="bi bi-house"></i>
                <span class="sidebar-link-text">Ana Sayfa</span>
            </a>
            <a href="appts.php" class="nav-link<?= basename($_SERVER['PHP_SELF']) == 'appts.php' ? ' active' : '' ?>">
                <i class="bi bi-calendar2-week"></i>
                <span class="sidebar-link-text">Randevularım</span>
            </a>
            <a href="makeAppt.php" class="nav-link<?= basename($_SERVER['PHP_SELF']) == 'makeAppt.php' ? ' active' : '' ?>">
                <i class="bi bi-plus-circle"></i>
                <span class="sidebar-link-text">Randevu Oluştur</span>
            </a>
            <a href="makeAppt.php" class="nav-link<?= basename($_SERVER['PHP_SELF']) == ' calisma_saatler_musteri.php.php' ? ' active' : '' ?>">
                <i class="bi bi-clock-history"></i>
                <span class="sidebar-link-text">Çalışma Saatleri ve Tatil</span>
            </a>
            <a href="past-appts.php" class="nav-link<?= basename($_SERVER['PHP_SELF']) == 'past-appts.php' ? ' active' : '' ?>">
    <i class="bi bi-clock-history"></i>
    <span class="sidebar-link-text">Geçmiş Randevularım</span>
</a>
        </nav>
        <div class="sidebar-footer mt-auto">
            <a href="./includes/signout-inc.php" class="nav-link text-danger">
                <i class="bi bi-box-arrow-right"></i>
                <span class="sidebar-link-text">Çıkış Yap</span>
            </a>
            <small>Müşteri olarak giriş yaptınız.</small>
        </div>
    </div>
<?php endif; ?>

<div class="container d-flex flex-column align-items-center justify-content-center" style="min-height: 90vh;">
    <div class="card main-card p-5 rounded-4">
        <div class="text-center mb-4">
            <i class="bi bi-calendar2-check-fill text-primary" style="font-size:3rem;"></i>
            <h1 class="fw-bold mt-2 mb-0 fs-1">Randevu Sistemi</h1>
            <div class="text-secondary fs-5 mb-2">Hızlı ve kolay randevu</div>
        </div>
        <?php
// Eğer müşteri girişi yapıldıysa randevu istatistiklerini çek
$toplamRandevu = $onaylananRandevu = $reddedilenRandevu = 0;
if (isset($_SESSION["userID"])) {
    $userID = intval($_SESSION["userID"]);
    $sql = "SELECT 
                COUNT(*) AS toplam,
                SUM(CASE WHEN durum = 'Onaylandı' THEN 1 ELSE 0 END) AS onaylanan,
                SUM(CASE WHEN durum = 'Reddedildi' THEN 1 ELSE 0 END) AS reddedilen
            FROM randevular
            WHERE musteriID = $userID";
    $result = mysqli_query($connection, $sql);
    if ($row = mysqli_fetch_assoc($result)) {
        $toplamRandevu = $row['toplam'];
        $onaylananRandevu = $row['onaylanan'];
        $reddedilenRandevu = $row['reddedilen'];
    }
}
?>

<?php if (isset($_SESSION["userID"])): ?>
<?php
// Randevuları çek
$userID = intval($_SESSION["userID"]);
$randevular = [];
$onaylananlar = [];
$reddedilenler = [];

$sqlAll = "SELECT tarih, saat FROM randevular WHERE musteriID = $userID ORDER BY tarih DESC, saat DESC";
$resultAll = mysqli_query($connection, $sqlAll);
while ($row = mysqli_fetch_assoc($resultAll)) {
    $randevular[] = $row;
}

$sqlOnay = "SELECT tarih, saat FROM randevular WHERE musteriID = $userID AND durum = 'Onaylandı' ORDER BY tarih DESC, saat DESC";
$resultOnay = mysqli_query($connection, $sqlOnay);
while ($row = mysqli_fetch_assoc($resultOnay)) {
    $onaylananlar[] = $row;
}

$sqlRed = "SELECT tarih, saat FROM randevular WHERE musteriID = $userID AND durum = 'Reddedildi' ORDER BY tarih DESC, saat DESC";
$resultRed = mysqli_query($connection, $sqlRed);
while ($row = mysqli_fetch_assoc($resultRed)) {
    $reddedilenler[] = $row;
}
?>
<div class="row w-100 justify-content-center mb-4" style="max-width: 700px; margin: 0 auto;">
    <div class="col-12 col-md-4 mb-2">
        <div class="card text-center shadow-sm border-0" style="background: linear-gradient(90deg, #3b82f6 0%, #06b6d4 100%); color: #fff;">
            <button class="btn w-100 p-0" type="button" data-bs-toggle="modal" data-bs-target="#modalToplam" style="background: transparent; color: inherit; border-radius: .5rem;">
                <div class="card-body py-3">
                    <div class="fs-2 mb-1"><i class="bi bi-list-ul"></i></div>
                    <div class="fw-bold fs-5">Toplam Randevu</div>
                    <div class="fs-4"><?= $toplamRandevu ?></div>
                </div>
            </button>
        </div>
    </div>
    <div class="col-12 col-md-4 mb-2">
        <div class="card text-center shadow-sm border-0" style="background: linear-gradient(90deg, #22c55e 0%, #16a34a 100%); color: #fff;">
            <button class="btn w-100 p-0" type="button" data-bs-toggle="modal" data-bs-target="#modalOnaylanan" style="background: transparent; color: inherit; border-radius: .5rem;">
                <div class="card-body py-3">
                    <div class="fs-2 mb-1"><i class="bi bi-check-circle"></i></div>
                    <div class="fw-bold fs-5">Onaylanan</div>
                    <div class="fs-4"><?= $onaylananRandevu ?></div>
                </div>
            </button>
        </div>
    </div>
    <div class="col-12 col-md-4 mb-2">
        <div class="card text-center shadow-sm border-0" style="background: linear-gradient(90deg, #ef4444 0%, #f87171 100%); color: #fff;">
            <button class="btn w-100 p-0" type="button" data-bs-toggle="modal" data-bs-target="#modalReddedilen" style="background: transparent; color: inherit; border-radius: .5rem;">
                <div class="card-body py-3">
                    <div class="fs-2 mb-1"><i class="bi bi-x-circle"></i></div>
                    <div class="fw-bold fs-5">Reddedilen</div>
                    <div class="fs-4"><?= $reddedilenRandevu ?></div>
                </div>
            </button>
        </div>
    </div>
</div>


<!-- Toplam Randevu Modal -->
<div class="modal fade custom-modal" id="modalToplam" tabindex="-1" aria-labelledby="modalToplamLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalToplamLabel">Tüm Randevularınız</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>
      <div class="modal-body">
        <?php if (count($randevular) > 0): ?>
            <ul class="list-group">
                <?php foreach ($randevular as $r): ?>
                    <li class="list-group-item">
                        <?= htmlspecialchars($r['tarih']) ?> - <?= htmlspecialchars($r['saat']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="alert alert-info mb-0">Henüz randevunuz yok.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Onaylanan Randevu Modal -->
<div class="modal fade custom-modal" id="modalOnaylanan" tabindex="-1" aria-labelledby="modalOnaylananLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalOnaylananLabel">Onaylanan Randevularınız</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>
      <div class="modal-body">
        <?php if (count($onaylananlar) > 0): ?>
            <ul class="list-group">
                <?php foreach ($onaylananlar as $r): ?>
                    <li class="list-group-item">
                        <?= htmlspecialchars($r['tarih']) ?> - <?= htmlspecialchars($r['saat']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="alert alert-info mb-0">Onaylanan randevunuz yok.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Reddedilen Randevu Modal -->
<div class="modal fade custom-modal" id="modalReddedilen" tabindex="-1" aria-labelledby="modalReddedilenLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalReddedilenLabel">Reddedilen Randevularınız</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>
      <div class="modal-body">
        <?php if (count($reddedilenler) > 0): ?>
            <ul class="list-group">
                <?php foreach ($reddedilenler as $r): ?>
                    <li class="list-group-item">
                        <?= htmlspecialchars($r['tarih']) ?> - <?= htmlspecialchars($r['saat']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="alert alert-info mb-0">Reddedilen randevunuz yok.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
   

<?php endif; ?>
<?php
if (isset($_SESSION["userID"])) {
    $userID = intval($_SESSION["userID"]);
    $today = new DateTime();

    // 1. Son 7 gün
    $weekLabels = [];
    $weekDataUser = [];
    $weekDataAll = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = clone $today;
        $date->modify("-$i day");
        $label = $date->format('d.m');
        $sqlDate = $date->format('Y-m-d');
        $weekLabels[] = $label;
        $weekDataUser[] = (int)mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as cnt FROM randevular WHERE musteriID=$userID AND tarih='$sqlDate'"))["cnt"];
        $weekDataAll[] = (int)mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as cnt FROM randevular WHERE tarih='$sqlDate'"))["cnt"];
    }

    // 2. Son 12 ay
    $monthLabels = [];
    $monthDataUser = [];
    $monthDataAll = [];
    for ($i = 11; $i >= 0; $i--) {
        $date = new DateTime("first day of -$i month");
        $label = $date->format('m.Y');
        $sqlMonth = $date->format('Y-m');
        $monthLabels[] = $label;
        $monthDataUser[] = (int)mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as cnt FROM randevular WHERE musteriID=$userID AND DATE_FORMAT(tarih, '%Y-%m')='$sqlMonth'"))["cnt"];
        $monthDataAll[] = (int)mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as cnt FROM randevular WHERE DATE_FORMAT(tarih, '%Y-%m')='$sqlMonth'"))["cnt"];
    }

    // 3. Durumlara göre dağılım
    $onaylananCountUser = (int)mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as cnt FROM randevular WHERE musteriID=$userID AND durum='Onaylandı'"))["cnt"];
    $bekleyenCountUser = (int)mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as cnt FROM randevular WHERE musteriID=$userID AND durum='Bekliyor'"))["cnt"];
    $reddedilenCountUser = (int)mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as cnt FROM randevular WHERE musteriID=$userID AND durum='Reddedildi'"))["cnt"];
    $onaylananCountAll = (int)mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as cnt FROM randevular WHERE durum='Onaylandı'"))["cnt"];
    $bekleyenCountAll = (int)mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as cnt FROM randevular WHERE durum='Bekliyor'"))["cnt"];
    $reddedilenCountAll = (int)mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as cnt FROM randevular WHERE durum='Reddedildi'"))["cnt"];

    // 4. En yoğun saatler (top 5)
    $peakHoursUser = [];
    $peakHoursDataUser = [];
    $res = mysqli_query($connection, "SELECT saat, COUNT(*) as cnt FROM randevular WHERE musteriID=$userID GROUP BY saat ORDER BY cnt DESC LIMIT 5");
    while ($row = mysqli_fetch_assoc($res)) {
        $peakHoursUser[] = $row["saat"];
        $peakHoursDataUser[] = (int)$row["cnt"];
    }
    $peakHoursAll = [];
    $peakHoursDataAll = [];
    $res = mysqli_query($connection, "SELECT saat, COUNT(*) as cnt FROM randevular GROUP BY saat ORDER BY cnt DESC LIMIT 5");
    while ($row = mysqli_fetch_assoc($res)) {
        $peakHoursAll[] = $row["saat"];
        $peakHoursDataAll[] = (int)$row["cnt"];
    }

    // 5. En yoğun günler (top 5)
    $peakDaysUser = [];
    $peakDaysDataUser = [];
    $res = mysqli_query($connection, "SELECT tarih, COUNT(*) as cnt FROM randevular WHERE musteriID=$userID GROUP BY tarih ORDER BY cnt DESC LIMIT 5");
    while ($row = mysqli_fetch_assoc($res)) {
        $peakDaysUser[] = date("d.m.Y", strtotime($row["tarih"]));
        $peakDaysDataUser[] = (int)$row["cnt"];
    }
    $peakDaysAll = [];
    $peakDaysDataAll = [];
    $res = mysqli_query($connection, "SELECT tarih, COUNT(*) as cnt FROM randevular GROUP BY tarih ORDER BY cnt DESC LIMIT 5");
    while ($row = mysqli_fetch_assoc($res)) {
        $peakDaysAll[] = date("d.m.Y", strtotime($row["tarih"]));
        $peakDaysDataAll[] = (int)$row["cnt"];
    }

    // 6. Kullanıcı başına randevu (top 7, sadece tüm kullanıcılar için)
    $userAvgLabels = [];
    $userAvgData = [];
    $res = mysqli_query($connection, "SELECT m.ad_soyad, COUNT(r.randevuID) as cnt FROM musteriler m LEFT JOIN randevular r ON m.musteriID = r.musteriID GROUP BY m.musteriID ORDER BY cnt DESC LIMIT 7");
    while ($row = mysqli_fetch_assoc($res)) {
        $userAvgLabels[] = $row["ad_soyad"];
        $userAvgData[] = (int)$row["cnt"];
    }
}
?>

<?php if (isset($_SESSION["userID"])): ?>
<div class="mb-4 w-100" style="max-width:700px; margin:0 auto;">
    <div class="d-flex gap-3 align-items-center justify-content-end">
        <label class="fw-bold mb-0">Gösterim:</label>
        <select id="chartViewSelect" class="form-select w-auto" style="min-width:220px;">
            <option value="user" selected>Sadece kendi randevularım</option>
            <option value="all">Tüm kullanıcıların randevuları</option>
        </select>
    </div>
</div>
<div class="dashboard-charts mb-5"
     style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 2rem; width: 100%; margin-bottom: 2.5rem;">
    <div class="chart-box">
        <div class="chart-title">Son 7 Günlük Randevu</div>
        <canvas id="weeklyChart" height="200"></canvas>
    </div>
    <div class="chart-box">
        <div class="chart-title">Durum Dağılımı</div>
        <canvas id="statusChart" height="200"></canvas>
    </div>
    <div class="chart-box">
        <div class="chart-title">Aylık Randevu (12 Ay)</div>
        <canvas id="monthlyChart" height="200"></canvas>
    </div>
    <div class="chart-box">
        <div class="chart-title">En Yoğun Saatler</div>
        <canvas id="peakHourChart" height="200"></canvas>
    </div>
    <div class="chart-box">
        <div class="chart-title">En Yoğun Günler</div>
        <canvas id="peakDayChart" height="200"></canvas>
    </div>
    <div class="chart-box" id="userAvgChartBox">
        <div class="chart-title">Kullanıcı Başına Randevu</div>
        <canvas id="userAvgChart" height="200"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const weekLabels = <?= json_encode($weekLabels) ?>;
const monthLabels = <?= json_encode($monthLabels) ?>;

// Kullanıcıya özel
const weekDataUser = <?= json_encode($weekDataUser) ?>;
const monthDataUser = <?= json_encode($monthDataUser) ?>;
const statusDataUser = [<?= $onaylananCountUser ?>, <?= $bekleyenCountUser ?>, <?= $reddedilenCountUser ?>];
const peakHoursUser = <?= json_encode($peakHoursUser) ?>;
const peakHoursDataUser = <?= json_encode($peakHoursDataUser) ?>;
const peakDaysUser = <?= json_encode($peakDaysUser) ?>;
const peakDaysDataUser = <?= json_encode($peakDaysDataUser) ?>;

// Tüm kullanıcılar
const weekDataAll = <?= json_encode($weekDataAll) ?>;
const monthDataAll = <?= json_encode($monthDataAll) ?>;
const statusDataAll = [<?= $onaylananCountAll ?>, <?= $bekleyenCountAll ?>, <?= $reddedilenCountAll ?>];
const peakHoursAll = <?= json_encode($peakHoursAll) ?>;
const peakHoursDataAll = <?= json_encode($peakHoursDataAll) ?>;
const peakDaysAll = <?= json_encode($peakDaysAll) ?>;
const peakDaysDataAll = <?= json_encode($peakDaysDataAll) ?>;
const userAvgLabels = <?= json_encode($userAvgLabels) ?>;
const userAvgData = <?= json_encode($userAvgData) ?>;

let weeklyChart, statusChart, monthlyChart, peakHourChart, peakDayChart, userAvgChart;

function renderCharts(view) {
    // Haftalık
    if (weeklyChart) weeklyChart.destroy();
    weeklyChart = new Chart(document.getElementById('weeklyChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: weekLabels,
            datasets: [{
                label: 'Randevu',
                data: view === 'user' ? weekDataUser : weekDataAll,
                backgroundColor: '#3b82f6',
                borderRadius: 8,
                maxBarThickness: 24
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false }, tooltip: { enabled: true } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    // Durum dağılımı
    if (statusChart) statusChart.destroy();
    statusChart = new Chart(document.getElementById('statusChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ["Onaylandı", "Bekliyor", "Reddedildi"],
            datasets: [{
                data: view === 'user' ? statusDataUser : statusDataAll,
                backgroundColor: ['#22c55e', '#fbbf24', '#ef4444'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true, position: 'bottom' },
                tooltip: { enabled: true }
            },
            cutout: '65%'
        }
    });

    // Aylık
    if (monthlyChart) monthlyChart.destroy();
    monthlyChart = new Chart(document.getElementById('monthlyChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: monthLabels,
            datasets: [{
                label: 'Randevu',
                data: view === 'user' ? monthDataUser : monthDataAll,
                backgroundColor: '#06b6d4',
                borderRadius: 8,
                maxBarThickness: 24
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    // En yoğun saatler
    if (peakHourChart) peakHourChart.destroy();
    peakHourChart = new Chart(document.getElementById('peakHourChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: view === 'user' ? peakHoursUser : peakHoursAll,
            datasets: [{
                label: 'Randevu',
                data: view === 'user' ? peakHoursDataUser : peakHoursDataAll,
                backgroundColor: '#f59e42',
                borderRadius: 8,
                maxBarThickness: 24
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    // En yoğun günler
    if (peakDayChart) peakDayChart.destroy();
    peakDayChart = new Chart(document.getElementById('peakDayChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: view === 'user' ? peakDaysUser : peakDaysAll,
            datasets: [{
                label: 'Randevu',
                data: view === 'user' ? peakDaysDataUser : peakDaysDataAll,
                backgroundColor: '#a78bfa',
                borderRadius: 8,
                maxBarThickness: 24
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    // Kullanıcı başına randevu (sadece tüm kullanıcılar için göster)
    const userAvgBox = document.getElementById('userAvgChartBox');
    if (view === 'all') {
        userAvgBox.style.display = '';
        if (userAvgChart) userAvgChart.destroy();
        userAvgChart = new Chart(document.getElementById('userAvgChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: userAvgLabels,
                datasets: [{
                    label: 'Randevu',
                    data: userAvgData,
                    backgroundColor: '#2563eb',
                    borderRadius: 8,
                    maxBarThickness: 24
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    } else {
        userAvgBox.style.display = 'none';
        if (userAvgChart) userAvgChart.destroy();
    }
}

document.getElementById('chartViewSelect').addEventListener('change', function() {
    renderCharts(this.value);
});
renderCharts('user');
</script>
<?php endif; ?>
       
        <?php
        if (isset($_GET["error"])) {
            if ($_GET["error"] === 'stmtfail') {
                echo '<div class="alert alert-danger mt-4 fw-bold text-center">Veritabanı bağlantı hatası!</div>';
            }
        }
        ?>
        <div id="calendar"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarMobileToggle = document.getElementById('sidebarMobileToggle');
    let isCollapsed = false;

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            isCollapsed = !isCollapsed;
            sidebar.classList.toggle('collapsed', isCollapsed);
            sidebarToggle.innerHTML = isCollapsed
                ? '<i class="bi bi-chevron-double-right"></i>'
                : '<i class="bi bi-chevron-double-left"></i>';
        });
    }

    if (sidebarMobileToggle) {
        sidebarMobileToggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
        });
    }

    document.addEventListener('click', function (e) {
        if (window.innerWidth <= 900 && sidebar && sidebar.classList.contains('open')) {
            if (!sidebar.contains(e.target) && (!sidebarMobileToggle || !sidebarMobileToggle.contains(e.target))) {
                sidebar.classList.remove('open');
            }
        }
    });
});
</script>

<?php
include_once './footer.php';
?>


