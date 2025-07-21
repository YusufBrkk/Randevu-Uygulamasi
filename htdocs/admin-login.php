<?php

include_once './header.php';

// Eğer zaten giriş yaptıysa, admin.php'ye yönlendir
if (isset($_SESSION["yoneticiID"])) {
    header("Location: admin.php");
    exit();
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include_once './includes/dbh-inc.php';
    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");
    $remember = isset($_POST["rememberMe"]);

    if ($email === "" || $password === "") {
        $error = "E-posta ve şifre gereklidir.";
    } else {
        $stmt = mysqli_prepare($connection, "SELECT yoneticiID, ad_soyad, sifre FROM yoneticiler WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row["sifre"])) {
                $_SESSION["yoneticiID"] = $row["yoneticiID"];
                $_SESSION["ad_soyad"] = $row["ad_soyad"];

                if ($remember) {
                    setcookie("remembered_email", $email, time() + (86400 * 30), "/"); // 30 gün
                    setcookie("remembered_password", $password, time() + (86400 * 30), "/"); // 30 gün
                } else {
                    setcookie("remembered_email", "", time() - 3600, "/"); // Çerezi sil
                    setcookie("remembered_password", "", time() - 3600, "/"); // Çerezi sil
                }

                header("Location: admin.php");
                exit();
            } else {
                $error = "Şifre yanlış.";
            }
        } else {
            $error = "Kullanıcı bulunamadı.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<style>
    body {
        background: #fff;
        min-height: 100vh;
    }
    .admin-login-card {
        max-width: 410px;
        width: 100%;
        border: none;
        box-shadow: 0 8px 32px 0 rgba(244,63,94,0.10), 0 1.5px 8px 0 rgba(0,0,0,0.07);
        background: #fff;
        padding: 2.7rem 2.1rem 2.1rem 2.1rem;
        margin-top: 2rem;
        border-radius: 1.3rem;
        position: relative;
        overflow: hidden;
    }
    .admin-login-card .btn {
        font-size: 1.1rem;
        padding: 0.85rem 1.2rem;
        display: flex;
        align-items: center;
        gap: 0.7rem;
        transition: background 0.2s, color 0.2s, box-shadow 0.2s;
        box-shadow: 0 2px 8px rgba(244,63,94,0.07);
    }
    .admin-login-card .btn-danger {
        background: linear-gradient(90deg, #f43f5e 0%, #f87171 100%);
        border: none;
        color: #fff;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    .admin-login-card .btn-danger:hover {
        background: linear-gradient(90deg, #be123c 0%, #ef4444 100%);
        color: #fff;
    }
    .admin-login-card .btn-outline-dark {
        border: 1.5px solid #222;
        color: #222;
        background: #fff;
    }
    .admin-login-card .btn-outline-dark:hover {
        background: #222;
        color: #fff;
    }
    .admin-login-card .btn i {
        font-size: 1.25em;
    }
    .admin-login-card h2 {
        letter-spacing: 1px;
        font-size: 2.1rem;
        color: #f43f5e;
        font-weight: 700;
    }
    .admin-login-card .form-label {
        font-weight: 500;
        color: #be123c;
        letter-spacing: 0.2px;
    }
    .admin-login-card .form-control:focus {
        border-color: #f43f5e;
        box-shadow: 0 0 0 0.15rem rgba(244,63,94,.13);
    }
    .admin-login-card .input-group-text {
        background: #f43f5e;
        color: #fff;
        border: none;
        font-size: 1.2rem;
    }
    .admin-login-card .alert-danger {
        background: #fee2e2;
        color: #b91c1c;
        border: none;
        font-weight: 600;
        letter-spacing: 0.2px;
    }
</style>

<div class="container d-flex flex-column align-items-center justify-content-center" style="min-height: 90vh;">
    <div class="card admin-login-card shadow rounded-4">
        <div class="text-center mb-4">
            <i class="bi bi-shield-lock-fill" style="font-size:2.7rem; color:#f43f5e;"></i>
            <h2 class="fw-bold mt-2 mb-0">Admin Giriş</h2>
            <div class="text-secondary fs-6 mb-2" style="color:#be123c!important;">Yönetici paneline erişmek için giriş yapın</div>
        </div>

        <!-- Formun hemen üstüne ekleyin -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');

    // Kayıtlı girişleri getir
    function getSavedLogins() {
        return JSON.parse(localStorage.getItem('savedLogins') || '[]');
    }

    // Giriş kaydet
    function saveLogin(email, password) {
        let logins = getSavedLogins();
        // Aynı email varsa güncelle
        logins = logins.filter(l => l.email !== email);
        logins.unshift({email, password});
        // Sadece son 5 kaydı tut
        logins = logins.slice(0, 5);
        localStorage.setItem('savedLogins', JSON.stringify(logins));
    }

    // Dropdown oluştur
    function showDropdown() {
        let logins = getSavedLogins();
        let dropdown = document.getElementById('login-dropdown');
        if (!dropdown) {
            dropdown = document.createElement('div');
            dropdown.id = 'login-dropdown';
            dropdown.style.position = 'absolute';
            dropdown.style.background = '#fff';
            dropdown.style.border = '1px solid #ccc';
            dropdown.style.width = emailInput.offsetWidth + 'px';
            dropdown.style.zIndex = 1000;
            dropdown.style.maxHeight = '180px';
            dropdown.style.overflowY = 'auto';
            dropdown.style.cursor = 'pointer';
            document.body.appendChild(dropdown); // body'ye ekle
        }

        // Pozisyonu input'un altına ayarla
        const rect = emailInput.getBoundingClientRect();
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
        dropdown.style.left = (rect.left + scrollLeft) + 'px';
        dropdown.style.top = (rect.bottom + scrollTop) + 'px';
        dropdown.style.width = rect.width + 'px';

        dropdown.innerHTML = '';
        logins.forEach(login => {
            const item = document.createElement('div');
            item.style.padding = '8px';
            item.style.borderBottom = '1px solid #eee';
            item.textContent = login.email;
            item.onclick = function() {
                emailInput.value = login.email;
                passwordInput.value = login.password;
                dropdown.style.display = 'none';
            };
            dropdown.appendChild(item);
        });
        dropdown.style.display = logins.length ? 'block' : 'none';
    }

    // E-posta inputuna tıklanınca dropdown göster
    emailInput.addEventListener('focus', showDropdown);
    emailInput.addEventListener('click', showDropdown);

    // Dışarı tıklanınca dropdown gizle
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('login-dropdown');
        if (dropdown && !emailInput.contains(e.target) && e.target !== dropdown) {
            dropdown.style.display = 'none';
        }
    });

    // Form submit olunca kaydet
    document.querySelector('form').addEventListener('submit', function() {
        if (document.getElementById('rememberMe').checked) {
            saveLogin(emailInput.value, passwordInput.value);
        }
    });
});
</script>

        <form method="post" autocomplete="off">
            <div class="mb-3">
                <label class="form-label d-block text-start" for="email">
                    <i class="bi bi-envelope-fill me-2"></i>
                    E-posta
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" id="email" class="form-control form-control-lg" placeholder="E-posta adresiniz" name="email" required autofocus
                        value="<?= isset($_COOKIE['remembered_email']) ? htmlspecialchars($_COOKIE['remembered_email']) : '' ?>" />
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label d-block text-start" for="password">
                    <i class="bi bi-lock-fill me-2"></i>
                    Şifre
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" id="password" class="form-control form-control-lg" placeholder="Şifreniz" name="password" required
                        value="<?= isset($_COOKIE['remembered_password']) ? htmlspecialchars($_COOKIE['remembered_password']) : '' ?>" />
                </div>

            </div>
            <div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" id="rememberMe" name="rememberMe" <?= isset($_COOKIE['remembered_email']) ? 'checked' : '' ?>>
    <label class="form-check-label" for="rememberMe">
        Beni Hatırla
    </label>
</div>
            <div class="d-grid gap-2 mb-3">
                <button class="btn btn-danger btn-lg" type="submit">
                    <i class="bi bi-box-arrow-in-right"></i> Giriş Yap
                </button>
                <a class="btn btn-outline-dark btn-lg" href="./index.php">
                    <i class="bi bi-arrow-left"></i> Geri
                </a>
            </div>
            <?php if ($error): ?>
                <div class="alert alert-danger fw-bold text-center py-2"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

        </form>
    </div>
</div>

<?php
include_once './footer.php';
?>