<?php

class Section{
    var $sec_id;
    var $semester;
    var $year;
    var $start_week;
    var $end_week;
    var $number;
    var $selected_num;
    var $course_id;
    var $course_name;
    var $exam_id;

    var $class_to_time;
    var $class_to_time_str;
    var $exam_time;
    var $exam_place;

    function __construct($sec_id, $semester, $year, $start_week, $end_week, $number, $selected_num, $course_id, $course_name, $exam_id){
        $this->sec_id = $sec_id;
        $this->semester = $semester;
        $this->year = $year;
        $this->start_week = $start_week;
        $this->end_week = $end_week;
        $this->number = $number;
        $this->selected_num = $selected_num;
        $this->course_id = $course_id;
        $this->course_name = $course_name;
        $this->exam_id = $exam_id;
    }

    function __toString(){
        return $this->sec_id . $this->semester . $this->course_id . $this->year;
    }
}

class Application{
    var $app_id;
    var $app_status;
    var $app_content;
    var $app_time;
    var $app_stu_id;
    var $app_sec_id;
    var $app_course_id;
    var $app_semester;
    var $app_year;

    function __construct($app_id, $app_status, $app_content, $app_time, $app_stu_id, $app_sec_id, $app_course_id,
                            $app_semester, $app_year){
        $this->app_id = $app_id;
        $this->app_status = $app_status;
        $this->app_content = $app_content;
        $this->app_time = $app_time;
        $this->app_stu_id = $app_stu_id;
        $this->app_sec_id = $app_sec_id;
        $this->app_course_id = $app_course_id;
        $this->app_semester = $app_semester;
        $this->app_year = $app_year;
    }
}

class Student{
    var $student_id;
    var $student_name;
    var $enroll_time;
    var $graduate_time;
    var $total_credit;
    var $gpa;

    function __construct($student_id, $student_name, $total_credit, $gpa, $enroll_time, $graduate_time){
        $this->student_id = $student_id;
        $this->student_name = $student_name;
        $this->total_credit = $total_credit;
        $this->gpa = $gpa;
        $this->enroll_time = $enroll_time;
        $this->graduate_time = $graduate_time;
    }
}

function alert_error($conn, $err = null){
    if($err === null) {
        $err = mysqli_error($conn);
    }
    echo $err;
}
function alert_msg($msg){
    echo "<script>alert('$msg')</script>";
}
function jump_to_page($page){
    echo "<script>window.location='$page'</script>";
}
function refresh_page(){
    echo "<script>window.location.reload();</script>";
}
function time_slot_id_to_string($time_slot_id){
    $res = "";
    $weekday = substr($time_slot_id, 1, 1);
    switch ($weekday){
        case 1: $res .= "周一";break;
        case 2: $res .= "周二";break;
        case 3: $res .= "周三";break;
        case 4: $res .= "周四";break;
        case 5: $res .= "周五";break;
    }
    $AorP = substr($time_slot_id, 0, 1);
    if($AorP === 'A'){
        $res .= "上午";
    }else{
        $res .= "下午";
    }
    $time_slot = substr($time_slot_id, 3, 1);
    switch ($time_slot){
        case 1: $res .= "第一节";break;
        case 2: $res .= "第二节";break;
        case 3: $res .= "第三节";break;
        case 4: $res .= "第四节";break;
        case 5: $res .= "第五节";break;
        case 6: $res .= "第六节";break;
        case 7: $res .= "第七节";break;
    }
    return $res;
}

