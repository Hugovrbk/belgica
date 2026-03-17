<?php
session_start();
if(!isset($_SESSION['user_id'])){ header("Location: ../html/login.html"); exit; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Mon compte — RFC Liège</title>
<link rel="stylesheet" href="../css/compte.css">
</head>
<body>
<div class="auth-layout">
    <div class="auth-brand">
        <div class="brand-content">
            <div class="brand-logo">
                <svg viewBox="0 0 54 54" fill="none"><path d="M27 2L50 11L50 31C50 43 27 52 27 52C27 52 4 43 4 31L4 11Z" fill="#C8102E" stroke="#8B0000" stroke-width="2"/><path d="M27 7L45 15L45 31C45 40 27 50 27 50C27 50 9 40 9 31L9 16Z" fill="#0E1A2B"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="Bebas Neue,sans-serif" font-size="9" fill="#fff" letter-spacing="1">RFCL</text><text x="50%" y="68%" dominant-baseline="middle" text-anchor="middle" font-family="Barlow Condensed,sans-serif" font-size="5.5" fill="#D4A843" letter-spacing="1.5">1892</text></svg>
            </div>
            <div class="brand-name">RFC Liège</div>
            <div class="brand-mat">Espace membre</div>
        </div>
    </div>
    <div class="auth-form-panel" style="text-align:center;">
        <div class="auth-form-title">Bienvenue !</div>
        <p class="auth-form-sub">Supporter Sang &amp; Marine 👋</p>
        <p style="font-size:1.1rem;color:rgba(255,255,255,.7);margin:16px 0 30px;font-weight:600;"><?= htmlspecialchars($_SESSION['username']) ?></p>
        <div style="display:flex;flex-direction:column;gap:12px;">
            <a href="../../../index.php" class="btn-auth" style="text-decoration:none;">🏠 Aller au site RFC Liège</a>
            <a href="logout.php" class="btn-auth-outline" style="text-decoration:none;">⏻ Se déconnecter</a>
        </div>
    </div>
</div>
</body>
</html>
