// Variables globales
let editProductModal;
let deleteProductModal;
let dragSrcEl = null;
let dragSrcProduct = null;
let initialCategoryPositions = [];
let initialProductPositions = [];

// Funciones para manejar productos
window.editProduct = function(productId) {
    const productCard = document.querySelector(`#productsList > div[data-id="${productId}"]`);
    if (!productCard) return;

    const nombre = productCard.dataset.nombre;
    const descripcion = productCard.querySelector('p.text-gray-500')?.textContent || '';
    const precio = productCard.querySelector('p.text-lg').textContent.replace(/[^0-9]/g, '');
    const categoriaId = productCard.dataset.categoriaId;

    document.getElementById('editProductId').value = productId;
    document.getElementById('editProductName').value = nombre;
    document.getElementById('editProductDescription').value = descripcion;
    document.getElementById('editProductPrice').value = precio;

    const selectElement = document.getElementById('editProductCategory');
    loadCategoriesForSelect(selectElement, categoriaId);

    // Manejar la imagen actual
    const currentImage = productCard.querySelector('img');
    const imagePreview = document.getElementById('editImagePreview');
    const imageInput = document.getElementById('editProductImage');
    const imageInputContainer = imageInput.parentElement;
    
    if (currentImage) {
        imageInputContainer.classList.add('hidden');
        imagePreview.classList.remove('hidden');
        document.getElementById('editPreviewImage').src = currentImage.src;
    } else {
        imageInputContainer.classList.remove('hidden');
        imagePreview.classList.add('hidden');
    }

    if (editProductModal) {
        editProductModal.show();
    }
};

window.deleteProduct = function(productId) {
    const productCard = document.querySelector(`#productsList > div[data-id="${productId}"]`);
    if (!productCard) return;

    const categoriaId = productCard.dataset.categoriaId;
    const nombre = productCard.querySelector('h3').textContent.trim();

    document.getElementById('deleteProductId').value = productId;
    document.getElementById('deleteProductCategoryId').value = categoriaId;

    if (deleteProductModal) {
        deleteProductModal.show();
    }
};

// Función para cargar categorías en un select
function loadCategoriesForSelect(selectElement, selectedCategoryId = null) {
    while (selectElement.options.length > 1) {
        selectElement.remove(1);
    }
    
    const categoryCards = document.querySelectorAll('#categoriesList > div');
    categoryCards.forEach(card => {
        const option = document.createElement('option');
        option.value = card.dataset.id;
        const categoryName = card.querySelector('h3').textContent.trim();
        option.textContent = categoryName.replace(/^\s*[^\w]+\s*/, '').replace(/^\s*Principal\s*/, '').trim();
        selectElement.appendChild(option);
    });

    if (selectedCategoryId) {
        selectElement.value = selectedCategoryId;
    }
}

// Funciones para manejar el drag & drop de categorías
function handleDragStart(e) {
    this.style.opacity = '0.4';
    dragSrcEl = this;
    
    const categoriesList = document.getElementById('categoriesList');
    initialCategoryPositions = Array.from(categoriesList.children).map(cat => cat.dataset.id);
    
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', this.innerHTML);
    
    document.querySelectorAll('#categoriesList > div').forEach(item => {
        item.classList.add('drag-active');
    });
}

function handleDragOver(e) {
    if (e.preventDefault) {
        e.preventDefault();
    }
    e.dataTransfer.dropEffect = 'move';
    return false;
}

function handleDragEnter(e) {
    this.classList.add('drag-over');
}

function handleDragLeave(e) {
    this.classList.remove('drag-over');
}

function handleDrop(e) {
    if (e.stopPropagation) {
        e.stopPropagation();
    }
    
    if (dragSrcEl !== this) {
        const parent = document.getElementById('categoriesList');
        const srcIndex = Array.from(parent.children).indexOf(dragSrcEl);
        const destIndex = Array.from(parent.children).indexOf(this);
        
        if (srcIndex < destIndex) {
            parent.insertBefore(dragSrcEl, this.nextSibling);
        } else {
            parent.insertBefore(dragSrcEl, this);
        }
    }
    
    return false;
}

function handleDragEnd(e) {
    this.style.opacity = '1';
    
    document.querySelectorAll('#categoriesList > div').forEach(item => {
        item.classList.remove('drag-active', 'drag-over');
    });
    
    const categoriesList = document.getElementById('categoriesList');
    const currentPositions = Array.from(categoriesList.children).map(cat => cat.dataset.id);
    
    let positionsChanged = false;
    if (initialCategoryPositions.length === currentPositions.length) {
        for (let i = 0; i < initialCategoryPositions.length; i++) {
            if (initialCategoryPositions[i] !== currentPositions[i]) {
                positionsChanged = true;
                break;
            }
        }
    } else {
        positionsChanged = true;
    }
    
    if (positionsChanged) {
        updateCategoryPositions();
        utils.showNotification('success', 'Posiciones de categorías actualizadas correctamente');
    }
}

// Funciones para manejar el drag & drop de productos
function handleProductDragStart(e) {
    this.style.opacity = '0.4';
    dragSrcProduct = this;
    
    const productsList = document.getElementById('productsList');
    initialProductPositions = Array.from(productsList.children).map(prod => prod.dataset.id);
    
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', this.dataset.id);
    
    document.querySelectorAll('#productsList > div').forEach(item => {
        item.classList.add('drag-active');
    });
}

function handleProductDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    return false;
}

function handleProductDragEnter(e) {
    e.preventDefault();
    this.classList.add('drag-over');
}

function handleProductDragLeave(e) {
    e.preventDefault();
    this.classList.remove('drag-over');
}

function handleProductDrop(e) {
    e.preventDefault();
    
    if (dragSrcProduct !== this) {
        const parent = document.getElementById('productsList');
        const srcIndex = Array.from(parent.children).indexOf(dragSrcProduct);
        const destIndex = Array.from(parent.children).indexOf(this);
        
        if (srcIndex < destIndex) {
            parent.insertBefore(dragSrcProduct, this.nextSibling);
        } else {
            parent.insertBefore(dragSrcProduct, this);
        }
        
        const currentPositions = Array.from(parent.children).map(prod => prod.dataset.id);
        
        let positionsChanged = false;
        if (initialProductPositions.length === currentPositions.length) {
            for (let i = 0; i < initialProductPositions.length; i++) {
                if (initialProductPositions[i] !== currentPositions[i]) {
                    positionsChanged = true;
                    break;
                }
            }
        } else {
            positionsChanged = true;
        }
        
        if (positionsChanged) {
            updateProductPrincipalBadges();
            updateProductPositions();
            utils.showNotification('success', 'Posiciones de productos actualizadas correctamente');
        }
    }
    
    return false;
}

