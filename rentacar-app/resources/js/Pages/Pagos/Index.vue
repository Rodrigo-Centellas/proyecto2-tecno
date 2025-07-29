<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  pagos: Array,
});

const page = usePage();

// Estados reactivos
const modalQRVisible = ref(false);
const pagoSeleccionado = ref(null);
const qrImageUrl = ref('');
const transactionId = ref('');
const expirationDate = ref('');
const cargandoQR = ref(false);
const paymentStatus = ref('');
const tiempoRestante = ref('');

// Variables para intervalos
let intervalVerificacion = null;
let intervalTiempo = null;

// Computed para formatear la imagen QR
const qrImageSrc = computed(() => {
  if (!qrImageUrl.value) return '';
  
  // Si ya tiene el prefijo data:image, usarlo directamente
  if (qrImageUrl.value.startsWith('data:image')) {
    return qrImageUrl.value;
  }
  
  // Si no, agregar el prefijo para PNG
  return `data:image/png;base64,${qrImageUrl.value}`;
});

// Abrir modal de pago QR - USANDO AXIOS
const abrirModalPago = async (pago) => {
  console.log('🚀 Iniciando pago para:', pago.id);
  
  if (cargandoQR.value) return;
  
  pagoSeleccionado.value = pago;
  modalQRVisible.value = true;
  cargandoQR.value = true;
  
  // Limpiar estados previos
  qrImageUrl.value = '';
  transactionId.value = '';
  expirationDate.value = '';
  paymentStatus.value = '';
  tiempoRestante.value = '';

  try {
    // Usar axios que viene configurado con Laravel y maneja CSRF automáticamente
    const response = await axios.post(`/pagos/${pago.id}/generar-qr`);
    const data = response.data;
    
    console.log('✅ Respuesta recibida:', {
      success: data.success,
      transaction_id: data.transaction_id,
      qr_length: data.qr_image?.length,
      expiration: data.expiration_date
    });

    if (data.success && data.qr_image) {
      // Asignar los valores recibidos
      qrImageUrl.value = data.qr_image;
      transactionId.value = data.transaction_id;
      expirationDate.value = data.expiration_date;
      
      cargandoQR.value = false;
      
      // Iniciar contador de tiempo
      if (expirationDate.value) {
        iniciarContadorTiempo();
      }
      
      // Iniciar verificación automática
      if (transactionId.value) {
        iniciarVerificacionAutomatica();
      }
      
    } else {
      throw new Error(data.message || 'No se recibió el código QR');
    }
    
  } catch (error) {
    console.error('❌ Error al generar QR:', error);
    cargandoQR.value = false;
    cerrarModalPago();
    
    const errorMessage = error.response?.data?.message || error.message || 'Error al generar el código QR';
    Swal.fire('Error', errorMessage, 'error');
  }
};

// Iniciar verificación automática del pago
const iniciarVerificacionAutomatica = () => {
  if (!transactionId.value) return;
  
  console.log('🔍 Iniciando verificación automática para:', transactionId.value);
  paymentStatus.value = 'verificando';
  
  let intentos = 0;
  const maxIntentos = 24; // 2 minutos con verificaciones cada 5 segundos
  
  // Limpiar intervalo anterior si existe
  if (intervalVerificacion) {
    clearInterval(intervalVerificacion);
  }
  
  intervalVerificacion = setInterval(async () => {
    intentos++;
    console.log(`🔄 Verificando pago - Intento ${intentos}/${maxIntentos}`);
    
    try {
      const confirmado = await verificarPago(transactionId.value);
      
      if (confirmado) {
        clearInterval(intervalVerificacion);
        intervalVerificacion = null;
        paymentStatus.value = 'completado';
        
        console.log('✅ Pago confirmado exitosamente');
        
        setTimeout(() => {
          cerrarModalPago();
          
          Swal.fire({
            title: '¡Pago Exitoso!',
            text: 'Su pago ha sido procesado correctamente',
            icon: 'success',
            confirmButtonText: 'Aceptar'
          }).then(() => {
            // Recargar usando Inertia para mantener el estado
            router.reload({ only: ['pagos'] });
          });
        }, 2000);
        
        return;
      }
      
      if (intentos >= maxIntentos) {
        clearInterval(intervalVerificacion);
        intervalVerificacion = null;
        paymentStatus.value = 'timeout';
        
        Swal.fire({
          title: 'Tiempo agotado',
          text: 'No se pudo confirmar el pago. Por favor verifique en su aplicación de pagos.',
          icon: 'warning',
          confirmButtonText: 'Aceptar'
        });
      }
    } catch (error) {
      console.error('❌ Error en verificación:', error);
    }
  }, 5000);
};

