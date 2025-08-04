<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import dayjs from 'dayjs'
import 'dayjs/locale/es'     // opcional: para nombres de mes en español

dayjs.locale('es')
import Swal from 'sweetalert2';

const $page = usePage();

const hasPermission = computed(() => (permission) => {
  const user = $page.props.auth.user;
  if (user?.roles?.some(role => role.name === 'Administrador')) return true;
  return user?.permissions?.includes(permission) || false;
});

const props = defineProps({
  reservas: Array,
  filters: Object,
});

const search = ref(props.filters?.search || '');

watch(search, (value) => {
  router.get(route('reservas.index'), { search: value }, {
    preserveState: true,
    replace: true,
  });
});

const eliminar = (id) => {
  Swal.fire({
    title: '¿Eliminar reserva?',
    text: 'Esta acción no se puede deshacer.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(route('reservas.destroy', id));
    }
  });
};
</script>

<template>

  <Head title="Reservas" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-main">Reservas</h2>
    </template>

    <div class="py-12 text-main">
      <div class="mx-auto max-w-6xl sm:px-6 lg:px-8">
        <div class="p-8 rounded-lg shadow-lg card-bg">
          <!-- Encabezado -->
          <div class="flex flex-col sm:flex-row justify-between items-center mb-6">
            <h1 class="font-bold text-main" style="font-size: calc(1em + 0.5rem);">
              Lista de Reservas
            </h1>
            <a v-if="hasPermission('reservas.crear')" :href="route('reservas.create')"
              class="mt-4 sm:mt-0 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium shadow-md"
              style="font-size: inherit;">
              Nueva Reserva
            </a>
          </div>

          <!-- Filtro -->
          <div class="mb-6 max-w-md">
            <input v-model="search" type="text" placeholder="Buscar por ID, cliente o vehículo..."
              class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
              style="font-size: inherit;" />
          </div>

          <!-- Tabla -->
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm border-collapse">
              <thead class="border-b" :style="{ backgroundColor: 'var(--thead-bg)' }">
                <tr>
                  <th class="px-4 py-3 text-left font-medium text-main" style="font-size: calc(1em - 0.075rem);">ID</th>
                  <th class="px-4 py-3 text-left font-medium text-main" style="font-size: calc(1em - 0.075rem);">
                    Vehículo
                  </th>
                  <th class="px-4 py-3 text-left font-medium text-main" style="font-size: calc(1em - 0.075rem);">Usuario
                  </th>
                  <th class="px-4 py-3 text-left font-medium text-main" style="font-size: calc(1em - 0.075rem);">Fecha
                    Creacion</th>
                  <th class="px-4 py-3 text-left font-medium text-main" style="font-size: calc(1em - 0.075rem);">Fecha
                    Actualizacion</th>
                  <th class="px-4 py-3 text-left font-medium text-main" style="font-size: calc(1em - 0.075rem);">Estado
                  </th>
                  <th class="px-4 py-3 text-left font-medium text-main" style="font-size: calc(1em - 0.075rem);">
                    Acciones
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="r in reservas" :key="r.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                  <td class="px-4 py-3 border" style="font-size: inherit;">{{ r.id }}</td>
                  <td class="px-4 py-3 border" style="font-size: inherit;">{{ r.vehiculo?.placa || 'N/A' }}</td>
                  <td class="px-4 py-3 border" style="font-size: inherit;">{{ r.user?.name + ' ' + r.user.apellido ||
                    'N/A'
                    }}</td>
                  <td class="px-4 py-3 border">
                    {{ dayjs(r.created_at).format('DD/MM/YYYY HH:mm') }}
                  </td>
                  <td class="px-4 py-3 border">
                    {{ dayjs(r.updated_at).format('DD/MM/YYYY HH:mm') }}
                  </td>
                  <td class="px-4 py-3 border" style="font-size: inherit;">
                    <span :class="{
                      'text-green-600 font-semibold': r.estado === 'Confirmada',
                      'text-yellow-500 font-semibold': r.estado === 'Pendiente',
                      'text-red-600 font-semibold': r.estado === 'Cancelada',
                      'text-blue-600 font-semibold': r.estado === 'Completada',
                    }" style="font-size: inherit;">
                      {{ r.estado }}
                    </span>
                  </td>
                  <td class="px-4 py-3 border">
                    <div class="flex flex-wrap items-center gap-3">
                      <a :href="route('reservas.show', r.id)"
                        class="inline-flex items-center px-3 py-1 text-xs font-medium bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                        style="font-size: calc(0.875em);">
                        Ver
                      </a>
                      <button v-if="hasPermission('reservas.eliminar')" @click="eliminar(r.id)"
                        class="inline-flex items-center px-3 py-1 text-xs font-medium bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors"
                        style="font-size: calc(0.875em);">
                        Eliminar
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="reservas.length === 0">
                  <td colspan="6" class="py-6 text-center text-main opacity-70" style="font-size: calc(1em - 0.1rem);">
                    No se encontraron reservas.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
