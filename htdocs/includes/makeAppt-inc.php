<?php
session_start();
include_once 'dbh-inc.php';
include_once 'functions-inc.php';

// Admin randevu alamaz
if (isset($_SESSION["adminID"])) {
    header("Location: ../makeAppt.php?error=noadmappt");
    exit();
}

// Kullanıcı oturum açmamışsa giriş sayfasına yönlendir
if (!isset($_SESSION["userID"])) {
    header("Location: ../signin.php");
    exit();
}

// Tarih gönderilmemişse ana sayfaya yönlendir
if (!isset($_POST["date"]) || !isset($_POST["time"])) {
    header("Location: ../makeAppt.php");
    exit();
}

$uID = $_SESSION["userID"];
$apptDate = $_POST["date"];
$apptHour = $_POST["time"];
$date = date('Y-m-d', strtotime($apptDate));
$time = date('H:i', strtotime($apptHour));

// Randevu güncelleme isteği
if (isset($_POST["type"]) && $_POST["type"] === "update") {
    if (isApptTaken($connection, $date, $time)) {
        header("Location: ../appts.php?error=appttaken");
        exit();
    }
    if ($date < date('Y-m-d') || ($date == date('Y-m-d') && $time <= date('H:i'))) {
        header("Location: ../appts.php?error=pastdate");
        exit();
    }
    // Randevu güncellenirken durum tekrar "Bekliyor" yapılır
    $updateID = intval($_POST["updateID"]);
    $stmt = mysqli_prepare($connection, "UPDATE randevular SET tarih=?, saat=?, durum='Bekliyor' WHERE randevuID=?");
    mysqli_stmt_bind_param($stmt, "ssi", $date, $time, $updateID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: ../appts.php");
    exit();
}

// Randevu alınmış mı kontrolü
$stmt = mysqli_prepare($connection, "SELECT * FROM randevular WHERE tarih = ? AND saat = ?");
mysqli_stmt_bind_param($stmt, "ss", $date, $time);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    header("Location: ../makeAppt.php?error=appttaken");
    exit();
}

// Geçmiş tarih kontrolü
if ($date < date('Y-m-d') || ($date == date('Y-m-d') && $time <= date('H:i'))) {
    header("Location: ../makeAppt.php?error=pastdate");
    exit();
}

// Randevu kaydı (durum: Bekliyor)
$stmt = mysqli_prepare($connection, "INSERT INTO randevular (musteriID, tarih, saat, durum) VALUES (?, ?, ?, 'Bekliyor')");
mysqli_stmt_bind_param($stmt, "iss", $uID, $date, $time);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header("Location: ../makeAppt.php?error=none");
exit();
?>