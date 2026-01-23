// Utilidades para hacer peticiones fetch
window.utils = {
    fetch: async function(url, options = {}) {
        try {
            // Normalizar base path si se ejecuta en el navegador (soporta fallback si no se inyectó BASE_PATH)
            let requestUrl = url;
            if (typeof window !== 'undefined' && typeof url === 'string' && url.startsWith('/') && !url.startsWith('//')) {
                // Preferir BASE_PATH inyectado por el servidor
                let base = (typeof window.BASE_PATH !== 'undefined' && window.BASE_PATH !== null) ? window.BASE_PATH : '';
                if (!base) {
                    // Infer base from current path by removing any trailing file (e.g., /kartti/index.php -> /kartti)
                    const pathname = (window.location && window.location.pathname) ? window.location.pathname : '';
                    // Remove trailing filename like index.php or any .php/.html/.htm
                    base = pathname.replace(/\/[^\/]*\.(php|html|htm)$/, '');
                    // Normalize empty or root to ''
                    if (base === '/' || base === '') base = '';
                }
                requestUrl = base + url;
            }
            // Debug: log api requests to help diagnose base path issues
            if (typeof requestUrl === 'string' && requestUrl.indexOf('/api/') !== -1) {
                console.debug('utils.fetch ->', requestUrl);
            }

            const response = await fetch(requestUrl, {
                ...options,
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error en la petición:', error);
            throw error;
        }
    }
}; 