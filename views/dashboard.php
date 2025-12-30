<?php
// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

// Obtener datos del usuario de la sesión
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
?>
<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <!-- Overlay para el fondo oscuro cuando se muestra el modal -->
    <div id="modalBackdrop" class="hidden fixed inset-0 w-full h-full bg-black bg-opacity-80 z-49 transition-opacity duration-300 ease-in-out opacity-0 backdrop-blur-sm pointer-events-auto" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh;"></div>
    
    <!-- Overlay para el fondo oscuro cuando se muestra el modal de logout -->
    <div id="logoutModalBackdrop" class="hidden fixed inset-0 w-full h-full bg-black bg-opacity-80 z-49 transition-opacity duration-300 ease-in-out opacity-0 backdrop-blur-sm pointer-events-auto" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh;"></div>
    
    <!-- Modal para crear restaurante -->
    <div id="createRestaurantModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Crear tu Restaurante
                    </h3>
                </div>
                <form id="createRestaurantForm" class="p-4 md:p-5">
                    <div class="mb-4">
                        <label for="restaurantName" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nombre del Restaurante</label>
                        <input type="text" id="restaurantName" name="nombre" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required>
                    </div>
                    <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        Crear Restaurante
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal para confirmar cierre de sesión -->
    <div id="logoutModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Confirmar cierre de sesión
                    </h3>
                    <button type="button" class="end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="logoutModal">
                        <i class="fas fa-times"></i>
                        <span class="sr-only">Cerrar modal</span>
                    </button>
                </div>
                <div class="p-4 md:p-5">
                    <p class="text-gray-500 dark:text-gray-400 mb-4">¿Estás seguro de que deseas cerrar sesión?</p>
                    <div class="flex justify-end gap-3">
                        <button type="button" class="py-2.5 px-5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600" data-modal-hide="logoutModal">
                            Cancelar
                        </button>
                        <button id="confirmLogout" type="button" class="text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                            Cerrar Sesión
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside id="default-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0" aria-label="Sidebar">
            <div class="h-full px-3 py-4 overflow-y-auto bg-white dark:bg-gray-800">
                <div class="flex items-center justify-center h-16 px-4 bg-blue-600 rounded-lg">
                    <span class="text-xl font-bold text-white"><?php echo APP_NAME; ?></span>
                </div>
                
                <ul class="space-y-2 font-medium mt-5">
                    <li>
                        <a href="/dashboard" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white bg-gray-100 dark:bg-gray-700 group">
                            <i class="fas fa-home w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"></i>
                            <span class="ml-3">Área de Trabajo</span>
                        </a>
                    </li>
                    <li>
                        <a href="/menu" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <i class="fas fa-utensils w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"></i>
                            <span class="ml-3">Menú</span>
                        </a>
                    </li>
                    <li>
                        <a href="/qr" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <i class="fas fa-qrcode w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"></i>
                            <span class="ml-3">Códigos QR</span>
                        </a>
                    </li>
                    <li>
                        <a href="/alertas" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <i class="fas fa-chart-bar w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"></i>
                            <span class="ml-3">Alertas</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <i class="fas fa-cog w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"></i>
                            <span class="ml-3">Configuración</span>
                        </a>
                    </li>
                </ul>
                
                <!-- Perfil del usuario -->
                <div class="absolute bottom-0 left-0 w-64 p-4 border-t dark:border-gray-700">
                    <button data-modal-target="logoutModal" data-modal-toggle="logoutModal" 
                            class="w-full text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 mb-3 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                        <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
                    </button>
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_name']); ?>" alt="">
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo $_SESSION['user_name']; ?></p>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400"><?php echo $_SESSION['user_email']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Contenido principal -->
        <div class="w-full sm:ml-64">
            <!-- Barra superior -->
            <nav class="bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700">
                <div class="w-full flex flex-wrap items-center justify-between p-4">
                    <div class="flex items-center">
                        <button data-drawer-target="default-sidebar" data-drawer-toggle="default-sidebar" aria-controls="default-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
                            <span class="sr-only">Abrir menú</span>
                            <i class="fas fa-bars"></i>
                        </button>
                        <div class="flex items-center">
                            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Restaurante - </h1>
                            <span id="restaurantName" class="ml-2 text-2xl font-semibold text-blue-600 dark:text-blue-400" style="display: inline-block; visibility: visible; opacity: 1;"></span>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Contenido del dashboard -->
            <div class="p-4">
                <!-- Bienvenida -->
                <div class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
                    <div class="font-medium">Bienvenido, <?php echo $_SESSION['user_name']; ?></div>
                    <div class="mt-1">Panel de control de tu menú digital</div>
                </div>

                <!-- Tarjetas de estadísticas -->
                <div class="grid grid-cols-1 gap-4 mb-4 sm:grid-cols-2 lg:grid-cols-2">
                    <!-- Categorías -->
                    <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800 md:p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                                <i class="fas fa-tags text-2xl text-blue-600 dark:text-blue-300"></i>
                            </div>
                            <div class="ml-5">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Categorías Activas</h3>
                                <p id="categoriasActivas" class="text-2xl font-bold text-gray-900 dark:text-white">0</p>
                            </div>
                        </div>
                    </div>

                    <!-- Productos -->
                    <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800 md:p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                                <i class="fas fa-utensils text-2xl text-green-600 dark:text-green-300"></i>
                            </div>
                            <div class="ml-5">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Productos Activos</h3>
                                <p id="productosActivos" class="text-2xl font-bold text-gray-900 dark:text-white">0</p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Acciones rápidas -->
                <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Acciones Rápidas</h2>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <a href="/menu" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                                    <i class="fas fa-utensils text-2xl text-blue-600 dark:text-blue-300"></i>
                                </div>
                                <div class="ml-5">
                                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">Gestionar Menú</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Administra categorías y productos</p>
                                </div>
                            </div>
                        </a>
                        
                        <a href="#" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                                    <i class="fas fa-chair text-2xl text-green-600 dark:text-green-300"></i>
                                </div>
                                <div class="ml-5">
                                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">Gestionar Mesas</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Administra las mesas de tu restaurante</p>
                                </div>
                            </div>
                        </a>

                        <a href="#" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-gray-100 dark:bg-gray-900">
                                    <i class="fas fa-cog text-2xl text-gray-600 dark:text-gray-300"></i>
                                </div>
                                <div class="ml-5">
                                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">Configuración</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ajusta la configuración de tu menú</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    <script src="/public/js/utils.js"></script>
    <script src="/public/js/dashboard.js"></script>
</body>
</html> 