<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref, watch } from 'vue';

const props = defineProps({
  frecuenciaPago: Object,
});

const nombre = ref(props.frecuenciaPago.nombre);
const frecuencia_dias = ref(props.frecuenciaPago.frecuencia_dias);

watch(() => props.frecuenciaPago, newVal => {
  nombre.value = newVal.nombre;
  frecuencia_dias.value = newVal.frecuencia_dias;
});

const enviar = () => {
  router.put(`/frecuencia-pagos/${props.frecuenciaPago.id}`, {
    nombre: nombre.value,
    frecuencia_dias: frecuencia_dias.value,
  }, {
    onSuccess: () => {
      Swal.fire({
        title: '¡Actualizado!',
        text: 'La frecuencia de pago fue actualizada correctamente.',
        icon: 'success',
        confirmButtonText: 'Aceptar',
      }).then(() => {
        router.visit('/frecuencia-pagos');
      });
    },
    onError: () => {
      Swal.fire({
        title: 'Error',
        text: 'Hubo un problema al actualizar la frecuencia de pago.',
        icon: 'error',
        confirmButtonText: 'Cerrar',
      });
    }
  });
};
</script>

<template>
  <Head :title="`Editar Frecuencia de Pago - ${props.frecuenciaPago.nombre}`" />

  <AuthenticatedLayout>
    <template #header>
      <h2
        class="text-xl font-semibold leading-tight text-main"
        style="font-size: calc(1em + 0.25rem);"
      >
        Editar Frecuencia de Pago
      </h2>
    </template>

    <div class="py-12 text-main">
      <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
        <div class="p-8 rounded-lg shadow-lg card-bg space-y-6">
          <h1
            class="font-bold text-main"
            style="font-size: calc(1em + 0.5rem);"
          >
            Editar Frecuencia de Pago
          </h1>

          <form @submit.prevent="enviar" class="space-y-4">
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
                Días de Frecuencia *
              </label>
              <input
                v-model="frecuencia_dias"
                type="number"
                min="1"
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
