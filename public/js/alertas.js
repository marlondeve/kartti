// alertas.js

// Variables globales para el sistema de actualización automática
let autoRefreshInterval;
let lastAlertCount = 0;
let notificationsPermission = false;

document.addEventListener('DOMContentLoaded', async function () {
    await loadAlertasPendientes();
    
    // Solicitar permisos para notificaciones
    await requestNotificationPermission();
    
    // Iniciar actualización automática cada 5 segundos
    startAutoRefresh();

    // Tabs
    const tabPendientes = document.getElementById('tabPendientes');
    const tabResueltas = document.getElementById('tabResueltas');
    const contPendientes = document.getElementById('alertasContainer');
    const contResueltas = document.getElementById('alertasResueltasContainer');

    if (tabPendientes && tabResueltas) {
        tabPendientes.addEventListener('click', async function () {
            tabPendientes.classList.add('active');
            tabResueltas.classList.remove('active');
            contPendientes.classList.remove('hidden');
            contResueltas.classList.add('hidden');
            await loadAlertasPendientes();
        });
        tabResueltas.addEventListener('click', async function () {
            tabResueltas.classList.add('active');
            tabPendientes.classList.remove('active');
            contResueltas.classList.remove('hidden');
            contPendientes.classList.add('hidden');
            await loadAlertasResueltas();
        });
    }

    // Event listeners para los modales
    setupModalEventListeners();

    // Delegación de evento para actualizar estado de alerta y eliminar
    contPendientes.addEventListener('click', async function (e) {
        if (e.target.classList.contains('btn-resolver')) {
            const alertaId = e.target.dataset.alertaId;
            await actualizarEstadoAlerta(alertaId, 'resuelta');
        }
        if (e.target.classList.contains('btn-eliminar-alerta')) {
            const alertaId = e.target.dataset.alertaId;
            if (confirm('¿Seguro que deseas eliminar esta alerta?')) {
                await eliminarAlerta(alertaId);
            }
        }
    });
    contResueltas.addEventListener('click', async function (e) {
        if (e.target.classList.contains('btn-eliminar-alerta')) {
            const alertaId = e.target.dataset.alertaId;
            if (confirm('¿Seguro que deseas eliminar esta alerta?')) {
                await eliminarAlerta(alertaId);
            }
        }
    });
    // Botón global eliminar todas
    const btnEliminarTodas = document.getElementById('btnEliminarTodasAlertas');
    if (btnEliminarTodas) {
        btnEliminarTodas.addEventListener('click', async function() {
            let estado = contPendientes.classList.contains('hidden') ? 'resuelta' : 'pendiente';
            if (confirm('¿Seguro que deseas eliminar TODAS las alertas ' + estado + 's? Esta acción no se puede deshacer.')) {
                await eliminarTodasAlertas(estado);
            }
        });
    }
});

async function loadAlertasPendientes() {
    renderCargando('alertasContainer');
    showRefreshIndicator();
    
    try {
        const response = await fetch((window.BASE_PATH || '') + '/api/api_alertas.php?estado=pendiente', {
            method: 'GET',
        });
        const data = await response.json();
        
        updateConnectionStatus(true); // Conexión exitosa
        
        if (data.success) {
            renderAlertas('alertasContainer', data.data, true);
            // Actualizar contador para el auto-refresh
            lastAlertCount = data.data.length;
            updatePageTitle(lastAlertCount);
            updateAlertCounter(lastAlertCount);
            updateLastUpdateTime();
        } else {
            renderError('alertasContainer', data.message || 'Error al cargar alertas');
        }
    } catch (error) {
        renderError('alertasContainer', 'Error al cargar alertas');
        updateConnectionStatus(false); // Error de conexión
    } finally {
        hideRefreshIndicator();
    }
}

