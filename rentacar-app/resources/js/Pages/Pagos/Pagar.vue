<template>
  <Head :title="`Pagar #${pago.id}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight">
          Realizar Pago #{{ pago.id }}
        </h2>
        <button
          @click="router.visit(url(`/pagos/${pago.id}`))"
          class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Volver al Detalle
        </button>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <!-- Resumen del Pago -->
        <div class="bg-white shadow-sm sm:rounded-lg p-8 mb-6">
          <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Realizar Pago</h1>
            <p class="text-gray-600 mb-6">Revisa el resumen y elige el método</p>

            <!-- Información del pago -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-100 rounded-xl p-6 mb-6">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                <div>
                  <p class="text-sm text-gray-600 mb-1">Tipo de Pago</p>
                  <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
                    :class="{
                      'bg-blue-100 text-blue-800': pago.tipo_pago === 'contrato',
                      'bg-green-100 text-green-800': pago.tipo_pago === 'garantia',
                      'bg-purple-100 text-purple-800': pago.tipo_pago === 'reserva'
                    }"
                  >
                    {{ pago.tipo_pago }}
                  </span>
                </div>

                <div>
                  <p class="text-sm text-gray-600 mb-1">Monto a Pagar</p>
                  <p class="text-3xl font-bold text-indigo-600">Bs. {{ formatearMonto(pago.monto) }}</p>
                </div>

                <div>
                  <p class="text-sm text-gray-600 mb-1">Estado</p>
                  <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
                    :class="{
                      'bg-green-100 text-green-800': pago.estado === 'pagado',
                      'bg-yellow-100 text-yellow-800': pago.estado === 'pendiente',
                      'bg-blue-100 text-blue-800': pago.estado === 'procesando',
                      'bg-red-100 text-red-800': pago.estado === 'fallido',
                      'bg-gray-100 text-gray-800': pago.estado === 'vencido'
                    }"
                  >
                    {{ pago.estado }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Detalles -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-left bg-gray-50 rounded-lg p-4 mb-6">
              <div>
                <h4 class="font-medium text-gray-900 mb-2">Cliente</h4>
                <p class="text-gray-700">
                  {{ pago.contrato?.users?.[0]?.name || 'Cliente Desconocido' }}
                </p>
                <p class="text-sm text-gray-500">
                  {{ pago.contrato?.users?.[0]?.email || '' }}
                </p>
              </div>

              <div>
                <h4 class="font-medium text-gray-900 mb-2">Vehículo</h4>
                <p class="text-gray-700">
                  {{ pago.contrato?.vehiculo?.placa || 'Vehículo Desconocido' }}
                </p>
                <p class="text-sm text-gray-500">
                  {{ pago.contrato?.vehiculo?.marca || '' }}
                  {{ pago.contrato?.vehiculo?.modelo || '' }}
                </p>
              </div>
            </div>

            <!-- Selección y acciones -->
            <div class="space-y-6">
              <!-- Botón QR fijo -->
              <div v-if="pago.estado !== 'pagado'">
                <button
                  @click="mostrarQrFijo"
                  :disabled="cargandoQR"
                  class="w-full inline-flex items-center justify-center px-8 py-4 bg-blue-600 text-white text-xl font-bold rounded-xl hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-lg"
                >
                  <svg v-if="cargandoQR" class="animate-spin -ml-1 mr-3 h-6 w-6 text-white" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                    </circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                  <span v-else>Mostrar QR</span>
                </button>
              </div>

              <!-- Pago en efectivo -->
              <div v-if="pago.estado !== 'pagado'">
                <button
                  @click="confirmarEfectivo"
                  :disabled="pago.estado !== 'pendiente'"
                  class="w-full inline-flex items-center justify-center px-8 py-4 bg-yellow-600 text-white text-xl font-bold rounded-xl hover:bg-yellow-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-lg"
                >
                  Registrar pago en efectivo
                </button>
              </div>

              <!-- Pago completado -->
              <div v-if="pago.estado === 'pagado'">
                <div class="inline-flex items-center px-6 py-3 text-lg font-medium text-green-700 bg-green-100 rounded-lg">
                  <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                      d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                      clip-rule="evenodd" />
                  </svg>
                  Pago completado
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal QR fijo -->
        <div v-if="modalVisible" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
          <div class="bg-white rounded-xl shadow-2xl w-full max-w-md relative">
            <button @click="cerrarModal"
              class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 text-2xl z-10 w-8 h-8 flex items-center justify-center">&times;</button>

            <div class="p-6">
              <h3 class="text-xl font-semibold mb-4 text-center text-gray-800">Escanea el QR y luego confirma</h3>

              <div class="text-center mb-4 p-4 bg-gray-100 rounded-lg">
                <p class="text-sm text-gray-700 font-medium mb-1">
                  Pago #{{ pago.id }} - {{ pago.tipo_pago }}
                </p>
                <p class="text-2xl font-bold text-indigo-600">
                  Bs. {{ formatearMonto(pago.monto) }}
                </p>
              </div>

              <div class="flex justify-center mb-6">
                <div class="relative">
                  <div v-if="qrImageSrc" class="p-4 bg-white border rounded-lg shadow">
                    <img
                      :src="qrImageSrc"
                      alt="QR fijo"
                      class="w-[280px] h-[280px] object-contain"
                    />
                  </div>
                  <div v-else class="flex flex-col items-center justify-center w-[280px] h-[280px] bg-gray-50 border rounded-lg">
                    <svg class="animate-spin h-12 w-12 text-blue-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                      viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                      <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8v8z" />
                    </svg>
                    <span class="text-gray-600 font-medium">Cargando QR fijo...</span>
                  </div>
                </div>
              </div>

              <!-- Confirmar como pagado -->
              <div class="flex flex-col gap-3">
                <button
                  @click="marcarComoPagado"
                  :disabled="pago.estado === 'pagado' || marcando"
                  class="w-full inline-flex items-center justify-center px-6 py-3 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 disabled:opacity-50 transition-colors shadow"
                >
                  <svg v-if="marcando" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                    </circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                  <span v-else>Confirmar como pagado</span>
                </button>

                <button
                  @click="cerrarModal"
                  class="w-full inline-flex items-center justify-center px-6 py-3 bg-gray-500 text-white font-medium rounded-lg hover:bg-gray-600 transition-colors shadow"
                >
                  Cerrar
                </button>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref, computed } from 'vue';
