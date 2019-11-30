<?php
/**
 * Created by PhpStorm.
 * User: 36513
 * Date: 2019/11/30
 * Time: 18:58
 */
require_once 'ConnectSQL.php';
require_once 'components.php';

session_start();
if(!isset($_SESSION['st_id']) || !isset($_GET['sec_id'])) {
    jump_to_page("/SubjectSystem/index.php");
}

$role = $_SESSION['role'];
$st_id = $_SESSION['st_id'];
$conn = connectToDB("localhost", $role, $role, "course_select_system");

$sec_id = $_GET['sec_id'];
$course_id = $_GET['course_id'];
$semester = $_GET['semester'];
$year = $_GET['year'];

$mem_list = get_member_for_section($conn, $sec_id, $course_id, $semester, $year);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">
    <title>选课系统·个人主页</title>
</head>
<body>
<div class="container">
    <div class="row rowIndex">
        <div class="jumbotron text-center">
            <h1>选课系统</h1>
        </div>
    </div>
    <div class="row">
        <table class="table table-striped">
            <caption class="table panel-heading"><h3>课程花名册</h3></caption>
            <thead>
            <tr>
                <th>学号</th>
                <th>姓名</th>
                <th>总学分</th>
                <th>gpa</th>
                <th>入学时间</th>
                <th>毕业时间</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($mem_list as $mem){
                echo "<tr>
                            <td>$mem->student_id</td>
                            <td>$mem->student_name</td>
                            <td>$mem->total_credit</td>
                            <td>$mem->gpa</td>
                            <td>$mem->enroll_time</td>
                            <td>$mem->graduate_time</td>
                          </tr>";
            }
            ?>
            </tbody>
            <a href="personal_index_instructor.php" class="btn btn-default">返回个人主页</a>
    </div>
</div>
</body>
</html>
