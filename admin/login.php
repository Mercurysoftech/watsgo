<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/db.php';

session_start();

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'register') {
        // sanitize inputs
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $whatsapp = preg_replace('/\D+/', '', $_POST['whatsapp'] ?? '');

        // call your custom function
        $u = register_user($name, $email, $password, $whatsapp);

        if ($u) {
            $_SESSION['user'] = $u;
            header('Location: /whatsapp_sales/admin/index.php');
            exit;
        } else {
            $err = "Registration failed. Email may already exist.";
        }
    }

    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $u = login_user($email, $password);
        if ($u) {
            $_SESSION['user'] = $u;
            header('Location: /whatsapp_sales/admin/index.php');
            exit;
        } else {
            $err = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BotWave - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .form-container {
            transition: all 0.5s ease-in-out;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center"
    style="background-image: url('assets/imgs/backgrounds/bg.png');">
    <header class="w-full bg-white shadow-md py-4 fixed top-0 left-0 z-50">
        <div class="max-w-6xl mx-auto flex items-center justify-between px-4">
            <!-- Logo -->
            <div class="flex items-center gap-2">
                <img src="assets/imgs/logos/logo.webp" alt="BotWave Logo" class="h-10 w-10">
                <span class="text-2xl font-bold text-[#128c7e]">BotWave</span>
            </div>

            <!-- Optional Right Side -->
            <div class="text-gray-700 font-medium">
                Welcome! Please login or sign up
            </div>
        </div>
    </header>

    <div class="w-full max-w-[90rem] flex shadow-xl rounded-2xl overflow-hidden min-h-[700px]">

        <!-- Left Side - Dashboard Preview -->
        <div class="w-1/2 bg-white p-8 flex flex-col justify-center space-y-6">

            <div class="flex-1 flex items-center justify-center">
                <img src="assets/imgs/content-img.gif" alt="BotWave Preview"
                    class="w-full max-w-lg md:max-w-xl lg:max-w-2xl rounded-lg">
            </div>
        </div>

        <!-- Right Side - Login / Signup Card -->
        <div class="w-1/2 bg-white p-8 flex flex-col justify-center">

            <!-- Login Form -->
            <div id="loginForm" class="form-container">
                <h2 class="text-2xl font-semibold text-[#128c7e] mb-6 text-center">Login</h2>
                <form action="#" method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="login">
                    <input type="email"
                        class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#25d366]"
                        name="email" placeholder="Email" required />
                    <input type="password" placeholder="Password" name="password"
                        class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#25d366]">

                    <button type="submit"
                        class="w-full bg-[#25d366] text-white p-3 rounded-lg hover:bg-[#128c7e] transition">Login</button>
                </form>
                <p class="mt-4 text-sm text-gray-500 text-center">
                    Don't have an account?
                    <button id="showSignup" class="text-[#25d366] font-medium hover:underline">Sign Up</button>
                </p>
            </div>

            <!-- Signup Form -->
            <div id="signupForm" class="form-container hidden opacity-0">
                <h2 class="text-2xl font-semibold text-[#128c7e] mb-6 text-center">Sign Up</h2>
                <form action="#" method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="register">
                    <input type="text" placeholder="Full Name" name="name"
                        class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#25d366]">
                    <input type="email" placeholder="Email" name="email"
                        class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#25d366]">
                    <input type="password" placeholder="Password" name="password"
                        class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#25d366]">
                    <input class="w-full border p-3 rounded" name="whatsapp" placeholder="WhatsApp (e.g., 919876543210)"
                        required />
                    <button type="submit"
                        class="w-full bg-[#25d366] text-white p-3 rounded-lg hover:bg-[#128c7e] transition">Sign
                        Up</button>
                </form>
                <p class="mt-4 text-sm text-gray-500 text-center">
                    Already have an account?
                    <button id="showLogin" class="text-[#25d366] font-medium hover:underline">Login</button>
                </p>
            </div>

        </div>

    </div>

    <footer class="w-full bg-white shadow-md py-4 fixed bottom-0 left-0 z-50">
        <div class="max-w-6xl mx-auto text-center text-gray-600">
            &copy; 2024 BotWave. All rights reserved. Developed By <a href="https://www.mercurysoftech.com"
                class="text-[#25d366] hover:underline">Mercury Softech</a>
        </div>

        <!-- JS for toggle -->
        <script>
            const loginForm = document.getElementById('loginForm');
            const signupForm = document.getElementById('signupForm');
            const showSignup = document.getElementById('showSignup');
            const showLogin = document.getElementById('showLogin');

            showSignup.addEventListener('click', () => {
                loginForm.classList.add('opacity-0');
                setTimeout(() => {
                    loginForm.classList.add('hidden');
                    signupForm.classList.remove('hidden');
                    setTimeout(() => signupForm.classList.remove('opacity-0'), 50);
                }, 300);
            });

            showLogin.addEventListener('click', () => {
                signupForm.classList.add('opacity-0');
                setTimeout(() => {
                    signupForm.classList.add('hidden');
                    loginForm.classList.remove('hidden');
                    setTimeout(() => loginForm.classList.remove('opacity-0'), 50);
                }, 300);
            });
        </script>

</body>

</html>