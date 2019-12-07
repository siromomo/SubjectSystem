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
    jump_to_page("index.php");
}

$role = $_SESSION['role'];
$st_id = $_SESSION['st_id'];
$conn = connectToDB("127.0.0.1", $role, $role, "course_select_system");

$sec_id = $_GET['sec_id'];
$course_id = $_GET['course_id'];
$semester = $_GET['semester'];
$year = $_GET['year'];

$mem_list = get_member_for_section($conn, $sec_id, $course_id, $semester, $year);

$search_content = null;
$search_type = null;
if(isset($_GET["search_content"]) && isset($_GET['search_type'])){
    $search_content = $_GET['search_content'];
    $search_type = $_GET['search_type'];
}

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
    <form class="form-inline" action="section_member_list.php">
        <div class="col-sm-5">
            <label for="search">筛选学生</label>
            <input name="search_content" id="search_content" type="text" placeholder="输入筛选学生条件">
            <input type="radio" name="search_type" value="student_id" id="search_student_id">
            <label for="search_course_id">按照学生id</label>
            <input type="radio" name="search_type" value="student_name" id="search_student_name">
            <input type="hidden" name="sec_id" value="<?php echo $sec_id?>">
            <input type="hidden" name="course_id" value="<?php echo $course_id?>">
            <input type="hidden" name="semester" value="<?php echo $semester?>">
            <input type="hidden" name="year" value="<?php echo $year?>">
            <label for="search_course_name">按照学生名</label>
        </div>
        <div class="col-sm-1">
            <input type="submit" class="btn btn-default" value="筛选">
        </div>
    </form>
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
                $valid = false;
                if($search_type == null){
                    $valid = true;
                }
                else if($search_type === 'student_id'){
                    $valid = ($mem->student_id === $search_content);
                }
                else if($search_type === 'student_name'){
                    $valid = ($mem->student_name === $search_content);
                }
                if($valid) {
                    echo "<tr>
                            <td>$mem->student_id</td>
                            <td>$mem->student_name</td>
                            <td>$mem->total_credit</td>
                            <td>$mem->gpa</td>
                            <td>$mem->enroll_time</td>
                            <td>$mem->graduate_time</td>
                          </tr>";
                }
            }
            ?>
            </tbody>
            <a href="personal_index_instructor.php" class="btn btn-default">返回个人主页</a>
    </div>
</div>
</body>
</html>
