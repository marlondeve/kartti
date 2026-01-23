<?php
// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    redirect_to('index.php?route=login');
}

// Obtener restaurante del usuario (comprobación segura de columnas)
$rest = null;
try {
    $stmt = $pdo->prepare("SELECT id, nombre FROM restaurantes WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $rest = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // ignorar
}

$restId = $rest['id'] ?? null;
$restNombre = $rest['nombre'] ?? '';

// Intentar leer settings desde el JSON del restaurante si existe
$callEnabled = 1; // default
$whatsapp = '';
$colors = [
    'background' => '#1a1a1a',
    'card_background' => '#272728',
    'primary' => '#B38D57',
    'primary_dark' => '#80653E',
    'primary_light' => '#E4C57F',
    'text_primary' => '#E4C57F',
    'text_secondary' => '#B38D57',
    'header_bg' => '#272728',
    'header_text' => '#B38D57'
];
$showMigrationWarning = false;
if ($restId) {
    $jsonFile = __DIR__ . '/../public/json/restaurante' . $restId . '.json';
    if (file_exists($jsonFile)) {
        $raw = file_get_contents($jsonFile);
        $jsonData = json_decode($raw, true);
        if (is_array($jsonData) && isset($jsonData['_settings']) && is_array($jsonData['_settings'])) {
            if (isset($jsonData['_settings']['call_waiter_enabled'])) {
                $callEnabled = $jsonData['_settings']['call_waiter_enabled'] ? 1 : 0;
            }
            if (isset($jsonData['_settings']['whatsapp'])) {
                $whatsapp = $jsonData['_settings']['whatsapp'] ?? '';
            }
            if (isset($jsonData['_settings']['colors']) && is_array($jsonData['_settings']['colors'])) {
                $colors = array_merge($colors, $jsonData['_settings']['colors']);
            }
        }
    } else {
        // Si no existe JSON, mostrar advertencia para ejecutar el script de población (opcional)
        $showMigrationWarning = true;
    }
} else {
    // Si usuario sin restaurante, mostrar advertencia
    $showMigrationWarning = true;
}
?>
<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="<?php echo base_path('public/js/script.js'); ?>"></script>
    <script>
      window.BASE_PATH = '<?php echo addslashes(base_path('')); ?>';
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <div class="flex h-screen">
        <!-- Sidebar (copiado de dashboard) -->
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
                        <a href="<?php echo base_path('index.php?route=alertas'); ?>" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
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
                        <a href="#" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white bg-gray-100 dark:bg-gray-700 group">
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
        <div class="w-full sm:ml-64 p-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">Configuración</h1>

            <?php if (!empty($showMigrationWarning)) : ?>
                <div class="mb-4 p-4 rounded bg-yellow-50 text-yellow-800 border border-yellow-200">
                    <strong>Advertencia:</strong> faltan columnas en la base de datos (<?php echo htmlspecialchars(implode(', ', $missingCols)); ?>). Aplica la migración en <code>database/migrations/20260105_add_restaurant_settings.sql</code> para habilitar todas las opciones.
                </div>
            <?php endif; ?>


            <!-- Layout de dos columnas -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Columna izquierda: Formulario -->
                <div>
                    <form id="settingsForm" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                <div class="mb-4">
                    <label for="nombre" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nombre del Restaurante</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($restNombre); ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white">
                </div>

                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Botón "Llamar Mesero"</label>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Activa o desactiva la opción de que los clientes pidan al mesero desde la carta.</p>
                    </div>
                    <div>
                        <label class="inline-flex items-center cursor-pointer">
                            <input id="call_waiter_enabled" name="call_waiter_enabled" type="checkbox" class="sr-only" <?php echo $callEnabled ? 'checked' : ''; ?> />
                            <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-offset-2 peer-focus:ring-blue-600 flex items-center p-1 transition-colors">
                                <div class="dot w-4 h-4 bg-white rounded-full transition-transform <?php echo $callEnabled ? 'translate-x-5' : ''; ?>"></div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="whatsapp" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Número de WhatsApp</label>
                    <input type="text" id="whatsapp" name="whatsapp" value="<?php echo htmlspecialchars($whatsapp); ?>" placeholder="Ej: +573001112233" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white">
                   
                </div>

                <!-- Sección de Paleta de Colores -->
                <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Paleta de Colores de la Carta</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Elige un diseño precargado o personaliza tus propios colores. Los cambios se aplicarán inmediatamente.</p>
                    
                    <!-- Selector de Diseños Precargados -->
                    <div class="mb-6">
                        <label class="block mb-3 text-sm font-medium text-gray-900 dark:text-white">Diseños Precargados</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" id="presetDesignsContainer">
                            <!-- Los diseños se cargarán dinámicamente con JavaScript -->
                        </div>
                    </div>

                    <!-- Opción Personalizada (oculta por defecto) -->
                    <div id="customColorsSection" class="hidden">
                        <div class="mb-4 flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-900 dark:text-white">Personalizar Colores</label>
                            <button type="button" id="btnCloseCustom" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                <i class="fas fa-times"></i> Cerrar
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="color_background" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Fondo Principal</label>
                                <div class="flex gap-2">
                                    <input type="color" id="color_background" value="<?php echo htmlspecialchars($colors['background']); ?>" class="w-16 h-10 rounded border border-gray-300 dark:border-gray-600 cursor-pointer">
                                    <input type="text" id="color_background_text" value="<?php echo htmlspecialchars($colors['background']); ?>" class="flex-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white" pattern="^#[0-9A-Fa-f]{6}$">
                                </div>
                            </div>

                            <div>
                                <label for="color_card_background" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Fondo de Tarjetas</label>
                                <div class="flex gap-2">
                                    <input type="color" id="color_card_background" value="<?php echo htmlspecialchars($colors['card_background']); ?>" class="w-16 h-10 rounded border border-gray-300 dark:border-gray-600 cursor-pointer">
                                    <input type="text" id="color_card_background_text" value="<?php echo htmlspecialchars($colors['card_background']); ?>" class="flex-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white" pattern="^#[0-9A-Fa-f]{6}$">
                                </div>
                            </div>

                            <div>
                                <label for="color_primary" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Color Principal</label>
                                <div class="flex gap-2">
                                    <input type="color" id="color_primary" value="<?php echo htmlspecialchars($colors['primary']); ?>" class="w-16 h-10 rounded border border-gray-300 dark:border-gray-600 cursor-pointer">
                                    <input type="text" id="color_primary_text" value="<?php echo htmlspecialchars($colors['primary']); ?>" class="flex-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white" pattern="^#[0-9A-Fa-f]{6}$">
                                </div>
                            </div>

                            <div>
                                <label for="color_primary_dark" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Color Principal Oscuro</label>
                                <div class="flex gap-2">
                                    <input type="color" id="color_primary_dark" value="<?php echo htmlspecialchars($colors['primary_dark']); ?>" class="w-16 h-10 rounded border border-gray-300 dark:border-gray-600 cursor-pointer">
                                    <input type="text" id="color_primary_dark_text" value="<?php echo htmlspecialchars($colors['primary_dark']); ?>" class="flex-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white" pattern="^#[0-9A-Fa-f]{6}$">
                                </div>
                            </div>

                            <div>
                                <label for="color_primary_light" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Color Principal Claro</label>
                                <div class="flex gap-2">
                                    <input type="color" id="color_primary_light" value="<?php echo htmlspecialchars($colors['primary_light']); ?>" class="w-16 h-10 rounded border border-gray-300 dark:border-gray-600 cursor-pointer">
                                    <input type="text" id="color_primary_light_text" value="<?php echo htmlspecialchars($colors['primary_light']); ?>" class="flex-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white" pattern="^#[0-9A-Fa-f]{6}$">
                                </div>
                            </div>

                            <div>
                                <label for="color_text_primary" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Texto Principal</label>
                                <div class="flex gap-2">
                                    <input type="color" id="color_text_primary" value="<?php echo htmlspecialchars($colors['text_primary']); ?>" class="w-16 h-10 rounded border border-gray-300 dark:border-gray-600 cursor-pointer">
                                    <input type="text" id="color_text_primary_text" value="<?php echo htmlspecialchars($colors['text_primary']); ?>" class="flex-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white" pattern="^#[0-9A-Fa-f]{6}$">
                                </div>
                            </div>

                            <div>
                                <label for="color_text_secondary" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Texto Secundario</label>
                                <div class="flex gap-2">
                                    <input type="color" id="color_text_secondary" value="<?php echo htmlspecialchars($colors['text_secondary']); ?>" class="w-16 h-10 rounded border border-gray-300 dark:border-gray-600 cursor-pointer">
                                    <input type="text" id="color_text_secondary_text" value="<?php echo htmlspecialchars($colors['text_secondary']); ?>" class="flex-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white" pattern="^#[0-9A-Fa-f]{6}$">
                                </div>
                            </div>

                            <div>
                                <label for="color_header_bg" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Fondo del Header</label>
                                <div class="flex gap-2">
                                    <input type="color" id="color_header_bg" value="<?php echo htmlspecialchars($colors['header_bg']); ?>" class="w-16 h-10 rounded border border-gray-300 dark:border-gray-600 cursor-pointer">
                                    <input type="text" id="color_header_bg_text" value="<?php echo htmlspecialchars($colors['header_bg']); ?>" class="flex-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white" pattern="^#[0-9A-Fa-f]{6}$">
                                </div>
                            </div>

                            <div>
                                <label for="color_header_text" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Texto del Header</label>
                                <div class="flex gap-2">
                                    <input type="color" id="color_header_text" value="<?php echo htmlspecialchars($colors['header_text']); ?>" class="w-16 h-10 rounded border border-gray-300 dark:border-gray-600 cursor-pointer">
                                    <input type="text" id="color_header_text_text" value="<?php echo htmlspecialchars($colors['header_text']); ?>" class="flex-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white" pattern="^#[0-9A-Fa-f]{6}$">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
                </div>

                <!-- Columna derecha: Vista previa de la carta -->
                <div>
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md sticky top-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Vista Previa de la Carta</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Los cambios se reflejarán en tiempo real</p>
                        
                        <?php
                        // Obtener el primer QR activo del restaurante para la vista previa
                        $qrNombre = 'Carta';
                        $qrTipo = 'restaurante'; // Tipo por defecto más común
                        if ($restId) {
                            $qrJsonFile = __DIR__ . '/../public/json/qr' . $restId . '.json';
                            if (file_exists($qrJsonFile)) {
                                $qrJsonContent = file_get_contents($qrJsonFile);
                                $qrJsonData = json_decode($qrJsonContent, true);
                                if (is_array($qrJsonData) && isset($qrJsonData['qrs']) && is_array($qrJsonData['qrs'])) {
                                    // Buscar el primer QR activo
                                    foreach ($qrJsonData['qrs'] as $qr) {
                                        if (isset($qr['estado']) && $qr['estado'] === 'activo') {
                                            $qrNombre = $qr['nombre'] ?? 'Carta';
                                            $qrTipo = $qr['tipo'] ?? 'restaurante';
                                            break;
                                        }
                                    }
                                    // Si no hay activos, usar el primero disponible
                                    if ($qrNombre === 'Carta' && count($qrJsonData['qrs']) > 0) {
                                        $firstQr = $qrJsonData['qrs'][0];
                                        $qrNombre = $firstQr['nombre'] ?? 'Carta';
                                        $qrTipo = $firstQr['tipo'] ?? 'restaurante';
                                    }
                                }
                            }
                        }
                        ?>
                        
                        <div class="border-2 border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-900 mx-auto" style="width: 100%; max-width: 360px; aspect-ratio: 8/16; position: relative;">
                            <div class="absolute inset-0 flex items-center justify-center" style="pointer-events: none;">
                                <div class="text-gray-400 text-sm">Cargando vista previa...</div>
                            </div>
                            <iframe 
                                id="cartaPreview" 
                                src="<?php echo base_path('carta/?id=' . ($restId ?? '') . '&nombre=' . urlencode($qrNombre) . '&tipo=' . urlencode($qrTipo) . '&preview=1'); ?>" 
                                class="w-full h-full border-0"
                                style="transform: scale(1); transform-origin: top left; width: 100%; height: 100%;">
                            </iframe>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center">
                            La vista previa se actualiza automáticamente al cambiar los valores
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    const RESTAURANT_ID = <?php echo $restId ?? 'null'; ?>;
    const BASE_PATH = '<?php echo addslashes(base_path('')); ?>';
    
    // Valores iniciales del QR para la vista previa (desde PHP)
    const INITIAL_QR_NOMBRE = '<?php echo addslashes($qrNombre); ?>';
    const INITIAL_QR_TIPO = '<?php echo addslashes($qrTipo); ?>';
    
    // Función helper para construir URLs de carta correctamente
    function buildCartaUrl(id, nombre, tipo) {
        if (!id) {
            console.error('No se puede construir URL sin ID de restaurante');
            return '';
        }
        const base = BASE_PATH || '';
        const cleanBase = base.replace(/\/$/, ''); // Remover barra final si existe
        // Validar y sanitizar valores - usar valores por defecto seguros
        const safeNombre = (nombre && nombre.trim()) ? nombre.trim() : (INITIAL_QR_NOMBRE || 'Carta');
        const safeTipo = (tipo && tipo.trim()) ? tipo.trim() : (INITIAL_QR_TIPO || 'restaurante');
        return cleanBase + '/carta/?id=' + id + 
               '&nombre=' + encodeURIComponent(safeNombre) + 
               '&tipo=' + encodeURIComponent(safeTipo);
    }
    
    // Función para mostrar notificaciones (usa la misma que en menu.js)
    function showNotification(type, message) {
        if (typeof utils !== 'undefined' && utils.showNotification) {
            utils.showNotification(type, message);
        } else {
            // Fallback si utils no está disponible
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            } text-white z-50`;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    }
    
    // Variable para controlar el guardado automático
    let autoSaveTimeout = null;
    let isSaving = false;
    let isUpdatingPreview = false;

    // Función para guardar automáticamente los cambios
    async function autoSaveSettings() {
        if (isSaving || !RESTAURANT_ID) return;
        
        // Debounce para evitar guardados excesivos
        if (autoSaveTimeout) {
            clearTimeout(autoSaveTimeout);
        }
        
        autoSaveTimeout = setTimeout(async () => {
            isSaving = true;
            
            const data = {
                action: 'update_settings',
                nombre: document.getElementById('nombre').value.trim(),
                call_waiter_enabled: document.getElementById('call_waiter_enabled').checked ? 1 : 0,
                whatsapp: document.getElementById('whatsapp').value.trim(),
                colors: getColorsFromForm()
            };

            try {
                const json = await saveSettings(data);
                if (json.success) {
                    showNotification('success', 'Configuración guardada automáticamente');
                    // Esperar 3-4 segundos para que el JSON se actualice completamente antes de recargar
                    const delay = 3500; // 3.5 segundos
                    setTimeout(() => {
                        updateCartaPreview();
                    }, delay);
                } else {
                    showNotification('error', json.message || 'Error al guardar');
                }
            } catch (err) {
                showNotification('error', 'Ocurrió un error al guardar');
            } finally {
                isSaving = false;
            }
        }, 1000); // Guardar después de 1 segundo de inactividad
    }

    // Función para actualizar la vista previa de la carta
    let previewUpdateTimeout = null;
    function updateCartaPreview() {
        const iframe = document.getElementById('cartaPreview');
        if (!iframe || !RESTAURANT_ID || isUpdatingPreview) return;
        
        // Debounce para evitar recargas excesivas
        if (previewUpdateTimeout) {
            clearTimeout(previewUpdateTimeout);
        }
        
        previewUpdateTimeout = setTimeout(() => {
            isUpdatingPreview = true;
            
            // Obtener valores actuales del formulario
            const nombre = document.getElementById('nombre').value.trim();
            const callWaiterEnabled = document.getElementById('call_waiter_enabled').checked;
            const whatsapp = document.getElementById('whatsapp').value.trim();
            const colors = getColorsFromForm();
            
            // Construir URL con parámetros actualizados usando la función helper
            // El nombre debe ser el nombre del QR, no del restaurante
            // Usar el nombre inicial del QR (no el nombre del restaurante del formulario)
            const qrNombre = INITIAL_QR_NOMBRE || 'Carta';
            // Mantener el tipo del QR original para asegurar consistencia
            const qrTipo = INITIAL_QR_TIPO || 'restaurante';
            // Construir URL base sin parámetros de preview
            let url = buildCartaUrl(RESTAURANT_ID, qrNombre, qrTipo);
            // Agregar parámetros de preview al final solo si la URL es válida
            if (url) {
                url += '&preview=1&t=' + Date.now(); // timestamp para forzar recarga
            } else {
                console.error('No se pudo construir la URL de la vista previa');
                isUpdatingPreview = false;
                return;
            }
            
            // Guardar referencia a los colores para aplicar después de cargar
            const colorsToApply = { ...colors };
            const nombreToApply = nombre;
            const callWaiterEnabledToApply = callWaiterEnabled;
            const whatsappToApply = whatsapp;
            
            // Remover el listener anterior para evitar múltiples llamadas
            iframe.onload = null;
            
            // Configurar el listener ANTES de cambiar el src
            iframe.onload = function() {
                setTimeout(() => {
                    try {
                        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                        if (!iframeDoc) {
                            isUpdatingPreview = false;
                            return;
                        }
                        
                        const root = iframeDoc.documentElement;
                        
                        // Aplicar colores personalizados a las variables CSS
                        Object.keys(colorsToApply).forEach(key => {
                            const cssVarMap = {
                                'background': '--color-background',
                                'card_background': '--color-card-background',
                                'primary': '--color-primary',
                                'primary_dark': '--color-primary-dark',
                                'primary_light': '--color-primary-light',
                                'text_primary': '--color-text-primary',
                                'text_secondary': '--color-text-secondary',
                                'header_bg': '--color-header-bg',
                                'header_text': '--color-header-text'
                            };
                            if (cssVarMap[key] && colorsToApply[key]) {
                                root.style.setProperty(cssVarMap[key], colorsToApply[key]);
                            }
                        });
                        
                        // Actualizar nombre del restaurante en el iframe
                        const restaurantTitle = iframeDoc.getElementById('restaurantTitle');
                        if (restaurantTitle && nombreToApply) {
                            restaurantTitle.textContent = nombreToApply.toUpperCase();
                            if (colorsToApply.header_text) {
                                restaurantTitle.style.color = colorsToApply.header_text;
                            }
                        }
                        
                        // Actualizar botón de mesero
                        const btnLlamarMesero = iframeDoc.getElementById('btnLlamarMesero');
                        if (btnLlamarMesero) {
                            if (callWaiterEnabledToApply) {
                                btnLlamarMesero.classList.remove('hidden');
                            } else {
                                btnLlamarMesero.classList.add('hidden');
                            }
                        }
                        
                        // Actualizar botón de WhatsApp
                        const btnWhatsApp = iframeDoc.getElementById('btnWhatsApp');
                        if (btnWhatsApp) {
                            if (whatsappToApply) {
                                btnWhatsApp.classList.remove('hidden');
                                const phone = whatsappToApply.replace(/[^0-9]/g, '');
                                btnWhatsApp.href = 'https://wa.me/' + phone;
                            } else {
                                btnWhatsApp.classList.add('hidden');
                            }
                        }
                        
                        // Llamar a funciones de actualización del iframe si existen
                        if (iframe.contentWindow) {
                            // Esperar un poco más para que el iframe termine de inicializar
                            setTimeout(() => {
                                if (typeof iframe.contentWindow.applyCustomColors === 'function') {
                                    iframe.contentWindow.applyCustomColors();
                                }
                                if (typeof iframe.contentWindow.updateProductCards === 'function') {
                                    iframe.contentWindow.updateProductCards(colorsToApply);
                                }
                                if (typeof iframe.contentWindow.updateCategoryButtons === 'function') {
                                    iframe.contentWindow.updateCategoryButtons(colorsToApply);
                                }
                                isUpdatingPreview = false;
                            }, 300);
                        } else {
                            isUpdatingPreview = false;
                        }
                    } catch (e) {
                        // Silenciar errores de CORS - la recarga del iframe ya aplica los cambios
                        console.log('Vista previa actualizada (algunos cambios pueden requerir recarga completa)');
                        isUpdatingPreview = false;
                    }
                }, 800); // Aumentar el tiempo de espera para asegurar que el iframe cargue completamente
            };
            
            // Cambiar el src para recargar el iframe
            iframe.src = url;
        }, 500); // Aumentar el debounce para evitar recargas muy rápidas
    }

    async function saveSettings(payload) {
        const resp = await fetch(window.BASE_PATH + '/api/restaurantes.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        return await resp.json();
    }

    // Sincronizar inputs de color (color picker <-> text input)
    const colorInputs = [
        'background', 'card_background', 'primary', 'primary_dark', 'primary_light',
        'text_primary', 'text_secondary', 'header_bg', 'header_text'
    ];
    
    colorInputs.forEach(colorName => {
        const colorPicker = document.getElementById(`color_${colorName}`);
        const colorText = document.getElementById(`color_${colorName}_text`);
        
        if (colorPicker && colorText) {
            let isSyncing = false; // Bandera para evitar loops de sincronización
            
            // Sincronizar color picker -> text
            colorPicker.addEventListener('input', function() {
                if (isSyncing) return;
                isSyncing = true;
                colorText.value = this.value.toUpperCase();
                isSyncing = false;
                // Detectar si los colores coinciden con algún diseño
                checkAndUpdatePresetSelection();
                // NO actualizar vista previa aquí - se actualizará después de guardar
                autoSaveSettings(); // Guardar automáticamente (recargará vista previa después)
            });
            
            // Sincronizar text -> color picker (con validación)
            colorText.addEventListener('input', function() {
                if (isSyncing) return;
                const value = this.value.trim();
                if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
                    isSyncing = true;
                    colorPicker.value = value;
                    isSyncing = false;
                    // Detectar si los colores coinciden con algún diseño
                    checkAndUpdatePresetSelection();
                    // NO actualizar vista previa aquí - se actualizará después de guardar
                    autoSaveSettings(); // Guardar automáticamente (recargará vista previa después)
                }
            });
        }
    });

    // Botón para restaurar colores por defecto
    document.getElementById('btnResetColors')?.addEventListener('click', function() {
        const defaultColors = {
            background: '#1a1a1a',
            card_background: '#272728',
            primary: '#B38D57',
            primary_dark: '#80653E',
            primary_light: '#E4C57F',
            text_primary: '#E4C57F',
            text_secondary: '#B38D57',
            header_bg: '#272728',
            header_text: '#B38D57'
        };
        
        colorInputs.forEach(colorName => {
            const colorPicker = document.getElementById(`color_${colorName}`);
            const colorText = document.getElementById(`color_${colorName}_text`);
            const defaultValue = defaultColors[colorName];
            
            if (colorPicker && colorText && defaultValue) {
                colorPicker.value = defaultValue;
                colorText.value = defaultValue;
            }
        });
        // NO actualizar vista previa aquí - se actualizará después de guardar
        autoSaveSettings(); // Guardar automáticamente (recargará vista previa después)
    });

    // Diseños precargados
    const presetDesigns = [
        {
            id: 'gourmet-vino-cobre',
            name: 'Gourmet Vino & Cobre',
            colors: {
                background: '#1B0F14',
                card_background: '#2A151B',
                primary: '#CFAE70',
                primary_dark: '#CFAE70',
                primary_light: '#2A151B',
                text_primary: '#CFAE70',
                text_secondary: '#CFAE70',
                header_bg: '#2A151B',
                header_text: '#CFAE70'
            }
        },
        {
            id: 'natural-verde-oliva',
            name: 'Natural Verde Oliva',
            colors: {
                background: '#0F1A14',
                card_background: '#1C2B22',
                primary: '#A3C9A8',
                primary_dark: '#A3C9A8',
                primary_light: '#1C2B22',
                text_primary: '#A3C9A8',
                text_secondary: '#A3C9A8',
                header_bg: '#1C2B22',
                header_text: '#A3C9A8'
            }
        },
        {
            id: 'parrilla-fuego',
            name: 'Parrilla Fuego',
            colors: {
                background: '#1A0F0A',
                card_background: '#2A1912',
                primary: '#E26D2D',
                primary_dark: '#E26D2D',
                primary_light: '#2A1912',
                text_primary: '#E26D2D',
                text_secondary: '#E26D2D',
                header_bg: '#2A1912',
                header_text: '#E26D2D'
            }
        },
        {
            id: 'cafe-postres',
            name: 'Café & Postres',
            colors: {
                background: '#1A1411',
                card_background: '#2A221D',
                primary: '#D6B38C',
                primary_dark: '#D6B38C',
                primary_light: '#2A221D',
                text_primary: '#D6B38C',
                text_secondary: '#D6B38C',
                header_bg: '#2A221D',
                header_text: '#D6B38C'
            }
        },
        {
            id: 'mar-fresh',
            name: 'Mar & Fresh',
            colors: {
                background: '#0C1A1E',
                card_background: '#163038',
                primary: '#5ED3E6',
                primary_dark: '#5ED3E6',
                primary_light: '#163038',
                text_primary: '#5ED3E6',
                text_secondary: '#5ED3E6',
                header_bg: '#163038',
                header_text: '#5ED3E6'
            }
        }
    ];

    // Variable para rastrear el diseño seleccionado
    let selectedPresetId = null;

    // Función para comparar colores (ignorando mayúsculas/minúsculas)
    function colorsMatch(colors1, colors2) {
        for (let key in colors1) {
            if (colors1[key].toUpperCase() !== colors2[key].toUpperCase()) {
                return false;
            }
        }
        return true;
    }

    // Función para detectar qué diseño coincide con los colores actuales
    function detectCurrentPreset() {
        const currentColors = getColorsFromForm();
        for (let preset of presetDesigns) {
            if (colorsMatch(currentColors, preset.colors)) {
                return preset.id;
            }
        }
        return null;
    }

    // Función para crear tarjeta de diseño
    function createPresetCard(preset) {
        const card = document.createElement('div');
        card.className = 'preset-card relative cursor-pointer border-2 rounded-lg overflow-hidden transition-all duration-200 hover:shadow-lg border-gray-300 dark:border-gray-600';
        card.dataset.presetId = preset.id;
        
        // Vista previa visual del diseño
        const preview = document.createElement('div');
        preview.className = 'w-full h-36 relative';
        preview.style.backgroundColor = preset.colors.background;
        
        // Simular header
        const header = document.createElement('div');
        header.className = 'absolute top-0 left-0 right-0 h-8 flex items-center justify-center';
        header.style.backgroundColor = preset.colors.header_bg;
        const headerText = document.createElement('div');
        headerText.className = 'text-xs font-bold uppercase';
        headerText.style.color = preset.colors.header_text;
        headerText.textContent = 'RESTAURANTE';
        header.appendChild(headerText);
        
        // Simular una tarjeta de producto
        const cardPreview = document.createElement('div');
        cardPreview.className = 'absolute bottom-3 left-2 right-2 rounded-lg p-2.5 shadow-md';
        cardPreview.style.backgroundColor = preset.colors.card_background;
        cardPreview.style.border = `1px solid ${preset.colors.primary}40`;
        
        const title = document.createElement('div');
        title.className = 'text-xs font-semibold mb-1';
        title.style.color = preset.colors.text_primary;
        title.textContent = 'Plato Ejemplo';
        
        const price = document.createElement('div');
        price.className = 'text-xs font-bold mt-1';
        price.style.color = preset.colors.primary;
        price.textContent = '$25,000';
        
        cardPreview.appendChild(title);
        cardPreview.appendChild(price);
        preview.appendChild(header);
        preview.appendChild(cardPreview);
        
        // Nombre del diseño
        const nameDiv = document.createElement('div');
        nameDiv.className = 'p-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700';
        nameDiv.innerHTML = `
            <div class="text-sm font-medium text-gray-900 dark:text-white text-center">${preset.name}</div>
        `;
        
        // Indicador de selección
        const checkIcon = document.createElement('div');
        checkIcon.className = 'absolute top-2 right-2 w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center hidden shadow-md';
        checkIcon.innerHTML = '<i class="fas fa-check text-white text-xs"></i>';
        checkIcon.id = `check-${preset.id}`;
        
        card.appendChild(preview);
        card.appendChild(nameDiv);
        card.appendChild(checkIcon);
        
        // Event listener para seleccionar diseño
        card.addEventListener('click', function() {
            selectPreset(preset.id);
        });
        
        return card;
    }

    // Función para seleccionar un diseño precargado
    function selectPreset(presetId) {
        const preset = presetDesigns.find(p => p.id === presetId);
        if (!preset) return;
        
        // Actualizar variable de selección
        selectedPresetId = presetId;
        
        // Ocultar sección personalizada
        document.getElementById('customColorsSection').classList.add('hidden');
        
        // Aplicar colores del diseño
        colorInputs.forEach(colorName => {
            const colorPicker = document.getElementById(`color_${colorName}`);
            const colorText = document.getElementById(`color_${colorName}_text`);
            const colorValue = preset.colors[colorName];
            
            if (colorPicker && colorText && colorValue) {
                colorPicker.value = colorValue;
                colorText.value = colorValue.toUpperCase();
            }
        });
        
        // Actualizar indicadores visuales
        updatePresetIndicators();
        
        // Guardar automáticamente
        autoSaveSettings();
    }

    // Función para actualizar indicadores visuales de selección
    function updatePresetIndicators() {
        // Remover todas las selecciones
        document.querySelectorAll('.preset-card').forEach(card => {
            card.classList.remove('border-blue-600', 'ring-2', 'ring-blue-300');
            card.classList.add('border-gray-300', 'dark:border-gray-600');
            const checkIcon = card.querySelector('[id^="check-"]');
            if (checkIcon) checkIcon.classList.add('hidden');
        });
        
        // Marcar el seleccionado
        if (selectedPresetId) {
            const selectedCard = document.querySelector(`[data-preset-id="${selectedPresetId}"]`);
            if (selectedCard) {
                selectedCard.classList.remove('border-gray-300', 'dark:border-gray-600');
                selectedCard.classList.add('border-blue-600', 'ring-2', 'ring-blue-300');
                const checkIcon = selectedCard.querySelector(`#check-${selectedPresetId}`);
                if (checkIcon) checkIcon.classList.remove('hidden');
            }
        }
    }

    // Función para verificar y actualizar la selección de diseño
    function checkAndUpdatePresetSelection() {
        const currentPreset = detectCurrentPreset();
        if (currentPreset && currentPreset !== selectedPresetId) {
            selectedPresetId = currentPreset;
            document.getElementById('customColorsSection').classList.add('hidden');
            updatePresetIndicators();
        } else if (!currentPreset && selectedPresetId !== 'custom') {
            selectedPresetId = 'custom';
            document.getElementById('customColorsSection').classList.remove('hidden');
            updatePresetIndicators();
        }
    }

    // Función para mostrar opción personalizada
    function showCustomColors() {
        selectedPresetId = 'custom';
        document.getElementById('customColorsSection').classList.remove('hidden');
        updatePresetIndicators();
    }

    // Función para inicializar diseños precargados
    function initializePresetDesigns() {
        const container = document.getElementById('presetDesignsContainer');
        if (!container) return;
        
        // Crear tarjetas para cada diseño
        presetDesigns.forEach(preset => {
            const card = createPresetCard(preset);
            container.appendChild(card);
        });
        
        // Agregar tarjeta de opción personalizada
        const customCard = document.createElement('div');
        customCard.className = 'preset-card relative cursor-pointer border-2 border-dashed rounded-lg overflow-hidden transition-all duration-200 hover:shadow-lg border-gray-300 dark:border-gray-600';
        customCard.dataset.presetId = 'custom';
        customCard.innerHTML = `
            <div class="w-full h-32 flex items-center justify-center bg-gradient-to-br from-purple-100 to-pink-100 dark:from-purple-900 dark:to-pink-900">
                <i class="fas fa-palette text-4xl text-gray-400 dark:text-gray-500"></i>
            </div>
            <div class="p-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                <div class="text-sm font-medium text-gray-900 dark:text-white text-center">Personalizada</div>
            </div>
            <div id="check-custom" class="absolute top-2 right-2 w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center hidden">
                <i class="fas fa-check text-white text-xs"></i>
            </div>
        `;
        customCard.addEventListener('click', showCustomColors);
        container.appendChild(customCard);
        
        // Detectar diseño actual
        const currentPreset = detectCurrentPreset();
        if (currentPreset) {
            selectedPresetId = currentPreset;
            document.getElementById('customColorsSection').classList.add('hidden');
        } else {
            // Si no coincide con ningún diseño, mostrar opción personalizada
            selectedPresetId = 'custom';
            showCustomColors();
        }
        
        updatePresetIndicators();
    }

    // Botón para cerrar sección personalizada
    document.getElementById('btnCloseCustom')?.addEventListener('click', function() {
        document.getElementById('customColorsSection').classList.add('hidden');
        // Seleccionar el primer diseño por defecto si no hay ninguno seleccionado
        if (!selectedPresetId || selectedPresetId === 'custom') {
            selectPreset(presetDesigns[0].id);
        } else {
            updatePresetIndicators();
        }
    });

    // Función para obtener los colores del formulario
    function getColorsFromForm() {
        const colors = {};
        colorInputs.forEach(colorName => {
            const colorText = document.getElementById(`color_${colorName}_text`);
            if (colorText && colorText.value.trim()) {
                colors[colorName] = colorText.value.trim().toUpperCase();
            }
        });
        return colors;
    }

    // Prevenir envío del formulario (ya no es necesario)
    document.getElementById('settingsForm').addEventListener('submit', function(e){
        e.preventDefault();
    });

    // Guardar automáticamente cuando cambie el nombre (la vista previa se actualizará después de guardar)
    document.getElementById('nombre')?.addEventListener('input', function() {
        autoSaveSettings(); // Se recargará la vista previa después de guardar
    });

    // Guardar automáticamente cuando cambie WhatsApp (la vista previa se actualizará después de guardar)
    document.getElementById('whatsapp')?.addEventListener('input', function() {
        autoSaveSettings(); // Se recargará la vista previa después de guardar
    });

    // Toggle visual state and save immediately when the checkbox changes
    (function(){
        const checkbox = document.getElementById('call_waiter_enabled');
        if (!checkbox) return;
        const label = checkbox.closest('label');
        const dot = label ? label.querySelector('.dot') : null;

        checkbox.addEventListener('change', function(){
            // Update UI animation instantly
            if (dot) dot.classList.toggle('translate-x-5', checkbox.checked);
            // Guardar automáticamente (la vista previa se actualizará después de guardar)
            autoSaveSettings();
        });
    })();
    
    // Inicializar vista previa al cargar
    document.addEventListener('DOMContentLoaded', function() {
        initializePresetDesigns();
        setTimeout(updateCartaPreview, 500);
    });
    </script>
</body>
</html>