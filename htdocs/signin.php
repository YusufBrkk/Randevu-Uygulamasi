<?php
include_once './header.php';
if (isset($_SESSION["userID"]) || isset($_SESSION["adminID"])) {
    header("Location: ./index.php");
    exit();
}
?>

<style>
    body {
        background: linear-gradient(120deg, #e0e7ef 0%, #f8fafc 100%);
        min-height: 100vh;
    }
    .login-plant-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(120deg, #e0e7ef 0%, #f8fafc 100%);
    }
    .login-plant-card {
        display: flex;
        flex-direction: row;
        background: #fff;
        border-radius: 2rem;
        box-shadow: 0 8px 32px 0 rgba(31,38,135,0.18);
        overflow: hidden;
        max-width: 820px;
        width: 100%;
    }
    .login-plant-image {
        background: linear-gradient(120deg, #3b82f6 0%, #06b6d4 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        width: 340px;
        min-height: 480px;
        position: relative;
    }
    .login-plant-image img {
        width: 220px;
        height: auto;
        object-fit: contain;
        filter: drop-shadow(0 8px 32px rgba(31,38,135,0.10));
        border-radius: 1.5rem;
    }
    @media (max-width: 900px) {
        .login-plant-card {
            flex-direction: column;
            max-width: 400px;
        }
        .login-plant-image {
            width: 100%;
            min-height: 180px;
            padding: 1.5rem 0;
        }
    }
    .login-plant-form {
        flex: 1;
        padding: 2.5rem 2.2rem 2rem 2.2rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .login-plant-form h2 {
        font-size: 2.2rem;
        font-weight: 700;
        color: #22223b;
        margin-bottom: 0.5rem;
        letter-spacing: 1px;
    }
    .login-plant-form .subtitle {
        color: #64748b;
        font-size: 1.1rem;
        margin-bottom: 2rem;
    }
    .login-plant-form .form-label {
        font-weight: 500;
        color: #222;
        margin-bottom: 0.3rem;
    }
    .login-plant-form .form-control {
        border-radius: 1.2rem;
        border: 1.5px solid #e0e7ef;
        padding: 0.9rem 1.1rem;
        font-size: 1.08rem;
        background: #f8fafc;
        margin-bottom: 1.2rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .login-plant-form .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 0.15rem rgba(59,130,246,.13);
        background: #fff;
    }
    .login-plant-form .btn-primary {
        background: linear-gradient(90deg, #3b82f6 0%, #06b6d4 100%);
        border: none;
        color: #fff;
        font-size: 1.15rem;
        font-weight: 600;
        border-radius: 1.2rem;
        padding: 0.9rem 1.2rem;
        margin-bottom: 0.7rem;
        box-shadow: 0 2px 8px rgba(31,38,135,0.07);
        transition: background 0.2s, color 0.2s, box-shadow 0.2s;
    }
    .login-plant-form .btn-primary:hover {
        background: linear-gradient(90deg, #2563eb 0%, #0891b2 100%);
        color: #fff;
    }
    .login-plant-form .btn-outline-dark {
        border: 1.5px solid #222;
        color: #222;
        background: #fff;
        font-weight: 600;
        border-radius: 1.2rem;
        padding: 0.9rem 1.2rem;
        margin-bottom: 0.7rem;
        transition: background 0.2s, color 0.2s;
    }
    .login-plant-form .btn-outline-dark:hover {
        background: #222;
        color: #fff;
    }
    .login-plant-form .form-label i {
        color: #3b82f6;
        margin-right: 0.5rem;
    }
    .login-plant-form .signup-link {
        color: #393f81;
        font-size: 1rem;
        margin-top: 1.2rem;
    }
    .login-plant-form .signup-link a {
        color: #3b82f6;
        font-weight: 600;
        text-decoration: none;
    }
    .login-plant-form .signup-link a:hover {
        text-decoration: underline;
    }
    .login-plant-form .alert {
        border-radius: 1rem;
        font-size: 1rem;
        margin-top: 0.5rem;
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const adSoyadInput = document.getElementById('ad_soyad');
    const telefonInput = document.getElementById('telefon');

    function getSavedLogins() {
        return JSON.parse(localStorage.getItem('savedSignins') || '[]');
    }

    function saveLogin(ad_soyad, telefon) {
        let logins = getSavedLogins();
        logins = logins.filter(l => l.ad_soyad !== ad_soyad || l.telefon !== telefon);
        logins.unshift({ad_soyad, telefon});
        logins = logins.slice(0, 5);
        localStorage.setItem('savedSignins', JSON.stringify(logins));
    }

    function showDropdown() {
        let logins = getSavedLogins();
        let dropdown = document.getElementById('signin-dropdown');
        if (!dropdown) {
            dropdown = document.createElement('div');
            dropdown.id = 'signin-dropdown';
            dropdown.style.position = 'absolute';
            dropdown.style.background = '#fff';
            dropdown.style.border = '1px solid #ccc';
            dropdown.style.zIndex = 1000;
            dropdown.style.maxHeight = '180px';
            dropdown.style.overflowY = 'auto';
            dropdown.style.cursor = 'pointer';
            document.body.appendChild(dropdown);
        }

        // Pozisyonu input'un altına ayarla
        const rect = adSoyadInput.getBoundingClientRect();
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
            item.textContent = login.ad_soyad + ' - ' + login.telefon;
            item.onclick = function() {
                adSoyadInput.value = login.ad_soyad;
                telefonInput.value = login.telefon;
                dropdown.style.display = 'none';
            };
            dropdown.appendChild(item);
        });
        dropdown.style.display = logins.length ? 'block' : 'none';
    }

    adSoyadInput.addEventListener('focus', showDropdown);
    adSoyadInput.addEventListener('click', showDropdown);

    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('signin-dropdown');
        if (dropdown && !adSoyadInput.contains(e.target) && e.target !== dropdown) {
            dropdown.style.display = 'none';
        }
    });

    document.querySelector('form').addEventListener('submit', function() {
        if (document.getElementById('rememberMe').checked) {
            saveLogin(adSoyadInput.value, telefonInput.value);
        }
    });
});
</script>

