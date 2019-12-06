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
if($role === 'student'){
    jump_to_page("/SubjectSystem/personal_index_student.php");
}else if($role === 'root'){
    jump_to_page("/SubjectSystem/personal_index_root.php");
}
$st_id = $_SESSION['st_id'];
$conn = connectToDB("127.0.0.1", $role, $role, "course_select_system");

if(isset($_GET['new_status'])){
    $app_id = $_GET['app_id'];
    $new_status = $_GET['new_status'];
    $r = handle_application($conn, $app_id, $new_status);
    if($r){
        alert_msg("处理申请成功");
        jump_to_page("/SubjectSystem/personal_index_instructor.php");
    }
}

$get_personal_info = $conn->prepare(
    "select instructor_name, hire_time from instructor where instructor_id=?");
$get_personal_info->bind_param("s", $st_id);
$name = "";
$hire_time = "";
$get_personal_info->execute();
$get_personal_info->bind_result($name, $hire_time);
$get_personal_info->fetch();
$get_personal_info->free_result();
$sec_set = get_sec_for_instructor($conn, $st_id);
$app_list = get_app_for_sec_set($conn, $sec_set);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">
    <title>选课系统·个人主页</title>
    <script src="https://cdn.staticfile.org/jquery/2.1.1/jquery.min.js"></script>
    <script src="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container">
    <div class="row rowIndex">
        <div class="jumbotron text-center">
            <h1>选课系统</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-offset-11">
            <button type="button" class="btn bg-primary" id="exit_btn" onclick="f()">退出</button>
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
                <div class="list-group-item"><h4>入职时间：<?php echo $hire_time;?></h4></div>
            </div>
        </div>
        <div class="col-sm-9">
            <table class="table table-striped">
                <caption class="table panel-heading"><h3>教学课程列表</h3></caption>
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
                    <th>操作</th>
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
                            <td><a class='btn btn-default' 
                            href='section_member_list.php?sec_id=$sec->sec_id&course_id=$sec->course_id&semester=$sec->semester&year=$sec->year'>
                            查看花名册</a>
                            <a class='btn btn-default ";
                    if(!check_if_have_privilege($conn,"student","UPDATE"))
                        echo "disabled";
                    echo "' 
                            href='log_marks.php?sec_id=$sec->sec_id&course_id=$sec->course_id&semester=$sec->semester&year=$sec->year'>
                            登分</a></td>
                            
                          </tr>";
                }
                ?>
                </tbody>
            </table>
            <table class="table table-striped">
                <caption class="table panel-heading"><h3>申请列表</h3></caption>
                <thead>
                <tr>
                    <th>申请id</th>
                    <th>学生学号</th>
                    <th>申请内容</th>
                    <th>课程id</th>
                    <th>课程段id</th>
                    <th>学期</th>
                    <th>年份</th>
                    <th>申请状态</th>
                    <th>处理</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($app_list as $app){
                    if($app->app_status == ''){
                        $app->app_status = '未处理';
                    }
                    echo "<tr>
                            <td>$app->app_id</td>
                            <td>$app->app_stu_id</td>
                            <td>$app->app_content</td>
                            <td>$app->app_course_id</td>
                            <td>$app->app_sec_id</td>
                            <td>$app->app_semester</td>
                            <td>$app->app_year</td>
                            <td>$app->app_status</td>";
                    if($app->app_status == '未处理') {
                        echo "
                            <td>
                                <form action='personal_index_instructor.php'>
                                    <input type='hidden' name='app_id' value='$app->app_id'>
                                    <input type='radio' name='new_status' class='radio-inline' value='通过'>
                                    <label class='list-group-item-text'>通过</label>
                                    <input type='radio' name='new_status' class='radio-inline' value='拒绝'>
                                    <label class='list-group-item-text'>拒绝</label>
                                    <input type='submit' value='提交' class='btn btn-primary'>
                                </form>
                            </td>
                          </tr>";
                    }
                    else{
                        echo "<td>已处理</td></tr>";
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
<script type="text/javascript">
    $(function () {
        $("#exit_btn").click(function () {
            $.ajax({
                url:"/SubjectSystem/logout.php",
                success:
                    function () {
                        window.location.href = "/SubjectSystem/index.php";
                    }

            })
        });
    })
</script>