async function loadAlertasResueltas() {
    renderCargando('alertasResueltasContainer');
    try {
        const response = await fetch((window.BASE_PATH || '') + '/api/api_alertas.php?estado=resuelta', {
            method: 'GET',
        });
        const data = await response.json();
        if (data.success) {
            renderAlertas('alertasResueltasContainer', data.data, false);
        } else {
            renderError('alertasResueltasContainer', data.message || 'Error al cargar alertas');
        }
    } catch (error) {
        renderError('alertasResueltasContainer', 'Error al cargar alertas');
    }
}
document.getElementById('confirmLogout').addEventListener('click', function() {
    window.location.href = (window.BASE_PATH || '') + '/index.php?route=logout';

function renderCargando(containerId) {
    const container = document.getElementById(containerId);
    container.innerHTML = '<div class="text-gray-500">Cargando alertas...</div>';
}

function renderError(containerId, msg) {
    const container = document.getElementById(containerId);
    container.innerHTML = `<div class='text-red-500'>${msg}</div>`;
}

function renderAlertas(containerId, alertas, pendientes = true) {
    const container = document.getElementById(containerId);
    if (!alertas || alertas.length === 0) {
        container.innerHTML = `<div class="text-gray-500 text-center py-10"><i class=\"fas fa-bell-slash text-4xl mb-2 text-gray-300\"></i><br>No hay alertas ${pendientes ? 'pendientes' : 'resueltas'}</div>`;
        
        // Actualizar contador en pestaña
        if (pendientes) {
            updateAlertCounter(0);
        }
        return;
    }
    
    // Actualizar contador en pestaña si son pendientes
    if (pendientes) {
        updateAlertCounter(alertas.length);
    }
    container.innerHTML = alertas.map(alerta => {
        const isPendiente = alerta.estado === 'pendiente' && pendientes;
        const isResuelta = alerta.estado !== 'pendiente' || !pendientes;
        return `
        <div class="mb-4 p-6 rounded-xl shadow-lg flex flex-col md:flex-row md:items-center md:justify-between border-l-8 ${isPendiente ? 'border-blue-900 bg-gray-800 text-white' : 'border-gray-900 bg-gray-900 text-gray-300'} ${isResuelta ? 'opacity-60' : ''}">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-14 h-14 rounded-full ${isPendiente ? 'bg-yellow-400 text-yellow-700' : 'bg-gray-300 text-gray-500'} text-2xl shadow border-4 border-white dark:border-gray-900">
                    <i class="fas fa-bell"></i>
                </div>
                <div>
                    <div class="font-bold text-lg text-yellow-900 dark:text-yellow-100">
                        ${alerta.nombre} <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-yellow-200 text-yellow-900 border border-yellow-400">${alerta.tipo}</span>
                    </div>
                    <div class="text-xs text-gray-700 dark:text-gray-200 mt-1"><i class="far fa-clock mr-1 text-yellow-500"></i>${new Date(alerta.fecha_creacion).toLocaleString()}</div>
                    <div class="mt-2 text-xs text-yellow-800 dark:text-yellow-200">Estado: <span class="font-bold uppercase ${isPendiente ? 'text-yellow-600' : 'text-green-600'}">${alerta.estado}</span></div>
                </div>
            </div>
            <div class="flex flex-row gap-2 mt-4 md:mt-0">
                ${pendientes ? `<button class=\"btn-resolver px-5 py-2 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg shadow transition flex items-center gap-2\" data-alerta-id=\"${alerta.id}\"><i class=\"fas fa-check-circle\"></i> Marcar como resuelta</button>` : ''}
                ${pendientes ? `<button class=\"btn-eliminar-alerta px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow transition flex items-center gap-2\" data-alerta-id=\"${alerta.id}\"><i class=\"fas fa-trash\"></i> Eliminar</button>` : ''}
            </div>
        </div>
        `;
    }).join('');
}


async function eliminarAlerta(alertaId) {
    try {
        const response = await fetch((window.BASE_PATH || '') + '/api/api_alertas.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `alerta_id=${encodeURIComponent(alertaId)}`
        });
        const data = await response.json();
        if (data.success) {
            await loadAlertasPendientes();
            await loadAlertasResueltas();
        } else {
            showErrorModal(data.message || 'No se pudo eliminar la alerta');
        }
    } catch (error) {
        showErrorModal('Error al eliminar la alerta');
    }
}

async function eliminarTodasAlertas(estado) {
    try {
        const response = await fetch((window.BASE_PATH || '') + '/api/api_alertas.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `all=true&estado=${encodeURIComponent(estado)}`
        });
        const data = await response.json();
        if (data.success) {
            await loadAlertasPendientes();
            await loadAlertasResueltas();
        } else {
            showErrorModal(data.message || 'No se pudieron eliminar las alertas');
        }
    } catch (error) {
        showErrorModal('Error al eliminar todas las alertas');
    }
}

async function actualizarEstadoAlerta(alertaId, nuevoEstado) {
    showRefreshIndicator();
    try {
        const response = await fetch((window.BASE_PATH || '') + '/api/api_alertas.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ alerta_id: alertaId, estado: nuevoEstado })
        });
        const data = await response.json();
        if (data.success) {
            await loadAlertasPendientes();
            await loadAlertasResueltas();
            // Mostrar modal de éxito
            showModal('modalAlertaResuelta');
        } else {
            showErrorModal(data.message || 'No se pudo actualizar la alerta');
        }
    } catch (error) {
        showErrorModal('Error al actualizar la alerta');
        updateConnectionStatus(false);
    } finally {
        hideRefreshIndicator();
    }
}

// Función para solicitar permisos de notificación
async function requestNotificationPermission() {
    if ('Notification' in window) {
        const permission = await Notification.requestPermission();
        notificationsPermission = permission === 'granted';
        
        if (notificationsPermission) {
            updateConnectionStatus(true, 'Notificaciones activadas');
        } else {
            updateConnectionStatus(true, 'Notificaciones desactivadas');
        }
    } else {
        updateConnectionStatus(true, 'Navegador sin soporte');
    }
}

// Función para mostrar notificación nativa del navegador
function showBrowserNotification(title, body, icon = '/public/img/logo.png') {
    if (!notificationsPermission) return;
    
    const notification = new Notification(title, {
        body: body,
        icon: icon,
        badge: icon,
        tag: 'nueva-alerta', // Para reemplazar notificaciones anteriores
        requireInteraction: false,
        silent: false
    });
    
    // Auto-cerrar después de 5 segundos
    setTimeout(() => {
        notification.close();
    }, 5000);
    
    // Enfocar la ventana cuando se hace click en la notificación
    notification.onclick = function() {
        window.focus();
        notification.close();
    };
}

// Funciones para actualización automática
function startAutoRefresh() {
    // Limpiar intervalo existente si existe
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
    
    // Configurar nuevo intervalo de 5 segundos
    autoRefreshInterval = setInterval(async function() {
        await checkForNewAlerts();
    }, 5000);
    
    updateLastUpdateTime();
    
    // Ejecutar primera verificación inmediatamente
    setTimeout(async () => {
        await checkForNewAlerts();
    }, 1000);
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
}

async function checkForNewAlerts() {
    try {
        const response = await fetch((window.BASE_PATH || '') + '/api/api_alertas.php?estado=pendiente', {
            method: 'GET',
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            updateConnectionStatus(true); // Conexión exitosa
            const currentAlertCount = data.data.length;
            
            // Si hay cambios en el número de alertas
            if (currentAlertCount !== lastAlertCount) {
                // Mostrar notificación si hay más alertas que antes
                if (currentAlertCount > lastAlertCount) {
                    const newAlertsCount = currentAlertCount - lastAlertCount;
                    
                    // Obtener información de la alerta más reciente para la notificación
                    const latestAlert = data.data[0]; // La primera alerta es la más reciente
                    
                    // Notificación nativa del navegador
                    if (newAlertsCount === 1 && latestAlert) {
                        showBrowserNotification(
                            '🔔 Nueva Solicitud de Mesero',
                            `Mesa: ${latestAlert.nombre} (${latestAlert.tipo}) - ${new Date(latestAlert.fecha_creacion).toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' })}`,
                        );
                    } else {
                        showBrowserNotification(
                            '🔔 Múltiples Solicitudes de Mesero',
                            `Se han recibido ${newAlertsCount} nuevas alertas. Total pendientes: ${currentAlertCount}`,
                        );
                    }
                    
                    // Notificación visual en pantalla (como respaldo)
                    showNewAlertNotification();
                }
                
                // Actualizar las alertas pendientes SOLO si estamos viendo esa pestaña
                const contPendientes = document.getElementById('alertasContainer');
                if (contPendientes && !contPendientes.classList.contains('hidden')) {
                    renderAlertas('alertasContainer', data.data, true);
                }
                
                // Siempre actualizar estos elementos independientemente de la pestaña activa
                lastAlertCount = currentAlertCount;
                updatePageTitle(currentAlertCount);
                updateAlertCounter(currentAlertCount);
                updateLastUpdateTime();
            } else {
                updateLastUpdateTime(); // Solo actualizar el tiempo
            }
        } else {
            // Verificar si es un error de sesión
            if (data.message && data.message.includes('Sesión no válida')) {
                window.location.href = (window.BASE_PATH || '') + '/index.php?route=login';
                return;
            }
            
            updateConnectionStatus(false, 'Error de API');
        }
    } catch (error) {
        updateConnectionStatus(false); // Error de conexión
    }
}


