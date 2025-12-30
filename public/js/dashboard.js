// Archivo dashboard.js - Funcionalidades específicas para el dashboard

// Función para cerrar sesión
async function cerrarSesion() {
    try {
        const response = await fetch('/api/api_auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'logout'
            })
        });

        const data = await response.json();

        if (response.ok) {
            // Redirigir al login
            window.location.href = 'login';
        } else {
            // Mostrar mensaje de error con un toast o notificación
            const errorMessage = data.error || 'Error al cerrar sesión';
            alert(errorMessage); // Podríamos mejorar esto con un toast en el futuro
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cerrar sesión. Por favor, intenta nuevamente.');
    }
}

// Función para cargar las estadísticas
async function loadStatistics() {
    try {
        console.log('Cargando estadísticas...');
        const response = await fetch('/api/estadisticas.php');
        const data = await response.json();
        
        if (data.success) {
            console.log('Estadísticas recibidas:', data.data);
            // Actualizar los contadores en el dashboard
            document.getElementById('categoriasActivas').textContent = data.data.categorias_activas;
            document.getElementById('productosActivos').textContent = data.data.productos_activos;
            document.getElementById('qrActivos').textContent = data.data.qr_activos;
        } else {
            console.error('Error al cargar estadísticas:', data.message);
        }
    } catch (error) {
        console.error('Error al cargar estadísticas:', error);
    }
}

// Inicializar funcionalidades del dashboard
function inicializarDashboard() {
    console.log('Inicializando dashboard.js');
    
    // Cargar estadísticas al iniciar
    loadStatistics();
    
    // Configurar el botón de confirmación de cierre de sesión
    const confirmLogoutButton = document.getElementById('confirmLogout');
    if (confirmLogoutButton) {
        confirmLogoutButton.addEventListener('click', function() {
            cerrarSesion();
        });
    }
    
    // Mejorar el manejo del backdrop del modal de logout
    const logoutButton = document.querySelector('[data-modal-toggle="logoutModal"]');
    const logoutModalBackdrop = document.getElementById('logoutModalBackdrop');
    const logoutModal = document.getElementById('logoutModal');
    
    function ocultarBackdropLogout() {
        if (logoutModalBackdrop) {
            logoutModalBackdrop.classList.remove('opacity-100');
            logoutModalBackdrop.classList.add('opacity-0');
            document.body.style.overflow = '';
            
            setTimeout(function() {
                logoutModalBackdrop.classList.add('hidden');
                logoutModalBackdrop.classList.remove('block');
            }, 300);
        }
    }
    
    // Mostrar el backdrop cuando se hace clic en el botón de cerrar sesión
    if (logoutButton && logoutModalBackdrop) {
        logoutButton.addEventListener('click', function() {
            logoutModalBackdrop.classList.remove('hidden');
            logoutModalBackdrop.classList.add('block');
            document.body.style.overflow = 'hidden';
            
            setTimeout(function() {
                logoutModalBackdrop.classList.add('opacity-100');
                logoutModalBackdrop.classList.remove('opacity-0');
            }, 10);
        });
    }
    
    // Ocultar el backdrop cuando se hace clic en cualquier botón que cierre el modal
    document.querySelectorAll('[data-modal-hide="logoutModal"]').forEach(button => {
        button.addEventListener('click', ocultarBackdropLogout);
    });
    
    // Observar cambios en la visibilidad del modal para asegurarnos que el backdrop siempre se oculte
    if (logoutModal) {
        // Configurar un observador para detectar cambios en la clase del modal
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class') {
                    const modalVisible = !logoutModal.classList.contains('hidden');
                    
                    if (!modalVisible) {
                        // Si el modal está oculto, asegurarnos de que el backdrop también lo esté
                        ocultarBackdropLogout();
                    }
                }
            });
        });
        
        // Iniciar la observación del modal
        observer.observe(logoutModal, { attributes: true });
    }

    // Inicializar el sidebar con Flowbite
    inicializarSidebar();
}

// Función para inicializar el sidebar
function inicializarSidebar() {
    if (typeof flowbite !== 'undefined') {
        console.log('Flowbite está disponible', flowbite);
        const drawer = new flowbite.Drawer(document.getElementById('default-sidebar'), {
            backdrop: true,
            bodyScrolling: true,
            edge: true,
            edgeOffset: '',
            onHide: () => {
                console.log('drawer is hidden');
            },
            onShow: () => {
                console.log('drawer is shown');
            },
            onToggle: () => {
                console.log('drawer has been toggled');
            }
        });
    } else {
        console.error('Flowbite no está disponible');
    }
}

// Función para verificar si el usuario tiene restaurante
async function verificarRestaurante() {
    try {
        console.log('Iniciando petición a API de restaurantes');
        const response = await fetch('/api/restaurantes.php?action=check');
        console.log('Respuesta recibida:', response);
        
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Datos recibidos:', data);
        
        if (!data.has_restaurant) {
            mostrarModalCrearRestaurante();
        } else {
            // Mostrar el nombre del restaurante
            mostrarNombreRestaurante(data);
        }
    } catch (error) {
        console.error('Error al verificar restaurante:', error);
    }
}

