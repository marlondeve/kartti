// Variables globales
let currentPage = 0;
// currentCategory se declara en data.js

document.addEventListener('DOMContentLoaded', function() {
    // No inicializamos aquí, dejamos que data.js maneje la inicialización
});

// Función para generar los botones de categorías
function generateCategoryButtons() {
    //console.log('Generando botones de categorías...');
    const categories = Object.keys(menuData).filter(category => 
        menuData[category].status === 'activo' || !menuData[category].status
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
        if (categories.length > 3) {
            btnTodasCategorias.classList.remove('hidden');
        } else {
            btnTodasCategorias.classList.add('hidden');
        }
    }
    
    categories.forEach((category) => {
        const button = document.createElement('button');
        // Usar currentCategory para determinar qué botón debe estar activo
        const isActive = category === currentCategory;
        button.className = `category-btn shrink-0 px-4 py-2 rounded-full text-sm font-bold shadow-lg transition-colors ${isActive ? 'active bg-[#B38D57] text-[#272728]' : 'text-[#E4C57F] bg-[#272728] hover:bg-[#B38D57] hover:text-[#272728]'}`;
        button.textContent = category;
        button.dataset.category = category;
        container.appendChild(button);
        
        button.addEventListener('click', () => {
            selectCategory(category);
        });
    });
    
    // Configurar el modal de todas las categorías
    setupModalCategorias(categories);
}

// Función para seleccionar una categoría
function selectCategory(category) {
    // Actualizar currentCategory
    currentCategory = category;
    
    // Actualizar todos los botones de categoría
    document.querySelectorAll('.category-btn').forEach(btn => {
        const btnCategory = btn.dataset.category;
        btn.className = `category-btn shrink-0 px-4 py-2 rounded-full text-sm font-bold shadow-lg transition-colors ${btnCategory === currentCategory ? 'active bg-[#B38D57] text-[#272728]' : 'text-[#E4C57F] bg-[#272728] hover:bg-[#B38D57] hover:text-[#272728]'}`;
    });
    
    // Actualizar botones del modal también
    document.querySelectorAll('.modal-category-btn').forEach(btn => {
        const btnCategory = btn.dataset.category;
        btn.className = `modal-category-btn w-full px-4 py-3 rounded-lg text-sm font-bold transition-colors text-left ${btnCategory === currentCategory ? 'bg-[#B38D57] text-[#272728]' : 'bg-[#1a1a1a] text-[#E4C57F] hover:bg-[#B38D57] hover:text-[#272728]'}`;
    });
    
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
    
    categories.forEach((category) => {
        const button = document.createElement('button');
        const isActive = category === currentCategory;
        button.className = `modal-category-btn w-full px-4 py-3 rounded-lg text-sm font-bold transition-colors text-left ${isActive ? 'bg-[#B38D57] text-[#272728]' : 'bg-[#1a1a1a] text-[#E4C57F] hover:bg-[#B38D57] hover:text-[#272728]'}`;
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
        btnTodasCategorias.addEventListener('click', () => {
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
    
    // Calcular el número total de páginas basado solo en elementos activos
    const totalPages = Math.ceil(activeItems.length / 6);
    
    // Crear páginas solo si hay elementos activos
    if (activeItems.length > 0) {
        for (let i = 0; i < totalPages; i++) {
            const pageItems = activeItems.slice(i * 6, (i + 1) * 6);
            // Solo crear la página si tiene elementos
            if (pageItems.length > 0) {
                const page = document.createElement('div');
                page.id = `page${i + 1}`;
                page.className = `min-w-[90%] flex-shrink-0 transition-all duration-300 ease-in-out opacity-100 scale-100 mx-[5%]`;
                
                const grid = document.createElement('div');
                grid.className = 'grid grid-cols-2 gap-4';
                
                // Generate cards for this page
                pageItems.forEach((item, pageItemIndex) => {
                    // Calcular el índice global del producto en la lista de productos activos
                    const globalIndex = i * 6 + pageItemIndex;
                    
                    const cardHtml = `
                        <div class="product-card relative h-[185px] w-full rounded-xl overflow-hidden cursor-pointer transition-transform duration-200 hover:scale-105" 
                             data-category="${category}" 
                             data-index="${globalIndex}">
                            <div class="absolute inset-0 z-10 bg-[#272728] h-[41px] flex flex-col justify-center items-center p-2">
                                <h2 class="text-sm font-bold text-[#E4C57F]">${item.name}</h2>
                                <span class="text-[#E4C57F] font-normal text-sm">${item.price}</span>
                            </div>
                            ${item.image ? 
                                `<img src="${item.image}" alt="${item.name}" class="absolute inset-0 w-full h-full object-cover">` :
                                `<div class="absolute inset-0 w-full h-full bg-[#272728] flex items-center justify-center">
                                    <svg class="w-16 h-16 text-[#E4C57F]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>`
                            }
                        </div>
                    `;
                    grid.innerHTML += cardHtml;
                });
                
                page.appendChild(grid);
                sliderWrapper.appendChild(page);
            }
        }
    }

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
    
    // Actualizar el slider
    if (typeof initializeSlider === 'function') {
        initializeSlider();
    }
}

// Función para inicializar el slider
function initializeSlider() {
    const categoryData = menuData[currentCategory] || { products: [] };
    const activeItems = categoryData.products.filter(item => item.status === 'activo' || item.status === undefined);
    const totalPages = Math.ceil(activeItems.length / 6);
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
        for (let i = 0; i < totalPages; i++) {
            const dot = document.createElement('div');
            dot.className = `w-2 h-2 rounded-full ${i === currentPage ? 'bg-[#B38D57]' : 'bg-[#272728]'} transition-colors duration-300`;
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
        for (let i = 0; i < totalPages; i++) {
            const dot = document.createElement('div');
            dot.className = `w-2 h-2 rounded-full ${i === currentPage ? 'bg-[#B38D57]' : 'bg-[#272728]'} transition-colors duration-300`;
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
    for (let i = 0; i < dots.length; i++) {
        dots[i].className = `w-2 h-2 rounded-full ${i === pageIndex ? 'bg-[#B38D57]' : 'bg-[#272728]'} transition-colors duration-300`;
    }

    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    prevBtn.style.opacity = pageIndex === 0 ? '0.5' : '1';
    nextBtn.style.opacity = pageIndex === dots.length - 1 ? '0.5' : '1';
}

// Función para ir a una página específica
function goToPage(pageIndex) {
    const sliderWrapper = document.getElementById('sliderWrapper');
    currentPage = pageIndex;
    sliderWrapper.style.transform = `translateX(-${pageIndex * 100}%)`;
    updatePageIndicators(pageIndex);
}

// Función para mostrar el modal de producto
function mostrarModalProducto(producto) {
    const modal = document.getElementById('modalProducto');
    const imagen = document.getElementById('modalProductoImagenSrc');
    const imagenPlaceholder = document.getElementById('modalProductoImagenPlaceholder');
    const nombre = document.getElementById('modalProductoNombre');
    const precio = document.getElementById('modalProductoPrecio');
    const descripcion = document.getElementById('modalProductoDescripcion');
    
    // Establecer los datos del producto
    nombre.textContent = producto.name || 'Producto';
    precio.textContent = producto.price || '';
    descripcion.textContent = producto.description || 'Sin descripción disponible.';
    
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
    
    // Cerrar modal al hacer clic en el botón cerrar
    if (btnCerrarProducto) {
        btnCerrarProducto.addEventListener('click', cerrarModalProducto);
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