// Variables globales
let currentPage = 0;
// currentCategory se declara en data.js

// Función helper para obtener colores personalizados (disponible globalmente)
// Prioriza SIEMPRE los colores cargados desde el JSON (_settings.colors)
window.getColor = function(colorName) {
    // 1) Si tenemos colores actuales cargados desde el JSON, usarlos primero
    if (window.currentColors && window.currentColors[colorName]) {
        return window.currentColors[colorName];
    }

    // 2) Si no, leer la variable CSS correspondiente
    const root = getComputedStyle(document.documentElement);
    const colorMap = {
        'primary': '--color-primary',
        'primary_dark': '--color-primary-dark',
        'primary_light': '--color-primary-light',
        'text_primary': '--color-text-primary',
        'text_secondary': '--color-text-secondary',
        'card_background': '--color-card-background',
        'background': '--color-background'
    };
    const cssVar = colorMap[colorName];
    if (cssVar) {
        const value = root.getPropertyValue(cssVar).trim();
        if (value) return value;
    }

    // 3) Fallback a valores por defecto
    return getDefaultColor(colorName);
};

function getDefaultColor(colorName) {
    const defaults = {
        'primary': '#B38D57',
        'primary_dark': '#80653E',
        'primary_light': '#E4C57F',
        'text_primary': '#E4C57F',
        'text_secondary': '#B38D57',
        'card_background': '#272728',
        'background': '#1a1a1a'
    };
    return defaults[colorName] || '#000000';
}

// Alias para compatibilidad
const getColor = window.getColor;

