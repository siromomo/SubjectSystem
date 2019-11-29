<?php
require_once 'components.php';
if(!isset($_GET['course_id'])){
    jump_to_page("/SubjectSystem/personal_index_student.php");
}
$course_id = $_GET['course_id'];
$sec_id = $_GET['sec_id'];
$semester = $_GET['semester'];
$year = $_GET['year'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">
    <title>选课系统·选课申请</title>
</head>
<body>
<div class="container">
    <div class="row rowIndex">
        <div class="jumbotron text-center">
            <h1>选课系统</h1>
        </div>
    </div>
    <div class="row">
        <form role="form" action="personal_index_student.php" method="post">
            <div class="form-group">
                <label for="app_course_id">课程id</label>
                <input type="text" name="app_course_id" id="app_course_id" class="form-control"
                       placeholder="课程id" value="<?php echo $course_id;?>">
            </div>
            <div class="form-group">
                <label for="app_sec_id">课程段id</label>
                <input type="text" name="app_sec_id" id="app_sec_id" class="form-control"
                       placeholder="课程段id" value="<?php echo $sec_id;?>">
            </div>
            <div class="form-group">
                <label for="app_content">申请理由</label>
                <textarea name="app_content" id="app_content" class="form-control" rows="3"></textarea>
            </div>
            <input type="hidden" name="app_year" value="<?php echo $year;?>">
            <input type="hidden" name="app_semester" value="<?php echo $semester?>">
            <div class="form-group">
                <input class="btn btn-primary" type="submit" value="提交">
            </div>
        </form>
    </div>
</div>
</body>
</html>
