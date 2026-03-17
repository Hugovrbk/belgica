<?php
session_start();
require_once '../../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Récupérer et nettoyer les données du formulaire
    $equipe_adversaire = trim($_POST['equipe_adversaire']);
    $stade = trim($_POST['stade']);
    $date_match = $_POST['date_match'];
    $competition = trim($_POST['competition']);
    
    // Vérifier que les champs obligatoires sont remplis
    if (empty($equipe_adversaire) || empty($stade) || empty($date_match)) {
        $_SESSION['message'] = 'Veuillez remplir tous les champs obligatoires.';
        $_SESSION['message_type'] = 'error';
        header('Location: ../html/admin.php');
        exit;
    }
    
    // Vérifier que la date est dans le futur
    $date_obj = new DateTime($date_match);
    $now = new DateTime();
    
    if ($date_obj <= $now) {
        $_SESSION['message'] = 'La date du match doit être dans le futur.';
        $_SESSION['message_type'] = 'error';
        header('Location: ../html/admin.php');
        exit;
    }
    
    try {
        // Insérer le nouveau match dans la base de données
        $sql = "INSERT INTO matches (equipe_adversaire, stade, date_match, competition) 
                VALUES (:equipe_adversaire, :stade, :date_match, :competition)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'equipe_adversaire' => $equipe_adversaire,
            'stade' => $stade,
            'date_match' => $date_match,
            'competition' => $competition
        ]);
        
        $_SESSION['message'] = 'Match enregistré avec succès ! ✅';
        $_SESSION['message_type'] = 'success';
        
    } catch (PDOException $e) {
        $_SESSION['message'] = 'Erreur lors de l\'enregistrement : ' . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }
    
    header('Location: ../html/admin.php');
    exit;
    
} else {
    header('Location: ../html/admin.php');
    exit;
}
?>
