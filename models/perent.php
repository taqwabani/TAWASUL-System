<?php

require_once 'User.php';
require_once 'Inquiry.php';

//يمثل مستخدم من نوع "ولي أمر
//  لخصائص   User  يرث من كلاس 

class ParentUser extends User { 
    
    //دالة إرسال استفسار
    //تعمل كوسيط بين الواجهة وبين كلاس الاستفسار لتنفيذ عمليةالارسال
    public function sendInquiry($db, $subject, $messageText, $userID) {
        $inquiry = new Inquiry($db);
        //القادم من الصفحة مباشرةuserID اخد 
        return $inquiry->create($userID, $subject, $messageText);
    }
}

?>