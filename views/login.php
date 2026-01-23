<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: {
                            '900': '#111827',
                            '800': '#1F2937',
                            '700': '#374151',
                            '600': '#4B5563',
                            '500': '#6B7280',
                            '400': '#9CA3AF',
                            '300': '#D1D5DB',
                            '200': '#E5E7EB',
                            '100': '#F3F4F6',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="<?php echo base_path('public/css/styles.css'); ?>">
    <script>
      window.BASE_PATH = '<?php echo addslashes(base_path('')); ?>';
      window.USE_PRETTY_URLS = false;
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #111827;
        }
    </style>
</head>
<body class="bg-gray-900">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full space-y-8 p-8 bg-gray-800 rounded-lg shadow-lg">
            <div>
                <h2 class="text-center text-3xl font-bold text-white">Iniciar Sesión</h2>
                <p class="mt-2 text-center text-sm text-gray-400">
                    ¿No tienes una cuenta?
                    <a href="<?php echo base_path('index.php?route=registro'); ?>" class="font-medium text-blue-500 hover:text-blue-400">
                        Regístrate aquí
                    </a>
                </p>
            </div>
            <form class="mt-8 space-y-6" id="loginForm" action="<?php echo base_path('api/api_auth.php'); ?>" method="POST">
                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-300">Correo electrónico</label>
                        <input id="email" name="email" type="email" required 
                            class="mt-1 block w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-white sm:text-sm" 
                            placeholder="tu@email.com">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-300">Contraseña</label>
                        <input id="password" name="password" type="password" required 
                            class="mt-1 block w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-white sm:text-sm" 
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember_me" name="remember_me" type="checkbox" 
                            class="w-4 h-4 text-blue-600 bg-gray-700 border-gray-600 rounded focus:ring-blue-500">
                        <label for="remember_me" class="ml-2 text-sm font-medium text-gray-300">
                            Recordarme
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="<?php echo base_path('index.php?route=recuperar-password'); ?>" class="font-medium text-blue-500 hover:text-blue-400">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Iniciar Sesión
                    </button>
                </div>
            </form>

            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-600"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-gray-800 text-gray-400">
                            O continúa con
                        </span>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3">
                    <div>
                        <a href="#" class="w-full inline-flex justify-center py-2 px-4 border border-gray-600 rounded-lg shadow-sm bg-gray-700 text-sm font-medium text-gray-300 hover:bg-gray-600">
                            <span class="sr-only">Iniciar sesión con Google</span>
                            <svg class="w-5 h-5" viewBox="0 0 24 24">
                                <path fill="currentColor" d="M12.545,10.239v3.821h5.445c-0.712,2.315-2.647,3.972-5.445,3.972c-3.332,0-6.033-2.701-6.033-6.032s2.701-6.032,6.033-6.032c1.498,0,2.866,0.549,3.921,1.453l2.814-2.814C17.503,2.988,15.139,2,12.545,2C7.021,2,2.543,6.477,2.543,12s4.478,10,10.002,10c8.396,0,10.249-7.85,9.426-11.748L12.545,10.239z"/>
                            </svg>
                        </a>
                    </div>

                    <div>
                        <a href="#" class="w-full inline-flex justify-center py-2 px-4 border border-gray-600 rounded-lg shadow-sm bg-gray-700 text-sm font-medium text-gray-300 hover:bg-gray-600">
                            <span class="sr-only">Iniciar sesión con Facebook</span>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    <script src="<?php echo base_path('public/js/script.js'); ?>"></script>
</body>
</html> 