import axios from 'axios';
import { useBaseUrl } from '@/composables/useBaseUrl';
const { url } = useBaseUrl();
const props = defineProps({
  pago: Object,
});

const pago = props.pago;

const modalVisible = ref(false);
const qrImageUrl = ref('');
const cargandoQR = ref(false);
const marcando = ref(false);

// Computed para mostrar imagen
const qrImageSrc = computed(() => qrImageUrl.value);

// Formateo
const formatearMonto = (monto) => parseFloat(monto).toFixed(2);

// Mostrar QR fijo
const mostrarQrFijo = async () => {
  if (cargandoQR.value) return;
  modalVisible.value = true;
  cargandoQR.value = true;
  qrImageUrl.value = '';

  try {
    const res = await axios.post(`/pagos/${pago.id}/qr-fijo`);
    if (res.data.success && res.data.qr_image) {
      qrImageUrl.value = res.data.qr_image;
    } else {
      throw new Error(res.data.message || 'No se pudo cargar el QR fijo');
    }
  } catch (e) {
    Swal.fire('Error', e.response?.data?.message || e.message || 'Error cargando QR fijo', 'error');
    modalVisible.value = false;
  } finally {
    cargandoQR.value = false;
  }
};

// Marcar como pagado (QR fijo)
const marcarComoPagado = async () => {
  if (marcando.value) return;
  marcando.value = true;
  try {
    const res = await axios.post(`/pagos/${pago.id}/marcar-pagado`);
    if (res.data.success) {
      Swal.fire('Listo', res.data.message, 'success').then(() => {
        router.reload();
      });
    } else {
      throw new Error(res.data.message || 'No se pudo actualizar');
    }
  } catch (e) {
    Swal.fire('Error', e.response?.data?.message || e.message || 'Error al marcar pagado', 'error');
  } finally {
    marcando.value = false;
  }
};

// Pago en efectivo
const confirmarEfectivo = async () => {
  try {
    const resp = await axios.post(`/pagos/${pago.id}/efectivo`, {});
    Swal.fire('Listo', 'Pago en efectivo registrado correctamente', 'success').then(() => {
      router.reload();
    });
  } catch (error) {
    Swal.fire('Error', error.response?.data?.message || 'No se pudo registrar el pago en efectivo', 'error');
  }
};

const cerrarModal = () => {
  modalVisible.value = false;
};
</script>