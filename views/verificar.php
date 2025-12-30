<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Cuenta - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <nav class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="/landing" class="text-2xl font-bold text-blue-600"><?php echo APP_NAME; ?></a>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-8">
            <h1 class="text-2xl font-bold text-center mb-8">Verificar Cuenta</h1>
            
            <div class="text-center mb-6">
                <p class="text-gray-600">
                    Hemos enviado un código de verificación a tu correo electrónico.
                    Por favor, ingresa el código para verificar tu cuenta.
                </p>
            </div>

            <form id="verificarForm" class="space-y-6">
                <!-- Email (oculto) -->
                <input type="hidden" id="email" value="<?php echo $_GET['email'] ?? ''; ?>">

                <!-- Código de Verificación -->
                <div>
                    <label for="codigo" class="block text-sm font-medium text-gray-700 mb-1">Código de Verificación</label>
                    <input type="text" id="codigo" name="codigo" required maxlength="6"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-center text-2xl tracking-widest">
                </div>

                <!-- Mensaje de error -->
                <div id="errorMessage" class="hidden text-red-600 text-sm"></div>

                <!-- Botón de Verificación -->
                <button type="submit"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Verificar Cuenta
                </button>

                <!-- Reenviar código -->
                <div class="text-center">
                    <button type="button" id="reenviarCodigo" class="text-blue-600 hover:text-blue-800 text-sm">
                        Reenviar código
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script>
        document.getElementById('verificarForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const codigo = document.getElementById('codigo').value;
            
            try {
                const response = await fetch('/api/api_auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'verificar',
                        email,
                        codigo
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Redirigir al login
                    window.location.href = '/login?verified=1';
                } else {
                    document.getElementById('errorMessage').textContent = data.message;
                    document.getElementById('errorMessage').classList.remove('hidden');
                }
            } catch (error) {
                document.getElementById('errorMessage').textContent = 'Error al procesar la solicitud';
                document.getElementById('errorMessage').classList.remove('hidden');
            }
        });

        // Reenviar código
        document.getElementById('reenviarCodigo').addEventListener('click', async function() {
            const email = document.getElementById('email').value;
            
            try {
                const response = await fetch('/api/api_auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'reenviar',
                        email
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Código reenviado correctamente. Revisa tu correo.');
                } else {
                    alert(data.message || 'Error al reenviar el código');
                }
            } catch (error) {
                alert('Error al reenviar el código');
            }
        });
    </script>
</body>
</html> 