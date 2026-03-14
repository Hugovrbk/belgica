<?php session_start(); require_once '../../../db.php'; require_once '../html/check_admin.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $ea=trim($_POST['equipe_adversaire']??'');$st=trim($_POST['stade']??'');$dm=$_POST['date_match']??'';$co=trim($_POST['competition']??'');
    if(!$ea||!$st||!$dm){$_SESSION['message']='Champs obligatoires manquants.';$_SESSION['message_type']='error';header('Location: ../html/admin.php');exit;}
    if(new DateTime($dm)<=new DateTime()){$_SESSION['message']='La date doit être dans le futur.';$_SESSION['message_type']='error';header('Location: ../html/admin.php');exit;}
    try{$pdo->prepare("INSERT INTO matches(equipe_adversaire,stade,date_match,competition)VALUES(?,?,?,?)")->execute([$ea,$st,$dm,$co]);$_SESSION['message']='Match enregistré ! ✅';$_SESSION['message_type']='success';}
    catch(PDOException $e){$_SESSION['message']='Erreur : '.$e->getMessage();$_SESSION['message_type']='error';}
}
header('Location: ../html/admin.php');exit;
