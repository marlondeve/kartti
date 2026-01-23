<?php
// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    redirect_to('index.php?route=login');
}

$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
?>
<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alertas - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script>
      window.BASE_PATH = '<?php echo addslashes(base_path('')); ?>';
      window.USE_PRETTY_URLS = false;
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
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
                        <a href="<?php echo base_path('index.php?route=dashboard'); ?>" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <i class="fas fa-home w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"></i>
                            <span class="ml-3">Área de Trabajo</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo base_path('index.php?route=menu'); ?>" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <i class="fas fa-utensils w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"></i>
                            <span class="ml-3">Menú</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo base_path('index.php?route=qr'); ?>" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <i class="fas fa-qrcode w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"></i>
                            <span class="ml-3">Códigos QR</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo base_path('index.php?route=alertas'); ?>" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white bg-gray-100 dark:bg-gray-700 group">
                            <i class="fas fa-chart-bar w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"></i>
                            <span class="ml-3">Alertas</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo base_path('index.php?route=analitica'); ?>" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <i class="fas fa-chart-line w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"></i>
                            <span class="ml-3">Analítica</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo base_path('index.php?route=configuracion'); ?>" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
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
                <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
                    <div class="flex items-center">
                        <button data-drawer-target="default-sidebar" data-drawer-toggle="default-sidebar" aria-controls="default-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
                            <span class="sr-only">Abrir menú</span>
                            <i class="fas fa-bars"></i>
                        </button>
                        <div class="flex items-center">
                            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Alertas</h1>
                        </div>
                    </div>
                </div>
            </nav>
            <!-- Contenido de alertas -->
            <div class="p-4">
                <div class="mb-6 p-6 rounded-2xl shadow-lg bg-gradient-to-r from-blue-600 via-blue-400 to-blue-200 dark:from-blue-900 dark:via-blue-800 dark:to-gray-900 flex items-center gap-5 border-l-8 border-blue-700">
                    <div class="flex-shrink-0 flex items-center justify-center w-16 h-16 rounded-full bg-white/80 dark:bg-gray-900/70 shadow text-blue-700 text-3xl">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-blue-900 dark:text-white tracking-tight flex items-center gap-2">
                            Sección de Alertas
                        </div>
                        <div class="mt-1 text-base text-gray-800 dark:text-gray-300">Aquí puedes ver y gestionar las alertas recibidas desde los QR.</div>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row items-center justify-between gap-2 mb-4">
                    <div class="flex gap-2 w-full sm:w-auto">
                        <button id="tabPendientes" type="button" class="tab-alerta font-semibold px-4 py-2 rounded-lg focus:outline-none bg-blue-600 text-white shadow relative">
                            Pendientes
                            <span id="alertCounter" class="hidden absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center animate-pulse"></span>
                        </button>
                        <button id="tabResueltas" type="button" class="tab-alerta font-semibold px-4 py-2 rounded-lg focus:outline-none bg-gray-200 text-blue-800 shadow hover:bg-blue-100">Resueltas</button>
                    </div>
                    <div class="flex items-center gap-2">
                        <!-- Indicador de estado en tiempo real -->
                        <div id="statusIndicator" class="flex items-center gap-2 px-3 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg text-sm font-medium">
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                            <span>Actualización automática activa</span>
                        </div>
                        <!-- Indicador de última actualización -->
                        <div id="lastUpdateIndicator" class="flex items-center gap-2 px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg text-xs">
                            <i class="fas fa-clock"></i>
                            <span id="lastUpdateTime">Actualizando...</span>
                        </div>
                        <button id="btnEliminarTodasAlertas" type="button" class="flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow transition focus:outline-none focus:ring-2 focus:ring-red-400">
                            <i class="fas fa-trash"></i>
                            Eliminar todas
                        </button>
                    </div>
                </div>
                <div id="alertasContainer" class="space-y-4"></div>
                <div id="alertasResueltasContainer" class="space-y-4 hidden"></div>
                <style>
                    .tab-alerta.active {
                        background: linear-gradient(to right, #2563eb, #60a5fa);
                        color: #fff !important;
                    }
                    .tab-alerta:not(.active) {
                        background: #f1f5f9;
                        color: #2563eb;
                    }
                    
                    /* Animaciones para nuevas alertas */
                    @keyframes slideInFromTop {
                        0% {
                            transform: translateY(-20px);
                            opacity: 0;
                        }
                        100% {
                            transform: translateY(0);
                            opacity: 1;
                        }
                    }
                    
                    .nueva-alerta {
                        animation: slideInFromTop 0.5s ease-out;
                        border-left: 4px solid #10b981 !important;
                        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%);
                    }
                    
                    /* Indicador de actualización */
                    .updating {
                        opacity: 0.7;
                        transition: opacity 0.3s ease;
                    }
                    
                    .refresh-spin {
                        animation: spin 1s linear infinite;
                    }
                    
                    @keyframes spin {
                        from {
                            transform: rotate(0deg);
                        }
                        to {
                            transform: rotate(360deg);
                        }
                    }
                    
                    /* Notificación de nueva alerta */
                    .notification-slide {
                        animation: slideInFromRight 0.3s ease-out;
                    }
                    
                    @keyframes slideInFromRight {
                        0% {
                            transform: translateX(100%);
                            opacity: 0;
                        }
                        100% {
                            transform: translateX(0);
                            opacity: 1;
                        }
                    }
                    
                    /* Estado offline */
                    .status-offline {
                        background: #fef2f2 !important;
                        color: #dc2626 !important;
                    }
                    
                    .dark .status-offline {
                        background: #7f1d1d !important;
                        color: #fca5a5 !important;
                    }
                </style>
            </div>
        </div>
    </div>

    <!-- Modal de éxito para alerta resuelta -->
    <div id="modalAlertaResuelta" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 mx-4 max-w-sm w-full shadow-2xl border border-green-500">
            <div class="text-center">
                <div class="w-16 h-16 mx-auto mb-4 bg-green-500 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-green-600 dark:text-green-400 mb-2">¡Alerta Resuelta!</h3>
                <p class="text-gray-600 dark:text-gray-300 mb-6">La solicitud ha sido marcada como atendida exitosamente</p>
                <button id="btnCerrarModalExito" class="px-6 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-medium transition-colors">
                    Perfecto
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de error -->
    <div id="modalError" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 mx-4 max-w-sm w-full shadow-2xl border border-red-500">
            <div class="text-center">
                <div class="w-16 h-16 mx-auto mb-4 bg-red-500 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-red-600 dark:text-red-400 mb-2">Error</h3>
                <p id="modalErrorMessage" class="text-gray-600 dark:text-gray-300 mb-6">Ha ocurrido un error. Intente nuevamente.</p>
                <button id="btnCerrarModalError" class="px-6 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-medium transition-colors">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    <script src="<?php echo base_path('public/js/alertas.js'); ?>"></script>
</body>
</html>