// Función para actualizar botones de categoría con colores personalizados
function updateCategoryButtons(colors) {
    const categoryButtons = document.querySelectorAll('.category-btn');
    const primaryColor = colors.primary || getColor('primary');
    const cardBgColor = colors.card_background || getColor('card_background');
    const textPrimaryColor = colors.text_primary || getColor('text_primary');
    
    categoryButtons.forEach(btn => {
        // Usar currentCategory para determinar si está activo, no la clase 'active'
        const btnCategory = btn.dataset.category;
        const isActive = btnCategory === currentCategory;
        if (isActive) {
            btn.style.backgroundColor = primaryColor;
            btn.style.color = cardBgColor;
        } else {
            btn.style.backgroundColor = cardBgColor;
            btn.style.color = textPrimaryColor;
        }
    });
    
    // Actualizar botones del modal
    const modalButtons = document.querySelectorAll('.modal-category-btn');
    const bgColor = colors.background || getColor('background');
    
    modalButtons.forEach(btn => {
        const isActive = btn.style.backgroundColor === primaryColor || 
                        getComputedStyle(btn).backgroundColor === primaryColor;
        if (isActive) {
            btn.style.backgroundColor = primaryColor;
            btn.style.color = cardBgColor;
        } else {
            btn.style.backgroundColor = bgColor;
            btn.style.color = textPrimaryColor;
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // No inicializamos aquí, dejamos que data.js maneje la inicialización
});

// Función para generar los botones de categorías
function generateCategoryButtons() {
    //console.log('Generando botones de categorías...');
    const categories = Object.keys(menuData).filter(category => 
        // Ignorar claves internas que empiezan por '_' y tomar solo categorías válidas
        !category.startsWith('_') && (menuData[category].status === 'activo' || !menuData[category].status)
    );
    const container = document.querySelector('.nav-container .flex');
    container.innerHTML = '';
    
    // Si currentCategory no está definido, usar la primera categoría
    if (!currentCategory && categories.length > 0) {
        currentCategory = categories[0];
    }
        
    // Mostrar/ocultar botón de todas las categorías según la cantidad
    const btnTodasCategorias = document.getElementById('btnTodasCategorias');
    if (btnTodasCategorias) {
        if (categories.length > 2) {
            btnTodasCategorias.classList.remove('hidden');
        } else {
            btnTodasCategorias.classList.add('hidden');
        }
    }
    
    categories.forEach((category) => {
        const button = document.createElement('button');
        // Usar currentCategory para determinar qué botón debe estar activo
        const isActive = category === currentCategory;
        const primaryColor = getColor('primary');
        const cardBgColor = getColor('card_background');
        const textPrimaryColor = getColor('text_primary');
        
        button.className = `category-btn shrink-0 px-4 py-2 rounded-full text-sm font-bold shadow-lg transition-colors`;
        button.style.backgroundColor = isActive ? primaryColor : cardBgColor;
        button.style.color = isActive ? cardBgColor : textPrimaryColor;
        button.textContent = category;
        button.dataset.category = category;
        container.appendChild(button);
        
        button.addEventListener('click', () => {
            selectCategory(category);
        });
    });
    
    // Configurar el modal de todas las categorías
    setupModalCategorias(categories);
    
    // Actualizar colores de los botones después de generarlos
    // Asegurar que el botón activo se mantenga seleccionado
    setTimeout(() => {
        // Usar window.currentColors si está disponible (más reciente), sino leer del JSON
        const colors = window.currentColors || 
                      ((typeof menuData !== 'undefined' && menuData && menuData._settings) ? 
                       (menuData._settings.colors || {}) : {});
        if (Object.keys(colors).length > 0) {
            updateCategoryButtons(colors);
        }
        // Asegurar que currentCategory esté seleccionado visualmente después de actualizar colores
        if (currentCategory) {
            document.querySelectorAll('.category-btn').forEach(btn => {
                const btnCategory = btn.dataset.category;
                if (btnCategory === currentCategory) {
                    const primaryColor = colors.primary || getColor('primary');
                    const cardBgColor = colors.card_background || getColor('card_background');
                    btn.style.backgroundColor = primaryColor;
                    btn.style.color = cardBgColor;
                }
            });
        }
    }, 100);
}

// Función para actualizar los botones del modal cuando se abre
function updateModalCategoryButtons() {
    const modalButtons = document.querySelectorAll('.modal-category-btn');
    const primaryColor = getColor('primary');
    const cardBgColor = getColor('card_background');
    const textPrimaryColor = getColor('text_primary');
    const bgColor = getColor('background');
    
    modalButtons.forEach(btn => {
        const btnCategory = btn.dataset.category;
        const isActive = btnCategory === currentCategory;
        btn.style.backgroundColor = isActive ? primaryColor : bgColor;
        btn.style.color = isActive ? cardBgColor : textPrimaryColor;
    });
}

// Función para seleccionar una categoría
// options.force = true permite forzar la recarga aunque ya esté seleccionada (útil en la carga inicial)
function selectCategory(category, options = {}) {
    const { force = false } = options;

    // Si la categoría ya está seleccionada y no se fuerza, no hacer nada
    if (!force && currentCategory === category) {
        return;
    }
    
    // Actualizar currentCategory
    currentCategory = category;
    
    // Actualizar todos los botones de categoría
    const primaryColor = getColor('primary');
    const cardBgColor = getColor('card_background');
    const textPrimaryColor = getColor('text_primary');
    const bgColor = getColor('background');
    
    document.querySelectorAll('.category-btn').forEach(btn => {
        const btnCategory = btn.dataset.category;
        const isActive = btnCategory === currentCategory;
        btn.style.backgroundColor = isActive ? primaryColor : cardBgColor;
        btn.style.color = isActive ? cardBgColor : textPrimaryColor;
    });
    
    // Actualizar botones del modal también
    updateModalCategoryButtons();
    
    // Generar las tarjetas para la nueva categoría
    generateCardsForCategory(category);
    
    // Cerrar el modal si está abierto
    const modal = document.getElementById('modalTodasCategorias');
    if (modal) {
        modal.classList.add('hidden');
    }
}

// Función para configurar el modal de categorías
function setupModalCategorias(categories) {
    const listaModal = document.getElementById('listaCategoriasModal');
    if (!listaModal) return;
    
    listaModal.innerHTML = '';
    
    // Asegurar que currentCategory esté definido (usar primera categoría si no lo está)
    if (!currentCategory && categories.length > 0) {
        currentCategory = categories[0];
    }
    
    categories.forEach((category) => {
        const button = document.createElement('button');
        const isActive = category === currentCategory;
        const primaryColor = getColor('primary');
        const cardBgColor = getColor('card_background');
        const textPrimaryColor = getColor('text_primary');
        const bgColor = getColor('background');
        
        button.className = `modal-category-btn w-full px-4 py-3 rounded-lg text-sm font-bold transition-colors text-left`;
        button.style.backgroundColor = isActive ? primaryColor : bgColor;
        button.style.color = isActive ? cardBgColor : textPrimaryColor;
        button.textContent = category;
        button.dataset.category = category;
        listaModal.appendChild(button);
        
        button.addEventListener('click', () => {
            selectCategory(category);
        });
    });
    
    // Configurar botón para abrir modal
    const btnTodasCategorias = document.getElementById('btnTodasCategorias');
    const modal = document.getElementById('modalTodasCategorias');
    const btnCerrarModal = document.getElementById('btnCerrarModalCategorias');
    
    if (btnTodasCategorias && modal) {
        // Remover listener anterior si existe para evitar duplicados
        const newBtn = btnTodasCategorias.cloneNode(true);
        btnTodasCategorias.parentNode.replaceChild(newBtn, btnTodasCategorias);
        const btnTodasCategoriasNew = document.getElementById('btnTodasCategorias');
        
        btnTodasCategoriasNew.addEventListener('click', () => {
            // Actualizar estilos de los botones del modal antes de mostrarlo
            updateModalCategoryButtons();
            modal.classList.remove('hidden');
        });
    }
    
    if (btnCerrarModal && modal) {
        btnCerrarModal.addEventListener('click', () => {
            modal.classList.add('hidden');
        });
    }
    
    // Cerrar modal al hacer clic fuera
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.add('hidden');
            }
        });
    }
}
// Función para generar las tarjetas de una categoría
function generateCardsForCategory(category) {
    // Obtener los productos de la categoría
    const categoryData = menuData[category] || { products: [] };
    // Filtrar solo los productos activos si la categoría está activa
    const activeItems = categoryData.status === 'activo' || !categoryData.status ? 
        categoryData.products.filter(item => item.status === 'activo' || !item.status) : [];

    // Resetear la página actual a 0 cuando cambia de categoría
    currentPage = 0;
    
    const sliderWrapper = document.getElementById('sliderWrapper');
    sliderWrapper.innerHTML = '';
    
    // Asegurar que el sliderWrapper tenga el overflow correcto
    sliderWrapper.style.overflow = 'hidden';
    sliderWrapper.style.width = '100%';
    
    // Calcular el número total de páginas basado solo en elementos activos
    // Siempre asegurar al menos 1 página
    const totalPages = Math.max(1, Math.ceil(activeItems.length / 6));
    
    // Calcular altura de las cards dinámicamente basado en la altura disponible (una sola vez)
    // Altura disponible = 100vh - header - nav - bottom section - padding
    const calculateCardHeight = () => {
        const vh = window.innerHeight;
        const header = document.querySelector('.flex.flex-col.justify-center.items-center');
        const nav = document.querySelector('nav');
        const bottom = document.querySelector('section.fixed.bottom-0');
        
        // Obtener alturas reales de los elementos
        const headerHeight = header ? header.offsetHeight : 0;
        const navHeight = nav ? nav.offsetHeight : 0;
        // Calcular bottomHeight siempre usando la altura máxima (con paginador visible)
        // para mantener consistencia con o sin paginador
        const bottom22 = document.querySelector('section.fixed.bottom-0');
        const dotsContainer = document.getElementById('dotsContainer');
        let bottomHeight = 0;
        
        if (bottom22) {
            // Si el paginador está oculto, calcular la altura que tendría si estuviera visible
            const wasHidden = dotsContainer && dotsContainer.classList.contains('hidden');
            if (wasHidden && dotsContainer) {
                // Temporalmente hacer visible para medir
                dotsContainer.classList.remove('hidden');
                bottomHeight = bottom.offsetHeight;
                // Volver a ocultar
                dotsContainer.classList.add('hidden');
            } else {
                // Ya está visible, usar altura actual
                bottomHeight = bottom.offsetHeight;
            }
        }
        const mainContainer = document.getElementById('mainContainer');
        const mainPaddingTop = mainContainer ? parseFloat(getComputedStyle(mainContainer).paddingTop) : 16;
        const mainPaddingBottom = mainContainer ? parseFloat(getComputedStyle(mainContainer).paddingBottom) : 80;
        
        // Usar la altura REAL del contenedor donde se dibuja el grid (evita desfases por flex/medidas)
        let availableHeight = 0;
        if (mainContainer && mainContainer.clientHeight > 0) {
            availableHeight = mainContainer.clientHeight - mainPaddingTop - mainPaddingBottom;
        }
        if (availableHeight <= 0) {
            const safetyMargin = 6;
            const usedHeight = headerHeight + navHeight + bottomHeight + mainPaddingTop + mainPaddingBottom + safetyMargin;
            availableHeight = Math.max(0, vh - usedHeight);
        }
        availableHeight = Math.max(0, availableHeight);
        
        const isShortViewport = vh < 600;
        const gapBetweenRows = isShortViewport ? 4 : 8;
        // Altura máxima que puede tener una card para que las 3 filas + 2 gaps quepan sin cortarse
        const maxHeightThatFits = availableHeight > 0 ? (availableHeight - 2 * gapBetweenRows) / 3 : 0;
        let cardHeight = (availableHeight / 3) - (gapBetweenRows * 2 / 3);
        const minCardHeight = isShortViewport ? 72 : 120;
        const maxCardHeight = isShortViewport ? 140 : 400;
        cardHeight = Math.max(minCardHeight, Math.min(maxCardHeight, cardHeight + (isShortViewport ? 0 : 20)));
        // Nunca superar lo que realmente cabe en el contenedor para que las dos últimas cards no se corten
        cardHeight = Math.min(cardHeight, Math.max(0, maxHeightThatFits));
        
        return cardHeight;
    };
    
    const cardHeight = calculateCardHeight();
    
                // Recalcular altura cuando cambie el tamaño de la ventana
                if (!window.cardHeightResizeHandler) {
                    let resizeTimeout;
                    window.cardHeightResizeHandler = () => {
                        clearTimeout(resizeTimeout);
                        resizeTimeout = setTimeout(() => {
                            const newHeight = calculateCardHeight();
                            document.querySelectorAll('.product-card').forEach(card => {
                                card.style.height = `${newHeight}px`;
                            });
                        }, 100);
                    };
                    window.addEventListener('resize', window.cardHeightResizeHandler);
                }
    
    // Obtener el ancho del contenedor una sola vez
    const mainContainer = document.getElementById('mainContainer');
    const containerWidth = mainContainer ? mainContainer.offsetWidth : window.innerWidth;
    
    // Crear páginas solo si hay elementos activos
    if (activeItems.length > 0) {
        for (let i = 0; i < totalPages; i++) {
            const pageItems = activeItems.slice(i * 6, (i + 1) * 6);
            // Siempre crear la página, incluso si tiene menos de 6 elementos
            const page = document.createElement('div');
            page.id = `page${i + 1}`;
            page.className = `flex-shrink-0 transition-all duration-300 ease-in-out opacity-100 scale-100 h-full`;
            // Agregar espacio entre páginas (gap de 20px)
            const gapBetweenPages = 20;
            // Asegurar que cada página ocupe exactamente el 100% del ancho del contenedor más el gap
            const pageWidth = containerWidth - gapBetweenPages;
            page.style.width = `${pageWidth}px`;
            page.style.minWidth = `${pageWidth}px`;
            page.style.maxWidth = `${pageWidth}px`;
            page.style.marginRight = `${gapBetweenPages}px`;
            page.style.flex = '0 0 auto';
            
            const grid = document.createElement('div');
            grid.className = 'grid grid-cols-2 gap-x-4 gap-y-2 h-full items-stretch';
            
            // Función para determinar en qué posición del grid colocar cada producto
            const getGridPosition = (index, total) => {
                // Posiciones del grid (0-5): 
                // 0: fila 1, col 1 | 1: fila 1, col 2
                // 2: fila 2, col 1 | 3: fila 2, col 2
                // 4: fila 3, col 1 | 5: fila 3, col 2
                
                if (total === 1) return 0; // Centrar en posición 1 (fila 1, col 2) para mejor visualización
                if (total === 2) return index; // Primera fila (0, 1)
                if (total === 3) {
                    // Primera fila completa + primera columna segunda fila
                    return index; // 0, 1, 2
                }
                if (total === 4) {
                    // Dos filas completas
                    return index; // 0, 1, 2, 3
                }
                if (total === 5) {
                    // Dos filas completas + primera columna tercera fila
                    return index; // 0, 1, 2, 3, 4
                }
                // Si hay 6, usar todas las posiciones
                return index; // 0, 1, 2, 3, 4, 5
            };
            
            // Crear 6 slots siempre, pero solo llenar los necesarios
            const itemsCount = pageItems.length;
            const slots = Array(6).fill(null);
            
            // Distribuir los productos en los slots según su cantidad
            pageItems.forEach((item, pageItemIndex) => {
                const globalIndex = i * 6 + pageItemIndex;
                const gridPosition = getGridPosition(pageItemIndex, itemsCount);
                slots[gridPosition] = { item, globalIndex };
            });
            
            // Generar las cards para cada slot
            slots.forEach((slot, slotIndex) => {
                const cardBgColor = getColor('card_background');
                const textPrimaryColor = getColor('text_primary');
                
                if (slot) {
                    // Slot con producto
                    const cardHtml = `
                        <div class="product-card relative w-full rounded-xl overflow-hidden cursor-pointer transition-transform  " 
                             style="height: ${cardHeight}px; border: 1px solid ${textPrimaryColor}45;"
                             data-category="${category}" 
                             data-index="${slot.globalIndex}">
                            <div class="absolute bottom-0 left-0 right-0 z-10 min-h-[41px] flex flex-col justify-center items-center p-1" style="background-color: ${cardBgColor} !important;">
                                <h2 class="product-name text-sm font-bold text-center leading-tight line-clamp-2" style="color: ${textPrimaryColor} !important;">${slot.item.name}</h2>
                                <span class="font-normal text-sm mt-0.5" style="color: ${textPrimaryColor} !important;">${slot.item.price}</span>
                            </div>
                            ${slot.item.image ? 
                                `<img src="${slot.item.image}" alt="${slot.item.name}" class="absolute inset-0 w-full h-full object-cover">` :
                                `<div class="absolute inset-0 w-full h-full flex items-center justify-center" style="background-color: ${cardBgColor};">
                                    <svg class="w-16 h-16" style="color: ${textPrimaryColor};" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>`
                            }
                        </div>
                    `;
                    grid.innerHTML += cardHtml;
                } else {
                    // Slot vacío - crear un div invisible para mantener el espacio
                    const emptySlot = document.createElement('div');
                    emptySlot.className = 'w-full';
                    emptySlot.style.height = `${cardHeight}px`;
                    emptySlot.style.visibility = 'hidden';
                    grid.appendChild(emptySlot);
                }
            });
            
            page.appendChild(grid);
            sliderWrapper.appendChild(page);
        }
        
        // Ajustar el ancho total del sliderWrapper después de crear todas las páginas
        // Usar requestAnimationFrame para asegurar que el DOM esté actualizado
        requestAnimationFrame(() => {
            if (totalPages > 0) {
                const actualContainerWidth = mainContainer ? mainContainer.offsetWidth : containerWidth;
                const gapBetweenPages = 20;
                const pageWidth = actualContainerWidth - gapBetweenPages;
                // Ancho total = (ancho de página * número de páginas) + (gap * número de páginas - 1)
                // Pero como cada página tiene margin-right, el último no necesita gap
                sliderWrapper.style.width = `${(pageWidth * totalPages) + (gapBetweenPages * (totalPages - 1))}px`;
            }
        });
    }
    
    // Actualizar colores de las cards después de generarlas
    setTimeout(() => {
        // Usar window.currentColors si está disponible (más reciente), sino leer del JSON
        const colors = window.currentColors || 
                      ((typeof menuData !== 'undefined' && menuData && menuData._settings) ? 
                       (menuData._settings.colors || {}) : {});
        if (typeof window.updateProductCards === 'function') {
            window.updateProductCards(colors);
        }
        
        // Recalcular altura de las cards después de que el DOM esté completamente renderizado
        // Esto asegura que las cards 5 y 6 se ajusten correctamente

        
                const recalculatedHeight = calculateCardHeight();
                document.querySelectorAll('.product-card').forEach(card => {
                    card.style.height = `${recalculatedHeight}px`;
                });
         
       
    }, 100);

    // Ajustar tamaño de fuente para nombres largos
    setTimeout(() => {
        document.querySelectorAll('.product-name').forEach(nameElement => {
            const text = nameElement.textContent.trim();
            const length = text.length;
            const card = nameElement.closest('.product-card');
            const container = nameElement.closest('.absolute');
            
            if (!card || !container) return;
            
            if (length > 20) {
                // Texto largo: reducir moderadamente
                nameElement.style.fontSize = '0.75rem';
                nameElement.style.lineHeight = '1.2';
            } 
            
            // Verificar desbordamiento después de aplicar estilos
            requestAnimationFrame(() => {
                const nameRect = nameElement.getBoundingClientRect();
                const priceElement = nameElement.nextElementSibling;
                
                if (priceElement) {
                    const priceRect = priceElement.getBoundingClientRect();
                    const containerRect = container.getBoundingClientRect();
                    const totalContentHeight = nameRect.height + priceRect.height + 16; // 16px para padding total
                    
                    // Si el contenido excede el espacio, ajustar más
                    if (totalContentHeight > containerRect.height) {
                        const currentSize = parseFloat(window.getComputedStyle(nameElement).fontSize);
                        nameElement.style.fontSize = (currentSize * 1) + 'px';
                        nameElement.style.lineHeight = '1.1';
                    }
                }
            });
        });
    }, 150);

    // Inicializar funcionalidad de modal para las nuevas tarjetas
    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('click', function() {
            const categoryName = this.dataset.category;
            const itemIndex = parseInt(this.dataset.index);
            
            // Obtener el producto desde menuData
            const categoryData = menuData[categoryName];
            if (categoryData && categoryData.products) {
                const activeItems = categoryData.products.filter(item => item.status === 'activo' || !item.status);
                const producto = activeItems[itemIndex];
                
                if (producto) {
                    mostrarModalProducto(producto);
                }
            }
        });
    });

    // Actualizar los dots y botones de navegación
    updateDotsAndButtons(totalPages);
    updatePageIndicators(0);
    
    // Resetear la posición del slider
    sliderWrapper.style.transform = 'translateX(0)';
    
    // Actualizar el slider y recalcular alturas después de que el DOM esté listo
    requestAnimationFrame(() => {
        // Recalcular altura de las cards con las dimensiones finales del DOM
        const recalculatedHeight = calculateCardHeight();
        document.querySelectorAll('.product-card').forEach(card => {
            card.style.height = `${recalculatedHeight}px`;
        });
        
        // Inicializar slider después de ajustar alturas
        if (typeof initializeSlider === 'function') {
            initializeSlider();
        }
    });
}

