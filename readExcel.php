<?php
require_once 'ConnectSQL.php';
require_once 'PHPExcel.php';
require_once 'PHPExcel/IOFactory.php';
require_once 'PHPExcel/Reader/Excel5.php';
require_once 'components.php';

$conn = connectToDB();

function basicExcelLoader($filename)
{
//以上三步加载phpExcel的类
    $objReader = PHPExcel_IOFactory::createReader('Excel5');//use excel2007 for 2007 format
    $objPHPExcel = $objReader->load($filename); //$filename可以是上传的文件，或者是指定的文件
    $sheet = $objPHPExcel->getSheet(0);
    return $sheet;
}

function insertIntoChartBasic($chartName, $columnName, $type, $conn){
    $sheet = basicExcelLoader("./data/$chartName.xls");
    $highestRow = $sheet->getHighestRow(); // 取得总行数
    //echo $highestRow;
    $highestColumn = $sheet->getHighestColumn(); // 取得总列数
    $k = 0;
    $sta = "ok";
    $initStr = "insert into $chartName(";
    $initStr2 = "(";
    for($i = 0; $i < sizeof($columnName); $i++){
        $initStr .= $columnName[$i];
        $initStr2 .= "?";
        if($i < sizeof($columnName) - 1) {
            $initStr .= ",";
            $initStr2 .= ",";
        }
    }
    $initStr .= ") VALUES";
    $initStr2 .= ")";
    $initStr .= $initStr2;
    //echo $initStr;
    $stmt = $conn->prepare($initStr);

    mysqli_set_charset($conn, "utf8"); //设置字符utf-8
    for($j=2;$j<=$highestRow;$j++) { //j=2是因为第一行表为提示，从第二行开始取
        $values = [];
        $A = ord("A");
        for($k = 0; $k < sizeof($columnName); $k++, $A++){
            $sheet->getCell(chr($A) . $j)->setDataType(PHPExcel_Cell_DataType::TYPE_STRING);
            $values[$k] = $sheet->getCell(chr($A) . $j)->getValue();
            //echo $values[$k] . " ";
        }
        if(sizeof($columnName) == 2){
            $stmt->bind_param($type, $values[0], $values[1]);
        }else if(sizeof($columnName) == 4) {
            $stmt->bind_param($type, $values[0], $values[1], $values[2], $values[3]);
        }else if(sizeof($columnName) == 5){
            $stmt->bind_param($type, $values[0], $values[1], $values[2], $values[3], $values[4]);
        }else if(sizeof($columnName) == 6){
            $stmt->bind_param($type, $values[0], $values[1], $values[2], $values[3], $values[4], $values[5]);
        }
        $result = $stmt->execute();
        if ($result) {
            $sta = "ok";
        } else {
            $sta = "on";
        }
    }
    if($sta=="ok"){
        echo '<script>alert("数据导入成功！");</script>';
    }
}
/*phpExcel有时候会读出一个object要转换一下*/
function toString($cell){
    if(is_object($cell))
        $cell = $cell->__toString();
    return $cell;
}

