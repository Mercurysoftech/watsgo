<?php
require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/config.php';

// Avoid duplicate session_start()
if (session_status() === PHP_SESSION_NONE) {
    // session_start();
}

$_SESSION['fetched_report'] = $api_data ?? null;
$_SESSION['mismatch_report'] = $mismatches ?? null;

$user = require_login();
$webhookUrl = rtrim(envv('APP_URL'), '/') . '/webhook.php?token=' . $user['webhook_token'];
$events = envv('RAZORPAY_EVENTS', 'payment.captured');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsBot Configuration - Admin Dashboard</title>
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

        .config-section {
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
        }

        .config-section.active {
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

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .template-item:hover {
            background-color: #f9fafb;
        }

        .workflow-step {
            position: relative;
        }

        .workflow-step::after {
            content: '';
            position: absolute;
            inset-block-start: 50px;
            inset-inline-start: 50%;
            inline-size: 2px;
            block-size: 30px;
            background-color: #e5e7eb;
            transform: translateX(-50%);
        }

        .workflow-step:last-child::after {
            display: none;
        }

        .code-editor {
            font-family: 'Courier New', monospace;
            background-color: #1e293b;
            color: #e2e8f0;
            border-radius: 8px;
            padding: 1rem;
            overflow-x: auto;
        }

        .json-key {
            color: #38bdf8;
        }

        .json-string {
            color: #22d3ee;
        }

        .json-number {
            color: #fb7185;
        }

        .json-boolean {
            color: #a78bfa;
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
                            <h1 class="text-xl font-semibold text-gray-900">Configuration</h1>
                            <p class="text-sm text-gray-500">Configure your WhatsApp bot settings and behavior</p>
                        </div>
                    </div>

                    <!-- Right Section -->
                    <div class="flex items-center gap-4">
                        <!-- Save Button -->
                        <button
                            class="px-4 py-2 bg-whatsapp text-white rounded-lg hover:bg-whatsapp-dark transition-colors"
                            id="saveConfig">
                            <i class="fas fa-save mr-2"></i>
                            Save Changes
                        </button>

                        <!-- Bot Status -->
                        <div class="flex items-center gap-2 px-3 py-2 bg-green-50 rounded-lg border border-green-200">
                            <div class="w-2 h-2 bg-whatsapp rounded-full animate-pulse"></div>
                            <span class="text-sm font-medium text-green-800">Bot Active</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Configuration Content -->
            <div class="flex-1 flex">
                <!-- Configuration Sidebar -->
                <div class="w-64 bg-white border-r border-gray-200 overflow-y-auto">
                    <div class="p-4">
                        <h3 class="font-medium text-gray-900 mb-4">Configuration</h3>
                        <nav class="space-y-2">
                            <button
                                class="config-nav-item active w-full text-left px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50"
                                data-section="wp-integration">
                                <i class="fas fa-comment mr-2"></i>
                                WhatsApp Integration
                            </button>
                            <button
                                class="config-nav-item w-full text-left px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50"
                                data-section="rp-settings">
                                <i class="fas fa-rupee-sign mr-2"></i>
                                Razorpay Settings
                            </button>
                            <button
                                class="config-nav-item w-full text-left px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50"
                                data-section="wb-integration">
                                <i class="fas fa-plug mr-2"></i>
                                Webhook Configuration
                            </button>
                            <button
                                class="config-nav-item w-full text-left px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50"
                                data-section="ec-integration">
                                <i class="fas fa-link mr-2"></i>
                                Ecommerce Store integration
                            </button>
                            <!-- <button
                                class="config-nav-item w-full text-left px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50"
                                data-section="training">
                                <i class="fas fa-brain mr-2"></i>
                                Training Data
                            </button> -->
                            <!-- <button
                                class="config-nav-item w-full text-left px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50"
                                data-section="analytics">
                                <i class="fas fa-chart-line mr-2"></i>
                                Analytics
                            </button> -->
                            <button
                                class="config-nav-item w-full text-left px-3 py-2 rounded-lg text-sm font-medium text-whatsapp bg-green-50"
                                data-section="general">
                                <i class="fas fa-cog mr-2"></i>
                                General Settings
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Main Configuration Area -->
                <div class="flex-1 overflow-y-auto">

                    <div class="config-section p-6" id="wp-integration">
                        <div class="max-w-4xl mx-auto space-y-6">
                            <!-- Bot Information -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                        <i class="fas fa-robot text-whatsapp"></i>
                                        Bot Information
                                    </h2>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Bot Name</label>
                                            <input type="text" value="WhatsBot Assistant"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Bot
                                                Version</label>
                                            <input type="text" value="v2.1.0"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                        <textarea rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">Intelligent customer support bot for WhatsApp with advanced AI capabilities</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Message Settings -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h2 class="text-lg font-semibold text-gray-900">Message Settings</h2>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Welcome
                                            Message</label>
                                        <textarea rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">Hi! 👋 I'm your virtual assistant. How can I help you today?</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Fallback
                                            Message</label>
                                        <textarea rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">I'm sorry, I didn't understand that. Let me connect you with a human agent.</textarea>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Response Delay
                                                (ms)</label>
                                            <input type="number" value="1000" min="0" max="5000"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Max Message
                                                Length</label>
                                            <input type="number" value="1000" min="100" max="4096"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Features -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h2 class="text-lg font-semibold text-gray-900">Bot Features</h2>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div
                                        class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Auto-Reply</h4>
                                            <p class="text-sm text-gray-600">Automatically respond to incoming messages
                                            </p>
                                        </div>
                                        <div class="toggle-switch active" data-feature="auto-reply"></div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Smart Routing</h4>
                                            <p class="text-sm text-gray-600">Route messages to appropriate departments
                                            </p>
                                        </div>
                                        <div class="toggle-switch active" data-feature="smart-routing"></div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Language Detection</h4>
                                            <p class="text-sm text-gray-600">Detect and respond in user's language</p>
                                        </div>
                                        <div class="toggle-switch" data-feature="language-detection"></div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Sentiment Analysis</h4>
                                            <p class="text-sm text-gray-600">Analyze customer satisfaction in real-time
                                            </p>
                                        </div>
                                        <div class="toggle-switch active" data-feature="sentiment-analysis"></div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Business Hours</h4>
                                            <p class="text-sm text-gray-600">Only respond during business hours</p>
                                        </div>
                                        <div class="toggle-switch active" data-feature="business-hours"></div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Typing Indicator</h4>
                                            <p class="text-sm text-gray-600">Show typing indicator for natural feel</p>
                                        </div>
                                        <div class="toggle-switch active" data-feature="typing-indicator"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="config-section p-6" id="rp-settings">
                        <div class="max-w-4xl mx-auto space-y-6">

                            <!-- Message Settings -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h2 class="text-lg font-semibold text-gray-900">Razorpay Settings</h2>
                                    <p>Configure your payment gateway</p>
                                </div>
                                <div class="p-6 space-y-6 bg-white shadow-md rounded-xl">
                                    <!-- Key ID -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Key ID</label>
                                        <input type="text" name="key_id"
                                            value="<?= h($user['razorpay_key_id'] ?? '') ?>" required
                                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-gray-700 shadow-sm px-3 py-2"
                                            placeholder="Enter Razorpay Key ID">
                                    </div>

                                    <!-- Key Secret -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Key Secret</label>
                                        <div class="relative">
                                            <input type="password" id="key_secret"
                                                value="<?= h($user['razorpay_key_secret'] ?? '') ?>"
                                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-gray-700 shadow-sm px-3 py-2"
                                                placeholder="Enter Razorpay Key Secret">
                                            <button type="button" id="showKeySecretBtn"
                                                class="absolute right-2 top-2 text-indigo-600 text-sm">Show</button>
                                        </div>
                                    </div>

                                    <!-- Save Button -->
                                    <div>
                                        <button type="submit"
                                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-200 ease-in-out">
                                            Save Configuration
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- Modal -->
                    <div id="passwordModal"
                        class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                        <div class="bg-white p-6 rounded-lg w-96">
                            <h3 class="text-lg font-medium mb-4">Enter Your Password</h3>
                            <input type="password" id="userPassword" class="w-full border rounded px-3 py-2 mb-4"
                                placeholder="Password">
                            <div class="flex justify-end">
                                <button id="cancelModal" class="mr-2 px-4 py-2 bg-gray-300 rounded">Cancel</button>
                                <button id="confirmModal"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded">Confirm</button>
                            </div>
                        </div>
                    </div>
                    <div class="config-section p-6" id="wb-integration">
                        <div class="max-w-4xl mx-auto space-y-6">
                            <!-- Bot Information -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                        <i class="fas fa-robot text-whatsapp"></i>
                                        Bot Information
                                    </h2>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Bot Name</label>
                                            <input type="text" value="WhatsBot Assistant"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Bot
                                                Version</label>
                                            <input type="text" value="v2.1.0"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                        <textarea rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">Intelligent customer support bot for WhatsApp with advanced AI capabilities</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Message Settings -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h2 class="text-lg font-semibold text-gray-900">Message Settings</h2>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Welcome
                                            Message</label>
                                        <textarea rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">Hi! 👋 I'm your virtual assistant. How can I help you today?</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Fallback
                                            Message</label>
                                        <textarea rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">I'm sorry, I didn't understand that. Let me connect you with a human agent.</textarea>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Response Delay
                                                (ms)</label>
                                            <input type="number" value="1000" min="0" max="5000"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Max Message
                                                Length</label>
                                            <input type="number" value="1000" min="100" max="4096"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Features -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h2 class="text-lg font-semibold text-gray-900">Bot Features</h2>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div
                                        class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Auto-Reply</h4>
                                            <p class="text-sm text-gray-600">Automatically respond to incoming messages
                                            </p>
                                        </div>
                                        <div class="toggle-switch active" data-feature="auto-reply"></div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Smart Routing</h4>
                                            <p class="text-sm text-gray-600">Route messages to appropriate departments
                                            </p>
                                        </div>
                                        <div class="toggle-switch active" data-feature="smart-routing"></div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Language Detection</h4>
                                            <p class="text-sm text-gray-600">Detect and respond in user's language</p>
                                        </div>
                                        <div class="toggle-switch" data-feature="language-detection"></div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Sentiment Analysis</h4>
                                            <p class="text-sm text-gray-600">Analyze customer satisfaction in real-time
                                            </p>
                                        </div>
                                        <div class="toggle-switch active" data-feature="sentiment-analysis"></div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Business Hours</h4>
                                            <p class="text-sm text-gray-600">Only respond during business hours</p>
                                        </div>
                                        <div class="toggle-switch active" data-feature="business-hours"></div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Typing Indicator</h4>
                                            <p class="text-sm text-gray-600">Show typing indicator for natural feel</p>
                                        </div>
                                        <div class="toggle-switch active" data-feature="typing-indicator"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="config-section p-6" id="ec-integration">
                        <div class="max-w-4xl mx-auto space-y-6">
                            <!-- Bot Information -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                        <i class="fas fa-robot text-whatsapp"></i>
                                        Bot Information
                                    </h2>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Bot Name</label>
                                            <input type="text" value="WhatsBot Assistant"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Bot
                                                Version</label>
                                            <input type="text" value="v2.1.0"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                        <textarea rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">Intelligent customer support bot for WhatsApp with advanced AI capabilities</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Message Settings -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h2 class="text-lg font-semibold text-gray-900">Message Settings</h2>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Welcome
                                            Message</label>
                                        <textarea rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">Hi! 👋 I'm your virtual assistant. How can I help you today?</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Fallback
                                            Message</label>
                                        <textarea rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">I'm sorry, I didn't understand that. Let me connect you with a human agent.</textarea>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Response Delay
                                                (ms)</label>
                                            <input type="number" value="1000" min="0" max="5000"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Max Message
                                                Length</label>
                                            <input type="number" value="1000" min="100" max="4096"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Features -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h2 class="text-lg font-semibold text-gray-900">Bot Features</h2>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div
                                        class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Auto-Reply</h4>
                                            <p class="text-sm text-gray-600">Automatically respond to incoming messages
                                            </p>
                                        </div>
                                        <div class="toggle-switch active" data-feature="auto-reply"></div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Smart Routing</h4>
                                            <p class="text-sm text-gray-600">Route messages to appropriate departments
                                            </p>
                                        </div>
                                        <div class="toggle-switch active" data-feature="smart-routing"></div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Language Detection</h4>
                                            <p class="text-sm text-gray-600">Detect and respond in user's language</p>
                                        </div>
                                        <div class="toggle-switch" data-feature="language-detection"></div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Sentiment Analysis</h4>
                                            <p class="text-sm text-gray-600">Analyze customer satisfaction in real-time
                                            </p>
                                        </div>
                                        <div class="toggle-switch active" data-feature="sentiment-analysis"></div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Business Hours</h4>
                                            <p class="text-sm text-gray-600">Only respond during business hours</p>
                                        </div>
                                        <div class="toggle-switch active" data-feature="business-hours"></div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Typing Indicator</h4>
                                            <p class="text-sm text-gray-600">Show typing indicator for natural feel</p>
                                        </div>
                                        <div class="toggle-switch active" data-feature="typing-indicator"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="config-section p-6" id="general">
                        <div class="max-w-4xl mx-auto space-y-6">
                            <!-- Bot Information -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                        <i class="fas fa-robot text-whatsapp"></i>
                                        Bot Information
                                    </h2>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Bot Name</label>
                                            <input type="text" value="WhatsBot Assistant"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Bot
                                                Version</label>
                                            <input type="text" value="v2.1.0"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                        <textarea rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">Intelligent customer support bot for WhatsApp with advanced AI capabilities</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Message Settings -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h2 class="text-lg font-semibold text-gray-900">Message Settings</h2>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Welcome
                                            Message</label>
                                        <textarea rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">Hi! 👋 I'm your virtual assistant. How can I help you today?</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Fallback
                                            Message</label>
                                        <textarea rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">I'm sorry, I didn't understand that. Let me connect you with a human agent.</textarea>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Response Delay
                                                (ms)</label>
                                            <input type="number" value="1000" min="0" max="5000"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Max Message
                                                Length</label>
                                            <input type="number" value="1000" min="100" max="4096"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Features -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h2 class="text-lg font-semibold text-gray-900">Bot Features</h2>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div
                                        class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Auto-Reply</h4>
                                            <p class="text-sm text-gray-600">Automatically respond to incoming messages
                                            </p>
                                        </div>
                                        <div class="toggle-switch active" data-feature="auto-reply"></div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Smart Routing</h4>
                                            <p class="text-sm text-gray-600">Route messages to appropriate departments
                                            </p>
                                        </div>
                                        <div class="toggle-switch active" data-feature="smart-routing"></div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Language Detection</h4>
                                            <p class="text-sm text-gray-600">Detect and respond in user's language</p>
                                        </div>
                                        <div class="toggle-switch" data-feature="language-detection"></div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Sentiment Analysis</h4>
                                            <p class="text-sm text-gray-600">Analyze customer satisfaction in real-time
                                            </p>
                                        </div>
                                        <div class="toggle-switch active" data-feature="sentiment-analysis"></div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Business Hours</h4>
                                            <p class="text-sm text-gray-600">Only respond during business hours</p>
                                        </div>
                                        <div class="toggle-switch active" data-feature="business-hours"></div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Typing Indicator</h4>
                                            <p class="text-sm text-gray-600">Show typing indicator for natural feel</p>
                                        </div>
                                        <div class="toggle-switch active" data-feature="typing-indicator"></div>
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
        // Configuration Management System
        class ConfigManager {
            constructor() {
                this.init();
            }

            init() {
                this.setupNavigation();
                this.setupToggleSwitches();
                this.setupSaveButton();
                this.setupTemplateEditor();
                this.setupFileUpload();
                this.setupWebhookTesting();
                this.initializeFirstSection();
            }

            // Navigation between configuration sections
            setupNavigation() {
                const navItems = document.querySelectorAll('.config-nav-item');

                navItems.forEach(item => {
                    item.addEventListener('click', (e) => {
                        const section = e.currentTarget.dataset.section;
                        this.switchSection(section, e.currentTarget);
                    });
                });
            }

            switchSection(sectionId, activeNav) {
                // Clear active states from all nav items
                document.querySelectorAll('.config-nav-item').forEach(nav => {
                    nav.classList.remove('active', 'text-whatsapp', 'bg-green-50');
                    nav.classList.add('text-gray-600');
                });

                // Activate clicked nav item
                activeNav.classList.add('active', 'text-whatsapp', 'bg-green-50');
                activeNav.classList.remove('text-gray-600');

                // Hide all sections
                document.querySelectorAll('.config-section').forEach(section => {
                    section.classList.add('hidden');   // hide others
                    section.classList.remove('active');
                });

                // Show target section
                const targetSection = document.getElementById(sectionId);
                if (targetSection) {
                    targetSection.classList.remove('hidden'); // make visible
                    setTimeout(() => targetSection.classList.add('active'), 150);
                }
            }


            // Toggle switch functionality
            setupToggleSwitches() {
                const toggles = document.querySelectorAll('.toggle-switch');

                toggles.forEach(toggle => {
                    toggle.addEventListener('click', (e) => {
                        this.handleToggle(e.currentTarget);
                    });
                });
            }

            handleToggle(toggle) {
                toggle.classList.toggle('active');

                const feature = toggle.dataset.feature;
                if (feature) {
                    this.updateToggleBadge(toggle);
                }
            }

            updateToggleBadge(toggle) {
                const badge = toggle.closest('.flex')?.querySelector('span.px-2');
                if (!badge) return;

                const isActive = toggle.classList.contains('active');

                badge.textContent = isActive ? 'ON' : 'OFF';
                badge.className = isActive
                    ? 'bg-whatsapp text-white px-2 py-1 rounded-full text-xs font-medium'
                    : 'bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs font-medium';
            }

            // Save configuration with loading states
            setupSaveButton() {
                const saveButton = document.getElementById('saveConfig');
                if (!saveButton) return;

                saveButton.addEventListener('click', (e) => {
                    this.saveConfiguration(e.currentTarget);
                });
            }

            async saveConfiguration(button) {
                const originalContent = button.innerHTML;

                // Show loading state
                button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
                button.disabled = true;

                try {
                    // Simulate API call
                    await this.delay(2000);

                    // Show success state
                    button.innerHTML = '<i class="fas fa-check mr-2"></i>Saved!';
                    await this.delay(1000);

                } catch (error) {
                    // Show error state
                    button.innerHTML = '<i class="fas fa-times mr-2"></i>Error!';
                    await this.delay(1000);
                } finally {
                    // Reset button
                    button.innerHTML = originalContent;
                    button.disabled = false;
                }
            }

            // Template editor functionality
            setupTemplateEditor() {
                this.setupAddTemplate();
                this.setupTemplateSelection();
            }

            setupAddTemplate() {
                const addButton = document.getElementById('addTemplate');
                if (!addButton) return;

                addButton.addEventListener('click', () => {
                    const nameInput = document.querySelector('#messages input[placeholder="Enter template name"]');
                    nameInput?.focus();
                });
            }

            setupTemplateSelection() {
                const templateItems = document.querySelectorAll('.template-item');

                templateItems.forEach(item => {
                    item.addEventListener('click', (e) => {
                        this.selectTemplate(e.currentTarget);
                    });
                });
            }

            selectTemplate(selectedTemplate) {
                // Clear all active templates
                document.querySelectorAll('.template-item').forEach(template => {
                    template.classList.remove('bg-green-50', 'border-whatsapp');
                });

                // Activate selected template
                selectedTemplate.classList.add('bg-green-50', 'border-whatsapp');

                // Load template content
                const templateName = selectedTemplate.querySelector('.font-medium')?.textContent;
                if (templateName) {
                    this.loadTemplateContent(templateName);
                }
            }

            loadTemplateContent(templateName) {
                const nameInput = document.querySelector('#messages input[placeholder="Enter template name"]');
                if (nameInput) {
                    nameInput.value = templateName;
                }
            }

            // File upload handling
            setupFileUpload() {
                const uploadArea = document.querySelector('#training .border-dashed');
                if (!uploadArea) return;

                uploadArea.addEventListener('click', () => {
                    this.handleFileUpload();
                });
            }

            handleFileUpload() {
                const fileInput = document.createElement('input');
                fileInput.type = 'file';
                fileInput.accept = '.csv,.json';
                fileInput.multiple = true;

                fileInput.addEventListener('change', (e) => {
                    const files = Array.from(e.target.files);
                    if (files.length > 0) {
                        const fileNames = files.map(file => file.name).join(', ');
                        this.showFileUploadResult(fileNames);
                    }
                });

                fileInput.click();
            }

            showFileUploadResult(fileNames) {
                // You might want to replace this with a proper notification system
                alert(`Files selected: ${fileNames}`);
            }

            // Webhook testing functionality
            setupWebhookTesting() {
                const testButtons = document.querySelectorAll('button');

                testButtons.forEach(button => {
                    if (button.textContent.includes('Test Webhook')) {
                        button.addEventListener('click', (e) => {
                            this.testWebhook(e.currentTarget);
                        });
                    }
                });
            }

            async testWebhook(button) {
                const originalContent = button.innerHTML;
                const originalClasses = button.className;

                // Show testing state
                button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Testing...';
                button.disabled = true;

                try {
                    // Simulate webhook test
                    await this.delay(3000);

                    // Show success state
                    button.innerHTML = '<i class="fas fa-check mr-2"></i>Test Successful';
                    button.classList.add('bg-green-500');
                    button.classList.remove('border-gray-300', 'text-gray-700');

                    await this.delay(2000);

                } catch (error) {
                    // Show error state
                    button.innerHTML = '<i class="fas fa-times mr-2"></i>Test Failed';
                    button.classList.add('bg-red-500');

                    await this.delay(2000);
                } finally {
                    // Reset button
                    button.innerHTML = originalContent;
                    button.className = originalClasses;
                    button.disabled = false;
                }
            }

            // Initialize the first section as active
            // Initialize the first section as active
            initializeFirstSection() {
                const allSections = document.querySelectorAll('.config-section');
                allSections.forEach((section, index) => {
                    if (index === 0) {
                        section.classList.add('active');
                        section.classList.remove('hidden'); // first section visible
                    } else {
                        section.classList.remove('active');
                        section.classList.add('hidden'); // others hidden
                    }
                });
            }


            // Utility method for delays
            delay(ms) {
                return new Promise(resolve => setTimeout(resolve, ms));
            }
        }

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            new ConfigManager();
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const showBtn = document.getElementById('showKeySecretBtn');
            const modal = document.getElementById('passwordModal');
            const cancelBtn = document.getElementById('cancelModal');
            const confirmBtn = document.getElementById('confirmModal');
            const passwordInput = document.getElementById('userPassword');
            const keySecretInput = document.getElementById('key_secret');

            // Show modal
            showBtn.addEventListener('click', () => {
                passwordInput.value = '';
                modal.classList.remove('hidden');
                passwordInput.focus();
            });

            // Cancel modal
            cancelBtn.addEventListener('click', () => {
                modal.classList.add('hidden');
            });

            // Confirm password
            confirmBtn.addEventListener('click', async () => {
                const password = passwordInput.value.trim();
                if (!password) {
                    alert('Please enter your password');
                    return;
                }

                try {
                    const response = await fetch('verify_password.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ password })
                    });

                    // Try parsing JSON, catch invalid response
                    let data;
                    try {
                        data = await response.json();
                    } catch (e) {
                        const text = await response.text();
                        console.error('Server returned non-JSON:', text);
                        alert('Server error: check console for details');
                        return;
                    }

                    if (data.success) {
                        // Show the key secret
                        keySecretInput.type = 'text';
                        modal.classList.add('hidden');
                    } else {
                        alert('Incorrect password');
                        passwordInput.value = '';
                        passwordInput.focus();
                    }
                } catch (err) {
                    console.error('Fetch error:', err);
                    alert('Network error. Please try again.');
                }
            });

            // Optional: Press Enter in modal input triggers confirm
            passwordInput.addEventListener('keyup', (e) => {
                if (e.key === 'Enter') confirmBtn.click();
            });
        });

    </script>
</body>

</html>