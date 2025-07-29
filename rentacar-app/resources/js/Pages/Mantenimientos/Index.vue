<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref, watch } from 'vue';

const props = defineProps({
  mantenimientos: Array,
  filters: Object,
});

const search = ref(props.filters?.search || '');

watch(search, val => {
  router.get('/mantenimientos', { search: val }, { preserveState: true, replace: true });
});

const eliminar = id => {
  Swal.fire({
    title: '¿Estás seguro?',
    text: "¡No podrás revertir esto!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
  }).then(result => {
    if (result.isConfirmed) {
      router.delete(`/mantenimientos/${id}`, {
        onSuccess: () => {
          Swal.fire('¡Eliminado!', 'El mantenimiento ha sido eliminado.', 'success');
        }
      });
    }
  });
};
</script>

<template>
  <Head title="Mantenimientos" />

  <AuthenticatedLayout>
    <template #header>
      <h2
        class="text-xl font-semibold leading-tight text-main"
        style="font-size: calc(1em + 0.25rem);"
      >
        Mantenimientos
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
              Lista de Mantenimientos
            </h1>
            <Link
              href="/mantenimientos/create"
              class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium shadow-md transition-colors"
              style="font-size: calc(1em - 0.075rem);"
            >
              Nuevo
            </Link>
          </div>

          <form @submit.prevent="$event.preventDefault();" class="flex gap-4 max-w-md mb-6">
            <input
              v-model="search"
              type="text"
              placeholder="Buscar por nombre o descripción..."
              class="flex-1 p-3 border rounded-lg card-bg focus:ring-2 focus:ring-blue-500 transition-colors"
              style="font-size: calc(1em - 0.075rem);"
            />
            <button
              @click.prevent="router.get('/mantenimientos', { search: search }, { preserveState: true, replace: true })"
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
                  <th class="px-4 py-3 text-left font-medium text-main border" style="font-size: calc(1em - 0.075rem);">Descripción</th>
                  <th class="px-4 py-3 text-left font-medium text-main border" style="font-size: calc(1em - 0.075rem);">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr
                  v-for="m in mantenimientos"
                  :key="m.id"
                  class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                >
                  <td class="px-4 py-3 border" style="font-size: inherit;">{{ m.id }}</td>
                  <td class="px-4 py-3 border" style="font-size: inherit;">{{ m.nombre }}</td>
                  <td class="px-4 py-3 border" style="font-size: inherit;">{{ m.descripcion }}</td>
                  <td class="px-4 py-3 border">
                    <div class="flex flex-wrap gap-3">
                      <Link
                        :href="`/mantenimientos/${m.id}/edit`"
                        class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 font-medium transition-colors"
                        style="font-size: calc(1em - 0.075rem);"
                      >
                        Editar
                      </Link>
                      <button
                        @click="eliminar(m.id)"
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 font-medium transition-colors"
                        style="font-size: calc(1em - 0.075rem);"
                      >
                        Eliminar
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="!mantenimientos.length">
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