function sectionLoader($conn,$filename){
    $sheet = basicExcelLoader($filename);//"./data/section.xls"
    $highestRow = $sheet->getHighestRow(); // 取得总行数

    $conn->autocommit(false);
    $sta = true;
    //事务处理
    for($i = 2; $i <= $highestRow; $i++){
//        echo "<script>alert('第".$i."行')</script>";
        $values = [];
        $class_time = [];
        $A = ord("A");
        for($j = 0; $j <=3; $j++,$A++){
            $values[$j] = $sheet->getCell(chr($A) . $i)->getValue();
            $values[$j] = toString($values[$j]);
        }
        //读入的数据
        $values[4] = $sheet->getCell(chr($A++) . $i)->getValue();//start_week
        $values[4] = toString($values[4]);
        $values[5] = $sheet->getCell(chr($A++) . $i)->getValue();//end_week
        $values[5] = toString($values[5]);
        $values[6] = $sheet->getCell(chr($A++) . $i)->getValue();//number
        $values[6] = toString($values[6]);
        $values[7] = $sheet->getCell(chr($A++) . $i)->getValue();//selected_number
        $values[7] = toString($values[7]);
        $insIds = $sheet->getCell(chr($A++).$i)->getValue();//instructors
        $insIds = toString($insIds);
        $insIdArray = explode(",",$insIds);
        $exam_week = $sheet->getCell(chr($A++) . $i)->getValue();
        $exam_week = toString($exam_week);
        $exam_day = $sheet->getCell(chr($A++) . $i)->getValue();
        $exam_day = toString($exam_day);
        $exam_type = $sheet->getCell(chr($A++) . $i)->getValue();
        $exam_type = toString($exam_type);
        $exam_des = $sheet->getCell(chr($A++) . $i)->getValue();
        $exam_des = toString($exam_des);
        $exam_stime = $sheet->getCell(chr($A++) . $i)->getValue();
        $exam_stime = toString($exam_stime);
        $exam_etime = $sheet->getCell(chr($A++) . $i)->getValue();
        $exam_etime = toString($exam_etime);

        for(;;$A += 2){
            $class = $sheet->getCell(chr($A) . $i)->getValue();
            $times = $sheet->getCell(chr($A+1) . $i)->getValue();
            $class = toString($class);
            $times = toString($times);
            if(empty($class) || strlen($class) == 0 || stristr($class, "冲突") || stristr($class, "-"))
                break;
            $class_time[$class]=$times;
        }
        if(empty($values[0]))
            break;

        $error_mes_update = "第".$i."行数据：".$values[0].".".$values[1].$values[2].$values[3]." 更新失败，请检查";
        $error_mes_insert = "第".$i."行数据：".$values[0].".".$values[1].$values[2].$values[3]." 插入失败，请检查";
        $error_mes_insert_conflict1 = "第".$i."行数据：".$values[0].".".$values[1].$values[2].$values[3]." 插入失败，请检查教室-时间冲突";
        $error_mes_insert_conflict2 = "第".$i."行数据：".$values[0].".".$values[1].$values[2].$values[3]." 插入失败，请检查老师-时间冲突";

        if(load_single_section($conn,$values,$class_time,$insIdArray,$exam_week,$exam_day,$exam_type,$exam_des,
            $exam_stime,$exam_etime,$error_mes_update,$error_mes_insert,$error_mes_insert_conflict1,$error_mes_insert_conflict2)){

        }else {
            $sta = false;
        }

    }
    if($sta){
        echo "<script>alert('导入成功')</script>";
    }else{
        echo "<script>alert('导入结束')</script>";
    }
    $conn->autocommit(true);
//mysqli_error($conn);
}

function testLoader($conn){
    $sheet = basicExcelLoader("./data/test.xls");
    $highestRow = $sheet->getHighestRow();
    $stmt1 = $conn->prepare("insert into test (exam_id, style) values(?, ?)");
    $stmt2 = $conn->prepare("insert into exam_time_place (exam_id, time_slot_id, classroom_id) values (?, ?, ?)");
    for($i = 2; $i <= $highestRow; $i++){
        $values = [];
        $values[0] = $sheet->getCell("A" . $i)->getValue();
        $values[1] = $sheet->getCell("B" . $i)->getValue();
        $stmt1->bind_param("ss", $values[0], $values[1]);
        //$stmt1->execute();
        $classroom = $sheet->getCell("C" . $i)->getValue();
        $times = $sheet->getCell("D" . $i)->getValue();
        $timeArray = explode(",", $times);
        foreach ($timeArray as $time){
            $stmt2->bind_param("sss", $values[0], $time, $classroom);

            $r = $stmt2->execute();
            if(!$r){
                $msg = mysqli_error($conn);
                if(stristr($msg, "duplicate")){
                    echo "考试$values[0]时间冲突";
                }else{
                    echo $msg;
                }
            }
        }
    }
}
function takesLoader($conn){
    $sheet = basicExcelLoader("./data/takes.xls");
    $highestRow = $sheet->getHighestRow();

    for($i = 2; $i <= $highestRow; $i++){
        $values = [];
        $A = ord("A");
        for($j = 0; $j < 5; $j++,$A++){
            $values[$j] = $sheet->getCell(chr($A) . $i)->getValue();
        }
        //TODO 我注释了下面这行因为找不到这个函数
//        takeCourse($conn, $values[0], $values[1], $values[4], $values[2], $values[3]);
    }
}

