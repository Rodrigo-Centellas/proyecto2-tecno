<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  vehiculos: Array,
});
</script>

<template>
  <Head title="Ver Vehículos" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-main">Vehículos Disponibles</h2>
    </template>

    <div class="py-12 text-main">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Título de la sección -->
        <div class="mb-8 text-center">
          <h1 class="font-bold text-main" style="font-size: calc(1em + 0.5rem);">
            Nuestra Flota de Vehículos
          </h1>
          <p class="text-main opacity-70 mt-2" style="font-size: calc(1em - 0.125rem);">
            Selecciona el vehículo perfecto para tu próximo viaje
          </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
          <div
            v-for="v in vehiculos"
            :key="v.id"
            class="p-6 rounded-lg shadow-lg card-bg hover:shadow-xl transition-shadow duration-300"
          >
            <!-- Imagen -->
            <div class="relative h-48 bg-gray-200 overflow-hidden rounded-lg mb-4">
              <img
                v-if="v.url_imagen"
                :src="v.url_imagen"
                alt="Foto del vehículo"
                class="w-full h-full object-cover"
              />
              <div
                v-else
                class="w-full h-full bg-gradient-to-br from-gray-300 to-gray-400 flex items-center justify-center"
              >
                <svg
                  class="w-12 h-12 text-gray-500"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                  />
                </svg>
              </div>
              <div class="absolute top-3 right-3">
                <span
                  :class="[
                    'px-3 py-1 font-semibold rounded-full shadow-md',
                    {
                      'bg-green-100 text-green-800': v.estado === 'Disponible',
                      'bg-yellow-100 text-yellow-800': v.estado === 'Reservado',
                      'bg-red-100 text-red-800': v.estado === 'En mantenimiento',
                      'bg-gray-100 text-gray-800': v.estado === 'Retirado',
                    },
                  ]"
                  style="font-size: calc(1em - 0.15rem);"
                >
                  {{ v.estado }}
                </span>
              </div>
            </div>

            <!-- Información del vehículo -->
            <div class="space-y-4">
              <!-- Título y ID -->
              <div class="flex items-center justify-between border-b border-opacity-20 border-gray-300 pb-3">
                <h3 class="font-bold text-main" style="font-size: calc(1em + 0.125rem);">
                  {{ v.tipo }}
                </h3>
                <span
                  class="px-3 py-1 font-medium rounded-lg bg-blue-100 text-blue-800 shadow-sm"
                  style="font-size: calc(1em - 0.15rem);"
                >
                  ID: {{ v.id }}
                </span>
              </div>

              <!-- Detalles del vehículo -->
              <div class="space-y-3">
                <div class="flex justify-between items-center">
                  <span class="font-semibold text-main" style="font-size: calc(1em - 0.075rem);">Marca:</span>
                  <span class="text-main opacity-80" style="font-size: calc(1em - 0.075rem);">{{ v.marca }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="font-semibold text-main" style="font-size: calc(1em - 0.075rem);">Modelo:</span>
                  <span class="text-main opacity-80" style="font-size: calc(1em - 0.075rem);">{{ v.modelo }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="font-semibold text-main" style="font-size: calc(1em - 0.075rem);">Placa:</span>
                  <span class="text-main opacity-80" style="font-size: calc(1em - 0.075rem);">{{ v.placa }}</span>
                </div>
              </div>

              <!-- Precios -->
              <div class="card-bg border border-opacity-20 border-gray-300 rounded-lg p-4 space-y-3">
                <h4 class="font-semibold text-main mb-2" style="font-size: calc(1em + 0.025rem);">
                  Información de Precios
                </h4>
                <div class="flex justify-between items-center">
                  <span class="text-main opacity-70" style="font-size: calc(1em - 0.125rem);">Precio por día:</span>
                  <span class="text-green-600 font-bold" style="font-size: calc(1em + 0.125rem);">${{ v.precio_dia }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-main opacity-70" style="font-size: calc(1em - 0.125rem);">Garantía requerida:</span>
                  <span class="text-blue-600 font-semibold" style="font-size: inherit;">${{ v.monto_garantia }}</span>
                </div>
              </div>

              <!-- Acciones -->
              <div class="space-y-3 pt-2">
                <button
                  @click="$inertia.get(route('reservas.create'), { vehiculo_id: v.id })"
                  :disabled="v.estado !== 'Disponible'"
                  :class="[
                    'w-full px-6 py-3 rounded-lg text-white font-medium flex items-center justify-center space-x-2 transition-colors shadow-md hover:shadow-lg',
                    v.estado === 'Disponible'
                      ? 'bg-blue-600 hover:bg-blue-700'
                      : 'bg-gray-400 cursor-not-allowed opacity-50',
                  ]"
                  style="font-size: inherit;"
                >
                  <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                  </svg>
                  <span>Hacer Reserva</span>
                </button>

                <button
                  @click="$inertia.get(route('contratos.create'), { vehiculo_id: v.id })"
                  :disabled="v.estado !== 'Disponible'"
                  :class="[
                    'w-full px-6 py-3 rounded-lg text-white font-medium flex items-center justify-center space-x-2 transition-colors shadow-md hover:shadow-lg',
                    v.estado === 'Disponible'
                      ? 'bg-green-600 hover:bg-green-700'
                      : 'bg-gray-400 cursor-not-allowed opacity-50',
                  ]"
                  style="font-size: inherit;"
                >
                  <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                    />
                  </svg>
                  <span>Alquilar Ahora</span>
                </button>
              </div>

              <!-- Estado adicional -->
              <div class="text-center pt-3 border-t border-opacity-20 border-gray-300">
                <div v-if="v.estado === 'Disponible'" class="text-green-600 font-medium" style="font-size: calc(1em - 0.125rem);">
                  ✅ Disponible para reserva o alquiler inmediato
                </div>
                <div v-else class="text-main opacity-60" style="font-size: calc(1em - 0.125rem);">
                  <span v-if="v.estado === 'Reservado'">⏰ Vehículo con reserva activa</span>
                  <span v-else-if="v.estado === 'En mantenimiento'">🔧 Vehículo en mantenimiento</span>
                  <span v-else-if="v.estado === 'Retirado'">❌ Vehículo fuera de servicio</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Estado sin vehículos -->
        <div v-if="!vehiculos || vehiculos.length === 0" class="text-center py-16">
          <div class="card-bg rounded-lg p-12 shadow-lg">
            <svg
              class="w-20 h-20 mx-auto mb-6 text-main opacity-30"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
              />
            </svg>
            <h3 class="font-bold text-main mb-3" style="font-size: calc(1em + 0.25rem);">
              No hay vehículos disponibles
            </h3>
            <p class="text-main opacity-70" style="font-size: calc(1em - 0.125rem);">
              En este momento no tenemos vehículos en nuestra flota. Vuelve más tarde para ver nuestra selección actualizada.
            </p>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>