function takeCourse($conn, $student_id, $sec_id, $year, $course_id, $semester){
    $select_time_slot = $conn->prepare("select time_slot_id from class_time_place 
        where (sec_id, course_id, semester, year) = (?, ?, ?, ?)");
    $select_time_slot_2 = $conn->prepare("select time_slot_id from class_time_place 
        where (sec_id, year, course_id, semester) in 
        (select sec_id, year, course_id, semester from takes where student_id=(?))");
    $stmt = $conn->prepare("insert into takes(student_id, sec_id, year, course_id, semester) values (?,?,?,?,?)");
    $check_selected_num = $conn->prepare("select selected_num, number from section 
        where (sec_id, course_id, semester, year)=(?,?,?,?)");
    $update_selected_num = $conn->prepare("update section set selected_num=? 
        where (sec_id, course_id, semester, year)=(?,?,?,?)");
    $select_time_slot->bind_param("issi", $sec_id, $course_id, $semester, $year);
    $ts1 = $select_time_slot->execute();
    if(!$ts1){
        alert_error($conn);
        return false;
    }
    $time_slot_1 = '';
    $select_time_slot->store_result();
    $select_time_slot->bind_result($time_slot_1);
    if(!$select_time_slot_2){
        alert_error($conn);
        return false;
    }
    $select_time_slot_2->bind_param("s", $student_id);
    $ts2 = $select_time_slot_2->execute();
    if(!$ts2){
        return false;
    }
    $time_slot_2 = '';
    $select_time_slot_2->store_result();
    $select_time_slot_2->bind_result($time_slot_2);
    //$select_time_slot_2->close();
    while ($select_time_slot->fetch()){
        while($select_time_slot_2->fetch()){
            if($time_slot_1 === $time_slot_2){
                alert_msg("学生 $student_id 选的课程 $course_id 与已选课程时间冲突");
                return false;
            }
        }
    }
    $check_selected_num->bind_param("issi", $sec_id, $course_id, $semester, $year);
    $ts3 = $check_selected_num->execute();
    if(!$ts3){
        alert_error($conn);
        return false;
    }
    $check_selected_num->store_result();
    $selected_num = 0; $number = 0;
    $check_selected_num->bind_result($selected_num, $number);
    $check_selected_num->fetch();
    if($selected_num >= $number){
        alert_msg("学生 $student_id 选的课程 $course_id 选课人数已满");
        return false;
    }
    $stmt->bind_param("siiss", $student_id, $sec_id, $year, $course_id, $semester);
    $r = $stmt->execute();
    if(!$r){
        alert_error($conn);
        return false;
    }
    $selected_num++;
    $update_selected_num->bind_param("iissi", $selected_num, $sec_id, $course_id, $semester, $year);
    $r = $update_selected_num->execute();
    if(!$r){
        alert_error($conn);
        return false;
    }
    return true;
}

function add_class_time_place($conn, $time_slot_id, $classroom_id, $sec_id, $year, $course_id, $semester){
    $stmt1 = $conn->prepare("insert into class_time_place (time_slot_id, classroom_id, sec_id, year, course_id, semester) 
                     values(?, ?, ?, ?, ?, ?)");
    $stmt1->bind_param("ssiiss", $time_slot_id, $classroom_id, $sec_id, $year, $course_id, $semester);
    $r1 = $stmt1->execute();
    if(!$r1){
        echo mysqli_error($conn);
    }
}

function add_teaches($conn, $instructor_id, $sec_id, $year, $course_id, $semester){
    $stmt1 = $conn->prepare("insert into teaches(instructor_id, sec_id, year, course_id, semester) values (?,?,?,?,?)");
    $stmt1->bind_param("siiss", $instructor_id, $sec_id, $year, $course_id, $semester);
    $r1 = $stmt1->execute();
    if(!$r1){
        echo mysqli_error($conn);
    }
}

function add_Section($conn, $sec_id, $semester, $year, $start_week, $end_week, $number, $selected_num, $course_id, $exam_id){
    $stmt1 = $conn->prepare("insert into section (sec_id, semester, year, start_week, end_week, number, selected_num, 
                     course_id, exam_id) values(?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt1->bind_param("ssiiiiiss", $sec_id, $semester, $year, $start_week, $end_week,
        $number, $selected_num, $course_id, $exam_id);
    $r1 = $stmt1->execute();
    if(!$r1){
        echo mysqli_error($conn);
    }
}

function get_section_have_chosen($conn, $student_id){
    $stmt = $conn->prepare("select sec_id, semester, year, start_week, end_week, number, selected_num, course.course_id, course_name, exam_id
from takes natural join section natural join course where student_id=?");
    $stmt->bind_param("s", $student_id);
    return get_section_set($conn, $stmt);
}

function get_section_set($conn, $stmt){
    $section_set = [];
    if(!$stmt){
        echo mysqli_error($conn);
        return false;
    }
    $r = $stmt->execute();
    if(!$r){
        alert_error($conn);
        return false;
    }
    $sec_id = 0;
    $semester = '';
    $year = 0;
    $start_week = 0;
    $end_week = 0;
    $number = 0;
    $selected_num = 0;
    $course_id = '';
    $course_name = '';
    $exam_id = '';
    $stmt->bind_result($sec_id, $semester, $year, $start_week, $end_week, $number, $selected_num, $course_id, $course_name, $exam_id);
    while($stmt->fetch()){
        $sec = new Section($sec_id, $semester, $year, $start_week, $end_week, $number,
            $selected_num, $course_id, $course_name, $exam_id);

        array_push($section_set, $sec);
    }
    $stmt->free_result();
    foreach($section_set as $section){
        get_class_time_place($conn, $section);
    }

    return $section_set;
}

function get_section_to_choose($conn){
    $stmt = $conn->prepare(
        "select sec_id, semester, year, start_week, end_week, number, selected_num, course.course_id, course_name, exam_id 
from section natural join course");
    $section_set = get_section_set($conn, $stmt);
    return $section_set;
}

function get_course_name($conn, $course_id){
    $stmt = $conn->prepare("select course_name from course where course_id=?");
    if(!$stmt){
        echo mysqli_error($conn);
        return '';
    }
    $stmt->bind_param("s", $course_id);
    $res = '';
    $stmt->bind_result($res);
    $stmt->fetch();
    $stmt->free_result();
    return $res;
}

function get_class_time_place($conn, $section){
    $stmt = $conn->prepare("select time_slot_id, classroom_id from class_time_place 
        where sec_id=? and course_id=? and semester=? and year=?");
    if(!$stmt){
        echo mysqli_error($conn);
        return '';
    }
    $sec_id = $section->sec_id;
    $course_id = $section->course_id;
    $semester = $section->semester;
    $year = $section->year;
    //echo $sec_id . " " . $course_id . " " . $semester . " ".  $year . "<br>";
    $time_slot_id = '';
    $classroom_id = '';
    $stmt->bind_param("issi", $sec_id, $course_id, $semester, $year);
    $stmt->execute();
    $stmt->bind_result($time_slot_id, $classroom_id);

    $class_to_time = [];

    while($stmt->fetch()){
        $time_slot = time_slot_id_to_string($time_slot_id);
        if(!isset($class_to_time[$classroom_id]))
            $class_to_time[$classroom_id] = $time_slot;
        else
            $class_to_time[$classroom_id] .= (",".$time_slot);
    }
    $section->class_to_time = $class_to_time;
    $class_to_time_str = '';
    foreach($class_to_time as $class=>$time) {
        $class_to_time_str .= ($time . " " . $class . "<br>");
    }
    $section->class_to_time_str = $class_to_time_str;

    $stmt->free_result();
}

function choose_lesson($conn, $student_id, $sec_id, $course_id, $semester, $year){
    //alter table takes add unique(student_id, sec_id, course_id, semester, year);
    $stmt = $conn->prepare("insert into takes(student_id, sec_id, course_id, semester, year) values(?,?,?,?,?)");
    $stmt->bind_param("sissi", $student_id, $sec_id, $course_id, $semester, $year);
    $r1 = $stmt->execute();
    if(!$r1){
        alert_error($conn);
        return false;
    }
    $stmt2 = $conn->prepare("update section set selected_num=
    (select a.selected_num+1 from (select selected_num from section where (sec_id, course_id, semester, year)=(?,?,?,?)) as a) 
    where (sec_id, course_id, semester, year)=(?,?,?,?);");
    $stmt2->bind_param("issiissi", $sec_id, $course_id, $semester, $year, $sec_id, $course_id, $semester, $year);
    $r2 = $stmt2->execute();
    if(!$r2){
        alert_error($conn);
        return false;
    }
    $stmt->free_result();
    $stmt2->free_result();
    return true;
}

function drop_lesson($conn, $student_id, $sec_id, $course_id, $semester, $year){
    $stmt = $conn->prepare("insert into drops(student_id, sec_id, course_id, semester, year) values(?,?,?,?,?)");
    if(!$stmt){
        alert_error($conn);
        return false;
    }
    $stmt->bind_param("sissi", $student_id, $sec_id, $course_id, $semester, $year);
    $r1 = $stmt->execute();
    if(!$r1){
        alert_error($conn);
        return false;
    }
    $stmt1 = $conn->prepare("delete from takes where (student_id, sec_id, course_id, semester, year) = (?,?,?,?,?)");
    $stmt1->bind_param("sissi", $student_id, $sec_id, $course_id, $semester, $year);
    $r1 = $stmt1->execute();
    if(!$r1){
        alert_error($conn);
        return false;
    }
    $stmt2 = $conn->prepare("update section set selected_num=
    (select a.selected_num-1 from (select selected_num from section where (sec_id, course_id, semester, year)=(?,?,?,?)) as a) 
    where (sec_id, course_id, semester, year)=(?,?,?,?);");
    $stmt2->bind_param("issiissi", $sec_id, $course_id, $semester, $year, $sec_id, $course_id, $semester, $year);
    $r2 = $stmt2->execute();
    if(!$r2){
        alert_error($conn);
    }
    $stmt->free_result();
    $stmt1->free_result();
    $stmt2->free_result();
    return true;
}

function make_application($conn, $student_id, $sec_id, $course_id, $semester, $year, $appli_content){
    $valid = check_section_available_to_application($conn, $student_id, $sec_id, $course_id, $semester, $year);
    if(!$valid){
        return false;
    }
    if($valid != 'valid'){
        alert_msg($valid);
        return false;
    }
    $stmt = $conn->prepare("insert into application(appli_content, student_id, course_id, sec_id, semester, year)
             values (?,?,?,?,?,?)");
    if(!$stmt){
        alert_error($conn);
        return false;
    }
    $stmt->bind_param("sssisi", $appli_content, $student_id, $course_id, $sec_id, $semester, $year);
    $r1 = $stmt->execute();
    if(!$r1){
        alert_error($conn);
        return false;
    }
    return true;
}

function check_section_available_to_application($conn, $student_id, $sec_id, $course_id, $semester, $year){
    $stmt = $conn->prepare("select distinct selected_num, capacity from section natural join class_time_place 
  natural join classroom where sec_id=? and course_id=? and semester=? and year=?;");
    if(!$stmt){
        alert_error($conn);
        return false;
    }
    $stmt->bind_param("issi", $sec_id, $course_id, $semester, $year);
    $r1 = $stmt->execute();
    if(!$r1){
        alert_error($conn);
        return false;
    }
    $selected_num = 0;
    $capacity = 0;
    $stmt->bind_result($selected_num, $capacity);
    while($stmt->fetch()) {
        if ($selected_num >= $capacity) {
            return "已选人数大于教室容量，无法进行选课申请";
        }
    }
    $stmt->free_result();
    $stmt1 = $conn->prepare("select count(*) from drops where student_id=? and sec_id=? 
    and course_id=? and semester=? and year=?");
    if(!$stmt1){
        alert_error($conn);
        return false;
    }
    $stmt1->bind_param("sissi", $student_id, $sec_id, $course_id, $semester, $year);
    $drop_count = 0;
    $r2 = $stmt1->execute();
    if(!$r2){
        alert_error($conn);
        return false;
    }
    $stmt1->bind_result($drop_count);
    $stmt1->fetch();
    if($drop_count > 0){
        return "您已退过该课，不能重复申请";
    }
    $stmt1->free_result();
    return "valid";
}

function get_application_list_for_student($conn, $student_id){
    $stmt = $conn->prepare("select appli_id, appii_status, appli_content, appli_time, student_id, sec_id, course_id, semester, year
     from application where student_id=?");
    $stmt->bind_param("s", $student_id);
    return get_application_list($conn, $stmt);
}

function get_application_list($conn, $stmt){
    if(!$stmt){
        alert_error($conn);
        return false;
    }
    $app_list = [];
    $r = $stmt->execute();
    if(!$r){
        alert_error($conn);
        return false;
    }
    $app_id = 0;
    $app_status = '';
    $app_content = '';
    $app_time = '';
    $student_id = '';
    $app_sec_id = 0;
    $app_course_id = '';
    $app_semester = '';
    $app_year = 0;
    $stmt->bind_result($app_id, $app_status, $app_content, $app_time,
        $student_id, $app_sec_id, $app_course_id, $app_semester, $app_year);
    while($stmt->fetch()) {
        $app = new Application($app_id, $app_status, $app_content, $app_time, $student_id,
            $app_sec_id, $app_course_id, $app_semester, $app_year);
        array_push($app_list, $app);
    }
    $stmt->free_result();
    return $app_list;
}


function get_sec_for_instructor($conn, $instructor_id){
    $stmt = $conn->prepare(
        "select sec_id, semester, year, start_week, end_week, number, selected_num, course.course_id, course_name, exam_id 
from section natural join course natural join teaches where instructor_id=?");
    $stmt->bind_param("s", $instructor_id);
    $section_set = get_section_set($conn, $stmt);
    return $section_set;
}

function get_app_for_sec_set($conn, $sec_set){
    $stmt = $conn->prepare("select appli_id, appii_status, appli_content, appli_time, student_id, sec_id, course_id, semester, year
     from application where (sec_id, course_id, semester, year)=(?,?,?,?)");
    $res_list = [];
    foreach ($sec_set as $sec){
        $stmt->bind_param("issi", $sec->sec_id, $sec->course_id, $sec->semester, $sec->year);
        $app_list = get_application_list($conn, $stmt);
        $res_list = array_merge($res_list, $app_list);
    }
    return $res_list;
}

function handle_application($conn, $app_id, $new_app_status){
    $stmt = $conn->prepare("update application set appii_status=? where appli_id=?");
    $stmt->bind_param("si", $new_app_status, $app_id);
    $r = $stmt->execute();
    if(!$r){
        alert_error($conn);
        return false;
    }
    if($new_app_status == '通过') {
        $stmt1 = $conn->prepare("select student_id, sec_id, course_id, semester, year from application where appli_id=?");
        if(!$stmt1){
            alert_error($conn);
            return false;
        }
        $stmt1->bind_param("i", $app_id);
        $student_id = '';
        $sec_id = 0;
        $course_id = '';
        $semester = '';
        $year = 0;
        $stmt1->execute();
        $stmt1->bind_result($student_id, $sec_id, $course_id, $semester, $year);
        $stmt1->fetch();
        $stmt1->free_result();
        $stmt2 = $conn->prepare("update section set selected_num=
    (select a.selected_num+1 from (select selected_num from section where (sec_id, course_id, semester, year)=(?,?,?,?)) as a) 
    where (sec_id, course_id, semester, year)=(?,?,?,?);");
        if (!$stmt2) {
            alert_error($conn);
            return false;
        }
        //这个地方是不是需要处理事务的原子性问题？比如如果这一步执行不成功需要回滚这个状态
        //再说吧
        $stmt2->bind_param("issiissi", $sec_id, $course_id, $semester, $year, $sec_id, $course_id, $semester, $year);
        $r2 = $stmt2->execute();
        if(!$r2){
            alert_error($conn);
            return false;
        }
        $stmt3 = $conn->prepare("insert into takes (student_id, sec_id, course_id, semester, year)
  values (?,?,?,?,?)");
        if(!$stmt3){
            alert_error($conn);
            return false;
        }
        $stmt3->bind_param("sissi", $student_id, $sec_id, $course_id, $semester, $year);
        $r3 = $stmt3->execute();
        if(!$r3){
            alert_error($conn);
            return false;
        }

        $stmt4 = $conn->prepare("select distinct selected_num, capacity from section natural join class_time_place 
  natural join classroom where sec_id=? and course_id=? and semester=? and year=?;");
        if(!$stmt4){
            alert_error($conn);
            return false;
        }
        $stmt4->bind_param("issi", $sec_id, $course_id, $semester, $year);
        $r4 = $stmt4->execute();
        if(!$r4){
            alert_error($conn);
            return false;
        }
        $selected_num = 0;
        $capacity = 0;
        $stmt4->bind_result($selected_num, $capacity);
        while($stmt4->fetch()) {
            if ($selected_num >= $capacity) {
                $stmt4->free_result();
                handle_unavailable_applications($conn, $sec_id, $course_id, $semester, $year);
                break;
            }
        }
        $stmt4->free_result();
    }
    return true;
}

function handle_unavailable_applications($conn, $sec_id, $course_id, $semester, $year){
    $stmt = $conn->prepare("update application set appii_status='拒绝' 
            where (sec_id, course_id, semester, year)=(?,?,?,?)");
    if(!$stmt){
        alert_error($conn);
        return false;
    }
    $stmt->bind_param("issi", $sec_id, $course_id, $semester, $year);
    $r = $stmt->execute();
    if(!$r){
        alert_error($conn);
        return false;
    }
    return true;
}

function get_member_for_section($conn, $sec_id, $course_id, $semester, $year){
    $stmt = $conn->prepare("select student.student_id, student_name, total_credit, gpa, enroll_time, graduate_time
    from student natural join takes where (sec_id, course_id, semester, year)=(?,?,?,?)");
    if(!$stmt){
        alert_error($conn);
        return false;
    }
    $stmt->bind_param("issi", $sec_id, $course_id, $semester, $year);
    $r = $stmt->execute();
    if(!$r){
        alert_error($conn);
        return false;
    }
    $student_id = '';
    $student_name = '';
    $total_credit = 0;
    $gpa = 0;
    $enroll_time = '';
    $graduate_time = '';
    $mem_list =  [];
    $stmt->bind_result($student_id, $student_name, $total_credit, $gpa, $enroll_time, $graduate_time);
    while($stmt->fetch()){
        $stu = new Student($student_id, $student_name, $total_credit, $gpa, $enroll_time, $graduate_time);
        array_push($mem_list, $stu);
    }
    $stmt->free_result();
    return $mem_list;
}