// Verificar pago
const verificarPago = async (numeroTransaccion) => {
  try {
    const response = await axios.post('/pagos/verificar-pago', { numeroTransaccion });
    const result = response.data;
    
    console.log('Resultado verificación:', result);
    
    // EstadoTransaccion 5 = Completado en PagoFácil
    return result.data?.EstadoTransaccion === 5;
    
  } catch (error) {
    console.error('Error al verificar pago:', error);
    return false;
  }
};

// Iniciar contador de tiempo
const iniciarContadorTiempo = () => {
  if (!expirationDate.value) return;
  
  console.log('⏰ Iniciando contador de tiempo hasta:', expirationDate.value);
  
  // Limpiar intervalo anterior si existe
  if (intervalTiempo) {
    clearInterval(intervalTiempo);
  }
  
  const actualizarTiempo = () => {
    const ahora = new Date().getTime();
    const expiracion = new Date(expirationDate.value).getTime();
    const diferencia = expiracion - ahora;
    
    if (diferencia > 0) {
      const minutos = Math.floor((diferencia % (1000 * 60 * 60)) / (1000 * 60));
      const segundos = Math.floor((diferencia % (1000 * 60)) / 1000);
      tiempoRestante.value = `${minutos}:${segundos.toString().padStart(2, '0')}`;
    } else {
      tiempoRestante.value = 'Expirado';
      clearInterval(intervalTiempo);
      intervalTiempo = null;
    }
  };
  
  actualizarTiempo();
  intervalTiempo = setInterval(actualizarTiempo, 1000);
};

// Cerrar modal
const cerrarModalPago = () => {
  console.log('🔒 Cerrando modal de pago');
  
  // Limpiar intervalos
  if (intervalVerificacion) {
    clearInterval(intervalVerificacion);
    intervalVerificacion = null;
  }
  
  if (intervalTiempo) {
    clearInterval(intervalTiempo);
    intervalTiempo = null;
  }
  
  // Resetear estados
  modalQRVisible.value = false;
  paymentStatus.value = '';
  qrImageUrl.value = '';
  transactionId.value = '';
  expirationDate.value = '';
  tiempoRestante.value = '';
  pagoSeleccionado.value = null;
  cargandoQR.value = false;
};

// Descargar QR
const descargarQR = () => {
  if (!qrImageSrc.value) {
    console.warn('⚠️ No hay imagen QR para descargar');
    return;
  }
  
  try {
    const link = document.createElement('a');
    link.download = `QR_Pago_${pagoSeleccionado.value?.id || Date.now()}.png`;
    link.href = qrImageSrc.value;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    console.log('💾 QR descargado exitosamente');
  } catch (error) {
    console.error('❌ Error al descargar QR:', error);
    Swal.fire('Error', 'No se pudo descargar el código QR', 'error');
  }
};

// Funciones de formateo
const formatearMonto = (monto) => {
  return parseFloat(monto).toFixed(2);
};