// Función para inicializar el slider
function initializeSlider() {
    const categoryData = menuData[currentCategory] || { products: [] };
    const activeItems = categoryData.products.filter(item => item.status === 'activo' || item.status === undefined);
    // Siempre asegurar al menos 1 página
    const totalPages = Math.max(1, Math.ceil(activeItems.length / 6));
    const mainContainer = document.getElementById('mainContainer');
    const sliderWrapper = document.getElementById('sliderWrapper');
    const dotsContainer = document.getElementById('dotsContainer');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');

    const canSlide = totalPages > 1;

    // Clear existing dots
    dotsContainer.innerHTML = '';

    // Create dots only if we can slide
    if (canSlide) {
        const primaryColor = getColor('primary');
        const cardBgColor = getColor('card_background');
        for (let i = 0; i < totalPages; i++) {
            const dot = document.createElement('div');
            dot.className = `w-2 h-2 rounded-full transition-colors duration-300`;
            dot.style.backgroundColor = i === currentPage ? primaryColor : cardBgColor;
            dot.addEventListener('click', () => goToPage(i));
            dotsContainer.appendChild(dot);
        }
        dotsContainer.classList.remove('hidden');
    } else {
        dotsContainer.classList.add('hidden');
    }

    // Show/hide navigation buttons
    if (canSlide) {
        prevBtn.classList.remove('hidden');
        nextBtn.classList.remove('hidden');
        
        // Update button states
        prevBtn.style.opacity = currentPage === 0 ? '0.5' : '1';
        nextBtn.style.opacity = currentPage === totalPages - 1 ? '0.5' : '1';
    } else {
        prevBtn.classList.add('hidden');
        nextBtn.classList.add('hidden');
        sliderWrapper.style.transform = 'translateX(0)';
        currentPage = 0;
    }

    // Touch events
    let startX, startY;
    let isDragging = false;

    // Remove existing event listeners if any
    const existingTouchStart = mainContainer._touchStartHandler;
    const existingTouchMove = mainContainer._touchMoveHandler;
    const existingTouchEnd = mainContainer._touchEndHandler;

    if (existingTouchStart) {
        mainContainer.removeEventListener('touchstart', existingTouchStart);
    }
    if (existingTouchMove) {
        mainContainer.removeEventListener('touchmove', existingTouchMove);
    }
    if (existingTouchEnd) {
        mainContainer.removeEventListener('touchend', existingTouchEnd);
    }

    // Define new event handlers
    mainContainer._touchStartHandler = (e) => {
        if (!canSlide) return;
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
        isDragging = true;
    };

    mainContainer._touchMoveHandler = (e) => {
        if (!isDragging || !canSlide) return;

        const currentX = e.touches[0].clientX;
        const currentY = e.touches[0].clientY;
        const diffX = startX - currentX;
        const diffY = startY - currentY;

        // Only handle horizontal sliding
        if (Math.abs(diffX) > Math.abs(diffY)) {
            e.preventDefault();
            
            if (Math.abs(diffX) > 50) {
                if (diffX > 0 && currentPage < totalPages - 1) {
                    goToPage(currentPage + 1);
                    isDragging = false;
                } else if (diffX < 0 && currentPage > 0) {
                    goToPage(currentPage - 1);
                    isDragging = false;
                }
            }
        }
    };

    mainContainer._touchEndHandler = () => {
        isDragging = false;
    };

    // Add new event listeners
    mainContainer.addEventListener('touchstart', mainContainer._touchStartHandler, { passive: true });
    mainContainer.addEventListener('touchmove', mainContainer._touchMoveHandler, { passive: false });
    mainContainer.addEventListener('touchend', mainContainer._touchEndHandler);

    // Set initial transform
    sliderWrapper.style.transform = `translateX(${-currentPage * 100}%)`;
}

