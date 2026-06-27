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
            const timestamp = Date.now();
            const jsonPathWithCache = `${jsonPath}?t=${timestamp}`;
            const respuesta = await fetch(jsonPathWithCache, {
                cache: 'no-store',
                headers: {
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache'
                }
            });
            
            if (!respuesta.ok) {
                throw new Error('No se encontró el archivo JSON del restaurante');
            }
            
            const datosMenu = await respuesta.json();
            estructuraMenu = datosMenu;
            
            // APLICAR COLORES INMEDIATAMENTE después de cargar el JSON, antes de ocultar el spinner
            if (datosMenu._settings && datosMenu._settings.colors) {
                applyColorsImmediate(datosMenu._settings.colors);
            }
            
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

    // WhatsApp: abrir confirmación antes de redirigir
    (function(){
        const btnWhats = document.getElementById('btnWhatsApp');
        if (!btnWhats) return;

        btnWhats.addEventListener('click', function(e) {
            const href = btnWhats.getAttribute('href') || btnWhats.href;
            if (!href) {
                e.preventDefault();
                return;
            }
            e.preventDefault();
            const displayPhone = btnWhats.getAttribute('title') || '';
            const modalTextEl = document.getElementById('modalWhatsAppText');
            if (modalTextEl) {
                modalTextEl.textContent = displayPhone ? `Vas a salir del menú y se abrirá WhatsApp (${displayPhone}). ¿Deseas continuar?` : 'Vas a salir del menú y se abrirá WhatsApp. ¿Deseas continuar?';
            }
            mostrarModal('modalWhatsApp');
        });

        // Botones de la confirmación WhatsApp
        const btnCancel = document.getElementById('btnCancelarWhatsApp');
        const btnConfirm = document.getElementById('btnConfirmarWhatsApp');
        if (btnCancel) btnCancel.addEventListener('click', function() { ocultarModal('modalWhatsApp'); });
        if (btnConfirm) btnConfirm.addEventListener('click', function() { 
            ocultarModal('modalWhatsApp');
            const href = btnWhats.getAttribute('href') || btnWhats.href;
            if (href) window.open(href, '_blank');
        });
    })();
    
    // Cerrar modales al hacer click fuera
    ['modalConfirmacion', 'modalExito', 'modalError', 'modalWhatsApp'].forEach(modalId => {
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
        
        // Asegurar que los colores estén establecidos ANTES de generar elementos
        // Si window.currentColors no está establecido, aplicar colores ahora
        if (!window.currentColors && menuData._settings && menuData._settings.colors) {
            applyColorsImmediate(menuData._settings.colors);
        }
        
        if (typeof generateCategoryButtons === 'function') {
            // Generar botones de categorías
            generateCategoryButtons();
        }

        // Asegurar que la primera categoría válida quede seleccionada visualmente
        // y que sus productos se carguen al entrar
        const categoriasValidas = Object.keys(menuData).filter(key => !key.startsWith('_'));
        if (categoriasValidas.length > 0) {
            const primeraCategoria = categoriasValidas[0];
            if (typeof selectCategory === 'function') {
                // Forzar selección inicial aunque currentCategory ya tenga valor
                selectCategory(primeraCategoria, { force: true });
            } else if (typeof generateCardsForCategory === 'function') {
                currentCategory = primeraCategoria;
                generateCardsForCategory(currentCategory);
            }

            // Inicializar slider después de generar la primera página
            // Usar un timeout más largo para asegurar que el DOM esté completamente renderizado
            /**setTimeout(() => {
                // Recalcular altura de las cards con las dimensiones finales
                if (typeof generateCardsForCategory === 'function') {
                    // Forzar recálculo de alturas después de que todo esté renderizado
                    requestAnimationFrame(() => {
                        const cards = document.querySelectorAll('.product-card');
                        if (cards.length > 0) {
                            // Recalcular altura usando la función interna
                            const vh = window.innerHeight;
                            const header = document.querySelector('.flex.flex-col.justify-center.items-center');
                            const nav = document.querySelector('nav');
                            const bottom = document.querySelector('section.fixed.bottom-0');
                            const mainContainer = document.getElementById('mainContainer');
                            
                            if (header && nav && bottom && mainContainer) {
                                const headerHeight = header.offsetHeight;
                                const navHeight = nav.offsetHeight;
                                const bottomHeight = bottom.offsetHeight;
                                const mainPaddingTop = parseFloat(getComputedStyle(mainContainer).paddingTop);
                                const mainPaddingBottom = parseFloat(getComputedStyle(mainContainer).paddingBottom);
                                const safetyMargin = 8;
                                
                                const usedHeight = headerHeight + navHeight + bottomHeight + mainPaddingTop + mainPaddingBottom + safetyMargin;
                                const availableHeight = vh - usedHeight;
                                const gapBetweenRows = 8;
                                const cardHeight = (availableHeight / 3) - (gapBetweenRows * 2 / 3);
                                const finalHeight = Math.max(cardHeight+20, 120);
                                
                                cards.forEach(card => {
                                    card.style.height = `${finalHeight}px`;
                                });
                            }
                        }
                        
                        // Inicializar slider después de ajustar alturas
                        if (typeof initializeSlider === 'function') {
                            initializeSlider();
                        }
                    });
                } else if (typeof initializeSlider === 'function') {
                    initializeSlider();
                }
            }, 150);**/
        }
    } catch (error) {
        console.error('Error al inicializar el menú:', error);
    } finally {
        hideSpinner();
        // Activar animaciones
        setTimeout(() => {
            document.querySelectorAll('.fade-in').forEach(el => el.classList.add('show'));
            // Mostrar/ocultar botón de llamar mesero según settings en JSON (si existen), si no, aplicar reglas antiguas
            (function(){
                const btn = document.getElementById('btnLlamarMesero');
                const settingsFromJson = (menuData && menuData._settings) ? menuData._settings : null;
                if (settingsFromJson) {
                    if (btn) {
                        if (settingsFromJson.call_waiter_enabled == 1) {
                            btn.classList.remove('hidden');
                        } else {
                            btn.classList.add('hidden');
                        }
                    }
                    // Guardar whatsapp y actualizar botón
                    window.restaurantWhatsapp = settingsFromJson.whatsapp || null;
                    if (typeof updateWhatsAppButton === 'function') updateWhatsAppButton();
                    return;
                }

                // Comportamiento por compatibilidad si no hay settings en el JSON
                if (btn) {
                    if(opcional != "EstosBurgers" && opcional != "ESTOS MALL" &&  opcional != "Caucasia" && opcional != "Montelíbano"){
                        btn.classList.remove('hidden');
                    } else {
                        btn.classList.add('hidden');
                    }
                }
            })();
        }, 100);
        
        // Aplicar colores personalizados después de cargar los datos
        // Solo re-aplicar si window.currentColors no está establecido (por si acaso)
        setTimeout(() => {
            if (!window.currentColors && menuData._settings && menuData._settings.colors) {
                applyCustomColors();
            }
            // Re-aplicar colores después de generar las cards (usar window.currentColors si está disponible)
            setTimeout(() => {
                const colors = window.currentColors || 
                              ((menuData && menuData._settings) ? 
                               (menuData._settings.colors || {}) : {});
                if (Object.keys(colors).length > 0) {
                    updateElementColors(colors);
                }
            }, 300);
        }, 150);
    }
}

