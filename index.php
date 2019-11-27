<?php
/**
 * Created by PhpStorm.
 * User: 36513
 * Date: 2019/11/27
 * Time: 12:50
 */
require_once 'ConnectSQL.php';
require_once 'components.php';
$conn_root = connectToDB();
if(isset($_POST['st_id'])) {
    $st_id = $_POST['st_id'];
    $st_name=$_POST['st_name'];
    $role = $_POST['role'];
    $verify_user = false;
    $id_in_db = "";
    if($role === 'student'){
        $verify_user = $conn_root->prepare("select student_id from student where student_name=?");
    }else{
        $verify_user = $conn_root->prepare("select instructor_id from student where instructor_name=?");
    }

    if(!$verify_user){
        alert_error($conn_root);
    }
    $verify_user->bind_param("s", $st_name);
    $verify_user->execute();
    $verify_user->bind_result($id_in_db);
    $verify_user->fetch();
    if($id_in_db === $st_id){
        alert_msg("登录成功");
    }else{
        alert_msg($id_in_db . " " . $st_id);
    }

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">
    <title>选课系统</title>
</head>
<body>
<div class="container">
    <div class="row rowIndex">
        <div class="jumbotron text-center">
            <h1>选课系统</h1>
        </div>
    </div>
    <div class="row rowIndex">
        <div class="col-sm-7">
            <form class="form-horizontal" role="form" method="post" action="index.php">
                <div class="form-group">
                    <label for="firstname" class="col-sm-2 control-label">学/工号</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="st_id" placeholder="请输入学号/工号">
                    </div>
                </div>
                <div class="form-group">
                    <label for="lastname" class="col-sm-2 control-label">姓名</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="st_name" placeholder="请输入姓名">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <div class="checkbox">
                            <label class="col-sm-3">
                                <input type="checkbox" name="role" value="student">我是学生
                            </label>
                            <label>
                                <input type="checkbox" name="role" value="teacher">我是老师
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" class="btn btn-default">登录</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>