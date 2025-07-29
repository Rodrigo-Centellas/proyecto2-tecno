<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import dayjs from 'dayjs';

const props = defineProps({
  vehiculoSeleccionado: Object,
});

const form = useForm({
  vehiculo_id: props.vehiculoSeleccionado?.id || '',
  fecha: '',
});

const showImageModal = ref(false);

const fechaMinima = computed(() =>
  dayjs().add(1, 'day').format('YYYY-MM-DD')
);

const diasReserva = computed(() => {
  const inicio = dayjs().add(1, 'day').startOf('day');
  const fin = dayjs(form.fecha);
  const dias = fin.diff(inicio, 'day') + 1;
  return dias > 0 ? dias : 0;
});

const totalPagar = computed(() =>
  diasReserva.value > 0
    ? diasReserva.value * props.vehiculoSeleccionado.precio_dia
    : 0
);

const totalFinal = computed(() => totalPagar.value);

const fechasFormateadas = computed(() => {
  if (!form.fecha) return { inicio: '', fin: '' };
  return {
    inicio: dayjs().add(1, 'day').format('DD/MM/YYYY'),
    fin: dayjs(form.fecha).format('DD/MM/YYYY'),
  };
});

const submit = () => form.post(route('reservas.store'));
const openImageModal = () => (showImageModal.value = true);
const closeImageModal = () => (showImageModal.value = false);
</script>