// Limpiar intervalos al destruir el componente
onUnmounted(() => {
  if (intervalVerificacion) {
    clearInterval(intervalVerificacion);
  }
  if (intervalTiempo) {
    clearInterval(intervalTiempo);
  }
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
              Total: {{ pagos?.length || 0 }} pago{{ (pagos?.length || 0) !== 1 ? 's' : '' }}
            </div>
          </div>

          <!-- Tabla de pagos -->
          <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left font-medium text-gray-900 border">ID</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-900 border">Tipo</th>
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
                        @click="abrirModalPago(pago)"
                        :disabled="cargandoQR"
                        class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-sm">
                        <svg v-if="cargandoQR && pagoSeleccionado?.id === pago.id" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h4.01M12 12v4.01M12 12v4.01"></path>
                        </svg>
                        {{ cargandoQR && pagoSeleccionado?.id === pago.id ? 'Generando...' : 'Pagar con QR' }}
                      </button>
                      
                      <!-- Badge de estado pagado -->
                      <span v-else-if="pago.estado === 'pagado'" 
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-green-700 bg-green-100 rounded-lg">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Pagado
                      </span>
                      
                      <!-- Badge de otros estados -->
                      <span v-else class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-gray-100 rounded-lg">
                        {{ pago.estado }}
                      </span>
                    </div>
                  </td>
                </tr>
                
                <!-- Fila vacía -->
                <tr v-if="!pagos || pagos.length === 0">
                  <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                    No hay pagos registrados
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal QR -->
    <div v-if="modalQRVisible" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-md relative">
        <button @click="cerrarModalPago"
          class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl z-10 w-8 h-8 flex items-center justify-center">&times;</button>

        <div class="p-6">
          <h3 class="text-xl font-semibold mb-4 text-center text-gray-800">Escanea y paga con QR</h3>
          
          <!-- Información del pago -->
          <div class="text-center mb-4 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-100">
            <p class="text-sm text-gray-700 font-medium">
              <strong>Pago #{{ pagoSeleccionado?.id }}</strong> - {{ pagoSeleccionado?.tipo_pago }}
            </p>
            <p class="text-2xl font-bold text-indigo-600 mt-1">
              Bs. {{ formatearMonto(pagoSeleccionado?.monto || 0) }}
            </p>
          </div>
          
          <!-- QR Code -->
          <div class="flex justify-center mb-6">
            <div class="relative">
              <!-- Imagen QR -->
              <div v-if="qrImageSrc && !cargandoQR" class="p-4 bg-white border-2 border-gray-200 rounded-lg shadow-lg">
                <img 
                  :src="qrImageSrc" 
                  alt="Código QR para pago" 
                  class="w-[280px] h-[280px] object-contain"
                  @error="(e) => {
                    console.error('❌ Error al cargar imagen QR');
                    Swal.fire('Error', 'No se pudo cargar la imagen del QR', 'error');
                  }"
                  @load="() => console.log('✅ Imagen QR cargada correctamente')"
                />
              </div>
              
              <!-- Loading state -->
              <div v-else class="flex flex-col items-center justify-center w-[280px] h-[280px] bg-gray-50 border-2 border-gray-200 rounded-lg">
                <svg class="animate-spin h-12 w-12 text-blue-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                </svg>
                <span class="text-gray-600 font-medium">Generando QR...</span>
              </div>
            </div>
          </div>

          <!-- Información de la transacción -->
          <div v-if="transactionId" class="text-center mb-4 bg-gray-50 rounded-lg p-3">
            <div class="space-y-2 text-sm">
              <p class="text-gray-700">
                <strong>ID Transacción:</strong> 
                <span class="font-mono text-xs">{{ transactionId }}</span>
              </p>
              <p v-if="tiempoRestante" class="text-gray-700">
                <strong>Tiempo restante:</strong> 
                <span :class="tiempoRestante === 'Expirado' ? 'text-red-600 font-bold' : 'text-green-600 font-bold'">
                  {{ tiempoRestante }}
                </span>
              </p>
            </div>
          </div>

          <!-- Estado del pago -->
          <div v-if="paymentStatus === 'verificando'" class="text-center mb-4">
            <div class="flex items-center justify-center space-x-3 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
              <svg class="animate-spin h-6 w-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
              </svg>
              <span class="font-medium text-yellow-800">Verificando pago...</span>
            </div>
          </div>

          <div v-else-if="paymentStatus === 'completado'" class="text-center mb-4">
            <div class="flex items-center justify-center space-x-3 p-4 bg-green-50 rounded-lg border border-green-200">
              <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
              </svg>
              <span class="font-medium text-green-800">✅ Pago verificado correctamente</span>
            </div>
          </div>

          <!-- Botones de acción -->
          <div class="flex space-x-3 mb-4">
            <button 
              @click="descargarQR" 
              :disabled="!qrImageSrc || cargandoQR"
              class="flex-1 bg-green-600 text-white py-3 px-4 rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium transition-colors shadow-sm">
              <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
              </svg>
              Descargar QR
            </button>
            <button 
              @click="cerrarModalPago" 
              class="flex-1 bg-gray-600 text-white py-3 px-4 rounded-lg hover:bg-gray-700 text-sm font-medium transition-colors shadow-sm">
              <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
              Cerrar
            </button>
          </div>

          <!-- Instrucciones -->
          <div class="text-center text-xs text-gray-600 bg-blue-50 rounded-lg p-3 border border-blue-100">
            <p class="font-medium mb-1">📱 Instrucciones:</p>
            <p>1. Abre tu app de Tigo Money</p>
            <p>2. Escanea el código QR</p>
            <p>3. Confirma el pago en tu aplicación</p>
            <p class="text-blue-600 font-medium mt-2">El sistema verificará automáticamente tu pago</p>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>