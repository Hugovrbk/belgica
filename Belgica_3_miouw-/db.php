<?php
$host = 'localhost'; // L'adresse du serveur (ton PC en local).
$dbname = '';      // Le nom de ta base de données.
$user = 'root';      // Ton identifiant MySQL (par défaut 'root' sur Wamp).
$pass = '';          // Ton mot de passe (vide par défaut sur Wamp).

try {
    // On crée l'objet $pdo qui sert de tunnel entre PHP et MySQL
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    
    // Si une erreur survient, PHP doit lancer une "Exception" (un message d'alerte précis)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // On dit à PHP de ramener les résultats sous forme de tableau associatif par défaut
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Si la connexion échoue, on arrête tout et on affiche pourquoi
    die("Erreur de connexion : " . $e->getMessage());
}
?>