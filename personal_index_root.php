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

$conn = connectToDB("127.0.0.1","collegeadmin","collegeadmin");

//insertIntoChartBasic("classroom", ["classroom_id", "capacity"], "si", $conn);
//insertIntoChartBasic("course", ["course_id", "course_name", "credit", "class_hours"], "ssii", $conn);
//insertIntoChartBasic("time_slot", ["time_slot_id", "start_time", "end_time", "day_of_week"], "sssi", $conn);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
        <div class="tab-pane fade in active" id="edit_students">
            <br/>
            <form class="form-inline" role="form" method="post" enctype="multipart/form-data" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                <div class="form-group">
                    <input type="file" id="file_stu" name="file_stu">
                </div>
                <button type="submit" class="btn btn-default">导入</button>
            </form>
            <br/>

            <hr style="height:1px;border:none;border-top:1px solid 	#D3D3D3;" />

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
                                        <button id='delete_personal_info_btn' name='delete_personal_info_btn' class='form-control btn btn-default' value='delete' type='submit'>删除</button>
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
//                            echo "<form class='tr' method='post' role='form'>
//                                       <span class='td'>{$course_des}</span>
//                                       <span class='td'><input name='new_grade' value='{$row2["grade"]}' class='form-control'></span>
//                                       <span class='td'><button name='change_takes_info_btn' class='form-control btn btn-primary' type='submit' value='change_grade'>修改成绩</button></span>
//                                  ";
//                            if(empty($row2["grade"])||strlen($row2["grade"])==0){
//                                echo "<span class='td'><button name='delete_takes_info_btn' class='form-control btn btn-default' type='submit' value='drop_class'>退课</button></span>
//                                 </form>";
//                            }else{
//                                echo "<span class='td'><button id='delete_takes_info_btn' class='form-control btn btn-default disabled' type='submit'>退课</button></span>
//                                </form>";
//                            }


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
                <?php
                if(!empty($_POST["grade_2"])){
                    echo "<script>alert('测试成功')</script>";
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
                            $stmt_fine_course->bind_param("s",$row2["course_id"]);
                            $stmt_fine_course->execute();
                            $course_result = $stmt_fine_course->get_result();
                            $row5 = $course_result->fetch_assoc();
                            $course_des = $row4["course_id"].".".$row4["sec_id"]." ".$row5["course_name"]." ".$row4["year"]." ".$row4["semester"];

                            if($row4["appii_status"] == "未处理"){
                                echo "<tr>
                                        <th>{$course_des}</th>
                                        <th><textarea class='form-control' rows='5'>{$row4["appli_content"]}</textarea></th>
                                        <th>{$row4["appii_status"]}</th>
                                        <th><button id='agree_btn' class='form-control btn btn-default' type='button'>通过</button></th>
                                        <th><button id='reject_btn' class='form-control btn btn-default' type='button'>驳回</button></th>
                                      </tr>";
                            }else{
                                echo "<tr>
                                        <th>{$course_des}</th>
                                        <th><textarea class='form-control' rows='5'>{$row4["appli_content"]}</textarea></th>
                                        <th>{$row4["appii_status"]}</th>
                                      </tr>";
                            }

                        }

                    }
                }
                ?>
                </tbody>
            </table>


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
            <br/>
            <form class="form-inline" role="form" method="post" enctype="multipart/form-data" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                <div class="form-group">
                    <input type="file" id="file_sec" name="file_sec">
                </div>
                <button type="submit" class="btn btn-default">导入</button>
            </form>
        </div>
        <?php
        if(isset($_FILES['file_sec'])){
            if (is_uploaded_file($_FILES['file_sec']['tmp_name'])) {
                sectionLoader($conn,$_FILES['file_sec']['tmp_name']);
            }
        }
        ?>
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

})

</script>