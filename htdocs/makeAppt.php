<?php
include_once './header.php';
include_once './includes/functions-inc.php';

if (!(isset($_SESSION["userID"]) || isset($_SESSION["adminID"]))) {
    header("Location: ./signin.php");
    exit();
}
?>

<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet" />

<style>
    body {
        background: linear-gradient(120deg, #f8fafc 0%, #e0e7ef 100%);
    }
    .appt-card {
        max-width: 950px; /* Genişlik appts-card ile aynı */
        width: 100%;
        border: none;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
        background: rgba(255,255,255,0.98);
        padding: 2.2rem 1.5rem 1.5rem 1.5rem;
        margin-top: 2.5rem;
        border-radius: 1.2rem;
    }
    .appt-card .btn-success {
        background: linear-gradient(90deg, #22c55e 0%, #16a34a 100%);
        border: none;
        color: #fff;
    }
    .appt-card .btn-success:hover {
        background: linear-gradient(90deg, #16a34a 0%, #15803d 100%);
        color: #fff;
    }
    .appt-card .btn-outline-dark {
        border: 1.5px solid #222;
        color: #222;
        background: #fff;
    }
    .appt-card .btn-outline-dark:hover {
        background: #222;
        color: #fff;
    }
    .appt-card .btn-warning {
        color: #fff;
    }
    .appt-card .btn i {
        font-size: 1.25em;
    }
    .appt-card h2 {
        letter-spacing: 1px;
        font-size: 2.1rem;
    }
    .appt-card .form-label {
        font-weight: 500;
        color: #222;
    }
    .appt-card .form-control:focus {
        border-color: #22c55e;
        box-shadow: 0 0 0 0.15rem rgba(34,197,94,.15);
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
        margin-right: 0;
        font-size: 1.5em;
    }
    /* Sidebar CSS */
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
        .appt-card {
            padding: 1.2rem 0.5rem 1rem 0.5rem;
        }
    }
    @media (max-width: 600px) {
        .appt-card {
            margin-top: 1.2rem;
            padding: 0.7rem 0.2rem 0.7rem 0.2rem;
        }
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
    /* FullCalendar özel stiller */
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
    .fc .fc-toolbar-title,
    .fc .fc-col-header-cell-cushion {
        color: #1e293b !important;
        font-weight: 600;
    }
    .fc .fc-daygrid-day-number,
    .fc .fc-daygrid-day {
        color: #334155 !important;
        background: #f8fafc !important;
    }
    .fc .fc-day-today {
        background: #e0e7ef !important;
        color: #2563eb !important;
    }
    .fc .fc-event {
        background: #f59e42 !important;
        color: #fff !important;
        border: none !important;
        border-radius: 0.5rem !important;
        font-size: 0.97rem;
        padding: 2px 6px;
    }
    .fc .fc-event:hover {
        background: #d97706 !important;
        color: #fff !important;
    }
    .fc .fc-scrollgrid,
    .fc .fc-scrollgrid-section,
    .fc .fc-scrollgrid-sync-table {
        border-color: #cbd5e1 !important;
    }
    .fc-daygrid-day.has-appointment {
        background: #f59e42 !important;
        color: #fff !important;
        border-radius: 0.5rem;
    }
    .fc-daygrid-day.has-appointment .fc-daygrid-day-number {
        color: #fff !important;
        font-weight: bold;
    }
    .fc-daygrid-day.has-appointment-pending {
        background: #fbbf24 !important;
        color: #fff !important;
    }
    .fc-daygrid-day.has-appointment-pending .fc-daygrid-day-number {
        color: #fff !important;
        font-weight: bold;
    }
    .fc-daygrid-day.has-appointment-approved {
        background: #22c55e !important;
        color: #fff !important;
    }
    .fc-daygrid-day.has-appointment-approved .fc-daygrid-day-number {
        color: #fff !important;
        font-weight: bold;
    }
    .fc-daygrid-day.has-appointment-rejected {
        background: #ef4444 !important;
        color: #fff !important;
    }
    .fc-daygrid-day.has-appointment-rejected .fc-daygrid-day-number {
        color: #fff !important;
        font-weight: bold;
    }
</style>

<?php if (!isset($_SESSION["adminID"]) && isset($_SESSION["userID"])): ?>
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

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<?php if (!isset($_SESSION["userID"]) && !isset($_SESSION["adminID"])): ?>

<nav class="navbar navbar-expand-lg navbar-custom px-3">
    <div class="container-fluid">
        <a class="navbar-brand" href="./index.php">
            <i class="bi bi-calendar2-check-fill text-primary"></i> Randevu Sistemi
        </a>
        <div class="d-flex align-items-center ms-auto">
            <?php if (isset($_SESSION["userID"]) || isset($_SESSION["adminID"])): ?>
                <div class="dropdown">
                    <button class="btn dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle text-primary"></i>
                        <?php
                            if (isset($_SESSION["ad_soyad"])) {
                                echo htmlspecialchars($_SESSION["ad_soyad"]);
                            } else if (isset($_SESSION["adminID"])) {
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
                <a href="signin.php" class="btn btn-outline-primary ms-2">
                    <i class="bi bi-box-arrow-in-right"></i> Giriş Yap / Kaydol
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<?php endif; ?>
<div class="container d-flex flex-column align-items-center justify-content-center" style="min-height: 90vh;">
    <div class="card appt-card rounded-4">
        <div class="text-center mb-4">
            <i class="bi bi-plus-circle text-success" style="font-size:2.7rem;"></i>
            <h2 class="fw-bold mt-2 mb-0">
                <?php
                if (isset($_GET["updateID"]) && isset($_GET["date"]) && isset($_GET["time"])) {
                    echo 'Randevu Güncelle';
                } else {
                    echo 'Randevu Oluştur';
                }
                ?>
            </h2>
            <div class="text-secondary fs-6 mb-2">
                <?php
                if (isset($_GET["updateID"]) && isset($_GET["date"]) && isset($_GET["time"])) {
                    echo htmlspecialchars($_GET["date"] . ' ' . $_GET["time"]) . ' yerine yeni tarih ve saat seçin';
                } else {
                    echo 'Tarih ve saat seçerek randevunuzu oluşturun';
                }
                ?>
            </div>
        </div>
        <!-- TAKVİM -->
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
        <!-- /TAKVİM -->
        <form action="./includes/makeAppt-inc.php" method="POST" autocomplete="off">
            <div class="mb-3">
                <label class="form-label d-block text-start" for="date">
                    <i class="bi bi-calendar-event me-2"></i>
                    Tarih
                </label>
                <input type="date" id="date" name="date" class="form-control form-control-lg" required />
            </div>
            <div class="mb-4">
                <label class="form-label d-block text-start" for="appt">
                    <i class="bi bi-clock me-2"></i>
                    Saat
                </label>
                <input type="time" id="appt" name="time" min="08:00" max="20:00" step="1800" list="time_list" class="form-control form-control-lg" required />
                <datalist id="time_list">
                    <option value="08:00">
                    <option value="08:30">
                    <option value="09:00">
                    <option value="09:30">
                    <option value="10:00">
                    <option value="10:30">
                    <option value="11:00">
                    <option value="11:30">
                    <option value="13:00">
                    <option value="13:30">
                    <option value="14:00">
                    <option value="14:30">
                    <option value="15:00">
                    <option value="15:30">
                    <option value="16:00">
                    <option value="16:30">
                    <option value="17:00">
                    <option value="17:30">
                    <option value="18:00">
                    <option value="18:30">
                    <option value="19:00">
                    <option value="19:30">
                </datalist>
            </div>
            <div class="d-grid gap-2 mb-3">
                
                <?php
                if (isset($_GET["updateID"]) && isset($_GET["date"]) && isset($_GET["time"])) {
                    echo '<input type="hidden" name="type" value="update">';
                    echo '<input type="hidden" name="updateID" value="' . htmlspecialchars($_GET["updateID"]) . '">';
                    echo '<button class="btn btn-warning btn-lg fw-bold" type="submit" name="submit"><i class="bi bi-pencil-square"></i> Güncelle</button>';
                } else {
                    echo '<button class="btn btn-success btn-lg" type="submit" name="submit"><i class="bi bi-plus-circle"></i> Randevu Al</button>';
                }
                ?>
            </div>
            <?php
            if (isset($_GET["error"])) {
                if ($_GET["error"] === 'appttaken') {
                    echo '<div class="alert alert-danger fw-bold text-center py-2">Bu tarih ve saatte başka bir randevu var!</div>';
                } else if ($_GET["error"] === 'none') {
                    echo '<div class="alert alert-success fw-bold text-center py-2"><i class="bi bi-check-circle-fill"></i> Randevu başarıyla oluşturuldu.</div>';
                } else if ($_GET["error"] === 'succapptupdt') {
                    echo '<div class="alert alert-success fw-bold text-center py-2"><i class="bi bi-check-circle-fill"></i> Randevu başarıyla güncellendi.</div>';
                } else if ($_GET["error"] === 'pastdate') {
                    echo '<div class="alert alert-warning fw-bold text-center py-2"><i class="bi bi-exclamation-circle-fill"></i> Geçmiş zaman seçilemez.</div>';
                } else if ($_GET["error"] === 'noadmappt') {
                    echo '<div class="alert alert-warning fw-bold text-center py-2"><i class="bi bi-exclamation-circle-fill"></i> Yönetici hesapla randevu oluşturulamaz.</div>';
                }
            }
            ?>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Takvim
    var calendarEl = document.getElementById('calendar');
    var calendar;
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
            var cells = calendarEl.querySelectorAll('.has-appointment-pending, .has-appointment-approved, .has-appointment-rejected');
            cells.forEach(function(cell) {
                cell.classList.remove('has-appointment-pending', 'has-appointment-approved', 'has-appointment-rejected');
            });
        }
    });
    calendar.render();

    // Sadece bu günden itibaren 30 gün seçilebilir
    const dateInput = document.getElementById('date');
    const today = new Date();
    const minDate = today.toISOString().split('T')[0];
    const maxDate = new Date(today.setDate(today.getDate() + 30)).toISOString().split('T')[0];
    dateInput.setAttribute("min", minDate);
    dateInput.setAttribute("max", maxDate);

    // Sidebar aç/kapa fonksiyonları
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