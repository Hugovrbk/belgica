<?php
session_start();
require_once '../../../db.php';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $u=trim($_POST['username']??''); $e=trim($_POST['email']??''); $p=$_POST['password']??'';
    if(!$u||!$e||!$p){echo "Veuillez remplir tous les champs.";exit;}
    try {
        $pdo->prepare("INSERT INTO user (username,email,password,isadmin) VALUES(?,?,?,0)")->execute([$u,$e,password_hash($p,PASSWORD_DEFAULT)]);
        header("Location: ../html/login.html?registered=1"); exit;
    } catch(PDOException $ex){ echo "Erreur : ".$ex->getMessage(); }
}
