<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
  role: Object,
  permissions: Array,
});

const name = ref(props.role.name);
const permisosSeleccionados = ref(props.role.permissions.map(p => p.id));
const processing = ref(false);

const enviar = () => {
  processing.value = true;
  router.put(`/roles/${props.role.id}`, {
    name: name.value,
    permissions: permisosSeleccionados.value,
  }, {
    onSuccess: () => {
      Swal.fire({
        title: 'Rol actualizado',
        text: 'El rol ha sido modificado correctamente.',
        icon: 'success',
      }).then(() => {
        router.visit('/roles');
      });
    },
    onFinish: () => {
      processing.value = false;
    },
  });
};
</script>

<template>
  <Head title="Editar Rol" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-main">Editar Rol</h2>
    </template>

    <div class="py-12 text-main">
      <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
        <div class="p-8 rounded-lg shadow-lg card-bg">
          <!-- Título -->
          <div class="mb-6 text-center">
            <h1 class="font-bold text-main" style="font-size: calc(1em + 0.5rem);">
              Modificar Rol
            </h1>
            <p class="text-main opacity-70 mt-1" style="font-size: calc(1em - 0.125rem);">
              Actualice el nombre y permisos del rol
            </p>
          </div>

          <form @submit.prevent="enviar" class="space-y-6">
            <!-- Nombre del Rol -->
            <div>
              <label
                class="block font-semibold text-main mb-2"
                style="font-size: calc(1em - 0.075rem);"
              >
                Nombre del Rol *
              </label>
              <input
                v-model="name"
                type="text"
                required
                class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                style="font-size: inherit;"
              />
            </div>

            <!-- Permisos -->
            <div>
              <label
                class="block font-semibold text-main mb-4"
                style="font-size: calc(1em + 0.125rem);"
              >
                Permisos *
              </label>
              <p class="text-main opacity-70 mb-4" style="font-size: calc(1em - 0.125rem);">
                Seleccione los permisos asociados a este rol
              </p>
              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div
                  v-for="permiso in permissions"
                  :key="permiso.id"
                  class="card-bg border border-opacity-20 border-gray-300 rounded-lg p-4 hover:shadow-md transition-shadow"
                >
                  <label class="inline-flex items-center space-x-2 cursor-pointer">
                    <input
                      type="checkbox"
                      :value="permiso.id"
                      v-model="permisosSeleccionados"
                      class="rounded focus:ring-2 focus:ring-blue-500"
                    />
                    <span class="text-main" style="font-size: inherit;">
                      {{ permiso.name }}
                    </span>
                  </label>
                </div>
              </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex flex-col sm:flex-row justify-center gap-4 pt-4">
              <button
                type="submit"
                :disabled="processing"
                class="px-8 py-3 text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors font-medium shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                style="font-size: inherit;"
              >
                <span v-if="processing">Guardando...</span>
                <span v-else>Guardar Cambios</span>
              </button>
              <a
                href="/roles"
                class="px-8 py-3 text-white bg-gray-500 rounded-lg hover:bg-gray-600 transition-colors font-medium shadow-md hover:shadow-lg text-center"
                style="font-size: inherit;"
              >
                Cancelar
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
