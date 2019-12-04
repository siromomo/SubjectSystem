<?php

/*
 * component：工具函数类
 * 相关函数：
 * ------------学生相关--------------
 * take_lesson($conn, $student_id, $sec_id, $year, $course_id, $semester) - 选课
 * get_section_have_chosen($conn, $student_id) - 获取学生已选课程
 * get_section_to_choose($conn) - 获取可选课程
 * get_class_time_place($conn, $section) - 从相关联系集中初始化课程的时间地点
 * 注：在学生主页的php代码部分将已选课程从可选课程中减去了
 * 注2：那个choose_lesson好像没有什么用，不过我也不知道我是不是在哪调用了这东西，所以就没删
 * drop_lesson($conn, $student_id, $sec_id, $course_id, $semester, $year) - 退课
 * get_test_list($conn, $student_id) - 获取考试列表
 * get_test_time_place($conn, $test) - 从相关联系集中初始化考试的时间地点
 * make_application($conn, $student_id, $sec_id, $course_id, $semester, $year, $appli_content) - 提交申请
 * check_section_available_to_application($conn, $student_id, $sec_id, $course_id, $semester, $year) - 检查是否能提交申请
 * get_application_list_for_student($conn, $student_id) - 获取该学生已提交的申请列表
 * ------------老师相关----------------
 * get_sec_for_instructor($conn, $instructor_id) - 获取老师教授的课程列表
 * get_app_for_sec_set($conn, $sec_set) - 从课程列表获取老师需要处理的申请列表
 * handle_application($conn, $app_id, $new_app_status) - 老师处理申请
 * handle_unavailable_applications($conn, $sec_id, $course_id, $semester, $year) - 系统自动驳回教室人数已达上限的申请
 * get_member_for_section($conn, $sec_id, $course_id, $semester, $year) - 获取课程花名册
 * -----------------------------------
 * 注3：
 *  所有连接mysql的语句都用了预处理，做sql注入题做魔怔了…
 *  这个预处理有点麻烦，有返回结果的做完了一定$stmt->free_result()否则下个查询可能无法进行
 *  然后就是bind_param的第一个参数是要绑定的参数类型，s是string，i是int，其他大概也用不太到
 * 注4：
 *  alert_error我本来是想就alert的然后发现不知道为什么alert不出来，所以就把所有报错都echo在了页面上
 *  您要是想到了更好的可以改一下这里！
 * 注5：
 *  所有函数都有返回值，不需要返回查询结果的函数在成功时返回true，所有函数失败时都返回false
 * 注6：
 *  那个refresh_page在页面有get参数的时候一定不要随便调用……会执行很多遍命令的……
 * */

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

class Test{
    var $exam_id;
    var $course_id;
    var $style;
    var $class_to_time;
    var $class_to_time_str;

    function __construct($exam_id, $course_id, $style){
        $this->exam_id = $exam_id;
        $this->course_id = $course_id;
        $this->style = $style;
        $this->class_to_time = [];
    }
}

class Paper{
    var $exam_id;
    var $course_id;
    var $demand;

