<?php session_start(); require_once '../../../db.php'; require_once '../html/check_admin.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
    if($_POST['action']==='add'){
        $n=trim($_POST['nom_equipe']??'');
        if(!$n){$_SESSION['message']='Nom obligatoire.';$_SESSION['message_type']='error';header('Location: ../html/admin.php');exit;}
        try{$pdo->prepare("INSERT INTO equipes(nom)VALUES(?)")->execute([$n]);$_SESSION['message']='Équipe ajoutée ! ✅';$_SESSION['message_type']='success';}
        catch(PDOException $e){$_SESSION['message']='Erreur : '.$e->getMessage();$_SESSION['message_type']='error';}
    }elseif($_POST['action']==='delete'){
        try{$pdo->prepare("DELETE FROM equipes WHERE id=?")->execute([intval($_POST['equipe_id'])]);$_SESSION['message']='Équipe supprimée. ✅';$_SESSION['message_type']='success';}
        catch(PDOException $e){$_SESSION['message']='Erreur : '.$e->getMessage();$_SESSION['message_type']='error';}
    }
}
header('Location: ../html/admin.php');exit;
