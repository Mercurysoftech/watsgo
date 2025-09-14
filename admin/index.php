<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BotWave Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'whatsapp': '#25D366',
                        'whatsapp-dark': '#128C7E',
                        'whatsapp-light': '#DCF8C6',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: .5;
            }
        }

        .sidebar-item:hover {
            background-color: #f9fafb;
        }

        .sidebar-item.active {
            background-color: #25D366;
            color: white;
        }

        .chart-bar {
            transition: all 0.3s ease;
        }

        .chart-bar:hover {
            opacity: 0.8;
        }
    </style>
</head>

<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <?php include './includes/sidebar.php'; ?>


        <!-- Main Content -->
        <div class="ml-64 p-6 flex-1 flex flex-col">
            <!-- Header -->
            <header class="bg-white border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <!-- Left Section -->
                    <div class="flex items-center gap-4">
                        <div>
                            <h1 class="text-xl font-semibold text-gray-900">Dashboard</h1>
                            <p class="text-sm text-gray-500">Welcome back! <span class="font-medium text-green-600"><?php echo $_SESSION['user']['name']; ?></span></p>
                        </div>
                    </div>

                    <!-- Right Section -->
                    <div class="flex items-center gap-4">
                        <!-- Search -->
                        <div class="relative">
                            <i
                                class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" placeholder="Search messages, contacts..."
                                class="pl-10 pr-4 py-2 w-64 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                        </div>

                        <!-- Notifications -->
                        <button class="relative p-2 text-gray-600 hover:text-gray-900">
                            <i class="fas fa-bell"></i>
                            <span class="absolute -top-1 -right-1 w-3 h-3 bg-whatsapp rounded-full"></span>
                        </button>

                        <!-- Settings -->
                        <button class="p-2 text-gray-600 hover:text-gray-900">
                            <i class="fas fa-cog"></i>
                        </button>

                        <!-- Status Indicator -->
                        <div class="flex items-center gap-2 px-3 py-2 bg-green-50 rounded-lg border border-green-200">
                            <div class="w-2 h-2 bg-whatsapp rounded-full animate-pulse"></div>
                            <span class="text-sm font-medium text-green-800">Bot Active</span>
                        </div>
                        <div class="flex items-center gap-2 px-3 py-2 bg-red-50 rounded-lg border border-red-200">
                            <div class="nav-logout">
                                <a href="/whatsapp_sales/admin/logout.php" class="nav-item">
                                    <i class="bi bi-box-arrow-in-right"></i>
                                    <span>Logout</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <main class="flex-1 p-6 space-y-6 overflow-auto">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6">
                    <!-- Total Messages -->
                    <div class="bg-white p-6 rounded-lg border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-gray-600">Total Messages</h3>
                            <div class="bg-blue-500 p-2 rounded-lg">
                                <i class="fas fa-comment text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="text-2xl font-bold text-gray-900">12,459</div>
                        <div class="flex items-center mt-2">
                            <span class="text-sm font-medium text-green-600">+12.5%</span>
                            <span class="text-sm text-gray-500 ml-1">from last month</span>
                        </div>
                    </div>

                    <!-- Active Users -->
                    <div class="bg-white p-6 rounded-lg border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-gray-600">Active Users</h3>
                            <div class="bg-whatsapp p-2 rounded-lg">
                                <i class="fas fa-users text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="text-2xl font-bold text-gray-900">2,847</div>
                        <div class="flex items-center mt-2">
                            <span class="text-sm font-medium text-green-600">+8.2%</span>
                            <span class="text-sm text-gray-500 ml-1">from last month</span>
                        </div>
                    </div>

                    <!-- Bot Responses -->
                    <div class="bg-white p-6 rounded-lg border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-gray-600">Bot Responses</h3>
                            <div class="bg-purple-500 p-2 rounded-lg">
                                <i class="fas fa-robot text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="text-2xl font-bold text-gray-900">9,364</div>
                        <div class="flex items-center mt-2">
                            <span class="text-sm font-medium text-green-600">+15.3%</span>
                            <span class="text-sm text-gray-500 ml-1">from last month</span>
                        </div>
                    </div>

                    <!-- Response Rate -->
                    <div class="bg-white p-6 rounded-lg border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-gray-600">Response Rate</h3>
                            <div class="bg-orange-500 p-2 rounded-lg">
                                <i class="fas fa-chart-line text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="text-2xl font-bold text-gray-900">94.2%</div>
                        <div class="flex items-center mt-2">
                            <span class="text-sm font-medium text-green-600">+2.1%</span>
                            <span class="text-sm text-gray-500 ml-1">from last month</span>
                        </div>
                    </div>

                    <!-- Avg Response Time -->
                    <div class="bg-white p-6 rounded-lg border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-gray-600">Avg Response Time</h3>
                            <div class="bg-red-500 p-2 rounded-lg">
                                <i class="fas fa-clock text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="text-2xl font-bold text-gray-900">1.2s</div>
                        <div class="flex items-center mt-2">
                            <span class="text-sm font-medium text-green-600">-0.3s</span>
                            <span class="text-sm text-gray-500 ml-1">from last month</span>
                        </div>
                    </div>

                    <!-- Delivered Messages -->
                    <div class="bg-white p-6 rounded-lg border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-gray-600">Delivered Messages</h3>
                            <div class="bg-green-500 p-2 rounded-lg">
                                <i class="fas fa-check-double text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="text-2xl font-bold text-gray-900">11,892</div>
                        <div class="flex items-center mt-2">
                            <span class="text-sm font-medium text-green-600">+10.1%</span>
                            <span class="text-sm text-gray-500 ml-1">from last month</span>
                        </div>
                    </div>
                </div>

                <!-- Analytics Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Message Activity Bar Chart -->
                    <div class="lg:col-span-2 bg-white p-6 rounded-lg border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Message Activity (Last 7 Days)</h3>

                        <!-- Legend -->
                        <div class="flex items-center gap-6 mb-6">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-whatsapp rounded"></div>
                                <span class="text-sm text-gray-600">Messages Received</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-whatsapp-dark rounded"></div>
                                <span class="text-sm text-gray-600">Bot Responses</span>
                            </div>
                        </div>

                        <!-- Bar Chart -->
                        <div class="grid grid-cols-7 gap-4 h-64">
                            <div class="flex flex-col items-center justify-end h-full">
                                <div class="flex flex-col items-center justify-end h-48 w-full gap-1">
                                    <div class="chart-bar w-6 bg-whatsapp rounded-t" style="block-size: 67%;"
                                        title="Messages: 1200"></div>
                                    <div class="chart-bar w-6 bg-whatsapp-dark rounded-t" style="block-size: 63%;"
                                        title="Responses: 1140"></div>
                                </div>
                                <span class="text-xs text-gray-600 mt-2">Mon</span>
                            </div>
                            <div class="flex flex-col items-center justify-end h-full">
                                <div class="flex flex-col items-center justify-end h-48 w-full gap-1">
                                    <div class="chart-bar w-6 bg-whatsapp rounded-t" style="block-size: 78%;"
                                        title="Messages: 1400"></div>
                                    <div class="chart-bar w-6 bg-whatsapp-dark rounded-t" style="block-size: 73%;"
                                        title="Responses: 1320"></div>
                                </div>
                                <span class="text-xs text-gray-600 mt-2">Tue</span>
                            </div>
                            <div class="flex flex-col items-center justify-end h-full">
                                <div class="flex flex-col items-center justify-end h-48 w-full gap-1">
                                    <div class="chart-bar w-6 bg-whatsapp rounded-t" style="block-size: 61%;"
                                        title="Messages: 1100"></div>
                                    <div class="chart-bar w-6 bg-whatsapp-dark rounded-t" style="block-size: 58%;"
                                        title="Responses: 1050"></div>
                                </div>
                                <span class="text-xs text-gray-600 mt-2">Wed</span>
                            </div>
                            <div class="flex flex-col items-center justify-end h-full">
                                <div class="flex flex-col items-center justify-end h-48 w-full gap-1">
                                    <div class="chart-bar w-6 bg-whatsapp rounded-t" style="block-size: 89%;"
                                        title="Messages: 1600"></div>
                                    <div class="chart-bar w-6 bg-whatsapp-dark rounded-t" style="block-size: 84%;"
                                        title="Responses: 1520"></div>
                                </div>
                                <span class="text-xs text-gray-600 mt-2">Thu</span>
                            </div>
                            <div class="flex flex-col items-center justify-end h-full">
                                <div class="flex flex-col items-center justify-end h-48 w-full gap-1">
                                    <div class="chart-bar w-6 bg-whatsapp rounded-t" style="block-size: 100%;"
                                        title="Messages: 1800"></div>
                                    <div class="chart-bar w-6 bg-whatsapp-dark rounded-t" style="block-size: 95%;"
                                        title="Responses: 1710"></div>
                                </div>
                                <span class="text-xs text-gray-600 mt-2">Fri</span>
                            </div>
                            <div class="flex flex-col items-center justify-end h-full">
                                <div class="flex flex-col items-center justify-end h-48 w-full gap-1">
                                    <div class="chart-bar w-6 bg-whatsapp rounded-t" style="block-size: 50%;"
                                        title="Messages: 900"></div>
                                    <div class="chart-bar w-6 bg-whatsapp-dark rounded-t" style="block-size: 48%;"
                                        title="Responses: 860"></div>
                                </div>
                                <span class="text-xs text-gray-600 mt-2">Sat</span>
                            </div>
                            <div class="flex flex-col items-center justify-end h-full">
                                <div class="flex flex-col items-center justify-end h-48 w-full gap-1">
                                    <div class="chart-bar w-6 bg-whatsapp rounded-t" style="block-size: 44%;"
                                        title="Messages: 800"></div>
                                    <div class="chart-bar w-6 bg-whatsapp-dark rounded-t" style="block-size: 42%;"
                                        title="Responses: 750"></div>
                                </div>
                                <span class="text-xs text-gray-600 mt-2">Sun</span>
                            </div>
                        </div>
                    </div>

                    <!-- Hourly Distribution -->
                    <div class="bg-white p-6 rounded-lg border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Hourly Message Distribution</h3>
                        <div class="h-64 relative">
                            <svg width="100%" height="200" class="overflow-visible">
                                <!-- Grid lines -->
                                <line x1="0" y1="0" x2="100%" y2="0" stroke="#f0f0f0" stroke-width="1" />
                                <line x1="0" y1="50" x2="100%" y2="50" stroke="#f0f0f0" stroke-width="1" />
                                <line x1="0" y1="100" x2="100%" y2="100" stroke="#f0f0f0" stroke-width="1" />
                                <line x1="0" y1="150" x2="100%" y2="150" stroke="#f0f0f0" stroke-width="1" />
                                <line x1="0" y1="200" x2="100%" y2="200" stroke="#f0f0f0" stroke-width="1" />

                                <!-- Line path -->
                                <path
                                    d="M 0% 164 L 14.3% 191 L 28.6% 183 L 42.9% 88 L 57.1% 31 L 71.4% 64 L 85.7% 0 L 100% 79"
                                    fill="none" stroke="#25D366" stroke-width="3" />

                                <!-- Data points -->
                                <circle cx="0%" cy="164" r="4" fill="#25D366" stroke="white" stroke-width="2" />
                                <circle cx="14.3%" cy="191" r="4" fill="#25D366" stroke="white" stroke-width="2" />
                                <circle cx="28.6%" cy="183" r="4" fill="#25D366" stroke="white" stroke-width="2" />
                                <circle cx="42.9%" cy="88" r="4" fill="#25D366" stroke="white" stroke-width="2" />
                                <circle cx="57.1%" cy="31" r="4" fill="#25D366" stroke="white" stroke-width="2" />
                                <circle cx="71.4%" cy="64" r="4" fill="#25D366" stroke="white" stroke-width="2" />
                                <circle cx="85.7%" cy="0" r="4" fill="#25D366" stroke="white" stroke-width="2" />
                                <circle cx="100%" cy="79" r="4" fill="#25D366" stroke="white" stroke-width="2" />
                            </svg>

                            <!-- X-axis labels -->
                            <div class="flex justify-between mt-2 text-xs text-gray-600">
                                <span>00:00</span>
                                <span>03:00</span>
                                <span>06:00</span>
                                <span>09:00</span>
                                <span>12:00</span>
                                <span>15:00</span>
                                <span>18:00</span>
                                <span>21:00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Message Categories -->
                    <div class="bg-white p-6 rounded-lg border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Message Categories</h3>
                        <div class="flex items-center justify-center mb-6">
                            <div class="relative w-48 h-48">
                                <svg width="192" height="192" class="transform -rotate-90">
                                    <circle cx="96" cy="96" r="80" fill="none" stroke="#f3f4f6" stroke-width="16" />
                                    <!-- Customer Support - 35% -->
                                    <circle cx="96" cy="96" r="80" fill="none" stroke="#25D366" stroke-width="16"
                                        stroke-dasharray="175.84 326.72" stroke-dashoffset="0" />
                                    <!-- Sales Inquiries - 25% -->
                                    <circle cx="96" cy="96" r="80" fill="none" stroke="#128C7E" stroke-width="16"
                                        stroke-dasharray="125.6 376.96" stroke-dashoffset="-175.84" />
                                    <!-- General Info - 20% -->
                                    <circle cx="96" cy="96" r="80" fill="none" stroke="#34D399" stroke-width="16"
                                        stroke-dasharray="100.48 401.08" stroke-dashoffset="-301.44" />
                                    <!-- Technical Issues - 15% -->
                                    <circle cx="96" cy="96" r="80" fill="none" stroke="#10B981" stroke-width="16"
                                        stroke-dasharray="75.36 426.2" stroke-dashoffset="-401.92" />
                                    <!-- Other - 5% -->
                                    <circle cx="96" cy="96" r="80" fill="none" stroke="#6EE7B7" stroke-width="16"
                                        stroke-dasharray="25.12 476.44" stroke-dashoffset="-477.28" />
                                </svg>

                                <!-- Center text -->
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-gray-900">100%</div>
                                        <div class="text-sm text-gray-500">Total</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Legend -->
                        <div class="space-y-2">
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded-full bg-whatsapp"></div>
                                <span class="text-sm text-gray-600 flex-1">Customer Support</span>
                                <span class="text-sm font-medium text-gray-900">35%</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded-full bg-whatsapp-dark"></div>
                                <span class="text-sm text-gray-600 flex-1">Sales Inquiries</span>
                                <span class="text-sm font-medium text-gray-900">25%</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded-full bg-green-400"></div>
                                <span class="text-sm text-gray-600 flex-1">General Info</span>
                                <span class="text-sm font-medium text-gray-900">20%</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                <span class="text-sm text-gray-600 flex-1">Technical Issues</span>
                                <span class="text-sm font-medium text-gray-900">15%</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded-full bg-green-300"></div>
                                <span class="text-sm text-gray-600 flex-1">Other</span>
                                <span class="text-sm font-medium text-gray-900">5%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Messages and Bot Performance -->
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    <!-- Recent Messages -->
                    <div class="xl:col-span-2 bg-white rounded-lg border border-gray-200">
                        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <i class="fas fa-comment text-whatsapp"></i>
                                Recent Messages
                            </h3>
                            <button
                                class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                                View All
                            </button>
                        </div>
                        <div class="p-6 space-y-4">
                            <!-- Message 1 -->
                            <div class="flex items-start gap-4 p-4 border border-gray-100 rounded-lg hover:bg-gray-50">
                                <div
                                    class="w-10 h-10 bg-whatsapp rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-user text-white text-sm"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-medium text-gray-900">John Doe</h4>
                                            <span
                                                class="text-xs px-2 py-1 rounded-full text-blue-600 bg-blue-50">support</span>
                                        </div>
                                        <span class="text-xs text-gray-500 flex items-center gap-1">
                                            <i class="fas fa-clock"></i>
                                            2 min ago
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2">+1 234 567 8901</p>
                                    <p class="text-sm text-gray-800 mb-3">Hi, I need help with my order #12345</p>
                                    <div class="flex items-center justify-between">
                                        <span class="bg-whatsapp text-white px-3 py-1 rounded-full text-xs font-medium">
                                            <i class="fas fa-check-double mr-1"></i>
                                            Responded
                                        </span>
                                        <button class="p-1 text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Message 2 -->
                            <div class="flex items-start gap-4 p-4 border border-gray-100 rounded-lg hover:bg-gray-50">
                                <div
                                    class="w-10 h-10 bg-whatsapp rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-user text-white text-sm"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-medium text-gray-900">Sarah Wilson</h4>
                                            <span
                                                class="text-xs px-2 py-1 rounded-full text-purple-600 bg-purple-50">info</span>
                                        </div>
                                        <span class="text-xs text-gray-500 flex items-center gap-1">
                                            <i class="fas fa-clock"></i>
                                            5 min ago
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2">+1 234 567 8902</p>
                                    <p class="text-sm text-gray-800 mb-3">What are your business hours?</p>
                                    <div class="flex items-center justify-between">
                                        <span
                                            class="bg-orange-100 text-orange-800 px-3 py-1 rounded-full text-xs font-medium">
                                            <i class="fas fa-check-double mr-1"></i>
                                            Pending
                                        </span>
                                        <button class="p-1 text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Message 3 -->
                            <div class="flex items-start gap-4 p-4 border border-gray-100 rounded-lg hover:bg-gray-50">
                                <div
                                    class="w-10 h-10 bg-whatsapp rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-user text-white text-sm"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-medium text-gray-900">Mike Johnson</h4>
                                            <span
                                                class="text-xs px-2 py-1 rounded-full text-green-600 bg-green-50">sales</span>
                                        </div>
                                        <span class="text-xs text-gray-500 flex items-center gap-1">
                                            <i class="fas fa-clock"></i>
                                            8 min ago
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2">+1 234 567 8903</p>
                                    <p class="text-sm text-gray-800 mb-3">I'm interested in your premium package</p>
                                    <div class="flex items-center justify-between">
                                        <span class="bg-whatsapp text-white px-3 py-1 rounded-full text-xs font-medium">
                                            <i class="fas fa-check-double mr-1"></i>
                                            Responded
                                        </span>
                                        <button class="p-1 text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bot Performance -->
                    <div class="space-y-6">
                        <div class="bg-white p-6 rounded-lg border border-gray-200">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-semibold text-gray-900">Bot Performance</h3>
                                <div class="w-3 h-3 bg-whatsapp rounded-full animate-pulse"></div>
                            </div>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Uptime</span>
                                    <span class="text-sm font-medium">99.9%</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Avg Response</span>
                                    <span class="text-sm font-medium">1.2s</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Success Rate</span>
                                    <span class="text-sm font-medium">94.2%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-4">
                                    <div class="bg-whatsapp h-2 rounded-full" style="inline-size: 94.2%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bot Configuration -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Bot Settings -->
                    <div class="bg-white rounded-lg border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <i class="fas fa-robot text-whatsapp"></i>
                                Bot Configuration
                            </h3>
                        </div>
                        <div class="p-6 space-y-6">
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Bot Name</label>
                                <input type="text" value="BotWave Assistant"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Welcome Message</label>
                                <textarea rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">Hi! 👋 I'm your virtual assistant. How can I help you today?</textarea>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Fallback Message</label>
                                <textarea rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">I'm sorry, I didn't understand that. Let me connect you with a human agent.</textarea>
                            </div>

                            <div
                                class="flex items-center justify-between p-4 bg-green-50 rounded-lg border border-green-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-whatsapp rounded-lg flex items-center justify-center">
                                        <i class="fas fa-bolt text-white"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">Bot Status</p>
                                        <p class="text-sm text-gray-600">Currently active and responding</p>
                                    </div>
                                </div>
                                <span
                                    class="bg-whatsapp text-white px-3 py-1 rounded-full text-sm font-medium">Active</span>
                            </div>
                        </div>
                    </div>

                    <!-- Features Toggle -->
                    <div class="bg-white rounded-lg border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <i class="fas fa-cog text-whatsapp"></i>
                                Features & Integrations
                            </h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <!-- Auto-Reply -->
                            <div class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h4 class="font-medium text-gray-900">Auto-Reply</h4>
                                        <span
                                            class="bg-whatsapp text-white px-2 py-1 rounded-full text-xs font-medium">ON</span>
                                    </div>
                                    <p class="text-sm text-gray-600">Automatic responses to common queries</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" checked class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-whatsapp">
                                    </div>
                                </label>
                            </div>

                            <!-- Smart Routing -->
                            <div class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h4 class="font-medium text-gray-900">Smart Routing</h4>
                                        <span
                                            class="bg-whatsapp text-white px-2 py-1 rounded-full text-xs font-medium">ON</span>
                                    </div>
                                    <p class="text-sm text-gray-600">Route messages to appropriate departments</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" checked class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-whatsapp">
                                    </div>
                                </label>
                            </div>

                            <!-- Language Detection -->
                            <div class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h4 class="font-medium text-gray-900">Language Detection</h4>
                                        <span
                                            class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs font-medium">OFF</span>
                                    </div>
                                    <p class="text-sm text-gray-600">Detect and respond in user's language</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-whatsapp">
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="lg:col-span-2 bg-white rounded-lg border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <button
                                    class="h-20 flex flex-col items-center justify-center gap-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                    <i class="fas fa-paper-plane text-whatsapp text-xl"></i>
                                    <span class="text-sm">Broadcast Message</span>
                                </button>
                                <button
                                    class="h-20 flex flex-col items-center justify-center gap-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                    <i class="fas fa-clock text-whatsapp text-xl"></i>
                                    <span class="text-sm">Schedule Message</span>
                                </button>
                                <button
                                    class="h-20 flex flex-col items-center justify-center gap-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                    <i class="fas fa-robot text-whatsapp text-xl"></i>
                                    <span class="text-sm">Test Bot</span>
                                </button>
                                <button
                                    class="h-20 flex flex-col items-center justify-center gap-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                    <i class="fas fa-globe text-whatsapp text-xl"></i>
                                    <span class="text-sm">Webhook Settings</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Sidebar navigation functionality
        document.querySelectorAll('.sidebar-item').forEach(item => {
            item.addEventListener('click', function () {
                // Remove active class from all items
                document.querySelectorAll('.sidebar-item').forEach(i => i.classList.remove('active'));
                // Add active class to clicked item
                this.classList.add('active');
            });
        });

        // Toggle switches functionality
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                const badge = this.closest('.flex').querySelector('span');
                if (badge) {
                    if (this.checked) {
                        badge.textContent = 'ON';
                        badge.className = 'bg-whatsapp text-white px-2 py-1 rounded-full text-xs font-medium';
                    } else {
                        badge.textContent = 'OFF';
                        badge.className = 'bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs font-medium';
                    }
                }
            });
        });

        // Search functionality
        document.querySelector('input[placeholder="Search messages, contacts..."]').addEventListener('input', function () {
            console.log('Searching for:', this.value);
            // Add search functionality here
        });

        // Quick action buttons
        document.querySelectorAll('.grid button').forEach(button => {
            button.addEventListener('click', function () {
                const action = this.querySelector('span').textContent;
                console.log('Action clicked:', action);
                // Add action functionality here
            });
        });
    </script>
</body>

</html>