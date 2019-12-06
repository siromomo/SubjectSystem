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

$course_id = $_GET["course_id"];
$sec_id = $_GET["sec_id"];
$semester = $_GET["semester"];
$year = $_GET["year"];
if(delete_section($conn,$course_id,$sec_id,$semester,$year)){
    echo 0;
}
?>