    function __construct($exam_id, $course_id, $demand){
        $this->exam_id = $exam_id;
        $this->course_id = $course_id;
        $this->demand = $demand;
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

function take_lesson($conn, $student_id, $sec_id, $year, $course_id, $semester){
    $conn->autocommit(false);
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
        $conn->rollback();
        return false;
    }
    $time_slot_1 = '';
    $select_time_slot->store_result();
    $select_time_slot->bind_result($time_slot_1);
    if(!$select_time_slot_2){
        alert_error($conn);
        $conn->rollback();
        return false;
    }
    $select_time_slot_2->bind_param("s", $student_id);
    $ts2 = $select_time_slot_2->execute();
    if(!$ts2){
        $conn->rollback();
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
                $conn->rollback();
                return false;
            }
        }
    }
    $select_time_slot->free_result();
    $select_time_slot_2->free_result();
    $check_selected_num->bind_param("issi", $sec_id, $course_id, $semester, $year);
    $ts3 = $check_selected_num->execute();
    if(!$ts3){
        alert_error($conn);
        $conn->rollback();
        return false;
    }
    $check_selected_num->store_result();
    $selected_num = 0; $number = 0;
    $check_selected_num->bind_result($selected_num, $number);
    $check_selected_num->fetch();
    if($selected_num >= $number){
        alert_msg("学生 $student_id 选的课程 $course_id 选课人数已满");
        $conn->rollback();
        return false;
    }
    $check_selected_num->free_result();
    $check_exam_time_1 = $conn->prepare("select time_slot_id 
              from section natural join exam_time_place where (sec_id, course_id, semester, year) = (?, ?, ?, ?)");
    $check_exam_time_2 = $conn->prepare("select time_slot_id from section natural join takes natural join exam_time_place
        where student_id=?");
    $check_exam_time_1->bind_param("issi", $sec_id, $course_id, $semester, $year);
    $et1 = $check_exam_time_1->execute();
    if(!$et1){
        alert_error($conn);
        $conn->rollback();
        return false;
    }
    $exam_time_1 = '';
    $check_exam_time_1->store_result();
    $check_exam_time_1->bind_result($exam_time_1);
    $check_exam_time_2->bind_param("s", $student_id);
    $et2 = $check_exam_time_2->execute();
    if(!$et2){
        alert_error($conn);
        $conn->rollback();
        return false;
    }
    $exam_time_2 = '';
    $check_exam_time_2->store_result();
    $check_exam_time_2->bind_result($exam_time_2);
    while($check_exam_time_1->fetch()){
        while($check_exam_time_2->fetch()){
            if($exam_time_1 === $exam_time_2){
                alert_msg("学生 $student_id 选的课程 $course_id 与已选课程考试时间冲突");
                $conn->rollback();
                return false;
            }
        }
    }
    $check_exam_time_1->free_result();
    $check_exam_time_2->free_result();
    $stmt->bind_param("siiss", $student_id, $sec_id, $year, $course_id, $semester);
    $r = $stmt->execute();
    if(!$r){
        alert_error($conn);
        $conn->rollback();
        return false;
    }
    $selected_num++;
    $update_selected_num->bind_param("iissi", $selected_num, $sec_id, $course_id, $semester, $year);
    $r = $update_selected_num->execute();
    if(!$r){
        alert_error($conn);
        $conn->rollback();
        return false;
    }
    $conn->commit();
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
    $conn->autocommit(false);
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
        $conn->rollback();
        return false;
    }
    $stmt2 = $conn->prepare("update section set selected_num=
    (select a.selected_num-1 from (select selected_num from section where (sec_id, course_id, semester, year)=(?,?,?,?)) as a) 
    where (sec_id, course_id, semester, year)=(?,?,?,?);");
    $stmt2->bind_param("issiissi", $sec_id, $course_id, $semester, $year, $sec_id, $course_id, $semester, $year);
    $r2 = $stmt2->execute();
    if(!$r2){
        alert_error($conn);
        $conn->rollback();
        return false;
    }
    $stmt->free_result();
    $stmt1->free_result();
    $stmt2->free_result();
    $conn->commit();
    return true;
}

function get_test_list($conn, $student_id){
    $stmt = $conn->prepare("select test.exam_id,course_id,style from takes natural join section natural join test where student_id=?");
    if(!$stmt){
        alert_error($conn);
        return false;
    }
    $test_list = [];
    $stmt->bind_param("s", $student_id);
    $exam_id = 0;
    $style = '';
    $course_id = '';
    $stmt->execute();
    $stmt->bind_result($exam_id, $course_id, $style);
    while($stmt->fetch()){
        $test = new Test($exam_id, $course_id, $style);
        array_push($test_list, $test);
    }
    $stmt->free_result();
    foreach ($test_list as $test){
        get_test_time_place($conn, $test);
    }
    return $test_list;
}

function get_paper_list($conn, $student_id){
    $stmt = $conn->prepare("select paper.exam_id, course_id, demand 
    from takes natural join section natural join paper where student_id=?");
    if(!$stmt){
        alert_error($conn);
        return false;
    }
    $paper_list = [];
    $stmt->bind_param("s", $student_id);
    $exam_id = 0;
    $course_id = '';
    $demand = '';
    $stmt->execute();
    $stmt->bind_result($exam_id, $course_id, $demand);
    while($stmt->fetch()){
        $paper = new Paper($exam_id, $course_id, $demand);
        array_push($paper_list, $paper);
    }
    $stmt->free_result();
    return $paper_list;
}

function get_test_time_place($conn, $test){
    $stmt = $conn->prepare("select time_slot_id, classroom_id from exam_time_place 
        where exam_id=?");
    if(!$stmt){
        echo mysqli_error($conn);
        return '';
    }
    $exam_id = $test->exam_id;

    $time_slot_id = '';
    $classroom_id = '';
    $stmt->bind_param("s", $exam_id);
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
    $test->class_to_time = $class_to_time;
    $class_to_time_str = '';
    foreach($class_to_time as $class=>$time) {
        $class_to_time_str .= ($time . " " . $class . "<br>");
    }
    $test->class_to_time_str = $class_to_time_str;

    $stmt->free_result();
}

function make_application($conn, $student_id, $sec_id, $course_id, $semester, $year, $appli_content){
    $conn->autocommit(false);
    $valid = check_section_available_to_application($conn, $student_id, $sec_id, $course_id, $semester, $year);
    if(!$valid){
        return false;
    }
    if($valid != 'valid'){
        alert_msg($valid);
        $conn->rollback();
        return false;
    }
    $stmt = $conn->prepare("insert into application(appli_content, student_id, course_id, sec_id, semester, year)
             values (?,?,?,?,?,?)");
    if(!$stmt){
        alert_error($conn);
        $conn->rollback();
        return false;
    }
    $stmt->bind_param("sssisi", $appli_content, $student_id, $course_id, $sec_id, $semester, $year);
    $r1 = $stmt->execute();
    if(!$r1){
        alert_error($conn);
        $conn->rollback();
        return false;
    }
    $conn->commit();
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
    $conn->autocommit(false);
    $stmt = $conn->prepare("update application set appii_status=? where appli_id=?");
    $stmt->bind_param("si", $new_app_status, $app_id);
    $r = $stmt->execute();
    if(!$r){
        alert_error($conn);
        $conn->rollback();
        return false;
    }
    if($new_app_status == '通过') {
        $stmt1 = $conn->prepare("select student_id, sec_id, course_id, semester, year from application where appli_id=?");
        if(!$stmt1){
            alert_error($conn);
            $conn->rollback();
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
            $conn->rollback();
            return false;
        }
        $stmt2->bind_param("issiissi", $sec_id, $course_id, $semester, $year, $sec_id, $course_id, $semester, $year);
        $r2 = $stmt2->execute();
        if(!$r2){
            alert_error($conn);
            $conn->rollback();
            return false;
        }
        $stmt3 = $conn->prepare("insert into takes (student_id, sec_id, course_id, semester, year)
  values (?,?,?,?,?)");
        if(!$stmt3){
            alert_error($conn);
            $conn->rollback();
            return false;
        }
        $stmt3->bind_param("sissi", $student_id, $sec_id, $course_id, $semester, $year);
        $r3 = $stmt3->execute();
        if(!$r3){
            alert_error($conn);
            $conn->rollback();
            return false;
        }

        $stmt4 = $conn->prepare("select distinct selected_num, capacity from section natural join class_time_place 
  natural join classroom where sec_id=? and course_id=? and semester=? and year=?;");
        if(!$stmt4){
            alert_error($conn);
            $conn->rollback();
            return false;
        }
        $stmt4->bind_param("issi", $sec_id, $course_id, $semester, $year);
        $r4 = $stmt4->execute();
        if(!$r4){
            alert_error($conn);
            $conn->rollback();
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
    $conn->commit();
    return true;
}

function handle_unavailable_applications($conn, $sec_id, $course_id, $semester, $year){
    $conn->autocommit(false);
    $stmt = $conn->prepare("update application set appii_status='拒绝' 
            where (sec_id, course_id, semester, year)=(?,?,?,?)");
    if(!$stmt){
        alert_error($conn);
        $conn->rollback();
        return false;
    }
    $stmt->bind_param("issi", $sec_id, $course_id, $semester, $year);
    $r = $stmt->execute();
    if(!$r){
        alert_error($conn);
        $conn->rollback();
        return false;
    }
    $conn->commit();
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

function commit_grade_for_one_student($conn,$student_id,$course_id,$sec_id,$semester,$year,$grade){
    $cre = 0;
    switch ($grade){
        case "A":
            $cre = 4.0;
            break;
        case "A-":
            $cre = 3.7;
            break;
        case "B+":
            $cre = 3.3;
            break;
        case "B":
            $cre = 3.0;
            break;
        case "B-":
            $cre = 2.7;
            break;
        case "C+":
            $cre = 2.3;
            break;
        case "C":
            $cre = 2.0;
            break;
        case "C-":
            $cre = 1.7;
            break;
        case "D+":
            $cre = 1.3;
            break;
        case "D":
            $cre = 1.0;
            break;
        case "F":
            $cre = 0.0;
            break;
        default:
            echo "<script>alert('学生: ".$student_id."的成绩格式错误')</script>";
            return false;
    }

//    var_dump($conn);
//    echo "------------";
    $conn->autocommit(false);


    $stmt_check = $conn->prepare("select grade from takes where student_id=? and course_id=? and sec_id=? and semester=? and `year`=?");
    $sec_id = intval($sec_id);
    $year = intval($year);
//    var_dump($student_id);
//    var_dump($course_id);
//    var_dump($sec_id);
//    var_dump($semester);
//    var_dump($year);
//    var_dump($conn);
//    echo "------------";
    $stmt_check->bind_param("ssisi",$student_id,$course_id,$sec_id,$semester,$year);
    $stmt_check->execute();
    $check_grade = "";
    $stmt_check->bind_result($check_grade);
    $stmt_check->fetch();
    if(!(strlen($check_grade)==0 || empty($check_grade))){
        echo "<script>alert('学生".$student_id."的成绩已经导入，联系管理员更改')</script>";
        return false;
    }
    $stmt_check->free_result();

    $stmt = $conn->prepare("update takes set grade = ? where student_id=? and course_id=? and sec_id=? and semester=? and year=?");
    $stmt->bind_param("sssisi",$grade,$student_id,$course_id,$sec_id,$semester,$year);
    $r = $stmt->execute();
    if(!$r){
        $conn->rollback();
        echo "<script>alert('此学生成绩导入失败')</script>";
        alert_error($conn);
        $stmt->free_result();
        return false;
    }
    $stmt->free_result();
    //修改学生绩点
    $stmt_get_old_credits = $conn->prepare("select total_credit,gpa from student where student_id=?");
    $stmt_get_course_credit = $conn->prepare("select credit from course where course_id=?");
    $stmt_update_credit_gpa = $conn->prepare("update student set total_credit=?,gpa=? where student_id=?");

    $stmt_get_old_credits->bind_param("s",$student_id);
    $stmt_get_old_credits->execute();
    $old_credit = 0;
    $old_gpa = 0.0;
    $stmt_get_old_credits->bind_result($old_credit,$old_gpa);
    $stmt_get_old_credits->fetch();
    $stmt_get_old_credits->free_result();

    $stmt_get_course_credit->bind_param("s",$course_id);
    $stmt_get_course_credit->execute();
    $course_cre = 0;
    $stmt_get_course_credit->bind_result($course_cre);
    $stmt_get_course_credit->fetch();
    $stmt_get_course_credit->free_result();

    $new_total_credits = $old_credit + $course_cre;
    $new_gpa = ($old_credit * $old_gpa + $cre * $course_cre) / $new_total_credits;
    $new_gpa = sprintf("%.2f",$new_gpa);

    $new_gpa = (double)$new_gpa;

//    var_dump($student_id);
//    var_dump($new_total_credits);
//    var_dump($new_gpa);
    $stmt_update_credit_gpa->bind_param("ids",$new_total_credits,$new_gpa,$student_id);
    $r = $stmt_update_credit_gpa->execute();
    if(!$r){
        $conn->rollback();
        echo "<script>alert('此学生学分与绩点导入失败')</script>";
        alert_error($conn);
        $stmt_update_credit_gpa->free_result();
        return false;
    }
    $stmt_update_credit_gpa->free_result();

    $conn->commit();
    $conn->autocommit(true);
    return true;
}
