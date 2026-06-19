<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../models/Announcement.php';

class AdminTest extends TestCase
{
    private $db;
    private $admin;
         // تهيئة الاتصال بقاعدة البيانات 
    protected function setUp(): void
    {
        $database = Database::getInstance();
        $this->db = $database->getConnection();

        $userData = [
            'userID' => 1,
            'userName' => 'admin',
            'name' => 'Admin',
            'role' => 'admin'
        ];

        $this->admin = new Admin(
            $this->db,
            $userData
        );
    }
    // اختبار جلب جميع المستخدمين
    public function testGetAllUsers()
    {
        $result = $this->admin->getAllUsers();

        $this->assertIsArray($result);

        $this->assertNotEmpty($result);
        $this->assertObjectHasProperty('userID', $result[0]);

        $this->assertObjectHasProperty('role',$result[0]);
        $this->assertObjectHasProperty('name',$result[0]);

        $this->assertObjectHasProperty('userName',$result[0]);
    }
// اختبار جلب مستخدم بواسطة المعرف
    public function testGetUserById()
    {
        $result = $this->admin->getUserById(1);

        $this->assertNotFalse($result);

        $this->assertEquals(1,$result->userID);

        $this->assertEquals('admin',$result->role);
        $this->assertEquals('admin_tawasul',$result->userName);
    }
// اختبار جلب جميع معرفات أولياء الأمور
    public function testGetParents()
    {
        $result = $this->admin->getAllParentIDs();

        $this->assertIsArray($result);

        $this->assertContains(2,$result);
        $this->assertNotEmpty($result);
        $this->assertGreaterThan(0,count($result));
    }

    // اختبار منع حذف حساب المدير
    public function testDeleteAdmin()
    {
        $result = $this->admin->deleteUser(1);

        $this->assertFalse($result);
    }

    // اختبار إضافة مستخدم جديد
    public function testAddUser()
    {
        $result = $this->admin->addUser("Test User","test_user","123456","parent");

        $this->assertTrue($result);

        $user = $this->admin->getUserById( $this->db->lastInsertId());

        $this->assertNotFalse($user);

        $this->assertEquals("Test User",$user->name);

        $this->assertEquals("parent",$user->role);
    }
    // اختبار تحديث بيانات مستخدم
    public function testUpdateUser()
    {
        $result = $this->admin->updateUser(
            2,
            "Parent Updated",
            "parent_updated"
        );

        $this->assertTrue($result);
        $user = $this->admin->getUserById(2);

        $this->assertEquals(
            "Parent Updated",
            $user->name
        );
        $this->assertEquals(
            "parent_updated",
            $user->userName
        );
    }
    // اختبار حذف مستخدم غير موجود
    public function testDeleteInvalid()
    {
        $result = $this->admin->deleteUser(
            99999
        );

        $this->assertFalse($result);
    }
    // اختبار جلب جميع الاستفسارات
    public function testViewAll()
    {
        $result = $this->admin->viewInquiries();

        $this->assertIsArray($result);

        $this->assertNotEmpty($result);
    }
// اختبار جلب استفسار محدد مع الرسائل
    public function testViewById()
    {
        $result = $this->admin->viewInquiries(5);

        $this->assertArrayHasKey('details',$result);

        $this->assertArrayHasKey('chat',$result );
        $this->assertEquals(5,$result['details']->inquiryID);

        $this->assertIsArray($result['chat']);
    }
    // اختبار جلب استفسار غير موجود
    public function testInvalidInquiry()
    {
        $result = $this->admin->viewInquiries(99999);

        $this->assertFalse($result['details']);
    }
    // اختبار جلب مستخدم غير موجود
    public function testInvalidUser()
    {
        $result = $this->admin->getUserById(99999);

        $this->assertFalse($result);
    }

    // اختبار إضافة إعلان جديد
    public function testAddAnnouncement()
    {
        $title = "إعلان اختبار " . time();

        $announcement = new Announcement($title, "محتوى اختبار", "test.jpg");

        $result = $this->admin->addAnnouncement($announcement);

        $this->assertTrue($result);


        $stmt = $this->db->prepare("SELECT * FROM announcements WHERE title = ?");

        $stmt->execute([$title]);
        $data = $stmt->fetch();


        $this->assertNotFalse($data);

        $this->assertEquals($title, $data['title']);
    }
   
    // اختبار تعديل إعلان
    public function testUpdateAnnouncement()
    {
        $announcement = new Announcement("عنوان معدل","محتوى معدل","updated.jpg");

        $announcement->setAnnouncementId(7);

        $result = $this->admin->updateAnnouncement($announcement);

        $this->assertTrue($result);

        $stmt = $this->db->prepare("SELECT title,content FROM announcements WHERE announcementID = ?");

        $stmt->execute([7]);

        $data = $stmt->fetch();

        $this->assertEquals( "عنوان معدل",$data['title']);
        $this->assertEquals("محتوى معدل",$data['content']);
    }
    // اختبار حذف إعلان
    public function testDeleteAnnouncement()
    {
        $announcement = new Announcement("Delete Test","Delete Content","delete.jpg");

        $this->admin->addAnnouncement($announcement);

        $id = $this->db->lastInsertId();

        $result = $this->admin->deleteAnnouncement($id);

        $this->assertTrue($result);

        $stmt = $this->db->prepare("SELECT * FROM announcements WHERE announcementID = ?");

        $stmt->execute([$id]);

        $this->assertFalse($stmt->fetch());
    }
    // اختبار حذف حساب ولي أمر
    public function testDeleteParent()
    {
        $this->admin->addUser("Delete User","delete_user","123456","parent");

        $id = $this->db->lastInsertId();

        $result = $this->admin->deleteUser($id);

        $this->assertTrue($result);

        $user = $this->admin->getUserById($id);

        $this->assertFalse($user);
    }
    // اختبار إضافة طالب جديد
    public function testAddStudent()
    {
        $result = $this->admin->addStudent("طالب اختبار", 1, 2);

        $this->assertTrue($result);

        $stmt = $this->db->prepare("SELECT * FROM students WHERE studentName = ?");

        $stmt->execute([ "طالب اختبار"]);
        $data = $stmt->fetch();

        $this->assertNotFalse($data);


        $this->assertEquals(1,$data['classID']);
        $this->assertEquals(2,$data['parentID']);
    }