<template>
  <Head title="Crear Reserva" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center space-x-3">
        <div class="p-2 rounded-lg card-bg">
          <svg class="w-6 h-6 text-main" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </div>
        <h2 class="font-bold text-main" style="font-size: calc(1em + 0.5rem);">
          Nueva Reserva
        </h2>
      </div>
    </template>

    <div class="py-8 text-main">
      <div class="mx-auto max-w-4xl sm:px-6 lg:px-8 space-y-6">
        <div class="rounded-2xl shadow-lg card-bg overflow-hidden">
          <!-- Header Vehículo -->
          <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-8 py-6">
            <div class="flex justify-between items-center text-white">
              <div>
                <h3 class="font-bold" style="font-size: calc(1em + 0.5rem);">
                  {{ props.vehiculoSeleccionado?.tipo }}
                </h3>
                <p class="opacity-75">
                  {{ props.vehiculoSeleccionado?.marca }}
                  {{ props.vehiculoSeleccionado?.modelo }}
                </p>
              </div>
              <div class="text-right">
                <div class="font-bold" style="font-size: calc(1.5em);">
                  ${{ props.vehiculoSeleccionado?.precio_dia }}
                </div>
                <div class="opacity-75">por día</div>
              </div>
            </div>
          </div>

          <div class="p-8 grid md:grid-cols-2 gap-8">
            <!-- Imagen y detalles -->
            <div class="space-y-6">
              <div class="relative group rounded-xl overflow-hidden bg-gray-100 h-64">
                <img
                  v-if="props.vehiculoSeleccionado?.url_imagen"
                  :src="props.vehiculoSeleccionado.url_imagen"
                  :alt="`${props.vehiculoSeleccionado.marca} ${props.vehiculoSeleccionado.modelo}`"
                  class="w-full h-full object-cover cursor-pointer transition-transform duration-300 group-hover:scale-105"
                  @click="openImageModal"
                />
                <div
                  v-else
                  class="w-full h-full flex items-center justify-center bg-gray-200"
                >
                  <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                  </svg>
                </div>
              </div>

              <div class="bg-gray-50 rounded-xl p-6">
                <h4 class="font-semibold mb-4">Detalles del Vehículo</h4>
                <div class="grid grid-cols-2 gap-4">
                  <div class="flex items-center space-x-3">
                    <div class="p-2 rounded-lg bg-blue-100">
                      <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                      </svg>
                    </div>
                    <div>
                      <div class="text-sm opacity-75">Placa</div>
                      <div class="font-medium">{{ props.vehiculoSeleccionado?.placa }}</div>
                    </div>
                  </div>
                  <div class="flex items-center space-x-3">
                    <div class="p-2 rounded-lg bg-green-100">
                      <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>
                    </div>
                    <div>
                      <div class="text-sm opacity-75">Estado</div>
                      <div class="font-medium">{{ props.vehiculoSeleccionado?.estado }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Formulario -->
            <div class="space-y-6">
              <form @submit.prevent="submit" class="space-y-6">
                <input type="hidden" v-model="form.vehiculo_id" />

                <!-- Fecha -->
                <div class="bg-blue-50 rounded-xl p-6">
                  <label class="block font-semibold mb-2">Fecha de devolución *</label>
                  <input
                    type="date"
                    v-model="form.fecha"
                    :min="fechaMinima"
                    class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 transition-colors"
                    required
                  />
                  <p class="text-sm opacity-75 mt-2">
                    Reserva desde mañana hasta la fecha seleccionada
                  </p>
                </div>

                <!-- Período -->
                <div v-if="form.fecha" class="bg-yellow-50 rounded-xl p-6">
                  <h4 class="font-semibold mb-4">Período de Reserva</h4>
                  <div class="flex items-center justify-between">
                    <div class="text-center">
                      <div class="font-bold text-green-600" style="font-size: calc(1.25em);">
                        {{ fechasFormateadas.inicio }}
                      </div>
                      <div class="opacity-75 text-sm">Inicio</div>
                    </div>
                    <div class="flex-1 mx-4">
                      <div class="border-t-2 border-dashed border-gray-300 relative">
                        <div
                          class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-yellow-50 px-2"
                        >
                          <span class="text-sm font-medium">
                            {{ diasReserva }} día{{ diasReserva !== 1 ? 's' : '' }}
                          </span>
                        </div>
                      </div>
                    </div>
                    <div class="text-center">
                      <div class="font-bold text-red-600" style="font-size: calc(1.25em);">
                        {{ fechasFormateadas.fin }}
                      </div>
                      <div class="opacity-75 text-sm">Devolución</div>
                    </div>
                  </div>
                </div>

                <!-- Costos -->
                <div class="bg-gray-50 rounded-xl p-6">
                  <h4 class="font-semibold mb-4">Resumen de Costos</h4>
                  <div class="space-y-3">
                    <div class="flex justify-between">
                      <span class="opacity-75">
                        Alquiler ({{ diasReserva }}× ${{ props.vehiculoSeleccionado.precio_dia }})
                      </span>
                      <span class="font-medium">${{ totalPagar }}</span>
                    </div>
                    <div class="border-t pt-3">
                      <div class="flex justify-between font-bold">
                        <span>Total a Pagar</span>
                        <span class="text-green-600">${{ totalFinal }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Info Importante -->
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                  <div class="flex items-start space-x-3">
                    <svg
                      class="w-6 h-6 text-amber-600 flex-shrink-0"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                      <h5 class="font-medium text-amber-800">Información Importante</h5>
                      <p class="text-sm opacity-75 mt-1">
                        Luego de confirmar, ve a pagos para completar. La garantía se devuelve sin daños.
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Confirmar -->
                <button
                  type="submit"
                  :disabled="!form.fecha || form.processing"
                  class="w-full py-4 rounded-xl font-semibold text-lg text-white bg-gradient-to-r from-blue-600 to-purple-600 shadow-lg hover:shadow-xl transition-all disabled:opacity-50"
                >
                  <span v-if="form.processing">Procesando…</span>
                  <span v-else>Confirmar Reserva</span>
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Imagen -->
      <div
        v-if="showImageModal"
        class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 z-50"
        @click="closeImageModal"
      >
        <div class="relative max-w-4xl max-h-full">
          <img
            :src="props.vehiculoSeleccionado.url_imagen"
            class="w-full h-auto rounded-lg"
          />
          <button
            @click="closeImageModal"
            class="absolute top-4 right-4 p-2 rounded-full bg-white hover:bg-gray-100 transition-colors"
          >
            ✕
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
