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
        <a href="dashboard.php" class="nav-item <?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>">
            <img src="../images/lucide-LayoutDashboard.svg" class="nav-icon" alt="Dashboard"> 
            <span>الرئيسية</span>
        </a>
        
        <a href="inquiries.php" class="nav-item <?php echo ($currentPage == 'inquiries.php') ? 'active' : ''; ?>">
            <img src="../images/lucide-MessageSquare.svg" class="nav-icon" alt="Inquiries"> 
            <span>الاستفسارات</span>
        </a>
        
        <a href="schedule.php" class="nav-item <?php echo ($currentPage == 'schedule.php') ? 'active' : ''; ?>">
            <img src="../images/lucide-CalendarDays.svg" class="nav-icon" alt="Schedule"> 
            <span>الجدول الدراسي</span>
        </a>
        
        <a href="notifications.php" class="nav-item <?php echo ($currentPage == 'notifications.php') ? 'active' : ''; ?>">
            <img src="../images/lucide-Bell.svg" class="nav-icon" alt="Notifications"> 
            <span>الإشعارات</span>
        </a>
        
        <a href="../logout.php" class="nav-item logout-link">
            <img src="../images/material-Login.svg" alt="Logout Icon" class="nav-icon">
            <span>تسجيل الخروج</span>
        </a>

    </nav>
     
</aside>