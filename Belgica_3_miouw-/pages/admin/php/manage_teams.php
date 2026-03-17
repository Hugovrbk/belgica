<?php
session_start();
require_once '../../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $nom = trim($_POST['nom_equipe']);

        if (empty($nom)) {
            $_SESSION['message']      = 'Le nom de l\'équipe est obligatoire.';
            $_SESSION['message_type'] = 'error';
            header('Location: ../html/admin.php');
            exit;
        }

        try {
            $sql  = "INSERT INTO equipes (nom) VALUES (:nom)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['nom' => $nom]);
            $_SESSION['message']      = 'Équipe ajoutée avec succès ! ✅';
            $_SESSION['message_type'] = 'success';
        } catch (PDOException $e) {
            $_SESSION['message']      = 'Erreur : ' . $e->getMessage();
            $_SESSION['message_type'] = 'error';
        }

    } elseif ($action === 'delete') {
        $id = intval($_POST['equipe_id']);
        try {
            $sql  = "DELETE FROM equipes WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            $_SESSION['message']      = 'Équipe supprimée. ✅';
            $_SESSION['message_type'] = 'success';
        } catch (PDOException $e) {
            $_SESSION['message']      = 'Erreur : ' . $e->getMessage();
            $_SESSION['message_type'] = 'error';
        }

    } else {
        $_SESSION['message']      = 'Action invalide.';
        $_SESSION['message_type'] = 'error';
    }

} else {
    $_SESSION['message']      = 'Requête invalide.';
    $_SESSION['message_type'] = 'error';
}

header('Location: ../html/admin.php');
exit;
?>
