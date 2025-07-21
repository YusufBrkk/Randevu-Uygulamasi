<?php
include_once './header.php';
include_once './includes/dbh-inc.php';
session_start();

// Sadece adminler erişebilsin
//if (!isset($_SESSION["adminID"])) {
//    header("Location: ./index.php");
//    exit();}


$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ad_soyad = trim($_POST["ad_soyad"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($ad_soyad) || empty($email) || empty($password)) {
        $error = "Tüm alanları doldurun!";
    } else {
        // E-posta benzersiz mi kontrolü
        $stmt = mysqli_prepare($connection, "SELECT * FROM yoneticiler WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_fetch_assoc($result)) {
            $error = "Bu e-posta ile zaten bir admin var!";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($connection, "INSERT INTO yoneticiler (ad_soyad, email, sifre) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sss", $ad_soyad, $email, $hash);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Yeni admin başarıyla eklendi!";
            } else {
                $error = "Kayıt sırasında hata oluştu!";
            }
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<style>
    body {
        background: linear-gradient(120deg, #f8fafc 0%, #e0e7ef 100%);
    }
    .admin-add-card {
        max-width: 400px;
        width: 100%;
        border: none;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
        background: rgba(255,255,255,0.98);
        padding: 2.5rem 2rem 2rem 2rem;
        margin-top: 2rem;
    }
</style>

<div class="container d-flex flex-column align-items-center justify-content-center" style="min-height: 90vh;">
    <div class="card admin-add-card rounded-4">
        <div class="text-center mb-4">
            <i class="bi bi-person-plus-fill text-danger" style="font-size:2.7rem;"></i>
            <h2 class="fw-bold mt-2 mb-0">Yeni Admin Ekle</h2>
        </div>
        <?php if ($success): ?>
            <div class="alert alert-success fw-bold text-center py-2"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger fw-bold text-center py-2"><?= $error ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <div class="mb-3">
                <label class="form-label d-block text-start" for="ad_soyad">
                    <i class="bi bi-person-badge-fill me-2"></i>Ad Soyad
                </label>
                <input type="text" id="ad_soyad" class="form-control form-control-lg" name="ad_soyad" required />
            </div>
            <div class="mb-3">
                <label class="form-label d-block text-start" for="email">
                    <i class="bi bi-envelope me-2"></i>E-posta
                </label>
                <input type="email" id="email" class="form-control form-control-lg" name="email" required />
            </div>
            <div class="mb-4">
                <label class="form-label d-block text-start" for="password">
                    <i class="bi bi-lock me-2"></i>Şifre
                </label>
                <input type="password" id="password" class="form-control form-control-lg" name="password" required />
            </div>
            <div class="d-grid gap-2 mb-3">
                <button class="btn btn-danger btn-lg" type="submit">
                    <i class="bi bi-person-plus"></i> Admin Ekle
                </button>
                <a class="btn btn-outline-dark btn-lg" href="./admin.php">
                    <i class="bi bi-arrow-left"></i> Geri
                </a>
            </div>
        </form>
    </div>
</div>

<?php
include_once './footer.php';
?>