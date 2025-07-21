<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once __DIR__ . '/includes/dbh-inc.php';
header('Content-Type: application/json');

$events = [];
$sql = "SELECT r.randevuID, r.tarih, r.saat, r.durum
        FROM randevular r
        WHERE r.durum != 'İptal'";
$result = mysqli_query($connection, $sql);
if (!$result) {
    echo json_encode(['error' => mysqli_error($connection)]);
    exit;
}
while ($row = mysqli_fetch_assoc($result)) {
    // start alanı: 2024-06-15T14:00:00 formatında olmalı
    $start = $row['tarih'] . 'T' . $row['saat'];
    $events[] = [
        'id' => $row['randevuID'],
        'title' => $row['saat'],
        'start' => $start,
        'durum' => $row['durum']
    ];
}
echo json_encode($events);
?>