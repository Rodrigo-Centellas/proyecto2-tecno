<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';

const apellido = ref('');
const ci = ref('');
const domicilio = ref('');
const nombre = ref('');
const telefono = ref('');

const enviar = () => {
  router.post('/garantes', {
    apellido: apellido.value,
    ci: ci.value,
    domicilio: domicilio.value,
    nombre: nombre.value,
    telefono: telefono.value,
  }, {
    onSuccess: () => {
      Swal.fire({
        title: '¡Garante creado!',
        icon: 'success',
        confirmButtonText: 'Aceptar',
      }).then(() => router.visit('/garantes'));
    },
    onError: () => {
      Swal.fire({
        title: 'Error',
        text: 'Hubo un problema al guardar el garante.',
        icon: 'error',
        confirmButtonText: 'Cerrar',
      });
    }
  });
};
</script>

<template>
  <Head title="Crear Garante" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">Crear Garante</h2>
    </template>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white shadow-sm sm:rounded-lg p-6">
        <form @submit.prevent="enviar" class="space-y-4">
          <div>
            <label>Apellido</label>
            <input v-model="apellido" class="w-full border p-2 rounded" />
          </div>
          <div>
            <label>Nombre</label>
            <input v-model="nombre" class="w-full border p-2 rounded" />
          </div>
          <div>
            <label>CI</label>
            <input v-model="ci" type="number" class="w-full border p-2 rounded" />
          </div>
          <div>
            <label>Domicilio</label>
            <input v-model="domicilio" class="w-full border p-2 rounded" />
          </div>
          <div>
            <label>Teléfono</label>
            <input v-model="telefono" type="number" class="w-full border p-2 rounded" />
          </div>
          <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Guardar</button>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
