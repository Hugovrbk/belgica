<?php
// On vérifie si une session est déjà active, sinon on la démarre



// Vérification de sécurité : l'utilisateur doit être connecté ET admin (valeur 1)
// On utilise 'user_admin' car c'est le nom que nous avons défini dans login.php
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_admin']) || $_SESSION['user_admin'] != 1) {
    
    // Si la vérification échoue, on redirige vers le login
    // Le chemin remonte de 2 niveaux pour sortir de admin/html/ et aller dans compte/php/
    header("Location: ../../compte/php/login.php");
    exit(); 
}
?>