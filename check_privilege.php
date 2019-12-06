<?php
/**
 * @param $conn
 * @param $tablename
 * @param $priv 必须大写 "ALL PRIVILEGES";"SELECT";"INSERT";"UPDATE"...
 * @return bool 判断当前连接有无$priv权限
 */
function check_if_have_privilege($conn,$tablename,$priv){

    $dbname = "course_select_system";
    $query = mysqli_query($conn,"SHOW GRANTS FOR CURRENT_USER()");
    if($tablename == "*"){
        $needle = "`".$dbname."`.*";
    }else{
        $needle = "`".$dbname."`.`".$tablename."`";
    }
    while ($priv_row= mysqli_fetch_array($query)){
        if(strpos($priv_row[0],$needle) && strpos($priv_row[0],$priv)){
            return true;
        }
    }
    return false;
}

/**
 * @param $conn
 * @param $user
 * @param $tablename
 * @param $priv
 * @return bool 判断$user账户是否有$priv权限
 */

function check_his_privilege($conn,$user,$tablename,$priv){
    $dbname = "course_select_system";
    $tmp = "SHOW GRANTS FOR ".$user;
//    var_dump($conn);
//    echo "------------------";
    $query = mysqli_query($conn,$tmp);
//    var_dump($query);
    if($tablename == "*"){
        $needle = "`".$dbname."`.*";
    }else{
        $needle = "`".$dbname."`.`".$tablename."`";
    }
    while ($priv_row= mysqli_fetch_array($query)){
        if(strpos($priv_row[0],$needle) && strpos($priv_row[0],$priv)){
            return true;
        }
    }
    return false;
}

?>