<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoş Geldiniz | Yeni Web Sitesi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #f8fafc 0%, #e0e7ef 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .welcome-card {
            max-width: 420px;
            width: 100%;
            background: rgba(255,255,255,0.98);
            box-shadow: 0 8px 32px 0 rgba(31,38,135,0.15);
            border-radius: 1.2rem;
            padding: 2.5rem 2rem 2rem 2rem;
            text-align: center;
        }
        .welcome-card h1 {
            font-size: 2.1rem;
            font-weight: 700;
            color: #2563eb;
        }
        .welcome-card .lead {
            color: #64748b;
            font-size: 1.13rem;
        }
        .welcome-card .btn-primary {
            background: linear-gradient(90deg, #3b82f6 0%, #06b6d4 100%);
            border: none;
            border-radius: 0.5rem;
            font-weight: 500;
            font-size: 1.08rem;
            margin-top: 1.5rem;
        }
        .welcome-card .btn-primary:hover {
            background: linear-gradient(90deg, #2563eb 0%, #0891b2 100%);
        }
        .welcome-card .bi {
            font-size: 2.5rem;
            color: #06b6d4;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="welcome-card">
        <i class="bi bi-stars"></i>
        <h1>Hoş Geldiniz</h1>
        <p class="lead mb-3">Yepyeni, bağımsız bir web sitesine hoş geldiniz!<br>Bu sayfa tamamen örnek amaçlıdır ve istediğiniz gibi özelleştirilebilir.</p>
        <a href="https://getbootstrap.com/" target="_blank" class="btn btn-primary w-100">
            <i class="bi bi-box-arrow-up-right"></i> Bootstrap ile Daha Fazlası
        </a>
    </div>
</body>
</html>