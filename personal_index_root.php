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

$conn = connectToDB();//"127.0.0.1","collegeadmin","collegeadmin"

//insertIntoChartBasic("classroom", ["classroom_id", "capacity"], "si", $conn);
//insertIntoChartBasic("course", ["course_id", "course_name", "credit", "class_hours"], "ssii", $conn);
//insertIntoChartBasic("time_slot", ["time_slot_id", "start_time", "end_time", "day_of_week"], "sssi", $conn);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
<!--    <link rel="stylesheet" href="https://cdn.staticfile.org/twitter-bootstrap/4.3.1/css/bootstrap.min.css">-->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">

    <title>教务系统·管理员主页</title>
<!--    <script src="js/jquery.js"></script>-->
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
        <li>
            <a href="#status" data-toggle="tab">系统状态控制</a>
        </li>
    </ul>
    <br/>
    <div class="container">
        <div class="row">
            <div class="col-sm-offset-11">
                <button type="button" class="btn bg-primary" id="exit_btn" onclick="f()">退出</button>
            </div>
        </div>
    </div>
    <div class="tab-content">
        <div class="tab-pane active" id="edit_students">
            <br/>
            <label>导入学生信息</label>
            <form class="form-inline" role="form" method="post" enctype="multipart/form-data" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                <div class="form-group">
                    <input type="file" id="file_stu" name="file_stu">
                </div>
                <button type="submit" class="btn btn-default">导入</button>
            </form>
            <br/>
            <hr style="height:1px;border:none;border-top:1px solid 	#D3D3D3;" />

            <div class="container">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="list-group">
                            <div class="list-group-item active"><h5>手动导入</h5></div>
                            <form class="form list-group-item" role="form" method="post">
                                <input class="form-group form-control" placeholder="student id" name="import_stu_id">
                                <input class="form-group form-control" placeholder="student name" name="import_stu_name">
                                <input class="form-group form-control" placeholder="credits" name="import_credit">
                                <input class="form-group form-control" placeholder="gpa" name="import_gpa">
                                <input class="form-group form-control" placeholder="enroll time" name="import_etime">
                                <input class="form-group form-control" placeholder="graduate time" name="import_gtime">
                                <button class="btn btn-primary" type="submit" value="import_manual" name="import_m_btn">添加</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-sm-9">
                        <br/>
                        <form class="form-inline" role="form" method="post" id="search_form">
                            <div class="input-group input-group-lg">
                                <input id="stu_search" type="text" class="form-control" placeholder="输入学号" name="stu_search">
                                <span class="btn btn-default input-group-addon" id="search_btn">搜索</span>
                            </div>
                        </form>

                        <caption class="table panel-heading"><h4>个人信息</h4></caption>
                        <table class="table">
                            <thead>
                            <tr>
                                <th>学号</th>
                                <th>姓名</th>
                                <th>学分</th>
                                <th>绩点</th>
                                <th>入学时间</th>
                                <th>毕业时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $stu_id = "";
                            if(isset($_GET["stu_search"])){
                                $stmt_search_basic_info = $conn->prepare("select * from student where student_id = ?");
                                $stmt_search_basic_info->bind_param("s",$_GET["stu_search"]);
                                $stmt_search_basic_info->execute();
                                $basic_info_result = $stmt_search_basic_info->get_result();
                                if($basic_info_result->num_rows == 0){
                                    echo "<script>alert('无此学号学生')</script>";
                                    echo '<script>window.location.href="personal_index_root.php"</script>';
                                }else{
                                    $row = $basic_info_result->fetch_assoc();

                                    if(empty($row["graduate_time"])){
                                        echo "
                                      <form role='form' method='post'>
                                      <tr>
                                        <td>{$row["student_id"]}</td>
                                        <td><input name='new_name' class='form-control' value='{$row["student_name"]}'></td>
                                        <td><input name='new_credit' class='form-control' value='{$row["total_credit"]}'></td>
                                        <td><input name='new_gpa' class='form-control' value='{$row["gpa"]}'></td>
                                        <td><input name='new_etime' class='form-control' value='{$row["enroll_time"]}'></td>
                                        <td><input name='new_gtime' class='form-control' value='{$row["graduate_time"]}'></td>
                                      </tr>
                                      <input type='hidden' name='stu_id' value='{$row["student_id"]}'>
                                      <tr>  
                                         <button id='change_personal_info_btn' name='change_personal_info_btn' class='form-group btn btn-primary' value='change' type='submit'>修改</button>
                                        <button id='delete_personal_info_btn' name='delete_personal_info_btn' class='form-group btn btn-default' value='delete' type='submit'>删除</button>
                                      </tr>
                                      </form>";
                                    }else{
                                        echo "<form role='form' method='post'><tr>
                                        <td>{$row["student_id"]}<input name='stu_id' value='{$row["student_id"]}' type='hidden'></td>
                                        <td>{$row["student_name"]}<input name='new_name' value='{$row["student_name"]}' type='hidden'></td>
                                        <td>{$row["total_credit"]}<input name='new_credit' value='{$row["total_credit"]}' type='hidden'></td>
                                        <td>{$row["gpa"]}<input name='new_gpa' value='{$row["gpa"]}' type='hidden'></td>
                                        <td>{$row["enroll_time"]}<input name='new_etime' value='{$row["enroll_time"]}' type='hidden'></td>
                                        <td>{$row["graduate_time"]}<input name='new_gtime' value='{$row["graduate_time"]}' type='hidden'></td>
                                        <button id='delete_personal_info_btn' name='delete_personal_info_btn' class='form-control btn btn-primary' value='delete' type='submit'>删除</button>
                                      </tr></form>";
                                    }

                                }
                                $stmt_search_basic_info->free_result();
                            }
                            ?>
                            <?php
                            if(!empty($_POST["change_personal_info_btn"])){
                                $student = new Student($_POST["stu_id"],$_POST["new_name"],$_POST["new_credit"],$_POST["new_gpa"],$_POST["new_etime"],$_POST["new_gtime"]);
                                update_student_personal_info($conn,$student);
                                echo "<script language=JavaScript> location.replace(location.href);</script>";
                            }
                            if(!empty($_POST["delete_personal_info_btn"])){
                                delete_student_personal_info($conn,$_POST["stu_id"]);
                                echo '<script>window.location.href="personal_index_root.php"</script>';
                            }
                            ?>
                            </tbody>
                        </table>

                        <caption class="table panel-heading"><h4>学生选课信息</h4></caption>
                        <table class="table table-striped" role="form" method="post" id="personal_takes_form">
                            <thead>
                            <tr>
                                <th>课程</th>
                                <th>成绩</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            if(isset($_GET["stu_search"])){
                                $stmt_search_takes_info = $conn->prepare("select * from takes where student_id = ?");
                                $stmt_search_takes_info->bind_param("s",$_GET["stu_search"]);
                                $stmt_search_takes_info->execute();
                                $takes_result = $stmt_search_takes_info->get_result();
                                if($takes_result->num_rows == 0){

                                }else{
                                    while ($row2 = $takes_result->fetch_assoc()){
                                        $stmt_fine_course = $conn->prepare("select course_name from course where course_id = ?");
                                        $stmt_fine_course->bind_param("s",$row2["course_id"]);
                                        $stmt_fine_course->execute();
                                        $course_result = $stmt_fine_course->get_result();
                                        $row3 = $course_result->fetch_assoc();
                                        $course_des = $row2["course_id"].".".$row2["sec_id"]." ".$row3["course_name"]." ".$row2["year"]." ".$row2["semester"];

                                        echo "<tr>
                                        <th class='form-group'>{$course_des}</th>
                                        <input type='hidden' name='stu_id' value='{$_GET["stu_search"]}'>
                                        <input type='hidden' name='c_id' value='{$row2["course_id"]}'>
                                        <input type='hidden' name='s_id' value='{$row2["sec_id"]}'>
                                        <input type='hidden' name='sem' value='{$row2["semester"]}'>
                                        <input type='hidden' name='year' value='{$row2["year"]}'>
                                        <th><input name='new_grade' value='{$row2["grade"]}' class='form-control'></th>
                                        <th><button name='change_takes_info_btn' class='form-control btn btn-primary' type='submit' value='change_grade'>修改成绩</button></th>";
                                        if(empty($row2["grade"])||strlen($row2["grade"])==0){
                                            echo "<th><button name='delete_takes_info_btn' class='form-control btn btn-default' type='submit' value='drop_class'>退课</button></th>
                                     </tr>";
                                        }else{
                                            echo "<th><button class='form-control btn btn-default disabled' type='submit'>退课</button></th></tr>";
                                        }
                                        $stmt_fine_course->free_result();
                                    }

                                }
                                $stmt_search_takes_info->free_result();
                            }
                            ?>
                            </tbody>
                        </table>

                        <caption class="table panel-heading"><h4>学生申请信息</h4></caption>
                        <table class="table" role="form" method="post" id="personal_takes_form">
                            <thead>
                            <tr>
                                <th>申请课程</th>
                                <th>申请信息</th>
                                <th>申请状态</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            if(isset($_GET["stu_search"])){
                                $stmt_appli_info = $conn->prepare("select * from application where student_id = ?");
                                $stmt_appli_info->bind_param("s",$_GET["stu_search"]);
                                $stmt_appli_info->execute();
                                $appli_result = $stmt_appli_info->get_result();
                                if($appli_result->num_rows == 0){

                                }else{
                                    while ($row4 = $appli_result->fetch_assoc()){
                                        $stmt_fine_course = $conn->prepare("select course_name from course where course_id = ?");
                                        $stmt_fine_course->bind_param("s",$row4["course_id"]);
                                        $stmt_fine_course->execute();
                                        $course_result = $stmt_fine_course->get_result();
                                        $row5 = $course_result->fetch_assoc();
                                        $course_des = $row4["course_id"].".".$row4["sec_id"]." ".$row5["course_name"]." ".$row4["year"]." ".$row4["semester"];

                                        if($row4["appii_status"] == ""){
                                            echo "<tr>
                                        <td>{$course_des}</td>
                                        <td><textarea class='form-control' rows='5'>{$row4["appli_content"]}</textarea></td>
                                        <td>未处理</td>
                                        <input type='hidden' value='{$row4["appli_id"]}'>
                                        <td><button name='agree_btn' class='form-control btn btn-primary' type='button'>通过</button></td>
                                        <td><button name='reject_btn' class='form-control btn btn-default' type='button'>驳回</button></td>
                                      </tr>";
                                        }else{
                                            echo "<tr>
                                        <td>{$course_des}</td>
                                        <td><textarea class='form-control' rows='5'>{$row4["appli_content"]}</textarea></td>
                                        <td>{$row4["appii_status"]}</td>
                                      </tr>";
                                        }

                                    }

                                }
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>学号</th>
                            <th>姓名</th>
                            <th>学分</th>
                            <th>绩点</th>
                            <th>入学时间</th>
                            <th>毕业时间</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $stmt_search_all_basic_info = $conn->prepare("select * from student");
                        $stmt_search_all_basic_info->execute();
                        $basic_all_info_result = $stmt_search_all_basic_info->get_result();
                        while ($row = $basic_all_info_result->fetch_assoc()){
                            echo "<tr>
                                        <td>{$row["student_id"]}</td>
                                        <td>{$row["student_name"]}</td>
                                        <td>{$row["total_credit"]}</td>
                                        <td>{$row["gpa"]}</td>
                                        <td>{$row["enroll_time"]}</td>
                                        <td>{$row["graduate_time"]}</td>
                                      </tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php
            if(isset($_POST["import_m_btn"])){
                $student_new = new Student($_POST["import_stu_id"],$_POST["import_stu_name"],$_POST["import_credit"],$_POST["import_gpa"],$_POST["import_etime"],$_POST["import_gtime"]);
                import_one_student($conn,$student_new);
            }
            ?>



        </div>
        <?php
        if(isset($_FILES['file_stu'])){
            if (is_uploaded_file($_FILES['file_stu']['tmp_name'])) {
                studentLoader($conn,$_FILES['file_stu']['tmp_name']);
            }
        }
        ?>
        <div class="tab-pane " id="edit_teachers">
            <br/>
            <label>导入教师信息</label>
            <form class="form-inline" role="form" method="post" enctype="multipart/form-data" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                <div class="form-group">
                    <input type="file" id="file_tea" name="file_tea">
                </div>
                <button type="submit" class="btn btn-default btn-sm">导入</button>
            </form>
            <br/>
            <hr style="height:1px;border:none;border-top:1px solid 	#D3D3D3;" />
            <div class="container">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="list-group">
                            <div class="list-group-item active"><h5>手动导入</h5></div>
                            <form class="form list-group-item" method="post" role="form">
                                <input name="add_ins_id" class="form-group form-control" placeholder="instructor id">
                                <input name="add_ins_name" class="form-group form-control" placeholder="instructor name">
                                <input name="add_ins_htime" class="form-group form-control" placeholder="hire time">
                                <input name="add_ins_qtimr" class="form-group form-control" placeholder="quit time">
                                <button class="btn btn-primary" type="submit" name="add_ins_btn" value="添加">添加</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-sm-9">
                        <form class="form-inline" role="form" method="post" id="search_form">
                            <div class="input-group input-group-lg">
                                <input id="ins_search" type="text" class="form-control" placeholder="输入工号" name="ins_search">
                                <span class="btn btn-default input-group-addon" id="ins_search_btn">搜索</span>
                            </div>
                        </form>
                        <br/>
                        <caption class="table panel-heading"><h4>个人信息</h4></caption>
                        <table class="table">
                            <thead>
                            <tr>
                                <th>工号</th>
                                <th>姓名</th>
                                <th>入职时间</th>
                                <th>离职时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            if(isset($_GET["ins_search"])){
                                $stmt_ins_info = $conn->prepare("select * from instructor where instructor_id = ?");
                                $stmt_ins_info->bind_param("s",$_GET["ins_search"]);
                                $stmt_ins_info->execute();
                                $ins_info_result = $stmt_ins_info->get_result();
                                if($ins_info_result->num_rows == 0){
                                    echo "<script>alert('无此工号教师')</script>";
                                    echo '<script>window.location.href="personal_index_root.php#edit_teachers"</script>';
                                }else{
                                    $row = $ins_info_result->fetch_assoc();

                                    if(empty($row["quit_time"])){
                                        echo "
                                      <form role='form' method='post'>
                                      <tr>
                                        <td>{$row["instructor_id"]}</td>
                                        <td><input name='new_ins_name' class='form-control' value='{$row["instructor_name"]}'></td>
                                        <td><input name='new_ins_htime' class='form-control' value='{$row["hire_time"]}'></td>
                                        <td><input name='new_ins_qtime' class='form-control' value='{$row["quit_time"]}'></td>
                                      </tr>
                                      <input type='hidden' name='ins_id' value='{$row["instructor_id"]}'>
                                      <tr>  
                                         <button id='change_ins_info_btn' name='change_ins_info_btn' class='form-group btn btn-primary' value='change' type='submit'>修改</button>
                                        <button id='delete_ins_info_btn' name='delete_ins_info_btn' class='form-group btn btn-default' value='delete' type='submit'>删除</button>
                                      </tr>
                                      </form>";
                                    }else{
                                        echo "<form role='form' method='post'><tr>
                                        <td>{$row["instructor_id"]}<input name='ins_id' value='{$row["instructor_id"]}' type='hidden'></td>
                                        <td>{$row["instructor_name"]}<input name='new_ins_name' value='{$row["instructor_name"]}' type='hidden'></td>
                                        <td>{$row["hire_time"]}<input name='new_ins_htime' value='{$row["hire_time"]}' type='hidden'></td>
                                        <td>{$row["quit_time"]}<input name='new_ins_qtime' value='{$row["quit_time"]}' type='hidden'></td>
                                        <button id='delete_ins_info_btn' name='delete_ins_info_btn' class='form-control btn btn-primary' value='delete' type='submit'>删除</button>
                                      </tr></form>";
                                    }

                                }
                                $stmt_ins_info->free_result();
                            }
                            ?>
                            <?php
                            if(!empty($_POST["change_ins_info_btn"])){
                                $instructor = new Instructor($_POST["ins_id"],$_POST["new_ins_name"],$_POST["new_ins_htime"],$_POST["new_ins_qtime"]);
                                update_instructor($conn,$instructor);
                                echo "<script language=JavaScript> location.replace(location.href);</script>";
                            }
                            if(!empty($_POST["delete_ins_info_btn"])){
                                if(delete_teacher($conn,$_POST["ins_id"]))
                                    echo '<script>window.location.href="personal_index_root.php#edit_teachers"</script>';
                            }
                            ?>
                            </tbody>
                        </table>

                        <caption class="table panel-heading"><h4>任课信息</h4></caption>
                        <table class="table" role="form" method="post" id="personal_takes_form">
                            <thead>
                            <tr>
                                <th>任课课程</th>
                                <th>任课老师</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            if(isset($_GET["ins_search"])){
                                $stmt_teaches_info = $conn->prepare("select * from teaches natural join course where instructor_id = ?");
                                $stmt_teaches_info->bind_param("s",$_GET["ins_search"]);
                                $stmt_teaches_info->execute();
                                $teaches_result = $stmt_teaches_info->get_result();
                                if($teaches_result->num_rows == 0){

                                }else{
                                    while ($row = $teaches_result->fetch_assoc()){
                                        $course_des = $row["course_id"].".".$row["sec_id"]." ".$row["course_name"]." ".$row["year"]." ".$row["semester"];

                                        $stmt_search_college = $conn->prepare("select instructor_id,instructor_name from teaches natural join instructor
                                                                          where course_id=? and sec_id=? and semester=? and `year`=?");
                                        $stmt_search_college->bind_param("sisi",$row["course_id"],$row["sec_id"],$row["semester"],$row["year"]);
                                        $stmt_search_college->execute();
                                        $colleague_result = $stmt_search_college->get_result();
                                        $ins_list = "";
                                        $ins_num = $colleague_result->num_rows;
                                        while ($row_il = $colleague_result->fetch_assoc()){
                                            $ins_list = $ins_list." ".$row_il["instructor_id"].$row_il["instructor_name"];
                                        }
                                        $stmt_search_college->free_result();

                                        echo "<tr>
                                    <td>{$course_des}</td>
                                    <td>{$ins_list}</td>
                                    <input type='hidden' value='{$_GET["ins_search"]}'>
                                    <input type='hidden' value='{$row["course_id"]}'>
                                    <input type='hidden' value='{$row["sec_id"]}'>
                                    <input type='hidden' value='{$row["semester"]}'>
                                    <input type='hidden' value='{$row["year"]}'>
                                 ";

                                        if($ins_num > 1){
                                            echo "<td><button name='move_out_btn' class='form-control btn btn-primary btn-sm' type='button'>移除</button></td></tr>";
                                        }else{
                                            echo "<td><button class='form-control btn btn-default disabled btn-sm' type='button'>移除</button></td></tr>";
                                        }
                                    }
                                }
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>工号</th>
                            <th>姓名</th>
                            <th>入职时间</th>
                            <th>离职时间</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $stmt_ins_all_info = $conn->prepare("select * from instructor");
                        $stmt_ins_all_info->execute();
                        $ins_all_info_result = $stmt_ins_all_info->get_result();
                        while ($row = $ins_all_info_result->fetch_assoc()){
                            echo "<tr>
                                        <td>{$row["instructor_id"]}</td>
                                        <td>{$row["instructor_name"]}</td>
                                        <td>{$row["hire_time"]}</td>
                                        <td>{$row["quit_time"]}</td>
                                      </tr>";
                        }
                        ?>
                        </tbody>


                    </table>
                </div>
            </div>




            <?php
            if(isset($_POST["add_ins_btn"])){
                $instructor = new Instructor($_POST["add_ins_id"],$_POST["add_ins_name"],$_POST["add_ins_htime"],$_POST["add_ins_qtimr"]);
                import_one_instructor($conn,$instructor);
            }
            ?>


        </div>
        <?php
        if(isset($_FILES['file_tea'])){
            if (is_uploaded_file($_FILES['file_tea']['tmp_name'])) {
                teacherLoader($conn,$_FILES['file_tea']['tmp_name']);
            }
        }
        ?>

        <div class="tab-pane" id="edit_sections">
            <br/>
            <div class="container">
                <div class="row">
                    <div class="col-sm-6">
                        <label>导入课程信息</label>
                        <form class="form-inline" role="form" method="post" enctype="multipart/form-data" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                            <div class="form-group">
                                <input type="file" id="file_cour" name="file_cour">
                            </div>
                            <button type="submit" class="btn btn-default btn-sm">导入</button>
                        </form>
                    </div>
                    <div class="col-sm-6">
                        <label>导入开课信息</label>
                        <form class="form-inline" role="form" method="post" enctype="multipart/form-data" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                            <div class="form-group">
                                <input type="file" id="file_sec" name="file_sec">
                            </div>
                            <button type="submit" class="btn btn-default btn-sm">导入</button>
                        </form>
                    </div>
                </div>
            </div>

            <br/>
            <hr style="height:1px;border:none;border-top:1px solid 	#D3D3D3;" />
            <div class="container">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="list-group">
                            <div class="list-group-item active"><h5>导入课程</h5></div>
                            <form class="list-group-item" method="post" role="form">
                                <input name="addc_cid" class="form-group form-control" placeholder="课程代码">
                                <input name="addc_cname" class="form-group form-control" placeholder="课程名称">
                                <input name="addc_credit" class="form-group form-control" placeholder="学分">
                                <input name="addc_ch" class="form-group form-control" placeholder="学时">
                                <button class="btn btn-primary" type="submit" name="add_cour_btn" value="添加">添加/修改</button>
                            </form>
                        </div>
                        <?php
                        if(isset($_POST["add_cour_btn"])){
                            if(import_one_course($conn,$_POST["addc_cid"],$_POST["addc_cname"],$_POST["addc_credit"],$_POST["addc_ch"]))
                                echo "<script>window.location.href='personal_index_root.php?course_search=".$_POST["addc_cid"]."#edit_sections'</script>";
                            else
                                echo "<script>window.location.href='personal_index_root.php#edit_sections'</script>";
                        }
                        ?>
                        <div class="list-group">
                            <div class="list-group-item active"><h5>导入开课</h5></div>
                            <form class="form list-group-item" method="post" role="form">
                                <input name="adds_cid" class="form-group form-control" placeholder="课程代码">
                                <input name="adds_sid" class="form-group form-control" placeholder="课程段代码">
                                <input name="adds_semester" class="form-group form-control" placeholder="学期">
                                <input name="adds_year" class="form-group form-control" placeholder="学年">
                                <input name="adds_startweek" class="form-group form-control" placeholder="起始周">
                                <input name="adds_endweek" class="form-group form-control" placeholder="结束周">
                                <input name="adds_number" class="form-group form-control" placeholder="可选人数">
                                <input name="adds_instructor" class="form-group form-control" placeholder="任课教师">
                                <input name="adds_examweek" class="form-group form-control" placeholder="考试周">
                                <input name="adds_examday" class="form-group form-control" placeholder="考试天(1-7)">
                                <input name="adds_examtype" class="form-group form-control" placeholder="考试/其他">
                                <input name="adds_examdes" class="form-group form-control" placeholder="开/闭卷/其他">
                                <input name="adds_examtimestart" class="form-group form-control" placeholder="考试开始时间">
                                <input name="adds_examtimeend" class="form-group form-control" placeholder="考试结束时间">
                                <textarea name="adds_classtime" rows="5" class="form-group form-control" placeholder="教室号:时间段号1,时间段号2|教室号:时间段号|...|教室号:时间段号"></textarea>
                                <button class="btn btn-primary" type="submit" name="add_sec_btn" value="添加">添加/修改</button>
                            </form>
                        </div>
                        <?php
                        if(isset($_POST["add_sec_btn"])){
                            if(import_one_section($conn,$_POST["adds_cid"],$_POST["adds_sid"],$_POST["adds_semester"],$_POST["adds_year"],$_POST["adds_startweek"],
                                $_POST["adds_endweek"],$_POST["adds_number"],$_POST["adds_instructor"],
                                $_POST["adds_examweek"],$_POST["adds_examday"],$_POST["adds_examtype"],
                                $_POST["adds_examdes"],$_POST["adds_examtimestart"],$_POST["adds_examtimeend"],$_POST["adds_classtime"]))
                                echo "<script>window.location.href='personal_index_root.php?course_search=".$_POST["adds_cid"]."#edit_sections'</script>";
                            else
                                echo "<script>window.location.href='personal_index_root.php#edit_sections'</script>";
                        }
                        ?>
                    </div>
                    <div class="col-sm-9">
                        <form class="form-inline" role="form" method="post">
                            <div class="input-group input-group-lg">
                                <input id="course_search" type="text" class="form-control" placeholder="输入课程号" name="course_search">
                                <span class="btn btn-default input-group-addon" id="course_search_btn">搜索</span>
                            </div>
                        </form>
                        <br/>
                        <caption class="table panel-heading"><h4>开课信息</h4></caption>
                        <table class="table">
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
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            if(isset($_GET["course_search"])){
                                $sec_set = admin_get_section_course($conn,$_GET["course_search"]);
                            }else{
                                $sec_set = get_section_to_choose($conn);
                            }
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
                            <td><form method='post' role='form'>
                                <input type='hidden' name='del_course_id' value='$sec->course_id'>
                                <input type='hidden' name='del_sec_id' value='$sec->sec_id'>
                                <input type='hidden' name='del_semester' value='$sec->semester'>
                                <input type='hidden' name='del_year' value='$sec->year'>
                                <input type='submit' value='删除' class='btn btn-primary' name='delete_lesson'>
                            </form></td>
                          </tr>";
                            }


                            ?>

                            </tbody>
                        </table>
                        <caption class="table panel-heading"><h4>课程信息</h4></caption>
                        <table class="table">
                            <thead>
                            <tr>
                                <th>课程id</th>
                                <th>课程名称</th>
                                <th>课程学分</th>
                                <th>课程学时</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            if(isset($_GET["course_search"])){
                                $stmt_get_course = $conn->prepare("select * from course where course_id=?");
                                $stmt_get_course->bind_param("s",$_GET["course_search"]);
                                $stmt_get_course->execute();
                                $result = $stmt_get_course->get_result();
                                $row = $result->fetch_assoc();
                                if($result->num_rows){
                                    echo "<tr>
                            <td>{$row["course_id"]}</td>
                            <td>{$row["course_name"]}</td>
                            <td>{$row["credit"]}</td>
                            <td>{$row["class_hours"]}</td>
                            <td><form method='post' role='form'>
                                <input type='hidden' value='{$row["course_id"]}'>
                                <input type='submit' value='删除' class='btn btn-primary' name='delete_course'>
                            </form></td>
                          </tr>";
                                }

                            }
                            ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>



        </div>
        <?php
        if(isset($_FILES['file_sec'])){
            if (is_uploaded_file($_FILES['file_sec']['tmp_name'])) {
                sectionLoader($conn,$_FILES['file_sec']['tmp_name']);
            }
        }
        if(isset($_FILES['file_cour'])){
            if (is_uploaded_file($_FILES['file_cour']['tmp_name'])) {
                courseLoader($conn,$_FILES['file_cour']['tmp_name']);
            }
        }
        ?>

        <div class="tab-pane" id="status">
            <h4 id="now_status">系统状态:<?php echo get_system_status($conn);?></h4>
            <br/>
            <div class="container">
                <div class="row">
                    <button class="btn btn-danger" id="ini_btn">点此进入初始化</button>
                </div>
                <div class="row">
                    <label>此状态下学生/教师将只有查看权限，管理员需在此状态下初始化学生/老师/课程信息以免用户选课对初始数据修改造成干扰(状态:initializing)</label>
                </div>
                <br/>
                <div class="row">
                    <button class="btn btn-danger" id="sta_btn">点此开启选/退课系统</button>
                </div>
                <div class="row">
                    <label>点击后学生/教师将可以开始自由选/退课，教师无法在此阶段上传成绩(状态:starting)</label>
                </div>
                <br/>
                <div class="row">
                    <button class="btn btn-danger" id="gra_btn">点此关闭选/退课系统</button>
                </div>
                <div class="row">
                    <label>点击后选/退课系统将关闭，教师可以随时进行登分(状态:grading)</label>
                </div>

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

$(function () {
    $("#ini_btn").click(function () {
        $.ajax({
            url:"/SubjectSystem/tool_change_status.php?toStatus=ini",
            success:
                function (data) {
                    switch (data) {
                        case "0":
                            // $("#now_status").text("系统状态:initializing");
                            window.location.reload();
                            break;
                        case "1":
                            alert("数据库连接失败");
                            break;
                        default:
                            alert(data);
                    }
                }
        })
    })
    $("#sta_btn").click(function () {
        $.ajax({
            url:"/SubjectSystem/tool_change_status.php?toStatus=sta",
            success:
                function (data) {
                    switch (data) {
                        case "0":
                            window.location.reload();
                            break;
                        case "1":
                            alert("数据库连接失败");
                            break;
                        default:
                            alert(data);
                    }
                }
        })
    })
    $("#gra_btn").click(function () {
        $.ajax({
            url:"/SubjectSystem/tool_change_status.php?toStatus=gra",
            success:
                function (data) {
                    switch (data) {
                        case "0":
                            window.location.reload();
                            break;
                        case "1":
                            alert("数据库连接失败");
                            break;
                        default:
                            alert(data);
                    }
                }
        })
    })
    $("#exit_btn").click(function () {
        $.ajax({
            url:"/SubjectSystem/logout.php",
            success:
            function () {
                window.location.href = "/SubjectSystem/index.php";
            }

        })
    });
    $("#search_btn").click(function () {
        // $("#search_form").submit();
        if($("#stu_search").val() == "")
            window.location.href = "personal_index_root.php";
        else
            window.location.href = "personal_index_root.php?stu_search="+$("#stu_search").val();
    });
    $("#ins_search_btn").click(function () {
        if($("#ins_search").val() == "")
            window.location.href = "personal_index_root.php#edit_teachers";
        else
            window.location.href = "personal_index_root.php?ins_search="+$("#ins_search").val()+"#edit_teachers";

    });
    $("#course_search_btn").click(function () {
        if($("#course_search").val() == "")
            window.location.href = "personal_index_root.php#edit_sections";
        else
            window.location.href = "personal_index_root.php?course_search="+$("#course_search").val()+"#edit_sections";
    })

    $("[name='change_takes_info_btn']").click(function () {
        var th = $(this).parent("th");
        var grade = th.prev().children("input").val();
        var year = th.prev().prev();
        var semester = year.prev();
        var sec_id = semester.prev();
        var course_id = sec_id.prev();
        var stu_id = course_id.prev();


        $.ajax({
            url:"/SubjectSystem/tool_update_grade.php?grade="+grade+"&course_id="+course_id.val()+"&sec_id="+sec_id.val()+"&semester="+semester.val()+"&year="+year.val()+"&stu_id="+stu_id.val(),
            success:
                function (data) {
                // alert(data)
                    switch (data) {
                        case "0":
                            window.location.reload();
                            break;
                        case "1":
                            alert("数据库连接失败");
                            break;
                        case "2":
                            alert("输入的成绩格式错误，请检查");
                            break;
                        case "3":
                            alert("更新失败");
                            break;
                        default:
                            alert("未知错误 "+data);

                    }
                }

        })

    });
    $("[name='delete_takes_info_btn']").click(function () {
        var th = $(this).parent("th");
        var grade = th.prev().prev().children("input").val();
        var year = th.prev().prev().prev();
        var semester = year.prev();
        var sec_id = semester.prev();
        var course_id = sec_id.prev();
        var stu_id = course_id.prev();

        $.ajax({
            url:"/SubjectSystem/tool_admin_drop_sec.php?grade="+grade+"&course_id="+course_id.val()+"&sec_id="+sec_id.val()+"&semester="+semester.val()+"&year="+year.val()+"&stu_id="+stu_id.val(),
            success:
            function (data) {
                switch (data) {
                    case "0":
                        window.location.reload();
                        break;
                    case "1":
                        alert("数据库连接失败");
                        break;
                    case "2":
                        alert("删除失败");
                        break;
                    default:
                        alert("未知错误 "+data);
                }

            }
        })
    })
    $("[name='agree_btn']").click(function () {
        var td = $(this).parent("td");
        var appli_id = td.prev().val();

        $.ajax({
            url:"/SubjectSystem/tool_admin_handleAppli.php?appli_id="+appli_id+"&status=通过",
            success:
                function (data) {
                    switch (data) {
                        case "0":
                            window.location.reload();
                            break;
                        case "1":
                            alert("数据库连接失败");
                            break;
                        case "2":
                            alert("处理失败");
                            break;
                        default:
                            alert("未知错误 "+data);
                    }
                }
        })
    })
    $("[name='reject_btn']").click(function () {
        var td = $(this).parent("td");
        var appli_id = td.prev().prev().val();

        $.ajax({
            url:"/SubjectSystem/tool_admin_handleAppli.php?appli_id="+appli_id+"&status=拒绝",
            success:
                function (data) {
                    switch (data) {
                        case "0":
                            window.location.reload();
                            break;
                        case "1":
                            alert("数据库连接失败");
                            break;
                        case "2":
                            alert("处理失败");
                            break;
                        default:
                            alert("未知错误 "+data);
                    }
                }
        })
    })
    $("[name='move_out_btn']").click(function () {
        var td = $(this).parent("td");
        var year = td.prev();
        var semester = year.prev();
        var sec_id = semester.prev();
        var course_id = sec_id.prev();
        var instructor_id = course_id.prev();
        $.ajax({
            url:"/SubjectSystem/tool_remove_ins_from_sec.php?instructor_id="+instructor_id.val()+"&course_id="+course_id.val()+"&sec_id="+sec_id.val()+"&semester="+semester.val()+"&year="+year.val(),
            success:
            function (data) {
                switch (data) {
                    case "0":
                        window.location.reload();
                        break;
                    case "1":
                        alert("数据库连接失败");
                        break;
                    case "2":
                        alert("处理失败");
                        break;
                    default:
                        alert("未知错误 "+data);
                }
            }
        })

    })
    $("[name='delete_lesson']").click(function () {
        var year = $(this).prev();
        var semester = year.prev();
        var sec_id = semester.prev();
        var course_id = sec_id.prev();
        $.ajax({
            url:"/SubjectSystem/tool_delete_sec.php?course_id="+course_id.val()+"&sec_id="+sec_id.val()+"&semester="+semester.val()+"&year="+year.val(),
            success:
                function (data){
                    switch (data) {
                        case "0":
                            window.location.reload();
                            break;
                        case "1":
                            alert("数据库连接失败");
                            break;
                        default:
                            alert(data);
                    }
                }
        })

    })
    $("[name='delete_course']").click(function () {
        var course_id = $(this).prev().val();
        $.ajax({
            url:"/SubjectSystem/tool_delete_course.php?course_id="+course_id,
            success:
                function (data){
                    switch (data) {
                        case "0":
                            window.location.reload();
                            break;
                        case "1":
                            alert("数据库连接失败");
                            break;
                        default:
                            alert(data);
                    }
                }
        })
    })

})

</script>