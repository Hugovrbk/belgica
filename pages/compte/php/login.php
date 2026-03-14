<?php
session_start();
require_once '../../../db.php';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $u=trim($_POST['username']??''); $e=trim($_POST['email']??''); $p=$_POST['password']??'';
    if(!$u||!$e||!$p){header("Location: ../html/login.html?error=1");exit;}
    $st=$pdo->prepare("SELECT * FROM user WHERE username=? AND email=?");
    $st->execute([$u,$e]); $user=$st->fetch();
    if($user && password_verify($p,$user['password'])){
        $_SESSION['user_id']=$user['id']; $_SESSION['username']=$user['username']; $_SESSION['user_admin']=$user['isadmin'];
        header("Location: ".($user['isadmin']==1?'../../admin/html/admin.php':'dashboard.php')); exit;
    }
    header("Location: ../html/login.html?error=1"); exit;
}
header("Location: ../html/login.html"); exit;
