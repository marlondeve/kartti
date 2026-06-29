<?php
// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    redirect_to('index.php?route=login');
}

// Obtener datos del usuario de la sesión
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
?>
<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Menú - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <style>
        /* Estilos para drag & drop */
        .drag-active {
            transition: transform 0.2s ease;
        }
        .drag-over {
            border: 2px dashed #3b82f6 !important;
            transform: translateY(5px);
        }
        .cursor-move {
            cursor: move;
        }
        .handle {
            cursor: grab;
        }
    </style>
    <script>
      window.BASE_PATH = '<?php echo addslashes(base_path('')); ?>';
      window.USE_PRETTY_URLS = false;
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
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

    <!-- Modal para crear categoría -->
    <div id="createCategoryModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Crear nueva categoría
                    </h3>
                    <button type="button" class="end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="createCategoryModal">
                        <i class="fas fa-times"></i>
                        <span class="sr-only">Cerrar modal</span>
                    </button>
                </div>
                <form id="createCategoryForm" class="p-4 md:p-5">
                    <div class="mb-4">
                        <label for="categoryName" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nombre de la categoría</label>
                        <input type="text" id="categoryName" name="nombre" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" placeholder="Ej: Entradas, Platos Principales, Postres..." required>
                    </div>
                    <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        Crear Categoría
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para editar categoría -->
    <div id="editCategoryModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Editar categoría
                    </h3>
                    <button type="button" class="end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="editCategoryModal">
                        <i class="fas fa-times"></i>
                        <span class="sr-only">Cerrar modal</span>
                    </button>
                </div>
                <form id="editCategoryForm" class="p-4 md:p-5">
                    <input type="hidden" id="editCategoryId" name="id">
                    <div class="mb-4">
                        <label for="editCategoryName" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nombre de la categoría</label>
                        <input type="text" id="editCategoryName" name="nombre" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required>
                    </div>
                    <button type="submit" id="editCategorySubmitBtn" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        Guardar Cambios
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para confirmar eliminación de categoría -->
    <div id="deleteCategoryModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Eliminar categoría
                    </h3>
                    <button type="button" class="end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="deleteCategoryModal">
                        <i class="fas fa-times"></i>
                        <span class="sr-only">Cerrar modal</span>
                    </button>
                </div>
                <div class="p-4 md:p-5">
                    <p class="text-gray-500 dark:text-gray-400 mb-4">¿Estás seguro de que deseas eliminar esta categoría? Esta acción no se puede deshacer.</p>
                    <p class="text-sm text-red-500 dark:text-red-400 mb-4" id="deleteCategoryWarning"></p>
                    <input type="hidden" id="deleteCategoryId">
                    <div class="flex justify-end gap-3">
                        <button type="button" class="py-2.5 px-5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600" data-modal-hide="deleteCategoryModal">
                            Cancelar
                        </button>
                        <button id="confirmDeleteCategory" type="button" class="text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para crear producto -->
    <div id="createProductModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Añadir nuevo producto
                    </h3>
                    <button type="button" class="end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="createProductModal">
                        <i class="fas fa-times"></i>
                        <span class="sr-only">Cerrar modal</span>
                    </button>
                </div>
                <form id="createProductForm" class="p-4 md:p-5" enctype="multipart/form-data">
                    <div class="mb-4" hidden>
                        <label for="productCategory" class=" block mb-2 text-sm font-medium text-gray-900 dark:text-white">Categoría</label>
                        <select id="productCategory" name="categoria_id" class=" bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required>
                            <option value="" disabled selected>Selecciona una categoría</option>
                            <!-- Las opciones se cargarán dinámicamente -->
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="productName" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nombre del producto</label>
                        <input type="text" id="productName" name="nombre" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" placeholder="Ej: Pizza Margarita, Ensalada César..." required>
                    </div>
                    <div class="mb-4">
                        <label for="productDescription" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Descripción</label>
                        <textarea id="productDescription" name="descripcion" rows="3" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" placeholder="Describe el producto..."></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="productPrice" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Precio</label>
                        <div class="flex">
                            <span class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border rounded-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                €
                            </span>
                            <input type="number" id="productPrice" name="precio" min="0" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-none rounded-e-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" placeholder="0" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="productImage" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Imagen del producto</label>
                        <div class="flex items-center justify-center w-full">
                            <label for="productImage" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-gray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 dark:text-gray-500 mb-2"></i>
                                    <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Haz clic para subir</span> o arrastra y suelta</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG o WEBP (MAX. 5MB)</p>
                                </div>
                                <input id="productImage" name="imagen" type="file" class="hidden" accept="image/png,image/jpeg,image/webp" />
                            </label>
                        </div>
                        <!-- Preview de la imagen -->
                        <div id="imagePreview" class="mt-2 hidden">
                            <img id="previewImage" class="max-h-40 rounded-lg mx-auto" alt="Preview">
                            <div class="flex justify-center gap-3 mt-2">
                                <button type="button" id="cropImageBtn" class="text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded-lg">
                                    <i class="fas fa-crop mr-1"></i>Recortar imagen
                                </button>
                                <button type="button" id="removeImage" class="text-red-600 hover:text-red-700 dark:text-red-500 dark:hover:text-red-400 text-sm px-3 py-1 rounded-lg">
                                    <i class="fas fa-trash mr-1"></i>Eliminar imagen
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="w-full text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800">
                        Crear Producto
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para editar producto -->
    <div id="editProductModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Editar producto
                    </h3>
                    <button type="button" class="end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="editProductModal">
                        <i class="fas fa-times"></i>
                        <span class="sr-only">Cerrar modal</span>
                    </button>
                </div>

    <!-- Modal para recortar imagen -->
    <div id="imageCropModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-2xl max-h-full">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Recortar imagen</h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="imageCropModal">
                        <i class="fas fa-times"></i>
                        <span class="sr-only">Cerrar modal</span>
                    </button>
                </div>
                <div class="p-4 md:p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Arrastra para mover y usa el control de zoom para ajustar. El resultado será un cuadrado de 500×500 px.</p>
                    <div class="flex flex-col items-center gap-3">
                        <div id="cropCanvasWrapper" class="bg-gray-100 rounded-lg overflow-hidden" style="width:480px;height:480px;">
                            <canvas id="cropCanvas" width="480" height="480" style="display:block;width:480px;height:480px;"></canvas>
                        </div>
                        <div class="w-full flex items-center gap-3">
                            <label class="text-sm text-gray-500">Zoom</label>
                            <input id="cropZoom" type="range" min="0" max="3" step="0.01" value="0" class="w-full">
                        </div>
                        <div class="flex gap-2">
                            <button id="confirmCropBtn" type="button" class="text-white bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg">Confirmar recorte</button>
                            <button id="cancelCropBtn" type="button" class="text-gray-700 bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg" data-modal-hide="imageCropModal">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
                <form id="editProductForm" class="p-4 md:p-5" enctype="multipart/form-data">
                    <input type="hidden" id="editProductId" name="id">
                    <div class="mb-4" hidden>
                        <label for="editProductCategory" class=" block mb-2 text-sm font-medium text-gray-900 dark:text-white">Categoría</label>
                        <select id="editProductCategory" name="categoria_id" class=" bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required>
                            <option value="" disabled selected>Selecciona una categoría</option>
                            <!-- Las opciones se cargarán dinámicamente -->
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="editProductName" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nombre del producto</label>
                        <input type="text" id="editProductName" name="nombre" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required>
                    </div>
                    <div class="mb-4">
                        <label for="editProductDescription" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Descripción</label>
                        <textarea id="editProductDescription" name="descripcion" rows="3" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="editProductPrice" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Precio</label>
                        <div class="flex">
                            <span class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border rounded-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                €
                            </span>
                            <input type="number" id="editProductPrice" name="precio" min="0" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-none rounded-e-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="editProductImage" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Imagen del producto</label>
                        <div class="flex items-center justify-center w-full">
                            <label for="editProductImage" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-gray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 dark:text-gray-500 mb-2"></i>
                                    <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Haz clic para subir</span> o arrastra y suelta</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG o WEBP (MAX. 5MB)</p>
                                </div>
                                <input id="editProductImage" name="imagen" type="file" class="hidden" accept="image/png,image/jpeg,image/webp" />
                            </label>
                        </div>
                        <!-- Preview de la imagen -->
                        <div id="editImagePreview" class="mt-2 hidden">
                            <img id="editPreviewImage" class="max-h-40 rounded-lg mx-auto" alt="Preview">
                            <div class="flex justify-center gap-3 mt-2">
                                <button type="button" id="cropEditImageBtn" class="text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded-lg">
                                    <i class="fas fa-crop mr-1"></i>Recortar imagen
                                </button>
                                <button type="button" id="removeEditImage" class="text-red-600 hover:text-red-700 dark:text-red-500 dark:hover:text-red-400 text-sm px-3 py-1 rounded-lg">
                                    <i class="fas fa-trash mr-1"></i>Eliminar imagen
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="submit" id="editProductSubmitBtn" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        Guardar Cambios
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para confirmar eliminación de producto -->
    <div id="deleteProductModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Eliminar producto
                    </h3>
                    <button type="button" class="end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="deleteProductModal">
                        <i class="fas fa-times"></i>
                        <span class="sr-only">Cerrar modal</span>
                    </button>
                </div>
                <div class="p-4 md:p-5">
                    <p class="text-gray-500 dark:text-gray-400 mb-4">¿Estás seguro de que deseas eliminar este producto? Esta acción no se puede deshacer.</p>
                    <input type="hidden" id="deleteProductId">
                    <input type="hidden" id="deleteProductCategoryId">
                    <div class="flex justify-end gap-3">
                        <button type="button" class="py-2.5 px-5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600" data-modal-hide="deleteProductModal">
                            Cancelar
                        </button>
                        <button id="confirmDeleteProduct" type="button" class="text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                        <a href="<?php echo base_path('index.php?route=menu'); ?>" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white bg-gray-100 dark:bg-gray-700 group">
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
                            <i class="fas fa-chart-bar w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"></i>
                            <span class="ml-3">Alertas</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo base_path('index.php?route=analitica'); ?>" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
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
            <nav class="bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 ">
                <div class="w-full flex flex-wrap items-center justify-between p-4">
                    <div class="flex items-center">
                        <button data-drawer-target="default-sidebar" data-drawer-toggle="default-sidebar" aria-controls="default-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
                            <span class="sr-only">Abrir menú</span>
                            <i class="fas fa-bars"></i>
                        </button>
                        <div class="flex items-center">
                            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Gestión de Menú</h1>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Contenido del menú -->
            <div class="p-4">
                <!-- Información de bienvenida -->
                <div class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
                    <div class="font-medium">Sección de Menú</div>
                    <div class="mt-1">Aquí podrás gestionar las categorías y productos de tu menú digital</div>
                </div>
                
                <!-- Contenedor de categorías -->
                <div id="categoriesContainer" class="p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 mb-4">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Categorías</h2>
                        <div class="flex gap-2">
                            <button type="button" id="addCategoryBtn" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                                <i class="fas fa-plus mr-2"></i>Añadir Categoría
                            </button>
                        </div>
                    </div>
                    
                    <!-- Mensaje de no categorías -->
                    <div id="noCategoriesMessage" class="text-center p-8" style="display: none;">
                        <i class="fas fa-folder-open text-5xl text-gray-400 dark:text-gray-600 mb-4"></i>
                        <p class="text-gray-500 dark:text-gray-400 mb-2">No tienes categorías creadas</p>
                        <p class="text-gray-500 dark:text-gray-400 mb-4">Añade tu primera categoría para comenzar a organizar tu menú</p>
                        <button type="button" id="createFirstCategoryBtn" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                            <i class="fas fa-plus mr-2"></i>Crear Primera Categoría
                        </button>
                    </div>
                    
                    <!-- Lista de categorías (inicialmente oculta) -->
                    <div id="categoriesList" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mt-4" style="display: none">
                        <!-- Las categorías se añadirán aquí dinámicamente -->
                    </div>
                    
                    <!-- Indicador de carga -->
                    <div id="loadingIndicator" class="text-center p-8">
                        <i class="fas fa-spinner fa-spin text-5xl text-gray-400 dark:text-gray-600 mb-4"></i>
                        <p class="text-gray-500 dark:text-gray-400">Cargando categorías...</p>
                    </div>
                </div>
                
                <!-- Contenedor de productos -->
                <div id="productsContainer" class="p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 mb-4">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Productos</h2>
                        <div class="flex gap-2">
                            <button type="button" id="addProductToSelectedBtn" class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-green-600 dark:hover:bg-green-700 focus:outline-none dark:focus:ring-green-800" disabled>
                                <i class="fas fa-plus mr-2"></i>Añadir Producto
                            </button>
                        </div>
                    </div>
                    
                    <!-- Mensaje de no categoría seleccionada -->
                    <div id="noSelectedCategoryMessage" class="text-center p-8">
                        <i class="fas fa-hand-pointer text-5xl text-gray-400 dark:text-gray-600 mb-4"></i>
                        <p class="text-gray-500 dark:text-gray-400 mb-2">Debe seleccionar una categoría primero</p>
                        <p class="text-gray-500 dark:text-gray-400">Haga clic en una categoría para ver sus productos</p>
                    </div>
                    
                    <!-- Lista de productos (inicialmente oculta) -->
                    <div id="productsList" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mt-4" style="display: none">
                        <!-- Los productos se añadirán aquí dinámicamente -->
                    </div>
                    
                    <!-- Indicador de carga para productos -->
                    <div id="loadingProductsIndicator" class="text-center p-8" style="display: none">
                        <i class="fas fa-spinner fa-spin text-5xl text-gray-400 dark:text-gray-600 mb-4"></i>
                        <p class="text-gray-500 dark:text-gray-400">Cargando productos...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>

    <script src="<?php echo base_path('public/js/script.js'); ?>"></script>
    <script src="<?php echo base_path('public/js/menu.js'); ?>"></script>
</body>
</html> 