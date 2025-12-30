// Variables globales (mantener estas)
let estructuraMenu = {};
let menuData = {};
let currentCategory = '';
let opcional = '';

// Función modificada para obtener datos del JSON local
function showSpinner() {
    document.getElementById('spinnerOverlay').style.display = 'flex';
}

function hideSpinner() {
    document.getElementById('spinnerOverlay').style.display = 'none';
}

async function obtenerDatosMenu() {
    showSpinner();
    try {
        // Obtener el ID del restaurante de la URL
        const urlParams = new URLSearchParams(window.location.search);
        const restauranteId = urlParams.get('id');
        const qrNombre = urlParams.get('nombre');
        const qrTipo = urlParams.get('tipo');
        
        tomarDatos(restauranteId, qrNombre, qrTipo);

        if (!restauranteId) {
            throw new Error('ID de restaurante no proporcionado');
        }

        // Construir la ruta al archivo JSON local
        // Detectar el base path automáticamente
        const basePath = window.location.pathname.split('/carta')[0] || '';
        const jsonPath = `${basePath}/public/json/restaurante${restauranteId}.json`;

        try {
            const respuesta = await fetch(jsonPath);
            
            if (!respuesta.ok) {
                throw new Error('No se encontró el archivo JSON del restaurante');
            }
            
            const datosMenu = await respuesta.json();
            estructuraMenu = datosMenu;
            hideSpinner();
            return datosMenu;
        } catch (error) {
            console.error('Error al cargar el archivo JSON:', error);
            hideSpinner();
            return {};
        }
    } catch (error) {
        console.error('Error al obtener los datos del menú:', error);
        hideSpinner();
        return {};
    }
}

function tomarDatos(id, nombre, tipo){

    document.getElementById('mesa_id').value = id;
    document.getElementById('qrNombre').value = nombre;
    document.getElementById('qrTipo').value = tipo;
    opcional = nombre;
}
function llamarMesero() {
    // Mostrar modal de confirmación
    mostrarModal('modalConfirmacion');
}

