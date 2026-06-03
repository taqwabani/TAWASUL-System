<?php
/**
 * نمط المصنع (Factory Pattern) لإنشاء كائنات المستخدمين بناءً على دورهم
 */
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Admin.php';

// التأكد من وجود ملف ولي الأمر 
    require_once __DIR__ . '/perent.php';

class UserFactory {
    /**
     * إنشاء كائن مستخدم جديد
     * @param PDO $db اتصال قاعدة البيانات
     * @param string $role دور المستخدم (admin, parent)
     * @return Admin|ParentUser
     */
    public static function create($db, $role) {
        switch ($role) {
            case 'admin':
                return new Admin($db);
            case 'parent':
                return new ParentUser($db) ;
            default:
                return new User($db);
        }
    }
}