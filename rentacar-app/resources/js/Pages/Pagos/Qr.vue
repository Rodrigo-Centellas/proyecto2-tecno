<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref, onMounted } from 'vue';

const props = defineProps({
  pagos: Array,
});

const modalQRVisible = ref(false);
const pagoSeleccionado = ref(null);
const qrImage = ref('');
const transactionId = ref('');
const expirationDate = ref('');
const cargandoQR = ref(false);
const consultandoEstado = ref(false);

// Abrir modal de QR
const generarQR = async (pago) => {
  if (cargandoQR.value) return;

  pagoSeleccionado.value = pago;
  cargandoQR.value = true;

  try {
    const response = await fetch(`/api/pagos/${pago.id}/generar-qr`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    });

    const data = await response.json();

    if (data.success) {
      qrImage.value = 'data:image/png;base64,' + data.qr_image;
      transactionId.value = data.transaction_id;
      expirationDate.value = data.expiration_date;
      modalQRVisible.value = true;
      
      // Iniciar verificación periódica del estado
      iniciarVerificacionEstado();
      
      Swal.fire({
        title: '¡QR Generado!',
        text: 'Escanea el código QR para realizar el pago',
        icon: 'success',
        timer: 3000,
        showConfirmButton: false
      });
    } else {
      Swal.fire('Error', data.message, 'error');
    }
  } catch (error) {
    console.error('Error al generar QR:', error);
    Swal.fire('Error', 'No se pudo generar el código QR', 'error');
  } finally {
    cargandoQR.value = false;
  }
};

// Verificación periódica del estado del pago
let intervaloVerificacion = null;

const iniciarVerificacionEstado = () => {
  if (intervaloVerificacion) {
    clearInterval(intervaloVerificacion);
  }

  intervaloVerificacion = setInterval(async () => {
    await consultarEstadoPago();
  }, 5000); // Consultar cada 5 segundos

  // Detener después de 10 minutos
  setTimeout(() => {
    if (intervaloVerificacion) {
      clearInterval(intervaloVerificacion);
      intervaloVerificacion = null;
    }
  }, 600000);
};

const consultarEstadoPago = async () => {
  if (!pagoSeleccionado.value || consultandoEstado.value) return;

  consultandoEstado.value = true;

  try {
    const response = await fetch(`/api/pagos/${pagoSeleccionado.value.id}/consultar-estado`);
    const data = await response.json();

    if (data.success && data.estado === 'pagado') {
      // Pago completado
      clearInterval(intervaloVerificacion);
      intervaloVerificacion = null;
      
      modalQRVisible.value = false;
      
      Swal.fire({
        title: '¡Pago Exitoso!',
        text: 'Su pago ha sido procesado correctamente',
        icon: 'success',
        confirmButtonText: 'Aceptar'
      }).then(() => {
        // Recargar la página para actualizar los datos
        window.location.reload();
      });
    }
  } catch (error) {
    console.error('Error al consultar estado:', error);
  } finally {
    consultandoEstado.value = false;
  }
};

const cerrarModalQR = () => {
  modalQRVisible.value = false;
  qrImage.value = '';
  transactionId.value = '';
  expirationDate.value = '';
  pagoSeleccionado.value = null;
  
  if (intervaloVerificacion) {
    clearInterval(intervaloVerificacion);
    intervaloVerificacion = null;
  }
};

const formatearFecha = (fecha) => {
  return new Date(fecha).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  });
};

const formatearMonto = (monto) => {
  return parseFloat(monto).toFixed(2);
};

// Limpiar intervalo al desmontar componente
onMounted(() => {
  return () => {
    if (intervaloVerificacion) {
      clearInterval(intervaloVerificacion);
    }
  };
});
</script>