// Función para reinicializar el slider
function reinicializarSlider() {
    isSliderInitialized = false;
    initializeSlider();
}

// Función para actualizar los dots y botones de navegación
function updateDotsAndButtons(totalPages) {
    const dotsContainer = document.getElementById('dotsContainer');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');

    // Clear existing dots
    dotsContainer.innerHTML = '';

    // Create dots only if we can slide
    if (totalPages > 1) {
        const primaryColor = getColor('primary');
        const cardBgColor = getColor('card_background');
        for (let i = 0; i < totalPages; i++) {
            const dot = document.createElement('div');
            dot.className = `w-2 h-2 rounded-full transition-colors duration-300`;
            dot.style.backgroundColor = i === currentPage ? primaryColor : cardBgColor;
            dot.addEventListener('click', () => goToPage(i));
            dotsContainer.appendChild(dot);
        }
        dotsContainer.classList.remove('hidden');
    } else {
        dotsContainer.classList.add('hidden');
    }

    // Show/hide navigation buttons
    if (totalPages > 1) {
        prevBtn.classList.remove('hidden');
        nextBtn.classList.remove('hidden');
    } else {
        prevBtn.classList.add('hidden');
        nextBtn.classList.add('hidden');
    }
}

// Función para actualizar los indicadores de página
function updatePageIndicators(pageIndex) {
    const dots = document.getElementById('dotsContainer').children;
    const primaryColor = getColor('primary');
    const cardBgColor = getColor('card_background');
    for (let i = 0; i < dots.length; i++) {
        dots[i].className = `w-2 h-2 rounded-full transition-colors duration-300`;
        dots[i].style.backgroundColor = i === pageIndex ? primaryColor : cardBgColor;
    }

    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    prevBtn.style.opacity = pageIndex === 0 ? '0.5' : '1';
    nextBtn.style.opacity = pageIndex === dots.length - 1 ? '0.5' : '1';
}

