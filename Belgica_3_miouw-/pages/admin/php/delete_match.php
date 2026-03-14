<?php session_start(); require_once '../../../db.php'; require_once '../html/check_admin.php';
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['match_id'])){
    try{$pdo->prepare("DELETE FROM matches WHERE id=?")->execute([intval($_POST['match_id'])]);$_SESSION['message']='Match supprimé. ✅';$_SESSION['message_type']='success';}
    catch(PDOException $e){$_SESSION['message']='Erreur : '.$e->getMessage();$_SESSION['message_type']='error';}
}else{$_SESSION['message']='Requête invalide.';$_SESSION['message_type']='error';}
header('Location: ../html/admin.php');exit;