//testLoader($conn);
takesLoader($conn);

//alter table class_time_place add unique(time_slot_id, classroom_id, semester, year);
// alter table exam_time_place add unique(time_slot_id, classroom_id);
/*
insertIntoChartBasic("classroom", ["classroom_id", "capacity"], "si", $conn);
insertIntoChartBasic("course", ["course_id", "course_name", "credit", "class_hours"], "ssii", $conn);
insertIntoChartBasic("time_slot", ["time_slot_id", "start_time", "end_time", "day_of_week"], "sssi", $conn);
insertIntoChartBasic("instructor",
    ["instructor_id", "instructor_name", "hire_time", "quit_time"], "ssss", $conn);
insertIntoChartBasic("student", ["student_id", "student_name", "total_credit", "gpa", "enroll_time", "graduate_time"],
    "ssidss", $conn);
insertIntoChartBasic("exam", ["exam_id", "week"], "si", $conn);
insertIntoChartBasic("test", ["exam_id", "style"], "ss", $conn);
insertIntoChartBasic("paper", ["exam_id", "demand"], "ss", $conn);
*/
//sectionLoader($conn);

function studentLoader($conn,$filename){
    mysqli_set_charset($conn, "utf8");
    $sheet = basicExcelLoader($filename);
    $highestRow = $sheet->getHighestRow();

    $stmt_not_graduate = $conn->prepare("insert into student (student_id,student_name,total_credit,gpa,enroll_time,graduate_time) values (?,?,?,?,?,null)");
    $stmt_graduated = $conn->prepare("insert into student (student_id,student_name,total_credit,gpa,enroll_time,graduate_time) values (?,?,?,?,?,?)");

    $stmt_check = $conn->prepare("select * from student where student_id=?");
    $stmt_update_not_graduate = $conn->prepare("update student set student_name=?,total_credit=?,gpa=?,enroll_time=?,graduate_time=null where student_id=?");
    $stmt_update_graduated = $conn->prepare("update student set student_name=?,total_credit=?,gpa=?,enroll_time=?,graduate_time=? where student_id=?");

    $sta = true;
    for($i = 2; $i <= $highestRow; $i++){
        $stu_id = $sheet->getCell("A".$i)->getValue();
        $stu_name = $sheet->getCell("B".$i)->getValue();
        $stu_credit = $sheet->getCell("C".$i)->getValue();
        $stu_gpa = $sheet->getCell("D".$i)->getValue();
        $stu_etime = $sheet->getCell("E".$i)->getValue();
        $stu_gtime = $sheet->getCell("F".$i)->getValue();
        if(empty($stu_credit)){
            $stu_credit = 0;
        }
        if(empty($stu_gpa)){
            $stu_gpa = 0;
        }
        $stu_etime = excelTime($stu_etime);
        if(!empty($stu_gtime)){
            $stu_gtime = excelTime($stu_gtime);
        }else{
            $stu_gtime = null;
        }
        //phpExcel有时会读入空行
        if(empty($stu_id))
            continue;

        //检查此学生数据是否已经存在及是否要更新
        $stmt_check->bind_param("s",$stu_id);
        $stmt_check->execute();
        $check_result = $stmt_check->get_result();

        if($check_result->num_rows != 0){
            $row = $check_result->fetch_assoc();
            if($row['student_id'] == $stu_id && $row['student_name'] == $stu_name && $row['total_credit'] == $stu_credit && $row['gpa'] == $stu_gpa
                && $row['enroll_time'] == $stu_etime && $row['graduate_time'] == excelTime($stu_gtime)){
                //数据库内数据一样，不变
                $result = true;
            }else{
                //更新学生信息
                if(empty($stu_gtime)){
                    $stmt_update_not_graduate->bind_param("sidss",$stu_name,$stu_credit,$stu_gpa,$stu_etime,$stu_id);
                    $result = $stmt_update_not_graduate->execute();
                }else{
                    $stmt_update_graduated->bind_param("sidsss",$stu_name,$stu_credit,$stu_gpa,$stu_etime,$stu_gtime,$stu_id);
                    $result = $stmt_update_graduated->execute();
                }
            }
        }else{
            if(empty($stu_gtime)){
                //还没毕业
                $stmt_not_graduate->bind_param("ssids",$stu_id,$stu_name,$stu_credit,$stu_gpa,$stu_etime);
                $result = $stmt_not_graduate->execute();
            }else{
                //已经毕业
                $stmt_graduated->bind_param("ssidss",$stu_id,$stu_name,$stu_credit,$stu_gpa,$stu_etime,$stu_gtime);
                $result = $stmt_graduated->execute();
            }
        }

        if(!$result){
            $sta = false;
            echo "<script>alert('请查看id:".$stu_id.",name:".$stu_name." 的数据，出现错误')</script>";
        }else{
            $stmt_not_graduate->free_result();
            $stmt_graduated->free_result();
            $stmt_check->free_result();
            $stmt_update_not_graduate->free_result();
            $stmt_update_graduated->free_result();
        }
    }

    if($sta){
        echo "<script>alert('导入成功')</script>";
    }

}