// Función para ir a una página específica
function goToPage(pageIndex) {
    const sliderWrapper = document.getElementById('sliderWrapper');
    if (!sliderWrapper) return;
    
    // Asegurar que el índice esté dentro del rango válido
    const categoryData = menuData[currentCategory] || { products: [] };
    const activeItems = categoryData.products.filter(item => item.status === 'activo' || item.status === undefined);
    const totalPages = Math.max(1, Math.ceil(activeItems.length / 6));
    
    if (pageIndex < 0 || pageIndex >= totalPages) return;
    
    currentPage = pageIndex;
    // Calcular el desplazamiento basado en el ancho real del contenedor
    const mainContainer = document.getElementById('mainContainer');
    if (mainContainer) {
        const containerWidth = mainContainer.offsetWidth;
        const gapBetweenPages = 20;
        const pageWidth = containerWidth - gapBetweenPages;
        // Desplazamiento = (ancho de página + gap) * índice de página
        const offset = pageIndex * (pageWidth + gapBetweenPages);
        sliderWrapper.style.transform = `translateX(-${offset}px)`;
    } else {
        sliderWrapper.style.transform = `translateX(-${pageIndex * 100}%)`;
    }
    updatePageIndicators(pageIndex);
    
    // Actualizar botones de navegación
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    if (prevBtn) prevBtn.style.opacity = currentPage === 0 ? '0.5' : '1';
    if (nextBtn) nextBtn.style.opacity = currentPage === totalPages - 1 ? '0.5' : '1';
}

