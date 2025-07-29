<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import { useNotifications } from '@/composables/useNotifications';

const $page = usePage();
const showingNavigationDropdown = ref(false);
const { theme, setTheme, fontSize, setFontSize, contrast, setContrast } = useTheme();

// 🔔 SISTEMA DE NOTIFICACIONES
const {
  notificaciones,
  totalNotificaciones,
  isPolling,
  iniciarPolling,
  detenerPolling,
  solicitarPermisoNotificaciones,
  crearNotificacionPrueba
} = useNotifications();

// Variables para el buscador global
const searchQuery = ref('');
const searchCategory = ref('todo');
const searchResults = ref([]);
const showResults = ref(false);
const searching = ref(false);
let searchTimeout = null;

// Sistema de permisos
const hasPermission = computed(() => (permission) => {
  const user = $page.props.auth.user;
  if (user?.roles?.some(role => role.name === 'Administrador')) return true;
  return user?.permissions?.includes(permission) || false;
});

const hasRole = computed(() => (role) => {
  return $page.props.auth.user?.roles?.some(r => r.name === role) || false;
});

const isAdmin = computed(() => hasRole.value('Administrador'));
const isEmpleado = computed(() => hasRole.value('Empleado Operativo'));
const isCliente = computed(() => hasRole.value('Cliente'));

// 🔔 Computed para mostrar notificaciones
const puedeVerNotificaciones = computed(() => {
  return isAdmin.value || isEmpleado.value;
});

const notificacionesRecientes = computed(() => {
  return notificaciones.value.slice(0, 5);
});

