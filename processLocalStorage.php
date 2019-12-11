<?php
/**
 * Created by PhpStorm.
 * User: 36513
 * Date: 2019/12/10
 * Time: 14:45
 * @param $name
 * @param $value
 */
require_once 'components.php';
require_once 'ConnectSQL.php';
require_once 'process_local_storage_helper.php';

session_start();

if(!isset($_SESSION['role'])){
    jump_to_page("index.php");
}
$role = $_SESSION['role'];
$conn = connectToDB("127.0.0.1", $role, $role, "course_select_system");
$redo_list = [];
if(isset($_POST['recover'])){
    foreach ( $_POST as $key => $value )
    {
        if($key !== "recover"){
            $redo_list[$key] = $value;
        }
    }
    recover_local_storage($redo_list, $conn);
}

