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
                            <h1 class="text-xl font-semibold text-gray-900">Bot Configuration</h1>
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
                                class="config-nav-item active w-full text-left px-3 py-2 rounded-lg text-sm font-medium text-whatsapp bg-green-50"
                                data-section="general">
                                <i class="fas fa-cog mr-2"></i>
                                General Settings
                            </button>
                            <button
                                class="config-nav-item w-full text-left px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50"
                                data-section="messages">
                                <i class="fas fa-comment mr-2"></i>
                                Message Templates
                            </button>
                            <button
                                class="config-nav-item w-full text-left px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50"
                                data-section="workflows">
                                <i class="fas fa-sitemap mr-2"></i>
                                Workflows
                            </button>
                            <button
                                class="config-nav-item w-full text-left px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50"
                                data-section="integrations">
                                <i class="fas fa-plug mr-2"></i>
                                Integrations
                            </button>
                            <button
                                class="config-nav-item w-full text-left px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50"
                                data-section="webhooks">
                                <i class="fas fa-link mr-2"></i>
                                Webhooks
                            </button>
                            <button
                                class="config-nav-item w-full text-left px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50"
                                data-section="training">
                                <i class="fas fa-brain mr-2"></i>
                                Training Data
                            </button>
                            <button
                                class="config-nav-item w-full text-left px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50"
                                data-section="analytics">
                                <i class="fas fa-chart-line mr-2"></i>
                                Analytics
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Main Configuration Area -->
                <div class="flex-1 overflow-y-auto">
                    <!-- General Settings -->
                    <div class="config-section active p-6" id="general">
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

                    <!-- Message Templates -->
                    <div class="config-section p-6" id="messages">
                        <div class="max-w-4xl mx-auto space-y-6">
                            <!-- Templates Header -->
                            <div class="flex items-center justify-between">
                                <h2 class="text-2xl font-semibold text-gray-900">Message Templates</h2>
                                <button
                                    class="px-4 py-2 bg-whatsapp text-white rounded-lg hover:bg-whatsapp-dark transition-colors"
                                    id="addTemplate">
                                    <i class="fas fa-plus mr-2"></i>
                                    Add Template
                                </button>
                            </div>

                            <!-- Template Categories -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <!-- Welcome Templates -->
                                        <div>
                                            <h3 class="font-medium text-gray-900 mb-4">Welcome Messages</h3>
                                            <div class="space-y-3">
                                                <div
                                                    class="template-item p-3 border border-gray-200 rounded-lg cursor-pointer">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-sm font-medium">Standard Welcome</span>
                                                        <button class="text-gray-400 hover:text-gray-600">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </div>
                                                    <p class="text-xs text-gray-500 mt-1">Hi! Welcome to our service...
                                                    </p>
                                                </div>
                                                <div
                                                    class="template-item p-3 border border-gray-200 rounded-lg cursor-pointer">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-sm font-medium">VIP Welcome</span>
                                                        <button class="text-gray-400 hover:text-gray-600">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </div>
                                                    <p class="text-xs text-gray-500 mt-1">Welcome back, valued
                                                        customer...</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Support Templates -->
                                        <div>
                                            <h3 class="font-medium text-gray-900 mb-4">Support Messages</h3>
                                            <div class="space-y-3">
                                                <div
                                                    class="template-item p-3 border border-gray-200 rounded-lg cursor-pointer">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-sm font-medium">Order Status</span>
                                                        <button class="text-gray-400 hover:text-gray-600">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </div>
                                                    <p class="text-xs text-gray-500 mt-1">Let me check your order
                                                        status...</p>
                                                </div>
                                                <div
                                                    class="template-item p-3 border border-gray-200 rounded-lg cursor-pointer">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-sm font-medium">Technical Issue</span>
                                                        <button class="text-gray-400 hover:text-gray-600">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </div>
                                                    <p class="text-xs text-gray-500 mt-1">I understand you're
                                                        experiencing...</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Sales Templates -->
                                        <div>
                                            <h3 class="font-medium text-gray-900 mb-4">Sales Messages</h3>
                                            <div class="space-y-3">
                                                <div
                                                    class="template-item p-3 border border-gray-200 rounded-lg cursor-pointer">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-sm font-medium">Product Info</span>
                                                        <button class="text-gray-400 hover:text-gray-600">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </div>
                                                    <p class="text-xs text-gray-500 mt-1">I'd be happy to tell you
                                                        about...</p>
                                                </div>
                                                <div
                                                    class="template-item p-3 border border-gray-200 rounded-lg cursor-pointer">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-sm font-medium">Pricing</span>
                                                        <button class="text-gray-400 hover:text-gray-600">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </div>
                                                    <p class="text-xs text-gray-500 mt-1">Here are our current pricing
                                                        options...</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Template Editor -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h3 class="font-medium text-gray-900">Template Editor</h3>
                                </div>
                                <div class="p-6">
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Template
                                                Name</label>
                                            <input type="text" placeholder="Enter template name"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">

                                            <label
                                                class="block text-sm font-medium text-gray-700 mb-2 mt-4">Category</label>
                                            <select
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                                <option>Welcome</option>
                                                <option>Support</option>
                                                <option>Sales</option>
                                                <option>General</option>
                                            </select>

                                            <label class="block text-sm font-medium text-gray-700 mb-2 mt-4">Message
                                                Content</label>
                                            <textarea rows="6" placeholder="Enter your message template..."
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp"></textarea>

                                            <div class="flex gap-2 mt-4">
                                                <button
                                                    class="px-4 py-2 bg-whatsapp text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                                                    Save Template
                                                </button>
                                                <button
                                                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                                    Test Template
                                                </button>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Preview</label>
                                            <div class="border border-gray-300 rounded-lg p-4 bg-gray-50 min-h-[300px]">
                                                <div class="bg-whatsapp-light p-3 rounded-lg inline-block max-w-xs">
                                                    <p class="text-sm">Your template preview will appear here...</p>
                                                    <span class="text-xs text-gray-500 mt-2 block">12:30 PM ✓✓</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Workflows -->
                    <div class="config-section p-6" id="workflows">
                        <div class="max-w-4xl mx-auto space-y-6">
                            <div class="flex items-center justify-between">
                                <h2 class="text-2xl font-semibold text-gray-900">Conversation Workflows</h2>
                                <button
                                    class="px-4 py-2 bg-whatsapp text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                                    <i class="fas fa-plus mr-2"></i>
                                    Create Workflow
                                </button>
                            </div>

                            <!-- Workflow Visual Builder -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h3 class="font-medium text-gray-900">Customer Support Workflow</h3>
                                </div>
                                <div class="p-6">
                                    <div class="flex items-center justify-center">
                                        <div class="workflow-step text-center">
                                            <div
                                                class="w-12 h-12 bg-whatsapp rounded-full flex items-center justify-center mx-auto mb-2">
                                                <i class="fas fa-play text-white"></i>
                                            </div>
                                            <p class="text-sm font-medium">Start</p>
                                            <p class="text-xs text-gray-500">User sends message</p>
                                        </div>
                                        <i class="fas fa-arrow-down mx-4 text-gray-400"></i>
                                        <div class="workflow-step text-center">
                                            <div
                                                class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-2">
                                                <i class="fas fa-brain text-white"></i>
                                            </div>
                                            <p class="text-sm font-medium">Analyze</p>
                                            <p class="text-xs text-gray-500">Intent detection</p>
                                        </div>
                                        <i class="fas fa-arrow-down mx-4 text-gray-400"></i>
                                        <div class="workflow-step text-center">
                                            <div
                                                class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-2">
                                                <i class="fas fa-route text-white"></i>
                                            </div>
                                            <p class="text-sm font-medium">Route</p>
                                            <p class="text-xs text-gray-500">Department routing</p>
                                        </div>
                                        <i class="fas fa-arrow-down mx-4 text-gray-400"></i>
                                        <div class="workflow-step text-center">
                                            <div
                                                class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-2">
                                                <i class="fas fa-reply text-white"></i>
                                            </div>
                                            <p class="text-sm font-medium">Response</p>
                                            <p class="text-xs text-gray-500">Send response</p>
                                        </div>
                                    </div>

                                    <!-- Workflow Configuration -->
                                    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <h4 class="font-medium text-gray-900 mb-3">Triggers</h4>
                                            <div class="space-y-2">
                                                <div class="flex items-center gap-2">
                                                    <input type="checkbox" checked class="rounded border-gray-300">
                                                    <span class="text-sm">Keywords: "help", "support"</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <input type="checkbox" checked class="rounded border-gray-300">
                                                    <span class="text-sm">Order-related queries</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <input type="checkbox" class="rounded border-gray-300">
                                                    <span class="text-sm">Technical issues</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-900 mb-3">Actions</h4>
                                            <div class="space-y-2">
                                                <div class="flex items-center gap-2">
                                                    <input type="checkbox" checked class="rounded border-gray-300">
                                                    <span class="text-sm">Send welcome message</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <input type="checkbox" checked class="rounded border-gray-300">
                                                    <span class="text-sm">Collect user information</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <input type="checkbox" class="rounded border-gray-300">
                                                    <span class="text-sm">Escalate to human</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Integrations -->
                    <div class="config-section p-6" id="integrations">
                        <div class="max-w-4xl mx-auto space-y-6">
                            <h2 class="text-2xl font-semibold text-gray-900">API Integrations</h2>

                            <!-- Integration Cards -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <!-- WhatsApp API -->
                                <div class="bg-white rounded-lg border border-gray-200 p-6">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-12 h-12 bg-whatsapp rounded-lg flex items-center justify-center">
                                            <i class="fab fa-whatsapp text-white text-xl"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-medium text-gray-900">WhatsApp Business API</h3>
                                            <div class="flex items-center gap-2 mt-1">
                                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                                <span class="text-sm text-green-600">Connected</span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-4">Primary messaging platform integration</p>
                                    <button
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                        Configure
                                    </button>
                                </div>

                                <!-- OpenAI -->
                                <div class="bg-white rounded-lg border border-gray-200 p-6">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-12 h-12 bg-gray-900 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-brain text-white"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-medium text-gray-900">OpenAI GPT</h3>
                                            <div class="flex items-center gap-2 mt-1">
                                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                                <span class="text-sm text-green-600">Connected</span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-4">AI-powered conversation intelligence</p>
                                    <button
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                        Configure
                                    </button>
                                </div>

                                <!-- CRM Integration -->
                                <div class="bg-white rounded-lg border border-gray-200 p-6">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-users text-white"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-medium text-gray-900">CRM System</h3>
                                            <div class="flex items-center gap-2 mt-1">
                                                <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                                <span class="text-sm text-gray-600">Not Connected</span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-4">Customer relationship management</p>
                                    <button
                                        class="w-full px-4 py-2 bg-whatsapp text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                                        Connect
                                    </button>
                                </div>
                            </div>

                            <!-- API Configuration -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h3 class="font-medium text-gray-900">API Configuration</h3>
                                </div>
                                <div class="p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">API
                                                Endpoint</label>
                                            <input type="url" placeholder="https://api.example.com/v1"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">API Key</label>
                                            <div class="relative">
                                                <input type="password" placeholder="Enter API key"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                                <button
                                                    class="absolute right-2 top-2 text-gray-400 hover:text-gray-600">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Request
                                            Headers</label>
                                        <div class="code-editor">
                                            <pre><code>{
  <span class="json-key">"Content-Type"</span>: <span class="json-string">"application/json"</span>,
  <span class="json-key">"Authorization"</span>: <span class="json-string">"Bearer YOUR_API_KEY"</span>,
  <span class="json-key">"User-Agent"</span>: <span class="json-string">"WhatsBot/2.1.0"</span>
}</code></pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Webhooks -->
                    <div class="config-section p-6" id="webhooks">
                        <div class="max-w-4xl mx-auto space-y-6">
                            <div class="flex items-center justify-between">
                                <h2 class="text-2xl font-semibold text-gray-900">Webhook Configuration</h2>
                                <button
                                    class="px-4 py-2 bg-whatsapp text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                                    <i class="fas fa-plus mr-2"></i>
                                    Add Webhook
                                </button>
                            </div>

                            <!-- Webhook List -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h3 class="font-medium text-gray-900">Active Webhooks</h3>
                                </div>
                                <div class="divide-y divide-gray-200">
                                    <div class="p-6 flex items-center justify-between">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Message Received</h4>
                                            <p class="text-sm text-gray-600">
                                                https://your-app.com/webhooks/message-received</p>
                                            <div class="flex items-center gap-2 mt-2">
                                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                                <span class="text-sm text-green-600">Active</span>
                                                <span class="text-sm text-gray-500">• Last triggered 2 minutes
                                                    ago</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button class="p-2 text-gray-400 hover:text-gray-600">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="p-2 text-gray-400 hover:text-red-600">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="p-6 flex items-center justify-between">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Order Status Update</h4>
                                            <p class="text-sm text-gray-600">https://your-app.com/webhooks/order-update
                                            </p>
                                            <div class="flex items-center gap-2 mt-2">
                                                <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                                                <span class="text-sm text-yellow-600">Inactive</span>
                                                <span class="text-sm text-gray-500">• Never triggered</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button class="p-2 text-gray-400 hover:text-gray-600">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="p-2 text-gray-400 hover:text-red-600">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Webhook Configuration Form -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h3 class="font-medium text-gray-900">Webhook Settings</h3>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Webhook URL</label>
                                        <input type="url" placeholder="https://your-app.com/webhook"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Event
                                                Type</label>
                                            <select
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                                <option>message.received</option>
                                                <option>message.sent</option>
                                                <option>message.delivered</option>
                                                <option>message.read</option>
                                                <option>user.joined</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">HTTP
                                                Method</label>
                                            <select
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                                <option>POST</option>
                                                <option>PUT</option>
                                                <option>PATCH</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Secret Key</label>
                                        <input type="password" placeholder="Enter webhook secret"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                    </div>
                                    <div class="flex gap-4">
                                        <button
                                            class="px-4 py-2 bg-whatsapp text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                                            Save Webhook
                                        </button>
                                        <button
                                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                            Test Webhook
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Training Data -->
                    <div class="config-section p-6" id="training">
                        <div class="max-w-4xl mx-auto space-y-6">
                            <h2 class="text-2xl font-semibold text-gray-900">Training Data Management</h2>

                            <!-- Training Stats -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <div class="bg-white rounded-lg border border-gray-200 p-6">
                                    <div class="text-2xl font-bold text-gray-900">2,450</div>
                                    <div class="text-sm text-gray-500">Training Examples</div>
                                </div>
                                <div class="bg-white rounded-lg border border-gray-200 p-6">
                                    <div class="text-2xl font-bold text-gray-900">85%</div>
                                    <div class="text-sm text-gray-500">Accuracy Score</div>
                                </div>
                                <div class="bg-white rounded-lg border border-gray-200 p-6">
                                    <div class="text-2xl font-bold text-gray-900">12</div>
                                    <div class="text-sm text-gray-500">Intent Categories</div>
                                </div>
                                <div class="bg-white rounded-lg border border-gray-200 p-6">
                                    <div class="text-2xl font-bold text-gray-900">v2.1</div>
                                    <div class="text-sm text-gray-500">Model Version</div>
                                </div>
                            </div>

                            <!-- Training Data Upload -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h3 class="font-medium text-gray-900">Upload Training Data</h3>
                                </div>
                                <div class="p-6">
                                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                                        <h4 class="text-lg font-medium text-gray-900 mb-2">Upload CSV or JSON Files</h4>
                                        <p class="text-sm text-gray-500 mb-4">Drag and drop your training data files or
                                            click to browse</p>
                                        <button
                                            class="px-4 py-2 bg-whatsapp text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                                            Choose Files
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Intent Management -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h3 class="font-medium text-gray-900">Intent Categories</h3>
                                </div>
                                <div class="p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        <div class="p-4 border border-gray-200 rounded-lg">
                                            <div class="flex items-center justify-between mb-2">
                                                <h4 class="font-medium text-gray-900">Customer Support</h4>
                                                <span class="text-sm text-gray-500">45%</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-blue-500 h-2 rounded-full" style="inline-size: 45%"></div>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-2">1,102 examples</p>
                                        </div>
                                        <div class="p-4 border border-gray-200 rounded-lg">
                                            <div class="flex items-center justify-between mb-2">
                                                <h4 class="font-medium text-gray-900">Product Inquiry</h4>
                                                <span class="text-sm text-gray-500">30%</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-whatsapp h-2 rounded-full" style="inline-size: 30%"></div>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-2">735 examples</p>
                                        </div>
                                        <div class="p-4 border border-gray-200 rounded-lg">
                                            <div class="flex items-center justify-between mb-2">
                                                <h4 class="font-medium text-gray-900">Order Status</h4>
                                                <span class="text-sm text-gray-500">25%</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-purple-500 h-2 rounded-full" style="inline-size: 25%"></div>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-2">613 examples</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Analytics -->
                    <div class="config-section p-6" id="analytics">
                        <div class="max-w-4xl mx-auto space-y-6">
                            <h2 class="text-2xl font-semibold text-gray-900">Bot Analytics Configuration</h2>

                            <!-- Analytics Settings -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h3 class="font-medium text-gray-900">Tracking Settings</h3>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div
                                        class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Message Analytics</h4>
                                            <p class="text-sm text-gray-600">Track message volume and response times</p>
                                        </div>
                                        <div class="toggle-switch active" data-analytics="messages"></div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900">User Behavior</h4>
                                            <p class="text-sm text-gray-600">Track user interactions and engagement</p>
                                        </div>
                                        <div class="toggle-switch active" data-analytics="behavior"></div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Performance Metrics</h4>
                                            <p class="text-sm text-gray-600">Monitor bot performance and accuracy</p>
                                        </div>
                                        <div class="toggle-switch active" data-analytics="performance"></div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-4 border border-gray-100 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Error Logging</h4>
                                            <p class="text-sm text-gray-600">Log and track system errors</p>
                                        </div>
                                        <div class="toggle-switch active" data-analytics="errors"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Data Retention -->
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h3 class="font-medium text-gray-900">Data Retention</h3>
                                </div>
                                <div class="p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Message Data
                                                Retention (days)</label>
                                            <select
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                                <option>30 days</option>
                                                <option>60 days</option>
                                                <option selected>90 days</option>
                                                <option>180 days</option>
                                                <option>365 days</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Analytics Data
                                                Retention (days)</label>
                                            <select
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp">
                                                <option>90 days</option>
                                                <option>180 days</option>
                                                <option selected>365 days</option>
                                                <option>730 days</option>
                                                <option>Indefinite</option>
                                            </select>
                                        </div>
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
        // Configuration navigation
        document.querySelectorAll('.config-nav-item').forEach(item => {
            item.addEventListener('click', function () {
                const section = this.dataset.section;

                // Remove active class from all nav items
                document.querySelectorAll('.config-nav-item').forEach(nav => {
                    nav.classList.remove('active', 'text-whatsapp', 'bg-green-50');
                    nav.classList.add('text-gray-600');
                });

                // Add active class to clicked nav
                this.classList.add('active', 'text-whatsapp', 'bg-green-50');
                this.classList.remove('text-gray-600');

                // Hide all sections
                document.querySelectorAll('.config-section').forEach(sec => {
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
            toggle.addEventListener('click', function () {
                this.classList.toggle('active');

                // Update associated badge if it exists
                const feature = this.dataset.feature;
                if (feature) {
                    const badge = this.closest('.flex').querySelector('span');
                    if (badge && badge.classList.contains('px-2')) {
                        if (this.classList.contains('active')) {
                            badge.textContent = 'ON';
                            badge.className = 'bg-whatsapp text-white px-2 py-1 rounded-full text-xs font-medium';
                        } else {
                            badge.textContent = 'OFF';
                            badge.className = 'bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs font-medium';
                        }
                    }
                }
            });
        });

        // Save configuration
        document.getElementById('saveConfig').addEventListener('click', function () {
            // Show loading state
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
            this.disabled = true;

            // Simulate save operation
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-check mr-2"></i>Saved!';
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                }, 1000);
            }, 2000);
        });

        // Template editor functionality
        document.getElementById('addTemplate')?.addEventListener('click', function () {
            // Focus on template name input
            const nameInput = document.querySelector('#messages input[placeholder="Enter template name"]');
            if (nameInput) {
                nameInput.focus();
            }
        });

        // Template item selection
        document.querySelectorAll('.template-item').forEach(item => {
            item.addEventListener('click', function () {
                // Remove active class from all templates
                document.querySelectorAll('.template-item').forEach(t => {
                    t.classList.remove('bg-green-50', 'border-whatsapp');
                });

                // Add active class to clicked template
                this.classList.add('bg-green-50', 'border-whatsapp');

                // Load template content (simplified)
                const templateName = this.querySelector('.font-medium').textContent;
                const nameInput = document.querySelector('#messages input[placeholder="Enter template name"]');
                if (nameInput) {
                    nameInput.value = templateName;
                }
            });
        });

        // File upload simulation
        document.querySelector('#training .border-dashed')?.addEventListener('click', function () {
            // Create a temporary file input
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = '.csv,.json';
            fileInput.multiple = true;

            fileInput.addEventListener('change', function () {
                if (this.files.length > 0) {
                    const fileList = Array.from(this.files).map(f => f.name).join(', ');
                    alert(`Files selected: ${fileList}`);
                }
            });

            fileInput.click();
        });

        // Webhook testing
        document.querySelectorAll('button').forEach(button => {
            if (button.textContent.includes('Test Webhook')) {
                button.addEventListener('click', function () {
                    this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Testing...';
                    this.disabled = true;

                    setTimeout(() => {
                        this.innerHTML = '<i class="fas fa-check mr-2"></i>Test Successful';
                        this.classList.add('bg-green-500');
                        this.classList.remove('border-gray-300', 'text-gray-700');

                        setTimeout(() => {
                            this.innerHTML = 'Test Webhook';
                            this.classList.remove('bg-green-500');
                            this.classList.add('border-gray-300', 'text-gray-700');
                            this.disabled = false;
                        }, 2000);
                    }, 3000);
                });
            }
        });

        // Initialize first section as active
        document.addEventListener('DOMContentLoaded', function () {
            const firstSection = document.getElementById('general');
            if (firstSection) {
                firstSection.classList.add('active');
            }
        });
    </script>
</body>

</html>