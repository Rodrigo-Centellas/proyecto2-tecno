<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Swal from 'sweetalert2';
import { useBaseUrl } from '@/composables/useBaseUrl';
const { url } = useBaseUrl();
// Props
const props = defineProps({
  reserva: Object,
});

// Inertia form
const form = useForm({
  estado: props.reserva.estado,
});

// Permisos
const { props: pageProps } = usePage();
const hasPermission = computed(() => permission =>
  pageProps.auth.user.roles.some(r => r.name === 'Administrador') ||
  pageProps.auth.user.permissions.includes(permission)
);

// Opciones de estado
const estados = [
  'Cancelada',
];

// Enviar
const submit = () => {
  form.put(route('reservas.update', props.reserva.id), {
    onSuccess: () => {
      Swal.fire('¡Listo!', 'Estado actualizado.', 'success');
      router.visit(route('reservas.index'));
    }
  });
};
</script>

<template>
  <Head :title="`Editar Reserva #${reserva.id}`" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight">Editar Estado de Reserva</h2>
    </template>

    <div class="py-12">
      <div class="max-w-md mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-lg p-6 space-y-6">
          
          <!-- Info básica -->
          <div>
            <p><strong>ID Reserva:</strong> #{{ reserva.id }}</p>
            <p><strong>Vehículo:</strong> {{ reserva.vehiculo.placa }}</p>
            <p><strong>Usuario:</strong> {{ reserva.user.name }} {{ reserva.user.apellido }}</p>
            <p><strong>fecha reserva:</strong> {{ reserva.fecha }}</p>
            <p><strong>fecha creacion:</strong> {{ reserva.created_at }}</p>
          </div>

          <!-- Selección de estado -->
          <div>
            <label class="block font-medium mb-1">Estado</label>
            <select
              v-model="form.estado"
              class="w-full border rounded px-3 py-2"
            >
              <option v-for="e in estados" :key="e" :value="e">
                {{ e }}
              </option>
            </select>
            <div v-if="form.errors.estado" class="text-red-600 text-sm mt-1">
              {{ form.errors.estado }}
            </div>
          </div>

          <!-- Botones -->
          <div class="flex justify-end space-x-4">
            <inertia-link
              :href="url('/reservas')"
              class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600"
            >
              Cancelar
            </inertia-link>
            <button
              v-if="hasPermission('reservas.editar')"
              @click="submit"
              :disabled="form.processing"
              class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
            >
              Guardar
            </button>
          </div>

        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