// Función para aplicar colores INMEDIATAMENTE (sin esperar a que se cargue todo)
function applyColorsImmediate(colors) {
    if (!colors || Object.keys(colors).length === 0) return;
    
    const root = document.documentElement;
    
    // Mapeo de nombres de colores del JSON a variables CSS
    const colorMap = {
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
    
    // Aplicar variables CSS INMEDIATAMENTE
    Object.keys(colorMap).forEach(key => {
        if (colors[key]) {
            root.style.setProperty(colorMap[key], colors[key]);
        }
    });
    
    // Aplicar fondo del body y html INMEDIATAMENTE con mayor especificidad
    if (colors.background) {
        document.body.style.backgroundColor = colors.background;
        document.body.style.setProperty('background-color', colors.background, 'important');
        // También aplicar al html para mayor especificidad y evitar estilos de Tailwind
        document.documentElement.style.setProperty('background-color', colors.background, 'important');
    }

    // Guardar colores activos globalmente para que otras funciones (getColor) los usen primero
    window.currentColors = { ...colors };
}

// Función para aplicar colores personalizados desde el JSON
function applyCustomColors() {
    const settings = (menuData && menuData._settings) ? menuData._settings : null;
    const colors = settings && settings.colors ? settings.colors : {};
    const root = document.documentElement;
    
    // Mapeo de nombres de colores del JSON a variables CSS
    const colorMap = {
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
    
    // Aplicar variables CSS - SIEMPRE usar los colores del JSON si existen
    if (Object.keys(colors).length > 0) {
        // Hay colores definidos en el JSON, usar esos
        Object.keys(colorMap).forEach(key => {
            if (colors[key]) {
                root.style.setProperty(colorMap[key], colors[key]);
            }
        });
        // Actualizar elementos específicos
        updateElementColors(colors);
        // IMPORTANTE: Guardar los colores actuales para que getColor() los use
        window.currentColors = { ...colors };
    } else {
        // No hay colores definidos, usar valores por defecto
        const defaultColors = {
            'background': '#1a1a1a',
            'card_background': '#272728',
            'primary': '#B38D57',
            'primary_dark': '#80653E',
            'primary_light': '#E4C57F',
            'text_primary': '#E4C57F',
            'text_secondary': '#B38D57',
            'header_bg': '#272728',
            'header_text': '#B38D57'
        };
        Object.keys(colorMap).forEach(key => {
            root.style.setProperty(colorMap[key], defaultColors[key]);
        });
        // Guardar valores por defecto también
        window.currentColors = { ...defaultColors };
    }

    // Guardar colores activos globalmente (aunque sean los por defecto)
    window.currentColors = { ...colors };
}

// Función para actualizar elementos con colores hardcodeados
function updateElementColors(colors) {
    // Actualizar fondo del body y html con !important para sobrescribir cualquier estilo
    if (colors.background) {
        document.body.style.backgroundColor = colors.background;
        document.body.style.setProperty('background-color', colors.background, 'important');
        // También aplicar al html para mayor especificidad
        document.documentElement.style.setProperty('background-color', colors.background, 'important');
    }
    
    // Actualizar título del restaurante
    const restaurantTitle = document.getElementById('restaurantTitle');
    if (restaurantTitle && colors.header_text) {
        restaurantTitle.style.color = colors.header_text;
    }
    
    // Actualizar título del QR
    const qrTitle = document.getElementById('qrTitle');
    if (qrTitle && colors.text_primary) {
        qrTitle.style.color = colors.text_primary;
    }
    
    // Actualizar contenedor del header
    const headerContainer = document.querySelector('.flex.flex-col.justify-center.items-center.w-\\[90\\%\\]');
    if (headerContainer && colors.card_background) {
        headerContainer.style.backgroundColor = colors.card_background;
    }
    
    // Actualizar botón de todas las categorías
    const btnTodasCategorias = document.getElementById('btnTodasCategorias');
    if (btnTodasCategorias) {
        if (colors.primary && colors.primary_dark) {
            btnTodasCategorias.style.background = `linear-gradient(to right, ${colors.primary}, ${colors.primary_dark})`;
        }
        if (colors.primary_light) {
            btnTodasCategorias.style.borderColor = colors.primary_light;
        }
    }
    
    // Actualizar spinner
    const spinner = document.querySelector('.spinner-overlay svg');
    if (spinner && colors.primary) {
        const fillPath = spinner.querySelector('path[fill]');
        if (fillPath) {
            fillPath.setAttribute('fill', colors.primary);
        }
    }
    
    // Actualizar botón llamar mesero
    const btnLlamarMesero = document.getElementById('btnLlamarMesero');
    if (btnLlamarMesero) {
        if (colors.primary_dark && colors.primary) {
            btnLlamarMesero.style.background = `linear-gradient(to right, ${colors.primary_dark}, ${colors.primary}, ${colors.primary_dark}, ${colors.primary_dark})`;
        }
    }
    
    // Actualizar botones de navegación prev/next
    const prevBtn = document.getElementById('prevBtnButton');
    const nextBtn = document.getElementById('nextBtnButton');
    if (prevBtn && colors.card_background) {
        prevBtn.style.backgroundColor = colors.card_background;
    }
    if (nextBtn && colors.card_background) {
        nextBtn.style.backgroundColor = colors.card_background;
    }
    if (prevBtn && colors.primary) {
        prevBtn.style.color = colors.primary;
    }
    if (nextBtn && colors.primary) {
        nextBtn.style.color = colors.primary;
    }
    
    // Actualizar sección inferior
    const bottomSection = document.querySelector('section.fixed.bottom-0');
    if (bottomSection && colors.background) {
        bottomSection.style.backgroundColor = colors.background;
    }
    
    // Actualizar todas las cards de productos
    if (typeof window.updateProductCards === 'function') {
        window.updateProductCards(colors);
    }
    
    // Actualizar botones de categoría (se actualizarán cuando se generen)
    setTimeout(() => {
        // Usar window.currentColors si está disponible (más reciente), sino usar los colores pasados
        const colorsToUse = window.currentColors || colors;
        if (typeof updateCategoryButtons === 'function') {
            updateCategoryButtons(colorsToUse);
        }
    }, 200);
}

// Función para actualizar las cards de productos (disponible globalmente)
window.updateProductCards = function(colors) {
    const cards = document.querySelectorAll('.product-card');
    const getColorFunc = typeof window.getColor === 'function' ? window.getColor : function(name) {
        const defaults = {
            'card_background': '#272728',
            'text_primary': '#E4C57F'
        };
        return defaults[name] || '#000000';
    };
    const cardBgColor = colors && colors.card_background ? colors.card_background : getColorFunc('card_background');
    const textPrimaryColor = colors && colors.text_primary ? colors.text_primary : getColorFunc('text_primary');
    
    cards.forEach(card => {
        // Actualizar fondo de la overlay de texto - buscar por posición bottom-0
        const overlays = card.querySelectorAll('.absolute');
        overlays.forEach(overlay => {
            if (overlay.classList.contains('bottom-0') || overlay.style.position === 'absolute') {
                // Verificar si es el overlay de texto (tiene z-10 o contiene texto)
                const hasText = overlay.querySelector('h2, span');
                if (hasText || overlay.classList.contains('z-10')) {
                    overlay.style.backgroundColor = cardBgColor;
                    // Forzar el estilo para sobrescribir cualquier clase de Tailwind
                    overlay.setAttribute('style', `background-color: ${cardBgColor} !important; ${overlay.getAttribute('style') || ''}`);
                }
            }
        });
        
        // Actualizar textos dentro de la card - forzar estilos inline
        const texts = card.querySelectorAll('h2.product-name, span');
        texts.forEach(text => {
            const currentStyle = text.getAttribute('style') || '';
            // Eliminar cualquier color anterior y agregar el nuevo
            const newStyle = currentStyle.replace(/color:\s*[^;]+;?/gi, '') + ` color: ${textPrimaryColor} !important;`;
            text.setAttribute('style', newStyle.trim());
            text.style.color = textPrimaryColor;
            text.style.setProperty('color', textPrimaryColor, 'important');
        });
        
        // Actualizar placeholder de imagen
        const placeholder = card.querySelector('.absolute.inset-0.w-full.h-full.flex');
        if (placeholder) {
            placeholder.style.backgroundColor = cardBgColor;
            placeholder.setAttribute('style', `background-color: ${cardBgColor} !important; ${placeholder.getAttribute('style') || ''}`);
            const svg = placeholder.querySelector('svg');
            if (svg) {
                svg.style.color = textPrimaryColor;
                svg.style.setProperty('color', textPrimaryColor, 'important');
            }
        }
    });
};

// Función para actualizar botones de categoría
function updateCategoryButtons(colors) {
    const categoryButtons = document.querySelectorAll('.category-btn');
    categoryButtons.forEach(btn => {
        const isActive = btn.classList.contains('active');
        if (isActive && colors.primary) {
            btn.style.backgroundColor = colors.primary;
            if (colors.card_background) {
                btn.style.color = colors.card_background;
            }
        } else {
            if (colors.card_background) {
                btn.style.backgroundColor = colors.card_background;
            }
            if (colors.text_primary) {
                btn.style.color = colors.text_primary;
            }
        }
    });
    
    // Actualizar botones del modal
    const modalButtons = document.querySelectorAll('.modal-category-btn');
    modalButtons.forEach(btn => {
        const isActive = btn.classList.contains('bg-\\[\\#B38D57\\]');
        if (isActive && colors.primary) {
            btn.style.backgroundColor = colors.primary;
            if (colors.card_background) {
                btn.style.color = colors.card_background;
            }
        } else {
            if (colors.background) {
                btn.style.backgroundColor = colors.background;
            }
            if (colors.text_primary) {
                btn.style.color = colors.text_primary;
            }
        }
    });
}

// Mantener el event listener para DOMContentLoaded
document.addEventListener('DOMContentLoaded', inicializarMenu);

// Función para capitalizar la primera letra
function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
}

// Actualiza el botón de WhatsApp con el número y mensaje por defecto
function updateWhatsAppButton() {
    const link = document.getElementById('btnWhatsApp');
    if (!link) return;

    const raw = (window.restaurantWhatsapp || '') + '';
    const phone = raw.replace(/[^0-9]/g, '');

    if (!phone) {
        link.classList.add('hidden');
        link.removeAttribute('href');
        link.removeAttribute('title');
        link.classList.remove('whatsapp-pulse');
        return;
    }

    // Obtener nombre del restaurante para el mensaje
    const restaurantName = (document.getElementById('restaurantTitle') && document.getElementById('restaurantTitle').textContent) ? document.getElementById('restaurantTitle').textContent.trim() : '';
    const defaultMsg = restaurantName ? `Hola, estoy viendo la carta de ${restaurantName} y quisiera información.` : 'Hola, quisiera información sobre su restaurante.';
    const href = `https://api.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(defaultMsg)}`;

    link.href = href;
    // Mostrar número en el tooltip/title para referencia rápida
    const displayPhone = (window.restaurantWhatsapp || '').trim();
    link.setAttribute('title', displayPhone ? `WhatsApp: ${displayPhone}` : 'WhatsApp');
    link.classList.remove('hidden');
    // Añadir una animación sutil para llamar la atención
    link.classList.add('whatsapp-pulse');
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
    // Agregar timestamp para evitar caché
    const timestamp = Date.now();
    fetch(`${basePath}/public/json/qr${restauranteId}.json?t=${timestamp}`, {
        cache: 'no-store',
        headers: {
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache'
        }
    })
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
            }
            
            // Establecer el nombre del restaurante como título principal
            titleElement.textContent = restaurantName;
            
            // Mostrar el nombre del QR solo si este QR tiene "vista nombre" activo (mostrar_nombre_en_carta en el JSON del QR)
            const mostrarNombreQR = qrEncontrado.mostrar_nombre_en_carta !== 0 && 
                qrEncontrado.mostrar_nombre_en_carta !== false && 
                qrEncontrado.mostrar_nombre_en_carta !== 'inactivo';
            if (qrNombre && mostrarNombreQR) {
                let qrName = capitalizeFirstLetter(qrNombre);
                if(restauranteId == "14"){
                    qrName = "Montelibano";
                }else if(restauranteId == "17"){
                    qrName = "Lorica";
                }else if(restauranteId == "18"){
                    qrName = "Caucasia";
                }
                qrTitleElement.textContent = qrName;
                if (qrTitleElement.style) qrTitleElement.style.display = '';
            } else {
                qrTitleElement.textContent = '';
                if (qrTitleElement.style) qrTitleElement.style.display = 'none';
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