<?php

require_once __DIR__ . '/user.php';
require_once 'Inquiry.php';

//يمثل مستخدم من نوع "ولي أمر
//  لخصائص   User  يرث من كلاس 

class ParentUser extends User { 
    
    public function __construct($db,$userData = null) {
        parent::__construct($db);
        if ($userData) {
            $this->userID = $userData['userID'];
            $this->userName = $userData['userName'];
            $this->name = $userData['name'];
            $this->role = $userData['role'];
        }
    }

    //دالة إرسال استفسار
    //تعمل كوسيط بين الواجهة وبين كلاس الاستفسار لتنفيذ عمليةالارسال
    public function sendInquiry($db, $subject, $messageText, $userID) {
        $inquiry = new Inquiry($db);
        //القادم من الصفحة مباشرةuserID اخد 
        return $inquiry->create($userID, $subject, $messageText);
    }
}

?>