function updatePageTitle(alertCount) {
    const originalTitle = document.title.replace(/\(\d+\) /, ''); // Remover contador anterior
    if (alertCount > 0) {
        document.title = `(${alertCount}) ${originalTitle}`;
    } else {
        document.title = originalTitle;
    }
}

// Actualizar contador visual en la pestaña
function updateAlertCounter(count) {
    const counter = document.getElementById('alertCounter');
    if (counter) {
        if (count > 0) {
            counter.textContent = count;
            counter.classList.remove('hidden');
        } else {
            counter.classList.add('hidden');
        }
    }
}

// Actualizar indicador de estado de conexión
function updateConnectionStatus(online = true, customMessage = null) {
    const indicator = document.getElementById('statusIndicator');
    
    if (indicator) {
        if (online) {
            const message = customMessage || 'Actualización automática activa';
            indicator.className = 'flex items-center gap-2 px-3 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg text-sm font-medium';
            indicator.innerHTML = `<div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div><span>${message}</span>`;
        } else {
            indicator.className = 'flex items-center gap-2 px-3 py-1 status-offline rounded-lg text-sm font-medium';
            indicator.innerHTML = '<div class="w-2 h-2 bg-red-500 rounded-full"></div><span>Sin conexión</span>';
        }
    }
}

