<?php
include_once './header.php';
include_once './includes/functions-inc.php';

if (!(isset($_SESSION["userID"]) || isset($_SESSION["yoneticiID"]))) {
    header("Location: ./signin.php");
    exit();
}

// Admin yeni randevu ekleme işlemi
if (isset($_SESSION["yoneticiID"]) && isset($_POST["add_appt"])) {
    $ad = trim($_POST["new_ad"]);
    $soyad = trim($_POST["new_soyad"]);
    $tarih = $_POST["new_tarih"];
    $saat = $_POST["new_saat"];

    // Müşteri var mı kontrol et, yoksa ekle
    $ad_soyad = $ad . " " . $soyad;
    $stmt = mysqli_prepare($connection, "SELECT musteriID FROM musteriler WHERE ad_soyad = ?");
    mysqli_stmt_bind_param($stmt, "s", $ad_soyad);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        $musteriID = $row["musteriID"];
    } else {
        $stmt2 = mysqli_prepare($connection, "INSERT INTO musteriler (ad_soyad) VALUES (?)");
        mysqli_stmt_bind_param($stmt2, "s", $ad_soyad);
        mysqli_stmt_execute($stmt2);
        $musteriID = mysqli_insert_id($connection);
    }

    // Randevu ekle
    $stmt3 = mysqli_prepare($connection, "INSERT INTO randevular (musteriID, tarih, saat, durum) VALUES (?, ?, ?, 'Bekliyor')");
    mysqli_stmt_bind_param($stmt3, "iss", $musteriID, $tarih, $saat);
    mysqli_stmt_execute($stmt3);

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

