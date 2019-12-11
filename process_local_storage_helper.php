<?php
/**
 * Created by PhpStorm.
 * User: 36513
 * Date: 2019/12/10
 * Time: 19:01
 */

function check_local_storage(){
    echo "<script>
$(function(){
    function checkStorage() {
        return JSON.stringify(localStorage) == '{}' ? false : true;
    };
    if(checkStorage()){
        alert('检测到上次有未执行完的数据插入请求，开始重新执行');
        for (var i = 0; i < localStorage.length; i++) {
            var key = localStorage.key(i); 
            var value = localStorage.getItem(key);
            $.ajax({
            type : 'POST',
            url : 'processLocalStorage.php',
            data : {'recover':'true',key:value},
            success : function(result) {
                if(result === 1)
                    alert('恢复完毕');
                else
                    alert(result);
            },
            error : function(e){
                alert(e.responseText);
            }
            });
       }
    }
});
</script>";
}

function add_local_storage($name, $value){
    echo "
<script>
    localStorage.setItem($name,$value);
</script>
";
}
function clear_local_storage(){
    echo "<script>localStorage.clear();</script>";
}

/*
 * take_lesson($conn, $student_id, $sec_id, $year, $course_id, $semester)
 * drop_lesson($conn, $student_id, $sec_id, $course_id, $semester, $year)
 * make_application($conn, $student_id, $sec_id, $course_id, $semester, $year, $appli_content)
 * handle_application($conn, $app_id, $new_app_status)
 * handle_unavailable_applications($conn, $sec_id, $course_id, $semester, $year)
 * commit_grade_for_one_student($conn,$student_id,$course_id,$sec_id,$semester,$year,$grade)
 * update_student_personal_info($conn,$student) //param is a object
 * delete_student_personal_info($conn,$id)
 * update_instructor($conn,$instructor) //param is a object
 * delete_teacher($conn,$instructor_id)
 * import_one_student($conn,$student) //param is a object
 * import_one_instructor($conn,$instructor) //param is a object
 * delete_section($conn,$course_id,$sec_id,$semester,$year)
 * delete_course($conn,$course_id)
 * import_one_course($conn,$course_id,$course_name,$course_credit,$class_hours)
 * import_one_section($conn,$course_id,$sec_id,$semester,$year,$sweek,$eweek,$number,$instructor_str,
 * $exam_week,$exam_day,$exam_type,$exam_des,$exam_stime,$exam_etime,$classtime_str)
 * load_single_section($conn,$values,$class_time,$insIdArray,$exam_week,$exam_day,$exam_type,
                             $exam_des,$exam_stime,$exam_etime,$error_mes_update,$error_mes_insert,
                             $error_mes_insert_conflict1,$error_mes_insert_conflict2)
   change_status2initializing($conn)
   change_status2starting($conn)
   change_status2grading($conn)
   arrange_test($conn)
 * */
function recover_local_storage($redo_list, $conn){
    try {
        foreach ($redo_list as $key => $value) {
            $params = explode("|||", $value);
            switch ($key) {
                case "take_lesson":
                    take_lesson($conn, $params[0], $params[1], $params[2], $params[3], $params[4]);
                    break;
                case "drop_lesson":
                    drop_lesson($conn, $params[0], $params[1], $params[2], $params[3], $params[4]);
                    break;
                case "make_application":
                    make_application($conn, $params[0], $params[1], $params[2], $params[3], $params[4], $params[5]);
                    break;
                case "handle_application":
                    handle_application($conn, $params[0], $params[1]);
                    break;
                case "handle_unavailable_applications":
                    handle_unavailable_applications($conn, $params[0], $params[1], $params[2], $params[3]);
                    break;
                case "commit_grade_for_one_student":
                    commit_grade_for_one_student($conn, $params[0], $params[1], $params[2], $params[3], $params[4], $params[5]);
                    break;
                case "update_student_personal_info":
                    $student = json_decode($params[0]);
                    update_student_personal_info($conn, $student);
                    break;
                case "delete_student_personal_info":
                    delete_student_personal_info($conn, $params[0]);
                    break;
                case "update_instructor":
                    $ins = json_decode($params[0]);
                    update_instructor($conn, $ins);
                    break;
                case "delete_teacher":
                    delete_teacher($conn, $params[0]);
                    break;
                case "import_one_student":
                    $student = json_decode($params[0]);
                    import_one_student($conn, $student);
                    break;
                case "import_one_instructor":
                    $ins = json_decode($params[0]);
                    import_one_instructor($conn, $ins);
                    break;
                case "delete_section":
                    delete_section($conn, $params[0], $params[1], $params[2], $params[3]);
                    break;
                case "delete_course":
                    delete_course($conn, $params[0]);
                    break;
                case "import_one_course":
                    import_one_course($conn, $params[0], $params[1], $params[2], $params[3]);
                    break;
                case "import_one_section":
                    import_one_section($conn, $params[0], $params[1], $params[2], $params[3], $params[4], $params[5], $params[6],
                        $params[7], $params[8], $params[9], $params[10], $params[11], $params[12], $params[13], $params[14]);
                    break;
                case "change_status2initializing":
                    change_status2initializing($conn);
                    break;
                case "change_status2starting":
                    change_status2starting($conn);
                    break;
                case "arrange_test":
                    arrange_test($conn);
                    break;
            }
            echo 1;
        }
    }catch (Exception $exception){
        var_dump($exception);
    }

}
