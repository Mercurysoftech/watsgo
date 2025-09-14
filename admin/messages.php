<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsBot Messages - Admin Dashboard</title>
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
        .conversation-item:hover {
            background-color: #f9fafb;
        }
        .conversation-item.active {
            background-color: #e8f5e8;
            border-inline-start: 4px solid #25D366;
        }
        .message-bubble {
            max-inline-size: 80%;
            word-wrap: break-word;
        }
        .message-bubble.sent {
            background-color: #DCF8C6;
            margin-inline-start: auto;
        }
        .message-bubble.received {
            background-color: #ffffff;
        }
        .emoji-picker {
            display: none;
            position: absolute;
            inset-block-end: 100%;
            inset-inline-end: 0;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            z-index: 10;
        }
        .emoji-picker.show {
            display: block;
        }
        .scroll-smooth {
            scroll-behavior: smooth;
        }
        .typing-indicator {
            display: inline-flex;
            align-items: center;
        }
        .typing-indicator span {
            block-size: 8px;
            inline-size: 8px;
            border-radius: 50%;
            background-color: #9ca3af;
            margin: 0 1px;
            animation: typing 1.4s infinite ease-in-out;
        }
        .typing-indicator span:nth-child(1) { animation-delay: -0.32s; }
        .typing-indicator span:nth-child(2) { animation-delay: -0.16s; }
        @keyframes typing {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1); }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <?php include './includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="ml-64 p-6 flex-1 flex">
            <!-- Conversations List -->
            <div class="w-80 bg-white border-r border-gray-200 flex flex-col">
                <!-- Messages Header -->
                <div class="p-4 border-b border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <h1 class="text-xl font-semibold text-gray-900">Messages</h1>
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Search -->
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input 
                            type="text" 
                            placeholder="Search conversations..." 
                            class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-whatsapp"
                            id="conversationSearch"
                        >
                    </div>
                </div>

                <!-- Filter Tabs -->
                <div class="flex border-b border-gray-200">
                    <button class="filter-tab active flex-1 px-4 py-3 text-sm font-medium text-center border-b-2 border-whatsapp text-whatsapp" data-filter="all">
                        All (24)
                    </button>
                    <button class="filter-tab flex-1 px-4 py-3 text-sm font-medium text-center border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-filter="unread">
                        Unread (8)
                    </button>
                    <button class="filter-tab flex-1 px-4 py-3 text-sm font-medium text-center border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-filter="archived">
                        Archived
                    </button>
                </div>

                <!-- Conversations List -->
                <div class="flex-1 overflow-y-auto">
                    <!-- Conversation 1 - Active -->
                    <div class="conversation-item active p-4 border-b border-gray-100 cursor-pointer" data-conversation="john-doe">
                        <div class="flex items-start gap-3">
                            <div class="relative">
                                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-medium">JD</span>
                                </div>
                                <div class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 border-2 border-white rounded-full"></div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <h3 class="font-medium text-gray-900 truncate">John Doe</h3>
                                    <span class="text-xs text-gray-500">2m</span>
                                </div>
                                <p class="text-sm text-gray-600 truncate">Hi, I need help with my order #12345</p>
                                <div class="flex items-center justify-between mt-2">
                                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">Support</span>
                                    <div class="w-2 h-2 bg-whatsapp rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Conversation 2 -->
                    <div class="conversation-item p-4 border-b border-gray-100 cursor-pointer" data-conversation="sarah-wilson">
                        <div class="flex items-start gap-3">
                            <div class="relative">
                                <div class="w-12 h-12 bg-pink-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-medium">SW</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <h3 class="font-medium text-gray-900 truncate">Sarah Wilson</h3>
                                    <span class="text-xs text-gray-500">5m</span>
                                </div>
                                <p class="text-sm text-gray-600 truncate">What are your business hours?</p>
                                <div class="flex items-center justify-between mt-2">
                                    <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded-full">Info</span>
                                    <div class="w-2 h-2 bg-orange-500 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Conversation 3 -->
                    <div class="conversation-item p-4 border-b border-gray-100 cursor-pointer" data-conversation="mike-johnson">
                        <div class="flex items-start gap-3">
                            <div class="relative">
                                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-medium">MJ</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <h3 class="font-medium text-gray-900 truncate">Mike Johnson</h3>
                                    <span class="text-xs text-gray-500">8m</span>
                                </div>
                                <p class="text-sm text-gray-600 truncate">I'm interested in your premium package</p>
                                <div class="flex items-center justify-between mt-2">
                                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Sales</span>
                                    <i class="fas fa-check-double text-whatsapp text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Conversation 4 -->
                    <div class="conversation-item p-4 border-b border-gray-100 cursor-pointer" data-conversation="emma-davis">
                        <div class="flex items-start gap-3">
                            <div class="relative">
                                <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-medium">ED</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <h3 class="font-medium text-gray-900 truncate">Emma Davis</h3>
                                    <span class="text-xs text-gray-500">12m</span>
                                </div>
                                <p class="text-sm text-gray-600 truncate">The app is not working properly</p>
                                <div class="flex items-center justify-between mt-2">
                                    <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">Technical</span>
                                    <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Conversation 5 -->
                    <div class="conversation-item p-4 border-b border-gray-100 cursor-pointer" data-conversation="david-brown">
                        <div class="flex items-start gap-3">
                            <div class="relative">
                                <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-medium">DB</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <h3 class="font-medium text-gray-900 truncate">David Brown</h3>
                                    <span class="text-xs text-gray-500">15m</span>
                                </div>
                                <p class="text-sm text-gray-600 truncate">Thank you for the quick response!</p>
                                <div class="flex items-center justify-between mt-2">
                                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">Support</span>
                                    <i class="fas fa-check-double text-whatsapp text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chat Area -->
            <div class="flex-1 flex flex-col">
                <!-- Chat Header -->
                <div class="bg-white border-b border-gray-200 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                <span class="text-white font-medium">JD</span>
                            </div>
                            <div>
                                <h2 class="font-semibold text-gray-900">John Doe</h2>
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                    <span class="text-sm text-gray-500">Online • Last seen 2 minutes ago</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                <i class="fas fa-phone"></i>
                            </button>
                            <button class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                <i class="fas fa-video"></i>
                            </button>
                            <button class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                <i class="fas fa-info-circle"></i>
                            </button>
                            <button class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Messages Area -->
                <div class="flex-1 bg-gray-50 overflow-y-auto p-4 scroll-smooth" id="messagesContainer">
                    <!-- Date Separator -->
                    <div class="flex justify-center mb-4">
                        <span class="bg-white px-3 py-1 rounded-full text-sm text-gray-500 border">Today</span>
                    </div>

                    <!-- Received Message -->
                    <div class="mb-4">
                        <div class="flex items-start gap-2">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-white text-xs font-medium">JD</span>
                            </div>
                            <div class="message-bubble received bg-white p-3 rounded-lg shadow-sm border">
                                <p class="text-gray-800">Hi there! I need help with my order #12345. It was supposed to arrive yesterday but I haven't received it yet.</p>
                                <div class="flex items-center justify-between mt-2">
                                    <span class="text-xs text-gray-500">10:30 AM</span>
                                    <button class="text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-reply text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sent Message (Bot Response) -->
                    <div class="mb-4">
                        <div class="flex justify-end">
                            <div class="message-bubble sent bg-whatsapp-light p-3 rounded-lg shadow-sm">
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="fas fa-robot text-whatsapp text-sm"></i>
                                    <span class="text-sm font-medium text-gray-700">Bot Response</span>
                                </div>
                                <p class="text-gray-800">Hello! I understand you're concerned about your order #12345. Let me check the status for you right away. Please hold on for a moment.</p>
                                <div class="flex items-center justify-between mt-2">
                                    <span class="text-xs text-gray-500">10:31 AM</span>
                                    <i class="fas fa-check-double text-whatsapp text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sent Message (Bot Response with Order Info) -->
                    <div class="mb-4">
                        <div class="flex justify-end">
                            <div class="message-bubble sent bg-whatsapp-light p-3 rounded-lg shadow-sm">
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="fas fa-robot text-whatsapp text-sm"></i>
                                    <span class="text-sm font-medium text-gray-700">Bot Response</span>
                                </div>
                                <p class="text-gray-800 mb-3">Great news! I found your order details:</p>
                                <div class="bg-white/50 p-3 rounded-lg border border-white/20">
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Order #:</span>
                                            <span class="text-sm font-medium">12345</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Status:</span>
                                            <span class="text-sm font-medium text-orange-600">In Transit</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Expected:</span>
                                            <span class="text-sm font-medium">Today, 2:00 PM</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Tracking:</span>
                                            <span class="text-sm font-medium text-blue-600">TR123456789</span>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-gray-800 mt-3">Your package is currently out for delivery and should arrive by 2:00 PM today. Would you like me to connect you with a human agent for further assistance?</p>
                                <div class="flex items-center justify-between mt-2">
                                    <span class="text-xs text-gray-500">10:32 AM</span>
                                    <i class="fas fa-check-double text-whatsapp text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Received Message -->
                    <div class="mb-4">
                        <div class="flex items-start gap-2">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-white text-xs font-medium">JD</span>
                            </div>
                            <div class="message-bubble received bg-white p-3 rounded-lg shadow-sm border">
                                <p class="text-gray-800">Perfect! Thank you so much for the quick response. I'll wait for the delivery then.</p>
                                <div class="flex items-center justify-between mt-2">
                                    <span class="text-xs text-gray-500">10:33 AM</span>
                                    <button class="text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-reply text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Typing Indicator -->
                    <div class="mb-4" id="typingIndicator" style="display: none;">
                        <div class="flex items-start gap-2">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-white text-xs font-medium">JD</span>
                            </div>
                            <div class="bg-white p-3 rounded-lg shadow-sm border">
                                <div class="typing-indicator">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Message Input -->
                <div class="bg-white border-t border-gray-200 p-4">
                    <div class="flex items-end gap-3">
                        <!-- Attachment Button -->
                        <button class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                            <i class="fas fa-paperclip"></i>
                        </button>

                        <!-- Message Input Container -->
                        <div class="flex-1 relative">
                            <textarea 
                                id="messageInput"
                                placeholder="Type a message..." 
                                class="w-full p-3 pr-20 border border-gray-300 rounded-lg resize-none focus:outline-none focus:ring-2 focus:ring-whatsapp"
                                rows="1"
                                style="min-block-size: 44px; max-block-size: 120px;"
                            ></textarea>
                            
                            <!-- Input Actions -->
                            <div class="absolute right-2 top-1/2 transform -translate-y-1/2 flex items-center gap-2">
                                <button class="p-1 text-gray-600 hover:text-gray-900" id="emojiButton">
                                    <i class="far fa-smile"></i>
                                </button>
                                <div class="emoji-picker" id="emojiPicker">
                                    <div class="grid grid-cols-8 gap-1">
                                        <button class="emoji-btn p-1 hover:bg-gray-100 rounded">😀</button>
                                        <button class="emoji-btn p-1 hover:bg-gray-100 rounded">😃</button>
                                        <button class="emoji-btn p-1 hover:bg-gray-100 rounded">😄</button>
                                        <button class="emoji-btn p-1 hover:bg-gray-100 rounded">😁</button>
                                        <button class="emoji-btn p-1 hover:bg-gray-100 rounded">😆</button>
                                        <button class="emoji-btn p-1 hover:bg-gray-100 rounded">😅</button>
                                        <button class="emoji-btn p-1 hover:bg-gray-100 rounded">😂</button>
                                        <button class="emoji-btn p-1 hover:bg-gray-100 rounded">🤣</button>
                                        <button class="emoji-btn p-1 hover:bg-gray-100 rounded">❤️</button>
                                        <button class="emoji-btn p-1 hover:bg-gray-100 rounded">👍</button>
                                        <button class="emoji-btn p-1 hover:bg-gray-100 rounded">👎</button>
                                        <button class="emoji-btn p-1 hover:bg-gray-100 rounded">🙏</button>
                                        <button class="emoji-btn p-1 hover:bg-gray-100 rounded">💯</button>
                                        <button class="emoji-btn p-1 hover:bg-gray-100 rounded">🎉</button>
                                        <button class="emoji-btn p-1 hover:bg-gray-100 rounded">🔥</button>
                                        <button class="emoji-btn p-1 hover:bg-gray-100 rounded">⭐</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Send Button -->
                        <button 
                            id="sendButton"
                            class="bg-whatsapp text-white p-3 rounded-lg hover:bg-whatsapp-dark transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <i class="fas fa-paper-plane"></i>
                        </button>

                        <!-- Voice Message Button -->
                        <button class="p-3 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                            <i class="fas fa-microphone"></i>
                        </button>
                    </div>

                    <!-- Quick Responses -->
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button class="quick-response px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-gray-200">
                            Thanks for your message!
                        </button>
                        <button class="quick-response px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-gray-200">
                            Let me check that for you
                        </button>
                        <button class="quick-response px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-gray-200">
                            I'll transfer you to an agent
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar - Contact Info -->
            <div class="w-80 bg-white border-l border-gray-200 overflow-y-auto">
                <!-- Contact Header -->
                <div class="p-6 border-b border-gray-200 text-center">
                    <div class="w-20 h-20 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white text-xl font-medium">JD</span>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900">John Doe</h2>
                    <p class="text-sm text-gray-500">+1 234 567 8901</p>
                    <div class="flex items-center justify-center gap-2 mt-2">
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                        <span class="text-sm text-green-600">Online</span>
                    </div>
                </div>

                <!-- Contact Actions -->
                <div class="p-4 border-b border-gray-200">
                    <div class="grid grid-cols-2 gap-3">
                        <button class="flex flex-col items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                            <i class="fas fa-phone text-whatsapp mb-1"></i>
                            <span class="text-sm text-gray-700">Call</span>
                        </button>
                        <button class="flex flex-col items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                            <i class="fas fa-video text-whatsapp mb-1"></i>
                            <span class="text-sm text-gray-700">Video</span>
                        </button>
                        <button class="flex flex-col items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                            <i class="fas fa-user-plus text-whatsapp mb-1"></i>
                            <span class="text-sm text-gray-700">Add Contact</span>
                        </button>
                        <button class="flex flex-col items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                            <i class="fas fa-ban text-red-500 mb-1"></i>
                            <span class="text-sm text-gray-700">Block</span>
                        </button>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="p-4 border-b border-gray-200">
                    <h3 class="font-medium text-gray-900 mb-3">Contact Information</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm text-gray-500">Name</label>
                            <p class="text-sm font-medium text-gray-900">John Doe</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-500">Phone</label>
                            <p class="text-sm font-medium text-gray-900">+1 234 567 8901</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-500">Email</label>
                            <p class="text-sm font-medium text-gray-900">john.doe@email.com</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-500">Location</label>
                            <p class="text-sm font-medium text-gray-900">New York, USA</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-500">Tags</label>
                            <div class="flex flex-wrap gap-1 mt-1">
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">Customer</span>
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">Premium</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Conversation History -->
                <div class="p-4 border-b border-gray-200">
                    <h3 class="font-medium text-gray-900 mb-3">Recent Activity</h3>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-comment text-blue-500"></i>
                            <div class="flex-1">
                                <p class="text-sm text-gray-900">Started conversation</p>
                                <p class="text-xs text-gray-500">2 minutes ago</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <i class="fas fa-shopping-cart text-green-500"></i>
                            <div class="flex-1">
                                <p class="text-sm text-gray-900">Placed order #12345</p>
                                <p class="text-xs text-gray-500">2 days ago</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <i class="fas fa-user-plus text-purple-500"></i>
                            <div class="flex-1">
                                <p class="text-sm text-gray-900">Registered account</p>
                                <p class="text-xs text-gray-500">1 week ago</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Notes -->
                <div class="p-4">
                    <h3 class="font-medium text-gray-900 mb-3">Notes</h3>
                    <textarea 
                        placeholder="Add notes about this contact..."
                        class="w-full p-3 border border-gray-300 rounded-lg text-sm resize-none focus:outline-none focus:ring-2 focus:ring-whatsapp"
                        rows="3"
                    ></textarea>
                    <button class="w-full mt-2 bg-whatsapp text-white py-2 rounded-lg hover:bg-whatsapp-dark transition-colors">
                        Save Note
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-resize textarea
        const messageInput = document.getElementById('messageInput');
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });

        // Send message functionality
        const sendButton = document.getElementById('sendButton');
        sendButton.addEventListener('click', sendMessage);
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        function sendMessage() {
            const message = messageInput.value.trim();
            if (!message) return;

            // Add message to chat
            const messagesContainer = document.getElementById('messagesContainer');
            const messageElement = createMessageElement(message, true);
            messagesContainer.appendChild(messageElement);
            
            // Clear input
            messageInput.value = '';
            messageInput.style.height = 'auto';
            
            // Scroll to bottom
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            
            // Simulate bot response after a delay
            setTimeout(() => {
                showTypingIndicator();
                setTimeout(() => {
                    hideTypingIndicator();
                    const botResponse = "Thank you for your message! I'll help you with that right away.";
                    const botMessageElement = createMessageElement(botResponse, false, true);
                    messagesContainer.appendChild(botMessageElement);
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }, 2000);
            }, 500);
        }

        function createMessageElement(text, isSent, isBot = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'mb-4';
            
            const now = new Date();
            const time = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            
            if (isSent) {
                messageDiv.innerHTML = `
                    <div class="flex justify-end">
                        <div class="message-bubble sent bg-whatsapp-light p-3 rounded-lg shadow-sm">
                            ${isBot ? `
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="fas fa-user text-blue-500 text-sm"></i>
                                    <span class="text-sm font-medium text-gray-700">You</span>
                                </div>
                            ` : ''}
                            <p class="text-gray-800">${text}</p>
                            <div class="flex items-center justify-between mt-2">
                                <span class="text-xs text-gray-500">${time}</span>
                                <i class="fas fa-check-double text-whatsapp text-xs"></i>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                messageDiv.innerHTML = `
                    <div class="flex justify-end">
                        <div class="message-bubble sent bg-whatsapp-light p-3 rounded-lg shadow-sm">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fas fa-robot text-whatsapp text-sm"></i>
                                <span class="text-sm font-medium text-gray-700">Bot Response</span>
                            </div>
                            <p class="text-gray-800">${text}</p>
                            <div class="flex items-center justify-between mt-2">
                                <span class="text-xs text-gray-500">${time}</span>
                                <i class="fas fa-check-double text-whatsapp text-xs"></i>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            return messageDiv;
        }

        function showTypingIndicator() {
            document.getElementById('typingIndicator').style.display = 'block';
            const messagesContainer = document.getElementById('messagesContainer');
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function hideTypingIndicator() {
            document.getElementById('typingIndicator').style.display = 'none';
        }

        // Emoji picker functionality
        const emojiButton = document.getElementById('emojiButton');
        const emojiPicker = document.getElementById('emojiPicker');
        
        emojiButton.addEventListener('click', function(e) {
            e.stopPropagation();
            emojiPicker.classList.toggle('show');
        });

        document.addEventListener('click', function() {
            emojiPicker.classList.remove('show');
        });

        // Emoji selection
        document.querySelectorAll('.emoji-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                messageInput.value += this.textContent;
                messageInput.focus();
                emojiPicker.classList.remove('show');
            });
        });

        // Quick responses
        document.querySelectorAll('.quick-response').forEach(btn => {
            btn.addEventListener('click', function() {
                messageInput.value = this.textContent;
                messageInput.focus();
            });
        });

        // Conversation switching
        document.querySelectorAll('.conversation-item').forEach(item => {
            item.addEventListener('click', function() {
                // Remove active class from all conversations
                document.querySelectorAll('.conversation-item').forEach(conv => {
                    conv.classList.remove('active');
                });
                
                // Add active class to clicked conversation
                this.classList.add('active');
                
                // Update chat header (simplified)
                const name = this.querySelector('h3').textContent;
                const chatHeader = document.querySelector('.bg-white.border-b.border-gray-200.p-4 h2');
                if (chatHeader) {
                    chatHeader.textContent = name;
                }
            });
        });

        // Filter tabs
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                document.querySelectorAll('.filter-tab').forEach(t => {
                    t.classList.remove('active', 'border-whatsapp', 'text-whatsapp');
                    t.classList.add('border-transparent', 'text-gray-500');
                });
                
                // Add active class to clicked tab
                this.classList.add('active', 'border-whatsapp', 'text-whatsapp');
                this.classList.remove('border-transparent', 'text-gray-500');
            });
        });

        // Search functionality
        const conversationSearch = document.getElementById('conversationSearch');
        conversationSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            document.querySelectorAll('.conversation-item').forEach(item => {
                const name = item.querySelector('h3').textContent.toLowerCase();
                const message = item.querySelector('p').textContent.toLowerCase();
                
                if (name.includes(searchTerm) || message.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Auto-scroll to bottom on page load
        const messagesContainer = document.getElementById('messagesContainer');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    </script>
</body>
</html>