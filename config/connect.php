<?php 
   session_start();
    $host = "localhost";
    // user database
    $db_user = "root";
    // รหัส database
    $db_pass = "";
    // ชื่อ database
    $db = "inventory";
    //connect to database
    $conn = new PDO("mysql:host=$host;dbname=$db",$db_user,$db_pass);
    $conn->exec("set names utf8mb4");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //end connect to database
    //function query
    function dd_q($str, $arr = []) {
        global $conn;
        try {
            $exec = $conn->prepare($str);
            $exec->execute($arr);
        } catch (PDOException $e) {
            return false;
        }
        return $exec;
    }

    date_default_timezone_set('Asia/Bangkok');
?>