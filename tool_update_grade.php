<?php
require_once "ConnectSQL.php";
/**
 * 这里写的是管理员更改成绩，权限高于教师的
 */
session_start();
$role = $_SESSION['role'];
if($role == "root")
    $role = "collegeadmin";
else{
    echo 1;
    die();
}

$conn = connectToDB("127.0.0.1",$role,$role);

function convert2cre($grade){
    $cre = 0;
    switch ($grade){
        case "A":$cre = 4.0;break;
        case "A-":$cre = 3.7;break;
        case "B+":$cre = 3.3;break;
        case "B":$cre = 3.0;break;
        case "B-":$cre = 2.7;break;
        case "C+":$cre = 2.3;break;
        case "C":$cre = 2.0;break;
        case "C-":$cre = 1.7;break;
        case "D+":$cre = 1.3;break;
        case "D":$cre = 1.0;break;
        case "F":$cre = 0.0;break;
        default:$cre = 5.0;
    }
    return $cre;
}

if(!$conn){
    echo 1;
    die();
}
$grade = $_GET["grade"];
$course_id = $_GET["course_id"];
$sec_id = $_GET["sec_id"];
$semester = $_GET["semester"];
$year = $_GET["year"];
$stu_id = $_GET["stu_id"];

$cre = convert2cre($grade);
if(intval($cre) == 5){
    echo 2;
    die();
}


$conn->autocommit(false);

$stmt = $conn->prepare("update takes set grade = ? 
                               where student_id = ? and course_id = ? and sec_id = ? and semester = ? and `year` = ?");
$stmt_set_null = $conn->prepare("update takes set grade = null 
                               where student_id = ? and course_id = ? and sec_id = ? and semester = ? and `year` = ?");
$stmt_update_credit_gpa = $conn->prepare("update student set total_credit=?,gpa=? where student_id=?");

$stmt_previous_state = $conn->prepare("select total_credit,gpa from student where student_id = ?");
$stmt_previous_state->bind_param("s",$stu_id);
$stmt_previous_state->execute();
$old_credit = 0;
$old_gpa = 0.0;
$stmt_previous_state->bind_result($old_credit,$old_gpa);
$stmt_previous_state->fetch();
$stmt_previous_state->free_result();
$stmt_course_credit = $conn->prepare("select credit from course where course_id = ?");
$stmt_course_credit->bind_param("s",$course_id);
$stmt_course_credit->execute();
$course_credit = 0;
$stmt_course_credit->bind_result($course_credit);
$stmt_course_credit->fetch();
$stmt_course_credit->free_result();

$stmt_check = $conn->prepare("select grade from takes where student_id = ? and course_id = ? and sec_id = ? and semester = ? and `year` = ?");
$stmt_check->bind_param("ssisi",$stu_id,$course_id,$sec_id,$semester,$year);
$stmt_check->execute();
//$check_result = $stmt_check->get_result();
//$check_row = $check_result->fetch_assoc();
//$stmt_check->free_result();
$previous_grade = "";
$stmt_check->bind_result($previous_grade);
$stmt_check->fetch();
$stmt_check->free_result();
if(empty($previous_grade) || strlen($previous_grade) == 0){//本来无成绩
    if(empty($grade) || strlen($grade)==0){
        //不用登分
//        echo "{本来无成绩现在有}";
    }else{
        //第一次登分
        $stmt->bind_param("sssisi",$grade,$stu_id,$course_id,$sec_id,$semester,$year);
        $r = $stmt->execute();

        if(!$r){
//            echo "{1:".alert_error($conn)."}";
            echo 3;
            $conn->rollback();
            die();
        }
        $stmt->free_result();

        $new_credit = intval($old_credit) + intval($course_credit);
        $new_gpa = (doubleval($old_gpa) * intval($old_credit) + doubleval($cre) * intval($course_credit))/$new_credit;
        $new_gpa = sprintf("%.2f",$new_gpa);
//        echo var_dump($stu_id).var_dump($new_credit)." ".$new_gpa;
        $stmt_update_credit_gpa->bind_param("ids",$new_credit,$new_gpa,$stu_id);

        $r = $stmt_update_credit_gpa->execute();
        if(!$r){
//            echo "{2:".alert_error($conn)."}";
            echo 3;
            $conn->rollback();
            die();
        }

        $stmt_update_credit_gpa->free_result();
    }
}else{
    //本来有成绩
    if(empty($grade) || strlen($grade)==0){
        // 取消成绩
//        $stmt_set_null->bind_param("ssisi",$stu_id,$course_id,$sec_id,$semester,$year);
//        $r = $stmt_set_null->execute();
//        if(!$r){
////            echo "{3:".alert_error($conn)."}";
//            echo 3;
//            $conn->rollback();
//            die();
//        }
//        $stmt_set_null->free_result();
//
//        $new_credit = intval($old_credit) - intval($course_credit);
//        $new_gpa = (doubleval($old_gpa) * intval($old_credit) - doubleval($cre) * intval($course_credit))/$new_credit;
//        $new_gpa = sprintf("%.2f",$new_gpa);
//        $stmt_update_credit_gpa->bind_param("ids",$new_credit,$new_gpa,$stu_id);
//        $r = $stmt_update_credit_gpa->execute();
//        if(!$r){
////            echo "{4:".alert_error($conn)."}";
//            echo 3;
//            $conn->rollback();
//            die();
//        }
//        $stmt_update_credit_gpa->free_result();
    }else{
        //改变原有成绩
        $stmt->bind_param("sssisi",$grade,$stu_id,$course_id,$sec_id,$semester,$year);
        $r = $stmt->execute();
        if(!$r){
//            echo "{5:".alert_error($conn)."}";
            echo 3;
            $conn->rollback();
            die();
        }
        $stmt->free_result();

        $previous_grade_this_sec = convert2cre($previous_grade);
        $new_credit = $old_credit;
        $new_gpa = (doubleval($old_gpa)*intval($old_credit)-doubleval($previous_grade_this_sec)*intval($course_credit)+doubleval($cre)*intval($course_credit))/$new_credit;
        $new_gpa = sprintf("%.2f",$new_gpa);
        $stmt_update_credit_gpa->bind_param("ids",$new_credit,$new_gpa,$stu_id);
        $r = $stmt_update_credit_gpa->execute();
        if(!$r){
//            echo "{6:".alert_error($conn)."}";
            echo 3;
            $conn->rollback();
            die();
        }
        $stmt_update_credit_gpa->free_result();
    }
}

$conn->commit();
$conn->autocommit(true);
echo 0;


?>