/**
 * @param $date - excel里读出来的东西
 * @return false|string
 * 如果excel里单元格是日期类型，phpExcel读出来是纳秒数，需要进行转换
 */
function excelTime($date){
    if($date == null)
        return null;
    $type1 = strpos($date,'/');
    $type2 = strpos($date,'-');
    if($type1 || $type2){
        $ret_date = $date;
    }else{
        $ret_date = date('Y/m/d',PHPExcel_Shared_Date::ExcelToPHP($date));
    }
    return $ret_date;
}

function teacherLoader($conn,$filename){
    mysqli_set_charset($conn, "utf8");
    $sheet = basicExcelLoader($filename);
    $highestRow = $sheet->getHighestRow();

    $stmt_not_quit = $conn->prepare("insert into instructor (instructor_id,instructor_name,hire_time,quit_time) values (?,?,?,null)");
    $stmt_quit = $conn->prepare("insert into instructor (instructor_id,instructor_name,hire_time,quit_time) values (?,?,?,?)");
    $stmt_check = $conn->prepare("select * from instructor where instructor_id=?");
    $stmt_update_not_quit = $conn->prepare("update instructor set instructor_name=?,hire_time=?,quit_time=null where instructor_id=?");
    $stmt_update_quit = $conn->prepare("update instructor set instructor_name=?,hire_time=?,quit_time=? where instructor_id=?");

    $sta = true;
    for($i = 2; $i <= $highestRow; $i++){
        $ins_id = $sheet->getCell("A".$i)->getValue();
        $ins_name = $sheet->getCell("B".$i)->getValue();
        $ins_htime = $sheet->getCell("C".$i)->getValue();
        $ins_qtime = $sheet->getCell("D".$i)->getValue();
        $ins_htime = excelTime($ins_htime);
        if(empty($ins_qtime))
            $ins_qtime = null;
        else
            $ins_qtime = excelTime($ins_qtime);

        //phpExcel有时会读入空行
        if(empty($ins_id))
            continue;


        $stmt_check->bind_param("s",$ins_id);
        $stmt_check->execute();
        $check_result = $stmt_check->get_result();
        if($check_result->num_rows){
            $row = $check_result->fetch_assoc();
            if($row["instructor_id"] == $ins_id && $row["instructor_name"] == $ins_name
                && $row["hire_time"] == $ins_htime && $row["quit_time"] == $ins_qtime){
                $result = true;
            }else{
                //更新老师信息
                if($ins_qtime == null){
                    $stmt_update_not_quit->bind_param("sss",$ins_name,$ins_htime,$ins_id);
                    $result = $stmt_update_not_quit->execute();
                }else{
                    $stmt_update_quit->bind_param("ssss",$ins_name,$ins_htime,$ins_qtime,$ins_id);
                    $result = $stmt_update_quit->execute();
                }
            }
        }else{
            if($ins_qtime == null){
                $stmt_not_quit->bind_param("sss",$ins_id,$ins_name,$ins_htime);
                $result = $stmt_not_quit->execute();
            }else{
                $stmt_quit->bind_param("ssss",$ins_id,$ins_name,$ins_htime,$ins_qtime);
                $result = $stmt_quit->execute();
            }
        }

        if(!$result){
            $sta = false;
            echo "<script>alert('请查看id:".$ins_id.",name:".$ins_id." 的数据，出现错误')</script>";
        }else{
            $stmt_not_quit->free_result();
            $stmt_quit->free_result();
            $stmt_check->free_result();
            $stmt_update_not_quit->free_result();
            $stmt_update_quit->free_result();
        }
    }
    if($sta){
        echo "<script>alert('导入成功')</script>";
    }
}

