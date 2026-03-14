<?php session_start(); require_once '../../../db.php'; require_once '../html/check_admin.php';
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['result_id'])){
    try{$pdo->prepare("DELETE FROM resultats WHERE id=?")->execute([intval($_POST['result_id'])]);$_SESSION['message']='Résultat supprimé. ✅';$_SESSION['message_type']='success';}
    catch(PDOException $e){$_SESSION['message']='Erreur : '.$e->getMessage();$_SESSION['message_type']='error';}
}else{$_SESSION['message']='Requête invalide.';$_SESSION['message_type']='error';}
header('Location: ../html/admin.php');exit;
