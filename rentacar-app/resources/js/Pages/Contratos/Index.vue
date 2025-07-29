<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import Swal from 'sweetalert2';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import 'dayjs/locale/es';

dayjs.extend(relativeTime);
dayjs.locale('es');

const props = defineProps({
  contratos: Array,
  filters: Object,
});

const $page = usePage();

const hasPermission = computed(() => (permission) => {
  const user = $page.props.auth.user;
  if (user?.roles?.some(role => role.name === 'Administrador')) return true;
  return user?.permissions?.includes(permission) || false;
});

const search = ref(props.filters?.search || '');

watch(search, (val) => {
  router.get('/contratos', { search: val }, { preserveState: true, replace: true });
});

const eliminar = (id) => {
  Swal.fire({
    title: '¿Eliminar Contrato?',
    text: 'Esta acción eliminará permanentemente el contrato y liberará el vehículo',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#dc2626',
  }).then(result => {
    if (result.isConfirmed) {
      router.delete(`/contratos/${id}`, {
        onSuccess: () => Swal.fire('Eliminado', 'Contrato eliminado correctamente', 'success'),
        onError: () => Swal.fire('Error', 'No se pudo eliminar el contrato', 'error'),
      });
    }
  });
};

const getEstadoColor = (estado) => {
  switch (estado.toLowerCase()) {
    case 'vigente':
      return 'bg-green-100 text-green-800 border-green-200';
    case 'cumplido':
      return 'bg-blue-100 text-blue-800 border-blue-200';
    case 'vencido':
      return 'bg-red-100 text-red-800 border-red-200';
    case 'cancelado':
      return 'bg-gray-100 text-gray-800 border-gray-200';
    default:
      return 'bg-yellow-100 text-yellow-800 border-yellow-200';
  }
};

const calcularDiasRestantes = (fechaFin) => {
  const hoy = dayjs();
  const fin = dayjs(fechaFin);
  const diff = fin.diff(hoy, 'day');

  if (diff > 0) {
    return `${diff} día${diff !== 1 ? 's' : ''} restante${diff !== 1 ? 's' : ''}`;
  } else if (diff === 0) {
    return 'Vence hoy';
  } else {
    return `Vencido hace ${Math.abs(diff)} día${Math.abs(diff) !== 1 ? 's' : ''}`;
  }
};

const calcularDuracionTotal = (fechaInicio, fechaFin) => {
  const inicio = dayjs(fechaInicio);
  const fin = dayjs(fechaFin);
  return fin.diff(inicio, 'day') + 1;
};

const estadisticas = computed(() => {
  const total = props.contratos.length;
  const vigentes = props.contratos.filter(c => c.estado.toLowerCase() === 'vigente').length;
  const cumplidos = props.contratos.filter(c => c.estado.toLowerCase() === 'cumplido').length;
  const Pendientes = props.contratos.filter(c => c.estado.toLowerCase() === 'pendiente de aprobacion').length;
  const vencidos = props.contratos.filter(c => {
    const hoy = dayjs();
    const fin = dayjs(c.fecha_fin);
    return c.estado.toLowerCase() === 'vigente' && fin.isBefore(hoy);
  }).length;

  return { total, vigentes, cumplidos, vencidos, Pendientes };
});
</script>

