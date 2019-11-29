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
        takeCourse($conn, $values[0], $values[1], $values[4], $values[2], $values[3]);
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

 ?>
