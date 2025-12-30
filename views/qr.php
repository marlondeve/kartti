<?php
// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

// Obtener datos del usuario de la sesión
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
?>
<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de QR - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <!-- Overlay para el fondo oscuro cuando se muestra el modal -->
    <div id="modalBackdrop" class="hidden fixed inset-0 w-full h-full bg-black bg-opacity-80 z-49 transition-opacity duration-300 ease-in-out opacity-0 backdrop-blur-sm pointer-events-auto"></div>
    
    <!-- Overlay para el fondo oscuro cuando se muestra el modal de logout -->
    <div id="logoutModalBackdrop" class="hidden fixed inset-0 w-full h-full bg-black bg-opacity-80 z-49 transition-opacity duration-300 ease-in-out opacity-0 backdrop-blur-sm pointer-events-auto"></div>
    
    <!-- Modal para crear QR -->
    <div id="createQRModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Crear Nuevo QR
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="createQRModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="createQRForm" class="p-4 md:p-5" onsubmit="event.preventDefault(); createQR();">
                    <div class="mb-4">
                        <label for="nombre" class="block text-sm font-medium text-gray-900 dark:text-white">Nombre</label>
                        <input type="text" id="nombre" name="nombre" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                    </div>
                    <div class="mb-4">
                        <label for="tipo" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tipo de QR</label>
                        <div class="relative">
                            <select id="tipo" name="tipo" required class="appearance-none bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 pr-10">
                                <option value="restaurante">QR Principal del Restaurante</option>
                                <option value="mesa">QR para Mesa</option>
                                <option value="area">QR para Área</option>
                                <option value="especial">QR Especial</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        Crear QR
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
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="logoutModal">
                        <i class="fas fa-times"></i>
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

    <!-- Modal para confirmar eliminación de QR -->
    <div id="deleteQRModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Confirmar eliminación
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="deleteQRModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-4 md:p-5">
                    <p class="text-gray-500 dark:text-gray-400 mb-4">¿Estás seguro de que deseas eliminar este código QR?</p>
                    <div class="flex justify-end gap-3">
                        <button type="button" class="py-2.5 px-5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600" data-modal-hide="deleteQRModal">
                            Cancelar
                        </button>
                        <button id="confirmDeleteQR" type="button" class="text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                            Eliminar
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
                        <a href="/dashboard" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
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
                        <a href="/qr" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white bg-gray-100 dark:bg-gray-700 group">
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
                            <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_name); ?>" alt="">
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo $user_name; ?></p>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400"><?php echo $user_email; ?></p>
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
                            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Gestión de Códigos QR</h1>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Contenido de la página QR -->
            <div class="p-4">
            <div class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
                    <div class="font-medium">Sección de Códigos QR</div>
                    <div class="mt-1">Aquí podrás gestionar los QRs de tu menú digital</div>
                </div>
                <div class="p-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Gestión de Códigos QR</h2>
                        <button type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800" data-modal-target="createQRModal" >
                            <i class="fas fa-plus mr-2"></i>Nuevo QR
                        </button>
                    </div>

                    <!-- Grid de QR -->
                    <div id="qrGrid" class="space-y-8">
                        <!-- QR Principal del Restaurante -->
                        <div class="space-y-4">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                                <i class="fas fa-store mr-2 text-blue-500"></i>
                                QR Principal del Restaurante
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="restauranteQRs">
                                <!-- QR del restaurante se cargarán aquí -->
                            </div>
                        </div>

                        <!-- QR para Mesas -->
                        <div class="space-y-4">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                                <i class="fas fa-chair mr-2 text-green-500"></i>
                                QR para Mesas
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="mesaQRs">
                                <!-- QR de mesas se cargarán aquí -->
                            </div>
                        </div>

                        <!-- QR para Áreas -->
                        <div class="space-y-4">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                                <i class="fas fa-map-marker-alt mr-2 text-purple-500"></i>
                                QR para Áreas
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="areaQRs">
                                <!-- QR de áreas se cargarán aquí -->
                            </div>
                        </div>

                        <!-- QR Especiales -->
                        <div class="space-y-4">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                                <i class="fas fa-star mr-2 text-yellow-500"></i>
                                QR Especiales
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="especialQRs">
                                <!-- QR especiales se cargarán aquí -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    <script src="/public/js/script.js"></script>
    <script>
        // Inicializar modales solo si los elementos existen
        let qrModal, previewModal, deleteModal;
        let qrToDelete = null;
        
        // Función para mostrar notificaciones
        function showNotification(type, message) {
            if (typeof utils !== 'undefined' && utils.showNotification) {
                utils.showNotification(type, message);
            } else {
                // Fallback si utils no está disponible
                alert(message);
            }
        }

        // Función para hacer fetch
        async function makeFetch(url, options = {}) {
            if (typeof utils !== 'undefined' && utils.fetch) {
                return await utils.fetch(url, options);
            } else {
                // Fallback si utils no está disponible
                const response = await fetch(url, {
                    ...options,
                    headers: {
                        'Content-Type': 'application/json',
                        ...options.headers
                    }
                });
                return await response.json();
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Inicializar el modal de Flowbite
            const qrModalElement = document.getElementById('createQRModal');
            const previewModalElement = document.getElementById('previewQRModal');
            const deleteModalElement = document.getElementById('deleteQRModal');
            
            if (qrModalElement) {
                qrModal = new Modal(qrModalElement, {
                    placement: 'center',
                    backdrop: 'dynamic',
                    backdropClasses: 'bg-gray-900/50 dark:bg-gray-900/80 fixed inset-0 z-40',
                    closable: true,
                    onHide: () => {
                        // Limpiar cualquier backdrop residual
                        const backdrops = document.querySelectorAll('[modal-backdrop]');
                        backdrops.forEach(backdrop => {
                            backdrop.remove();
                        });
                        // Asegurarse de que el modal backdrop también se limpie
                        const modalBackdrop = document.getElementById('modalBackdrop');
                        if (modalBackdrop) {
                            modalBackdrop.classList.add('hidden');
                            modalBackdrop.classList.add('opacity-0');
                        }
                    }
                });

                // Agregar event listener al botón de crear QR
                const createButton = document.querySelector('[data-modal-target="createQRModal"]');
                if (createButton) {
                    createButton.addEventListener('click', () => {
                        const modalBackdrop = document.getElementById('modalBackdrop');
                        if (modalBackdrop) {
                            modalBackdrop.classList.remove('hidden');
                            setTimeout(() => {
                                modalBackdrop.classList.remove('opacity-0');
                            }, 10);
                        }
                        qrModal.show();
                    });
                }

                // Agregar event listener al botón de cerrar
                const closeButton = qrModalElement.querySelector('[data-modal-hide="createQRModal"]');
                if (closeButton) {
                    closeButton.addEventListener('click', () => {
                        qrModal.hide();
                    });
                }
            }
            
            if (previewModalElement) {
                previewModal = new Modal(previewModalElement, {
                    placement: 'center',
                    backdrop: 'dynamic',
                    backdropClasses: 'bg-gray-900/50 dark:bg-gray-900/80 fixed inset-0 z-40',
                    closable: true
                });
            }

            if (deleteModalElement) {
                deleteModal = new Modal(deleteModalElement, {
                    placement: 'center',
                    backdrop: 'dynamic',
                    backdropClasses: 'bg-gray-900/50 dark:bg-gray-900/80 fixed inset-0 z-40',
                    closable: true
                });
            }

            // Cargar los QR al iniciar
            loadQRs();

            // Manejar la confirmación de eliminación
            document.getElementById('confirmDeleteQR').addEventListener('click', async function() {
                if (qrToDelete) {
                    await confirmDeleteQR();
                    qrToDelete = null;
                    deleteModal.hide();
                }
            });
        });

        // Función para cargar los QR
        async function loadQRs() {
            try {
                console.log('Cargando QRs...');
                const response = await makeFetch('api/api_qrs.php');
                console.log('Respuesta recibida:', response);
                
                if (response.success) {
                    // Obtener el ID del restaurante
                    const restaurantResponse = await makeFetch('api/restaurantes.php?action=check');
                    if (!restaurantResponse.success || !restaurantResponse.id) {
                        showNotification('error', 'Error al obtener el ID del restaurante');
                        return;
                    }

                    const restauranteId = restaurantResponse.id;
                    
                    // Limpiar contenedores
                    const containers = ['restauranteQRs', 'mesaQRs', 'areaQRs', 'especialQRs'];
                    containers.forEach(containerId => {
                        const container = document.getElementById(containerId);
                        if (container) {
                            container.innerHTML = '';
                        }
                    });
                    
                    if (response.data && response.data.length > 0) {
                        console.log('Procesando QRs:', response.data);
                        response.data.forEach(qr => {
                            try {
                                const card = document.createElement('div');
                                // Determinar el color según el tipo
                                let bgColor = 'bg-[#1c1c1e]';
                                let icon = 'fa-qrcode';
                                let typeLabel = 'QR';
                                let containerId = 'especialQRs'; // Contenedor por defecto
                                
                                // Si no tiene tipo o está vacío, lo ponemos como especial
                                if (!qr.tipo || qr.tipo === '') {
                                    qr.tipo = 'especial';
                                }
                                
                                switch(qr.tipo.toLowerCase()) {
                                    case 'restaurante':
                                        bgColor = 'bg-[#1c1c1e]';
                                        icon = 'fa-store';
                                        typeLabel = 'Restaurante';
                                        containerId = 'restauranteQRs';
                                        break;
                                    case 'mesa':
                                        bgColor = 'bg-[#1c1c1e]';
                                        icon = 'fa-chair';
                                        typeLabel = 'Mesa';
                                        containerId = 'mesaQRs';
                                        break;
                                    case 'area':
                                        bgColor = 'bg-[#1c1c1e]';
                                        icon = 'fa-map-marker-alt';
                                        typeLabel = 'Área';
                                        containerId = 'areaQRs';
                                        break;
                                    default:
                                        bgColor = 'bg-[#1c1c1e]';
                                        icon = 'fa-star';
                                        typeLabel = 'Especial';
                                        containerId = 'especialQRs';
                                        break;
                                }

                                console.log('Creando tarjeta para:', qr.nombre, 'tipo:', qr.tipo, 'contenedor:', containerId);

                                card.className = `${bgColor} rounded-2xl p-4 shadow-md w-full max-w-xs text-white`;
                                card.innerHTML = `
                                    <div class="flex flex-col">
                                        <!-- QR Code and Toggle -->
                                        <div class="flex justify-between items-start">
                                            <div id="qr-${qr.id}" class="w-24 h-24 rounded-lg bg-white"></div>
                                            <label class="inline-flex items-center cursor-pointer ml-2">
                                                <input type="checkbox" ${qr.estado === 'activo' ? 'checked' : ''} 
                                                       class="sr-only peer" 
                                                       onchange="updateQRStatus(${qr.id}, this.checked)">
                                                <div class="w-10 h-5 bg-gray-600 rounded-full peer peer-checked:bg-blue-500 transition-all"></div>
                                                <div class="w-4 h-4 bg-white rounded-full absolute translate-x-1 peer-checked:translate-x-5 transition-transform"></div>
                                            </label>
                                        </div>

                                        <!-- Name and Date -->
                                        <div class="mt-4">
                                            <div class="flex items-center justify-between">
                                                <h3 class="text-lg font-semibold">${qr.nombre}</h3>
                                                <span class="text-xs font-semibold px-2 py-1 rounded-full bg-gray-700 text-gray-300">
                                                    <i class="fas ${icon} mr-1"></i>${typeLabel}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-400">${new Date(qr.created_at).toLocaleDateString('es-ES', { 
                                                year: 'numeric',
                                                month: 'short',
                                                day: 'numeric'
                                            }).replace('.', '')}</p>
                                        </div>

                                        <!-- Action Icons -->
                                        <div class="mt-4 flex justify-between text-gray-400">
                                            <a href="${window.location.origin}/carta/?id=${restauranteId}&tipo=${encodeURIComponent(qr.tipo)}&nombre=${encodeURIComponent(qr.nombre)}" target="_blank" class="hover:text-white transition-colors" title="Vista previa">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            <button onclick="copyToClipboard('${window.location.origin}/carta/?id=${restauranteId}&tipo=${encodeURIComponent(qr.tipo)}&nombre=${encodeURIComponent(qr.nombre)}')" class="hover:text-white transition-colors" title="Copiar enlace">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                                </svg>
                                            </button>
                                            <button onclick="downloadQR('${qr.imagen}', '${qr.nombre}')" class="hover:text-white transition-colors" title="Descargar QR">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                            </button>
                                            <button onclick="deleteQR(${qr.id})" class="hover:text-white transition-colors" title="Eliminar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-9V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                `;

                                // Agregar la tarjeta al contenedor correspondiente
                                const container = document.getElementById(containerId);
                                if (container) {
                                    console.log('Agregando tarjeta al contenedor:', containerId);
                                    container.appendChild(card);
                                    // Generar QR usando la imagen guardada
                                    setTimeout(() => generateQRCode(qr.imagen, `qr-${qr.id}`), 100);
                                } else {
                                    console.error(`Contenedor no encontrado para el tipo: ${qr.tipo}`);
                                }
                            } catch (error) {
                                console.error('Error al procesar QR:', error);
                            }
                        });
                    } else {
                        containers.forEach(containerId => {
                            const container = document.getElementById(containerId);
                            if (container) {
                                container.innerHTML = '<div class="col-span-full text-center text-gray-500">No hay códigos QR creados</div>';
                            }
                        });
                    }
                } else {
                    console.error('Error en la respuesta:', response);
                    showNotification('error', response.message || 'Error al cargar los códigos QR');
                }
            } catch (error) {
                console.error('Error al cargar QRs:', error);
                showNotification('error', 'Error al cargar los códigos QR');
            }
        }

        // Función para crear un nuevo QR
        async function createQR() {
            const nombre = document.getElementById('nombre');
            const tipo = document.getElementById('tipo');
            
            if (!nombre || !tipo) {
                showNotification('error', 'Elementos del formulario no encontrados');
                return;
            }

            if (!nombre.value || !tipo.value) {
                showNotification('error', 'Nombre y tipo son requeridos');
                return;
            }

            try {
                // Obtener el ID del restaurante del usuario
                const response = await makeFetch('api/restaurantes.php?action=check');
                if (!response.success || !response.has_restaurant) {
                    showNotification('error', 'No tienes un restaurante configurado');
                    return;
                }

                if (!response.id) {
                    showNotification('error', 'Error al obtener el ID del restaurante');
                    return;
                }

                // Generar el QR primero
                const qrPath = '/carta/';
                const params = new URLSearchParams({
                    id: response.id,
                    tipo: tipo.value,
                    nombre: nombre.value
                });
                const qrUrl = `${window.location.origin}${qrPath}?${params.toString()}`;
                
                // Validar la URL
                try {
                    new URL(qrUrl);
                } catch (error) {
                    showNotification('error', 'Error al generar la URL del QR');
                    return;
                }

                // Generar la imagen del QR
                const qr = qrcode(0, 'M');
                qr.addData(qrUrl);
                qr.make();
                const qrDataUrl = qr.createDataURL(10); // Aumentamos el tamaño para mejor calidad

                // Convertir el Data URL a Blob
                const responseBlob = await fetch(qrDataUrl);
                const blob = await responseBlob.blob();

                // Crear FormData y añadir los datos
                const formData = new FormData();
                formData.append('action', 'create');
                formData.append('nombre', nombre.value);
                formData.append('tipo', tipo.value);
                formData.append('url', qrUrl);
                formData.append('qr_image', blob, `${nombre.value}_qr.png`);

                const apiResponse = await makeFetch('api/api_qrs.php', {
                    method: 'POST',
                    body: formData
                });

                if (apiResponse.success) {
                    showNotification('success', 'Código QR creado exitosamente');
                    document.getElementById('createQRForm').reset();
                    
                    // Cerrar el modal y recargar
                    if (qrModal) {
                        qrModal.hide();
                        // Esperar a que se complete la animación antes de recargar
                        setTimeout(() => {
                            loadQRs();
                        }, 300);
                    }
                } else {
                    showNotification('error', apiResponse.message || 'Error al crear el código QR');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('error', 'Error al crear el código QR');
            }
        }

        // Función para generar el código QR
        function generateQRCode(url, elementId) {
            try {
                console.log('Cargando QR para:', url, 'en elemento:', elementId);
                const qrElement = document.getElementById(elementId);
                if (!qrElement) {
                    console.error('Elemento no encontrado:', elementId);
                    return;
                }

                // Crear una imagen que muestre el QR guardado
                const img = new Image();
                img.src = `${window.location.origin}/${url}`;
                img.style.width = '96px';
                img.style.height = '96px';
                img.style.objectFit = 'contain';
                
                // Manejar errores de carga de imagen
                img.onerror = function() {
                    console.error('Error al cargar la imagen del QR');
                    qrElement.innerHTML = '<div class="text-red-500">Error al cargar QR</div>';
                };
                
                qrElement.innerHTML = '';
                qrElement.appendChild(img);
                console.log('QR cargado exitosamente');
            } catch (error) {
                console.error('Error al cargar QR:', error);
                const qrElement = document.getElementById(elementId);
                if (qrElement) {
                    qrElement.innerHTML = '<div class="text-red-500">Error al cargar QR</div>';
                }
            }
        }

        // Función para copiar la URL al portapapeles
        async function copyToClipboard(text) {
            try {
                await navigator.clipboard.writeText(text);
                showNotification('success', 'URL copiada al portapapeles');
            } catch (error) {
                console.error('Error al copiar:', error);
                showNotification('error', 'Error al copiar la URL');
            }
        }

        // Función para eliminar un QR
        async function deleteQR(id) {
            qrToDelete = id;
            deleteModal.show();
        }

        // Función para eliminar un QR (función interna)
        async function confirmDeleteQR() {
            if (!qrToDelete) return;

            try {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', qrToDelete);

                const response = await makeFetch('api/api_qrs.php', {
                    method: 'POST',
                    body: formData
                });

                if (response.success) {
                    showNotification('success', 'Código QR eliminado exitosamente');
                    loadQRs(); // Recargar la lista de QRs
                } else {
                    showNotification('error', response.message || 'Error al eliminar el código QR');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('error', 'Error al eliminar el código QR');
            }
        }

        // Función para actualizar el estado de un QR
        async function updateQRStatus(id, estado) {
            try {
                const formData = new FormData();
                formData.append('action', 'update');
                formData.append('id', id);
                formData.append('estado', estado === true ? 'activo' : 'inactivo');

                const response = await makeFetch('api/api_qrs.php', {
                    method: 'POST',
                    body: formData
                });

                if (response.success) {
                    showNotification('success', 'Estado del QR actualizado exitosamente');
                } else {
                    showNotification('error', response.message || 'Error al actualizar el estado del QR');
                    // Recargar los QRs para mantener la consistencia
                    loadQRs();
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('error', 'Error al actualizar el estado del QR');
                // Recargar los QRs para mantener la consistencia
                loadQRs();
            }
        }

        // Manejar el cierre de sesión
        document.getElementById('confirmLogout').addEventListener('click', function() {
            window.location.href = '/logout';
        });

        function showPreview(qr) {
            window.open(qr.url, '_blank');
        }

        // Función para descargar el QR
        async function downloadQR(imagePath, nombre) {
            try {
                // Construir la URL completa
                const fullUrl = `${window.location.origin}/${imagePath}`;
                
                // Crear un elemento <a> temporal
                const link = document.createElement('a');
                link.href = fullUrl;
                
                // Obtener la extensión del archivo original
                const extension = imagePath.split('.').pop();
                
                // Establecer el nombre del archivo para la descarga
                link.download = `QR_${nombre}.${extension}`;
                
                // Añadir el enlace al documento
                document.body.appendChild(link);
                
                // Simular clic en el enlace
                link.click();
                
                // Eliminar el enlace
                document.body.removeChild(link);
                
                showNotification('success', 'Descargando código QR');
            } catch (error) {
                console.error('Error al descargar el QR:', error);
                showNotification('error', 'Error al descargar el código QR');
            }
        }
    </script>
</body>
</html>