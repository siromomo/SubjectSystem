<?php
unset($_SESSION['role']);
unset($_SESSION['st_id']);
$_SESSION = array();
if(isset($_COOKIE[session_name()])){
    setcookie(session_name(),'',time()-42000,'/');
}
session_destroy();
?>