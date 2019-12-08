<?php
/**
 * Created by PhpStorm.
 * User: 36513
 * Date: 2019/12/8
 * Time: 19:31
 */
require_once 'components.php';
require_once 'ConnectSQL.php';
$conn = connectToDB();
if(arrange_test($conn)){
    echo 0;
}else{
    echo 1;
}