// Randevu durumu güncelleme işlemi (admin için)
if (isset($_SESSION["yoneticiID"]) && isset($_POST["action"]) && isset($_POST["randevuID"])) {
    $randevuID = intval($_POST["randevuID"]);
    $action = $_POST["action"];
    if ($action === "onayla") {
        mysqli_query($connection, "UPDATE randevular SET durum='Onaylandı' WHERE randevuID=$randevuID");
    } elseif ($action === "reddet") {
        mysqli_query($connection, "UPDATE randevular SET durum='Reddedildi' WHERE randevuID=$randevuID");
    }
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

// Kullanıcıya randevu reddedildiyse bildirim göster
$showRedAlert = false;
if (isset($_SESSION["userID"])) {
    $uid = $_SESSION["userID"];
    $result = mysqli_query($connection, "SELECT durum FROM randevular WHERE musteriID=$uid ORDER BY randevuID DESC LIMIT 1");
    if ($row = mysqli_fetch_assoc($result)) {
        if ($row["durum"] === "Reddedildi") {
            $showRedAlert = true;
        }
    }
}

// Kullanıcı randevu iptal etme işlemi
if (isset($_POST['cancel_randevuID']) && isset($_POST['cancel_reason']) && isset($_SESSION["userID"])) {
    $randevuID = intval($_POST['cancel_randevuID']);
    $reason = trim($_POST['cancel_reason']);
    $userID = intval($_SESSION["userID"]);
    // Sadece kendi randevusunu iptal edebilsin
    $stmt = mysqli_prepare($connection, "UPDATE randevular SET durum='Reddedildi', iptal_nedeni=? WHERE randevuID=? AND musteriID=?");
    mysqli_stmt_bind_param($stmt, "sii", $reason, $randevuID, $userID);
    mysqli_stmt_execute($stmt);
    header("Location: appts.php?error=sccssfldelete");
    exit();
}
?>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<style>
    body {
        background: linear-gradient(120deg, #f8fafc 0%, #e0e7ef 100%);
        min-height: 100vh;
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

    .main-content {
        margin-left: 270px;
        transition: margin-left 0.25s;
        padding: 0;
        background: transparent;
    }
    .sidebar.collapsed ~ .main-content {
        margin-left: 60px;
    }
    @media (max-width: 900px) {
        .main-content {
            margin-left: 0;
        }
        .sidebar.open ~ .main-content {
            margin-left: 270px;
        }
        .sidebar.collapsed ~ .main-content {
            margin-left: 60px;
        }
    }
    .appts-card {
        max-width: 950px;
        width: 100%;
        border: none;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
        background: rgba(255,255,255,0.98);
        padding: 2.2rem 1.5rem 1.5rem 1.5rem;
        margin-top: 2.5rem;
        border-radius: 1.2rem;
    }
    .appts-card h2 {
        font-size: 1.6rem;
    }
    .appts-card .fs-6 {
        font-size: 1.05rem !important;
    }
    .appts-card .alert {
        border-radius: 0.7rem;
        font-size: 1.05rem;
        box-shadow: 0 2px 8px rgba(31,38,135,0.07);
    }
    .appts-card form .form-label {
        font-weight: 500;
        color: #2563eb;
    }
    .appts-card form input,
    .appts-card form select {
        border-radius: 0.5rem;
        border: 1px solid #e0e7ef;
        font-size: 1rem;
        padding: 0.55rem 0.75rem;
        background: #f8fafc;
        transition: border-color 0.2s;
    }
    .appts-card form input:focus,
    .appts-card form select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 2px #2563eb22;
    }
    .appts-card .btn-primary,
    .appts-card .btn-outline-primary {
        border-radius: 0.5rem;
        font-weight: 500;
        font-size: 1.05rem;
    }
    .appts-card .btn-primary {
        background: linear-gradient(90deg, #3b82f6 0%, #06b6d4 100%);
        border: none;
    }
    .appts-card .btn-primary:hover {
        background: linear-gradient(90deg, #2563eb 0%, #0891b2 100%);
    }
    .appts-card .btn-outline-primary {
        border: 1.5px solid #2563eb;
        color: #2563eb;
    }
    .appts-card .btn-outline-primary:hover {
        background: #2563eb;
        color: #fff;
    }
    .appts-card .btn-outline-success,
    .appts-card .btn-outline-danger {
        border-radius: 0.4rem;
        font-size: 0.98rem;
    }
    .appts-card .table {
        border-radius: 0.8rem;
        overflow: hidden;
        background: #f8fafc;
        margin-bottom: 0;
    }
    .appts-card .table thead tr {
        background: linear-gradient(90deg, #3b82f6 0%, #06b6d4 100%);
        color: #fff;
        font-size: 1.08rem;
    }
    .appts-card .table th, .appts-card .table td {
        vertical-align: middle;
        padding: 1rem 0.7rem;
    }
    .appts-card .table-striped > tbody > tr:nth-of-type(odd) {
        background-color: #f1f5f9;
    }
    .appts-card .table-striped > tbody > tr:nth-of-type(even) {
        background-color: #e0e7ef;
    }
    .appts-card .table tbody tr:hover {
        background: #dbeafe !important;
        transition: background 0.2s;
    }
    .appts-card .d-grid .btn {
        border-radius: 0.6rem;
        font-size: 1.1rem;
        font-weight: 500;
    }
    @media (max-width: 900px) {
        .appts-card {
            padding: 1.2rem 0.5rem 1rem 0.5rem;
        }
        .appts-card h2 {
            font-size: 1.25rem;
        }
    }
    @media (max-width: 600px) {
        .appts-card {
            margin-top: 1.2rem;
            padding: 0.7rem 0.2rem 0.7rem 0.2rem;
        }
        .appts-card .table th, .appts-card .table td {
            padding: 0.6rem 0.3rem;
            font-size: 0.97rem;
        }
        .appts-card .d-grid .btn {
            font-size: 1rem;
        }
    }
    /* Modal stilleri */
    .modal-content {
        border-radius: 1.2rem;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.18);
        background: rgba(255,255,255,0.98);
        border: none;
    }
    .modal-header {
        border-bottom: none;
        background: linear-gradient(90deg, #3b82f6 0%, #06b6d4 100%);
        color: #fff;
        border-top-left-radius: 1.2rem;
        border-top-right-radius: 1.2rem;
        padding-bottom: 0.7rem;
    }
    .modal-title {
        font-weight: 600;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .modal-footer {
        border-top: none;
        background: transparent;
        border-bottom-left-radius: 1.2rem;
        border-bottom-right-radius: 1.2rem;
    }
    .btn-close {
        filter: invert(1);
    }
    .appts-desc {
        font-size: 0.98rem;
        color: #334155;
        font-weight: 500;
        margin-bottom: 1.1rem;
    }

    /* Takvim boyutunu küçült */
    #calendar {
        max-width: 600px;
        min-width: 320px;
        height: 420px;
        margin: 24px auto 0 auto;
        background: #f1f5f9;
        border-radius: 0.8rem;
        box-shadow: 0 2px 12px 0 rgba(31,38,135,0.10);
        padding: 0.7rem 0.5rem 0.5rem 0.5rem;
        font-size: 0.97rem;
    }

    /* FullCalendar başlık ve gün isimleri */
    .fc .fc-toolbar-title,
    .fc .fc-col-header-cell-cushion {
        color: #1e293b !important;
        font-weight: 600;
    }

    /* Takvim gün kutuları ve yazıları */
    .fc .fc-daygrid-day-number,
    .fc .fc-daygrid-day {
        color: #334155 !important;
        background: #f8fafc !important;
    }

    /* Bugünün günü */
    .fc .fc-day-today {
        background: #e0e7ef !important;
        color: #2563eb !important;
    }

    /* Event kutuları */
    .fc .fc-event {
        background: #f59e42 !important;   /* Turuncu tonunda */
        color: #fff !important;
        border: none !important;
        border-radius: 0.5rem !important;
        font-size: 0.97rem;
        padding: 2px 6px;
    }

    /* Hover efekti */
    .fc .fc-event:hover {
        background: #d97706 !important;
        color: #fff !important;
    }

    /* Takvim grid çizgileri */
    .fc .fc-scrollgrid,
    .fc .fc-scrollgrid-section,
    .fc .fc-scrollgrid-sync-table {
        border-color: #cbd5e1 !important;
    }

    /* Randevu olan günlerin kutusunun arka planı */
    .fc-daygrid-day.has-appointment {
        background: #f59e42 !important;
        color: #fff !important;
        border-radius: 0.5rem;
    }
    .fc-daygrid-day.has-appointment .fc-daygrid-day-number {
        color: #fff !important;
        font-weight: bold;
    }
    /* Duruma göre farklı class'lar */
    .fc-daygrid-day.has-appointment-pending {
        background: #fbbf24 !important; /* Sarı */
        color: #fff !important;
    }
    .fc-daygrid-day.has-appointment-pending .fc-daygrid-day-number {
        color: #fff !important;
        font-weight: bold;
    }
    .fc-daygrid-day.has-appointment-approved {
        background: #22c55e !important; /* Yeşil */
        color: #fff !important;
    }
    .fc-daygrid-day.has-appointment-approved .fc-daygrid-day-number {
        color: #fff !important;
        font-weight: bold;
    }
    .fc-daygrid-day.has-appointment-rejected {
        background: #ef4444 !important; /* Kırmızı */
        color: #fff !important;
    }
    .fc-daygrid-day.has-appointment-rejected .fc-daygrid-day-number {
        color: #fff !important;
        font-weight: bold;
    }
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
        <a href="index.php" class="nav-link">
            <i class="bi bi-house"></i>
            <span class="sidebar-link-text">Ana Sayfa</span>
        </a>
        <a href="appts.php" class="nav-link active">
            <i class="bi bi-calendar2-week"></i>
            <span class="sidebar-link-text">Randevuları Yönet</span>
        </a>
        <a href="users.php" class="nav-link">
            <i class="bi bi-people"></i>
            <span class="sidebar-link-text">Müşterileri Görüntüle</span>
        </a>
       <a href="calisma_saatleri.php" class="nav-link">
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
<?php elseif (!isset($_SESSION["yoneticiID"]) && isset($_SESSION["userID"])): ?>
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
            <a href="index.php" class="nav-link">
                <i class="bi bi-house"></i>
                <span class="sidebar-link-text">Ana Sayfa</span>
            </a>
            <a href="appts.php" class="nav-link active">
                <i class="bi bi-calendar2-week"></i>
                <span class="sidebar-link-text">Randevularım</span>
            </a>
            <a href="makeAppt.php" class="nav-link">
                <i class="bi bi-plus-circle"></i>
                <span class="sidebar-link-text">Randevu Oluştur</span>
            </a>
            <a href="calisma_saatler_musteri.php" class="nav-link<?= basename($_SERVER['PHP_SELF']) == 'calisma_saatler_musteri.php' ? ' active' : '' ?>">
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
<script>
    // Sidebar aç/kapa
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

    let id;
    function getID(inID) {
        id = inID;
    }
    function approve(response) {
        if (response) {
            window.location.href='./includes/deleteAppt-inc.php?deleteID=' + id;
        }
    }

    function openCancelModal(randevuID) {
        document.getElementById('cancel_randevuID').value = randevuID;
        document.getElementById('cancel_reason').value = '';
        var modal = new bootstrap.Modal(document.getElementById('cancelReasonModal'));
        modal.show();
    }
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar;
    var dayStatus = {}; // { "2024-06-12": "Bekliyor", ... }

    function getPriority(status) {
        if (status === "Reddedildi") return 3;
        if (status === "Onaylandı") return 2;
        if (status === "Bekliyor") return 1;
        return 0;
    }

    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'tr',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: 'get-events.php',
        eventDidMount: function(info) {
            if (info.view.type === 'dayGridMonth') {
                var dateString = info.event.startStr.split('T')[0];
                var cell = calendarEl.querySelector('[data-date="' + dateString + '"]');
                if (cell) {
                    // Duruma göre class ekle
                    if (info.event.extendedProps.durum === "Bekliyor") {
                        cell.classList.add('has-appointment-pending');
                    } else if (info.event.extendedProps.durum === "Onaylandı") {
                        cell.classList.add('has-appointment-approved');
                    } else if (info.event.extendedProps.durum === "Reddedildi") {
                        cell.classList.add('has-appointment-rejected');
                    }
                }
            }
        },
        datesSet: function() {
            // Ay değişince işaretlemeleri sıfırla
            var cells = calendarEl.querySelectorAll('.has-appointment-pending, .has-appointment-approved, .has-appointment-rejected');
            cells.forEach(function(cell) {
                cell.classList.remove('has-appointment-pending', 'has-appointment-approved', 'has-appointment-rejected');
            });
        }
    });
    calendar.render();
});
</script>

<div class="main-content">
    <div class="container d-flex flex-column align-items-center justify-content-center" style="min-height: 90vh;">
        <div class="card appts-card rounded-4">
            <div class="text-center mb-4">
                <i class="bi bi-list-ul text-primary" style="font-size:2.3rem;"></i>
                <h2 class="fw-bold mt-2 mb-0">Randevularım</h2>
                <div class="appts-desc">Tüm randevularınızı görüntüleyin ve yönetin</div>
                <div id="calendar" class="mb-4"></div>
                <div class="calendar-legend mt-2 mb-3" style="font-size:0.98rem; color:#f59e42; font-weight:600;">
                    <i class="bi bi-square-fill" style="color:#f59e42; font-size:1.1rem; vertical-align:middle;"></i>
                    Turuncu renkli günler: Randevu olan günlerdir.
                </div>
                <div class="calendar-legend mt-2 mb-3" style="font-size:0.98rem; font-weight:600;">
                    <i class="bi bi-square-fill" style="color:#fbbf24; font-size:1.1rem; vertical-align:middle;"></i> Bekleyen randevu &nbsp;
                    <i class="bi bi-square-fill" style="color:#22c55e; font-size:1.1rem; vertical-align:middle;"></i> Onaylanan randevu &nbsp;
                    <i class="bi bi-square-fill" style="color:#ef4444; font-size:1.1rem; vertical-align:middle;"></i> Reddedilen randevu
                </div>
            </div>

            <?php if ($showRedAlert): ?>
                <div class="alert alert-danger fw-bold text-center py-2 mb-3">
                    <i class="bi bi-x-circle-fill"></i> Randevunuz reddedildi, lütfen başka bir tarih seçin.
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION["userID"])): ?>
    <!-- Kullanıcı arama formu -->
    <form method="get" class="row g-2 align-items-end mb-3 justify-content-center">
        
        <form method="get" class="row g-2 align-items-end mb-3 justify-content-center">
                <div class="col-md-3">
                    <label class="form-label" for="tarih1">Başlangıç Tarihi</label>
                    <input type="date" id="tarih1" name="tarih1" class="form-control" value="<?= isset($_GET['tarih1']) ? htmlspecialchars($_GET['tarih1']) : '' ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="tarih2">Bitiş Tarihi</label>
                    <input type="date" id="tarih2" name="tarih2" class="form-control" value="<?= isset($_GET['tarih2']) ? htmlspecialchars($_GET['tarih2']) : '' ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="saat1">Başlangıç Saati</label>
                    <input type="time" id="saat1" name="saat1" class="form-control" value="<?= isset($_GET['saat1']) ? htmlspecialchars($_GET['saat1']) : '' ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="saat2">Bitiş Saati</label>
                    <input type="time" id="saat2" name="saat2" class="form-control" value="<?= isset($_GET['saat2']) ? htmlspecialchars($_GET['saat2']) : '' ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="sort">Sırala</label>
                    <select id="sort" name="sort" class="form-select">
                        <option value="ad_asc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'ad_asc') ? 'selected' : '' ?>>Ada Göre (A-Z)</option>
                        <option value="ad_desc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'ad_desc') ? 'selected' : '' ?>>Ada Göre (Z-A)</option>
                    </select>
                </div>
                <div class="col-md-3 justify-content-center">
                    <label class="form-label" for="admin_search">Arama</label>
                    <input type="text" id="admin_search" name="admin_search" class="form-control" placeholder="Ad, tarih veya saat" value="<?= isset($_GET['admin_search']) ? htmlspecialchars($_GET['admin_search']) : '' ?>">
                </div>
                <div class="col-md-12 mt-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i> Filtrele / Ara
                    </button>
                </div>
            </form>
    </form>
    <?php endif; ?>

            <?php if (isset($_SESSION["yoneticiID"])): ?>
        <div class="d-flex justify-content-center mb-3">
            <button class="btn btn-success px-4 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#addApptModal">
                <i class="bi bi-plus-circle"></i> Randevu Ekle
            </button>
        </div>
            <form method="get" class="row g-2 align-items-end mb-3 justify-content-center">
                <div class="col-md-3">
                    <label class="form-label" for="tarih1">Başlangıç Tarihi</label>
                    <input type="date" id="tarih1" name="tarih1" class="form-control" value="<?= isset($_GET['tarih1']) ? htmlspecialchars($_GET['tarih1']) : '' ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="tarih2">Bitiş Tarihi</label>
                    <input type="date" id="tarih2" name="tarih2" class="form-control" value="<?= isset($_GET['tarih2']) ? htmlspecialchars($_GET['tarih2']) : '' ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="saat1">Başlangıç Saati</label>
                    <input type="time" id="saat1" name="saat1" class="form-control" value="<?= isset($_GET['saat1']) ? htmlspecialchars($_GET['saat1']) : '' ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="saat2">Bitiş Saati</label>
                    <input type="time" id="saat2" name="saat2" class="form-control" value="<?= isset($_GET['saat2']) ? htmlspecialchars($_GET['saat2']) : '' ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="sort">Sırala</label>
                    <select id="sort" name="sort" class="form-select">
                        <option value="ad_asc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'ad_asc') ? 'selected' : '' ?>>Ada Göre (A-Z)</option>
                        <option value="ad_desc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'ad_desc') ? 'selected' : '' ?>>Ada Göre (Z-A)</option>
                    </select>
                </div>
                <div class="col-md-3 justify-content-center">
                    <label class="form-label" for="admin_search">Arama</label>
                    <input type="text" id="admin_search" name="admin_search" class="form-control" placeholder="Ad, tarih veya saat" value="<?= isset($_GET['admin_search']) ? htmlspecialchars($_GET['admin_search']) : '' ?>">
                </div>
                <div class="col-md-12 mt-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i> Filtrele / Ara
                    </button>
                </div>
            </form>
            <?php endif; ?>

            <?php
            // Hata ve bilgi mesajları
            if (isset($_GET["error"])) {
                if ($_GET["error"] == "appttaken") {
                    echo '<div class="alert alert-danger fw-bold text-center py-2 mb-3">
                            <i class="bi bi-x-circle-fill"></i> Bu tarihte başka randevu bulunmakta.
                            <button class="btn btn-outline-dark btn-sm ms-2" type="button" data-bs-toggle="modal" data-bs-target="#apptsModal">Dolu Randevuları Göster</button>
                          </div>';
                } else if ($_GET["error"] == "pastdate") {
                    echo '<div class="alert alert-warning fw-bold text-center py-2 mb-3">
                            <i class="bi bi-exclamation-circle-fill"></i> Geçmiş zaman seçilemez.
                          </div>';
                } else if ($_GET["error"] == "sccssfldelete") {
                    echo '<div class="alert alert-success fw-bold text-center py-2 mb-3">
                            <i class="bi bi-check-circle"></i> Randevu başarıyla silindi.
                          </div>';
                }
            }
            ?>

            <div class="table-responsive rounded-3 mb-3" style="max-height: 55vh;">
                <table class="table table-striped mb-0 align-middle">
                    <thead>
                        <tr class="fs-5">
                            <th class="p-3" scope="col">Müşteri</th>
                            <th class="p-3" scope="col">Tarih</th>
                            <th class="p-3" scope="col">Saat</th>
                            <th class="p-3" scope="col">Durum</th>
                            <th class="p-3" scope="col">İptal Nedeni</th>
                            <th class="p-3 text-center" scope="col">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // ... (tablo satırları kodun aynı şekilde devam ediyor) ...
                        if (isset($_SESSION["userID"])) {
                            // Kullanıcı için arama
                            $uid = $_SESSION["userID"];
                            $today = date('Y-m-d');
                            $query = "SELECT r.*, m.ad_soyad FROM randevular r INNER JOIN musteriler m ON r.musteriID = m.musteriID WHERE r.musteriID = ? AND r.tarih >= ?";
                            $params = [$uid, $today];
                            $types = "is";
                            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
                            if ($search !== '') {
                                $query .= " AND (m.ad_soyad LIKE ? OR r.tarih LIKE ? OR r.saat LIKE ?)";
                                $searchParam = "%" . $search . "%";
                                $params[] = $searchParam;
                                $params[] = $searchParam;
                                $params[] = $searchParam;
                                $types .= "sss";
                            }
                            $query .= " ORDER BY r.tarih DESC, r.saat DESC";
                            $stmt = mysqli_prepare($connection, $query);
                            mysqli_stmt_bind_param($stmt, $types, ...$params);
                            mysqli_stmt_execute($stmt);
                            $response = mysqli_stmt_get_result($stmt);

                            if (mysqli_num_rows($response) == 0) {
                                echo '<tr><td class="p-3 text-dark fw-bold fs-5 text-center" colspan="5">Herhangi bir randevunuz bulunmamakta.</td></tr>';
                            } else {
                                while ($data = mysqli_fetch_assoc($response)) {
                                    echo '<tr>
                                        <td class="p-3">' . htmlspecialchars($data["ad_soyad"]) . '</td>
                                        <td class="p-3">' . htmlspecialchars($data["tarih"]) . '</td>
                                        <td class="p-3">' . htmlspecialchars($data["saat"]) . '</td>
                                        <td class="p-3">' . htmlspecialchars($data["durum"]) . '</td>
                                        <td class="p-3">' . htmlspecialchars($data["iptal_nedeni"] ?? '-') . '</td>
                                        <td class="text-center fs-4">
                                            <a href="./makeAppt.php?updateID=' . $data["randevuID"] . '&date=' . $data["tarih"] . '&time=' . $data["saat"] . '" class="btn btn-outline-success btn-sm me-2" title="Düzenle">
                                                Düzenle
                                            </a>
                                            <a href="#" onclick="openCancelModal(' . $data["randevuID"] . ')" class="btn btn-outline-danger btn-sm" title="İptal Et">
                                                Sil
                                            </a>
                                        </td>
                                    </tr>';
                                }
                            }
                        } else if (isset($_SESSION["yoneticiID"])) {
                            // Admin için filtre + arama
                            $where = [];
                            $params = [];
                            if (!empty($_GET['tarih1'])) {
                                $where[] = "r.tarih >= ?";
                                $params[] = $_GET['tarih1'];
                            }
                            if (!empty($_GET['tarih2'])) {
                                $where[] = "r.tarih <= ?";
                                $params[] = $_GET['tarih2'];
                            }
                            if (!empty($_GET['saat1'])) {
                                $where[] = "r.saat >= ?";
                                $params[] = $_GET['saat1'];
                            }
                            if (!empty($_GET['saat2'])) {
                                $where[] = "r.saat <= ?";
                                $params[] = $_GET['saat2'];
                            }
                            // Arama filtresi
                            $admin_search = isset($_GET['admin_search']) ? trim($_GET['admin_search']) : '';
                            if ($admin_search !== '') {
                                $where[] = "(m.ad_soyad LIKE ? OR r.tarih LIKE ? OR r.saat LIKE ?)";
                                $searchParam = "%" . $admin_search . "%";
                                $params[] = $searchParam;
                                $params[] = $searchParam;
                                $params[] = $searchParam;
                            }
                            $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
                            $sort = isset($_GET['sort']) ? $_GET['sort'] : 'ad_asc';
                            if ($sort == 'ad_desc') {
                                $orderBy = "ORDER BY m.ad_soyad DESC, r.tarih DESC, r.saat DESC";
                            } else {
                                $orderBy = "ORDER BY m.ad_soyad ASC, r.tarih DESC, r.saat DESC";
                            }
                            $sql = "SELECT r.*, m.ad_soyad FROM randevular r INNER JOIN musteriler m ON r.musteriID = m.musteriID $whereSql $orderBy";
                            $stmt = mysqli_prepare($connection, $sql);
                            if ($params) {
                                $types = str_repeat('s', count($params));
                                mysqli_stmt_bind_param($stmt, $types, ...$params);
                            }
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);

                            if (mysqli_num_rows($result) == 0) {
                                echo '<tr><td class="p-3 text-dark fw-bold fs-5 text-center" colspan="6">Kriterlere uygun randevu bulunamadı.</td></tr>';
                            } else {
                                while ($data = mysqli_fetch_assoc($result)) {
                                    echo '<tr>
                                        <td class="p-3">' . htmlspecialchars($data["ad_soyad"]) . '</td>
                                        <td class="p-3">' . htmlspecialchars($data["tarih"]) . '</td>
                                        <td class="p-3">' . htmlspecialchars($data["saat"]) . '</td>
                                        <td class="p-3">' . htmlspecialchars($data["durum"]) . '</td>
                                        <td class="p-3">' . htmlspecialchars($data["iptal_nedeni"] ?? '-') . '</td>
                                        <td class="text-center fs-5">';
                                    if ($data["durum"] === "Bekliyor") {
                                        echo '<form method="post" class="d-inline">
                                                <input type="hidden" name="randevuID" value="' . $data["randevuID"] . '">
                                                <button type="submit" name="action" value="onayla" class="btn btn-outline-success btn-sm me-1" title="Onayla">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                                <button type="submit" name="action" value="reddet" class="btn btn-outline-danger btn-sm" title="Reddet">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            </form>';
                                    }
                                    echo '  <a href="./makeAppt.php?updateID=' . $data["randevuID"] . '&date=' . $data["tarih"] . '&time=' . $data["saat"] . '" class="btn btn-outline-success btn-sm ms-1" title="Güncelle">
                                                Düzenle
                                            </a>
                                            <a href="#" onclick="openCancelModal(' . $data["randevuID"] . ')" class="btn btn-outline-danger btn-sm" title="İptal Et">
                                                Sil
                                            </a>
                                        </td>
                                    </tr>';
                                }
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="d-grid col-6 mx-auto">
                <?php
                $backUrl = (isset($_SESSION["yoneticiID"])) ? "./admin.php" : "./index.php";
                ?>
                
            </div>
        </div>
    </div>
</div>

<!-- Admin için Randevu Ekle Modalı -->
<div class="modal fade" id="addApptModal" tabindex="-1" aria-labelledby="addApptModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addApptModalLabel"><i class="bi bi-plus-circle"></i> Yeni Randevu Ekle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="new_ad" class="form-label">Ad</label>
          <input type="text" class="form-control" id="new_ad" name="new_ad" required>
        </div>
        <div class="mb-3">
          <label for="new_soyad" class="form-label">Soyad</label>
          <input type="text" class="form-control" id="new_soyad" name="new_soyad" required>
        </div>
        <div class="mb-3">
          <label for="new_tarih" class="form-label">Tarih</label>
          <input type="date" class="form-control" id="new_tarih" name="new_tarih" required>
        </div>
        <div class="mb-3">
          <label for="new_saat" class="form-label">Saat</label>
          <input type="time" class="form-control" id="new_saat" name="new_saat" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
        <button type="submit" class="btn btn-success" name="add_appt">Kaydet</button>
      </div>
    </form>
  </div>
</div>

<!-- Dolu randevuları gösteren modal -->
<div class="modal fade" id="apptsModal" tabindex="-1" aria-labelledby="apptsModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h1 class="modal-title fs-5 fw-bold" id="apptsModal">Rezerve Edilenler</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-striped mb-0">
                    <?php
                    if (isset($_SESSION["userID"])||isset($_SESSION["yoneticiID"])) {
                        $cevap = getAppts($connection, false);
                    }
                    if (mysqli_num_rows($cevap) == 0) {
                        echo '<tr><td class="p-3 text-dark fw-bold fs-5 text-center" colspan="2">Herhangi bir veri bulunmamakta.</td></tr>';
                    } else {
                        while ($data = mysqli_fetch_assoc($cevap)) {
                            echo '<tr>
                                    <td class="p-3">' . $data["tarih"] . '</td>
                                    <td class="p-3">' . $data["saat"] . '</td>
                                </tr>';
                        }
                    }
                    ?>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn mx-auto btn-secondary" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<!-- Randevu İptal Nedeni Modalı -->
<div class="modal fade" id="cancelReasonModal" tabindex="-1" aria-labelledby="cancelReasonModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="cancelReasonForm" method="post">
      <input type="hidden" name="cancel_randevuID" id="cancel_randevuID" value="">
      <div class="modal-content">
        <div class="modal-header bg-danger">
          <h5 class="modal-title text-light" id="cancelReasonModalLabel"><i class="bi bi-exclamation-circle-fill"></i> Randevuyu İptal Et</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
        </div>
        <div class="modal-body">
          <label for="cancel_reason" class="form-label fw-bold">Randevuyu neden iptal ediyorsunuz?</label>
          <textarea class="form-control" name="cancel_reason" id="cancel_reason" rows="3" required></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Vazgeç</button>
          <button type="submit" class="btn btn-danger">İptal Et</button>
        </div>
      </div>
    </form>
  </div>
</div>


<?php
include_once './footer.php';
mysqli_close($connection);
?>