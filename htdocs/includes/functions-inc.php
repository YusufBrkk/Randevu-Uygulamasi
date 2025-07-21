<?php
include_once 'dbh-inc.php';
// Türkiye saati ayarı
date_default_timezone_set('Turkey');
$currentDateTime = new DateTime();
$TR_Time = $currentDateTime->format('H:i');

// Formdaki girişler boş mu kontrolü (Kayıt için)
function isEmptyInput($ad_soyad, $telefon)
{
    return (empty($ad_soyad) || empty($telefon));
}

// Admin girişi fonksiyonu
function adminSignin($connection, $email, $password)
{
    $sql = "SELECT * FROM yoneticiler WHERE email = ?";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        header("Location: ../admin-login.php?error=stmtfail");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Şifre kontrolü (şifre hash'li ise password_verify kullanın)
        if (password_verify($password, $row["sifre"])) {
            session_start();
            $_SESSION["adminID"] = $row["yoneticiID"];
            $_SESSION["ad_soyad"] = $row["ad_soyad"];
            header("Location: ../admin.php");
            exit();
        } else {
            header("Location: ../admin-login.php?error=wrongpassword");
            exit();
        }
    } else {
        header("Location: ../admin-login.php?error=nosuchadmin");
        exit();
    }
}

// Aynı telefon ile kullanıcı var mı kontrolü
function phoneExists($connection, $telefon)
{
    $sql = "SELECT * FROM musteriler WHERE telefon = ?";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        header("Location: ../signup.php?error=stmtfail");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $telefon);
    mysqli_stmt_execute($stmt);
    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        mysqli_stmt_close($stmt);
        return $row;
    } else {
        mysqli_stmt_close($stmt);
        return false;
    }
}

// Veritabanı kullanıcı kaydı
function createUser($connection, $ad_soyad, $telefon)
{
    $sql = "INSERT INTO musteriler (ad_soyad, telefon) VALUES (?, ?)";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        header("Location: ../signup.php?error=stmtfail");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "ss", $ad_soyad, $telefon);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: ../signin.php?error=none");
    exit();
}

// Kullanıcı girişi
function signinUser($connection, $ad_soyad, $telefon)
{
    $sql = "SELECT * FROM musteriler WHERE ad_soyad = ? AND telefon = ?";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        header("Location: ../signin.php?error=stmtfail");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "ss", $ad_soyad, $telefon);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        session_start();
        $_SESSION["userID"] = $row["musteriID"];
        $_SESSION["ad_soyad"] = $row["ad_soyad"];
        header("Location: ../index.php");
        exit();
    } else {
        header("Location: ../signin.php?error=nosuchuser");
        exit();
    }
}

// Başkasına ait randevu var mı kontrolü. Varsa o randevu döndürülür.
function isApptTaken($connection, $date, $time)
{
    $sql = "SELECT * FROM randevular WHERE tarih = ? AND saat = ?";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        header("Location: ../index.php?error=stmtfail");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "ss", $date, $time);
    mysqli_stmt_execute($stmt);
    $resultData = mysqli_stmt_get_result($stmt);

    if (mysqli_fetch_assoc($resultData)) {
        mysqli_stmt_close($stmt);
        return true;
    } else {
        mysqli_stmt_close($stmt);
        return false;
    }
}

// Veritabanı randevu kaydı
function makeAppt($connection, $uID, $date, $time)
{
    $sql = "INSERT INTO randevular (musteriID, tarih, saat) VALUES (?, ?, ?)";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        header("Location: ../index.php?error=stmtfail");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "sss", $uID, $date, $time);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: ../makeAppt.php?error=none");
    exit();
}

// Veritabanı randevu güncellemesi
function updateAppt($connection, $randevuID, $date, $time)
{
    $sql = "UPDATE randevular SET tarih = ?, saat = ? WHERE randevuID = ?";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        header("Location: ../index.php?error=stmtfail");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "ssi", $date, $time, $randevuID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: ../makeAppt.php?error=succapptupdt");
    exit();
}

// Müşterinin ID'sine denk gelen randevuların çekilmesi
function getAppt($connection, $uID)
{
    $sql = "SELECT randevular.randevuID, randevular.tarih, randevular.saat, musteriler.ad_soyad
        FROM randevular
        INNER JOIN musteriler ON randevular.musteriID = musteriler.musteriID
        WHERE randevular.musteriID = ? AND randevular.tarih >= ?
        ORDER BY randevular.tarih ASC, randevular.saat ASC";

    $stmt = mysqli_prepare($connection, $sql);
    $currentDate = date('Y-m-d');
    mysqli_stmt_bind_param($stmt, "ss", $uID, $currentDate);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        header("Location: ../index.php?error=stmtfail");
        exit();
    }

    mysqli_stmt_close($stmt);
    return $result;
}

// Tüm randevuların çekilmesi
function getAppts($connection, $prevAppts)
{
    if ($prevAppts == false) {
        $sql = "SELECT randevular.randevuID, randevular.tarih, randevular.saat, musteriler.ad_soyad
        FROM randevular
        INNER JOIN musteriler ON randevular.musteriID = musteriler.musteriID
        WHERE randevular.tarih >= ?
        ORDER BY randevular.tarih ASC, randevular.saat ASC";
    } else {
        $sql = "SELECT randevular.randevuID, randevular.tarih, randevular.saat, musteriler.ad_soyad
        FROM randevular
        INNER JOIN musteriler ON randevular.musteriID = musteriler.musteriID
        ORDER BY randevular.tarih ASC, randevular.saat ASC";
    }

    $stmt = mysqli_prepare($connection, $sql);

    if ($prevAppts == false) {
        $currentDate = date('Y-m-d');
        mysqli_stmt_bind_param($stmt, "s", $currentDate);
    }

    mysqli_stmt_execute($stmt);
    $response = mysqli_stmt_get_result($stmt);

    if (!$response) {
        header("Location: ../index.php?error=stmtfail");
        exit();
    }

    mysqli_stmt_close($stmt);
    return $response;
}

function deleteAppt($connection, $apptID)
{
    $sql = "DELETE FROM randevular WHERE randevuID = ?";
    $stmt = mysqli_prepare($connection, $sql);

    if (!$stmt) {
        header("Location: ../index.php?error=stmtfail");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i", $apptID);
    $result = mysqli_stmt_execute($stmt);

    if (!$result) {
        header("Location: ../index.php?error=stmtfail");
        exit();
    }

    mysqli_stmt_close($stmt);

    header("Location: ../appts.php?error=sccssfldelete");
    exit();
}