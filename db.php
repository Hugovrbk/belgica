<?php
$host   = 'MYSQLHOST';
$dbname = 'MYSQLDATABASE'; // ← Ton nom de base confirmé
$user   = 'MYSQLUSER';
$pass   = 'MYSQLPASSWORD';       // ← Vide par défaut sur WAMP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
























