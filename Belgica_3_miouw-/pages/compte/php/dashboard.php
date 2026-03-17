<?php
session_start();

require_once '../../admin/html/check_admin.php'; // Ce fichier bloque l'accès si on n'est pas admin

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tableau de bord</title>
</head>
<body>
    <h1>Bienvenue <?php echo htmlspecialchars($_SESSION['username']); ?> !</h1>
    <p>Vous êtes connecté ✅</p>
    <a href="logout.php">Se déconnecter</a>
    <a href="../../../index.php">Accéder au site d'accueil</a>
</body>
</html>