<template>

  <Head title="Contratos" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="bg-blue-100 p-2 rounded-lg">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
              </path>
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-gray-900">Gestión de Contratos</h2>
        </div>
        <Link href="/contratos/create"
          class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        <span>Nuevo Contrato</span>
        </Link>
      </div>
    </template>


    <div class="py-8">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <!-- Estadísticas -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
          <div class="bg-white rounded-lg shadow-sm border p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-medium text-gray-600">Total</p>
                <p class="text-xl font-bold text-gray-900">{{ estadisticas.total }}</p>
              </div>
              <div class="bg-blue-100 p-2 rounded-lg">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                  </path>
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm border p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-medium text-gray-600">Vigentes</p>
                <p class="text-xl font-bold text-green-600">{{ estadisticas.vigentes }}</p>
              </div>
              <div class="bg-green-100 p-2 rounded-lg">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm border p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-medium text-gray-600">Cumplidos</p>
                <p class="text-xl font-bold text-blue-600">{{ estadisticas.cumplidos }}</p>
              </div>
              <div class="bg-blue-100 p-2 rounded-lg">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm border p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-medium text-gray-600">Pendientes</p>
                <p class="text-xl font-bold text-amber-600">{{ estadisticas.Pendientes }}</p>
              </div>
              <div class="bg-amber-100 p-2 rounded-lg">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm border p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-medium text-gray-600">Vencidos</p>
                <p class="text-xl font-bold text-red-600">{{ estadisticas.vencidos }}</p>
              </div>
              <div class="bg-red-100 p-2 rounded-lg">
                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
          </div>
        </div>
        <div class="mb-4 max-w-md">
          <input v-model="search" type="text" placeholder="Buscar por id, cliente o vehiculo..."
            class="border rounded px-3 py-1 w-full" />
        </div>
        <!-- Lista de Contratos -->
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">Lista de Contratos</h3>
          </div>


          <div v-if="contratos.length === 0" class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
              </path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No hay contratos</h3>
            <p class="text-gray-500 mb-4">Comienza creando tu primer contrato de alquiler</p>
            <Link href="/contratos/create"
              class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
              </path>
            </svg>
            Crear Primer Contrato
            </Link>
          </div>

          <div v-else class="divide-y divide-gray-200">
            <div v-for="contrato in contratos" :key="contrato.id" class="p-6 hover:bg-gray-50 transition-colors">
              <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">

                <!-- Información Principal -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
                  <div v-if="contrato.users && contrato.users.length > 0">
                    <div class="flex items-center space-x-2 mb-1">
                      <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                      </svg>
                      <p class="text-sm text-gray-500">Arrendatario</p>
                    </div>
                    <p class="text-sm text-gray-600">{{ contrato.users[0].name }} {{ contrato.users[0].apellido }}
                    </p>
                    <p class="text-xs text-gray-500">{{ contrato.users[0].email }}</p>
                    <p class="text-xs text-gray-500">C.I.: {{ contrato.users[0].ci }}</p>
                    <p v-if="contrato.users.length > 1" class="text-xs text-blue-600 mt-1">
                      +{{ contrato.users.length - 1 }} usuario{{ contrato.users.length > 2 ? 's' : '' }} más
                    </p>
                  </div>

                  <div v-if="contrato.vehiculo">
                    <div class="flex items-center space-x-2 mb-1">
                      <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2v0a2 2 0 01-2 2H9a2 2 0 01-2-2v0a2 2 0 01-2-2V9a2 2 0 012-2h2">
                        </path>
                      </svg>
                      <p class="text-sm text-gray-500">Vehículo</p>
                    </div>
                    <p class="font-medium text-gray-900">{{ contrato.vehiculo.tipo }}</p>
                    <p class="text-sm text-gray-600">{{ contrato.vehiculo.marca }} {{ contrato.vehiculo.modelo }}</p>
                    <p class="text-xs text-gray-500">Placa: {{ contrato.vehiculo.placa }}</p>
                  </div>

                  <div>
                    <div class="flex items-center space-x-2 mb-1">
                      <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                      <p class="text-sm text-gray-500">Período</p>
                    </div>
                    <p class="font-medium text-gray-900">{{ dayjs(contrato.fecha_inicio).format('DD/MM/YYYY') }}</p>
                    <p class="text-sm text-gray-600">hasta {{ dayjs(contrato.fecha_fin).format('DD/MM/YYYY') }}</p>
                    <p class="text-xs text-gray-500">{{ calcularDuracionTotal(contrato.fecha_inicio, contrato.fecha_fin)
                    }}
                      días total</p>
                  </div>

                  <div v-if="contrato.frecuencia_pago">
                    <div class="flex items-center space-x-2 mb-1">
                      <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                        </path>
                      </svg>
                      <p class="text-sm text-gray-500">Frecuencia de Pago</p>
                    </div>
                    <p class="font-medium text-gray-900">{{ contrato.frecuencia_pago.nombre }}</p>
                    <p class="text-sm text-gray-600">Cada {{ contrato.frecuencia_pago.frecuencia_dias }} día{{
                      contrato.frecuencia_pago.frecuencia_dias !== 1 ? 's' : '' }}</p>
                  </div>

                  <div v-if="contrato.estado">
                    <div class="flex items-center space-x-2 mb-1">
                      <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                        </path>
                      </svg>
                      <p class="text-sm text-gray-500">Estado</p>
                    </div>
                    <p class="text-sm text-gray-600">{{ contrato.estado }}</p>
                  </div>
                </div>

                <!-- Acciones -->
                <div class="flex flex-col sm:flex-row gap-2 lg:ml-6">
                  <Link :href="`/contratos/${contrato.id}`"
                    class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                    </path>
                  </svg>
                  Ver Detalles
                  </Link>

                  <Link v-if="hasPermission('contratos.editar')" :href="`/contratos/${contrato.id}/edit`"
                    class="inline-flex items-center justify-center px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors text-sm font-medium">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                    </path>
                  </svg>
                  Editar
                  </Link>

                  <button v-if="hasPermission('contratos.eliminar')" @click="eliminar(contrato.id)"
                    class="inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-medium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                      </path>
                    </svg>
                    Eliminar
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>