// Función para mostrar el modal de producto
function mostrarModalProducto(producto) {
    const modal = document.getElementById('modalProducto');
    const imagen = document.getElementById('modalProductoImagenSrc');
    const imagenPlaceholder = document.getElementById('modalProductoImagenPlaceholder');
    const nombre = document.getElementById('modalProductoNombre');
    const precio = document.getElementById('modalProductoPrecio');
    const descripcion = document.getElementById('modalProductoDescripcion');
    const btnOcultar = document.getElementById('btnOcultarProducto');
    
    // Establecer los datos del producto
    nombre.textContent = producto.name || 'Producto';
    precio.textContent = producto.price || '';
    descripcion.textContent = producto.description || 'Sin descripción disponible.';
    
    // Aplicar colores dinámicos al modal
    const primaryColor = getColor('primary');
    const textPrimaryColor = getColor('text_primary');
    const cardBgColor = getColor('card_background');
    
    if (nombre) {
        nombre.style.color = primaryColor;
        nombre.style.setProperty('color', primaryColor, 'important');
    }
    if (precio) {
        precio.style.color = textPrimaryColor;
        precio.style.setProperty('color', textPrimaryColor, 'important');
    }
    if (descripcion) {
        descripcion.style.color = textPrimaryColor;
        descripcion.style.setProperty('color', textPrimaryColor, 'important');
    }
    if (btnOcultar) {
        btnOcultar.style.backgroundColor = primaryColor;
        btnOcultar.style.color = cardBgColor;
        btnOcultar.style.setProperty('background-color', primaryColor, 'important');
        btnOcultar.style.setProperty('color', cardBgColor, 'important');
    }
    
    // Manejar la imagen
    if (producto.image) {
        imagen.src = producto.image;
        imagen.alt = producto.name;
        imagen.classList.remove('hidden');
        imagenPlaceholder.classList.add('hidden');
    } else {
        imagen.classList.add('hidden');
        imagenPlaceholder.classList.remove('hidden');
    }
    
    // Mostrar el modal
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden'; // Prevenir scroll del body
}

