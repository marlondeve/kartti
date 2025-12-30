<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Digital - Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/public/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <nav class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="text-2xl font-bold text-blue-600">Restaurante Demo</div>
            <div class="flex items-center space-x-4">
                <button class="text-gray-600 hover:text-blue-600">
                    <i class="fas fa-search"></i>
                </button>
                <button class="text-gray-600 hover:text-blue-600">
                    <i class="fas fa-filter"></i>
                </button>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <div class="relative h-64 bg-gradient-to-r from-blue-600 to-blue-800">
        <div class="absolute inset-0 bg-black opacity-40"></div>
        <div class="relative container mx-auto px-4 h-full flex items-center">
            <div class="text-white">
                <h1 class="text-4xl font-bold mb-2">Restaurante Demo</h1>
                <p class="text-lg">La mejor experiencia gastronómica</p>
            </div>
        </div>
    </div>

    <!-- Categories -->
    <div class="container mx-auto px-4 py-6">
        <div class="flex space-x-4 overflow-x-auto pb-4">
            <button class="px-4 py-2 bg-blue-600 text-white rounded-full whitespace-nowrap">
                Todos
            </button>
            <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-full whitespace-nowrap hover:bg-gray-300">
                Entradas
            </button>
            <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-full whitespace-nowrap hover:bg-gray-300">
                Productos Principales
            </button>
            <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-full whitespace-nowrap hover:bg-gray-300">
                Postres
            </button>
            <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-full whitespace-nowrap hover:bg-gray-300">
                Bebidas
            </button>
        </div>
    </div>

    <!-- Menu Items -->
    <div class="container mx-auto px-4 py-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Entrada -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c" alt="Sopa del Día" class="w-full h-48 object-cover">
                <div class="p-4">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-lg font-semibold">Sopa del Día</h3>
                        <span class="text-blue-600 font-bold">$8.99</span>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">Sopa casera preparada diariamente</p>
                    <div class="flex items-center text-sm text-gray-500">
                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                        <span>4.8</span>
                    </div>
                </div>
            </div>

            <!-- Producto Principal -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <img src="https://images.unsplash.com/photo-1544025162-d76694265947" alt="Filete de Res" class="w-full h-48 object-cover">
                <div class="p-4">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-lg font-semibold">Filete de Res</h3>
                        <span class="text-blue-600 font-bold">$24.99</span>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">Filete de res a la parrilla con vegetales</p>
                    <div class="flex items-center text-sm text-gray-500">
                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                        <span>4.9</span>
                    </div>
                </div>
            </div>

            <!-- Postre -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <img src="https://images.unsplash.com/photo-1571877227200-a0d98ea607e9" alt="Tiramisú" class="w-full h-48 object-cover">
                <div class="p-4">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-lg font-semibold">Tiramisú</h3>
                        <span class="text-blue-600 font-bold">$8.99</span>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">Postre italiano clásico</p>
                    <div class="flex items-center text-sm text-gray-500">
                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                        <span>4.7</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h4 class="text-lg font-semibold mb-4">Restaurante Demo</h4>
                    <p class="text-gray-400">La mejor experiencia gastronómica</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Horario</h4>
                    <p class="text-gray-400">Lunes - Viernes: 11:00 - 23:00</p>
                    <p class="text-gray-400">Sábados - Domingos: 12:00 - 00:00</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contacto</h4>
                    <p class="text-gray-400">Tel: (123) 456-7890</p>
                    <p class="text-gray-400">Email: info@restaurantedemo.com</p>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> Restaurante Demo. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="/public/js/script.js"></script>
</body>
</html> 