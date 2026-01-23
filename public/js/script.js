// Funciones de utilidad
const utils = {
    // Función para hacer peticiones HTTP (base-path aware)
    async fetch(url, options = {}) {
        try {
            // Normalizar base path si la URL comienza con '/'
            let requestUrl = url;
            if (typeof window !== 'undefined' && typeof url === 'string' && url.startsWith('/') && !url.startsWith('//')) {
                const base = (typeof window.BASE_PATH !== 'undefined' && window.BASE_PATH) ? window.BASE_PATH : (window.location && window.location.pathname ? window.location.pathname.replace(/\/[^"]*\.(php|html|htm)$/, '') : '');
                requestUrl = (base === '/' ? '' : base) + url;
            }
            
            // Si es una petición GET, no incluir body
            if (options.method === 'GET' || !options.method) {
                delete options.body;
            }
            
            const response = await fetch(requestUrl, options);
            if (!response.ok) {
                const error = new Error(`HTTP error! status: ${response.status}`);
                error.response = response;
                error.status = response.status;
                throw error;
            }
            return await response.json();
        } catch (error) {
            // Si el error es sobre body en GET, lo ignoramos
            if (error.message.includes('Request with GET/HEAD method cannot have body')) {
                return;
            }
            console.error('Error en la petición:', error);
            throw error;
        }
    },


    // Función para mostrar notificaciones
    showNotification(type, message) {
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
};

// Filtrar mensajes de error de Flowbite
const originalConsoleError = console.error;
const originalConsoleWarn = console.warn;

console.error = function(...args) {
    if (typeof args[0] === 'string' && 
        (args[0].includes('Modal with id') || 
         args[0].includes('has not been initialized'))) {
        return;
    }
    originalConsoleError.apply(console, args);
};

console.warn = function(...args) {
    if (typeof args[0] === 'string' && 
        (args[0].includes('Instance with ID') || 
         args[0].includes('does not exist'))) {
        return;
    }
    originalConsoleWarn.apply(console, args);
};

// Manejo de formularios
document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        // Solo manejar formularios de login y registro
        if (form.id === 'loginForm' || form.id === 'registroForm') {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                try {
                    const formData = new FormData(form);
                    const data = Object.fromEntries(formData.entries());
                    
                    // Determinar la acción basada en el ID del formulario
                    if (form.id === 'loginForm') {
                        data.action = 'login';
                        // Convertir el valor del checkbox a booleano
                        data.remember_me = formData.get('remember_me') === 'on';
                    } else if (form.id === 'registroForm') {
                        data.action = 'registro';
                        // Asegurarnos de que el nombre se maneje correctamente
                        data.nombre = data.name;
                        delete data.name;
                    }
                    
                    const response = await utils.fetch(form.action, {
                        method: form.method,
                        body: JSON.stringify(data)
                    });
                    
                    if (response && response.success) {
                        utils.showNotification('success', response.message || 'Operación exitosa');
                        // Redirigir después de un breve delay (base-path aware)
                        setTimeout(() => {
                            let dest = '';
                            if (form.id === 'registroForm') {
                                // Registro -> verificar
                                if (typeof window !== 'undefined') {
                                    if (window.USE_PRETTY_URLS) {
                                        dest = (window.BASE_PATH || '') + '/verificar?email=' + encodeURIComponent(data.email);
                                    } else {
                                        dest = (window.BASE_PATH || '') + '/index.php?route=verificar&email=' + encodeURIComponent(data.email);
                                    }
                                } else {
                                    dest = '/verificar?email=' + encodeURIComponent(data.email);
                                }
                            } else {
                                // Login -> usar redirect del servidor si existe, haciendo base-path aware en caso de rutas absolutas
                                if (response && response.redirect) {
                                    if (response.redirect.startsWith('/')) {
                                        dest = (window.BASE_PATH || '') + response.redirect;
                                    } else {
                                        dest = response.redirect;
                                    }
                                } else {
                                    dest = window.USE_PRETTY_URLS ? (window.BASE_PATH || '') + '/dashboard' : (window.BASE_PATH || '') + '/index.php?route=dashboard';
                                }
                            }
                            window.location.href = dest;
                        }, 1000);
                    } else {
                        utils.showNotification('error', response?.message || 'Error en la operación');
                    }
                } catch (error) {
                    // Solo mostrar notificación si no es un error de GET con body
                    if (!error.message.includes('Request with GET/HEAD method cannot have body')) {
                        // Si es un error 400 y es el formulario de login, mostrar "Credenciales incorrectas"
                        if (error.status === 400 && form.id === 'loginForm') {
                            utils.showNotification('error', 'Credenciales incorrectas');
                        } else {
                            // Para otros errores, intentar obtener el mensaje del servidor
                            try {
                                const errorResponse = await error.response?.json();
                                utils.showNotification('error', errorResponse?.message || 'Error en la operación');
                            } catch {
                                utils.showNotification('error', 'Error en la operación');
                            }
                        }
                    }
                }
            });
        }
    });
});

// Exportar funciones para uso en otros archivos
window.utils = utils;
window.showNotification = utils.showNotification; 