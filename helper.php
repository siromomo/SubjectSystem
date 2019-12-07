<?php
/**
 * Created by PhpStorm.
 * User: 36513
 * Date: 2019/12/7
 * Time: 14:44
 */

require_once "readExcel.php";

insertIntoChartBasic("classroom", ["classroom_id", "capacity"], "si", $conn);
insertIntoChartBasic("course", ["course_id", "course_name", "credit", "class_hours"], "ssii", $conn);
insertIntoChartBasic("time_slot", ["time_slot_id", "start_time", "end_time", "day_of_week"], "sssi", $conn);
