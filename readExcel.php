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

function sectionLoader($conn){
    $sheet = basicExcelLoader("./data/section.xls");
    $highestRow = $sheet->getHighestRow(); // 取得总行数
    $stmt1 = $conn->prepare("insert into section (sec_id, semester, year, start_week, end_week, number, selected_num, 
                     course_id, exam_id) values(?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt2 = $conn->prepare("insert into class_time_place (time_slot_id, classroom_id, sec_id, year, course_id, semester) 
                     values(?, ?, ?, ?, ?, ?)");
    $stmt3 = $conn->prepare("insert into teaches(instructor_id, sec_id, year, course_id, semester) values (?,?,?,?,?)");

    for($i = 2; $i <= $highestRow; $i++){
        $values = [];
        $class_time = [];
        $A = ord("A");
        for($j = 0; $j < 9; $j++,$A++){
            $values[$j] = $sheet->getCell(chr($A) . $i)->getValue();
        }
        $insIds = $sheet->getCell(chr($A).$i)->getValue();
        $insIdArray = explode(",",$insIds);
        for($A++; ; $j++, $A += 2){
            $class = $sheet->getCell(chr($A) . $i)->getValue();
            $times = $sheet->getCell(chr($A+1) . $i)->getValue();
            if(strlen($class) == 0 || stristr($class, "冲突") || stristr($class, "-"))
                break;
           /* echo $class . "<br>";
            echo $times . "<br>";*/
            $class_time[$class]=$times;
        }
        $stmt1->bind_param("ssiiiiiss", $values[0], $values[1], $values[2], $values[3], $values[4],
            $values[5], $values[6], $values[7], $values[8]);
        $r1 = $stmt1->execute();
        if(!$r1){
            echo mysqli_error($conn);
        }
        if(sizeof($insIdArray)>0 && strlen($insIdArray[0])>0) {
            foreach ($insIdArray as $insId) {
                echo $insId . "<br>";
                $stmt3->bind_param("siiss", $insId, $values[0], $values[2], $values[7], $values[1]);
                $r3 = $stmt3->execute();
                if (!$r3) {
                    echo mysqli_error($conn) . "<br>";
                }
            }
        }
        foreach ( $class_time as $class => $times){
            $time = explode(",", $times);
            foreach ($time as $t){
                $stmt2->bind_param("ssiiss", $t, $class, $values[0], $values[2], $values[7], $values[1]);
               // echo $t . " ", $class. " ", $values[0]. " ", $values[2]. " ", $values[7]. " ", $values[1]. "<br>";
                $r2 = $stmt2->execute();
                if(!$r2){
                    if(stristr(mysqli_error($conn), "duplicate")){
                        echo "课程 $values[7] 与已有课程时间段冲突" . "<br>";
                    }
                    else
                        echo mysqli_error($conn);
                }
            }
        }
    }
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
    for($i = 2; $i < $highestRow; $i++){
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
    if($type1){
        $ret_date = $date;
    }else{
        $ret_date = date('Y/m/d',PHPExcel_Shared_Date::ExcelToPHP($date));
    }
    return $ret_date;
}

 ?>
