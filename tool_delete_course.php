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

$course_id = $_GET["course_id"];
if(delete_course($conn,$course_id)){
    echo 0;
}
?>