// Función para mostrar el modal de crear restaurante
function mostrarModalCrearRestaurante() {
    console.log('Usuario no tiene restaurante, mostrando modal');
    // Mostrar modal si no tiene restaurante
    const createRestaurantModal = document.getElementById('createRestaurantModal');
    console.log('Modal element:', createRestaurantModal);
    
    // Inicializar el modal de manera segura
    setTimeout(() => {
        try {
            console.log('Intentando mostrar modal');
            const modalBackdrop = document.getElementById('modalBackdrop');
            
            // Mostrar el fondo oscuro
            if (modalBackdrop) {
                // Asegurarse de que el backdrop está sobre todo el contenido
                document.body.style.overflow = 'hidden'; // Previene el scroll
                
                modalBackdrop.classList.remove('hidden');
                modalBackdrop.classList.add('block');
                
                // Forzar el render para asegurar que el backdrop cubre todo
                modalBackdrop.getBoundingClientRect();
                
                // Dar tiempo al DOM para que aplique el cambio de display antes de añadir opacidad
                setTimeout(() => {
                    modalBackdrop.classList.add('opacity-100');
                    modalBackdrop.classList.remove('opacity-0');
                    
                    // Verificar que el backdrop cubre el sidebar
                    const sidebar = document.getElementById('default-sidebar');
                    if (sidebar) {
                        console.log('Verificando que el backdrop cubre el sidebar');
                        // Forzar que el z-index del backdrop sea mayor que el del sidebar
                        modalBackdrop.style.zIndex = '49';
                    }
                }, 10);
                
                // Agregar un event listener al backdrop para evitar interacciones con elementos debajo
                modalBackdrop.addEventListener('click', function(e) {
                    // Evitar que se propague el clic a elementos por debajo
                    e.stopPropagation();
                    // No cerramos el modal al hacer clic en el backdrop porque queremos que creen el restaurante
                });
            }
            
            mostrarModalFlowbite(createRestaurantModal, modalBackdrop);
        } catch (error) {
            console.error('Error al mostrar el modal:', error);
            // Mostrar modal manualmente como último recurso
            createRestaurantModal.classList.remove('hidden');
            createRestaurantModal.classList.add('flex');
            
            // Mostrar el fondo oscuro incluso en caso de error
            mostrarBackdropManual();
        }
    }, 500); // Pequeño retraso para asegurarnos de que todo está cargado
}

// Función para mostrar el modal con Flowbite
function mostrarModalFlowbite(modalElement, modalBackdrop) {
    if (!modalElement) {
        console.error('Elemento modal no encontrado');
        return;
    }

    if (typeof flowbite !== 'undefined' && flowbite.Modal) {
        const modal = new flowbite.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false,
            onHide: () => {
                if (modalBackdrop) {
                    document.body.style.overflow = '';
                    modalBackdrop.classList.remove('opacity-100');
                    modalBackdrop.classList.add('opacity-0');
                    modalBackdrop.classList.add('hidden');
                }
            }
        });
        modal.show();
    } else {
        // Fallback para cuando Flowbite no está disponible
        modalElement.classList.remove('hidden');
        modalElement.classList.add('flex');
    }
}

// Función para mostrar el nombre del restaurante
function mostrarNombreRestaurante(data) {
    console.log('Usuario tiene restaurante, datos:', data);
    
    // Buscar todos los elementos con id restaurantName y seleccionar el segundo
    // Primer elemento: input form, Segundo elemento: span para mostrar nombre
    const restaurantElements = document.querySelectorAll('#restaurantName');
    console.log('Elementos encontrados:', restaurantElements.length);
    
    if (restaurantElements.length >= 2) {
        const displayElement = restaurantElements[1]; // El span está en la posición 1
        console.log('Elemento para mostrar encontrado:', displayElement);
        
        if (displayElement && data.restaurant_name) {
            displayElement.textContent = data.restaurant_name;
            console.log('Nombre del restaurante establecido:', data.restaurant_name);
        }
    } else if (restaurantElements.length === 1 && data.restaurant_name) {
        // Si solo hay un elemento, verificar si es el span o el input
        const element = restaurantElements[0];
        if (element.tagName.toLowerCase() === 'span') {
            element.textContent = data.restaurant_name;
            console.log('Nombre del restaurante establecido en único elemento:', data.restaurant_name);
        } else {
            console.error('Solo se encontró el elemento input, no el span');
        }
    } else {
        console.error('No se encontraron suficientes elementos con id restaurantName');
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded ejecutado en dashboard.js');
    inicializarDashboard();
    verificarRestaurante();
    
    // Configurar el formulario de creación de restaurante
    const createRestaurantForm = document.getElementById('createRestaurantForm');
    if (createRestaurantForm) {
        createRestaurantForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            try {
                // Obtener el valor del campo de nombre
                const restaurantName = document.getElementById('restaurantName').value;
                console.log('Nombre del restaurante:', restaurantName);
                
                if (!restaurantName || restaurantName.trim() === '') {
                    console.error('El nombre del restaurante está vacío');
                    alert('Por favor, ingresa un nombre válido para tu restaurante.');
                    return;
                }
                
                // Crear FormData y agregar los datos
                const formData = new FormData();
                formData.append('action', 'create');
                formData.append('nombre', restaurantName);
                
                console.log('Enviando datos del restaurante:', {
                    nombre: restaurantName,
                    action: 'create'
                });
                
                const response = await fetch('/api/restaurantes.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
                
                console.log('Respuesta del servidor:', response);
                
                if (!response.ok) {
                    const errorData = await response.json().catch(() => null);
                    console.error('Error en la respuesta:', errorData);
                    throw new Error(errorData?.message || `Error HTTP: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Datos recibidos:', data);
                
                if (data.success) {
                    console.log('Restaurante creado exitosamente');
                    window.location.reload();
                } else {
                    console.error('Error en la respuesta:', data);
                    alert(data.message || 'Error al crear el restaurante.');
                }
            } catch (error) {
                console.error('Error detallado:', error);
                alert('Error al crear el restaurante. Por favor, intenta nuevamente.');
            }
        });
    }
}); 