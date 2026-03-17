<?php
session_start();
require_once '../../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['match_id'])) {
    
    $match_id = intval($_POST['match_id']);
    
    try {
        // Supprimer le match de la base de données
        $sql = "DELETE FROM matches WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $match_id]);
        
        $_SESSION['message'] = 'Match supprimé avec succès ! ✅';
        $_SESSION['message_type'] = 'success';
        
    } catch (PDOException $e) {
        $_SESSION['message'] = 'Erreur lors de la suppression : ' . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }
    
} else {
    $_SESSION['message'] = 'Requête invalide.';
    $_SESSION['message_type'] = 'error';
}

header('Location: ../html/admin.php');
exit;
?>
