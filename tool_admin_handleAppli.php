<?php
require_once "ConnectSQL.php";
require_once "components.php";
session_start();
$role = $_SESSION['role'];
if($role == "root")
    $role = "collegeadmin";
else{
    echo 1;
    die();
}

$conn = connectToDB("127.0.0.1",$role,$role);

if(!$conn){
    echo 1;
    die();
}
$appli_id = $_GET["appli_id"];
$status = $_GET["status"];
if(handle_application($conn,$appli_id,$status)){
    echo 0;
}else{
    echo 2;
}
?>