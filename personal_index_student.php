<?php
/**
 * Created by PhpStorm.
 * User: 36513
 * Date: 2019/11/27
 * Time: 20:48
 */

require_once 'ConnectSQL.php';
require_once 'components.php';

session_start();
if(!isset($_SESSION['st_id'])) {
    jump_to_page("/SubjectSystem/index.php");
}
$role = $_SESSION['role'];
$st_id = $_SESSION['st_id'];
$conn = connectToDB("localhost", $role, $role, "course_select_system");

if(isset($_GET['choose_or_drop'])){
    $cd_sec_id = $_GET['sec_id'];
    $cd_course_id = $_GET['course_id'];
    $cd_semester = $_GET['semester'];
    $cd_year = $_GET['year'];
    $res = false;
    if($_GET['choose_or_drop'] == 'choose'){
        $res = choose_lesson($conn, $st_id, $cd_sec_id, $cd_course_id, $cd_semester, $cd_year);
    }else{
        $res = drop_lesson($conn, $st_id, $cd_sec_id, $cd_course_id, $cd_semester, $cd_year);
    }
    if($res){
        jump_to_page("/SubjectSystem/personal_index_student.php");
    }
}

if(isset($_POST['app_course_id'])){
    //make_application($conn, $student_id, $sec_id, $course_id, $semester, $year, $appli_content)
    $app_course_id = $_POST['app_course_id'];
    $app_sec_id = $_POST['app_sec_id'];
    $app_semester = $_POST['app_semester'];
    $app_year = $_POST['app_year'];
    $app_content = $_POST['app_content'];
    $res = make_application($conn, $st_id, $app_sec_id, $app_course_id, $app_semester, $app_year, $app_content);
    if($res){
        alert_msg("申请课程成功");
        jump_to_page("/SubjectSystem/personal_index_student.php");
    }
}

$get_personal_info = $conn->prepare(
        "select student_name, total_credit, gpa, enroll_time, graduate_time from student where student_id=?");
$get_personal_info->bind_param("s", $st_id);
$name = "";
$total_credit = 0;
$gpa = 0;
$enroll_time = "";
$graduate_time = "";
$get_personal_info->execute();
$get_personal_info->bind_result($name, $total_credit, $gpa, $enroll_time, $graduate_time);
$get_personal_info->fetch();

$get_personal_info->free_result();

$sec_set = get_section_to_choose($conn);
$sec_set_chosen = get_section_have_chosen($conn, $st_id);
foreach($sec_set as $sec){
    foreach ($sec_set_chosen as $sec_chosen){
        if($sec_chosen == $sec){
            array_diff($sec_set, [$sec]);
        }
    }
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
    <div class="row">
        <div class="col-sm-3">
            <div class="list-group">
                <div class="list-group-item active">
                    <h3 class="list-group-item-heading">
                        个人信息
                    </h3>
                </div>
                <div class="list-group-item">
                    <h4>姓名：<?php echo $name;?></h4>
                </div>
                <div class="list-group-item"><h4>总学分：<?php echo $total_credit;?></h4></div>
                <div class="list-group-item"><h4>gpa：<?php echo $gpa;?></h4></div>
                <div class="list-group-item"><h4>入学时间：<?php echo $enroll_time;?></h4></div>
                <div class="list-group-item"><h4>毕业时间：<?php echo $graduate_time;?></h4></div>
            </div>
        </div>
        <div class="col-sm-9">
            <table class="table table-striped">
                <caption class="table panel-heading"><h3>已选课程列表</h3></caption>
                <thead>
                <tr>
                    <th>课程id</th>
                    <th>课程段id</th>
                    <th>学期</th>
                    <th>开始周</th>
                    <th>结束周</th>
                    <th>人数限制</th>
                    <th>当前人数</th>
                    <th>课程名称</th>
                    <th>上课时间</th>
                    <th>退课</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($sec_set_chosen as $sec){
                    echo "<tr>
                            <td>$sec->course_id</td>
                            <td>$sec->sec_id</td>
                            <td>$sec->semester</td>
                            <td>$sec->start_week</td>
                            <td>$sec->end_week</td>
                            <td>$sec->number</td>
                            <td>$sec->selected_num</td>
                            <td>$sec->course_name</td>
                            <td>$sec->class_to_time_str</td>
                            <td><form action='personal_index_student.php'>
                                <input type='hidden' name='course_id' value='$sec->course_id'>
                                <input type='hidden' name='sec_id' value='$sec->sec_id'>
                                <input type='hidden' name='semester' value='$sec->semester'>
                                <input type='hidden' name='year' value='$sec->year'>
                                <input type='hidden' name='student_id' value='$st_id'>
                                <input type='hidden' name='choose_or_drop' value='drop'>
                                <input type='submit' value='退课' class='btn btn-primary' id='drop_lesson'>
                            </form></td>
                          </tr>";
                }
                ?>
                </tbody>
            </table>
            <table class="table table-striped">
                <caption class="table panel-heading"><h3>可选课程列表</h3></caption>
                <thead>
                <tr>
                    <th>课程id</th>
                    <th>课程段id</th>
                    <th>学期</th>
                    <th>开始周</th>
                    <th>结束周</th>
                    <th>人数限制</th>
                    <th>当前人数</th>
                    <th>课程名称</th>
                    <th>上课时间</th>
                    <th>选课</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($sec_set as $sec){
                    echo "<tr>
                            <td>$sec->course_id</td>
                            <td>$sec->sec_id</td>
                            <td>$sec->semester</td>
                            <td>$sec->start_week</td>
                            <td>$sec->end_week</td>
                            <td>$sec->number</td>
                            <td>$sec->selected_num</td>
                            <td>$sec->course_name</td>
                            <td>$sec->class_to_time_str</td>
                            <td>";

                    if($sec->number > $sec->selected_num) {
                        echo "<form action='personal_index_student.php'>
                                <input type='hidden' name='course_id' value='$sec->course_id'>
                                <input type='hidden' name='sec_id' value='$sec->sec_id'>
                                <input type='hidden' name='semester' value='$sec->semester'>
                                <input type='hidden' name='year' value='$sec->year'>
                                <input type='hidden' name='student_id' value='$st_id'>
                                <input type='hidden' name='choose_or_drop' value='choose'>
                                <input type='submit' value='选课' class='btn btn-primary' id='choose_lesson'>
                            </form>";
                    }
                    else{
                        echo "<a href='make_application_student.php
                                                ?course_id=$sec->course_id&sec_id=$sec->sec_id
                                                &semester=$sec->semester&year=$sec->year' 
                                                class='btn btn-primary'>申请</a>";
                    }

                    echo "
                            </td>
                          </tr>";
                }
                ?>

                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
