<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref } from 'vue';

const nombre = ref('');
const frecuencia_dias = ref('');

const enviar = () => {
  router.post('/frecuencia-pagos', {
    nombre: nombre.value,
    frecuencia_dias: frecuencia_dias.value,
  }, {
    onSuccess: () => {
      Swal.fire({
        title: '¡Creado!',
        text: 'La frecuencia de pago fue registrada correctamente.',
        icon: 'success',
        confirmButtonText: 'Aceptar',
      }).then(() => {
        router.visit('/frecuencia-pagos');
      });
    },
    onError: () => {
      Swal.fire({
        title: 'Error',
        text: 'Hubo un problema al guardar la frecuencia de pago.',
        icon: 'error',
        confirmButtonText: 'Cerrar',
      });
    }
  });
};
</script>

<template>
  <Head title="Crear Frecuencia de Pago" />

  <AuthenticatedLayout>
    <template #header>
      <h2
        class="text-xl font-semibold leading-tight text-main"
        style="font-size: calc(1em + 0.25rem);"
      >
        Crear Frecuencia de Pago
      </h2>
    </template>

    <div class="py-12 text-main">
      <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
        <div class="p-8 rounded-lg shadow-lg card-bg space-y-6">
          <h1
            class="font-bold text-main"
            style="font-size: calc(1em + 0.5rem);"
          >
            Nueva Frecuencia de Pago
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
              class="w-full py-3 text-white bg-green-600 rounded-lg hover:bg-green-700 font-medium shadow-md transition-colors"
              style="font-size: calc(1em - 0.075rem);"
            >
              Guardar
            </button>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
