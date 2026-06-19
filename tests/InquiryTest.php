<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../models/Inquiry.php';

class InquiryTest extends TestCase
{
    private $db;
    private $inquiry;
     
    // تهيئة الاتصال بقاعدة البيانات 
    protected function setUp(): void
    {
        $database = Database::getInstance();
        $this->db = $database->getConnection();

        $this->inquiry = new Inquiry($this->db);
    }
    // اختبار تحديث حالة استفسار بنجاح
    public function testUpdateStatus()
    {
        $result = $this->inquiry->updateStatus(5,"تم الرد");

        $this->assertTrue($result);
        $stmt = $this->db->prepare("SELECT status FROM inquiries WHERE inquiryID = ?");

        $stmt->execute([5]);

        $data = $stmt->fetch();

        $this->assertEquals("تم الرد",$data['status']);
    }
    // اختبار جلب جميع الاستفسارات
    public function testViewAll()
    {
        $result = $this->inquiry->viewInquiries();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertObjectHasProperty(
            'inquiryId',
            $result[0]
        );

        $this->assertObjectHasProperty(
            'status',
            $result[0]
        );
        $this->assertObjectHasProperty(
            'parentName',
            $result[0]
        );
    }

    // اختبار جلب استفسار محدد مع الرسائل المرتبطة به
    public function testViewById()
    {
        $result = $this->inquiry->viewInquiries(5);

        $this->assertIsArray($result);

        $this->assertArrayHasKey('details',$result);

        $this->assertArrayHasKey('chat',$result);
        $this->assertIsArray($result['chat']);
        $this->assertEquals(5,$result['details']->inquiryID);
    }
    // اختبار إنشاء استفسار جديد وحفظه في قاعدة البيانات
    public function testCreate()
    {
        $result = $this->inquiry->create(2,"اختبار PHPUnit","رسالة تجريبية");

        $this->assertTrue($result);
        $stmt = $this->db->prepare("SELECT * FROM inquiries WHERE subject = ?");
        $stmt->execute(["اختبار PHPUnit"]);
        $data = $stmt->fetch();

        $this->assertNotFalse($data);

        $stmt2 = $this->db->prepare("SELECT * FROM messages WHERE inquiryID = ?");

        $stmt2->execute([$data['inquiryID']]);

        $message = $stmt2->fetch();

        $this->assertNotFalse($message);

        $this->assertEquals("رسالة تجريبية",$message['messageText']);

        $this->assertNotFalse($data);

        $this->assertEquals("قيد الانتظار",$data['status']);
    }
   // اختبار التعامل مع معرف استفسار غير موجود
    public function testInvalidId()
    {
        $result = $this->inquiry->viewInquiries(99999);

        $this->assertIsArray($result);

        $this->assertFalse($result['details']);
        $this->assertEmpty(
            $result['chat']
        );
    }

    // اختبار فشل إنشاء استفسار عند حدوث استثناء
    public function testCreateException()
    {
        $db = $this->createMock(PDO::class);

        $db->method('beginTransaction')
            ->willThrowException(new Exception());

        $inquiry = new Inquiry($db);

        $this->assertFalse($inquiry->create(1,"عنوان","رسالة"));
    }
    
    // اختبار فشل تحديث الحالة عند حدوث استثناء
    public function testUpdateStatusException()
    {
        $db = $this->createMock(PDO::class);

        $db->method('prepare')->willThrowException(new Exception());

        $inquiry = new Inquiry($db);

        $this->assertFalse($inquiry->updateStatus(5,"تم الرد" ));
    }
    // اختبار إرجاع مصفوفة فارغة عند حدوث استثناء أثناء جلب جميع الاستفسارات
    public function testViewAllException()
    {
        $db = $this->createMock(PDO::class);

        $db->method('prepare')->willThrowException(new PDOException());

        $inquiry = new Inquiry($db);

        $result = $inquiry->viewInquiries();

        $this->assertEquals([], $result);
    }
    //عند حدوث استثناء أثناء جلب استفسار محدد nullاختبار إرجاع  
    public function testViewByIdException()
    {
        $db = $this->createMock(PDO::class);

        $db->method('prepare')->willThrowException(new PDOException());

        $inquiry = new Inquiry($db);

        $this->assertNull($inquiry->viewInquiries(5));
    }
}
