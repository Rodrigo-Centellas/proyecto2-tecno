<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import dayjs from 'dayjs';
import 'dayjs/locale/es';
import Swal from 'sweetalert2';

dayjs.locale('es');

const $page = usePage();

const hasPermission = computed(() => (permission) => {
  const user = $page.props.auth.user;
  if (user?.roles?.some(r => r.name === 'Administrador')) return true;
  return user?.permissions?.includes(permission) || false;
});

const props = defineProps({
  reservas: Array,
  filters: Object,
});

const search = ref(props.filters?.search || '');

watch(search, val => {
  router.get(route('reservas.index'), { search: val }, { preserveState: true, replace: true });
});

const eliminar = id => {
  Swal.fire({
    title: '¿Eliminar reserva?',
    text: 'Esta acción no se puede deshacer.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
  }).then(result => {
    if (result.isConfirmed) {
      router.delete(route('reservas.destroy', id), {
        onSuccess: () => Swal.fire('¡Listo!', 'Reserva eliminada.', 'success')
      });
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
            <h1 class="font-bold text-main text-2xl">Lista de Reservas</h1>
            <Link
              v-if="hasPermission('reservas.crear')"
              :href="route('reservas.create')"
              class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
            >
              Nueva Reserva
            </Link>
          </div>

          <!-- Buscador -->
          <div class="mb-6 max-w-md">
            <input
              v-model="search"
              type="text"
              placeholder="Buscar por ID, cliente o vehículo..."
              class="w-full p-3 border rounded-lg card-bg focus:ring-2 focus:ring-blue-500 transition-colors"
            />
          </div>

          <!-- Tabla -->
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm border-collapse">
              <thead class="border-b bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left font-medium">ID</th>
                  <th class="px-4 py-3 text-left font-medium">Vehículo</th>
                  <th class="px-4 py-3 text-left font-medium">Usuario</th>
                  <th class="px-4 py-3 text-left font-medium">Creación</th>
                  <th class="px-4 py-3 text-left font-medium">Actualización</th>
                  <th class="px-4 py-3 text-left font-medium">Estado</th>
                  <th class="px-4 py-3 text-left font-medium">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="r in reservas"
                  :key="r.id"
                  class="hover:bg-gray-50 transition-colors"
                >
                  <td class="px-4 py-3 border">{{ r.id }}</td>
                  <td class="px-4 py-3 border">{{ r.vehiculo?.placa || 'N/A' }}</td>
                  <td class="px-4 py-3 border">
                    {{ r.user?.name }} {{ r.user?.apellido }}
                  </td>
                  <td class="px-4 py-3 border">
                    {{ dayjs(r.created_at).format('DD/MM/YYYY HH:mm') }}
                  </td>
                  <td class="px-4 py-3 border">
                    {{ dayjs(r.updated_at).format('DD/MM/YYYY HH:mm') }}
                  </td>
                  <td class="px-4 py-3 border">
                    <span
                      :class="{
                        'text-green-600': r.estado === 'Confirmada',
                        'text-yellow-500': r.estado === 'Pendiente',
                        'text-red-600': r.estado === 'Cancelada',
                        'text-blue-600': r.estado === 'Completada',
                      }"
                      class="font-semibold"
                    >
                      {{ r.estado }}
                    </span>
                  </td>
                  <td class="px-4 py-3 border">
                    <div class="flex flex-wrap items-center gap-2">
                      <Link
                        :href="route('reservas.show', r.id)"
                        class="px-3 py-1 bg-blue-500 text-white text-xs rounded-lg hover:bg-blue-600 transition-colors"
                      >
                        Ver
                      </Link>
                      <Link
                        v-if="hasPermission('reservas.editar')"
                        :href="route('reservas.edit', r.id)"
                        class="px-3 py-1 bg-yellow-500 text-white text-xs rounded-lg hover:bg-yellow-600 transition-colors"
                      >
                        Editar
                      </Link>
                      <button
                        v-if="hasPermission('reservas.eliminar')"
                        @click="eliminar(r.id)"
                        class="px-3 py-1 bg-red-500 text-white text-xs rounded-lg hover:bg-red-600 transition-colors"
                      >
                        Eliminar
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="!reservas.length">
                  <td colspan="7" class="py-6 text-center text-gray-500">
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
