<?php
session_start();
require_once '../../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['result_id'])) {

    $result_id = intval($_POST['result_id']);

    try {
        $sql  = "DELETE FROM resultats WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $result_id]);

        $_SESSION['message']      = 'Résultat supprimé avec succès ! ✅';
        $_SESSION['message_type'] = 'success';

    } catch (PDOException $e) {
        $_SESSION['message']      = 'Erreur lors de la suppression : ' . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }

} else {
    $_SESSION['message']      = 'Requête invalide.';
    $_SESSION['message_type'] = 'error';
}

header('Location: ../html/admin.php');
exit;
?>