<template>
  <Head title="Pagos" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Gestión de Pagos
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
          
          <!-- Header -->
          <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Listado de Pagos</h1>
            <div class="text-sm text-gray-500">
              Total: {{ pagos.length }} pago{{ pagos.length !== 1 ? 's' : '' }}
            </div>
          </div>

          <!-- Tabla de pagos -->
          <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left font-medium text-gray-900 border">ID</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-900 border">Tipo</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-900 border">Vehículo</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-900 border">Período</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-900 border">Fecha Venc.</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-900 border">Monto</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-900 border">Estado</th>
                  <th class="px-4 py-3 text-center font-medium text-gray-900 border">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="pago in pagos" :key="pago.id" class="hover:bg-gray-50">
                  <!-- ID -->
                  <td class="px-4 py-3 border font-mono text-sm">
                    #{{ pago.id }}
                  </td>
                  
                  <!-- Tipo -->
                  <td class="px-4 py-3 border">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                          :class="{
                            'bg-blue-100 text-blue-800': pago.tipo_pago === 'contrato',
                            'bg-green-100 text-green-800': pago.tipo_pago === 'garantia',
                            'bg-purple-100 text-purple-800': pago.tipo_pago === 'reserva'
                          }">
                      {{ pago.tipo_pago }}
                    </span>
                  </td>
                  
                  <!-- Vehículo -->
                  <td class="px-4 py-3 border">
                    <div v-if="pago.contrato?.vehiculo || pago.reserva?.vehiculo" class="text-sm">
                      <div class="font-medium text-gray-900">
                        {{ pago.contrato?.vehiculo?.marca || pago.reserva?.vehiculo?.marca }}
                        {{ pago.contrato?.vehiculo?.modelo || pago.reserva?.vehiculo?.modelo }}
                      </div>
                      <div class="text-gray-500">
                        {{ pago.contrato?.vehiculo?.placa || pago.reserva?.vehiculo?.placa }}
                      </div>
                    </div>
                    <div v-else class="text-sm text-gray-400">
                      Sin vehículo asignado
                    </div>
                  </td>
                  
                  <!-- Período -->
                  <td class="px-4 py-3 border text-sm">
                    <div v-if="pago.desde && pago.hasta">
                      <div class="text-gray-900">{{ formatearFecha(pago.desde) }}</div>
                      <div class="text-gray-500 text-xs">{{ formatearFecha(pago.hasta) }}</div>
                    </div>
                    <div v-else class="text-gray-400">-</div>
                  </td>
                  
                  <!-- Fecha Vencimiento -->
                  <td class="px-4 py-3 border text-sm">
                    <div class="text-gray-900">{{ formatearFecha(pago.fecha) }}</div>
                  </td>
                  
                  <!-- Monto -->
                  <td class="px-4 py-3 border">
                    <div class="font-semibold text-gray-900">
                      Bs. {{ formatearMonto(pago.monto) }}
                    </div>
                  </td>
                  
                  <!-- Estado -->
                  <td class="px-4 py-3 border">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                          :class="{
                            'bg-green-100 text-green-800': pago.estado === 'pagado',
                            'bg-yellow-100 text-yellow-800': pago.estado === 'pendiente',
                            'bg-blue-100 text-blue-800': pago.estado === 'procesando',
                            'bg-red-100 text-red-800': pago.estado === 'fallido',
                            'bg-gray-100 text-gray-800': pago.estado === 'vencido'
                          }">
                      {{ pago.estado }}
                    </span>
                  </td>
                  
                  <!-- Acciones -->
                  <td class="px-4 py-3 border text-center">
                    <div class="flex justify-center space-x-2">
                      <!-- Botón Pagar con QR -->
                      <button
                        v-if="pago.estado === 'pendiente'"
                        @click="generarQR(pago)"
                        :disabled="cargandoQR"
                        class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <svg v-if="cargandoQR" class="animate-spin -ml-1 mr-2 h-3 w-3 text-white" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h4.01M12 12v4.01M12 12v4.01"></path>
                        </svg>
                        {{ cargandoQR ? 'Generando...' : 'Pagar QR' }}
                      </button>
                      
                      <!-- Botón Consultar Estado -->
                      <button
                        v-if="pago.estado === 'procesando'"
                        @click="consultarEstadoPago"
                        :disabled="consultandoEstado"
                        class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <svg v-if="consultandoEstado" class="animate-spin -ml-1 mr-2 h-3 w-3 text-white" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Consultar
                      </button>
                      
                      <!-- Estado Pagado -->
                      <span v-if="pago.estado === 'pagado'" class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Pagado
                      </span>
                    </div>
                  </td>
                </tr>
                
                <!-- Fila vacía -->
                <tr v-if="pagos.length === 0">
                  <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                      <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                      </svg>
                      <p class="text-lg font-medium">No hay pagos registrados</p>
                      <p class="text-sm">Los pagos aparecerán aquí cuando se generen contratos o reservas</p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal QR -->
    <div v-if="modalQRVisible" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
        <!-- Header del Modal -->
        <div class="flex items-center justify-between p-6 border-b">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Pagar con QR</h3>
            <p class="text-sm text-gray-500">Pago #{{ pagoSeleccionado?.id }}</p>
          </div>
          <button @click="cerrarModalQR" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- Contenido del Modal -->
        <div class="p-6">
          <!-- Información del Pago -->
          <div class="bg-blue-50 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-medium text-blue-900">Monto a Pagar:</span>
              <span class="text-lg font-bold text-blue-900">Bs. {{ formatearMonto(pagoSeleccionado?.monto || 0) }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm text-blue-700">Tipo:</span>
              <span class="text-sm text-blue-700 capitalize">{{ pagoSeleccionado?.tipo_pago }}</span>
            </div>
          </div>

          <!-- Código QR -->
          <div class="text-center mb-6">
            <div class="inline-block p-4 bg-white border-2 border-gray-200 rounded-lg">
              <img :src="qrImage" alt="Código QR" class="w-48 h-48 mx-auto" />
            </div>
          </div>

          <!-- Instrucciones -->
          <div class="space-y-3 mb-6">
            <div class="flex items-start space-x-3">
              <div class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                <span class="text-sm font-medium text-blue-600">1</span>
              </div>
              <p class="text-sm text-gray-600">Abra su aplicación de banca móvil o billetera digital</p>
            </div>
            <div class="flex items-start space-x-3">
              <div class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                <span class="text-sm font-medium text-blue-600">2</span>
              </div>
              <p class="text-sm text-gray-600">Escanee el código QR mostrado arriba</p>
            </div>
            <div class="flex items-start space-x-3">
              <div class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                <span class="text-sm font-medium text-blue-600">3</span>
              </div>
              <p class="text-sm text-gray-600">Confirme el pago en su aplicación</p>
            </div>
          </div>

          <!-- Estado de Verificación -->
          <div class="flex items-center justify-center space-x-2 p-3 bg-yellow-50 rounded-lg">
            <svg class="animate-spin h-4 w-4 text-yellow-600" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm font-medium text-yellow-800">Esperando confirmación del pago...</span>
          </div>

          <!-- Información Adicional -->
          <div class="mt-4 p-3 bg-gray-50 rounded-lg">
            <div class="text-xs text-gray-500 space-y-1">
              <div class="flex justify-between">
                <span>ID Transacción:</span>
                <span class="font-mono">{{ transactionId }}</span>
              </div>
              <div class="flex justify-between">
                <span>Vence:</span>
                <span>{{ expirationDate }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer del Modal -->
        <div class="px-6 py-4 bg-gray-50 rounded-b-xl">
          <button @click="cerrarModalQR" 
                  class="w-full px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
            Cerrar
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>