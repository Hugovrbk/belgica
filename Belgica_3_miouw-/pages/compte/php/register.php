<?php
session_start(); // Démarre le système de session (indispensable sur chaque page).
require "db.php"; // Importe le fichier de connexion pour utiliser $pdo.

if ($_SERVER["REQUEST_METHOD"] === "POST") { // On vérifie que l'utilisateur a cliqué sur le bouton du formulaire (envoi en POST).

    $username = $_POST["username"]; // On récupère ce qui a été tapé dans le champ 'username'.
    $email = $_POST["email"];       // On récupère l'email.
    
    // password_hash : On transforme le mot de passe en code secret illisible pour plus de sécurité.
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    // On prépare la commande SQL avec des jetons (:username, etc.) pour éviter les piratages.
    $sql = "INSERT INTO user (username, email, password) VALUES (:username, :email, :password)";
    $stmt = $pdo->prepare($sql);

    try {
        // On remplace les jetons par les vraies valeurs et on envoie à la base de données.
        $stmt->execute([
            "username" => $username,
            "email"    => $email,
            "password" => $password
        ]);
        echo "Inscription réussie ✅"; 
    } catch (PDOException $e) {
        // Si l'email est déjà utilisé (clé unique), on tombe ici.
        echo "Erreur lors de l'inscription ❌ : " . $e->getMessage();
    }
}
?>  