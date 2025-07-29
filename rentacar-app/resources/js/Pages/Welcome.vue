<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useTheme } from '@/composables/useTheme'

const props = defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
  laravelVersion: String,
  phpVersion: String,
})

const { theme, setTheme, fontSize, setFontSize, contrast, setContrast } = useTheme()
</script>

<template>
  <Head title="RentaCar – Sistema de Alquiler de Vehículos" />

  <div class="min-h-screen flex flex-col bg-main text-main transition-colors duration-300">
    <!-- HEADER -->
    <header class="nav-bg px-4 py-3 flex justify-between items-center shadow-md">
      <div class="flex items-center space-x-3">
        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
          <span class="text-white font-bold" style="font-size: calc(1em - 0.2rem);">R</span>
        </div>
        <h1 class="font-bold text-main" style="font-size: calc(1em + 0.25rem);">
          RentaCar
        </h1>
      </div>
      
      <div class="flex items-center space-x-3">
        <!-- Controles de tema -->
        <div class="hidden sm:flex items-center space-x-2">
          <select v-model="theme" @change="setTheme(theme)"
                  class="p-2 border rounded-md card-bg text-main focus:ring-2 focus:ring-blue-500 transition-colors"
                  style="font-size: calc(1em - 0.15rem);">
            <option value="auto">Auto</option>
            <option value="niños">Niños</option>
            <option value="jovenes">Jóvenes</option>
            <option value="adultos">Adultos</option>
            <option value="day">Día</option>
            <option value="night">Noche</option>
          </select>

          <select v-model="fontSize" @change="setFontSize(fontSize)"
                  class="p-2 border rounded-md card-bg text-main focus:ring-2 focus:ring-blue-500 transition-colors"
                  style="font-size: calc(1em - 0.15rem);">
            <option value="small">Pequeña</option>
            <option value="normal">Normal</option>
            <option value="large">Grande</option>
          </select>

          <select v-model="contrast" @change="setContrast(contrast)"
                  class="p-2 border rounded-md card-bg text-main focus:ring-2 focus:ring-blue-500 transition-colors"
                  style="font-size: calc(1em - 0.15rem);">
            <option value="normal">Contraste normal</option>
            <option value="high">Alto contraste</option>
          </select>
        </div>

        <!-- Botones de autenticación -->
        <div class="flex items-center space-x-2">
          <template v-if="canLogin">
            <Link href="/login"
                  class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium shadow-md hover:shadow-lg"
                  style="font-size: calc(1em - 0.125rem);">
              Iniciar Sesión
            </Link>
          </template>
          <template v-if="canRegister">
            <Link href="/register"
                  class="px-4 py-2 border-2 border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors font-medium"
                  style="font-size: calc(1em - 0.125rem);">
              Registrarse
            </Link>
          </template>
        </div>
      </div>
    </header>

    <!-- HERO SECTION -->
    <section class="flex-1 flex flex-col justify-center items-center px-6 lg:px-12 py-20 text-center space-y-8">
      <div class="max-w-4xl space-y-6">
        <h2 class="font-bold leading-tight text-main" style="font-size: calc(1em + 1.5rem);">
          Sistema Profesional de Alquiler de Vehículos
        </h2>
        <p class="max-w-2xl mx-auto text-main opacity-80 leading-relaxed" style="font-size: calc(1em + 0.125rem);">
          Plataforma completa para la gestión de alquiler de vehículos con tecnología moderna, 
          diseñada para brindar una experiencia excepcional a usuarios de todas las edades.
        </p>
      </div>

      <!-- Características principales -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto mt-12">
        <div class="card-bg p-6 rounded-xl shadow-lg border border-opacity-20 border-gray-300">
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4 mx-auto">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <h3 class="font-semibold text-main mb-2" style="font-size: calc(1em + 0.125rem);">
            Gestión Completa
          </h3>
          <p class="text-main opacity-70" style="font-size: calc(1em - 0.075rem);">
            Sistema integral para reservas, contratos, pagos y mantenimiento de flota vehicular.
          </p>
        </div>

        <div class="card-bg p-6 rounded-xl shadow-lg border border-opacity-20 border-gray-300">
          <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4 mx-auto">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
          </div>
          <h3 class="font-semibold text-main mb-2" style="font-size: calc(1em + 0.125rem);">
            Seguro y Confiable
          </h3>
          <p class="text-main opacity-70" style="font-size: calc(1em - 0.075rem);">
            Plataforma segura con control de acceso por roles y protección de datos personales.
          </p>
        </div>

        <div class="card-bg p-6 rounded-xl shadow-lg border border-opacity-20 border-gray-300">
          <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4 mx-auto">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17v4a2 2 0 002 2h4M13 13h6"></path>
            </svg>
          </div>
          <h3 class="font-semibold text-main mb-2" style="font-size: calc(1em + 0.125rem);">
            Adaptable
          </h3>
          <p class="text-main opacity-70" style="font-size: calc(1em - 0.075rem);">
            Interfaz personalizable con temas y tamaños de fuente para diferentes tipos de usuarios.
          </p>
        </div>
      </div>

      <!-- Botones de acción -->
      <div class="flex flex-col sm:flex-row gap-4 justify-center pt-8">
        <Link href="/vehiculos/show"
              class="px-8 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium shadow-lg hover:shadow-xl"
              style="font-size: inherit;">
          Ver Flota de Vehículos
        </Link>
        <Link href="/login"
              class="px-8 py-3 border-2 border-green-600 text-green-600 rounded-lg hover:bg-green-50 transition-colors font-medium"
              style="font-size: inherit;">
          Acceder al Sistema
        </Link>
      </div>
    </section>

    <!-- PROCESO DE ALQUILER -->
    <section class="card-bg py-16 px-6 lg:px-12">
      <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12">
          <h3 class="font-bold text-main mb-4" style="font-size: calc(1em + 0.75rem);">
            Proceso Simple y Eficiente
          </h3>
          <p class="text-main opacity-70 max-w-2xl mx-auto" style="font-size: calc(1em + 0.125rem);">
            Nuestro sistema está diseñado para facilitar cada paso del proceso de alquiler
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="text-center">
            <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mb-6 mx-auto">
              <span class="text-white font-bold" style="font-size: calc(1em + 0.5rem);">1</span>
            </div>
            <h4 class="font-semibold text-main mb-3" style="font-size: calc(1em + 0.125rem);">
              Explora la Flota
            </h4>
            <p class="text-main opacity-70" style="font-size: inherit;">
              Navega por nuestra amplia selección de vehículos disponibles con información detallada de cada uno.
            </p>
          </div>

          <div class="text-center">
            <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mb-6 mx-auto">
              <span class="text-white font-bold" style="font-size: calc(1em + 0.5rem);">2</span>
            </div>
            <h4 class="font-semibold text-main mb-3" style="font-size: calc(1em + 0.125rem);">
              Realiza tu Reserva
            </h4>
            <p class="text-main opacity-70" style="font-size: inherit;">
              Selecciona fechas, completa el formulario de reserva y confirma tu elección de vehículo.
            </p>
          </div>

          <div class="text-center">
            <div class="w-16 h-16 bg-purple-600 rounded-full flex items-center justify-center mb-6 mx-auto">
              <span class="text-white font-bold" style="font-size: calc(1em + 0.5rem);">3</span>
            </div>
            <h4 class="font-semibold text-main mb-3" style="font-size: calc(1em + 0.125rem);">
              Gestiona tu Alquiler
            </h4>
            <p class="text-main opacity-70" style="font-size: inherit;">
              Firma el contrato, realiza el pago y disfruta de tu vehículo con total tranquilidad.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- FOOTER -->
    <footer class="nav-bg py-6 px-6 lg:px-12">
      <div class="max-w-6xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
          <div class="flex items-center space-x-3">
            <div class="w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center">
              <span class="text-white font-bold" style="font-size: calc(1em - 0.3rem);">R</span>
            </div>
            <span class="font-semibold text-main" style="font-size: calc(1em + 0.125rem);">RentaCar</span>
          </div>
          
          <div class="text-center md:text-right">
            <p class="text-main opacity-60" style="font-size: calc(1em - 0.125rem);">
              © 2025 RentaCar - Sistema de Gestión de Alquiler de Vehículos
            </p>
            <p class="text-main opacity-50 mt-1" style="font-size: calc(1em - 0.2rem);">
              Laravel {{ laravelVersion }} | PHP {{ phpVersion }}
            </p>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>