function scoreLoader($role,$filename,$course_id,$sec_id,$semester,$year){
//    mysqli_set_charset($conn, "utf8");
//    var_dump($conn);
//    echo "----------------\n";
    $sheet = basicExcelLoader($filename);
//    var_dump($conn);
//    echo "----------------\n";
    $highestRow = $sheet->getHighestRow();
//    var_dump($conn);
//    echo "----------------\n";
    $sta = true;
    for($i = 2; $i <= $highestRow; $i++){
        $student_id = $sheet->getCell("A".$i)->getValue();
        $score = $sheet->getCell("B".$i)->getValue();
        if(empty($student_id))
            break;
//        var_dump($conn);
//        echo "----------------\n";
        /*如果用同一个连接为每行插入的化，查询语句会报Commands out of sync错误*/
        $conn = connectToDB("127.0.0.1",$role,$role);
        if(!commit_grade_for_one_student($conn,$student_id,$course_id,$sec_id,$semester,$year,$score)){
            $sta = false;
        }
        $conn->close();
    }
    if($sta){
        echo "<script>alert('导入成功')</script>";
    }

}

function courseLoader($conn,$filename){
    mysqli_set_charset($conn, "utf8");
    $sheet = basicExcelLoader($filename);
    $highestRow = $sheet->getHighestRow();

    $sta = true;
    for($i = 2; $i <= $highestRow; $i++){
        $course_id = $sheet->getCell("A".$i)->getValue();
        $course_name = $sheet->getCell("B".$i)->getValue();
        $credit = $sheet->getCell("C".$i)->getValue();
        $class_hours = $sheet->getCell("D".$i)->getValue();
        $stmt_check = $conn->prepare("select * from course where course_id = ?");
        $stmt_check->free_result();
        $stmt_check->bind_param("s",$course_id);
        $stmt_check->execute();
        $check_result = $stmt_check->get_result();
        if($check_result->num_rows){//更新课程
            $stmt_update = $conn->prepare("update course set course_name=?,credit=?,class_hours=? where course_id=?");
            $stmt_update->bind_param("siis",$course_name,$credit,$class_hours,$course_id);
            $r = $stmt_update->execute();
        }else{
            $stmt_insert = $conn->prepare("insert into course (course_id,course_name,credit,class_hours) values (?,?,?,?)");
            $stmt_insert->bind_param("ssii",$course_id,$course_name,$credit,$class_hours);
            $r = $stmt_insert->execute();
        }
        if(!$r){
            $sta = false;
            echo "<script>alert('".$course_id.$course_name."添加/更新失败，请检查')</script>";
        }

    }
    if($sta){
        echo "<script>alert('导入成功')</script>";
    }else{
        echo "<script>alert('导入结束')</script>";
    }

}
 ?>