// Métodos del buscador
const handleSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout);

  if (searchQuery.value.trim().length < 2) {
    searchResults.value = [];
    showResults.value = false;
    return;
  }

  searching.value = true;

  searchTimeout = setTimeout(async () => {
    try {
      const response = await fetch(`/search?q=${encodeURIComponent(searchQuery.value)}&category=${searchCategory.value}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      });

      if (response.ok) {
        const data = await response.json();
        searchResults.value = data.results || [];
        showResults.value = true;
      }
    } catch (error) {
      searchResults.value = [];
    } finally {
      searching.value = false;
    }
  }, 300);
};

const goToResult = (result) => {
  showResults.value = false;
  searchQuery.value = '';

  const routes = {
    cliente: `/users/${result.id}`,
    vehiculo: `/vehiculos/${result.id}`,
    contrato: `/contratos/${result.id}`,
    reserva: `/reservas/${result.id}`,
    pago: `/pagos/${result.id}`
  };

  if (routes[result.type]) {
    router.visit(routes[result.type]);
  }
};

const handleClickOutside = (event) => {
  if (!event.target.closest('.search-container')) {
    showResults.value = false;
  }
};

// 🔔 Métodos para notificaciones
const toggleNotificaciones = () => {
  // Aquí podrías abrir un dropdown con las notificaciones
  console.log('Toggle notificaciones', notificacionesRecientes.value);
};

const verTodasNotificaciones = () => {
  router.visit('/notificaciones');
};

const crearPrueba = async () => {
  try {
    await crearNotificacionPrueba();
    console.log('Notificación de prueba creada');
  } catch (error) {
    console.error('Error creando notificación de prueba:', error);
  }
};

// Lifecycle
onMounted(async () => {
  document.addEventListener('click', handleClickOutside);

  // 🔔 Inicializar sistema de notificaciones para usuarios autorizados
  if (puedeVerNotificaciones.value) {
    // Solicitar permiso para notificaciones del navegador
    await solicitarPermisoNotificaciones();

    // Iniciar polling
    iniciarPolling();

    console.log('🔔 Sistema de notificaciones iniciado');
  }
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  if (searchTimeout) clearTimeout(searchTimeout);

  // 🔔 Detener polling al desmontar
  detenerPolling();
});
</script>

<template>
  <div class="min-h-screen bg-main text-main">
    <nav class="border-b nav-bg">
      <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <div class="flex items-center flex-shrink-0">
              <Link :href="route('dashboard')">
              <ApplicationLogo class="block w-auto fill-current h-9 text-main" />
              </Link>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden sm:flex sm:items-center sm:ms-10 sm:space-x-4">
              <NavLink v-if="!isCliente" :href="route('dashboard')" :active="route().current('dashboard')"
                class="text-main" style="font-size: inherit;">
                Dashboard
              </NavLink>

              <NavLink :href="route('vehiculos.show')" :active="route().current('vehiculos/show')" class="text-main"
                style="font-size: inherit;">
                Vehículos Disponibles
              </NavLink>

              <!-- Menú Operaciones -->
              <Dropdown v-if="!isCliente" align="left" width="64">
                <template #trigger>
                  <button
                    class="inline-flex items-center px-3 py-2 font-medium rounded text-main hover:opacity-80 transition-opacity"
                    style="font-size: calc(1em - 0.125rem);">
                    Operaciones
                    <svg class="ms-2 -me-0.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                    </svg>
                  </button>
                </template>
                <template #content>
                  <DropdownLink v-if="hasPermission('vehiculos.index')" :href="route('vehiculos.index')"
                    class="card-bg text-main" style="font-size: inherit;">
                    Vehículos
                  </DropdownLink>
                  <DropdownLink v-if="hasPermission('mantenimientos_Tipo.ver')" :href="route('mantenimientos.index')"
                    class="card-bg text-main" style="font-size: inherit;">
                    Tipos de Mantenimiento
                  </DropdownLink>
                  <DropdownLink v-if="hasPermission('mantenimientos.ver')"
                    :href="route('registro-mantenimientos.index')" class="card-bg text-main"
                    style="font-size: inherit;">
                    Registro de Mantenimientos
                  </DropdownLink>
                  <DropdownLink v-if="hasPermission('reservas.ver')" :href="route('reservas.index')"
                    class="card-bg text-main" style="font-size: inherit;">
                    Reservas
                  </DropdownLink>
                  <DropdownLink v-if="hasPermission('contratos.ver_todos')" :href="route('contratos.index')"
                    class="card-bg text-main" style="font-size: inherit;">
                    Contratos
                  </DropdownLink>
                </template>
              </Dropdown>

              <!-- Menú Administración -->
              <Dropdown v-if="isAdmin || isEmpleado" align="left" width="48">
                <template #trigger>
                  <button
                    class="inline-flex items-center px-3 py-2 font-medium rounded text-main hover:opacity-80 transition-opacity"
                    style="font-size: calc(1em - 0.125rem);">
                    Administración
                    <svg class="ms-2 -me-0.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                    </svg>
                  </button>
                </template>
                <template #content>
                  <DropdownLink v-if="hasPermission('usuarios.ver_todos')" :href="route('users.index')"
                    class="card-bg text-main" style="font-size: inherit;">
                    Usuarios
                  </DropdownLink>
                  <DropdownLink v-if="isAdmin" :href="route('roles.index')" class="card-bg text-main"
                    style="font-size: inherit;">
                    Roles
                  </DropdownLink>
                  <DropdownLink v-if="hasPermission('clausulas.index')" :href="route('clausulas.index')"
                    class="card-bg text-main" style="font-size: inherit;">
                    Clausulas
                  </DropdownLink>
                  <DropdownLink v-if="isAdmin" :href="route('frecuencia-pagos.index')" class="card-bg text-main"
                    style="font-size: inherit;">
                    Frecuencia de Pagos
                  </DropdownLink>
                  <DropdownLink v-if="isAdmin" :href="route('nro-cuentas.index')" class="card-bg text-main"
                    style="font-size: inherit;">
                    Nro de Cuentas
                  </DropdownLink>
                  <DropdownLink v-if="hasPermission('pagos.ver')" :href="route('pagos.index')" class="card-bg text-main"
                    style="font-size: inherit;">
                    Pagos
                  </DropdownLink>
                  <DropdownLink v-if="hasPermission('reportes.pagos')" :href="route('reportes.index')"
                    class="card-bg text-main" style="font-size: inherit;">
                    Reportes
                  </DropdownLink>
                </template>
              </Dropdown>

              <!-- Menú Cliente -->
              <Dropdown v-if="isCliente" align="left" width="48">
                <template #trigger>
                  <button
                    class="inline-flex items-center px-3 py-2 font-medium rounded text-main hover:opacity-80 transition-opacity"
                    style="font-size: calc(1em - 0.125rem);">
                    Mis Servicios
                    <svg class="ms-2 -me-0.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                    </svg>
                  </button>
                </template>
                <template #content>
                  <DropdownLink :href="route('contratos.index')" class="card-bg text-main" style="font-size: inherit;">
                    Mis Contratos
                  </DropdownLink>
                  <DropdownLink :href="route('pagos.index')" class="card-bg text-main" style="font-size: inherit;">
                    Mis Pagos
                  </DropdownLink>
                  <DropdownLink :href="route('reservas.index')" class="card-bg text-main" style="font-size: inherit;">
                    Mis Reservas
                  </DropdownLink>
                  <DropdownLink v-if="hasPermission('mantenimientos.ver')"
                    :href="route('registro-mantenimientos.index')" class="card-bg text-main"
                    style="font-size: inherit;">
                    Registro de Mantenimientos
                  </DropdownLink>
                  <DropdownLink :href="route('reportes.pagos.index')" class="card-bg text-main"
                    style="font-size: inherit;">
                    Mis Reportes
                  </DropdownLink>
                </template>
              </Dropdown>
            </div>
          </div>

          <!-- Buscador Global -->
          <div v-if="!isCliente" class="flex-1 max-w-lg mx-8 hidden sm:flex">
            <div class="relative w-full search-container">
              <div class="flex">
                <input v-model="searchQuery" @input="handleSearch" @focus="showResults = true" type="text"
                  placeholder="Buscar clientes, vehículos, contratos..."
                  class="flex-1 px-4 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent card-bg text-main"
                  style="font-size: calc(1em - 0.125rem);" />
                <select v-model="searchCategory" @change="handleSearch"
                  class="px-3 py-2 border border-l-0 border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-blue-500 card-bg text-main"
                  style="font-size: calc(1em - 0.125rem);">
                  <option value="todo">Todo</option>
                  <option v-if="hasPermission('usuarios.ver_todos')" value="clientes">Clientes</option>
                  <option v-if="hasPermission('vehiculos.index')" value="vehiculos">Vehículos</option>
                  <option v-if="hasPermission('contratos.ver_todos')" value="contratos">Contratos</option>
                  <option v-if="hasPermission('reservas.ver')" value="reservas">Reservas</option>
                  <option v-if="hasPermission('pagos.ver')" value="pagos">Pagos</option>
                </select>
              </div>

              <!-- Resultados -->
              <div v-if="showResults && searchResults.length > 0"
                class="absolute top-full left-0 right-0 mt-1 card-bg border border-gray-300 rounded-lg shadow-lg max-h-96 overflow-y-auto z-50">
                <div v-for="result in searchResults" :key="`${result.type}-${result.id}`" @click="goToResult(result)"
                  class="px-4 py-3 hover:opacity-80 cursor-pointer border-b border-gray-100 last:border-b-0 transition-opacity">
                  <div class="font-medium text-main" style="font-size: calc(1em - 0.125rem);">{{ result.title }}</div>
                  <div class="text-main opacity-60" style="font-size: calc(1em - 0.2rem);">{{ result.subtitle }}</div>
                </div>
              </div>

              <!-- Loading -->
              <div v-if="searching"
                class="absolute top-full left-0 right-0 mt-1 card-bg border border-gray-300 rounded-lg shadow-lg p-4 z-50">
                <div class="flex items-center justify-center space-x-2">
                  <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-500"></div>
                  <span class="text-main" style="font-size: calc(1em - 0.125rem);">Buscando...</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Controles de usuario -->
          <div class="hidden sm:flex sm:items-center sm:space-x-2">
            <select v-model="theme" @change="setTheme(theme)" class="p-1 border rounded card-bg text-main"
              style="font-size: calc(1em - 0.2rem);">
              <option value="auto">Auto</option>
              <option value="niños">Niños</option>
              <option value="jovenes">Jóvenes</option>
              <option value="adultos">Adultos</option>
              <option value="day">Día</option>
              <option value="night">Noche</option>
            </select>

            <select v-model="fontSize" @change="setFontSize(fontSize)" class="p-1 border rounded card-bg text-main"
              style="font-size: calc(1em - 0.2rem);">
              <option value="small">Pequeña</option>
              <option value="normal">Normal</option>
              <option value="large">Grande</option>
            </select>

            <select v-model="contrast" @change="setContrast(contrast)" class="p-1 border rounded card-bg text-main"
              style="font-size: calc(1em - 0.2rem);">
              <option value="normal">Contraste normal</option>
              <option value="high">Alto contraste</option>
            </select>

            <!-- 🔔 SISTEMA DE NOTIFICACIONES -->
            <div v-if="puedeVerNotificaciones" class="relative">
              <Dropdown align="right" width="80">
                <template #trigger>
                  <button class="relative p-2 text-main hover:opacity-80 transition-opacity">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                      <path fill-rule="evenodd"
                        d="M12 2a2 2 0 00-2 2v.341c-2.386.69-4 2.866-4 5.409v5.408L4 17h16l-2-1.842V9.75c0-2.543-1.614-4.719-4-5.409V4a2 2 0 00-2-2zm-1 17a1 1 0 102 0h-2z"
                        clip-rule="evenodd" />
                    </svg>

                    <!-- Badge de contador -->
                    <span v-if="totalNotificaciones > 0"
                      class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"
                      style="font-size: calc(1em - 0.25rem);">
                      {{ totalNotificaciones > 9 ? '9+' : totalNotificaciones }}
                    </span>

                    <!-- Indicador de polling activo -->
                    <span v-if="isPolling"
                      class="absolute -bottom-1 -right-1 bg-green-400 rounded-full h-2 w-2 animate-pulse"></span>
                  </button>
                </template>

                <template #content>
                  <!-- Header del dropdown -->
                  <div class="px-4 py-3 border-b border-gray-200 card-bg">
                    <div class="flex items-center justify-between">
                      <h3 class="font-semibold text-main" style="font-size: calc(1em + 0.125rem);">
                        🔔 Notificaciones
                      </h3>
                      <div class="flex space-x-2">
                        <!-- Botón de prueba solo para admin -->
                        <button v-if="isAdmin" @click="crearPrueba"
                          class="text-xs px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors"
                          style="font-size: calc(1em - 0.25rem);">
                          Prueba
                        </button>
                        <!-- Estado del polling -->
                        <span class="flex items-center space-x-1">
                          <div
                            :class="['w-2 h-2 rounded-full', isPolling ? 'bg-green-400 animate-pulse' : 'bg-gray-400']">
                          </div>
                          <span class="text-xs text-main opacity-60" style="font-size: calc(1em - 0.25rem);">
                            {{ isPolling ? 'Activo' : 'Inactivo' }}
                          </span>
                        </span>
                      </div>
                    </div>
                  </div>

                  <!-- Lista de notificaciones -->
                  <div class="max-h-80 overflow-y-auto">
                    <div v-if="notificacionesRecientes.length === 0"
                      class="px-4 py-6 text-center text-main opacity-60 card-bg">
                      <div class="text-4xl mb-2">🔕</div>
                      <p style="font-size: calc(1em - 0.125rem);">No hay notificaciones recientes</p>
                    </div>

                    <div v-for="notificacion in notificacionesRecientes" :key="notificacion.id"
                      class="px-4 py-3 border-b border-gray-100 last:border-b-0 hover:opacity-80 transition-opacity card-bg">
                      <div class="flex items-start space-x-3">
                        <div class="text-lg">{{ notificacion.icono }}</div>
                        <div class="flex-1 min-w-0">
                          <p class="font-medium text-main truncate" style="font-size: calc(1em - 0.125rem);">
                            {{ notificacion.mensaje }}
                          </p>
                          <div class="flex items-center justify-between mt-1">
                            <span :class="['px-2 py-1 text-xs rounded-full',
                              `bg-${notificacion.color}-100 text-${notificacion.color}-800`]"
                              style="font-size: calc(1em - 0.25rem);">
                              {{ notificacion.tipo.replace('_', ' ') }}
                            </span>
                            <span class="text-xs text-main opacity-50" style="font-size: calc(1em - 0.25rem);">
                              {{ notificacion.tiempo_transcurrido }}
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Footer del dropdown -->
                  <div class="px-4 py-3 border-t border-gray-200 card-bg">
                    <button @click="verTodasNotificaciones"
                      class="w-full text-center text-blue-600 hover:text-blue-800 font-medium transition-colors"
                      style="font-size: calc(1em - 0.125rem);">
                      Ver todas las notificaciones
                    </button>
                  </div>
                </template>
              </Dropdown>
            </div>

            <!-- Usuario -->
            <Dropdown align="right" width="48">
              <template #trigger>
                <button
                  class="inline-flex items-center px-3 py-2 font-medium border border-transparent rounded-md card-bg text-main hover:opacity-80 transition-opacity"
                  style="font-size: calc(1em - 0.125rem);">
                  {{ $page.props.auth.user.name }}
                  <svg class="ms-2 -me-0.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                      d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                      clip-rule="evenodd" />
                  </svg>
                </button>
              </template>
              <template #content>
                <DropdownLink :href="route('profile.edit')" class="card-bg text-main" style="font-size: inherit;">
                  Perfil
                </DropdownLink>
                <DropdownLink :href="route('logout')" method="post" as="button" class="card-bg text-main"
                  style="font-size: inherit;">
                  Salir
                </DropdownLink>
              </template>
            </Dropdown>
          </div>

          <!-- Mobile menu button -->
          <div class="flex items-center -me-2 sm:hidden">
            <button @click="showingNavigationDropdown = !showingNavigationDropdown"
              class="inline-flex p-2 rounded-md text-main hover:opacity-80 transition-opacity">
              <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                  stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                  stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile menu -->
      <div v-show="showingNavigationDropdown" class="border-t border-gray-200 sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
          <ResponsiveNavLink v-if="!isCliente" :href="route('dashboard')" :active="route().current('dashboard')"
            class="text-main" style="font-size: inherit;">
            Dashboard
          </ResponsiveNavLink>
          <ResponsiveNavLink :href="route('vehiculos.show')" :active="route().current('vehiculos.show')"
            class="text-main" style="font-size: inherit;">
            Vehículos Disponibles
          </ResponsiveNavLink>

          <!-- Operaciones móvil -->
          <div v-if="!isCliente" class="px-4 mt-2 font-semibold text-main opacity-70 uppercase"
            style="font-size: calc(1em - 0.25rem);">
            Operaciones
          </div>
          <ResponsiveNavLink v-if="hasPermission('vehiculos.index')" :href="route('vehiculos.index')" class="text-main"
            style="font-size: inherit;">
            Vehículos
          </ResponsiveNavLink>
          <ResponsiveNavLink v-if="hasPermission('mantenimientos_Tipo.ver')" :href="route('mantenimientos.index')"
            class="text-main" style="font-size: inherit;">
            Tipos de Mantenimiento
          </ResponsiveNavLink>
          <ResponsiveNavLink v-if="hasPermission('mantenimientos.ver')" :href="route('registro-mantenimientos.index')"
            class="text-main" style="font-size: inherit;">
            Registro de Mantenimientos
          </ResponsiveNavLink>
          <ResponsiveNavLink v-if="hasPermission('reservas.ver')" :href="route('reservas.index')" class="text-main"
            style="font-size: inherit;">
            Reservas
          </ResponsiveNavLink>
          <ResponsiveNavLink v-if="hasPermission('contratos.ver_todos')" :href="route('contratos.index')"
            class="text-main" style="font-size: inherit;">
            Contratos
          </ResponsiveNavLink>

          <!-- Administración móvil -->
          <div v-if="isAdmin || isEmpleado" class="px-4 mt-2 font-semibold text-main opacity-70 uppercase"
            style="font-size: calc(1em - 0.25rem);">
            Administración
          </div>
          <ResponsiveNavLink v-if="hasPermission('usuarios.ver_todos')" :href="route('users.index')" class="text-main"
            style="font-size: inherit;">
            Usuarios
          </ResponsiveNavLink>
          <ResponsiveNavLink v-if="isAdmin" :href="route('roles.index')" class="text-main" style="font-size: inherit;">
            Roles
          </ResponsiveNavLink>
          <ResponsiveNavLink v-if="hasPermission('usuarios.crear_cliente')" :href="route('garantes.index')"
            class="text-main" style="font-size: inherit;">
            Garantes
          </ResponsiveNavLink>
          <ResponsiveNavLink v-if="isAdmin" :href="route('frecuencia-pagos.index')" class="text-main"
            style="font-size: inherit;">
            Frecuencia de Pagos
          </ResponsiveNavLink>
          <ResponsiveNavLink v-if="isAdmin" :href="route('nro-cuentas.index')" class="text-main"
            style="font-size: inherit;">
            Nro de Cuentas
          </ResponsiveNavLink>
          <ResponsiveNavLink v-if="hasPermission('pagos.ver')" :href="route('pagos.index')" class="text-main"
            style="font-size: inherit;">
            Pagos
          </ResponsiveNavLink>
          <ResponsiveNavLink v-if="hasPermission('reportes.pagos')" :href="route('reportes.index')" class="text-main"
            style="font-size: inherit;">
            Reportes
          </ResponsiveNavLink>

          <!-- Cliente móvil -->
          <div v-if="isCliente" class="px-4 mt-2 font-semibold text-main opacity-70 uppercase"
            style="font-size: calc(1em - 0.25rem);">
            Mis Servicios
          </div>
          <ResponsiveNavLink v-if="isCliente" :href="route('contratos.index')" class="text-main"
            style="font-size: inherit;">
            Mis Contratos
          </ResponsiveNavLink>
          <ResponsiveNavLink v-if="isCliente" :href="route('pagos.index')" class="text-main"
            style="font-size: inherit;">
            Mis Pagos
          </ResponsiveNavLink>
          <ResponsiveNavLink v-if="isCliente" :href="route('reservas.index')" class="text-main"
            style="font-size: inherit;">
            Mis Reservas
          </ResponsiveNavLink>

          <!-- 🔔 Notificaciones móvil -->
          <div v-if="puedeVerNotificaciones" class="px-4 mt-4 border-t border-gray-200 pt-4">
            <div class="flex items-center justify-between mb-2">
              <span class="font-semibold text-main opacity-70 uppercase" style="font-size: calc(1em - 0.25rem);">
                🔔 Notificaciones
              </span>
              <span v-if="totalNotificaciones > 0"
                class="bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"
                style="font-size: calc(1em - 0.25rem);">
                {{ totalNotificaciones > 9 ? '9+' : totalNotificaciones }}
              </span>
            </div>
            <ResponsiveNavLink :href="route('notificaciones.index')" class="text-main" style="font-size: inherit;">
              Ver Notificaciones
            </ResponsiveNavLink>
          </div>
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
          <div class="px-4">
            <div class="font-medium text-main" style="font-size: calc(1em + 0.125rem);">
              {{ $page.props.auth.user.name }}
            </div>
            <div class="font-medium text-main opacity-70" style="font-size: inherit;">
              {{ $page.props.auth.user.email }}
            </div>
          </div>
          <div class="mt-3 space-y-1">
            <ResponsiveNavLink :href="route('profile.edit')" class="text-main" style="font-size: inherit;">
              Perfil
            </ResponsiveNavLink>
            <ResponsiveNavLink :href="route('logout')" method="post" as="button" class="text-main"
              style="font-size: inherit;">
              Salir
            </ResponsiveNavLink>
          </div>
        </div>
      </div>
    </nav>

    <header v-if="$slots.header" class="shadow card-bg">
      <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <slot name="header" />
      </div>
    </header>

    <main>
      <slot />
    </main>

    <footer class="p-4 mt-10 text-center card-bg text-main">
      <p style="font-size: calc(1em - 0.125rem);">
        Esta página ha sido visitada <strong>{{ $page.props.visitas?.contador || 0 }}</strong> veces.
        <!-- Debug info para notificaciones -->
        <span v-if="puedeVerNotificaciones && isAdmin" class="ml-4 text-xs opacity-50">
          | 🔔 Polling: {{ isPolling ? 'Activo' : 'Inactivo' }} | Notificaciones: {{ totalNotificaciones }}
        </span>
      </p>
    </footer>
  </div>
</template>