<?php
/**
 * Created by PhpStorm.
 * User: 36513
 * Date: 2019/11/22
 * Time: 21:01
 */
$servername = "localhost";
$username = "root";
$password = "090029";
$dbName = "lesson";

// 创建连接
$conn = new mysqli($servername, $username, $password, $dbName);

// 检测连接
if ($conn->connect_error) {
    die("连接数据库失败: " . $conn->connect_error);
}
