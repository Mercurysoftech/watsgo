<?php
/**
 * WhatsApp Template Library Dashboard
 * Displays and manages WhatsApp message templates
 */

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/config.php';

// Authentication and user setup
$user = require_login();
$user_id = $user['id'];

/**
 * Fetch user WhatsApp credentials from database
 */
function getUserWhatsAppCredentials($user_id)
{
    $conn = db();
    $sql = "SELECT com_access_token, wa_phone_id, wa_waba_id FROM users WHERE id = :id LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([":id" => $user_id]);

    $userCreds = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userCreds) {
        throw new Exception("WhatsApp credentials not found for user_id = $user_id");
    }

    return $userCreds;
}

/**
 * Fetch templates from WhatsApp Cloud API
 */
function fetchWhatsAppTemplates($wabaId, $accessToken)
{
    $url = "https://graph.facebook.com/v20.0/{$wabaId}/message_templates?access_token={$accessToken}";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception("cURL Error: " . $error);
    }

    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception("API returned HTTP code: " . $httpCode);
    }

    $data = json_decode($response, true);

    if (isset($data['error'])) {
        $err = $data['error'];
        throw new Exception("API Error ({$err['code']}): {$err['message']}");
    }

    return $data;
}

/**
 * Parse template components
 */
function parseTemplateComponents($components)
{
    $parsed = [
        'body' => '',
        'footer' => '',
        'buttons' => []
    ];

    if (empty($components)) {
        return $parsed;
    }

    foreach ($components as $component) {
        switch ($component['type']) {
            case 'BODY':
                $parsed['body'] = $component['text'] ?? '';
                break;
            case 'FOOTER':
                $parsed['footer'] = $component['text'] ?? '';
                break;
            case 'BUTTONS':
                $parsed['buttons'] = $component['buttons'] ?? [];
                break;
        }
    }

    return $parsed;
}

/**
 * Render template card HTML
 */
