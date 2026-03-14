<?php session_start(); require_once '../../../db.php'; require_once '../html/check_admin.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $j=intval($_POST['journee']??0);$ed=trim($_POST['equipe_domicile']??'');$bd=intval($_POST['buts_domicile']??0);
    $ee=trim($_POST['equipe_exterieur']??'');$be=intval($_POST['buts_exterieur']??0);$dm=!empty($_POST['date_match'])?$_POST['date_match']:null;
    if(!$ed||!$ee||$j<1){$_SESSION['message']='Champs obligatoires manquants.';$_SESSION['message_type']='error';header('Location: ../html/admin.php');exit;}
    if($ed===$ee){$_SESSION['message']='Équipes identiques.';$_SESSION['message_type']='error';header('Location: ../html/admin.php');exit;}
    try{$pdo->prepare("INSERT INTO resultats(journee,equipe_domicile,buts_domicile,equipe_exterieur,buts_exterieur,date_match)VALUES(?,?,?,?,?,?)")->execute([$j,$ed,$bd,$ee,$be,$dm]);$_SESSION['message']='Résultat enregistré ! ✅';$_SESSION['message_type']='success';}
    catch(PDOException $e){$_SESSION['message']='Erreur : '.$e->getMessage();$_SESSION['message_type']='error';}
}
header('Location: ../html/admin.php');exit;
