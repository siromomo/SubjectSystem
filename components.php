<?php
/**
 * Created by PhpStorm.
 * User: 36513
 * Date: 2019/11/27
 * Time: 13:54
 * @param $conn
 * @param null $err
 */
function alert_error($conn, $err = null){
    if($err === null) {
        $err = mysqli_error($conn);
    }
    alert_msg($err);
}
function alert_msg($msg){
    echo "<script>alert('$msg')</script>";
}
