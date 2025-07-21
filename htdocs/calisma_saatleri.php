<?php
include_once './header.php';
include_once './includes/dbh-inc.php';

// Çalışma saatleri kaydet/güncelle
if (isset($_POST['save_hours'])) {
    $baslangic = $_POST['baslangic'];
    $bitis = $_POST['bitis'];
    $haftalik_tatil = isset($_POST['haftalik_tatil']) ? implode(',', $_POST['haftalik_tatil']) : '';
    $exists = mysqli_fetch_assoc(mysqli_query($connection, "SELECT id FROM calisma_saatleri LIMIT 1"));
    if ($exists) {
        $stmt = mysqli_prepare($connection, "UPDATE calisma_saatleri SET baslangic=?, bitis=?, haftalik_tatil=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "sssi", $baslangic, $bitis, $haftalik_tatil, $exists['id']);
    } else {
        $stmt = mysqli_prepare($connection, "INSERT INTO calisma_saatleri (baslangic, bitis, haftalik_tatil) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $baslangic, $bitis, $haftalik_tatil);
    }
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $success = "Çalışma saatleri güncellendi.";
}

// Tatil günü ekle
if (isset($_POST['add_holiday'])) {
    $tarih = $_POST['tatil_tarih'];
    $aciklama = trim($_POST['tatil_aciklama']);
    $stmt = mysqli_prepare($connection, "INSERT INTO tatil_gunleri (tarih, aciklama) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "ss", $tarih, $aciklama);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $success = "Tatil günü eklendi.";
}

// Tatil günü sil
if (isset($_GET['delete_holiday'])) {
    $id = intval($_GET['delete_holiday']);
    mysqli_query($connection, "DELETE FROM tatil_gunleri WHERE id=$id");
    $success = "Tatil günü silindi.";
}

// Mevcut verileri çek
$calisma = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM calisma_saatleri LIMIT 1"));
$tatil_gunleri = [];
$res = mysqli_query($connection, "SELECT * FROM tatil_gunleri ORDER BY tarih ASC");
while ($row = mysqli_fetch_assoc($res)) $tatil_gunleri[] = $row;

$hafta = ['Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi','Pazar'];
$tatil_list = isset($calisma['haftalik_tatil']) ? explode(',', $calisma['haftalik_tatil']) : [];
?>

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
    .settings-card {
        max-width: 600px;
        width: 100%;
        border: none;
        box-shadow: 0 8px 32px 0 rgba(31,38,135,0.15);
        background: rgba(255,255,255,0.98);
        padding: 2.5rem 2rem 2rem 2rem;
        margin: 2.5rem auto 0 auto;
        border-radius: 1.2rem;
    }
    .settings-card h2 { font-size: 1.5rem; font-weight: 700; }
    .settings-card .form-label { font-weight: 500; color: #2563eb; }
    .settings-card .btn-primary { border-radius: 0.5rem; font-weight: 500; }
    .settings-card .btn-outline-danger { border-radius: 0.5rem; }
    .settings-card .table th, .settings-card .table td { vertical-align: middle; }
    .settings-card .form-check-input:checked {
        background-color: #2563eb;
        border-color: #2563eb;
    }
    .settings-card .form-check-label {
        font-weight: 500;
        color: #334155;
    }
    .settings-card .table-striped > tbody > tr:nth-of-type(odd) {
        background-color: #f1f5f9;
    }
    .settings-card .table-striped > tbody > tr:nth-of-type(even) {
        background-color: #e0e7ef;
    }
    .settings-card .table tbody tr:hover {
        background: #dbeafe !important;
        transition: background 0.2s;
    }
    .settings-card hr {
        margin: 2rem 0 1.5rem 0;
        border-top: 2px solid #e0e7ef;
    }
</style>

<!-- Sidebar Aç/Kapa Butonu (Mobil) -->
<button class="sidebar-mobile-toggle" id="sidebarMobileToggle" title="Menüyü Aç/Kapat">
    <i class="bi bi-list"></i>
</button>

<!-- Sidebar -->
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
        <a href="appts.php" class="nav-link">
            <i class="bi bi-calendar2-week"></i>
            <span class="sidebar-link-text">Randevuları Yönet</span>
        </a>
        <a href="users.php" class="nav-link">
            <i class="bi bi-people"></i>
            <span class="sidebar-link-text">Müşterileri Görüntüle</span>
        </a>
        <a href="calisma_saatleri.php" class="nav-link active">
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
</script>

<div class="main-content">
    <div class="settings-card">
        <div class="mb-4 text-center">
            <i class="bi bi-clock-history text-primary" style="font-size:2.3rem;"></i>
            <h2 class="fw-bold mt-2 mb-0">Çalışma Saatleri & Tatil Günleri</h2>
            <div class="text-secondary fs-6 mb-2">Sistem genelinde geçerli olacak şekilde ayarlayın</div>
        </div>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success fw-bold text-center py-2"><?= $success ?></div>
        <?php endif; ?>
        <form method="post" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Başlangıç Saati</label>
                    <input type="time" class="form-control" name="baslangic" value="<?= htmlspecialchars($calisma['baslangic'] ?? '09:00') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Bitiş Saati</label>
                    <input type="time" class="form-control" name="bitis" value="<?= htmlspecialchars($calisma['bitis'] ?? '18:00') ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Haftalık Tatil Günleri</label>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($hafta as $gun): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="haftalik_tatil[]" value="<?= $gun ?>" id="tatil_<?= $gun ?>" <?= in_array($gun, $tatil_list) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="tatil_<?= $gun ?>"><?= $gun ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-12 mt-2">
                    <button type="submit" name="save_hours" class="btn btn-primary w-100"><i class="bi bi-save"></i> Kaydet</button>
                </div>
            </div>
        </form>
        <hr>
        <div class="mb-3">
            <h5 class="fw-bold mb-2"><i class="bi bi-calendar-x text-danger"></i> Özel Tatil Günleri</h5>
            <form method="post" class="row g-2 align-items-end mb-3">
                <div class="col-md-5">
                    <label class="form-label">Tatil Tarihi</label>
                    <input type="date" class="form-control" name="tatil_tarih" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Açıklama</label>
                    <input type="text" class="form-control" name="tatil_aciklama" placeholder="(Opsiyonel)">
                </div>
                <div class="col-md-2">
                    <button type="submit" name="add_holiday" class="btn btn-outline-danger w-100"><i class="bi bi-plus"></i> Ekle</button>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Tarih</th>
                            <th>Açıklama</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tatil_gunleri)): ?>
                            <tr><td colspan="3" class="text-center text-muted">Kayıtlı özel tatil günü yok.</td></tr>
                        <?php else: foreach ($tatil_gunleri as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['tarih']) ?></td>
                                <td><?= htmlspecialchars($row['aciklama']) ?></td>
                                <td>
                                    <a href="?delete_holiday=<?= $row['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Tatil günü silinsin mi?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
include_once './footer.php';
mysqli_close($connection);
?>