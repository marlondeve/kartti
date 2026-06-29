<!DOCTYPE html>
<html lang="es" class="light">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kartti - Menús Digitales con QR para Restaurantes y Bares</title>
    <meta name="description" content="Transforma tu restaurante con Kartti, la plataforma de menús digitales con QR dinámicos. Ofrece una experiencia interactiva, actualiza tu menú en tiempo real y ahorra costos." />
    <meta name="keywords" content="menú digital, código QR, restaurantes, bares, menú interactivo, qr dinámico, kartti" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="https://kartti.com/uploads/img/2.png" type="image/x-icon">
    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: {
          extend: {
            colors: {
              dark: {
                bg: '#1a1a1a',
                text: '#ffffff',
                primary: '#3b82f6',
                secondary: '#1e293b'
              }
            }
          }
        }
      }
    </script>
    <title>Kartti</title>
    <script>
      window.BASE_PATH = '<?php echo addslashes(base_path('')); ?>';
      window.USE_PRETTY_URLS = false;
    </script>
  </head>
   <body class="bg-white dark:bg-dark-bg text-gray-900 dark:text-dark-text transition-colors duration-200">
    <nav
      id="navbar"
      class="bg-white dark:bg-dark-bg fixed w-full z-30 top-0 left-0 transition-all duration-300"
    >
      <div
        class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4"
      >
        <a href="#" class="flex items-center space-x-3">
          <img src="https://kartti.com/uploads/img/2.png" class="h-8" alt="Logo" />
          <span
            class="self-center text-2xl font-semibold whitespace-nowrap text-gray-900 dark:text-white"
            >Kartti</span
          >
        </a>
        <div class="flex md:order-2 space-x-3 md:space-x-0">
          <!-- Dark mode toggle -->
          <button
            id="darkModeToggle"
            type="button"
            class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-2.5"
          >
            <svg
              id="darkModeIcon"
              class="w-5 h-5"
              fill="currentColor"
              viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"
              ></path>
            </svg>
            <svg
              id="lightModeIcon"
              class="w-5 h-5 hidden"
              fill="currentColor"
              viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                fill-rule="evenodd"
                d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                clip-rule="evenodd"
              ></path>
            </svg>
          </button>

          <!-- Language selector -->
          <div class="relative">
            <button
              id="languageButton"
              type="button"
              class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-2.5"
            >
              <svg
                class="w-5 h-5"
                fill="currentColor"
                viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M10 2a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12z"
                ></path>
                <path
                  d="M10 4a6 6 0 100 12 6 6 0 000-12zm0 10a4 4 0 110-8 4 4 0 010 8z"
                ></path>
              </svg>
            </button>
          </div>

          <button
            type="button"
            onclick="(function(){ var r = window.USE_PRETTY_URLS ? (window.BASE_PATH + '/login') : (window.BASE_PATH + '/index.php?route=login'); window.location.href = r; })()"
            class="text-white bg-blue-700 dark:bg-blue-600 transform transition-transform duration-300 hover:bg-blue-800 dark:hover:bg-blue-700 hover:scale-105 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:focus:ring-blue-800 font-medium rounded-lg text-sm px-4 py-2 text-center"
          >
            Iniciar Sesión
          </button>
          <button
            data-collapse-toggle="navbar-sticky"
            type="button"
            class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200"
            aria-controls="navbar-sticky"
            aria-expanded="false"
          >
            <span class="sr-only">Open main menu</span>
            <svg
              class="w-5 h-5"
              aria-hidden="true"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 17 14"
            >
              <path
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M1 1h15M1 7h15M1 13h15"
              />
            </svg>
          </button>
        </div>
        <div
          class="items-center justify-between hidden w-full md:flex md:w-auto md:order-1"
          id="navbar-sticky"
        >
          <ul
            class="flex flex-col p-4 md:p-0 mt-4 font-medium border border-gray-100 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 md:space-x-8 md:flex-row md:mt-0 md:border-0 md:bg-white dark:md:bg-dark-bg"
          >
            <li>
              <a
                href="#home"
                class="block py-2 px-3 text-blue-700 dark:text-blue-400 rounded md:p-0"
                aria-current="page"
                >Home</a
              >
            </li>
            <li>
              <a
                href="#about"
                class="block py-2 px-3 text-gray-900 dark:text-gray-200 rounded hover:bg-gray-100 dark:hover:bg-gray-700 md:hover:bg-transparent md:hover:text-blue-700 dark:md:hover:text-blue-400 md:p-0"
                >Sobre nosotros</a
              >
            </li>
            <li>
              <a
                href="#features"
                class="block py-2 px-3 text-gray-900 dark:text-gray-200 rounded hover:bg-gray-100 dark:hover:bg-gray-700 md:hover:bg-transparent md:hover:text-blue-700 dark:md:hover:text-blue-400 md:p-0"
                >Servicios</a
              >
            </li>
            <li>
              <a
                href="#contact"
                class="block py-2 px-3 text-gray-900 dark:text-gray-200 rounded hover:bg-gray-100 dark:hover:bg-gray-700 md:hover:bg-transparent md:hover:text-blue-700 dark:md:hover:text-blue-400 md:p-0"
                >Contáctanos</a
              >
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <section id="home" class="pt-20 px-6 md:px-16 bg-white dark:bg-dark-bg">
      <div
        class="flex flex-col-reverse md:flex-row items-center justify-between max-w-7xl mx-auto"
      >
        <!-- Texto -->
        <div class="md:w-1/2 text-center md:text-left">
          <div class="mb-12 space-y-2">
            <h1
              class="text-4xl md:text-6xl font-black leading-tight transform scale-y-125"
            >
              TRANSFORMA TU
            </h1>
            <br />
            <h1
              class="text-4xl md:text-6xl font-black leading-tight transform scale-y-125"
            >
              RESTAURANTE CON
            </h1>
            <br />
            <h1
              class="text-4xl md:text-6xl font-black leading-tight text-blue-600 transform scale-y-125"
            >
              KARTTI
            </h1>
          </div>

          <p class="text-gray-700 dark:text-gray-100 mb-6">
            Plataforma de menús digitales con QR dinámicos para restaurantes y bares. Su diferencial está en la simplicidad, personalización en tiempo real y la experiencia mejorada para clientes y meseros.
          </p>
          <div
            class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start"
          >
            <button
              class="bg-blue-600 text-white px-6 py-2 rounded-md transform transition-transform duration-300 hover:scale-105"
              onclick="(function(){ var r = window.USE_PRETTY_URLS ? (window.BASE_PATH + '/registro') : (window.BASE_PATH + '/index.php?route=registro'); window.location.href = r; })()"
            >
              Comenzar gratis
            </button>
            <button
              class="bg-blue-100 text-blue-700 px-6 py-2 rounded-md transform transition-transform duration-300 hover:scale-105"
                            onclick="window.open('https://kartti.com/carta/?id=7&tipo=restaurante&nombre=Mesa%201','_blank')"
            >
              Ver demo
            </button>
          </div>
        </div>

        <!-- Imagen -->
        <div class="md:w-1/2 mb-10 md:mb-0 flex justify-center">
          <img
            src="https://kartti.com/uploads/img/iphone.png"
            alt="Phone mockup"
            class="w-full max-w-xl"
          />
        </div>
      </div>
    </section>

    <section id="features" class="py-16 px-6 md:px-16 bg-white dark:bg-dark-bg text-center">
      <p class="text-blue-600 dark:text-blue-400 font-medium mb-2">
        Características que aman nuestros clientes
      </p>
      <h2 class="text-3xl font-black mb-12 text-gray-900 dark:text-white">¿Por qué usar Kartti?</h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-12 max-w-5xl mx-auto">
        <!-- Feature cards -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
          <div class="text-5xl text-blue-600 dark:text-blue-400 mb-4">📱</div>
          <h3 class="font-bold text-xl mb-2 text-gray-900 dark:text-white">Menú Digital Interactivo</h3>
          <p class="text-gray-600 dark:text-gray-300">
            Convierte tu menú en una experiencia interactiva con fotos, descripciones y precios actualizados en tiempo real.
          </p>
        </div>
        <!-- Repeat for other feature cards with same dark mode classes -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
          <div class="text-5xl text-blue-600 dark:text-blue-400 mb-4">💰</div>
          <h3 class="font-bold text-xl mb-2 text-gray-900 dark:text-white">Ahorro de Costos</h3>
          <p class="text-gray-600 dark:text-gray-300">
            Elimina la necesidad de imprimir menús físicos y actualiza tus precios sin costo adicional.
          </p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
          <div class="text-5xl text-blue-600 dark:text-blue-400 mb-4">📊</div>
          <h3 class="font-bold text-xl mb-2 text-gray-900 dark:text-white">Análisis en Tiempo Real</h3>
          <p class="text-gray-600 dark:text-gray-300">
            Obtén insights valiosos sobre los platos más populares y el comportamiento de tus clientes.
          </p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
          <div class="text-5xl text-blue-600 dark:text-blue-400 mb-4">⚡</div>
          <h3 class="font-bold text-xl mb-2 text-gray-900 dark:text-white">Actualización Instantánea</h3>
          <p class="text-gray-600 dark:text-gray-300">
            Modifica tu menú en segundos desde cualquier dispositivo, sin necesidad de conocimientos técnicos.
          </p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
          <div class="text-5xl text-blue-600 dark:text-blue-400 mb-4">🎯</div>
          <h3 class="font-bold text-xl mb-2 text-gray-900 dark:text-white">Personalización Total</h3>
          <p class="text-gray-600 dark:text-gray-300">
            Adapta el diseño a tu marca con colores, fuentes y estilos que reflejen la identidad de tu restaurante.
          </p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
          <div class="text-5xl text-blue-600 dark:text-blue-400 mb-4">🤝</div>
          <h3 class="font-bold text-xl mb-2 text-gray-900 dark:text-white">Soporte Dedicado</h3>
          <p class="text-gray-600 dark:text-gray-300">
            Equipo de soporte local disponible para ayudarte a maximizar el potencial de tu menú digital.
          </p>
        </div>
      </div>
    </section>

    <section id="about" class="py-20 px-6 md:px-16 bg-white dark:bg-dark-bg text-center">
      <h2 class="text-3xl font-black text-blue-600 dark:text-blue-400 mb-2">
        Elegido por restaurantes y bares que crecen
      </h2>
      <p class="text-gray-600 dark:text-gray-300 mb-12">Simplicidad operativa, personalización en tiempo real y una experiencia superior para clientes y meseros.</p>

      <div
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 max-w-7xl mx-auto text-left"
      >
        <!-- Testimonio 1 -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
          <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-200 flex items-center justify-center font-bold border-2 border-blue-600 dark:border-blue-400">LM</div>
            <div>
              <h4 class="font-bold text-gray-900 dark:text-white">Grupo La Morena</h4>
              <p class="text-gray-500 dark:text-gray-400 text-sm">Ana Martínez, Gerente de Operaciones</p>
            </div>
          </div>
          <p class="text-gray-600 dark:text-gray-300 text-sm">
            Implementar Kartti tomó menos de un día. Ahora actualizamos precios y fotos en tiempo real, sin depender de reimpresiones.
          </p>
          <div class="mt-4 flex items-center gap-2 text-yellow-400" aria-label="5 de 5 estrellas">★★★★★</div>
          <div class="mt-4 flex flex-wrap gap-2 text-xs">
            <span class="px-2 py-1 rounded-full bg-green-50 text-green-700 dark:bg-green-900/40 dark:text-green-300">-30% costos de impresión</span>
            <span class="px-2 py-1 rounded-full bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">+18% ticket promedio</span>
          </div>
        </div>

        <!-- Testimonio 2 -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
          <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-200 flex items-center justify-center font-bold border-2 border-blue-600 dark:border-blue-400">BC</div>
            <div>
              <h4 class="font-bold text-gray-900 dark:text-white">Bar Central</h4>
              <p class="text-gray-500 dark:text-gray-400 text-sm">Carlos Ruiz, Propietario</p>
            </div>
          </div>
          <p class="text-gray-600 dark:text-gray-300 text-sm">
            Los meseros procesan pedidos más rápido y los clientes encuentran todo sin preguntar. El tiempo de espera bajó en un 25%.
          </p>
          <div class="mt-4 flex items-center gap-2 text-yellow-400" aria-label="5 de 5 estrellas">★★★★★</div>
          <div class="mt-4 flex flex-wrap gap-2 text-xs">
            <span class="px-2 py-1 rounded-full bg-purple-50 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300">+32% velocidad de atención</span>
            <span class="px-2 py-1 rounded-full bg-amber-50 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">Mejor experiencia en sala</span>
          </div>
        </div>

        <!-- Testimonio 3 -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
          <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-200 flex items-center justify-center font-bold border-2 border-blue-600 dark:border-blue-400">CA</div>
            <div>
              <h4 class="font-bold text-gray-900 dark:text-white">Cadenas Andinas</h4>
              <p class="text-gray-500 dark:text-gray-400 text-sm">Sofía Delgado, Marketing</p>
            </div>
          </div>
          <p class="text-gray-600 dark:text-gray-300 text-sm">
            Personalizamos menús por sede en minutos. Promociones y disponibilidad se actualizan al instante según inventario.
          </p>
          <div class="mt-4 flex items-center gap-2 text-yellow-400" aria-label="5 de 5 estrellas">★★★★★</div>
          <div class="mt-4 flex flex-wrap gap-2 text-xs">
            <span class="px-2 py-1 rounded-full bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">+22% conversión en promos</span>
            <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">Gestión en tiempo real</span>
          </div>
        </div>
      </div>
    </section>

    <section id="contact" class="py-20 px-6 md:px-16 bg-white dark:bg-dark-bg text-center">
      <h2 class="text-3xl font-black mb-2 text-gray-900 dark:text-white">Elige tu plan</h2>
      <p class="text-xl text-gray-600 dark:text-gray-300 mb-8">El que mejor se adapte a ti</p>
      <p class="text-gray-500 dark:text-gray-400 mb-10">
        Selecciona el plan que mejor se ajuste a tus necesidades, estamos aquí para ayudarte
      </p>

      <!-- Toggle botones -->
      <div class="inline-flex bg-gray-100 dark:bg-gray-800 rounded-lg p-1 mb-12">
        <button id="billingAnnual"           class="px-4 py-2 rounded-md text-sm font-medium text-white bg-blue-600 dark:bg-blue-500"

         
        >
          Pago Mensual
        </button>
        <button id="billingMonthly"
           class="px-4 py-2 rounded-md text-sm font-medium bg-white dark:bg-gray-700 text-gray-800 dark:text-white"
        >
          Pago Anual
        </button>
      </div>

      <!-- Tarjetas -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-40 max-w-3xl mx-auto">
        <!-- Plan Básico -->
        <div class="bg-white dark:bg-gray-800 hover:bg-blue-500 dark:hover:bg-blue-600 transition duration-300 text-center py-10 px-4 max-w-xs rounded-xl shadow-md group">
          <h3 class="text-2xl font-bold mb-2 text-gray-900 dark:text-white group-hover:text-white">Básico</h3>
          <p class="text-sm mb-6 text-gray-600 dark:text-gray-300 group-hover:text-white">Para restaurantes en crecimiento</p>
          <p id="pricePro" class="text-4xl font-bold mb-2 text-gray-900 dark:text-white group-hover:text-white">20€</p>
          <div class="bg-gray-100 dark:bg-gray-700 group-hover:bg-white dark:group-hover:bg-gray-800 rounded-lg p-6 text-left mb-6 transition duration-300">
            <ul class="space-y-3 text-sm mb-6">

              <li class="flex items-center gap-2 dark:text-white text-black">
                <span class="group-hover:text-pink-500 text-pink-500">✔️</span> Actualizaciones Ilimitadas
              </li>
              <li class="flex items-center gap-2 dark:text-white text-black ">
                <span class="group-hover:text-pink-500 text-pink-500">✔️</span> Soporte Prioritario
              </li>
              <li class="flex items-center gap-2 dark:text-white text-black">
                <span class="group-hover:text-pink-500 text-pink-500">✔️</span> Estadísticas Avanzadas
              </li>
              <li class="flex items-center gap-2"><span>✔️</span> 1 Mes gratis</li>

            </ul>
                <button
          onclick="window.open('https://wa.me/573174202747?text=Hola,%20quiero%20información%20sobre%20Kartti%202', '_blank')"
          class="w-full bg-white dark:bg-blue-800 group-hover:bg-blue-600 text-blue-600 dark:text-white group-hover:text-white font-semibold px-4 py-2 rounded-md transition"
        >
         Comenzar ahora
        </button>

          </div>
        </div>
        

        <!-- Plan Business -->
        <div
          class="bg-white dark:bg-gray-800 hover:bg-blue-500 dark:hover:bg-blue-600 transition duration-300 text-center py-10 px-4 max-w-xs rounded-xl shadow-md group"
        >
          <h3 class="text-2xl font-bold mb-2 text-gray-900 dark:text-white group-hover:text-white">Empresarial</h3>
          <p class="text-sm mb-6 text-gray-600 dark:text-gray-300 group-hover:text-white">Para cadenas y grupos de restaurantes</p>
          <p class="text-4xl font-bold mb-4 text-gray-900 dark:text-white group-hover:text-white">Contacto</p>

          <div
            class="bg-gray-100 dark:bg-gray-700 group-hover:bg-white dark:group-hover:bg-gray-800 rounded-lg p-6 text-left mb-6 transition duration-300"
          >
            <ul class="space-y-3 text-sm mb-6 text-gray-600 dark:text-gray-300">
              <li class="flex items-center gap-2 dark:text-white text-black"><span>✔️</span> Menús Ilimitados</li>
              <li class="flex items-center gap-2 dark:text-white text-black"><span>✔️</span> Gerente de Cuenta Dedicado</li>
              <li class="flex items-center gap-2 dark:text-white text-black"><span>✔️</span> Personalización Total</li>
              <li class="flex items-center gap-2 dark:text-white text-black"><span>✔️</span> Capacitación Incluida</li>
            </ul>

        <button
          onclick="window.open('https://wa.me/573174202747?text=Hola,%20quiero%20información%20sobre%20Kartti%203', '_blank')"
          class="w-full bg-white dark:bg-blue-800 group-hover:bg-blue-600 text-blue-600 dark:text-white group-hover:text-white font-semibold px-4 py-2 rounded-md transition"
        >
          Contactar ventas
        </button>
          </div>
        </div>
      </div>
    </section>

    <footer class="bg-white dark:bg-dark-bg text-center px-6 md:px-16 pt-16 pb-10 border-t dark:border-gray-700">
      <!-- Newsletter Signup -->
      <div class="mb-12">
        <h2 class="text-blue-600 dark:text-blue-400 text-lg font-semibold mb-1">Suscríbete a nuestro boletín</h2>
        <p class="text-gray-700 dark:text-gray-300 mb-4">Mantente actualizado con las últimas novedades</p>
        <form class="flex justify-center items-center gap-2 max-w-md mx-auto">
          <input type="email" placeholder="Tu correo electrónico" class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-4 py-2 rounded w-full max-w-xs" />
          <button type="submit" class="bg-blue-600 dark:bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700 dark:hover:bg-blue-600 transition">Suscribirse</button>
        </form>
      </div>
    
      <!-- Footer bottom grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-left mt-10 max-w-5xl mx-auto">
        
        <!-- Logo + address -->
        <div class="flex flex-col items-center md:items-start text-center md:text-left">
          <img src="https://kartti.com/uploads/img/2.png" alt="Logo" class="h-16 w-auto object-contain" />
          <p class="text-sm text-gray-800 dark:text-gray-300 mt-4">Calle 123 #45-67<br>Bogotá, Colombia<br>+57 3173202747</p>
          <div class="mt-6">
            <p class="font-semibold mb-2 text-gray-900 dark:text-white">Síguenos</p>
            <div class="flex gap-4 justify-center md:justify-start">
              <a href="#" class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400"><i class="fab fa-facebook-f"></i></a>
              <a href="#" class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400"><i class="fab fa-instagram"></i></a>
              <a href="#" class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400"><i class="fab fa-whatsapp"></i></a>
            </div>
          </div>
        </div>
    
        <!-- Empty middle column for spacing -->
        <div></div>
    
        <!-- Resources -->
        <div>
          <h3 class="font-bold mb-4 text-gray-900 dark:text-white">Recursos</h3>
          <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
            <li><a href="#" class="hover:text-blue-600 dark:hover:text-blue-400">Sobre nosotros</a></li>
            <li><a href="#" class="hover:text-blue-600 dark:hover:text-blue-400">Preguntas frecuentes</a></li>
            <li><a href="#" class="hover:text-blue-600 dark:hover:text-blue-400">Contáctanos</a></li>
            <li><a href="#" class="hover:text-blue-600 dark:hover:text-blue-400">Blog</a></li>
            <li><a href="#" class="hover:text-blue-600 dark:hover:text-blue-400">Política de privacidad</a></li>
          </ul>
        </div>
      </div>
    </footer>
    
    
    <script>
      const navbar = document.getElementById('navbar');
      window.addEventListener('scroll', function () {
        if (window.scrollY > 10) {
          navbar.classList.add('border-b', 'border-gray-200', 'dark:border-gray-700');
        } else {
          navbar.classList.remove('border-b', 'border-gray-200', 'dark:border-gray-700');
        }
      });

      // Toggle precios mensual/anual
      (function(){
        var btnMonthly = document.getElementById('billingMonthly');
        var btnAnnual = document.getElementById('billingAnnual');
        var pricePro = document.getElementById('pricePro');
        var savePro = document.getElementById('savePro');
        if(!btnMonthly || !btnAnnual || !pricePro) return;

        function setMonthly(){
          pricePro.textContent = '200€';
          if (savePro) savePro.classList.remove('hidden');
          btnAnnual.classList.add('bg-white','dark:bg-gray-700','text-gray-800','dark:text-white');
          btnAnnual.classList.remove('text-white','bg-blue-600','dark:bg-blue-500');
          btnMonthly.classList.remove('bg-white','dark:bg-gray-700','text-gray-800','dark:text-white');
          btnMonthly.classList.add('text-white','bg-blue-600','dark:bg-blue-500');
        }

        function setAnnual(){
          pricePro.textContent = '20€';
          if (savePro) savePro.classList.add('hidden');
          btnMonthly.classList.add('bg-white','dark:bg-gray-700','text-gray-800','dark:text-white');
          btnMonthly.classList.remove('text-white','bg-blue-600','dark:bg-blue-500');
          btnAnnual.classList.remove('bg-white','dark:bg-gray-700','text-gray-800','dark:text-white');
          btnAnnual.classList.add('text-white','bg-blue-600','dark:bg-blue-500');
          
        }

        btnMonthly.addEventListener('click', setMonthly);
        btnAnnual.addEventListener('click', setAnnual);
      })();

      // Dark mode functionality
      const darkModeToggle = document.getElementById('darkModeToggle');
      const darkModeIcon = document.getElementById('darkModeIcon');
      const lightModeIcon = document.getElementById('lightModeIcon');
      
      // Check for saved theme preference or use system preference
      const savedTheme = localStorage.getItem('theme');
      const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      
      // Set initial theme
      if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
        document.documentElement.classList.add('dark');
        darkModeIcon.classList.add('hidden');
        lightModeIcon.classList.remove('hidden');
      } else {
        document.documentElement.classList.remove('dark');
        darkModeIcon.classList.remove('hidden');
        lightModeIcon.classList.add('hidden');
      }
      
      // Toggle theme
      darkModeToggle.addEventListener('click', () => {
        const isDark = document.documentElement.classList.toggle('dark');
        darkModeIcon.classList.toggle('hidden');
        lightModeIcon.classList.toggle('hidden');
        
        // Save preference
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
      });

      // Smooth scrolling for navigation links
      const navLinks = document.querySelectorAll('#navbar-sticky a[href^="#"]');
      navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
          e.preventDefault();
          const targetId = this.getAttribute('href');
          if (targetId === '#') return; // Skip if href is just "#"
          
          const targetElement = document.querySelector(targetId);
          if (targetElement) {
            const navbarHeight = document.getElementById('navbar').offsetHeight;
            const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - navbarHeight;
            
            window.scrollTo({
              top: targetPosition,
              behavior: 'smooth'
            });
          }
        });
      });

      // Highlight active section in navigation
      const sections = document.querySelectorAll('section[id]');

      function highlightNavigation() {
        const scrollPosition = window.scrollY + 100; // Offset for navbar

        sections.forEach(section => {
          const sectionTop = section.offsetTop;
          const sectionHeight = section.offsetHeight;
          const sectionId = section.getAttribute('id');
          
          if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
            navLinks.forEach(link => {
              link.classList.remove('text-blue-700', 'dark:text-blue-400');
              link.classList.add('text-gray-900', 'dark:text-gray-200');
              
              if (link.getAttribute('href') === `#${sectionId}`) {
                link.classList.remove('text-gray-900', 'dark:text-gray-200');
                link.classList.add('text-blue-700', 'dark:text-blue-400');
              }
            });
          }
        });
      }

      window.addEventListener('scroll', highlightNavigation);
    </script>
  </body>
</html>