// Función para cerrar el modal de producto
function cerrarModalProducto() {
    const modal = document.getElementById('modalProducto');
    modal.classList.add('hidden');
    document.body.style.overflow = ''; // Restaurar scroll del body
}

// Agregar event listeners para los botones de navegación y el modal
document.addEventListener('DOMContentLoaded', function() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const btnCerrarProducto = document.getElementById('btnCerrarProducto');
    const btnOcultarProducto = document.getElementById('btnOcultarProducto');
    const modalProducto = document.getElementById('modalProducto');
    
    prevBtn.addEventListener('click', function() {
        if (currentPage > 0) {
            goToPage(currentPage - 1);
        }
    });
    
    nextBtn.addEventListener('click', function() {
        const totalPages = document.getElementById('dotsContainer').children.length;
        if (currentPage < totalPages - 1) {
            goToPage(currentPage + 1);
        }
    });
    
    // Cerrar modal al hacer clic en los botones
    if (btnCerrarProducto) {
        btnCerrarProducto.addEventListener('click', cerrarModalProducto);
    }
    if (btnOcultarProducto) {
        btnOcultarProducto.addEventListener('click', cerrarModalProducto);
    }
    
    // Cerrar modal al hacer clic fuera del contenido
    if (modalProducto) {
        modalProducto.addEventListener('click', function(e) {
            if (e.target === modalProducto) {
                cerrarModalProducto();
            }
        });
    }
    
    // Cerrar modal con la tecla Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modalProducto.classList.contains('hidden')) {
            cerrarModalProducto();
        }
    });
});