<?php
session_start();// بدء الجلسة  لحفظ رسائل الخطأ وبيانات المستخدم
include "config/db_connect.php";
include "models/UserFactory.php";
// متغير لتخزين رسالة الخطأ وعرضها داخل الصفحة
$error = "";

// التحقق هل تم إرسال النموذج باستخدام POST
// وأيضًا التأكد من الضغط على زر تسجيل الدخول
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['loginBtn'])) {

    try {
         // تخزين اسم المستخدم وكلمة المرور القادمة من النموذج
        $u = $_POST['userName'];
        $p = $_POST['password'];
        
        // نستخدم كائن مستخدم عام للتحقق الأولي
        $tempUser = new User($conn); 
        if ($tempUser->validateLogin($u, $p)) {   // التحقق من صحة بيانات تسجيل الدخول
            
            // بعد النجاح، نستخدم المصنع لإنشاء الكائن المتخصص (Admin أو Parent)
            $role = $tempUser->getRole();
            $user = UserFactory::create($conn, $role);
            $user->setUserId($tempUser->getUserId());
            $user->setRole($role);
            $user->setName($tempUser->getName());
            $user->login(); // إنشاء جلسة للمستخدم بعد نجاح تسجيل الدخول
          // التحقق من نوع المستخدم
            if ($user->getRole() == 'admin') {

                  // إذا كان الإدارة يتم تحويله إلى لوحة تحكم 
                header("Location: pages/dashboardA.php");
            } else {

                // إذا كان ولي أمر يتم تحويله إلى لوحة تحكم ولي الأمر
                header("Location: pages/dashboard.php");
            }
            exit();
        } else {
             //Session حفظ رسالة الخطأ مؤقتًا داخل 
            $_SESSION['error'] = "اسم المستخدم أو كلمة المرور غير صحيحة";
            
            header("Location: login.php"); // إعادة التوجيه لنفس صفحة تسجيل الدخول
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