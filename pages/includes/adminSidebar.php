<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <img src="../images/BookOpen.svg" alt="Logo" class="logo-icon">
            <h2>TAWASUL</h2>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <a href="dashboardA.php" class="nav-item <?php echo ($currentPage == 'dashboardA.php') ? 'active' : ''; ?>">
            <img src="../images/lucide-LayoutDashboard.svg" class="nav-icon" alt="Dashboard"> 
            <span>الرئيسية</span>
        </a>
        
        <a href="adminAnnouncements.PHP" class="nav-item <?php echo ($currentPage == 'adminAnnouncements.PHP') ? 'active' : ''; ?>">
            <img src="../images/lucide-MessageSquare.svg" class="nav-icon" alt="Inquiries"> 
            <span>ادارة الاعلانات</span>
        </a>
        
        <a href="user.PHP" class="nav-item <?php echo ($currentPage == 'user.PHP') ? 'active' : ''; ?>">
            <img src="../images/lucide-Users.svg" class="nav-icon" alt="Schedule"> 
            <span>ادارة المستخدمين</span>
        </a>
        
        <a href="ViewAdminInquiries.php" class="nav-item <?php echo ($currentPage == 'ViewAdminInquiries.php') ? 'active' : ''; ?>">
            <img src="../images/lucide-Bell.svg" class="nav-icon" alt="Notifications"> 
            <span>الردود والاستفسارات</span>
        </a>
            <a href="AdminSchedules.php" class="nav-item <?php echo ($currentPage == 'AdminSchedules.php') ? 'active' : ''; ?>">
            <img src="../images/lucide-CalendarDays.svg" class="nav-icon" alt="Schedules"> 
            <span>ادارة الجداول الدراسية </span>
        </a>
            <a href="viewstudent.php" class="nav-item <?php echo ($currentPage == 'students.php') ? 'active' : ''; ?>">
            <img src="../images/lucide-CalendarDays.svg" class="nav-icon" alt="Schedules"> 
            <span> الطلاب   </span>
        </a>
        <a href="../logout.php" class="nav-item logout-link"onclick="return confirm('هل أنت متأكد من تسجيل الخروج؟');">
            
            <img src="../images/material-Login.svg" alt="Logout Icon" class="nav-icon">
            <span>تسجيل الخروج</span>
        </a>

    </nav>
</aside>