// Actualizar tiempo de última actualización
function updateLastUpdateTime() {
    const timeElement = document.getElementById('lastUpdateTime');
    if (timeElement) {
        const now = new Date();
        const timeString = now.toLocaleTimeString('es-CO', { 
            hour: '2-digit', 
            minute: '2-digit',
            second: '2-digit'
        });
        timeElement.textContent = `Última actualización: ${timeString}`;
    }
}

// Mostrar indicador de actualización (solo en el contenedor)
function showRefreshIndicator() {
    const container = document.getElementById('alertasContainer');
    if (container && !container.classList.contains('hidden')) {
        container.classList.add('updating');
    }
}

// Ocultar indicador de actualización
function hideRefreshIndicator() {
    const container = document.getElementById('alertasContainer');
    if (container) {
        container.classList.remove('updating');
    }
}

function showNewAlertNotification() {
    // Crear notificación temporal mejorada
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-blue-600 text-white px-4 py-3 rounded-lg shadow-lg z-50 notification-slide flex items-center gap-2';
    notification.innerHTML = '<i class="fas fa-bell mr-2"></i><strong>Nueva alerta recibida</strong><i class="fas fa-times ml-2 cursor-pointer" onclick="this.parentElement.remove()"></i>';
    
    document.body.appendChild(notification);
    
    // Remover la notificación después de 4 segundos
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }
    }, 4000);
}

// Funciones de modal
function showModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function hideModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function showErrorModal(message) {
    document.getElementById('modalErrorMessage').textContent = message;
    showModal('modalError');
}

function setupModalEventListeners() {
    // Event listeners para cerrar modales
    document.getElementById('btnCerrarModalExito').addEventListener('click', function() {
        hideModal('modalAlertaResuelta');
    });
    
    document.getElementById('btnCerrarModalError').addEventListener('click', function() {
        hideModal('modalError');
    });
    
    // Cerrar modales al hacer click fuera
    ['modalAlertaResuelta', 'modalError'].forEach(modalId => {
        document.getElementById(modalId).addEventListener('click', function(e) {
            if (e.target === this) {
                hideModal(modalId);
            }
        });
    });
}

// Limpiar intervalo cuando la página se cierra
window.addEventListener('beforeunload', function() {
    stopAutoRefresh();
});
