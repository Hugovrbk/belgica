<?php
session_start();

require_once '../../../db.php'; // On remonte 3 fois pour atteindre la racine

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['username']) && !empty($_POST['email']) && !empty($_POST['password'])) {

        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $sql = "SELECT * FROM user WHERE username = ? AND email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            // ATTENTION : Le nom exact dans ta table SQL est 'isadmin'
            $_SESSION['user_admin'] = $user['isadmin']; 

            header("Location: dashboard.php");
            exit;
        } else {
            echo "Les informations ne correspondent pas ❌";
        }
    } else {
        echo "Veuillez remplir tous les champs ❗";
    }



    // Vérifie si les champs ne sont pas vides
    if (!empty($_POST['username']) && !empty($_POST['email']) && !empty($_POST['password'])) {

        $username = trim($_POST['username']); // trim() enlève les espaces inutiles au début/fin.
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // On cherche un utilisateur qui possède à la fois ce pseudo ET cet email.
        $sql = "SELECT * FROM user WHERE username = ? AND email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $email]);
        
        // fetch() récupère la ligne correspondante dans la base de données.
        $user = $stmt->fetch();

        // password_verify : On compare le mot de passe tapé avec celui (hashé) stocké en base.
        if ($user && password_verify($password, $user['password'])) {

            // Si c'est bon, on stocke ses infos dans la "Session" (la mémoire du serveur).
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_admin'] = $user['isadmin'];
            // Redirection vers la page d'accueil (dashboard).
            header("Location: dashboard.php");
            exit; // Très important : on arrête le script après une redirection.

        } else {
            echo "Les informations ne correspondent pas ❌";
        }
    } else {
        echo "Veuillez remplir tous les champs ❗";
    }
}
?>