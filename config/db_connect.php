<?php
//  الاتصال بقاعدة البيانات
$host = "localhost";
$db_name = "tawasul_db";
$username = "root";
$password = "";
try {
    $dsn = "mysql:host=$host;dbname=$db_name;charset=utf8mb4";
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    //echo "تم الاتصال بنجاح";

} catch (PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}
?>