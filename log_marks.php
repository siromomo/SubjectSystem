<?php
require_once 'ConnectSQL.php';
require_once 'components.php';
require_once 'readExcel.php';
session_start();
if(!isset($_SESSION['st_id']) || !isset($_GET['sec_id'])) {
    jump_to_page("/SubjectSystem/index.php");
}
$role = $_SESSION['role'];
$st_id = $_SESSION['st_id'];
$conn = connectToDB("127.0.0.1", $role, $role, "course_select_system");

$sec_id = $_GET['sec_id'];
$course_id = $_GET['course_id'];
$semester = $_GET['semester'];
$year = $_GET['year'];
$stmt_search_course = $conn->prepare("select course_name from course where course_id = ?");
$stmt_search_course->bind_param("s",$course_id);
$stmt_search_course->execute();
$course_name = "";
$stmt_search_course->bind_result($course_name);
$stmt_search_course->fetch();
$stmt_search_course->free_result();
$course_des = $course_id.".".$sec_id.$course_name;

$stmt_search_students_of_this_sec = $conn->prepare("select student_id,student_name,enroll_time,grade 
                                                           from takes natural join student where
                                                           course_id = ? and sec_id = ? and semester = ? and `year` = ?");
$stmt_search_students_of_this_sec->bind_param("sisi",$course_id,$sec_id,$semester,$year);
$stu_id_on_sec = "";
$stu_name_on_sec = "";
$stu_etime_on_sec = "";
$stu_grade_on_sec = "";
$stmt_search_students_of_this_sec->execute();
$stmt_search_students_of_this_sec->bind_result($stu_id_on_sec,$stu_name_on_sec,$stu_etime_on_sec,$stu_grade_on_sec);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">
    <title>选课系统·登分页面</title>
    <script src="https://cdn.staticfile.org/jquery/2.1.1/jquery.min.js"></script>
    <script src="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container">
    <div class="row rowIndex">
        <div class="jumbotron text-center">
            <h1>登分系统</h1>
        </div>
    </div>
    <div class="row">
        <a href="personal_index_instructor.php" class="btn btn-default">返回个人主页</a>
        <br/><br/><br/>
    </div>

    <div class="row">
        <form class="form-inline" role="form" method="post" enctype="multipart/form-data" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
            <div class="form-group">
                <input type="file" id="file_score" name="file_score">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">导入</button>
        </form>
    </div>
    <?php
    if(isset($_FILES['file_score'])){
        if (is_uploaded_file($_FILES['file_score']['tmp_name'])) {
            scoreLoader($_SESSION["role"],$_FILES['file_score']['tmp_name'],$course_id,$sec_id,$semester,$year);
            echo "<script language=JavaScript> location.replace(location.href);</script>";
        }
    }
    ?>

    <div class="row">
        <table class="table table-striped">
            <caption class="table panel-heading"><h3>登分</h3></caption>
            <thead>
            <tr>
                <th>课程名称</th>
                <th>学号</th>
                <th>姓名</th>
                <th>入学时间</th>
                <th>该课成绩</th>
            </tr>
            </thead>
            <tbody>
            <?php
            while ($stmt_search_students_of_this_sec->fetch()){
                echo "<tr><td>$course_des</td>
                    <td>$stu_id_on_sec</td>
                    <td>$stu_name_on_sec</td>
                    <td>$stu_etime_on_sec</td>";
                if(strlen($stu_grade_on_sec)==0 || empty($stu_grade_on_sec)){
                    echo "<td>
                               <form class='form-inline' role='form' method='post'>
                                    <input placeholder='输入成绩' name='submit_grade'>
                                    <input type='hidden' name='submit_stu_id' value='{$stu_id_on_sec}'>
                                    <button type='submit' class='btn btn-primary btn-sm'>提交</button>
                                </form>
                          </td>";
                }else{
                    echo "<td>
                                <label>{$stu_grade_on_sec}</label>
                         
                          </td>";
                }

            }

            if(isset($_POST["submit_grade"])){
                commit_grade_for_one_student($conn,$_POST["submit_stu_id"],$course_id,$sec_id,$semester,$year,$_POST["submit_grade"]);
                echo "<script language=JavaScript> location.replace(location.href);</script>";
            }
            ?>
            </tbody>

    </div>

</div>
</body>
</html>
