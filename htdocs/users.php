<?php
include_once './header.php';
include_once './includes/dbh-inc.php';

// Müşteri silme işlemi
if (isset($_GET['deleteID'])) {
    $deleteID = intval($_GET['deleteID']);
    $stmt = mysqli_prepare($connection, "DELETE FROM musteriler WHERE musteriID = ?");
    mysqli_stmt_bind_param($stmt, "i", $deleteID);
    if (mysqli_stmt_execute($stmt)) {
        $success = "Müşteri başarıyla silindi.";
    } else {
        $error = "Silme işlemi sırasında hata oluştu!";
    }
    mysqli_stmt_close($stmt);
}

// Müşteri güncelleme işlemi
if (isset($_POST['editID'])) {
    $editID = intval($_POST['editID']);
    $ad_soyad = trim($_POST['ad_soyad']);
    $telefon = trim($_POST['telefon']);
    if ($ad_soyad !== "" && $telefon !== "") {
        $stmt = mysqli_prepare($connection, "UPDATE musteriler SET ad_soyad=?, telefon=? WHERE musteriID=?");
        mysqli_stmt_bind_param($stmt, "ssi", $ad_soyad, $telefon, $editID);
        if (mysqli_stmt_execute($stmt)) {
            $success = "Müşteri bilgileri güncellendi.";
        } else {
            $error = "Güncelleme sırasında hata oluştu!";
        }
        mysqli_stmt_close($stmt);
    } else {
        $error = "Ad Soyad ve Telefon boş olamaz!";
    }
}

// Müşterileri çek
$result = mysqli_query($connection, "SELECT * FROM musteriler ORDER BY musteriID DESC");
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
    .users-card {
        max-width: 700px;
        width: 100%;
        border: none;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
        background: rgba(255,255,255,0.98);
        padding: 2.5rem 2rem 2rem 2rem;
        margin-top: 2rem;
    }
    .users-card .table thead {
        background: linear-gradient(90deg, #2563eb 0%, #06b6d4 100%);
        color: #fff;
    }
    .users-card .btn-outline-danger {
        border: 1.5px solid #dc3545;
        color: #dc3545;
        background: #fff;
    }
    .users-card .btn-outline-danger:hover {
        background: #dc3545;
        color: #fff;
    }
    .users-card .btn-outline-dark {
        border: 1.5px solid #222;
        color: #222;
        background: #fff;
    }
    .users-card .btn-outline-dark:hover {
        background: #222;
        color: #fff;
    }
    .users-card .btn-outline-primary {
        border: 1.5px solid #2563eb;
        color: #2563eb;
        background: #fff;
    }
    .users-card .btn-outline-primary:hover {
        background: #2563eb;
        color: #fff;
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
        <a href="users.php" class="nav-link active">
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

<div class="main-content">
    <div class="container d-flex flex-column align-items-center justify-content-center" style="min-height: 90vh;">
        <div class="card users-card rounded-4">
            <div class="text-center mb-4">
                <i class="bi bi-people text-primary" style="font-size:2.3rem;"></i>
                <h2 class="fw-bold mt-2 mb-0">Müşteriler</h2>
                <div class="text-secondary fs-6 mb-2">Kayıtlı tüm müşteriler</div>
            </div>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success fw-bold text-center py-2"><?= $success ?></div>
            <?php elseif (!empty($error)): ?>
                <div class="alert alert-danger fw-bold text-center py-2"><?= $error ?></div>
            <?php endif; ?>
            <div class="table-responsive rounded-3 mb-3">
                <table class="table table-striped mb-0 align-middle">
                    <thead>
                        <tr class="fs-5">
                            <th class="p-3" scope="col">ID</th>
                            <th class="p-3" scope="col">Ad Soyad</th>
                            <th class="p-3" scope="col">Telefon</th>
                            <th class="p-3 text-center" scope="col">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result) == 0) {
                            echo '<tr><td class="p-3 text-dark fw-bold fs-5 text-center" colspan="4">Kayıtlı müşteri yok.</td></tr>';
                        } else {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $id = $row["musteriID"];
                                $adsoyad = htmlspecialchars($row["ad_soyad"]);
                                $telefon = htmlspecialchars($row["telefon"]);
                                echo '<tr>
                                    <td class="p-3">' . $id . '</td>
                                    <td class="p-3">' . $adsoyad . '</td>
                                    <td class="p-3">' . $telefon . '</td>
                                    <td class="text-center">
                                        <button class="btn btn-outline-primary btn-sm me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editModal"
                                            data-id="' . $id . '"
                                            data-adsoyad="' . $adsoyad . '"
                                            data-telefon="' . $telefon . '">
                                            <i class="bi bi-pencil"></i> Düzenle
                                        </button>
                                        <a href="users.php?deleteID=' . $id . '" class="btn btn-outline-danger btn-sm" onclick="return confirm(\'Müşteri silinsin mi?\')">
                                            <i class="bi bi-trash"></i> Sil
                                        </a>
                                    </td>
                                </tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="d-grid col-6 mx-auto">
                <?php
                $backUrl = (isset($_SESSION["adminID"])) ? "./admin.php" : "./index.php";
                ?>
                <a class="btn btn-outline-dark btn-lg mt-3" href="<?= $backUrl ?>">
                    <i class="bi bi-arrow-left"></i> Geri
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Düzenle Modalı -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="post" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel"><i class="bi bi-pencil"></i> Müşteri Bilgilerini Düzenle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="editID" id="editID">
        <div class="mb-3">
            <label for="editAdSoyad" class="form-label">Ad Soyad</label>
            <input type="text" class="form-control" id="editAdSoyad" name="ad_soyad" required>
        </div>
        <div class="mb-3">
            <label for="editTelefon" class="form-label">Telefon</label>
            <input type="text" class="form-control" id="editTelefon" name="telefon" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary w-100">Kaydet</button>
      </div>
    </form>
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

    // Modal açılırken ilgili müşteri bilgilerini doldur
    var editModal = document.getElementById('editModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var adsoyad = button.getAttribute('data-adsoyad');
        var telefon = button.getAttribute('data-telefon');
        document.getElementById('editID').value = id;
        document.getElementById('editAdSoyad').value = adsoyad;
        document.getElementById('editTelefon').value = telefon;
    });
</script>

<?php
include_once './footer.php';
mysqli_close($connection);
?>