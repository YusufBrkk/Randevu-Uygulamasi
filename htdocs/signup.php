<?php
include_once './header.php';
if(isset($_SESSION["userID"])||isset($_SESSION["adminID"])){
    header("Location: ./index.php");
    exit();
}
?>

<style>
    body {
        background: linear-gradient(120deg, #f8fafc 0%, #e0e7ef 100%);
    }
    .signup-card {
        max-width: 400px;
        width: 100%;
        border: none;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.18);
        background: rgba(255,255,255,0.98);
        padding: 2.5rem 2rem 2rem 2rem;
        margin-top: 2rem;
    }
    .signup-card .btn {
        font-size: 1.1rem;
        padding: 0.85rem 1.2rem;
        display: flex;
        align-items: center;
        gap: 0.7rem;
        transition: background 0.2s, color 0.2s, box-shadow 0.2s;
        box-shadow: 0 2px 8px rgba(31,38,135,0.07);
    }
    .signup-card .btn-success {
        background: linear-gradient(90deg, #22c55e 0%, #16a34a 100%);
        border: none;
        color: #fff;
    }
    .signup-card .btn-success:hover {
        background: linear-gradient(90deg, #16a34a 0%, #15803d 100%);
        color: #fff;
    }
    .signup-card .btn-outline-dark {
        border: 1.5px solid #222;
        color: #222;
        background: #fff;
    }
    .signup-card .btn-outline-dark:hover {
        background: #222;
        color: #fff;
    }
    .signup-card .btn i {
        font-size: 1.25em;
    }
    .signup-card h2 {
        letter-spacing: 1px;
        font-size: 2.1rem;
    }
    .signup-card .form-label {
        font-weight: 500;
        color: #222;
    }
    .signup-card .form-control:focus {
        border-color: #22c55e;
        box-shadow: 0 0 0 0.15rem rgba(34,197,94,.15);
    }
</style>

<div class="container d-flex flex-column align-items-center justify-content-center" style="min-height: 90vh;">
    <div class="card signup-card rounded-4">
        <div class="text-center mb-4">
            <i class="bi bi-person-plus-fill text-success" style="font-size:2.7rem;"></i>
            <h2 class="fw-bold mt-2 mb-0">Kayıt Ol</h2>
            <div class="text-secondary fs-6 mb-2">Ad Soyad ve Telefon ile hızlıca kaydolun</div>
        </div>
        <form action="./includes/signup-inc.php" method="post" autocomplete="off">
            <div class="mb-3">
                <label class="form-label d-block text-start" for="ad_soyad">
                    <i class="bi bi-person-badge-fill me-2"></i>
                    Ad Soyad
                </label>
                <input type="text" id="ad_soyad" class="form-control form-control-lg" placeholder="Ad Soyad" name="ad_soyad" required />
            </div>
            <div class="mb-4">
                <label class="form-label d-block text-start" for="telefon">
                    <i class="bi bi-telephone me-2"></i>
                    Telefon Numarası
                </label>
                <input type="tel" id="telefon" class="form-control form-control-lg" placeholder="Telefon numaranız" name="telefon" required pattern="[0-9]{10,15}" />
            </div>
            <div class="d-grid gap-2 mb-3">
                <button class="btn btn-success btn-lg" type="submit" name="submit">
                    <i class="bi bi-person-plus"></i> Kaydol
                </button>
                <a class="btn btn-outline-dark btn-lg" href="./index.php">
                    <i class="bi bi-arrow-left"></i> Geri
                </a>
            </div>
            <p class="mb-3 pb-lg-2 text-center" style="color: #393f81;">
                Zaten hesabınız var mı? <a href="signin.php" style="color: #393f81;"><b>Giriş Yap</b></a>
            </p>
            <?php
            if (isset($_GET["error"])) {
                if ($_GET["error"] == "emptyinput") {
                    echo '<div class="alert alert-danger fw-bold text-center py-2">Lütfen tüm alanları doldurunuz!</div>';
                } else if ($_GET["error"] == "phoneexists") {
                    echo '<div class="alert alert-danger fw-bold text-center py-2">Bu telefon numarası ile zaten kayıt mevcut!</div>';
                } else if ($_GET["error"] == "stmtfail") {
                    echo '<div class="alert alert-warning fw-bold text-center py-2">Bir hata oluştu!</div>';
                }
            }
            ?>
        </form>
    </div>
</div>

<?php
include_once './footer.php';
?>