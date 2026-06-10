<?php
session_start();// بدء الجلسة  لحفظ رسائل الخطأ وبيانات المستخدم

require_once "config/db_connect.php";
require_once "models/Admin.php";
require_once "models/perent.php";
require_once "models/UserFactory.php";

// متغير لتخزين رسالة الخطأ وعرضها داخل الصفحة
$error = "";
$database = Database::getInstance();
$conn = $database->getConnection(); 

$user = new User($conn); 
// التحقق هل تم إرسال النموذج باستخدام POST
// وأيضًا التأكد من الضغط على زر تسجيل الدخول
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['loginBtn'])) {

    try {
         // تخزين اسم المستخدم وكلمة المرور القادمة من النموذج
        $u = $_POST['userName'];
        $p = $_POST['password'];
        
        if ($user->validateLogin($u, $p)) {   // التحقق من صحة بيانات تسجيل الدخول
            

             $userData = [
                'userID'   => $user->getUserId(),
                'userName' => $u,
                'name'     => $user->getName(), 
                'role'     => $user->getRole()
            ];

    
    
             $userObject = UserFactory::createUser($user->getRole(), $conn, $userData);
             $_SESSION['user'] = $userObject;

            $userObject->login(); // إنشاء جلسة للمستخدم بعد نجاح تسجيل الدخول
             
            if ($userObject instanceof Admin) {//للتحقق من نوع الكائن وتوجيهه للوحة التحكم الصحيحة
                header("Location: pages/dashboardA.php");
            } else {
                header("Location: pages/dashboard.php");
            }
            exit(); 
        }else {
            // حالة فشل تسجيل الدخول
            $_SESSION['error'] = "اسم المستخدم أو كلمة المرور غير صحيحة";
            header("Location: login.php");
            exit();
        }
                
    } catch (Exception $e) {
        
        $_SESSION['error'] = "حدث خطأ ، حاول لاحقاً";  // في حال حدوث خطأ تقني يتم حفظ رسالة خطأ عامة

        header("Location: login.php");
        exit();}
}

if (isset($_SESSION['error'])) {
    // تخزين الرسالة داخل المتغير لعرضها في الصفحة
    $error = $_SESSION['error'];

    // حذف الرسالة بعد عرضها
    //Refresh حتى لا تظهر مرة أخرى عند 
    unset($_SESSION['error']);
}

$htmlContent = file_get_contents('HTML/login.html');//HTML قراءة محتوى صفحة 
$htmlContent = str_replace('{{ERROR}}', $error, $htmlContent);// HTML داخل صفحة  {{ERROR}}استبدال
echo $htmlContent;// عرض الصفحة للمستخدم


?>