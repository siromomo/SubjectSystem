<?php
require_once 'ConnectSQL.php';
require_once 'readExcel.php';

session_start();
if(!isset($_SESSION['st_id'])) {
    jump_to_page("/SubjectSystem/index.php");
}
$role = $_SESSION['role'];
if($role === 'teacher'){
    jump_to_page("/SubjectSystem/personal_index_instructor.php");
}else if($role === 'student'){
    jump_to_page("/SubjectSystem/personal_index_student.php");
}

$conn = connectToDB();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">
    <title>教务系统·管理员主页</title>
    <script src="https://cdn.staticfile.org/jquery/2.1.1/jquery.min.js"></script>
    <script src="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container" id="myTab">
    <ul class="nav nav-tabs">
        <li class="active">
            <a href="#edit_students" data-toggle="tab">编辑学生信息</a>
        </li>
        <li>
            <a href="#edit_teachers" data-toggle="tab">编辑教师信息</a>
        </li>
        <li>
            <a href="#edit_sections" data-toggle="tab">编辑开课信息</a>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane fade in active" id="edit_students">
            <br/>
            <form class="form-inline" role="form" method="post" enctype="multipart/form-data" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                <div class="form-group">
                    <input type="file" id="file_stu" name="file_stu">
                </div>
                <button type="submit" class="btn btn-default">导入</button>
            </form>
        </div>
        <?php
        if(isset($_FILES['file_stu'])){
            if (is_uploaded_file($_FILES['file_stu']['tmp_name'])) {
                studentLoader($conn,$_FILES['file_stu']['tmp_name']);
            }
        }
        ?>
        <div class="tab-pane fade" id="edit_teachers">
            <br/>
            <form class="form-inline" role="form" method="post" enctype="multipart/form-data" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                <div class="form-group">
                    <input type="file" id="file_tea" name="file_tea">
                </div>
                <button type="submit" class="btn btn-default">导入</button>
            </form>
        </div>
        <?php
        if(isset($_FILES['file_tea'])){
            if (is_uploaded_file($_FILES['file_tea']['tmp_name'])) {
                teacherLoader($conn,$_FILES['file_tea']['tmp_name']);
            }
        }
        ?>
        <div class="tab-pane fade" id="edit_sections">
            <div class="row">
                3
            </div>
        </div>
    </div>
</div>
</body>
<script type="text/javascript">
$(document).ready(function () {
    if(location.hash){
        $('a[href='+location.hash+']').tab('show');
    }
    $(document.body).on("click","a[data-toggle]",function (event) {
        location.hash = this.getAttribute("href");
    });
});
$(window).on('popstate',function () {
    var anchor = location.hash || $("a[data-toggle=tab]").first().attr("href");
    $('a[href=' + anchor + ']').tab('show');
});
</script>