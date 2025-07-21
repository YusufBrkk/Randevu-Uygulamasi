<?php
include_once '../includes/db.php';

// Kategorileri ve ürünleri çek
$kategoriler = [];
$katResult = mysqli_query($connection, "SELECT * FROM kategoriler ORDER BY sira ASC, isim ASC");
while ($kat = mysqli_fetch_assoc($katResult)) {
    $kategoriler[$kat['id']] = $kat['isim'];
}

$urunler = [];
$urunResult = mysqli_query($connection, "SELECT * FROM urunler WHERE aktif=1 ORDER BY kategori_id, isim");
while ($urun = mysqli_fetch_assoc($urunResult)) {
    $urunler[$urun['kategori_id']][] = $urun;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Menü | Kafe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; }
        .menu-category { margin-bottom: 2.5rem; }
        .menu-title { font-size: 1.4rem; font-weight: 700; color: #2563eb; margin-bottom: 1.2rem; }
        .product-card {
            background: #fff;
            border-radius: 1.1rem;
            box-shadow: 0 4px 24px 0 rgba(31,38,135,0.10);
            padding: 1.2rem;
            margin-bottom: 1.2rem;
            display: flex;
            align-items: center;
            gap: 1.2rem;
        }
        .product-img {
            width: 70px; height: 70px; object-fit: cover; border-radius: 0.7rem; background: #e0e7ef;
        }
        .product-info { flex: 1; }
        .product-name { font-size: 1.13rem; font-weight: 600; }
        .product-desc { color: #64748b; font-size: 0.98rem; }
        .product-price { font-size: 1.08rem; font-weight: 500; color: #22c55e; }
        .add-btn { border-radius: 0.5rem; }
    </style>
</head>
<body>
<div class="container py-4">
    <h1 class="text-center mb-4"><i class="bi bi-cup-hot text-primary"></i> Menü</h1>
    <?php foreach ($kategoriler as $katId => $katIsim): ?>
        <div class="menu-category">
            <div class="menu-title"><?= htmlspecialchars($katIsim) ?></div>
            <?php if (!empty($urunler[$katId])): ?>
                <?php foreach ($urunler[$katId] as $urun): ?>
                    <div class="product-card">
                        <img src="../assets/img/<?= htmlspecialchars($urun['resim'] ?? 'noimage.png') ?>" class="product-img" alt="">
                        <div class="product-info">
                            <div class="product-name"><?= htmlspecialchars($urun['isim']) ?></div>
                            <?php if (!empty($urun['aciklama'])): ?>
                                <div class="product-desc"><?= htmlspecialchars($urun['aciklama']) ?></div>
                            <?php endif; ?>
                            <div class="product-price"><?= number_format($urun['fiyat'], 2) ?> ₺</div>
                        </div>
                        <button class="btn btn-primary add-btn">
                            <i class="bi bi-plus-circle"></i> Siparişlerime Ekle
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-muted">Bu kategoride ürün yok.</div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>