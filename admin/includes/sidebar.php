<!-- sidebar.php -->
<div class="w-64 bg-white border-r border-gray-200 h-screen flex flex-col fixed top-0 left-0">
    <!-- Logo Section -->
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center">
                <img src="assets/imgs/logos/logo.webp" alt="BotWave Logo">
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900">BotWave</h2>
                <p class="text-sm text-gray-500">Admin Panel</p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 p-4 space-y-2">
        <a href="index.php" class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : 'text-gray-600' ?>">
            <i class="fas fa-home w-5"></i>
            <span class="flex-1">Dashboard</span>
        </a>
        <a href="messages.php"
            class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer text-gray-600 <?= basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : 'text-gray-600' ?>">
            <i class="fas fa-comment w-5"></i>
            <span class="flex-1">Messages</span>
            <span class="bg-whatsapp text-white px-2.5 py-1 rounded-full text-xs font-medium">24</span>
        </a>
        <a href="configurations.php"
            class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer text-gray-600 <?= basename($_SERVER['PHP_SELF']) == 'configurations.php' ? 'active' : 'text-gray-600' ?>">
            <i class="fas fa-robot w-5"></i>
            <span class="flex-1">Configurations</span>
        </a>
        <a href="contacts.php"
            class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer text-gray-600 <?= basename($_SERVER['PHP_SELF']) == 'contacts.php' ? 'active' : 'text-gray-600' ?>">
            <i class="fas fa-users w-5"></i>
            <span class="flex-1">Contacts</span>
            <span class="bg-whatsapp text-white px-2.5 py-1 rounded-full text-xs font-medium">156</span>
        </a>
        <a href="analytics.php"
            class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer text-gray-600 <?= basename($_SERVER['PHP_SELF']) == 'analytics.php' ? 'active' : 'text-gray-600' ?>">
            <i class="fas fa-chart-bar w-5"></i>
            <span class="flex-1">Analytics</span>
        </a>
        <a href="scheduled.php"
            class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer text-gray-600 <?= basename($_SERVER['PHP_SELF']) == 'scheduled.php' ? 'active' : 'text-gray-600' ?>">
            <i class="fas fa-calendar w-5"></i>
            <span class="flex-1">Scheduled</span>
        </a>
        <a href="templates.php"
            class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer text-gray-600 <?= basename($_SERVER['PHP_SELF']) == 'templates.php' ? 'active' : 'text-gray-600' ?>">
            <i class="fas fa-file-text w-5"></i>
            <span class="flex-1">Templates</span>
        </a>
        <a href="notifications.php"
            class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer text-gray-600 <?= basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'active' : 'text-gray-600' ?>">
            <i class="fas fa-bell w-5"></i>
            <span class="flex-1">Notifications</span>
        </a>
        <a href="settings.php"
            class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer text-gray-600 <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : 'text-gray-600' ?>">
            <i class="fas fa-cog w-5"></i>
            <span class="flex-1">Settings</span>
        </a>
    </nav>

    <!-- Bottom Section -->
    <div class="p-4 border-t border-gray-200">
        <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50">
            <div class="w-8 h-8 bg-whatsapp rounded-full flex items-center justify-center">
                <span class="text-white text-sm font-medium"><i class="fas fa-user"></i></span>
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-900"><?php echo $_SESSION['user']['name']; ?></p>
                <p class="text-xs text-gray-500"><?php echo $_SESSION['user']['email']; ?></p>
            </div>
        </div>
    </div>
</div>