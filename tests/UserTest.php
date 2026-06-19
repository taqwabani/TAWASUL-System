<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . "/../models/user.php";
class UserTest extends TestCase
{
    public function testconstruct()
    {
        $db = $this->createMock(PDO::class); // إنشاء قاعدة بيانات وهمية بدل الحقيقية
        $user = new User($db); // إنشاء كائن User وتمرير قاعدة البيانات الوهمية
        $this->assertInstanceOf(User::class, $user); // التأكد أن الكائن من نوع User
    }
    public function testvalidateLogin()
    {
        $stmt = $this->createMock(PDOStatement::class); // إنشاء استعلام وهمي
        $stmt->method('execute')->willReturn(true); // محاكاة نجاح تنفيذ الاستعلام
        $stmt->method('fetch')->willReturn([ // محاكاة البيانات القادمة من قاعدة البيانات
            'userID' => 1,
            'userName' => 'admin',
            'name' => 'Administrator',
            'password' => '123',
            'role' => 'admin'
        ]);
        $db = $this->createMock(PDO::class); // إنشاء اتصال قاعدة بيانات وهمي
        $db->method('prepare')->willReturn($stmt); // جعل prepare يرجع الاستعلام الوهمي
        $user = new User($db); // إنشاء كائن User
        $this->assertTrue($user->validateLogin('admin', '123')); // اختبار نجاح تسجيل الدخول (return true)
        $this->assertFalse($user->validateLogin('admin', '999')); // اختبار فشل كلمة المرور (return false)
    }
    public function testValidateLoginCatchException()
    {
        $db = $this->createMock(PDO::class); // إنشاء قاعدة بيانات وهمية
        $db->method('prepare')
           ->willThrowException(new PDOException("Database Error")); // إجبار قاعدة البيانات على رمي خطأ لتجربة catch
        $user = new User($db); // إنشاء كائن User
        $result = $user->validateLogin('admin', '123'); // تنفيذ تسجيل الدخول
        $this->assertFalse($result); // التأكد أن catch أعاد false
    }
    public function testlogin()
    {
        $db = $this->createMock(PDO::class); // إنشاء قاعدة بيانات وهمية
        $user = new User($db); // إنشاء كائن User
        $user->setUserId(1); // تعيين رقم المستخدم
        $user->setRole('admin'); // تعيين صلاحية المستخدم
        $user->setName('Admin'); // تعيين الاسم
        $this->assertTrue($user->login()); // اختبار إنشاء الجلسة بنجاح return true
    }
    public function testloginnotfoundRole()
    {
        $db = $this->createMock(PDO::class); // إنشاء قاعدة بيانات وهمية
        $user = new User($db); // إنشاء كائن User
        $user->setRole('teacher'); // تعيين الصلاحية بدون UserID
        $user->setName('teacher'); // تعيين الاسم
        $this->assertFalse($user->login()); // التأكد أن تسجيل الدخول فشل return false
    }
    public function testSetUserId()
    {
        $db = $this->createMock(PDO::class); // إنشاء قاعدة بيانات وهمية
        $user = new User($db); // إنشاء كائن User
        $user->setUserId(6); // تعيين رقم المستخدم
        $this->assertEquals(6, $user->getUserId()); // التأكد أن Getter يرجع الرقم الصحيح
    }
    public function testsetRole()
    {
        $db = $this->createMock(PDO::class); // إنشاء قاعدة بيانات وهمية
        $user = new User($db); // إنشاء كائن User
        $user->setRole('parent'); // تعيين الدور
        $this->assertEquals('parent', $user->getRole()); // التأكد من إرجاع الدور الصحيح
    }
    public function testsetName()
    {
        $db = $this->createMock(PDO::class); // إنشاء قاعدة بيانات وهمية
        $user = new User($db); // إنشاء كائن User
        $user->setName('علي'); // تعيين الاسم
        $this->assertEquals('علي', $user->getName()); // التأكد من الاسم
    }
    public function testgetUserName()
    {
        $stmt = $this->createMock(PDOStatement::class); // إنشاء استعلام وهمي
        $stmt->method('execute')->willReturn(true); // محاكاة نجاح تنفيذ الاستعلام
        $stmt->method('fetch')->willReturn([ // محاكاة بيانات المستخدم
            'userID' => 1,
            'userName' => 'parent_user',
            'name' => 'Ali',
            'password' => '123',
            'role' => 'parent'
        ]);
        $db = $this->createMock(PDO::class); // إنشاء قاعدة بيانات وهمية
        $db->method('prepare')->willReturn($stmt); // إرجاع الاستعلام الوهمي
        $user = new User($db); // إنشاء كائن User
        $user->validateLogin('parent_user', '123'); // تعبئة بيانات المستخدم داخل الكلاس
        $this->assertEquals('parent_user', $user->getUserName()); // التأكد أن Getter يرجع اسم المستخدم
    }
    public function testgetName()
    {
        $db = $this->createMock(PDO::class); // إنشاء قاعدة بيانات وهمية
        $user = new User($db); // إنشاء كائن User
        $user->setName('علي'); // تعيين الاسم
        $this->assertEquals('علي', $user->getName()); // اختبار إرجاع الاسم
    }
    public function testgetRole()
    {
        $db = $this->createMock(PDO::class); // إنشاء قاعدة بيانات وهمية
        $user = new User($db); // إنشاء كائن User
        $user->setRole('parent'); // تعيين الدور
        $this->assertEquals('parent', $user->getRole()); // اختبار إرجاع الدور
    }
    
}