    // اختبار منع إضافة إعلان مكرر
    public function testDuplicateAnnouncement()
    {
        $title = "Duplicate Announcement " . time();
        $content = "Duplicate Content";

        $announcement1 = new Announcement($title,$content,"test.jpg");

        $announcement2 = new Announcement($title,$content,"test.jpg");

        $this->assertTrue($this->admin->addAnnouncement($announcement1));

        $this->assertFalse( $this->admin->addAnnouncement($announcement2));
    }
// اختبار فشل إضافة إعلان عند حدوث استثناء
    public function testAddAnnouncementException()
    {
        $db = $this->createMock(PDO::class);

        $db->method('prepare')
            ->willThrowException(new PDOException());

        $admin = new Admin($db);

        $announcement = new Announcement("Title","Content","test.jpg");

        $this->assertFalse($admin->addAnnouncement($announcement));
    }
// اختبار فشل تعديل إعلان عند حدوث استثناء
    public function testUpdateAnnouncementException()
    {
        $db = $this->createMock(PDO::class);

        $db->method('prepare')
            ->willThrowException(new PDOException());

        $admin = new Admin($db);

        $announcement = new Announcement("Title","Content","img.jpg");

        $this->assertFalse(
            $admin->updateAnnouncement($announcement)
        );
    }
    // اختبار فشل حذف إعلان عند حدوث استثناء
    public function testDeleteAnnouncementException()
    {
        $db = $this->createMock(PDO::class);

        $db->method('prepare')->willThrowException(new PDOException());

        $admin = new Admin($db);

        $this->assertFalse( $admin->deleteAnnouncement(1));
    }
// اختبار إرجاع مصفوفة فارغة عند حدوث استثناء أثناء جلب المستخدمين
    public function testGetAllUsersException()
    {
        $db = $this->createMock(PDO::class);

        $db->method('prepare')
            ->willThrowException(new PDOException());

        $admin = new Admin($db);

        $this->assertEquals(
            [],
            $admin->getAllUsers()
        );
    }
    // اختبار فشل حذف مستخدم عند حدوث استثناء
    public function testDeleteUserException()
{
    $pdo = $this->createMock(PDO::class);

    $pdo->method('prepare')
        ->willThrowException(new PDOException());

    $admin = $this->getMockBuilder(Admin::class)->setConstructorArgs([$pdo])->onlyMethods(['getUserById'])->getMock();

    // نجعل getUserById ترجع مستخدم عادي
    $admin->method('getUserById')->willReturn((object)['role' => 'parent']);

    $this->assertFalse($admin->deleteUser(2));
}

    public function testAddUserReturnsFalseOnException()
    {
        $db = $this->createMock(PDO::class);

        $db->method('prepare')->willThrowException(new PDOException());

        $admin = new Admin($db);

        $this->assertFalse($admin->addUser("A","B","123","parent"));
    }
    // اختبار فشل إضافة مستخدم عند حدوث استثناء

    public function testAddStudentException()
    {
        $db = $this->createMock(PDO::class);

        $db->method('prepare')
            ->willThrowException(new PDOException());

        $admin = new Admin($db);

        $this->assertFalse($admin->addStudent("Student",1,2));
    }
    //عند حدوث استثناء أثناء جلب مستخدم دnullاختبار إرجاع  
    public function testGetUserException()
    {
        $db = $this->createMock(PDO::class);

        $db->method('prepare')->willThrowException(new PDOException());

        $admin = new Admin($db);

        $this->assertNull($admin->getUserById(1));
    }
    // اختبار فشل تحديث بيانات مستخدم عند حدوث استثناء
    public function testUpdateUserException()
    {
        $db = $this->createMock(PDO::class);

        $db->method('prepare')
            ->willThrowException(new PDOException());

        $admin = new Admin($db);

        $this->assertFalse( $admin->updateUser(1,"A","B"));
    }
    // اختبار إرجاع مصفوفة فارغة عند حدوث استثناء أثناء جلب جميع الاستفسارات
    public function testViewAllException()
    {
        $db = $this->createMock(PDO::class);

        $db->method('prepare')->willThrowException(new PDOException());

        $admin = new Admin($db);

        $result = $admin->viewInquiries();

        $this->assertEquals([], $result);
    }
    // اختبار إرجاع null عند حدوث استثناء أثناء جلب استفسار محدد
    public function testViewByIdException()
    {
        $db = $this->createMock(PDO::class);

        $db->method('prepare')->willThrowException(new PDOException());

        $admin = new Admin($db);

        $this->assertNull( $admin->viewInquiries(5));
    }
    // اختبار إرجاع مصفوفة فارغة عند حدوث استثناء أثناء جلب معرفات أولياء الأمور
    public function testGetParentsException()
    {
        $db = $this->createMock(PDO::class);

        $db->method('prepare')->willThrowException(new PDOException());

        $admin = new Admin($db);

        $this->assertEquals([],$admin->getAllParentIDs());
    }
}
