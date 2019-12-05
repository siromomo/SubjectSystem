<?php
require_once "ConnectSQL.php";
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
$instructor_id = $_GET["instructor_id"];
$course_id = $_GET["course_id"];
$sec_id = $_GET["sec_id"];
$semester = $_GET["semester"];
$year = $_GET["year"];
$stmt = $conn->prepare("delete from teaches where instructor_id=? and sec_id=? and course_id=? and semester=? and `year`=?");
$stmt->bind_param("sissi",$instructor_id,$sec_id,$course_id,$semester,$year);
$r = $stmt->execute();
if(!$r){
    echo 2;
}else
    echo 0;
?>