function handleProductDragEnd(e) {
    this.style.opacity = '1';
    
    document.querySelectorAll('#productsList > div').forEach(item => {
        item.classList.remove('drag-active', 'drag-over');
    });
}

// Funciones para actualizar posiciones
function updateCategoryPositions() {
    const categoriesList = document.getElementById('categoriesList');
    const categories = Array.from(categoriesList.children);
    
    const positionData = categories.map((category, index) => {
        return {
            id: category.dataset.id,
            position: index
        };
    });
    
    categories.forEach((category, index) => {
        const title = category.querySelector('h3');
        const nombre = category.dataset.nombre;
        
        if (index === 0) {
            title.innerHTML = `<div class="flex flex-col gap-1">
                <span class="badge-principal inline bg-blue-100 text-blue-800 text-xs font-medium px-2 py-0.5 rounded w-fit dark:bg-blue-900 dark:text-blue-300">Principal</span>
                <span>${nombre}</span>
            </div>`;
        } else {
            title.innerHTML = nombre;
        }
    });
    
    utils.fetch('/api/categorias.php?action=updatePositions', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(positionData)
    })
    .then(data => {
        if (!data.success) {
            utils.showNotification('error', data.message || 'Error al actualizar posiciones');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        utils.showNotification('error', 'Error de conexión al actualizar posiciones');
    });
}

function updateProductPositions() {
    const productsList = document.getElementById('productsList');
    const products = Array.from(productsList.children);
    const selectedCategory = document.querySelector('#categoriesList > div.ring-2.ring-blue-500');
    
    if (!selectedCategory) return;
    
    const positions = products.map((product, index) => ({
        id: parseInt(product.dataset.id),
        categoria_id: parseInt(selectedCategory.dataset.id),
        posicion: index + 1
    }));
    
    utils.fetch('/api/productos.php?action=updatePositions', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(positions)
    })
    .then(data => {
        if (!data.success) {
            utils.showNotification('error', 'Error al actualizar el orden de los productos');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        utils.showNotification('error', 'Error al actualizar el orden de los productos');
    });
}

function updateProductPrincipalBadges() {
    const productsList = document.getElementById('productsList');
    const products = Array.from(productsList.children);
    
    products.forEach((product, index) => {
        const title = product.querySelector('h3');
        const nombre = product.dataset.nombre || title.textContent.replace(/^\s*[^\w]+\s*/, '').trim();
        
        const existingBadge = title.querySelector('.badge-principal');
        if (existingBadge) {
            existingBadge.closest('.flex.flex-col')?.remove() || existingBadge.remove();
        }
        
        if (index === 0) {
            title.innerHTML = `<div class="flex flex-col gap-1">
                <span class="badge-principal inline bg-green-100 text-green-800 text-xs font-medium px-2 py-0.5 rounded w-fit dark:bg-green-900 dark:text-green-300">Principal</span>
                <span>${nombre}</span>
            </div>`;
        } else {
            title.innerHTML = nombre;
        }
    });
}