async function enviarAlertaMesero() {
    ocultarModal('modalConfirmacion');
    showSpinner();
    
    try {
        // Obtener los datos del QR
        const restaurante_id = document.getElementById('mesa_id').value;
        const tipo = document.getElementById('qrTipo').value;
        const nombre = document.getElementById('qrNombre').value;

        // Detectar el base path automáticamente
        const basePath = window.location.pathname.split('/carta')[0] || '';
        const response = await fetch(`${basePath}/api/api_alertas.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                restaurante_id: restaurante_id,
                tipo: tipo,
                nombre: nombre
            })
        });

        const data = await response.json();
        hideSpinner();

        if (data.success) {
            mostrarModal('modalExito');
        } else {
            throw new Error(data.message || 'Error al llamar al mesero');
        }
    } catch (error) {
        hideSpinner();
        console.error('Error:', error);
        document.getElementById('errorMessage').textContent = error.message || 'Hubo un error al intentar llamar al mesero. Por favor, intente nuevamente.';
        mostrarModal('modalError');
    }
}

function mostrarModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function ocultarModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Event listeners para los modales
document.addEventListener('DOMContentLoaded', function() {
    // Modal de confirmación
    document.getElementById('btnCancelar').addEventListener('click', function() {
        ocultarModal('modalConfirmacion');
    });
    
    document.getElementById('btnConfirmar').addEventListener('click', function() {
        enviarAlertaMesero();
    });
    
    // Modal de éxito
    document.getElementById('btnCerrarExito').addEventListener('click', function() {
        ocultarModal('modalExito');
    });
    
    // Modal de error
    document.getElementById('btnCerrarError').addEventListener('click', function() {
        ocultarModal('modalError');
    });
    
    // Cerrar modales al hacer click fuera
    ['modalConfirmacion', 'modalExito', 'modalError'].forEach(modalId => {
        document.getElementById(modalId).addEventListener('click', function(e) {
            if (e.target === this) {
                ocultarModal(modalId);
            }
        });
    });
});
// Función de inicialización (mantener esta función igual)
async function inicializarMenu() {
    // El spinner ya está visible por defecto
    try {
        menuData = await obtenerDatosMenu();
        
        if (Object.keys(menuData).length === 0) {
            console.error('No se pudieron cargar los datos del menú');
            return;
        }

        const mainContainer = document.getElementById('mainContainer');
        const sliderWrapper = document.getElementById('sliderWrapper');
        const dotsContainer = document.getElementById('dotsContainer');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');

        if (!mainContainer || !sliderWrapper || !dotsContainer || !prevBtn || !nextBtn) {
            console.error('Elementos del DOM necesarios no encontrados');
            return;
        }

        sliderWrapper.innerHTML = '';
        
        if (typeof generateCategoryButtons === 'function') {
            generateCategoryButtons();
        }
        
        if (typeof generateCardsForCategory === 'function') {
            currentCategory = Object.keys(menuData)[0];
            generateCardsForCategory(currentCategory);
            
            setTimeout(() => {
                if (typeof initializeSlider === 'function') {
                    initializeSlider();
                }
            }, 100);
        }
    } catch (error) {
        console.error('Error al inicializar el menú:', error);
    } finally {
        hideSpinner();
        // Activar animaciones
        setTimeout(() => {
            document.querySelectorAll('.fade-in').forEach(el => el.classList.add('show'));
            // Mostrar el botón de llamar mesero
            if(opcional != "EstosBurgers" && opcional != "ESTOS MALL" &&  opcional != "Caucasia" && opcional != "Montelíbano"){
            document.getElementById('btnLlamarMesero').classList.remove('hidden');
                
            }
        }, 100);
    }
}

// Mantener el event listener para DOMContentLoaded
document.addEventListener('DOMContentLoaded', inicializarMenu);

// Función para capitalizar la primera letra
function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
}

// Función para actualizar el título
function updateRestaurantTitle() {
    const urlParams = new URLSearchParams(window.location.search);
    const qrNombre = urlParams.get('nombre') || '';
    const qrTipo = urlParams.get('tipo') || '';
    const restauranteId = urlParams.get('id');
    
    // Detectar el base path automáticamente
    const basePath = window.location.pathname.split('/carta')[0] || '';
    
    if (!restauranteId) {
        window.location.href = `${basePath}/404.html`;
        return;
    }
    // Obtener el nombre del restaurante del JSON
    fetch(`${basePath}/public/json/qr${restauranteId}.json`)
        .then(response => {
            if (!response.ok) {
                throw new Error('QR no encontrado');
            }
            return response.json();
        })
        .then(data => {
            if (!data.qrs || data.qrs.length === 0) {
                throw new Error('No hay QRs disponibles');
            }

            // Buscar el QR específico por nombre y tipo
            const qrEncontrado = data.qrs.find(qr => 
                qr.nombre === qrNombre && 
                qr.tipo === qrTipo
            );

            if (!qrEncontrado) {
                throw new Error('QR no encontrado');
            }

            // Verificar si el QR está inactivo
            if (qrEncontrado.estado === 'inactivo') {
                throw new Error('QR inactivo');
            }

            const restauranteNombre = data.qrs[0].restaurante_nombre || 'Restaurante';
            const titleElement = document.getElementById('restaurantTitle');
            const qrTitleElement = document.getElementById('qrTitle');
            
            // Formatear el nombre del restaurante
            let restaurantName = capitalizeFirstLetter(restauranteNombre);
            if(restauranteId == "14"){
                restaurantName = "Estosburgers";
            }else if(restauranteId == "17"){
                restaurantName = "Estosburgers";
            }else if(restauranteId == "18"){
                restaurantName = "Estosburgers";
            }
            
            // Establecer el nombre del restaurante como título principal
            titleElement.textContent = restaurantName;
            
            // Establecer el nombre del QR debajo en texto pequeño
            if (qrNombre) {
                let qrName = capitalizeFirstLetter(qrNombre);
                if(restauranteId == "14"){
                    qrName = "Montelibano";
                }else if(restauranteId == "17"){
                    qrName = "Lorica";
                }else if(restauranteId == "18"){
                    qrName = "Caucasia";
                }
                qrTitleElement.textContent = qrName;
            } else {
                qrTitleElement.textContent = '';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const basePath = window.location.pathname.split('/carta')[0] || '';
            window.location.href = `${basePath}/404.html`;
        });
}

// Llamar a la función cuando se carga la página
document.addEventListener('DOMContentLoaded', updateRestaurantTitle);