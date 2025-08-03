<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
  registro: Object,
});

const $page = usePage();

const formatearFecha = (f) => {
  if (!f) return 'N/A';
  return new Date(f).toLocaleDateString('es-BO', {
    year: 'numeric', month: 'long', day: 'numeric'
  });
};
</script>

<template>
  <Head :title="`Registro #${registro.id}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-main">
          Detalle del Registro #{{ registro.id }}
        </h2>
        <button
          @click="router.visit('/registro-mantenimientos')"
          class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors"
        >
          ← Volver
        </button>
      </div>
    </template>

    <div class="py-12 text-main">
      <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
        <div class="p-6 rounded-lg shadow-lg card-bg">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Vehículo -->
            <div class="space-y-2">
              <h3 class="font-semibold text-main">🚗 Vehículo</h3>
              <p><strong>Tipo:</strong> {{ registro.vehiculo?.tipo || 'N/A' }}</p>
              <p><strong>Placa:</strong> {{ registro.vehiculo?.placa || 'N/A' }}</p>
              <p><strong>Marca / Modelo:</strong> {{ registro.vehiculo?.marca || '' }} {{ registro.vehiculo?.modelo || '' }}</p>
            </div>

            <!-- Mantenimiento -->
            <div class="space-y-2">
              <h3 class="font-semibold text-main">🔧 Mantenimiento</h3>
              <p><strong>Nombre:</strong> {{ registro.mantenimiento?.nombre || 'N/A' }}</p>
              <p><strong>Fecha:</strong> {{ formatearFecha(registro.fecha) }}</p>
              <p><strong>Monto:</strong> Bs {{ registro.monto ? Number(registro.monto).toFixed(2) : '0.00' }}</p>
            </div>
          </div>

          <div class="mt-6">
            <h4 class="font-semibold text-main mb-2">Metadatos</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p><strong>ID:</strong> #{{ registro.id }}</p>
                <p><strong>Creado:</strong> {{ new Date(registro.created_at).toLocaleString('es-BO') }}</p>
              </div>
              <div>
                <p><strong>Actualizado:</strong> {{ new Date(registro.updated_at).toLocaleString('es-BO') }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
