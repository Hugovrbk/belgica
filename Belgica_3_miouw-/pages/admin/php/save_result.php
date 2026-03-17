<?php
session_start();
require_once '../../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $journee          = intval($_POST['journee']);
    $equipe_domicile  = trim($_POST['equipe_domicile']);
    $buts_domicile    = intval($_POST['buts_domicile']);
    $equipe_exterieur = trim($_POST['equipe_exterieur']);
    $buts_exterieur   = intval($_POST['buts_exterieur']);
    $date_match       = !empty($_POST['date_match']) ? $_POST['date_match'] : null;

    if (empty($equipe_domicile) || empty($equipe_exterieur) || $journee < 1) {
        $_SESSION['message']      = 'Veuillez remplir tous les champs obligatoires.';
        $_SESSION['message_type'] = 'error';
        header('Location: ../html/admin.php');
        exit;
    }

    if ($equipe_domicile === $equipe_exterieur) {
        $_SESSION['message']      = 'Les deux équipes ne peuvent pas être identiques.';
        $_SESSION['message_type'] = 'error';
        header('Location: ../html/admin.php');
        exit;
    }

    try {
        $sql = "INSERT INTO resultats (journee, equipe_domicile, buts_domicile, equipe_exterieur, buts_exterieur, date_match)
                VALUES (:journee, :equipe_domicile, :buts_domicile, :equipe_exterieur, :buts_exterieur, :date_match)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'journee'          => $journee,
            'equipe_domicile'  => $equipe_domicile,
            'buts_domicile'    => $buts_domicile,
            'equipe_exterieur' => $equipe_exterieur,
            'buts_exterieur'   => $buts_exterieur,
            'date_match'       => $date_match,
        ]);

        $_SESSION['message']      = 'Résultat enregistré avec succès ! ✅';
        $_SESSION['message_type'] = 'success';

    } catch (PDOException $e) {
        $_SESSION['message']      = 'Erreur lors de l\'enregistrement : ' . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }

} else {
    $_SESSION['message']      = 'Requête invalide.';
    $_SESSION['message_type'] = 'error';
}

header('Location: ../html/admin.php');
exit;
?>