// Función para actualizar el estado del producto (solo en el JSON)
window.updateProductStatus = function(productId, isActive) {
    const selectedCategory = document.querySelector('#categoriesList > div.ring-2.ring-blue-500');
    if (!selectedCategory) {
        utils.showNotification('error', 'Por favor, selecciona una categoría');
        return;
    }

    const categoriesList = document.getElementById('categoriesList');
    const restauranteId = categoriesList.dataset.restauranteId;
    
    if (!restauranteId) {
        utils.showNotification('error', 'No se pudo identificar el restaurante. Por favor, recarga la página');
        // Revertir el toggle ya que no podemos proceder
        const toggle = document.querySelector(`#productsList > div[data-id="${productId}"] input[type="checkbox"]`);
        if (toggle) {
            toggle.checked = true; // Siempre activo por defecto
        }
        return;
    }

    // Actualizar el JSON del menú
    utils.fetch('/api/productos.php?action=updateJson', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            restaurante_id: restauranteId,
            producto_id: productId,
            estado: isActive ? 'activo' : 'inactivo'
        })
    })
    .then(data => {
        if (data.success) {
            utils.showNotification('success', 'Estado del producto actualizado en el menú');
        } else {
            utils.showNotification('error', data.message || 'Error al actualizar el estado del producto');
            // Revertir el toggle si hubo error
            const toggle = document.querySelector(`#productsList > div[data-id="${productId}"] input[type="checkbox"]`);
            if (toggle) {
                toggle.checked = true; // Siempre activo por defecto
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        utils.showNotification('error', 'Error al actualizar el estado del producto');
        // Revertir el toggle si hubo error
        const toggle = document.querySelector(`#productsList > div[data-id="${productId}"] input[type="checkbox"]`);
        if (toggle) {
            toggle.checked = true; // Siempre activo por defecto
        }
    });
};

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar Flowbite
    initFlowbite();
    
    // Inicializar tooltip
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-tooltip-target]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new Tooltip(tooltipTriggerEl);
    });
    
    // Inicializar modales
    const createModal = new window.Modal(document.getElementById('createCategoryModal'), {
        placement: 'center',
        backdrop: 'static',
        closable: true,
    });
    
    const createProductModal = new window.Modal(document.getElementById('createProductModal'), {
        placement: 'center',
        backdrop: 'dynamic',
        backdropClasses: 'bg-gray-900/50 dark:bg-gray-900/80 fixed inset-0 z-40',
        closable: true,
        onHide: () => {
            document.getElementById('createProductForm').reset();
            const backdrop = document.querySelector('.bg-gray-900');
            if (backdrop) {
                backdrop.remove();
            }
            const imageInputContainer = document.querySelector('#productImage').parentElement;
            const imagePreview = document.getElementById('imagePreview');
            imageInputContainer.classList.remove('hidden');
            imagePreview.classList.add('hidden');
            croppedBlobForCreate = null;
        }
    });
    
    const editModal = new window.Modal(document.getElementById('editCategoryModal'), {
        placement: 'center',
        backdrop: 'static',
        closable: true,
    });
    
    const deleteModal = new window.Modal(document.getElementById('deleteCategoryModal'), {
        placement: 'center',
        backdrop: 'static',
        closable: true,
    });
    
    editProductModal = new window.Modal(document.getElementById('editProductModal'), {
        placement: 'center',
        backdrop: 'static',
        closable: true,
        onHide: () => {
            // resetear form y preview cuando se cierra el modal de edición
            const form = document.getElementById('editProductForm');
            if (form) form.reset();
            const imageInputContainer = document.getElementById('editProductImage')?.parentElement;
            const imagePreview = document.getElementById('editImagePreview');
            if (imageInputContainer) imageInputContainer.classList.remove('hidden');
            if (imagePreview) imagePreview.classList.add('hidden');
            croppedBlobForEdit = null;
        }
    });
    
    deleteProductModal = new window.Modal(document.getElementById('deleteProductModal'), {
        placement: 'center',
        backdrop: 'static',
        closable: true,
    });
    
    // Logout modal
    document.getElementById('confirmLogout').addEventListener('click', function() {
        var r = window.USE_PRETTY_URLS ? (window.BASE_PATH + '/logout') : (window.BASE_PATH + '/index.php?route=logout');
        window.location.href = r;
    });
    
    // Cargar categorías existentes
    loadCategories();
    
    // Botones para mostrar el modal de creación de categoría
    document.getElementById('createFirstCategoryBtn').addEventListener('click', function() {
        createModal.show();
    });
    
    document.getElementById('addCategoryBtn').addEventListener('click', function() {
        createModal.show();
    });
    
    // Manejar la preview de la imagen
    let croppedBlobForCreate = null;
    let croppedBlobForEdit = null;
    let currentCropTarget = null; // 'create' o 'edit'
    let cropImage = null;
    let cropState = { scale: 1, offsetX: 0, offsetY: 0, dragging: false, lastX: 0, lastY: 0 };
    const DISPLAY_SIZE = 480; // canvas display size (px)

    document.getElementById('productImage').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 5 * 1024 * 1024) {
                utils.showNotification('error', 'La imagen no puede superar los 5MB');
                this.value = '';
                return;
            }

            const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                utils.showNotification('error', 'Solo se permiten imágenes PNG, JPG o WEBP');
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('imagePreview');
                const previewImage = document.getElementById('previewImage');
                const imageInputContainer = this.parentElement;
                
                imageInputContainer.classList.add('hidden');
                previewImage.src = e.target.result;
                preview.classList.remove('hidden');
                // Reset any previous crop
                croppedBlobForCreate = null;
            }.bind(this);
            reader.readAsDataURL(file);
        }
    });

    // Manejar la eliminación de la imagen
    document.getElementById('removeImage').addEventListener('click', function() {
        const imageInput = document.getElementById('productImage');
        const imagePreview = document.getElementById('imagePreview');
        const imageInputContainer = imageInput.parentElement;
        
        imageInput.value = '';
        imagePreview.classList.add('hidden');
        imageInputContainer.classList.remove('hidden');
        croppedBlobForCreate = null;
    });
    
    // Inicializar modal de recorte
    const cropModal = new window.Modal(document.getElementById('imageCropModal'), {
        placement: 'center', backdrop: 'dynamic', backdropClasses: 'bg-gray-900/50 dark:bg-gray-900/80 fixed inset-0 z-40', closable: true,
        onHide: () => {
            // limpiar estado
            cropImage = null;
            cropState = { scale: 1, offsetX: 0, offsetY: 0, dragging: false, lastX: 0, lastY: 0 };
            document.getElementById('cropZoom').value = 1;
            const ctx = document.getElementById('cropCanvas').getContext('2d');
            ctx.clearRect(0,0,DISPLAY_SIZE,DISPLAY_SIZE);
        }
    });

    // Funciones del recortador
    function initCrop(src, target) {
        currentCropTarget = target; // 'create' o 'edit'
        cropImage = new Image();
        cropImage.onload = function() {
            // establecer scale mínimo para cubrir el área
            const minScale = Math.max(DISPLAY_SIZE / cropImage.width, DISPLAY_SIZE / cropImage.height);
            // el slider representa zoom adicional sobre minScale; iniciar sin zoom extra
            cropState.minScale = minScale;
            cropState.maxScale = 3;
            cropState.scale = cropState.minScale;
            // centrar
            cropState.offsetX = (DISPLAY_SIZE - cropImage.width * cropState.scale) / 2;
            cropState.offsetY = (DISPLAY_SIZE - cropImage.height * cropState.scale) / 2;

            const zoomInput = document.getElementById('cropZoom');
            zoomInput.min = 0;
            zoomInput.max = (cropState.maxScale - cropState.minScale).toFixed(2);
            zoomInput.step = 0.01;
            zoomInput.value = 0;

            renderCropCanvas();
             // DEBUG: Verificar que el modal existe
        const modalEl = document.getElementById('imageCropModal');
        console.log('Modal encontrado:', modalEl);
        console.log('Intentando mostrar modal de recorte');
        
        // Forzar z-index alto ANTES de mostrar
        if (modalEl) {
            modalEl.style.zIndex = '9999'; // Muy alto para estar por encima de todo
        }
        
        // Intentar mostrar el modal
        try {
            cropModal.show();
            console.log('cropModal.show() llamado');
            
            // Verificar después de un momento si se mostró
            setTimeout(() => {
                const isVisible = !modalEl.classList.contains('hidden');
                console.log('Modal visible después de show():', isVisible);
                console.log('Display style:', window.getComputedStyle(modalEl).display);
                console.log('Z-index:', window.getComputedStyle(modalEl).zIndex);
            }, 100);
        } catch (error) {
            console.error('Error al mostrar modal:', error);
        }
            cropModal.show();
        };
        cropImage.src = src;
    }

    function renderCropCanvas() {
        const canvas = document.getElementById('cropCanvas');
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0,0,DISPLAY_SIZE,DISPLAY_SIZE);
        if (!cropImage) return;

        const destW = cropImage.width * cropState.scale;
        const destH = cropImage.height * cropState.scale;
        const dx = cropState.offsetX;
        const dy = cropState.offsetY;

        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0,0,DISPLAY_SIZE,DISPLAY_SIZE);
        ctx.drawImage(cropImage, dx, dy, destW, destH);
    }

    // Eventos de interacción en canvas
    const cropCanvasEl = document.getElementById('cropCanvas');
    cropCanvasEl.addEventListener('pointerdown', function(e) {
        e.preventDefault();
        cropState.dragging = true;
        cropState.lastX = e.clientX;
        cropState.lastY = e.clientY;
        cropCanvasEl.setPointerCapture(e.pointerId);
    });
    cropCanvasEl.addEventListener('pointermove', function(e) {
        if (!cropState.dragging) return;
        const dx = e.clientX - cropState.lastX;
        const dy = e.clientY - cropState.lastY;
        cropState.lastX = e.clientX;
        cropState.lastY = e.clientY;
        cropState.offsetX += dx;
        cropState.offsetY += dy;
        // limitar para que no deje huecos
        const destW = cropImage.width * cropState.scale;
        const destH = cropImage.height * cropState.scale;
        cropState.offsetX = Math.min(0, Math.max(cropState.offsetX, DISPLAY_SIZE - destW));
        cropState.offsetY = Math.min(0, Math.max(cropState.offsetY, DISPLAY_SIZE - destH));
        renderCropCanvas();
    });
    cropCanvasEl.addEventListener('pointerup', function(e) {
        cropState.dragging = false;
        try { cropCanvasEl.releasePointerCapture(e.pointerId); } catch (err) {}
    });
    cropCanvasEl.addEventListener('pointercancel', function(e) { cropState.dragging = false; });

    // Zoom
    document.getElementById('cropZoom').addEventListener('input', function(e) {
        const oldScale = cropState.scale;
        const sliderVal = parseFloat(e.target.value);
        const newScale = (cropState.minScale || 1) + (isNaN(sliderVal) ? 0 : sliderVal);
        // mantener el centro al cambiar zoom
        const centerX = DISPLAY_SIZE / 2;
        const centerY = DISPLAY_SIZE / 2;

        // coordenadas de centro en espacio de imagen antes del zoom
        const imgCenterX = (centerX - cropState.offsetX) / oldScale;
        const imgCenterY = (centerY - cropState.offsetY) / oldScale;

        cropState.scale = newScale;

        // recalcular offset para mantener el mismo punto en el centro
        cropState.offsetX = centerX - imgCenterX * cropState.scale;
        cropState.offsetY = centerY - imgCenterY * cropState.scale;

        const destW = cropImage.width * cropState.scale;
        const destH = cropImage.height * cropState.scale;
        cropState.offsetX = Math.min(0, Math.max(cropState.offsetX, DISPLAY_SIZE - destW));
        cropState.offsetY = Math.min(0, Math.max(cropState.offsetY, DISPLAY_SIZE - destH));

        renderCropCanvas();
    });

    // Botones para abrir el recortador
    document.getElementById('cropImageBtn').addEventListener('click', function() {
        const src = document.getElementById('previewImage').src;
        if (!src) return utils.showNotification('error', 'No hay imagen para recortar');
        initCrop(src, 'create');
    });
    document.getElementById('cropEditImageBtn').addEventListener('click', function() {
        const src = document.getElementById('editPreviewImage').src;
        if (!src) return utils.showNotification('error', 'No hay imagen para recortar');
        initCrop(src, 'edit');
    });

    // Confirmar recorte: generar Blob 500x500 y actualizar preview
    document.getElementById('confirmCropBtn').addEventListener('click', function() {
        if (!cropImage) return;
        // calcular el rect en la imagen fuente que corresponde al canvas
        const destW = cropImage.width * cropState.scale;
        const destH = cropImage.height * cropState.scale;
        const sx = Math.max(0, (-cropState.offsetX) / cropState.scale);
        const sy = Math.max(0, (-cropState.offsetY) / cropState.scale);
        const sWidth = Math.min(cropImage.width - sx, DISPLAY_SIZE / cropState.scale);
        const sHeight = Math.min(cropImage.height - sy, DISPLAY_SIZE / cropState.scale);

        const outCanvas = document.createElement('canvas');
        outCanvas.width = 500;
        outCanvas.height = 500;
        const outCtx = outCanvas.getContext('2d');
        outCtx.fillStyle = '#ffffff';
        outCtx.fillRect(0,0,500,500);

        outCtx.drawImage(cropImage, sx, sy, sWidth, sHeight, 0, 0, 500, 500);

        outCanvas.toBlob(function(blob) {
            if (!blob) return utils.showNotification('error', 'Error al generar la imagen recortada');
            // asignar blob al objetivo correspondiente
            if (currentCropTarget === 'create') {
                croppedBlobForCreate = blob;
                // actualizar preview con blob
                const prev = document.getElementById('previewImage');
                prev.src = URL.createObjectURL(blob);
                // asegurarse de mostrar preview
                document.getElementById('imagePreview').classList.remove('hidden');
                document.getElementById('productImage').parentElement.classList.add('hidden');
            } else if (currentCropTarget === 'edit') {
                croppedBlobForEdit = blob;
                const prev = document.getElementById('editPreviewImage');
                prev.src = URL.createObjectURL(blob);
                document.getElementById('editImagePreview').classList.remove('hidden');
                document.getElementById('editProductImage').parentElement.classList.add('hidden');
            }

            cropModal.hide();
            utils.showNotification('success', 'Recorte aplicado');
        }, 'image/jpeg', 0.9);
    });

    document.getElementById('cancelCropBtn').addEventListener('click', function() {
        cropModal.hide();
    });

    // Manejar el envío del formulario de creación de producto
    document.getElementById('createProductForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'create');
        // Si hay un recorte realizado en el cliente, reemplazar el archivo por el blob recortado
        if (croppedBlobForCreate) {
            formData.set('imagen', croppedBlobForCreate, 'producto.jpg');
            console.debug('menu.js: Enviando imagen recortada (create)');
        }
        
        const nombreProducto = formData.get('nombre');
        if (nombreProducto) {
            formData.set('nombre', nombreProducto.charAt(0).toUpperCase() + nombreProducto.slice(1));
        }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...';
        
        utils.fetch('/api/productos.php?action=create', {
            method: 'POST',
            body: formData
        })
        .then(data => {
            if (data.success) {
                const categoryId = formData.get('categoria_id');
                
                const categoryCard = document.querySelector(`#categoriesList > div[data-id="${categoryId}"]`);
                if (categoryCard) {
                    const countText = categoryCard.querySelector('p');
                    const currentCount = parseInt(countText.textContent);
                    countText.textContent = `${currentCount + 1} productos`;
                }
                
                const selectedCategory = document.querySelector('#categoriesList > div.ring-2.ring-blue-500');
                if (selectedCategory && selectedCategory.dataset.id === categoryId) {
                    const productsList = document.getElementById('productsList');
                    
                    if (productsList.querySelector('.col-span-full')) {
                        productsList.innerHTML = '';
                        productsList.style.display = 'grid';
                    }
                    
                    addProductToList(data.producto);
                }
                
                createProductModal.hide();
                utils.showNotification('success', 'Producto creado exitosamente');
                
                this.reset();
                document.getElementById('imagePreview').classList.add('hidden');
            } else {
                utils.showNotification('error', data.message || 'Error al crear el producto. Inténtalo de nuevo.');
            }
        })
        .catch(error => {
            console.error('Error en la petición:', error);
            utils.showNotification('error', 'Error al crear el producto. Inténtalo de nuevo.');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        });
    });
    
    // Manejar el envío del formulario de creación de categoría
    document.getElementById('createCategoryForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const categoryName = document.getElementById('categoryName').value.trim();
        
        if (categoryName) {
            const capitalizedCategoryName = categoryName.charAt(0).toUpperCase() + categoryName.slice(1);
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...';
            
            const formData = new FormData();
            formData.append('action', 'create');
            formData.append('nombre', capitalizedCategoryName);
            
            utils.fetch('/api/categorias.php?action=create', {
                method: 'POST',
                body: formData
            })
            .then(data => {
                if (data.success) {
                    document.getElementById('noCategoriesMessage').style.display = 'none';
                    
                    const categoriesList = document.getElementById('categoriesList');
                    categoriesList.style.display = 'grid';
                    
                    loadCategories();
                    
                    createModal.hide();
                    utils.showNotification('success', 'Categoría creada exitosamente');
                    
                    document.getElementById('categoryName').value = '';
                } else {
                    utils.showNotification('error', data.message || 'Error al crear la categoría');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                utils.showNotification('error', 'Error al crear la categoría. Inténtalo de nuevo.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        }
    });
    
    // Función para cargar las categorías existentes
    function loadCategories() {
        const loadingIndicator = document.getElementById('loadingIndicator');
        const noCategoriesMessage = document.getElementById('noCategoriesMessage');
        const categoriesList = document.getElementById('categoriesList');

        loadingIndicator.style.display = 'block';
        noCategoriesMessage.style.display = 'none';
        categoriesList.style.display = 'none';

        utils.fetch('/api/categorias.php?action=list')
        .then(data => {
            loadingIndicator.style.display = 'none';
            
            if (data.success && data.categorias && data.categorias.length > 0) {
                noCategoriesMessage.style.display = 'none';
                
                categoriesList.style.display = 'grid';
                categoriesList.innerHTML = '';
                
                // Asegurarnos de que el ID del restaurante se establezca correctamente
                if (data.restaurante_id) {
                    categoriesList.dataset.restauranteId = data.restaurante_id;
                } else {
                    // Si no viene en data.restaurante_id, intentar obtenerlo de la primera categoría
                    const firstCategory = data.categorias[0];
                    if (firstCategory && firstCategory.restaurante_id) {
                        categoriesList.dataset.restauranteId = firstCategory.restaurante_id;
                    }
                }
                
                data.categorias.forEach(categoria => {
                    addCategoryToList(
                        categoria.id, 
                        categoria.nombre, 
                        categoria.productos_count,
                        categoria.estado
                    );
                });
            } else {
                noCategoriesMessage.style.display = 'block';
                
                createModal.show();
            }
        })
        .catch(error => {
            console.error('Error al cargar categorías:', error);
            loadingIndicator.style.display = 'none';
            noCategoriesMessage.style.display = 'block';
        });
    }
    
    // Función para añadir una categoría a la lista
    function addCategoryToList(id, nombre, productosCount, estado) {
        const categoriesList = document.getElementById('categoriesList');
        const categoryCard = document.createElement('div');
        categoryCard.className = 'p-3 bg-white border border-gray-700 rounded-lg shadow dark:bg-gray-800 cursor-move';
        categoryCard.dataset.id = id;
        categoryCard.dataset.nombre = nombre;
        categoryCard.draggable = true;
        
        const isFirst = categoriesList.children.length === 0;
        const categoryLabel = isFirst ? 
            `<div class="flex flex-col gap-1">
                <span class="badge-principal inline bg-blue-100 text-blue-800 text-xs font-medium px-2 py-0.5 rounded w-fit dark:bg-blue-900 dark:text-blue-300">Principal</span>
                <span>${nombre}</span>
            </div>` : 
            nombre;
        
        categoryCard.innerHTML = `
            <div class="flex justify-between items-center">
                <div class="flex items-center flex-1">
                    <i class="fas fa-grip-vertical text-gray-400 handle mr-2"></i>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">${categoryLabel}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">${productosCount} productos</p>
                    </div>
                </div>
                <div class="flex flex-col gap-2 ml-4 items-center">
                    <label class="inline-flex items-center justify-center w-10 h-10 cursor-pointer">
                        <input type="checkbox" ${estado === 'activo' ? 'checked' : ''}
                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                               onclick="event.stopPropagation()">
                    </label>
                    <button type="button" class="edit-category-btn w-10 h-10 flex items-center justify-center text-blue-600 hover:text-white hover:bg-blue-600 rounded-lg transition-colors duration-200 dark:text-blue-400 dark:hover:text-white dark:hover:bg-blue-600">
                        <i class="fas fa-edit text-lg"></i>
                    </button>
                    <button type="button" class="delete-category-btn w-10 h-10 flex items-center justify-center text-red-600 hover:text-white hover:bg-red-600 rounded-lg transition-colors duration-200 dark:text-red-400 dark:hover:text-white dark:hover:bg-red-600">
                        <i class="fas fa-trash text-lg"></i>
                    </button>
                </div>
            </div>
        `;
        
        categoriesList.appendChild(categoryCard);
        
        categoryCard.addEventListener('dragstart', handleDragStart);
        categoryCard.addEventListener('dragover', handleDragOver);
        categoryCard.addEventListener('dragenter', handleDragEnter);
        categoryCard.addEventListener('dragleave', handleDragLeave);
        categoryCard.addEventListener('drop', handleDrop);
        categoryCard.addEventListener('dragend', handleDragEnd);
        
        categoryCard.querySelector('.edit-category-btn').addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('editCategoryId').value = id;
            document.getElementById('editCategoryName').value = nombre;
            
            editModal.show();
        });
        
        categoryCard.querySelector('.delete-category-btn').addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('deleteCategoryId').value = id;
            
            const countText = categoryCard.querySelector('p');
            const currentCountText = countText.textContent;
            const currentCount = parseInt(currentCountText);
            
            const warningElement = document.getElementById('deleteCategoryWarning');
            if (!isNaN(currentCount) && currentCount > 0) {
                warningElement.textContent = `Esta categoría contiene ${currentCount} productos. Debes eliminarlos primero.`;
            } else {
                warningElement.textContent = '';
            }
            
            deleteModal.show();
        });
        
        const checkbox = categoryCard.querySelector('input[type="checkbox"]');
        checkbox.addEventListener('change', function(e) {
            e.stopPropagation();
            updateCategoryStatus(id, this.checked);
        });

        categoryCard.addEventListener('click', function() {
            document.querySelectorAll('#categoriesList > div').forEach(card => {
                card.classList.remove('ring-2', 'ring-blue-500');
            });
            this.classList.add('ring-2', 'ring-blue-500');
            
            loadProducts(id, nombre);
            
            const addProductBtn = document.getElementById('addProductToSelectedBtn');
            addProductBtn.disabled = false;
            addProductBtn.dataset.categoryId = id;
        });
    }
    
    // Función para cargar productos de una categoría
    function loadProducts(categoryId, categoryName) {
        document.getElementById('noSelectedCategoryMessage').style.display = 'none';
        document.getElementById('productsList').style.display = 'none';
        document.getElementById('loadingProductsIndicator').style.display = 'block';
        
        const productsTitle = document.querySelector('#productsContainer h2');
        productsTitle.textContent = `Productos de ${categoryName}`;
        
        utils.fetch(`/api/productos.php?action=list&categoria_id=${categoryId}`)
        .then(data => {
            document.getElementById('loadingProductsIndicator').style.display = 'none';
            
            if (data.success && data.productos && data.productos.length > 0) {
                const productsList = document.getElementById('productsList');
                productsList.style.display = 'grid';
                productsList.innerHTML = '';
                
                data.productos.forEach(producto => {
                    producto.categoria_id = categoryId;
                    addProductToList(producto);
                });
            } else {
                const productsList = document.getElementById('productsList');
                productsList.style.display = 'block';
                productsList.innerHTML = `
                    <div class="col-span-full text-center p-4">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        <p class="text-white dark:text-white">No hay productos en esta categoría. Añade tu primer producto.</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error al cargar productos:', error);
            document.getElementById('loadingProductsIndicator').style.display = 'none';
            document.getElementById('productsList').style.display = 'block';
            document.getElementById('productsList').innerHTML = `
                <div class="col-span-full text-center p-4 text-red-500">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Error al cargar los productos. Intenta de nuevo.
                </div>
            `;
        });
    }
    
    // Función para añadir un producto a la lista
    function addProductToList(producto) {
        const productsList = document.getElementById('productsList');
        if (!productsList) {
            console.error('No se encontró el elemento productsList');
            return;
        }

        if (productsList.querySelector('.col-span-full')) {
            productsList.innerHTML = '';
            productsList.style.display = 'grid';
        }

        const productElement = document.createElement('div');
        productElement.className = 'bg-white rounded-lg shadow-md p-4 flex items-center justify-between relative cursor-move dark:bg-gray-800 border border-gray-700';
        productElement.dataset.id = producto.id;
        const selectedCategory = document.querySelector('#categoriesList > div.ring-2.ring-blue-500');
        productElement.dataset.categoriaId = producto.categoria_id || (selectedCategory ? selectedCategory.dataset.id : '');
        productElement.dataset.nombre = producto.nombre;
        productElement.draggable = true;

        const imageHtml = producto.imagen ? 
            `<img src="${producto.imagen.startsWith('/') ? producto.imagen : '/' + producto.imagen}" alt="${producto.nombre}" class="w-full h-full object-cover">` : 
            '<div class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500"><i class="fas fa-image text-3xl"></i></div>';

        const descriptionHtml = producto.descripcion ? 
            `<p class="text-sm text-gray-500 dark:text-gray-400 mt-1">${producto.descripcion}</p>` : '';

        productElement.innerHTML = `
            <div class="flex items-center gap-6 flex-1">
                <div class="w-24 h-24 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0 dark:bg-gray-700">
                    ${imageHtml}
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">${producto.nombre}</h3>
                    ${descriptionHtml}
                    <div class="flex items-center mt-2">
                        <p class="text-lg font-bold text-blue-600 dark:text-blue-400">${parseInt(producto.precio).toLocaleString('es-ES')}€</p>
                    </div>
                </div>
            </div>
            <div class="flex flex-col gap-2">
                <label class="inline-flex items-center justify-center w-10 h-10 cursor-pointer">
                    <input type="checkbox" ${producto.estado === 'activo' || producto.estado === undefined ? 'checked' : ''} 
                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" 
                           onchange="updateProductStatus(${producto.id}, this.checked)">
                </label>
                <button onclick="editProduct(${producto.id})" 
                        class="w-10 h-10 flex items-center justify-center text-blue-600 hover:text-white hover:bg-blue-600 rounded-lg transition-colors duration-200 dark:text-blue-400 dark:hover:text-white dark:hover:bg-blue-600">
                    <i class="fas fa-edit text-lg"></i>
                </button>
                <button onclick="deleteProduct(${producto.id})" 
                        class="w-10 h-10 flex items-center justify-center text-red-600 hover:text-white hover:bg-red-600 rounded-lg transition-colors duration-200 dark:text-red-400 dark:hover:text-white dark:hover:bg-red-600">
                    <i class="fas fa-trash text-lg"></i>
                </button>
            </div>
        `;

        productElement.addEventListener('dragstart', handleProductDragStart);
        productElement.addEventListener('dragend', handleProductDragEnd);
        productElement.addEventListener('dragover', handleProductDragOver);
        productElement.addEventListener('dragenter', handleProductDragEnter);
        productElement.addEventListener('dragleave', handleProductDragLeave);
        productElement.addEventListener('drop', handleProductDrop);

        productsList.appendChild(productElement);
        
        updateProductPrincipalBadges();
    }
    
    // Manejar el clic en el botón de añadir producto a la categoría seleccionada
    document.getElementById('addProductToSelectedBtn').addEventListener('click', function() {
        const categoryId = this.dataset.categoryId;
        if (categoryId) {
            const selectElement = document.getElementById('productCategory');
            loadCategoriesForSelect(selectElement, categoryId);
            createProductModal.show();
        }
    });
    
    // Manejar envío del formulario de edición de categoría
    document.getElementById('editCategoryForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const categoryId = document.getElementById('editCategoryId').value;
        let categoryName = document.getElementById('editCategoryName').value;
        
        if (categoryName && categoryId) {
            categoryName = categoryName.charAt(0).toUpperCase() + categoryName.slice(1);
            
            const submitBtn = document.getElementById('editCategorySubmitBtn');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...';
            
            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('id', categoryId);
            formData.append('nombre', categoryName);
            
            utils.fetch('/api/categorias.php?action=update', {
                method: 'POST',
                body: formData
            })
            .then(data => {
                if (data.success) {
                    const categoryCard = document.querySelector(`#categoriesList > div[data-id="${categoryId}"]`);
                    if (categoryCard) {
                        categoryCard.dataset.nombre = categoryName;
                        
                        const title = categoryCard.querySelector('h3');
                        const isPrincipal = title.querySelector('.badge-principal') !== null;
                        
                        if (isPrincipal) {
                            title.innerHTML = `<div class="flex flex-col gap-1">
                                <span class="badge-principal inline bg-blue-100 text-blue-800 text-xs font-medium px-2 py-0.5 rounded w-fit dark:bg-blue-900 dark:text-blue-300">Principal</span>
                                <span>${categoryName}</span>
                            </div>`;
                        } else {
                            title.innerHTML = categoryName;
                        }
                    }
                    
                    editModal.hide();
                    utils.showNotification('success', data.message);
                } else {
                    utils.showNotification('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                utils.showNotification('error', 'Error al actualizar la categoría');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        }
    });
    
    // Manejar eliminación de categoría
    document.getElementById('confirmDeleteCategory').addEventListener('click', function() {
        const categoryId = document.getElementById('deleteCategoryId').value;
        
        if (categoryId) {
            const deleteBtn = this;
            const originalBtnText = deleteBtn.innerHTML;
            deleteBtn.disabled = true;
            deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Eliminando...';
            
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', categoryId);
            
            utils.fetch('/api/categorias.php?action=delete', {
                method: 'POST',
                body: formData
            })
            .then(data => {
                if (data.success) {
                    deleteModal.hide();
                    utils.showNotification('success', data.message);
                    
                    // Verificar si la categoría eliminada era la seleccionada
                    const selectedCategory = document.querySelector('#categoriesList > div.ring-2.ring-blue-500');
                    if (selectedCategory && selectedCategory.dataset.id === categoryId) {
                        // Limpiar la selección
                        selectedCategory.classList.remove('ring-2', 'ring-blue-500');
                        
                        // Mostrar mensaje de selección de categoría
                        document.getElementById('noSelectedCategoryMessage').style.display = 'block';
                        document.getElementById('productsList').style.display = 'none';
                        
                        // Deshabilitar el botón de añadir producto
                        const addProductBtn = document.getElementById('addProductToSelectedBtn');
                        addProductBtn.disabled = true;
                        delete addProductBtn.dataset.categoryId;
                    }
                    
                    loadCategories();
                } else {
                    utils.showNotification('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                utils.showNotification('error', 'Error al eliminar la categoría');
            })
            .finally(() => {
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = originalBtnText;
            });
        }
    });
    
    // Manejar envío del formulario de edición de producto
    document.getElementById('editProductForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const productId = document.getElementById('editProductId').value;
        let productName = document.getElementById('editProductName').value.trim();
        const productPrice = document.getElementById('editProductPrice').value;
        const productDescription = document.getElementById('editProductDescription').value;
        const categoryId = document.getElementById('editProductCategory').value;
        
        if (productName && productId) {
            productName = productName.charAt(0).toUpperCase() + productName.slice(1);
            
            const submitBtn = document.getElementById('editProductSubmitBtn');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...';
            
            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('id', productId);
            formData.append('nombre', productName);
            formData.append('precio', productPrice);
            formData.append('descripcion', productDescription);
            formData.append('categoria_id', categoryId);
            
            const imageInput = document.getElementById('editProductImage');
            if (croppedBlobForEdit) {
                formData.append('imagen', croppedBlobForEdit, 'producto.jpg');
                console.debug('menu.js: Enviando imagen recortada (edit)');
            } else if (imageInput.files.length > 0) {
                formData.append('imagen', imageInput.files[0]);
            }
            
            utils.fetch('/api/productos.php?action=update', {
                method: 'POST',
                body: formData
            })
            .then(data => {
                if (data.success) {
                    const productCard = document.querySelector(`#productsList > div[data-id="${productId}"]`);
                    if (productCard) {
                        productCard.dataset.nombre = productName;
                        productCard.dataset.categoriaId = categoryId;
                        
                        const precioNum = parseInt(productPrice);
                        const precioFormateado = precioNum.toLocaleString('es-ES');
                        
                        const imageHtml = data.imagen ? 
                            `<img src="${data.imagen.startsWith('/') ? data.imagen : '/' + data.imagen}" alt="${productName}" class="w-full h-full object-cover">` : 
                            '<div class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500"><i class="fas fa-image text-3xl"></i></div>';

                        const descriptionHtml = productDescription ? 
                            `<p class="text-sm text-gray-500 dark:text-gray-400 mt-1">${productDescription}</p>` : '';

                        productCard.innerHTML = `
                            <div class="flex items-center gap-6 flex-1">
                                <div class="w-24 h-24 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0 dark:bg-gray-700">
                                    ${imageHtml}
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">${productName}</h3>
                                    ${descriptionHtml}
                                    <div class="flex items-center mt-2">
                                        <p class="text-lg font-bold text-blue-600 dark:text-blue-400">$${precioFormateado}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="inline-flex items-center justify-center w-10 h-10 cursor-pointer">
                                    <input type="checkbox" ${data.estado === 'activo' ? 'checked' : ''} 
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" 
                                           onchange="updateProductStatus(${productId}, this.checked)">
                                </label>
                                <button onclick="editProduct(${productId})" 
                                        class="w-10 h-10 flex items-center justify-center text-blue-600 hover:text-white hover:bg-blue-600 rounded-lg transition-colors duration-200 dark:text-blue-400 dark:hover:text-white dark:hover:bg-blue-600">
                                    <i class="fas fa-edit text-lg"></i>
                                </button>
                                <button onclick="deleteProduct(${productId})" 
                                        class="w-10 h-10 flex items-center justify-center text-red-600 hover:text-white hover:bg-red-600 rounded-lg transition-colors duration-200 dark:text-red-400 dark:hover:text-white dark:hover:bg-red-600">
                                    <i class="fas fa-trash text-lg"></i>
                                </button>
                            </div>
                        `;

                        // Restaurar los event listeners de drag and drop
                        productCard.addEventListener('dragstart', handleProductDragStart);
                        productCard.addEventListener('dragend', handleProductDragEnd);
                        productCard.addEventListener('dragover', handleProductDragOver);
                        productCard.addEventListener('dragenter', handleProductDragEnter);
                        productCard.addEventListener('dragleave', handleProductDragLeave);
                        productCard.addEventListener('drop', handleProductDrop);
                    }
                    
                    editProductModal.hide();
                    utils.showNotification('success', 'Producto actualizado correctamente');
                    
                    this.reset();
                    document.getElementById('editImagePreview').classList.add('hidden');
                    document.getElementById('editProductImage').parentElement.classList.remove('hidden');
                } else {
                    utils.showNotification('error', data.message || 'Error al actualizar el producto');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                utils.showNotification('error', 'Error de conexión al actualizar el producto');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        }
    });
    
    // Manejar eliminación de producto
    document.getElementById('confirmDeleteProduct').addEventListener('click', function() {
        const productId = document.getElementById('deleteProductId').value;
        const categoryId = document.getElementById('deleteProductCategoryId').value;
        
        if (productId && categoryId) {
            const deleteBtn = this;
            const originalBtnText = deleteBtn.innerHTML;
            deleteBtn.disabled = true;
            deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Eliminando...';
            
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', productId);
            formData.append('categoria_id', categoryId);
            
            utils.fetch('/api/productos.php?action=delete', {
                method: 'POST',
                body: formData
            })
            .then(data => {
                if (data.success) {
                    const productCard = document.querySelector(`#productsList > div[data-id="${productId}"]`);
                    if (productCard) {
                        productCard.remove();
                    }
                    
                    const categoryCard = document.querySelector(`#categoriesList > div[data-id="${categoryId}"]`);
                    if (categoryCard) {
                        const countText = categoryCard.querySelector('p');
                        const currentCountText = countText.textContent;
                        const currentCount = parseInt(currentCountText);
                        if (!isNaN(currentCount)) {
                            const newCount = Math.max(0, currentCount - 1);
                            countText.textContent = `${newCount} productos`;
                            
                            categoryCard.dataset.productosCount = newCount;
                            
                            const warningElement = document.getElementById('deleteCategoryWarning');
                            if (newCount === 0) {
                                warningElement.textContent = '';
                            } else {
                                warningElement.textContent = `Esta categoría contiene ${newCount} productos. Debes eliminarlos primero.`;
                            }
                        }
                    }
                    
                    const productsList = document.getElementById('productsList');
                    if (productsList.children.length === 0) {
                        productsList.innerHTML = `
                            <div class="col-span-full text-center p-4">
                                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                <p class="text-white dark:text-white">No hay productos en esta categoría. Añade tu primer producto.</p>
                            </div>
                        `;
                        productsList.style.display = 'block';
                    } else {
                        updateProductPositions();
                    }
                    
                    deleteProductModal.hide();
                    utils.showNotification('success', 'Producto eliminado correctamente');
                } else {
                    utils.showNotification('error', data.message || 'Error al eliminar el producto');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                utils.showNotification('error', 'Error de conexión al eliminar el producto');
            })
            .finally(() => {
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = originalBtnText;
            });
        }
    });
    
    // Manejar la preview de la imagen en el formulario de edición
    document.getElementById('editProductImage').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 5 * 1024 * 1024) {
                utils.showNotification('error', 'La imagen no puede superar los 5MB');
                this.value = '';
                return;
            }

            const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                utils.showNotification('error', 'Solo se permiten imágenes PNG, JPG o WEBP');
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('editImagePreview');
                const previewImage = document.getElementById('editPreviewImage');
                const imageInputContainer = this.parentElement;
                
                imageInputContainer.classList.add('hidden');
                previewImage.src = e.target.result;
                preview.classList.remove('hidden');
            }.bind(this);
            reader.readAsDataURL(file);
        }
    });
    
    // Manejar la eliminación de la imagen en el formulario de edición
    document.getElementById('removeEditImage').addEventListener('click', function() {
        const imageInput = document.getElementById('editProductImage');
        const imagePreview = document.getElementById('editImagePreview');
        const imageInputContainer = imageInput.parentElement;
        
        imageInput.value = '';
        imagePreview.classList.add('hidden');
        imageInputContainer.classList.remove('hidden');
    });

    window.updateCategoryStatus = function(categoryId, isActive) {
        const categoriesList = document.getElementById('categoriesList');
        const restauranteId = categoriesList.dataset.restauranteId;
        
        if (!restauranteId) {
            utils.showNotification('error', 'No se pudo identificar el restaurante. Por favor, recarga la página');
            // Revertir el toggle ya que no podemos proceder
            const toggle = document.querySelector(`#categoriesList > div[data-id="${categoryId}"] input[type="checkbox"]`);
            if (toggle) {
                toggle.checked = !isActive; // Revertir al estado anterior
            }
            return;
        }

        const checkbox = document.querySelector(`#categoriesList > div[data-id="${categoryId}"] input[type="checkbox"]`);
        checkbox.disabled = true; // Deshabilitar mientras se procesa

        utils.fetch('/api/categorias.php?action=updateStatus', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                restaurante_id: restauranteId,
                categoria_id: categoryId,
                estado: isActive ? 'activo' : 'inactivo'
            })
        })
        .then(data => {
            if (data.success) {
                utils.showNotification('success', 'Estado de la categoría actualizado correctamente');
            } else {
                utils.showNotification('error', data.message || 'Error al actualizar el estado de la categoría');
                checkbox.checked = !isActive; // Revertir el cambio
            }
        })
        .catch(error => {
            console.error('Error:', error);
            utils.showNotification('error', 'Error al actualizar el estado de la categoría');
            checkbox.checked = !isActive; // Revertir el cambio
        })
        .finally(() => {
            checkbox.disabled = false; // Re-habilitar el checkbox
        });
    };
}); 