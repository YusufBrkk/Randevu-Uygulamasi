<?php
include_once './header.php';
include_once './includes/dbh-inc.php';

if (!isset($_SESSION["userID"])) {
    header("Location: signin.php");
    exit();
}

$userID = intval($_SESSION["userID"]);
$today = date('Y-m-d');

// Geçmiş randevuları çek (tarih < bugün)
$sql = "SELECT r.*, m.ad_soyad 
        FROM randevular r 
        INNER JOIN musteriler m ON r.musteriID = m.musteriID 
        WHERE r.musteriID = ? AND r.tarih < ?
        ORDER BY r.tarih DESC, r.saat DESC";
$stmt = mysqli_prepare($connection, $sql);
mysqli_stmt_bind_param($stmt, "is", $userID, $today);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<style>
    body {
        background: linear-gradient(120deg, #f8fafc 0%, #e0e7ef 100%);
    }
    .past-appts-card {
        max-width: 440px;
        width: 100%;
        border: none;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.18);
        background: linear-gradient(135deg, #f1f5f9 0%, #fff 100%);
        padding: 0;
        margin-top: 2.5rem;
        border-radius: 1.3rem;
        overflow: hidden;
        position: relative;
    }
    .past-appts-banner {
        background: linear-gradient(90deg, #2563eb 0%, #38bdf8 100%);
        color: #fff;
        padding: 2.2rem 1.5rem 1.2rem 1.5rem;
        text-align: center;
        border-top-left-radius: 1.3rem;
        border-top-right-radius: 1.3rem;
        position: relative;
    }
    .past-appts-banner i {
        font-size: 2.7rem;
        margin-bottom: 0.5rem;
        color: #fff;
        filter: drop-shadow(0 2px 8px #2563eb55);
    }
    .past-appts-banner h2 {
        font-weight: 800;
        font-size: 2rem;
        margin-bottom: 0.2rem;
        letter-spacing: 1px;
    }
    .past-appts-banner .desc {
        font-size: 1.08rem;
        color: #e0e7ef;
        margin-bottom: 0;
    }
    .past-appts-list {
        background: transparent;
        padding: 2rem 1.5rem 1.2rem 1.5rem;
    }
    .past-appts-list .list-group-item {
        background: #f8fafc;
        border: none;
        border-radius: 0.7rem;
        margin-bottom: 0.7rem;
        font-size: 1.13rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 2px 8px 0 rgba(31,38,135,0.07);
        padding: 1.1rem 1.2rem;
    }
    .past-appts-list .list-group-item i {
        color: #2563eb;
        font-size: 1.3rem;
        margin-right: 0.7rem;
    }
    .past-appts-list .fw-bold {
        color: #2563eb;
        font-size: 1.15rem;
        letter-spacing: 0.5px;
    }
    .past-appts-info {
        background: linear-gradient(90deg, #38bdf8 0%, #a7f3d0 100%);
        color: #2563eb;
        font-weight: 600;
        border-radius: 0.7rem;
        padding: 1rem 1.2rem;
        text-align: center;
        margin: 0 1.5rem 1.2rem 1.5rem;
        font-size: 1.08rem;
        box-shadow: 0 2px 8px 0 rgba(31,38,135,0.07);
    }
    .past-appts-footer {
        padding: 0 1.5rem 1.5rem 1.5rem;
    }
    .past-appts-footer .btn {
        width: 100%;
        font-size: 1.1rem;
        border-radius: 0.7rem;
        font-weight: 600;
        padding: 0.8rem 0;
    }
    @media (max-width: 600px) {
        .past-appts-card {
            margin-top: 1.2rem;
        }
        .past-appts-banner,
        .past-appts-list,
        .past-appts-info,
        .past-appts-footer {
            padding-left: 0.7rem;
            padding-right: 0.7rem;
        }
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
            <a href="past-appts.php" class="nav-link active">
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
    <div class="card past-appts-card rounded-4">
        <div class="past-appts-banner">
            <i class="bi bi-clock-history"></i>
            <h2>Geçmiş Randevularım</h2>
            <div class="desc">Tarihi geçmiş tüm randevularınız burada listelenir.</div>
        </div>
        <div class="past-appts-list">
            <div class="list-group list-group-flush mb-3">
                <?php
                if (mysqli_num_rows($result) == 0) {
                    echo '<div class="list-group-item text-dark fw-bold text-center">Geçmiş randevunuz bulunmamaktadır.</div>';
                } else {
                    while ($data = mysqli_fetch_assoc($result)) {
                        echo '<div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="bi bi-calendar-event"></i>' . htmlspecialchars($data["tarih"]) . ' - ' . htmlspecialchars($data["saat"]) . '
                                </span>
                                <span class="fw-bold">' . htmlspecialchars($data["durum"]) . '</span>
                            </div>';
                    }
                }
                ?>
            </div>
        </div>
        <div class="past-appts-info">
            <i class="bi bi-info-circle me-2"></i>
            Bu listede sadece tarihi geçmiş randevularınız yer alır.
        </div>
        <div class="past-appts-footer">
            
        </div>
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
mysqli_close($connection);
?>