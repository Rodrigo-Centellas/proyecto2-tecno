<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref } from 'vue';

const props = defineProps({
  roles: Array,
});

const processing = ref(false);

const eliminar = (id) => {
  Swal.fire({
    title: '¿Eliminar?',
    text: 'Esta acción no se puede deshacer.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
  }).then((result) => {
    if (result.isConfirmed) {
      processing.value = true;
      router.delete(`/roles/${id}`, {
        onSuccess: () => {
          Swal.fire('Eliminado', 'Rol eliminado correctamente.', 'success');
        },
        onFinish: () => {
          processing.value = false;
        },
      });
    }
  });
};
</script>

<template>
  <Head title="Roles" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-main">Roles</h2>
    </template>

    <div class="py-12 text-main">
      <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
        <div class="p-8 rounded-lg shadow-lg card-bg">
          <!-- Encabezado -->
          <div class="flex flex-col sm:flex-row justify-between items-center mb-6">
            <h1 class="font-bold text-main" style="font-size: calc(1em + 0.5rem);">
              Lista de Roles
            </h1>
            <a
              href="/roles/create"
              class="mt-4 sm:mt-0 px-6 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors font-medium shadow-md"
              style="font-size: inherit;"
            >
              Nuevo Rol
            </a>
          </div>

          <!-- Tabla de Roles -->
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm border-collapse">
              <thead class="border-b" :style="{ backgroundColor: 'var(--thead-bg)' }">
                <tr>
                  <th class="px-4 py-3 text-left font-medium border text-main" style="font-size: calc(1em - 0.075rem);">
                    ID
                  </th>
                  <th class="px-4 py-3 text-left font-medium border text-main" style="font-size: calc(1em - 0.075rem);">
                    Nombre
                  </th>
                  <th class="px-4 py-3 text-left font-medium border text-main" style="font-size: calc(1em - 0.075rem);">
                    Permisos
                  </th>
                  <th class="px-4 py-3 text-left font-medium border text-main" style="font-size: calc(1em - 0.075rem);">
                    Acciones
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="rol in roles"
                  :key="rol.id"
                 class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                >
                  <td class="px-4 py-3 border" style="font-size: inherit;">{{ rol.id }}</td>
                  <td class="px-4 py-3 border" style="font-size: inherit;">{{ rol.name }}</td>
                  <td class="px-4 py-3 border" style="font-size: inherit;">
                    <ul class="list-disc list-inside">
                      <li
                        v-for="permiso in rol.permissions"
                        :key="permiso.id"
                        style="font-size: inherit;"
                      >
                        {{ permiso.name }}
                      </li>
                    </ul>
                  </td>
                  <td class="px-4 py-3 border">
                    <div class="flex flex-wrap items-center gap-3">
                      <a
                        :href="`/roles/${rol.id}/edit`"
                        class="inline-flex items-center px-4 py-2 text-xs font-medium bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors"
                        style="font-size: calc(0.875em);"
                      >
                        Editar
                      </a>
                      <button
                        @click="eliminar(rol.id)"
                        :disabled="processing"
                        class="inline-flex items-center px-4 py-2 text-xs font-medium bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        style="font-size: calc(0.875em);"
                      >
                        Eliminar
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="roles.length === 0">
                  <td colspan="4" class="py-6 text-center text-main opacity-70" style="font-size: calc(1em - 0.1rem);">
                    No hay roles registrados
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
