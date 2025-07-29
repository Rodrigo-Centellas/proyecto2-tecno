<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref, watch } from 'vue';

const props = defineProps({
  mantenimiento: Object,
});

const nombre = ref(props.mantenimiento.nombre);
const descripcion = ref(props.mantenimiento.descripcion);

watch(() => props.mantenimiento, newVal => {
  nombre.value = newVal.nombre;
  descripcion.value = newVal.descripcion;
});

const actualizar = () => {
  router.put(`/mantenimientos/${props.mantenimiento.id}`, {
    nombre: nombre.value,
    descripcion: descripcion.value,
  }, {
    onSuccess: () => {
      Swal.fire({
        title: '¡Actualizado!',
        text: 'El mantenimiento fue modificado correctamente.',
        icon: 'success',
        confirmButtonText: 'Aceptar',
      }).then(() => {
        router.visit('/mantenimientos');
      });
    },
    onError: () => {
      Swal.fire({
        title: 'Error',
        text: 'Ocurrió un error al actualizar el mantenimiento.',
        icon: 'error',
        confirmButtonText: 'Cerrar',
      });
    }
  });
};
</script>

<template>
  <Head :title="`Editar Mantenimiento #${props.mantenimiento.id}`" />

  <AuthenticatedLayout>
    <template #header>
      <h2
        class="text-xl font-semibold leading-tight text-main"
        style="font-size: calc(1em + 0.25rem);"
      >
        Editar Mantenimiento
      </h2>
    </template>

    <div class="py-12 text-main">
      <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
        <div class="p-8 rounded-lg shadow-lg card-bg space-y-6">
          <h1
            class="font-bold text-main"
            style="font-size: calc(1em + 0.5rem);"
          >
            Editar Mantenimiento #{{ props.mantenimiento.id }}
          </h1>

          <form @submit.prevent="actualizar" class="space-y-4">
            <div>
              <label
                class="block font-semibold text-main mb-2"
                style="font-size: calc(1em - 0.075rem);"
              >
                Nombre *
              </label>
              <input
                v-model="nombre"
                type="text"
                required
                class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 transition-colors"
                style="font-size: inherit;"
              />
            </div>

            <div>
              <label
                class="block font-semibold text-main mb-2"
                style="font-size: calc(1em - 0.075rem);"
              >
                Descripción *
              </label>
              <input
                v-model="descripcion"
                type="text"
                required
                class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 transition-colors"
                style="font-size: inherit;"
              />
            </div>

            <button
              type="submit"
              class="w-full py-3 text-white bg-blue-600 rounded-lg hover:bg-blue-700 font-medium shadow-md transition-colors"
              style="font-size: calc(1em - 0.075rem);"
            >
              Actualizar
            </button>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