function renderTemplateCard($template)
{
    $components = parseTemplateComponents($template['components'] ?? []);

    $name = h($template['name']);
    $language = h($template['language']);
    $category = h($template['category']);
    $status = h($template['status']);
    $body = h($components['body']);
    $footer = h($components['footer']);

    ob_start();
    ?>
    <div class='bg-white shadow rounded-xl p-4 flex flex-col justify-between border border-gray-200 card-template'>
        <div class='text'>
            <h3 class='text-lg font-semibold text-gray-800 mb-2'><?= $name ?> (<?= $language ?>)</h3>
            <p class='text-sm text-gray-500 mb-2'>Category: <?= $category ?> | Status: <?= $status ?></p>

            <!-- WhatsApp Message Preview -->
            <div class='bg-gray-50 border rounded-lg p-3 text-gray-700 mb-3'>
                <?= nl2br($body) ?>
            </div>

            <?php if (!empty($footer)): ?>
                <div class='text-xs text-gray-500 italic mb-2'><?= $footer ?></div>
            <?php endif; ?>

            <?php if (!empty($components['buttons'])): ?>
                <div class='flex gap-2 mb-3 verify'>
                    <?php foreach ($components['buttons'] as $btn): ?>
                        <button class='px-3 py-1 rounded-lg border bg-blue-50 text-blue-600 text-sm'>
                            <?= h($btn['text']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Send Form -->
        <button class='w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg shadow send-btn open-wa-modal'
            data-template-name="<?= $name ?>" data-template-body="<?= $body ?>">
            Send Message
        </button>
    </div>
    <?php
    return ob_get_clean();
}

// Main execution
try {
    $userCreds = getUserWhatsAppCredentials($user_id);

    $accessToken = $userCreds['com_access_token'] ?? null;
    $phoneNumberId = $userCreds['wa_phone_id'] ?? null;
    $wabaId = $userCreds['wa_waba_id'] ?? null;

    if (empty($accessToken) || empty($wabaId)) {
        throw new Exception("Missing required credentials (WABA ID or Access Token). Please update your account settings.");
    }

    $templatesData = fetchWhatsAppTemplates($wabaId, $accessToken);
    $templates = $templatesData['data'] ?? [];

} catch (Exception $e) {
    // Log error for debugging
    error_log("WhatsApp Template Error: " . $e->getMessage());
    $errorMessage = $e->getMessage();
    $templates = [];
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BotWave - Admin Dashboard</title>
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
                            <h1 class="text-xl font-semibold text-gray-900">WhatsApp Templates</h1>
                            <p class="text-sm text-gray-500">Configure your WhatsApp bot templates and messages</p>
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


                <!-- Main Configuration Area -->
                <div class="flex-1 overflow-y-auto">
                    <!-- General Settings -->
                    <div class="config-section active p-6" id="general">
                        
                        <?php if (isset($errorMessage)): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle"></i>
                                <?= h($errorMessage) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Search and Filter Section -->
                        <div class="filters-section mb-4">
                            <div class="search-container">
                                <input type="text" id="template-search" placeholder="Search templates..."
                                    class="form-control">
                            </div>
                        </div>

                        <!-- Template Library Section -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6" id="templates-container">
                            <?php if (!empty($templates)): ?>
                                <?php foreach ($templates as $template): ?>
                                    <?= renderTemplateCard($template) ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-templates">
                                    <i class="bi bi-collection"></i>
                                    <h3>No Templates Found</h3>
                                    <p>Create templates in your WhatsApp Business account to see them here.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Message Templates -->
                    <!-- <div class="config-section p-6" id="messages">
                        <div class="max-w-4xl mx-auto space-y-6">
                            <div class="flex items-center justify-between">
                                <h2 class="text-2xl font-semibold text-gray-900">Message Templates</h2>
                                <button
                                    class="px-4 py-2 bg-whatsapp text-white rounded-lg hover:bg-whatsapp-dark transition-colors"
                                    id="addTemplate">
                                    <i class="fas fa-plus mr-2"></i>
                                    Add Template
                                </button>
                            </div>

                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
                    </div> -->

                    <!-- Workflows -->
                    <!-- <div class="config-section p-6" id="workflows">
                        <div class="max-w-4xl mx-auto space-y-6">
                            <div class="flex items-center justify-between">
                                <h2 class="text-2xl font-semibold text-gray-900">Conversation Workflows</h2>
                                <button
                                    class="px-4 py-2 bg-whatsapp text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                                    <i class="fas fa-plus mr-2"></i>
                                    Create Workflow
                                </button>
                            </div>

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
                    </div> -->

                    <!-- Integrations -->
                    <!-- <div class="config-section p-6" id="integrations">
                        <div class="max-w-4xl mx-auto space-y-6">
                            <h2 class="text-2xl font-semibold text-gray-900">API Integrations</h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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
                    </div> -->

                    <!-- Webhooks -->
                    <!-- <div class="config-section p-6" id="webhooks">
                        <div class="max-w-4xl mx-auto space-y-6">
                            <div class="flex items-center justify-between">
                                <h2 class="text-2xl font-semibold text-gray-900">Webhook Configuration</h2>
                                <button
                                    class="px-4 py-2 bg-whatsapp text-white rounded-lg hover:bg-whatsapp-dark transition-colors">
                                    <i class="fas fa-plus mr-2"></i>
                                    Add Webhook
                                </button>
                            </div>

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
                    </div> -->

                    <!-- Training Data -->
                    <!-- <div class="config-section p-6" id="training">
                        <div class="max-w-4xl mx-auto space-y-6">
                            <h2 class="text-2xl font-semibold text-gray-900">Training Data Management</h2>

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
                                                <div class="bg-blue-500 h-2 rounded-full" style="inline-size: 45%">
                                                </div>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-2">1,102 examples</p>
                                        </div>
                                        <div class="p-4 border border-gray-200 rounded-lg">
                                            <div class="flex items-center justify-between mb-2">
                                                <h4 class="font-medium text-gray-900">Product Inquiry</h4>
                                                <span class="text-sm text-gray-500">30%</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-whatsapp h-2 rounded-full" style="inline-size: 30%">
                                                </div>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-2">735 examples</p>
                                        </div>
                                        <div class="p-4 border border-gray-200 rounded-lg">
                                            <div class="flex items-center justify-between mb-2">
                                                <h4 class="font-medium text-gray-900">Order Status</h4>
                                                <span class="text-sm text-gray-500">25%</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-purple-500 h-2 rounded-full" style="inline-size: 25%">
                                                </div>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-2">613 examples</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> -->

                    <!-- Analytics -->
                    <!-- <div class="config-section p-6" id="analytics">
                        <div class="max-w-4xl mx-auto space-y-6">
                            <h2 class="text-2xl font-semibold text-gray-900">Bot Analytics Configuration</h2>

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
                    </div> -->
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/dashboard.js"></script>
    <script src="assets/js/wa-template.js"></script>
    <script>
        /**
 * WhatsApp Template Management JavaScript
 * Handles modal interactions, template filtering, and message sending
 */

        class WhatsAppTemplateManager {
            constructor() {
                this.modal = document.getElementById('wa-modal');
                this.modalContent = this.modal?.querySelector('.modal-content');
                this.searchInput = document.getElementById('template-search');
                this.templatesContainer = document.getElementById('templates-container');

                this.init();
            }

            init() {
                this.bindEvents();
                this.setupSearch();
            }

            bindEvents() {
                // Modal open buttons
                document.addEventListener('click', (e) => {
                    if (e.target.matches('.open-wa-modal') || e.target.closest('.open-wa-modal')) {
                        const button = e.target.matches('.open-wa-modal') ? e.target : e.target.closest('.open-wa-modal');
                        this.openModal(button);
                    }
                });

                // Modal close button
                const closeBtn = document.getElementById('close-wa-modal');
                closeBtn?.addEventListener('click', () => this.closeModal());

                // Close modal when clicking overlay
                this.modal?.addEventListener('click', (e) => {
                    if (e.target === this.modal) {
                        this.closeModal();
                    }
                });

                // Form submission
                const form = document.getElementById('send-message-form');
                form?.addEventListener('submit', (e) => this.handleFormSubmit(e));

                // Add contact button
                const addContactBtn = document.getElementById('add-contact-btn');
                addContactBtn?.addEventListener('click', () => this.handleAddContact());

                // Escape key to close modal
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && !this.modal.classList.contains('hidden')) {
                        this.closeModal();
                    }
                });
            }

            setupSearch() {
                if (!this.searchInput) return;

                this.searchInput.addEventListener('input', (e) => {
                    this.filterTemplates(e.target.value);
                });
            }

            openModal(button) {
                if (!this.modal) return;

                // Get template data from button attributes
                const templateName = button.dataset.templateName || '';
                const templateBody = button.dataset.templateBody || '';

                // Populate form
                const templateNameInput = document.getElementById('template-name');
                const messageTextArea = document.getElementById('message-text');

                if (templateNameInput) templateNameInput.value = templateName;
                if (messageTextArea) messageTextArea.value = templateBody;

                // Show modal with animation
                this.modal.classList.remove('hidden');

                // Trigger animation
                requestAnimationFrame(() => {
                    this.modalContent?.classList.add('modal-show');
                });

                // Focus on phone number input
                const phoneInput = document.getElementById('phone-number');
                setTimeout(() => phoneInput?.focus(), 100);

                // Prevent body scroll
                document.body.style.overflow = 'hidden';
            }

            closeModal() {
                if (!this.modal) return;

                // Hide modal with animation
                this.modalContent?.classList.remove('modal-show');

                setTimeout(() => {
                    this.modal.classList.add('hidden');
                    this.resetForm();
                }, 200);

                // Restore body scroll
                document.body.style.overflow = '';
            }

            resetForm() {
                const form = document.getElementById('send-message-form');
                form?.reset();
            }

            filterTemplates(searchTerm) {
                if (!this.templatesContainer) return;

                const templates = this.templatesContainer.querySelectorAll('.card-template');
                const term = searchTerm.toLowerCase().trim();

                templates.forEach(template => {
                    const templateName = template.querySelector('h3')?.textContent.toLowerCase() || '';
                    const templateCategory = template.querySelector('.text-gray-500')?.textContent.toLowerCase() || '';
                    const templateBody = template.querySelector('.bg-gray-50')?.textContent.toLowerCase() || '';

                    const matches = templateName.includes(term) ||
                        templateCategory.includes(term) ||
                        templateBody.includes(term);

                    template.style.display = matches ? 'block' : 'none';
                });

                // Show/hide no results message
                this.toggleNoResultsMessage(term);
            }

            toggleNoResultsMessage(searchTerm) {
                const visibleTemplates = this.templatesContainer.querySelectorAll('.card-template[style*="block"], .card-template:not([style*="none"])');
                let noResultsEl = this.templatesContainer.querySelector('.no-search-results');

                if (searchTerm && visibleTemplates.length === 0) {
                    if (!noResultsEl) {
                        noResultsEl = document.createElement('div');
                        noResultsEl.className = 'no-search-results text-center py-8';
                        noResultsEl.innerHTML = `
                    <i class="bi bi-search text-4xl text-gray-400 mb-3"></i>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No templates found</h3>
                    <p class="text-gray-500">Try adjusting your search terms</p>
                `;
                        this.templatesContainer.appendChild(noResultsEl);
                    }
                    noResultsEl.style.display = 'block';
                } else if (noResultsEl) {
                    noResultsEl.style.display = 'none';
                }
            }

            async handleFormSubmit(e) {
                e.preventDefault();

                const formData = new FormData(e.target);
                const submitBtn = e.target.querySelector('button[type="submit"]');

                // Show loading state
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Sending...';
                submitBtn.disabled = true;

                try {
                    const response = await fetch('api/send-whatsapp-message.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.showNotification('Message sent successfully!', 'success');
                        this.closeModal();
                    } else {
                        this.showNotification(result.error || 'Failed to send message', 'error');
                    }
                } catch (error) {
                    console.error('Send message error:', error);
                    this.showNotification('Network error. Please try again.', 'error');
                } finally {
                    // Reset button
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            }

            async handleAddContact() {
                const phoneNumber = document.getElementById('phone-number')?.value;

                if (!phoneNumber) {
                    this.showNotification('Please enter a phone number first', 'warning');
                    return;
                }

                const addContactBtn = document.getElementById('add-contact-btn');
                const originalText = addContactBtn.innerHTML;
                addContactBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Adding...';
                addContactBtn.disabled = true;

                try {
                    const formData = new FormData();
                    formData.append('phone_number', phoneNumber);

                    const response = await fetch('api/add-contact.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.showNotification('Contact added successfully!', 'success');
                    } else {
                        this.showNotification(result.error || 'Failed to add contact', 'error');
                    }
                } catch (error) {
                    console.error('Add contact error:', error);
                    this.showNotification('Network error. Please try again.', 'error');
                } finally {
                    // Reset button
                    addContactBtn.innerHTML = originalText;
                    addContactBtn.disabled = false;
                }
            }

            showNotification(message, type = 'info') {
                // Create notification element
                const notification = document.createElement('div');
                notification.className = `notification notification-${type}`;
                notification.innerHTML = `
            <div class="notification-content">
                <i class="bi bi-${this.getNotificationIcon(type)}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close">&times;</button>
        `;

                // Add to page
                document.body.appendChild(notification);

                // Show notification
                requestAnimationFrame(() => {
                    notification.classList.add('show');
                });

                // Auto remove after 5 seconds
                const removeNotification = () => {
                    notification.classList.remove('show');
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                };

                setTimeout(removeNotification, 5000);

                // Manual close
                notification.querySelector('.notification-close').addEventListener('click', removeNotification);
            }

            getNotificationIcon(type) {
                const icons = {
                    success: 'check-circle',
                    error: 'exclamation-circle',
                    warning: 'exclamation-triangle',
                    info: 'info-circle'
                };
                return icons[type] || icons.info;
            }
        }

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            new WhatsAppTemplateManager();
        });

        // Utility functions for backwards compatibility
        function filterTemplates() {
            const searchInput = document.getElementById('template-search');
            if (searchInput && window.whatsAppManager) {
                window.whatsAppManager.filterTemplates(searchInput.value);
            }
        }

        // Store instance globally for backwards compatibility
        document.addEventListener('DOMContentLoaded', () => {
            window.whatsAppManager = new WhatsAppTemplateManager();
        });
    </script>
</body>

</html>