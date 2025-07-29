<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref, watch } from 'vue';

const props = defineProps({
  frecuenciaPagos: Array,
  filters: Object,
});

const search = ref(props.filters?.search || '');

watch(search, val => {
  router.get('/frecuencia-pagos', { search: val }, { preserveState: true, replace: true });
});

const eliminar = id => {
  Swal.fire({
    title: '¿Eliminar?',
    text: 'Esta acción no se puede deshacer.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
  }).then(result => {
    if (result.isConfirmed) {
      router.delete(`/frecuencia-pagos/${id}`, {
        onSuccess: () => {
          Swal.fire('Eliminado', 'Frecuencia de pago eliminada correctamente.', 'success');
        }
      });
    }
  });
};
</script>

<template>
  <Head title="Frecuencias de Pago" />

  <AuthenticatedLayout>
    <template #header>
      <h2
        class="text-xl font-semibold leading-tight text-main"
        style="font-size: calc(1em + 0.25rem);"
      >
        Frecuencias de Pago
      </h2>
    </template>

    <div class="py-12 text-main">
      <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
        <div class="p-8 rounded-lg shadow-lg card-bg space-y-6">
          <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <h1
              class="font-bold text-main"
              style="font-size: calc(1em + 0.5rem);"
            >
              Listado de Frecuencias de Pago
            </h1>
            <Link
              href="/frecuencia-pagos/create"
              class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium shadow-md transition-colors"
              style="font-size: calc(1em - 0.075rem);"
            >
              Nuevo
            </Link>
          </div>

          <form @submit.prevent="$event.preventDefault()" class="flex gap-4 max-w-md">
            <input
              v-model="search"
              type="text"
              placeholder="Buscar por nombre..."
              class="flex-1 p-3 border rounded-lg card-bg focus:ring-2 focus:ring-blue-500 transition-colors"
              style="font-size: calc(1em - 0.075rem);"
            />
            <button
              @click.prevent="router.get('/frecuencia-pagos', { search }, { preserveState: true, replace: true })"
              class="px-6 py-3 bg-gray-800 text-white rounded-lg hover:bg-gray-700 font-medium shadow-md transition-colors"
              style="font-size: calc(1em - 0.075rem);"
            >
              Buscar
            </button>
          </form>

          <div class="overflow-x-auto">
            <table class="min-w-full text-sm border-collapse">
              <thead class="border-b" :style="{ backgroundColor: 'var(--thead-bg)' }">
                <tr>
                  <th class="px-4 py-3 text-left font-medium text-main border" style="font-size: calc(1em - 0.075rem);">ID</th>
                  <th class="px-4 py-3 text-left font-medium text-main border" style="font-size: calc(1em - 0.075rem);">Nombre</th>
                  <th class="px-4 py-3 text-left font-medium text-main border" style="font-size: calc(1em - 0.075rem);">Días</th>
                  <th class="px-4 py-3 text-left font-medium text-main border" style="font-size: calc(1em - 0.075rem);">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr
                  v-for="f in frecuenciaPagos"
                  :key="f.id"
                  class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                >
                  <td class="px-4 py-3 border" style="font-size: inherit;">{{ f.id }}</td>
                  <td class="px-4 py-3 border" style="font-size: inherit;">{{ f.nombre }}</td>
                  <td class="px-4 py-3 border" style="font-size: inherit;">{{ f.frecuencia_dias }}</td>
                  <td class="px-4 py-3 border">
                    <div class="flex flex-wrap gap-3">
                      <Link
                        :href="`/frecuencia-pagos/${f.id}/edit`"
                        class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 font-medium transition-colors"
                        style="font-size: calc(1em - 0.075rem);"
                      >
                        Editar
                      </Link>
                      <button
                        @click="eliminar(f.id)"
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 font-medium transition-colors"
                        style="font-size: calc(1em - 0.075rem);"
                      >
                        Eliminar
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="!frecuenciaPagos.length">
                  <td colspan="4" class="py-8 text-center opacity-70" style="font-size: calc(1em - 0.1rem);">
                    No hay registros
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