<div class="login-plant-wrapper">
    <div class="login-plant-card">
        <div class="login-plant-image d-none d-md-flex">
            <img src="https://cdn.pixabay.com/photo/2017/01/20/15/06/plant-1990280_1280.png" alt="Plant" />
        </div>
        <div class="login-plant-form">
            <div class="text-center mb-3">
                <i class="bi bi-box-arrow-in-right text-primary" style="font-size:2.7rem;"></i>
            </div>
            <h2 class="fw-bold mt-2 mb-0 text-center">Giriş Yap</h2>
            <div class="subtitle text-center">Ad Soyad ve Telefon ile giriş yapın</div>
            <form action="includes/signin-inc.php" method="post" autocomplete="off">
                <div class="mb-3">
                    <label class="form-label" for="ad_soyad">
                        <i class="bi bi-person-badge-fill"></i>
                        Ad Soyad
                    </label>
                    <input type="text" id="ad_soyad" class="form-control" name="ad_soyad"
                        value="<?= isset($_COOKIE['remembered_ad_soyad']) ? htmlspecialchars($_COOKIE['remembered_ad_soyad']) : '' ?>" />
                </div>
                <div class="mb-4">
                    <label class="form-label" for="telefon">
                        <i class="bi bi-telephone"></i>
                        Telefon Numarası
                    </label>
                    <input type="tel" id="telefon" class="form-control" name="telefon"
                        value="<?= isset($_COOKIE['remembered_telefon']) ? htmlspecialchars($_COOKIE['remembered_telefon']) : '' ?>" />
                       
                </div>
                  <div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" id="rememberMe" name="rememberMe" <?= isset($_COOKIE['remembered_ad_soyad']) ? 'checked' : '' ?>>
    <label class="form-check-label" for="rememberMe">
        Beni Hatırla
    </label>
                        </div>

                <button class="btn btn-primary w-100" type="submit" name="submit">
                    <i class="bi bi-box-arrow-in-right"></i> Giriş Yap
                </button>
                <a class="btn btn-outline-dark w-100" href="./index.php">
                    <i class="bi bi-arrow-left"></i> Geri
                </a>
                <div class="signup-link text-center">
                    Hesabınız yok mu? <a href="signup.php">Buradan kaydolun</a>
                </div>
                <?php
                if (isset($_GET["error"])) {
                    if ($_GET["error"] == "nosuchuser") {
                        echo '<div class="alert alert-danger fw-bold text-center py-2">Böyle bir kullanıcı yok.</div>';
                    }
                }
                ?>
            </form>
        </div>
    </div>
</div>

<?php
include_once './footer.php';
?>