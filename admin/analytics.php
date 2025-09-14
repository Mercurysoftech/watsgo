<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsBot Analytics - Admin Dashboard</title>
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
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }
        .sidebar-item:hover {
            background-color: #f9fafb;
        }
        .sidebar-item.active {
            background-color: #25D366;
            color: white;
        }
        .analytics-section {
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
        }
        .analytics-section.active {
            opacity: 1;
            transform: translateY(0);
        }
        .toggle-switch {
            position: relative;
            inline-size: 44px;
            block-size: 24px;
            background-color: #cbd5e1;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .toggle-switch.active {
            background-color: #25D366;
        }
        .toggle-switch::after {
            content: '';
            position: absolute;
            inset-block-start: 2px;
            inset-inline-start: 2px;
            inline-size: 20px;
            block-size: 20px;
            background-color: white;
            border-radius: 50%;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .toggle-switch.active::after {
            transform: translateX(20px);
        }
        .metric-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .metric-card.selected {
            border-color: #25D366;
            background-color: #f0fdf4;
        }
        .chart-preview {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 50%, #bbf7d0 100%);
            min-block-size: 120px;
            border-radius: 8px;
            position: relative;
            overflow: hidden;
        }
        .chart-preview::before {
            content: '';
            position: absolute;
            inset-block-start: 50%;
            inset-inline-start: 50%;
            transform: translate(-50%, -50%);
            inline-size: 80%;
            block-size: 2px;
            background: #25D366;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
        }
        .widget-placeholder {
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
        }
        .widget-placeholder:hover {
            border-color: #25D366;
            background-color: #f9fafb;
        }
        .color-picker {
            inline-size: 40px;
            block-size: 40px;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .color-picker:hover {
            border-color: #25D366;
            transform: scale(1.1);
        }
        .frequency-selector {
            display: flex;
            background: #f3f4f6;
            border-radius: 8px;
            padding: 4px;
        }
        .frequency-option {
            flex: 1;
            padding: 8px 12px;
            text-align: center;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .frequency-option.active {
            background: #25D366;
            color: white;
        }
        .progress-ring {
            transform: rotate(-90deg);
        }
        .progress-ring circle {
            fill: none;
            stroke-width: 8;
            stroke-linecap: round;
        }
        .export-format {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .export-format:hover {
            border-color: #25D366;
            background-color: #f0fdf4;
        }
        .export-format.selected {
            border-color: #25D366;
            background-color: #f0fdf4;
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
                            <h1 class="text-xl font-semibold text-gray-900">Analytics Configuration</h1>
                            <p class="text-sm text-gray-500">Customize your analytics dashboard and reports</p>
                        </div>
                    </div>

                    <!-- Right Section -->
                    <div class="flex items-center gap-4">
                        <!-- Export Button -->
                        <button class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors" id="exportData">
                            <i class="fas fa-download mr-2"></i>
                            Export Data
                        </button>

                        <!-- Save Button -->
                        <button class="px-4 py-2 bg-whatsapp text-white rounded-lg hover:bg-whatsapp-dark transition-colors" id="saveAnalytics">
                            <i class="fas fa-save mr-2"></i>
                            Save Changes
                        </button>

                        <!-- Data Status -->
                        <div class="flex items-center gap-2 px-3 py-2 bg-green-50 rounded-lg border border-green-200">
                            <div class="w-2 h-2 bg-whatsapp rounded-full animate-pulse"></div>
                            <span class="text-sm font-medium text-green-800">Live Data</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Analytics Content -->
            <div class="flex-1 flex">
                <!-- Analytics Sidebar -->
                <div class="w-64 bg-white border-r border-gray-200 overflow-y-auto">
                    <div class="p-4">
                        <h3 class="font-medium text-gray-900 mb-4">Analytics Settings</h3>
                        <nav class="space-y-2">
                            <button class="analytics-nav-item active w-full text-left px-3 py-2 rounded-lg text-sm font-medium text-whatsapp bg-green-50" data-section="dashboard">
                                <i class="fas fa-tachometer-alt mr-2"></i>
                                Dashboard
                            </button>
                            <button class="analytics-nav-item w-full text-left px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50" data-section="metrics">
                                <i class="fas fa-chart-line mr-2"></i>
                                Key Metrics
                            </button>
                            <button class="analytics-nav-item w-full text-left px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50" data-section="reports">
                                <i class="fas fa-file-chart mr-2"></i>
                                Reports
                            </button>
                            <button class="analytics-nav-item w-full text-left px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50" data-section="alerts">
                                <i class="fas fa-bell mr-2"></i>
                                Alerts
                            </button>
                            <button class="analytics-nav-item w-full text-left px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50" data-section="integrations">
                                <i class="fas fa-plug mr-2"></i>
                                Integrations
                            </button>
                            <button class="analytics-nav-item w-full text-left px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50" data-section="visualization">
                                <i class="fas fa-palette mr-2"></i>
                                Visualization
                            </button>
                            <button class="analytics-nav-item w-full text-left px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50" data-section="export">
                                <i class="fas fa-download mr-2"></i>
                                Export Settings
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Main Analytics Area -->
                <div class="flex-1 overflow-y-auto">
                    <!-- Dashboard Configuration -->
                    <div class="analytics-section active p-6" id="dashboard">
                        <div class="max-w-6xl mx-auto space-y-6">
                            <!-- Dashboard Layout -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                        <i class="fas fa-layout text-whatsapp"></i>
                                        Dashboard Layout
                                    </h2>
                                </div>
                                <div class="p-6">
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                        <div>
                                            <h3 class="font-medium text-gray-900 mb-4">Available Widgets</h3>
                                            <div class="space-y-3">
                                                <div class="metric-card p-4 border border-gray-200 rounded-lg" draggable="true" data-widget="message-volume">
                                                    <div class="flex items-center gap-3">
                                                        <i class="fas fa-chart-bar text-whatsapp"></i>
                                                        <div>
                                                            <h4 class="font-medium text-gray-900">Message Volume</h4>
                                                            <p class="text-sm text-gray-600">Track incoming and outgoing messages</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="metric-card p-4 border border-gray-200 rounded-lg" draggable="true" data-widget="response-time">
                                                    <div class="flex items-center gap-3">
                                                        <i class="fas fa-clock text-blue-500"></i>
                                                        <div>
                                                            <h4 class="font-medium text-gray-900">Response Time</h4>
                                                            <p class="text-sm text-gray-600">Average bot response time</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="metric-card p-4 border border-gray-200 rounded-lg" draggable="true" data-widget="user-satisfaction">
                                                    <div class="flex items-center gap-3">
                                                        <i class="fas fa-heart text-red-500"></i>
                                                        <div>
                                                            <h4 class="font-medium text-gray-900">User Satisfaction</h4>
                                                            <p class="text-sm text-gray-600">Customer satisfaction scores</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="metric-card p-4 border border-gray-200 rounded-lg" draggable="true" data-widget="conversion-rate">
                                                    <div class="flex items-center gap-3">
                                                        <i class="fas fa-percentage text-purple-500"></i>
                                                        <div>
                                                            <h4 class="font-medium text-gray-900">Conversion Rate</h4>
                                                            <p class="text-sm text-gray-600">Lead to customer conversion</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <h3 class="font-medium text-gray-900 mb-4">Dashboard Preview</h3>
                                            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 min-h-[400px]">
                                                <div class="dashboard-grid">
                                                    <div class="widget-placeholder" id="dropZone1">
                                                        <i class="fas fa-plus text-gray-400 text-2xl mb-2"></i>
                                                        <p class="text-gray-500">Drop widget here</p>
                                                    </div>
                                                    <div class="widget-placeholder" id="dropZone2">
                                                        <i class="fas fa-plus text-gray-400 text-2xl mb-2"></i>
                                                        <p class="text-gray-500">Drop widget here</p>
                                                    </div>
                                                    <div class="widget-placeholder" id="dropZone3">
                                                        <i class="fas fa-plus text-gray-400 text-2xl mb-2"></i>
                                                        <p class="text-gray-500">Drop widget here</p>
                                                    </div>
                                                    <div class="widget-placeholder" id="dropZone4">
                                                        <i class="fas fa-plus text-gray-400 text-2xl mb-2"></i>
                                                        <p class="text-gray-500">Drop widget here</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Stats -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <div class="bg-white p-6 rounded-lg border border-gray-200">
                                    <div class="text-2xl font-bold text-gray-900">8</div>
                                    <div class="text-sm text-gray-500">Active Widgets</div>
                                </div>
                                <div class="bg-white p-6 rounded-lg border border-gray-200">
                                    <div class="text-2xl font-bold text-gray-900">24hrs</div>
                                    <div class="text-sm text-gray-500">Data Refresh</div>
                                </div>
                                <div class="bg-white p-6 rounded-lg border border-gray-200">
                                    <div class="text-2xl font-bold text-gray-900">5</div>
                                    <div class="text-sm text-gray-500">Scheduled Reports</div>
                                </div>
                                <div class="bg-white p-6 rounded-lg border border-gray-200">
                                    <div class="text-2xl font-bold text-gray-900">99.9%</div>
                                    <div class="text-sm text-gray-500">Data Accuracy</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Key Metrics -->
                    <div class="analytics-section p-6" id="metrics">
                        <div class="max-w-6xl mx-auto space-y-6">
                            <h2 class="text-2xl font-semibold text-gray-900">Key Performance Indicators</h2>

                            <!-- KPI Configuration -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Primary KPIs -->
                                <div class="bg-white rounded-lg border border-gray-200">
                                    <div class="p-6 border-b border-gray-200">
                                        <h3 class="font-medium text-gray-900">Primary KPIs</h3>
                                    </div>
                                    <div class="p-6 space-y-4">
                                        <div class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                            <div>
                                                <h4 class="font-medium text-gray-900">Message Volume</h4>
                                                <p class="text-sm text-gray-600">Track daily message count</p>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <div class="toggle-switch active" data-kpi="message-volume"></div>
                                                <button class="text-gray-400 hover:text-gray-600">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                            <div>
                                                <h4 class="font-medium text-gray-900">Response Rate</h4>
                                                <p class="text-sm text-gray-600">Bot response success rate</p>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <div class="toggle-switch active" data-kpi="response-rate"></div>
                                                <button class="text-gray-400 hover:text-gray-600">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                            <div>
                                                <h4 class="font-medium text-gray-900">User Engagement</h4>
                                                <p class="text-sm text-gray-600">Active user interactions</p>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <div class="toggle-switch active" data-kpi="user-engagement"></div>
                                                <button class="text-gray-400 hover:text-gray-600">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Secondary KPIs -->
                                <div class="bg-white rounded-lg border border-gray-200">
                                    <div class="p-6 border-b border-gray-200">
                                        <h3 class="font-medium text-gray-900">Secondary KPIs</h3>
                                    </div>
                                    <div class="p-6 space-y-4">
                                        <div class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                            <div>
                                                <h4 class="font-medium text-gray-900">Conversion Rate</h4>
                                                <p class="text-sm text-gray-600">Lead conversion percentage</p>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <div class="toggle-switch" data-kpi="conversion-rate"></div>
                                                <button class="text-gray-400 hover:text-gray-600">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                            <div>
                                                <h4 class="font-medium text-gray-900">Error Rate</h4>
                                                <p class="text-sm text-gray-600">System error frequency</p>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <div class="toggle-switch active" data-kpi="error-rate"></div>
                                                <button class="text-gray-400 hover:text-gray-600">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                            <div>
                                                <h4 class="font-medium text-gray-900">Peak Hours</h4>
                                                <p class="text-sm text-gray-600">High activity time periods</p>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <div class="toggle-switch" data-kpi="peak-hours"></div>
                                                <button class="text-gray-400 hover:text-gray-600">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- KPI Targets -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h3 class="font-medium text-gray-900">Performance Targets</h3>
                                </div>
                                <div class="p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div class="text-center">
                                            <div class="relative inline-flex items-center justify-center">
                                                <svg class="progress-ring w-24 h-24">
                                                    <circle cx="48" cy="48" r="40" stroke="#e5e7eb" stroke-width="8"/>
                                                    <circle cx="48" cy="48" r="40" stroke="#25D366" stroke-width="8" 
                                                            stroke-dasharray="251.2" stroke-dashoffset="62.8"/>
                                                </svg>
                                                <div class="absolute text-center">
                                                    <div class="text-lg font-bold text-gray-900">75%</div>
                                                    <div class="text-xs text-gray-500">Target</div>
                                                </div>
                                            </div>
                                            <h4 class="font-medium text-gray-900 mt-3">Response Rate</h4>
                                            <p class="text-sm text-gray-600">Current: 94.2%</p>
                                        </div>
                                        <div class="text-center">
                                            <div class="relative inline-flex items-center justify-center">
                                                <svg class="progress-ring w-24 h-24">
                                                    <circle cx="48" cy="48" r="40" stroke="#e5e7eb" stroke-width="8"/>
                                                    <circle cx="48" cy="48" r="40" stroke="#3b82f6" stroke-width="8" 
                                                            stroke-dasharray="251.2" stroke-dashoffset="125.6"/>
                                                </svg>
                                                <div class="absolute text-center">
                                                    <div class="text-lg font-bold text-gray-900">50%</div>
                                                    <div class="text-xs text-gray-500">Target</div>
                                                </div>
                                            </div>
                                            <h4 class="font-medium text-gray-900 mt-3">User Satisfaction</h4>
                                            <p class="text-sm text-gray-600">Current: 4.2/5</p>
                                        </div>
                                        <div class="text-center">
                                            <div class="relative inline-flex items-center justify-center">
                                                <svg class="progress-ring w-24 h-24">
                                                    <circle cx="48" cy="48" r="40" stroke="#e5e7eb" stroke-width="8"/>
                                                    <circle cx="48" cy="48" r="40" stroke="#8b5cf6" stroke-width="8" 
                                                            stroke-dasharray="251.2" stroke-dashoffset="188.4"/>
                                                </svg>
                                                <div class="absolute text-center">
                                                    <div class="text-lg font-bold text-gray-900">25%</div>
                                                    <div class="text-xs text-gray-500">Target</div>
                                                </div>
                                            </div>
                                            <h4 class="font-medium text-gray-900 mt-3">Conversion Rate</h4>
                                            <p class="text-sm text-gray-600">Current: 18.5%</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reports -->
                    <div class="analytics-section p-6" id="reports">
                        <div class="max-w-6xl mx-auto space-y-6">
                            <div class="flex items-center justify-between">
                                <h2 class="text-2xl font-semibold text-gray-900">Report Configuration</h2>
                                <button class="px-4 py-2 bg-whatsapp text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                                    <i class="fas fa-plus mr-2"></i>
                                    Create Report
                                </button>
                            </div>

                            <!-- Scheduled Reports -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h3 class="font-medium text-gray-900">Scheduled Reports</h3>
                                </div>
                                <div class="divide-y divide-gray-200">
                                    <div class="p-6 flex items-center justify-between">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Daily Performance Report</h4>
                                            <p class="text-sm text-gray-600">Sent every day at 9:00 AM</p>
                                            <div class="flex items-center gap-2 mt-2">
                                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">Active</span>
                                                <span class="text-sm text-gray-500">• Last sent 2 hours ago</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button class="p-2 text-gray-400 hover:text-gray-600">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <div class="toggle-switch active"></div>
                                        </div>
                                    </div>
                                    <div class="p-6 flex items-center justify-between">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Weekly Analytics Summary</h4>
                                            <p class="text-sm text-gray-600">Sent every Monday at 8:00 AM</p>
                                            <div class="flex items-center gap-2 mt-2">
                                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">Active</span>
                                                <span class="text-sm text-gray-500">• Last sent 3 days ago</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button class="p-2 text-gray-400 hover:text-gray-600">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <div class="toggle-switch active"></div>
                                        </div>
                                    </div>
                                    <div class="p-6 flex items-center justify-between">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Monthly Executive Dashboard</h4>
                                            <p class="text-sm text-gray-600">Sent first day of each month</p>
                                            <div class="flex items-center gap-2 mt-2">
                                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">Paused</span>
                                                <span class="text-sm text-gray-500">• Last sent 1 month ago</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button class="p-2 text-gray-400 hover:text-gray-600">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <div class="toggle-switch"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Report Builder -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h3 class="font-medium text-gray-900">Report Builder</h3>
                                </div>
                                <div class="p-6">
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                        <div>
                                            <div class="space-y-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Report Name</label>
                                                    <input type="text" placeholder="Enter report name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Frequency</label>
                                                    <div class="frequency-selector">
                                                        <div class="frequency-option active">Daily</div>
                                                        <div class="frequency-option">Weekly</div>
                                                        <div class="frequency-option">Monthly</div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Recipients</label>
                                                    <input type="email" placeholder="Enter email addresses" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Metrics to Include</label>
                                                    <div class="space-y-2">
                                                        <div class="flex items-center gap-2">
                                                            <input type="checkbox" checked class="rounded border-gray-300">
                                                            <span class="text-sm">Message Volume</span>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <input type="checkbox" checked class="rounded border-gray-300">
                                                            <span class="text-sm">Response Times</span>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <input type="checkbox" class="rounded border-gray-300">
                                                            <span class="text-sm">User Satisfaction</span>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <input type="checkbox" class="rounded border-gray-300">
                                                            <span class="text-sm">Error Rates</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Report Preview</label>
                                            <div class="border border-gray-300 rounded-lg p-4 bg-gray-50 min-h-[300px]">
                                                <div class="text-center text-gray-500 mt-20">
                                                    <i class="fas fa-chart-bar text-4xl mb-4"></i>
                                                    <p>Report preview will appear here</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex gap-4 mt-6">
                                        <button class="px-4 py-2 bg-whatsapp text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                                            Save Report
                                        </button>
                                        <button class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                            Preview Report
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alerts -->
                    <div class="analytics-section p-6" id="alerts">
                        <div class="max-w-6xl mx-auto space-y-6">
                            <div class="flex items-center justify-between">
                                <h2 class="text-2xl font-semibold text-gray-900">Alert Configuration</h2>
                                <button class="px-4 py-2 bg-whatsapp text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                                    <i class="fas fa-plus mr-2"></i>
                                    Add Alert
                                </button>
                            </div>

                            <!-- Alert Rules -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h3 class="font-medium text-gray-900">Active Alert Rules</h3>
                                </div>
                                <div class="divide-y divide-gray-200">
                                    <div class="p-6">
                                        <div class="flex items-center justify-between mb-4">
                                            <div>
                                                <h4 class="font-medium text-gray-900">High Response Time</h4>
                                                <p class="text-sm text-gray-600">Alert when average response time exceeds 5 seconds</p>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs">Critical</span>
                                                <div class="toggle-switch active"></div>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-3 gap-4 text-sm">
                                            <div>
                                                <span class="text-gray-500">Metric:</span>
                                                <span class="font-medium ml-2">Response Time</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Threshold:</span>
                                                <span class="font-medium ml-2">> 5 seconds</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Notification:</span>
                                                <span class="font-medium ml-2">Email, Slack</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-6">
                                        <div class="flex items-center justify-between mb-4">
                                            <div>
                                                <h4 class="font-medium text-gray-900">Low Message Volume</h4>
                                                <p class="text-sm text-gray-600">Alert when daily messages drop below 100</p>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">Warning</span>
                                                <div class="toggle-switch active"></div>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-3 gap-4 text-sm">
                                            <div>
                                                <span class="text-gray-500">Metric:</span>
                                                <span class="font-medium ml-2">Daily Messages</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Threshold:</span>
                                                <span class="font-medium ml-2">< 100</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Notification:</span>
                                                <span class="font-medium ml-2">Email</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-6">
                                        <div class="flex items-center justify-between mb-4">
                                            <div>
                                                <h4 class="font-medium text-gray-900">Bot Offline</h4>
                                                <p class="text-sm text-gray-600">Alert when bot is offline for more than 2 minutes</p>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs">Critical</span>
                                                <div class="toggle-switch active"></div>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-3 gap-4 text-sm">
                                            <div>
                                                <span class="text-gray-500">Metric:</span>
                                                <span class="font-medium ml-2">Bot Status</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Threshold:</span>
                                                <span class="font-medium ml-2">Offline > 2min</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Notification:</span>
                                                <span class="font-medium ml-2">SMS, Email, Slack</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notification Channels -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h3 class="font-medium text-gray-900">Notification Channels</h3>
                                </div>
                                <div class="p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div class="p-4 border border-gray-200 rounded-lg">
                                            <div class="flex items-center gap-3 mb-4">
                                                <i class="fas fa-envelope text-blue-500 text-xl"></i>
                                                <div>
                                                    <h4 class="font-medium text-gray-900">Email</h4>
                                                    <div class="flex items-center gap-2 mt-1">
                                                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                                        <span class="text-sm text-green-600">Connected</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="text-sm text-gray-600 mb-4">Send alerts via email notifications</p>
                                            <input type="email" placeholder="admin@company.com" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                        </div>
                                        <div class="p-4 border border-gray-200 rounded-lg">
                                            <div class="flex items-center gap-3 mb-4">
                                                <i class="fab fa-slack text-purple-500 text-xl"></i>
                                                <div>
                                                    <h4 class="font-medium text-gray-900">Slack</h4>
                                                    <div class="flex items-center gap-2 mt-1">
                                                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                                        <span class="text-sm text-green-600">Connected</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="text-sm text-gray-600 mb-4">Post alerts to Slack channels</p>
                                            <input type="text" placeholder="#alerts" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                        </div>
                                        <div class="p-4 border border-gray-200 rounded-lg">
                                            <div class="flex items-center gap-3 mb-4">
                                                <i class="fas fa-sms text-green-500 text-xl"></i>
                                                <div>
                                                    <h4 class="font-medium text-gray-900">SMS</h4>
                                                    <div class="flex items-center gap-2 mt-1">
                                                        <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                                        <span class="text-sm text-gray-600">Not Connected</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="text-sm text-gray-600 mb-4">Send critical alerts via SMS</p>
                                            <button class="w-full px-3 py-2 bg-whatsapp text-white rounded-lg text-sm hover:bg-whatsapp-dark transition-colors">
                                                Connect SMS
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Integrations -->
                    <div class="analytics-section p-6" id="integrations">
                        <div class="max-w-6xl mx-auto space-y-6">
                            <h2 class="text-2xl font-semibold text-gray-900">Analytics Integrations</h2>

                            <!-- Integration Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <!-- Google Analytics -->
                                <div class="bg-white rounded-lg border border-gray-200 p-6">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                                            <i class="fab fa-google text-white text-xl"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-medium text-gray-900">Google Analytics</h3>
                                            <div class="flex items-center gap-2 mt-1">
                                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                                <span class="text-sm text-green-600">Connected</span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-4">Track user behavior and conversion funnels</p>
                                    <button class="w-full px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                        Configure
                                    </button>
                                </div>

                                <!-- Mixpanel -->
                                <div class="bg-white rounded-lg border border-gray-200 p-6">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-chart-pie text-white text-xl"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-medium text-gray-900">Mixpanel</h3>
                                            <div class="flex items-center gap-2 mt-1">
                                                <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                                <span class="text-sm text-gray-600">Not Connected</span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-4">Advanced event tracking and user analytics</p>
                                    <button class="w-full px-4 py-2 bg-whatsapp text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                                        Connect
                                    </button>
                                </div>

                                <!-- Tableau -->
                                <div class="bg-white rounded-lg border border-gray-200 p-6">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-chart-bar text-white text-xl"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-medium text-gray-900">Tableau</h3>
                                            <div class="flex items-center gap-2 mt-1">
                                                <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                                <span class="text-sm text-gray-600">Not Connected</span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-4">Advanced data visualization and reporting</p>
                                    <button class="w-full px-4 py-2 bg-whatsapp text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                                        Connect
                                    </button>
                                </div>

                                <!-- Power BI -->
                                <div class="bg-white rounded-lg border border-gray-200 p-6">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
                                            <i class="fab fa-microsoft text-white text-xl"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-medium text-gray-900">Power BI</h3>
                                            <div class="flex items-center gap-2 mt-1">
                                                <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                                <span class="text-sm text-gray-600">Not Connected</span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-4">Microsoft business intelligence platform</p>
                                    <button class="w-full px-4 py-2 bg-whatsapp text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                                        Connect
                                    </button>
                                </div>

                                <!-- Custom API -->
                                <div class="bg-white rounded-lg border border-gray-200 p-6">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-12 h-12 bg-gray-600 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-code text-white text-xl"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-medium text-gray-900">Custom API</h3>
                                            <div class="flex items-center gap-2 mt-1">
                                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                                <span class="text-sm text-green-600">Connected</span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-4">Custom data export via REST API</p>
                                    <button class="w-full px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                        Configure
                                    </button>
                                </div>

                                <!-- Webhook -->
                                <div class="bg-white rounded-lg border border-gray-200 p-6">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-12 h-12 bg-indigo-500 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-link text-white text-xl"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-medium text-gray-900">Webhooks</h3>
                                            <div class="flex items-center gap-2 mt-1">
                                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                                <span class="text-sm text-green-600">3 Active</span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-4">Real-time data push to external systems</p>
                                    <button class="w-full px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                        Manage
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Visualization -->
                    <div class="analytics-section p-6" id="visualization">
                        <div class="max-w-6xl mx-auto space-y-6">
                            <h2 class="text-2xl font-semibold text-gray-900">Visualization Settings</h2>

                            <!-- Chart Themes -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h3 class="font-medium text-gray-900">Chart Themes</h3>
                                </div>
                                <div class="p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div class="border-2 border-whatsapp rounded-lg p-4 cursor-pointer">
                                            <div class="chart-preview mb-3"></div>
                                            <h4 class="font-medium text-gray-900">WhatsApp Green</h4>
                                            <p class="text-sm text-gray-600">Brand-consistent green theme</p>
                                        </div>
                                        <div class="border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-gray-300">
                                            <div class="chart-preview mb-3" style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 50%, #93c5fd 100%);"></div>
                                            <h4 class="font-medium text-gray-900">Professional Blue</h4>
                                            <p class="text-sm text-gray-600">Clean business theme</p>
                                        </div>
                                        <div class="border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-gray-300">
                                            <div class="chart-preview mb-3" style="background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 50%, #d1d5db 100%);"></div>
                                            <h4 class="font-medium text-gray-900">Minimal Gray</h4>
                                            <p class="text-sm text-gray-600">Simple monochrome theme</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Color Customization -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h3 class="font-medium text-gray-900">Color Customization</h3>
                                </div>
                                <div class="p-6">
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Primary Color</label>
                                            <div class="color-picker" style="background-color: #25D366;"></div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Secondary Color</label>
                                            <div class="color-picker" style="background-color: #128C7E;"></div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Accent Color</label>
                                            <div class="color-picker" style="background-color: #3b82f6;"></div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Warning Color</label>
                                            <div class="color-picker" style="background-color: #f59e0b;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Display Options -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h3 class="font-medium text-gray-900">Display Options</h3>
                                </div>
                                <div class="p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="space-y-4">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-medium text-gray-700">Dark Mode</span>
                                                <div class="toggle-switch"></div>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-medium text-gray-700">Animated Charts</span>
                                                <div class="toggle-switch active"></div>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-medium text-gray-700">Show Grid Lines</span>
                                                <div class="toggle-switch active"></div>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-medium text-gray-700">Data Labels</span>
                                                <div class="toggle-switch"></div>
                                            </div>
                                        </div>
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Chart Height</label>
                                                <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                                    <option>Small (200px)</option>
                                                    <option selected>Medium (300px)</option>
                                                    <option>Large (400px)</option>
                                                    <option>Extra Large (500px)</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Animation Speed</label>
                                                <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                                    <option>Slow (2s)</option>
                                                    <option selected>Medium (1s)</option>
                                                    <option>Fast (0.5s)</option>
                                                    <option>Instant (0s)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Export Settings -->
                    <div class="analytics-section p-6" id="export">
                        <div class="max-w-6xl mx-auto space-y-6">
                            <h2 class="text-2xl font-semibold text-gray-900">Export Configuration</h2>

                            <!-- Export Formats -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h3 class="font-medium text-gray-900">Available Export Formats</h3>
                                </div>
                                <div class="p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="export-format selected">
                                            <div class="flex items-center gap-3 mb-3">
                                                <i class="fas fa-file-csv text-green-500 text-2xl"></i>
                                                <div>
                                                    <h4 class="font-medium text-gray-900">CSV</h4>
                                                    <p class="text-sm text-gray-600">Comma-separated values</p>
                                                </div>
                                            </div>
                                            <div class="text-xs text-gray-500">Best for: Spreadsheet analysis</div>
                                        </div>
                                        <div class="export-format">
                                            <div class="flex items-center gap-3 mb-3">
                                                <i class="fas fa-file-excel text-green-600 text-2xl"></i>
                                                <div>
                                                    <h4 class="font-medium text-gray-900">Excel</h4>
                                                    <p class="text-sm text-gray-600">Microsoft Excel format</p>
                                                </div>
                                            </div>
                                            <div class="text-xs text-gray-500">Best for: Advanced analysis</div>
                                        </div>
                                        <div class="export-format">
                                            <div class="flex items-center gap-3 mb-3">
                                                <i class="fas fa-file-pdf text-red-500 text-2xl"></i>
                                                <div>
                                                    <h4 class="font-medium text-gray-900">PDF</h4>
                                                    <p class="text-sm text-gray-600">Portable document format</p>
                                                </div>
                                            </div>
                                            <div class="text-xs text-gray-500">Best for: Reports & sharing</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Export Schedule -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h3 class="font-medium text-gray-900">Automated Exports</h3>
                                </div>
                                <div class="p-6">
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Export Frequency</label>
                                                <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                                    <option>Never</option>
                                                    <option>Daily</option>
                                                    <option selected>Weekly</option>
                                                    <option>Monthly</option>
                                                    <option>Quarterly</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Export Time</label>
                                                <input type="time" value="09:00" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Recipients</label>
                                                <textarea rows="3" placeholder="Enter email addresses, one per line" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">admin@company.com
analytics@company.com</textarea>
                                            </div>
                                        </div>
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Data Range</label>
                                                <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                                    <option>Last 7 days</option>
                                                    <option>Last 30 days</option>
                                                    <option>Last 90 days</option>
                                                    <option>Custom range</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Include</label>
                                                <div class="space-y-2">
                                                    <div class="flex items-center gap-2">
                                                        <input type="checkbox" checked class="rounded border-gray-300">
                                                        <span class="text-sm">Raw message data</span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <input type="checkbox" checked class="rounded border-gray-300">
                                                        <span class="text-sm">Aggregated metrics</span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <input type="checkbox" class="rounded border-gray-300">
                                                        <span class="text-sm">User information</span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <input type="checkbox" class="rounded border-gray-300">
                                                        <span class="text-sm">Error logs</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex gap-4 mt-6">
                                        <button class="px-4 py-2 bg-whatsapp text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                                            Save Export Settings
                                        </button>
                                        <button class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                            Test Export Now
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Analytics navigation
        document.querySelectorAll('.analytics-nav-item').forEach(item => {
            item.addEventListener('click', function() {
                const section = this.dataset.section;
                
                // Remove active class from all nav items
                document.querySelectorAll('.analytics-nav-item').forEach(nav => {
                    nav.classList.remove('active', 'text-whatsapp', 'bg-green-50');
                    nav.classList.add('text-gray-600');
                });
                
                // Add active class to clicked nav
                this.classList.add('active', 'text-whatsapp', 'bg-green-50');
                this.classList.remove('text-gray-600');
                
                // Hide all sections
                document.querySelectorAll('.analytics-section').forEach(sec => {
                    sec.classList.remove('active');
                });
                
                // Show selected section
                const targetSection = document.getElementById(section);
                if (targetSection) {
                    setTimeout(() => {
                        targetSection.classList.add('active');
                    }, 150);
                }
            });
        });

        // Toggle switches
        document.querySelectorAll('.toggle-switch').forEach(toggle => {
            toggle.addEventListener('click', function() {
                this.classList.toggle('active');
            });
        });

        // Metric card selection
        document.querySelectorAll('.metric-card').forEach(card => {
            card.addEventListener('click', function() {
                this.classList.toggle('selected');
            });
        });

        // Color picker functionality
        document.querySelectorAll('.color-picker').forEach(picker => {
            picker.addEventListener('click', function() {
                // Create a temporary color input
                const colorInput = document.createElement('input');
                colorInput.type = 'color';
                colorInput.value = this.style.backgroundColor;
                
                colorInput.addEventListener('change', () => {
                    this.style.backgroundColor = colorInput.value;
                });
                
                colorInput.click();
            });
        });

        // Export format selection
        document.querySelectorAll('.export-format').forEach(format => {
            format.addEventListener('click', function() {
                // Remove selected class from all formats
                document.querySelectorAll('.export-format').forEach(f => {
                    f.classList.remove('selected');
                });
                
                // Add selected class to clicked format
                this.classList.add('selected');
            });
        });

        // Frequency selector
        document.querySelectorAll('.frequency-option').forEach(option => {
            option.addEventListener('click', function() {
                // Remove active class from all options
                document.querySelectorAll('.frequency-option').forEach(opt => {
                    opt.classList.remove('active');
                });
                
                // Add active class to clicked option
                this.classList.add('active');
            });
        });

        // Drag and drop for dashboard widgets
        document.querySelectorAll('[draggable="true"]').forEach(widget => {
            widget.addEventListener('dragstart', function(e) {
                e.dataTransfer.setData('text/plain', this.dataset.widget);
                this.style.opacity = '0.5';
            });

            widget.addEventListener('dragend', function() {
                this.style.opacity = '1';
            });
        });

        document.querySelectorAll('.widget-placeholder').forEach(zone => {
            zone.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.style.borderColor = '#25D366';
                this.style.backgroundColor = '#f0fdf4';
            });

            zone.addEventListener('dragleave', function() {
                this.style.borderColor = '#d1d5db';
                this.style.backgroundColor = '';
            });

            zone.addEventListener('drop', function(e) {
                e.preventDefault();
                const widgetType = e.dataTransfer.getData('text/plain');
                
                // Update drop zone content
                this.innerHTML = `
                    <i class="fas fa-chart-bar text-whatsapp text-2xl mb-2"></i>
                    <p class="text-whatsapp font-medium">${widgetType.replace('-', ' ').toUpperCase()}</p>
                    <p class="text-sm text-gray-500">Widget added successfully</p>
                `;
                this.style.borderColor = '#25D366';
                this.style.backgroundColor = '#f0fdf4';
            });
        });

        // Save functionality
        document.getElementById('saveAnalytics').addEventListener('click', function() {
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
            this.disabled = true;
            
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-check mr-2"></i>Saved!';
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                }, 1000);
            }, 2000);
        });

        // Export functionality
        document.getElementById('exportData').addEventListener('click', function() {
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Exporting...';
            this.disabled = true;
            
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-check mr-2"></i>Exported!';
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                }, 1000);
            }, 3000);
        });

        // Initialize first section as active
        document.addEventListener('DOMContentLoaded', function() {
            const firstSection = document.getElementById('dashboard');
            if (firstSection) {
                firstSection.classList.add('active');
            }
        });
    </script>
</body>
</html>