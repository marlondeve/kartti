<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página no encontrada</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="<?php echo base_path('public/css/styles.css'); ?>">
<script>
  window.BASE_PATH = '<?php echo addslashes(base_path('')); ?>';
  window.USE_PRETTY_URLS = false;
</script>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full space-y-8 p-8 bg-white dark:bg-gray-800 rounded-lg shadow-lg">
            <div class="text-center">
                <h1 class="text-9xl font-bold text-blue-600 dark:text-blue-500">404</h1>
                <h2 class="mt-6 text-3xl font-bold text-gray-900 dark:text-white">Página no encontrada</h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Lo sentimos, la página que buscas no existe.
                </p>
                <div class="mt-8">
                    <a href="<?php echo base_path('index.php?route=landing'); ?>" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-offset-gray-800">
                        Volver al inicio
                    </a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>
</html> 