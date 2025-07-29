<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref } from 'vue';

const props = defineProps({
  users: Array,
  filters: Object,
});

const search = ref(props.filters?.search || '');
const processing = ref(false);

const eliminar = (id) => {
  Swal.fire({
    title: '¿Estás seguro?',
    text: 'Esta acción no se puede deshacer.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
  }).then(result => {
    if (result.isConfirmed) {
      processing.value = true;
      router.delete(`/users/${id}`, {
        onSuccess: () => {
          Swal.fire('Eliminado', 'Usuario eliminado correctamente.', 'success');
        },
        onFinish: () => {
          processing.value = false;
        }
      });
    }
  });
};

const buscar = () => {
  router.get('/users', { search: search.value }, { preserveState: true, replace: true });
};
</script>

<template>
  <Head title="Usuarios" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-main">
        Usuarios
      </h2>
    </template>

    <div class="py-12 text-main">
      <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
        <div class="p-8 rounded-lg shadow-lg card-bg">
          <!-- Título y botón Nuevo -->
          <div class="flex flex-col sm:flex-row justify-between items-center mb-6">
            <h1 class="font-bold text-main" style="font-size: calc(1em + 0.5rem);">
              Lista de Usuarios
            </h1>
            <a
              href="/users/create"
              class="mt-4 sm:mt-0 px-6 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors font-medium shadow-md"
              style="font-size: inherit;"
            >
              Nuevo Usuario
            </a>
          </div>

          <!-- Buscador -->
          <form @submit.prevent="buscar" class="flex flex-col sm:flex-row items-center gap-4 mb-6">
            <input
              v-model="search"
              type="text"
              placeholder="Buscar por nombre, apellido o email"
              class="w-full sm:flex-1 p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
              style="font-size: inherit;"
            />
            <button
              type="submit"
              class="px-6 py-3 text-white bg-gray-800 rounded-lg hover:bg-gray-700 transition-colors font-medium shadow-md"
              style="font-size: inherit;"
            >
              Buscar
            </button>
          </form>

          <!-- Tabla -->
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
                    Email
                  </th>
                  <th class="px-4 py-3 text-left font-medium border text-main" style="font-size: calc(1em - 0.075rem);">
                    Roles
                  </th>
                  <th class="px-4 py-3 text-left font-medium border text-main" style="font-size: calc(1em - 0.075rem);">
                    Acciones
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                  <td class="px-4 py-3 border" style="font-size: inherit;">
                    {{ user.id }}
                  </td>
                  <td class="px-4 py-3 border" style="font-size: inherit;">
                    {{ user.name }} {{ user.apellido }}
                  </td>
                  <td class="px-4 py-3 border" style="font-size: inherit;">
                    {{ user.email }}
                  </td>
                  <td class="px-4 py-3 border" style="font-size: inherit;">
                    <ul class="list-disc list-inside">
                      <li v-for="role in user.roles" :key="role.id" style="font-size: inherit;">
                        {{ role.name }}
                      </li>
                    </ul>
                  </td>
                  <td class="px-4 py-3 border">
                    <div class="flex flex-wrap items-center gap-3">
                      <!-- Ver -->
                      <a
                        :href="`/users/${user.id}`"
                        class="inline-flex items-center px-4 py-2 text-xs font-medium bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                        style="font-size: calc(0.875em);"
                      >
                        Ver
                      </a>
                      <!-- Editar -->
                      <a
                        :href="`/users/${user.id}/edit`"
                        class="inline-flex items-center px-4 py-2 text-xs font-medium bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors"
                        style="font-size: calc(0.875em);"
                      >
                        Editar
                      </a>
                      <!-- Eliminar -->
                      <button
                        @click="eliminar(user.id)"
                        :disabled="processing"
                        class="inline-flex items-center px-4 py-2 text-xs font-medium bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        style="font-size: calc(0.875em);"
                      >
                        Eliminar
                      </button>
                    </div>
                  </td>
                </tr>

                <tr v-if="users.length === 0">
                  <td colspan="5" class="py-6 text-center text-main opacity-70" style="font-size: calc(1em - 0.1rem);">
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
