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

$conn = connectToDB();//"127.0.0.1",$role,$role

if(!$conn){
    echo 1;
    die();
}
$grade = $_GET["grade"];
$course_id = $_GET["course_id"];
$sec_id = $_GET["sec_id"];
$semester = $_GET["semester"];
$year = $_GET["year"];
$stu_id = $_GET["stu_id"];

if(drop_lesson($conn,$stu_id,$sec_id,$course_id,$semester,$year)){
    echo 0;
}else{
    echo 2;
}
?>