<?php
require_once 'ConnectSQL.php';
require_once 'components.php';
session_start();

if(!isset($_SESSION['st_id'])) {
    jump_to_page("/SubjectSystem/index.php");
}

$role = $_SESSION['role'];
$st_id = $_SESSION['st_id'];
$conn = connectToDB("127.0.0.1", $role, $role, "course_select_system");

$stu_name = "";
$course_id = "";
$sec_id = 0;
$semester = "";
$year = 0;
$grade = "";
$total_credit = 0;
$gpa = 0.0;
$course_name = "";

$stmt_personal_info = $conn->prepare("select student_name,total_credit,gpa from student where student_id = ?");
$stmt_personal_info->bind_param("s",$st_id);
$stmt_personal_info->execute();
$stmt_personal_info->bind_result($stu_name,$total_credit,$gpa);
$stmt_personal_info->fetch();
$stmt_personal_info->free_result();

$stmt = $conn->prepare("select course_id,sec_id,semester,`year`,grade,course_name from takes natural join student natural join course where student_id = ?");
$stmt->bind_param("s",$st_id);
$stmt->execute();
$stmt->bind_result($course_id,$sec_id,$semester,$year,$grade,$course_name);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">
    <title>选课系统·查分页面</title>
    <script src="https://cdn.staticfile.org/jquery/2.1.1/jquery.min.js"></script>
    <script src="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container">
    <div class="row rowIndex">
        <div class="jumbotron text-center">
            <h1>查分系统</h1>
        </div>
    </div>
    <div class="row">
        <a href="personal_index_student.php" class="btn btn-default">返回个人主页</a>
        <br/><br/><br/>
    </div>

    <div class="row">
        <table class="table">
            <thead>
            <tr>
                <th>学号</th>
                <th>姓名</th>
                <th>学分</th>
                <th>绩点</th>
            </tr>
            </thead>
            <tbody>
            <?php
            echo "<tr>
                      <th>$st_id</th>
                      <th>$stu_name</th>
                      <th>$total_credit</th>
                      <th>$gpa</th>
                  </tr>"
            ?>
            </tbody>
        </table>
    </div>

    <div class="row">
        <table class="table table-striped">
            <caption class="table panel-heading"><h3>成绩</h3></caption>
            <thead>
            <tr>
                <th>课程名称</th>
                <th>该课成绩</th>
            </tr>
            </thead>
            <tbody>
            <?php
            while ($stmt->fetch()){
                $course_des = $course_id.".".$sec_id." ".$course_name." ".$year." ".$semester;
                echo "<tr><td>$course_des</td>";
                if(empty($grade) || strlen($grade) == 0){
                    echo "<td>--</td></tr>";
                }else{
                    echo "<td>$grade</td></tr>";
                }
            }
            ?>
            </tbody>

    </div>

</div>
</body>
</html>
