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

$conn = connectToDB();

if(!$conn){
    echo 1;
    die();
}
$toStatus = $_GET["toStatus"];
switch ($toStatus){
    case "ini":
        $r = change_status2initializing($conn);
        break;
    case "sta":
        $r = change_status2starting($conn);
        break;
    case "gra":
        $r = change_status2grading($conn);
        break;
}
if($r){
    echo 0;
}


?>