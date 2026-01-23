<?php
// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    redirect_to('index.php?route=login');
}

// Obtener restaurante del usuario
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

// Cargar datos de tracking
$trackingData = null;
$restaurantData = null;

if ($restId) {
    $trackFile = __DIR__ . '/../carta/qr-tracking.json';
    if (file_exists($trackFile)) {
        $trackingData = json_decode(file_get_contents($trackFile), true);
        
        // Filtrar datos del restaurante
        if ($trackingData) {
            $restaurantData = [
                'resumen_general' => $trackingData['resumen_general'][$restId] ?? 0,
                'historico_diario' => []
            ];
            
            // Filtrar histórico diario
            if (isset($trackingData['historico_diario']) && is_array($trackingData['historico_diario'])) {
                foreach ($trackingData['historico_diario'] as $fecha => $datos) {
                    if (isset($datos[$restId])) {
                        $restaurantData['historico_diario'][$fecha] = $datos[$restId];
                    }
                }
            }
        }
    }
}

// Preparar datos para JavaScript
$jsTrackingData = json_encode($restaurantData ?? ['resumen_general' => 0, 'historico_diario' => []]);
?>
<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analítica - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
      window.BASE_PATH = '<?php echo addslashes(base_path('')); ?>';
      window.USE_PRETTY_URLS = false;
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
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
                        <a href="<?php echo base_path('index.php?route=alertas'); ?>" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <i class="fas fa-bell w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"></i>
                            <span class="ml-3">Alertas</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white bg-gray-100 dark:bg-gray-700 group">
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
                <div class="w-full flex flex-wrap items-center justify-between p-4">
                    <div class="flex items-center">
                        <button data-drawer-target="default-sidebar" data-drawer-toggle="default-sidebar" aria-controls="default-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
                            <span class="sr-only">Abrir menú</span>
                            <i class="fas fa-bars"></i>
                        </button>
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Analítica - <?php echo htmlspecialchars($restNombre); ?></h1>
                    </div>
                    <div class="flex items-center gap-2">
                        <button id="downloadPNG" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            <i class="fas fa-download mr-2"></i>Descargar PNG
                        </button>
                        <button id="downloadPDF" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                            <i class="fas fa-file-pdf mr-2"></i>Descargar PDF
                        </button>
                    </div>
                </div>
            </nav>

            <!-- Contenido de analítica -->
            <div class="p-4" id="analyticsContent">
                <!-- Filtros de tiempo -->
                <div class="mb-6" id="filtrosContainer">
                    <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Filtros de Tiempo</h2>
                        <div class="flex flex-wrap items-center gap-4">
                            <div class="flex items-center gap-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Período:</label>
                                <select id="filtroPeriodo" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                                    <option value="todos">Todos los datos</option>
                                    <option value="semana">Última semana</option>
                                    <option value="mes">Último mes</option>
                                    <option value="rango">Rango de fechas</option>
                                </select>
                            </div>
                            <div id="rangoFechasContainer" class="hidden flex items-center gap-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Desde:</label>
                                <input type="date" id="fechaDesde" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Hasta:</label>
                                <input type="date" id="fechaHasta" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                                <button id="aplicarRango" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                    <i class="fas fa-filter mr-2"></i>Aplicar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumen general -->
                <div class="mb-6">
                    <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Resumen General</h2>
                        <div class="flex items-center">
                            <div class="p-4 rounded-full bg-blue-100 dark:bg-blue-900">
                                <i class="fas fa-eye text-3xl text-blue-600 dark:text-blue-300"></i>
                            </div>
                            <div class="ml-5">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total de Visitas</h3>
                                <p id="totalVisitas" class="text-4xl font-bold text-gray-900 dark:text-white">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Gráfico de visitas por día -->
                    <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Visitas por Día</h3>
                        <canvas id="visitasPorDiaChart"></canvas>
                    </div>

                    <!-- Gráfico de visitas por hora -->
                    <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Visitas por Hora (Promedio)</h3>
                        <canvas id="visitasPorHoraChart"></canvas>
                    </div>
                </div>

                <!-- Tabla de datos históricos -->
                <div class="mt-6 p-6 bg-white rounded-lg shadow dark:bg-gray-800">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Historial Detallado</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Fecha</th>
                                    <th scope="col" class="px-6 py-3">Total Visitas</th>
                                    <th scope="col" class="px-6 py-3">Hora Pico</th>
                                    <th scope="col" class="px-6 py-3">Distribución por Horas</th>
                                </tr>
                            </thead>
                            <tbody id="historialTableBody">
                                <!-- Se llenará con JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
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

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    <script src="<?php echo base_path('public/js/utils.js'); ?>"></script>
    <script>
        // Datos del restaurante
        const trackingData = <?php echo $jsTrackingData; ?>;
        const restaurantId = <?php echo $restId ?? 'null'; ?>;
        const restaurantName = <?php echo json_encode($restNombre); ?>;

        // Variables para los gráficos
        let visitasPorDiaChart = null;
        let visitasPorHoraChart = null;
        let datosFiltrados = {
            resumen_general: trackingData.resumen_general || 0,
            historico_diario: trackingData.historico_diario || {}
        };

        // Función para formatear fecha
        function formatFecha(fecha) {
            const date = new Date(fecha + 'T00:00:00');
            return date.toLocaleDateString('es-ES', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        }

        // Función para obtener fecha de inicio de semana (últimos 7 días)
        function getInicioSemana() {
            const hoy = new Date();
            const inicioSemana = new Date(hoy);
            inicioSemana.setDate(hoy.getDate() - 6); // Últimos 7 días (incluyendo hoy)
            inicioSemana.setHours(0, 0, 0, 0);
            return inicioSemana;
        }

        // Función para filtrar datos por período
        function filtrarDatos(periodo) {
            const historico = trackingData.historico_diario || {};
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);
            
            let fechaInicio = null;
            let fechaFin = new Date(hoy);
            fechaFin.setHours(23, 59, 59, 999);

            switch(periodo) {
                case 'semana':
                    fechaInicio = getInicioSemana();
                    break;
                case 'mes':
                    fechaInicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
                    fechaInicio.setHours(0, 0, 0, 0);
                    break;
                case 'rango':
                    const desde = document.getElementById('fechaDesde').value;
                    const hasta = document.getElementById('fechaHasta').value;
                    if (desde && hasta) {
                        fechaInicio = new Date(desde + 'T00:00:00');
                        fechaFin = new Date(hasta + 'T23:59:59');
                    } else {
                        // Si no hay fechas seleccionadas, mostrar todos
                        fechaInicio = null;
                    }
                    break;
                default:
                    // 'todos' - mostrar todos los datos
                    fechaInicio = null;
            }

            // Filtrar histórico diario
            const historicoFiltrado = {};
            let totalFiltrado = 0;

            Object.keys(historico).forEach(fecha => {
                const fechaObj = new Date(fecha + 'T00:00:00');
                
                if (!fechaInicio || (fechaObj >= fechaInicio && fechaObj <= fechaFin)) {
                    historicoFiltrado[fecha] = historico[fecha];
                    totalFiltrado += historico[fecha].total || 0;
                }
            });

            // Si el período es 'todos', usar el resumen_general original
            // Si no está disponible, calcular sumando todos los días
            // De lo contrario, usar el total calculado del filtro
            let totalFinal;
            if (periodo === 'todos' || (!fechaInicio && periodo !== 'rango')) {
                // Usar el resumen_general original cuando es 'todos'
                if (trackingData.resumen_general && trackingData.resumen_general > 0) {
                    totalFinal = trackingData.resumen_general;
                } else {
                    // Si no hay resumen_general, calcular sumando todos los días
                    totalFinal = Object.values(historico).reduce((sum, dia) => sum + (dia.total || 0), 0);
                }
            } else {
                // Usar el total calculado del filtro para otros períodos
                totalFinal = totalFiltrado;
            }

            datosFiltrados = {
                resumen_general: totalFinal,
                historico_diario: historicoFiltrado
            };

            return datosFiltrados;
        }

        // Función para actualizar todos los componentes
        function actualizarVista() {
            // Actualizar total de visitas
            document.getElementById('totalVisitas').textContent = datosFiltrados.resumen_general || 0;

            // Actualizar gráficos
            actualizarGraficos();

            // Actualizar tabla
            llenarTablaHistorial();
        }

        // Función para actualizar gráficos
        function actualizarGraficos() {
            // Preparar datos para gráfico de visitas por día
            const fechas = Object.keys(datosFiltrados.historico_diario || {}).sort();
            const visitasPorDia = fechas.map(fecha => datosFiltrados.historico_diario[fecha].total || 0);

            // Actualizar gráfico de visitas por día
            if (visitasPorDiaChart) {
                visitasPorDiaChart.data.labels = fechas.map(formatFecha);
                visitasPorDiaChart.data.datasets[0].data = visitasPorDia;
                visitasPorDiaChart.update();
            } else {
                // Crear gráfico si no existe
                const ctxDia = document.getElementById('visitasPorDiaChart');
                if (ctxDia) {
                    visitasPorDiaChart = new Chart(ctxDia, {
                        type: 'line',
                        data: {
                            labels: fechas.map(formatFecha),
                            datasets: [{
                                label: 'Visitas',
                                data: visitasPorDia,
                                borderColor: 'rgb(59, 130, 246)',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                tension: 0.4,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });
                }
            }

            // Preparar datos para gráfico de visitas por hora
            const horasMap = {};
            let totalDias = 0;

            Object.values(datosFiltrados.historico_diario || {}).forEach(dia => {
                if (dia.por_horas) {
                    totalDias++;
                    Object.keys(dia.por_horas).forEach(hora => {
                        if (!horasMap[hora]) {
                            horasMap[hora] = 0;
                        }
                        horasMap[hora] += dia.por_horas[hora];
                    });
                }
            });

            // Calcular promedio por hora
            const horas = Object.keys(horasMap).sort((a, b) => parseInt(a) - parseInt(b));
            const promediosPorHora = horas.map(hora => {
                return totalDias > 0 ? (horasMap[hora] / totalDias).toFixed(2) : 0;
            });

            // Actualizar gráfico de visitas por hora
            if (visitasPorHoraChart) {
                visitasPorHoraChart.data.labels = horas.map(h => h + ':00');
                visitasPorHoraChart.data.datasets[0].data = promediosPorHora;
                visitasPorHoraChart.update();
            } else {
                // Crear gráfico si no existe
                const ctxHora = document.getElementById('visitasPorHoraChart');
                if (ctxHora) {
                    visitasPorHoraChart = new Chart(ctxHora, {
                        type: 'bar',
                        data: {
                            labels: horas.map(h => h + ':00'),
                            datasets: [{
                                label: 'Promedio de Visitas',
                                data: promediosPorHora,
                                backgroundColor: 'rgba(34, 197, 94, 0.6)',
                                borderColor: 'rgb(34, 197, 94)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }
            }
        }

        // Función para inicializar gráficos
        function inicializarGraficos() {
            // Preparar datos para gráfico de visitas por día
            const fechas = Object.keys(datosFiltrados.historico_diario || {}).sort();
            const visitasPorDia = fechas.map(fecha => datosFiltrados.historico_diario[fecha].total || 0);

            actualizarGraficos();
        }

        // Función para llenar tabla de historial
        function llenarTablaHistorial() {
            const tbody = document.getElementById('historialTableBody');
            if (!tbody) return;

            tbody.innerHTML = '';

            const fechas = Object.keys(datosFiltrados.historico_diario || {}).sort().reverse();

            if (fechas.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No hay datos disponibles</td></tr>';
                return;
            }

            fechas.forEach(fecha => {
                const dia = datosFiltrados.historico_diario[fecha];
                const total = dia.total || 0;
                
                // Encontrar hora pico
                let horaPico = '-';
                let maxVisitas = 0;
                if (dia.por_horas) {
                    Object.keys(dia.por_horas).forEach(hora => {
                        if (dia.por_horas[hora] > maxVisitas) {
                            maxVisitas = dia.por_horas[hora];
                            horaPico = hora + ':00';
                        }
                    });
                }

                // Crear distribución por horas
                let distribucion = '-';
                if (dia.por_horas && Object.keys(dia.por_horas).length > 0) {
                    const horasDist = Object.keys(dia.por_horas)
                        .sort((a, b) => parseInt(a) - parseInt(b))
                        .map(h => `${h}:00 (${dia.por_horas[h]})`)
                        .join(', ');
                    distribucion = horasDist;
                }

                const row = document.createElement('tr');
                row.className = 'bg-white border-b dark:bg-gray-800 dark:border-gray-700';
                row.innerHTML = `
                    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">${formatFecha(fecha)}</td>
                    <td class="px-6 py-4">${total}</td>
                    <td class="px-6 py-4">${horaPico}</td>
                    <td class="px-6 py-4 text-xs">${distribucion}</td>
                `;
                tbody.appendChild(row);
            });
        }

        // Función para descargar PNG
        async function descargarPNG() {
            const content = document.getElementById('analyticsContent');
            const filtrosContainer = document.getElementById('filtrosContainer');
            if (!content) return;

            try {
                // Ocultar filtros temporalmente
                if (filtrosContainer) {
                    filtrosContainer.style.display = 'none';
                }

                // Esperar un momento para que el DOM se actualice
                await new Promise(resolve => setTimeout(resolve, 100));

                const canvas = await html2canvas(content, {
                    backgroundColor: '#111827',
                    scale: 2,
                    logging: false
                });

                // Mostrar filtros de nuevo
                if (filtrosContainer) {
                    filtrosContainer.style.display = '';
                }

                const link = document.createElement('a');
                link.download = `analitica_${restaurantName}_${new Date().toISOString().split('T')[0]}.png`;
                link.href = canvas.toDataURL('image/png');
                link.click();
            } catch (error) {
                console.error('Error al generar PNG:', error);
                // Asegurarse de mostrar los filtros incluso si hay error
                if (filtrosContainer) {
                    filtrosContainer.style.display = '';
                }
                alert('Error al generar la imagen PNG');
            }
        }

        // Función para descargar PDF
        async function descargarPDF() {
            const { jsPDF } = window.jspdf;
            const content = document.getElementById('analyticsContent');
            const filtrosContainer = document.getElementById('filtrosContainer');
            if (!content) return;

            try {
                // Ocultar filtros temporalmente
                if (filtrosContainer) {
                    filtrosContainer.style.display = 'none';
                }

                // Esperar un momento para que el DOM se actualice
                await new Promise(resolve => setTimeout(resolve, 100));

                const canvas = await html2canvas(content, {
                    backgroundColor: '#111827',
                    scale: 2,
                    logging: false
                });

                // Mostrar filtros de nuevo
                if (filtrosContainer) {
                    filtrosContainer.style.display = '';
                }

                const imgData = canvas.toDataURL('image/png');
                const pdf = new jsPDF('p', 'mm', 'a4');
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = pdf.internal.pageSize.getHeight();
                const imgWidth = canvas.width;
                const imgHeight = canvas.height;
                const ratio = Math.min(pdfWidth / imgWidth, pdfHeight / imgHeight);
                const imgX = (pdfWidth - imgWidth * ratio) / 2;
                const imgY = 0;

                pdf.addImage(imgData, 'PNG', imgX, imgY, imgWidth * ratio, imgHeight * ratio);
                pdf.save(`analitica_${restaurantName}_${new Date().toISOString().split('T')[0]}.pdf`);
            } catch (error) {
                console.error('Error al generar PDF:', error);
                // Asegurarse de mostrar los filtros incluso si hay error
                if (filtrosContainer) {
                    filtrosContainer.style.display = '';
                }
                alert('Error al generar el PDF');
            }
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar datos filtrados (todos por defecto)
            datosFiltrados = filtrarDatos('todos');

            // Actualizar la vista con los datos iniciales
            actualizarVista();

            // Event listener para cambio de período
            document.getElementById('filtroPeriodo').addEventListener('change', function() {
                const periodo = this.value;
                const rangoContainer = document.getElementById('rangoFechasContainer');
                
                if (periodo === 'rango') {
                    rangoContainer.classList.remove('hidden');
                } else {
                    rangoContainer.classList.add('hidden');
                    filtrarDatos(periodo);
                    actualizarVista();
                }
            });

            // Event listener para aplicar rango de fechas
            document.getElementById('aplicarRango').addEventListener('click', function() {
                const desde = document.getElementById('fechaDesde').value;
                const hasta = document.getElementById('fechaHasta').value;
                
                if (!desde || !hasta) {
                    alert('Por favor selecciona ambas fechas');
                    return;
                }
                
                if (new Date(desde) > new Date(hasta)) {
                    alert('La fecha de inicio debe ser anterior a la fecha de fin');
                    return;
                }
                
                filtrarDatos('rango');
                actualizarVista();
            });

            // Establecer fecha máxima como hoy para los inputs de fecha
            const hoy = new Date().toISOString().split('T')[0];
            document.getElementById('fechaDesde').setAttribute('max', hoy);
            document.getElementById('fechaHasta').setAttribute('max', hoy);

            // Event listeners para descargas
            document.getElementById('downloadPNG').addEventListener('click', descargarPNG);
            document.getElementById('downloadPDF').addEventListener('click', descargarPDF);

            // Logout
            document.getElementById('confirmLogout')?.addEventListener('click', function() {
                window.location.href = '<?php echo base_path('index.php?route=logout'); ?>';
            });
        });
    </script>
</body>
</html>
