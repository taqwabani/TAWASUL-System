<?php
require_once "Admin.php";
require_once "perent.php";

class UserFactory {
    public static function createUser($role, $db, $userData) {
        switch ($role) {
            case 'admin':
                return new Admin($db, $userData);
            case 'parent':
                return new ParentUser($db, $userData);
            default:
                throw new Exception("دور المستخدم غير معروف!");
        }
    }
}