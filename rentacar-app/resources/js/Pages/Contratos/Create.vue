<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Swal from 'sweetalert2';
import { useForm } from '@inertiajs/vue3';
import dayjs from 'dayjs';

const props = defineProps({
  vehiculos: Array,
  frecuencia_pagos: Array,
  clausulas: Array, // Solo cláusulas activas
  vehiculoSeleccionado: Object
});

const currentStep = ref(1);
const totalSteps = 3; // Reducido a 3 pasos

// Formulario simplificado
const form = useForm({
  // AUTOMÁTICOS
  estado: 'vigente',
  fecha_inicio: dayjs().format('YYYY-MM-DD'),
  vehiculo_id: props.vehiculoSeleccionado?.id || '',
  
  // CLIENTE SELECCIONA
  fecha_fin: '',
  frecuencia_pago_id: '',
  
  // ACEPTACIONES
  acepta_clausulas: false,
  pago_garantia_acepta: false
});

// Cálculos automáticos
const diasContrato = computed(() => {
  if (!form.fecha_inicio || !form.fecha_fin) return 0;
  const inicio = dayjs(form.fecha_inicio);
  const fin = dayjs(form.fecha_fin);
  return fin.diff(inicio, 'day') + 1;
});

const vehiculoActual = computed(() => {
  return props.vehiculoSeleccionado || props.vehiculos.find(v => v.id == form.vehiculo_id);
});

const costoTotal = computed(() => {
  if (!vehiculoActual.value || diasContrato.value <= 0) return 0;
  return diasContrato.value * vehiculoActual.value.precio_dia;
});

// Filtrar frecuencias válidas según la duración del contrato
const frecuenciasValidas = computed(() => {
  if (diasContrato.value <= 0) return props.frecuencia_pagos;
  
  return props.frecuencia_pagos.filter(frecuencia => {
    return frecuencia.frecuencia_dias <= diasContrato.value;
  });
});

// Obtener la frecuencia seleccionada
const frecuenciaSeleccionada = computed(() => {
  return props.frecuencia_pagos.find(f => f.id == form.frecuencia_pago_id);
});

// Calcular número de pagos y distribución
const distribucionPagos = computed(() => {
  if (!frecuenciaSeleccionada.value || diasContrato.value <= 0) {
    return { pagos: [], total: 0 };
  }
  
  const pagos = [];
  let diasRestantes = diasContrato.value;
  let fechaActual = dayjs(form.fecha_inicio);
  
  while (diasRestantes > 0) {
    const diasEstePago = Math.min(frecuenciaSeleccionada.value.frecuencia_dias, diasRestantes);
    const montoPago = diasEstePago * (vehiculoActual.value?.precio_dia || 0);
    
    pagos.push({
      numero: pagos.length + 1,
      dias: diasEstePago,
      monto: montoPago,
      desde: fechaActual.format('DD/MM'),
      hasta: fechaActual.add(diasEstePago - 1, 'day').format('DD/MM')
    });
    
    fechaActual = fechaActual.add(diasEstePago, 'day');
    diasRestantes -= diasEstePago;
  }
  
  return { 
    pagos, 
    total: pagos.reduce((sum, pago) => sum + pago.monto, 0) 
  };
});

const fechaFinMinima = computed(() => {
  return dayjs(form.fecha_inicio).add(1, 'day').format('YYYY-MM-DD');
});

// Validación de pasos simplificada
const canProceedToStep = computed(() => {
  switch(currentStep.value) {
    case 1:
      return form.fecha_fin && form.frecuencia_pago_id;
    case 2:
      return form.acepta_clausulas;
    case 3:
      return form.pago_garantia_acepta;
    default:
      return false;
  }
});

// Navegación entre pasos
const nextStep = () => {
  if (canProceedToStep.value && currentStep.value < totalSteps) {
    currentStep.value++;
  }
};

const previousStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--;
  }
};

// Enviar formulario
const enviar = () => {
  if (!canProceedToStep.value) {
    Swal.fire('Error', 'Por favor, complete todos los campos obligatorios.', 'error');
    return;
  }

  form.post('/contratos', {
    onSuccess: () => {
      Swal.fire({
        title: '¡Contrato creado exitosamente!',
        text: 'Se ha generado el pago de garantía. Proceda al área de pagos.',
        icon: 'success',
        confirmButtonColor: '#10B981'
      }).then(() => router.visit('/contratos'));
    },
    onError: (errors) => {
      console.log('Errores:', errors);
      Swal.fire('Error', 'Ocurrió un error al crear el contrato. Revise los datos.', 'error');
    },
  });
};

const steps = [
  { number: 1, title: 'Configuración del Alquiler', description: 'Duración y forma de pago' },
  { number: 2, title: 'Términos y Condiciones', description: 'Acepta las cláusulas del contrato' },
  { number: 3, title: 'Confirmación', description: 'Revisa y confirma el contrato' }
];
</script>

<template>
  <Head title="Nuevo Contrato de Alquiler" />
  
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center space-x-3">
        <div class="bg-green-100 p-2 rounded-lg">
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900">Nuevo Contrato de Alquiler</h2>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Progress Bar -->
        <div class="mb-8">
          <div class="flex items-center justify-between">
            <div v-for="step in steps" :key="step.number" class="flex flex-col items-center flex-1">
              <div class="flex items-center w-full">
                <div :class="[
                  'w-12 h-12 rounded-full flex items-center justify-center text-sm font-medium',
                  currentStep >= step.number 
                    ? 'bg-green-600 text-white' 
                    : 'bg-gray-300 text-gray-600'
                ]">
                  <span v-if="currentStep > step.number">✓</span>
                  <span v-else>{{ step.number }}</span>
                </div>
                <div v-if="step.number < totalSteps" 
                     :class="[
                       'flex-1 h-1 mx-4',
                       currentStep > step.number ? 'bg-green-600' : 'bg-gray-300'
                     ]">
                </div>
              </div>
              <div class="mt-3 text-center">
                <div class="text-sm font-medium">{{ step.title }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ step.description }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Content Card -->
        <div class="bg-white shadow-2xl rounded-2xl overflow-hidden">
          
          <!-- Vehicle Header -->
          <div v-if="vehiculoActual" class="bg-gradient-to-r from-green-600 to-blue-600 px-8 py-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between text-white space-y-4 md:space-y-0">
              <div>
                <h3 class="text-3xl font-bold">{{ vehiculoActual.tipo }}</h3>
                <p class="text-green-100 text-lg">{{ vehiculoActual.marca }} {{ vehiculoActual.modelo }}</p>
                <p class="text-sm text-green-200">Placa: {{ vehiculoActual.placa }}</p>
              </div>
              <div class="text-right">
                <div class="text-4xl font-bold">${{ vehiculoActual.precio_dia }}</div>
                <div class="text-green-100">por día</div>
                <div class="text-sm text-green-200">Garantía: ${{ vehiculoActual.monto_garantia }}</div>
              </div>
            </div>
          </div>

          <div class="p-8">
            <form @submit.prevent="enviar">
              
              <!-- STEP 1: Configuración del Alquiler -->
              <div v-show="currentStep === 1" class="space-y-8">
                <div class="text-center">
                  <h3 class="text-3xl font-bold text-gray-900">Configuración del Alquiler</h3>
                  <p class="text-gray-600 mt-2">Define la duración y frecuencia de pago</p>
                </div>

                <!-- Información del vehículo y fecha de inicio -->
                <div class="grid md:grid-cols-2 gap-6">
                  <!-- Vehículo Seleccionado -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Vehículo Seleccionado</label>
                    <div class="w-full px-4 py-4 border-2 border-green-200 rounded-xl bg-green-50">
                      <div class="flex items-center justify-between">
                        <div>
                          <div class="font-semibold text-green-800" v-if="vehiculoActual">
                            {{ vehiculoActual.tipo }}
                          </div>
                          <div class="text-sm text-green-600" v-if="vehiculoActual">
                            {{ vehiculoActual.marca }} {{ vehiculoActual.modelo }}
                          </div>
                        </div>
                        <div class="text-green-600">
                          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                          </svg>
                        </div>
                      </div>
                    </div>
                    <input type="hidden" v-model="form.vehiculo_id" />
                  </div>

                  <!-- Fecha de Inicio -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Fecha de Inicio</label>
                    <div class="w-full px-4 py-4 border-2 border-blue-200 rounded-xl bg-blue-50">
                      <div class="flex items-center justify-between">
                        <div class="font-semibold text-blue-800">
                          Hoy - {{ dayjs(form.fecha_inicio).format('DD/MM/YYYY') }}
                        </div>
                        <div class="text-blue-600">
                          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                          </svg>
                        </div>
                      </div>
                    </div>
                    <input type="hidden" v-model="form.fecha_inicio" />
                  </div>
                </div>

                <!-- Fecha de Finalización -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-3">Fecha de Finalización</label>
                  <input v-model="form.fecha_fin" 
                         type="date" 
                         :min="fechaFinMinima"
                         class="w-full px-4 py-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent text-lg"
                         required />
                  <p class="text-sm text-gray-500 mt-2">
                    Selecciona hasta cuándo durará el contrato de alquiler
                  </p>
                </div>

                <!-- Frecuencia de Pago -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-4">Frecuencia de Pago</label>
                  <div v-if="frecuenciasValidas.length === 0" class="text-center py-8 text-gray-500">
                    <p>No hay frecuencias de pago disponibles para la duración seleccionada</p>
                    <p class="text-sm">Selecciona una fecha de finalización para ver las opciones</p>
                  </div>
                  <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div v-for="fp in frecuenciasValidas" :key="fp.id" class="relative">
                      <input v-model="form.frecuencia_pago_id" 
                             :id="`frecuencia_${fp.id}`"
                             :value="fp.id"
                             type="radio" fe
                             class="sr-only peer">
                      <label :for="`frecuencia_${fp.id}`"
                             class="block p-6 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-green-300 peer-checked:border-green-500 peer-checked:bg-green-50 transition-all">
                        <div class="text-center">
                          <div class="text-xl font-semibold">{{ fp.nombre }}</div>
                          <div class="text-sm text-gray-600 mt-2">Cada {{ fp.frecuencia_dias }} día{{ fp.frecuencia_dias !== 1 ? 's' : '' }}</div>
                          <div v-if="form.frecuencia_pago_id == fp.id && distribucionPagos.pagos.length > 0" 
                               class="text-xs text-green-600 mt-2 font-medium">
                            {{ distribucionPagos.pagos.length }} pago{{ distribucionPagos.pagos.length !== 1 ? 's' : '' }}
                          </div>
                        </div>
                      </label>
                    </div>
                  </div>
                </div>

                <!-- Distribución de Pagos -->
                <div v-if="form.frecuencia_pago_id && distribucionPagos.pagos.length > 0" 
                     class="bg-purple-50 rounded-xl p-6">
                  <h4 class="font-bold text-purple-900 mb-4 text-center text-xl">📊 Distribución de Pagos</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                    <div v-for="pago in distribucionPagos.pagos" :key="pago.numero"
                         class="bg-white rounded-lg p-4 border border-purple-200">
                      <div class="text-center">
                        <div class="text-lg font-bold text-purple-800">Pago #{{ pago.numero }}</div>
                        <div class="text-sm text-gray-600">{{ pago.desde }} - {{ pago.hasta }}</div>
                        <div class="text-sm text-gray-600">{{ pago.dias }} día{{ pago.dias !== 1 ? 's' : '' }}</div>
                        <div class="text-xl font-bold text-green-600 mt-2">${{ pago.monto }}</div>
                      </div>
                    </div>
                  </div>
                  <div class="text-center border-t border-purple-200 pt-4">
                    <div class="text-lg">
                      <strong>Total: {{ distribucionPagos.pagos.length }} pago{{ distribucionPagos.pagos.length !== 1 ? 's' : '' }} = ${{ distribucionPagos.total }}</strong>
                    </div>
                  </div>
                </div>
              </div>

              <!-- STEP 2: Términos y Condiciones -->
              <div v-show="currentStep === 2" class="space-y-6">
                <div class="text-center">
                  <h3 class="text-3xl font-bold text-gray-900">Términos y Condiciones</h3>
                  <p class="text-gray-600 mt-2">Revisa y acepta las cláusulas del contrato</p>
                </div>
                
                <!-- Cláusulas Activas -->
                <div class="bg-gray-50 rounded-xl p-6">
                  <h4 class="font-bold text-gray-900 mb-4 text-xl">Cláusulas del Contrato</h4>
                  <div class="max-h-80 overflow-y-auto space-y-4">
                    <div v-for="(clausula, index) in clausulas" :key="clausula.id" 
                         class="p-4 bg-white rounded-lg border border-gray-200">
                      <div class="flex items-start space-x-3">
                        <div class="bg-blue-100 text-blue-600 rounded-full p-1 mt-1">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                          </svg>
                        </div>
                        <div>
                          <h5 class="font-semibold text-gray-900">Cláusula {{ index + 1 }}</h5>
                          <p class="text-sm text-gray-700 mt-1">{{ clausula.descripcion }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <p class="text-xs text-blue-600 mt-4 text-center">
                    ✓ Todas estas cláusulas se aplicarán automáticamente a tu contrato
                  </p>
                </div>

                <!-- Aceptación global -->
                <div class="bg-green-50 border-2 border-green-200 rounded-xl p-6">
                  <div class="flex items-start space-x-4">
                    <input v-model="form.acepta_clausulas" 
                           type="checkbox" 
                           class="w-6 h-6 text-green-600 focus:ring-green-500 border-gray-300 rounded mt-1"
                           required>
                    <div>
                      <label class="text-lg font-semibold text-green-800 cursor-pointer">
                        He leído y acepto todas las cláusulas del contrato de alquiler
                      </label>
                      <p class="text-sm text-green-600 mt-2">
                        Al marcar esta casilla, confirmas que has leído, entendido y aceptas cumplir con todos los términos y condiciones establecidos en las cláusulas del contrato.
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- STEP 3: Confirmación -->
              <div v-show="currentStep === 3" class="space-y-6">
                <div class="text-center">
                  <h3 class="text-3xl font-bold text-gray-900">Confirmación del Contrato</h3>
                  <p class="text-gray-600 mt-2">Revisa los detalles finales y confirma</p>
                </div>
                
                <!-- Resumen Final -->
                <div class="space-y-4">
                  <!-- Resumen Vehículo -->
                  <div class="bg-blue-50 rounded-xl p-6">
                    <h4 class="font-bold text-blue-900 mb-3 text-lg">🚗 Vehículo</h4>
                    <div class="grid md:grid-cols-2 gap-4" v-if="vehiculoActual">
                      <div>
                        <p class="text-blue-800"><strong>Tipo:</strong> {{ vehiculoActual.tipo }}</p>
                        <p class="text-blue-800"><strong>Modelo:</strong> {{ vehiculoActual.marca }} {{ vehiculoActual.modelo }}</p>
                      </div>
                      <div>
                        <p class="text-blue-800"><strong>Placa:</strong> {{ vehiculoActual.placa }}</p>
                        <p class="text-blue-800"><strong>Precio por día:</strong> ${{ vehiculoActual.precio_dia }}</p>
                      </div>
                    </div>
                  </div>

                  <!-- Resumen Período -->
                  <div class="bg-green-50 rounded-xl p-6">
                    <h4 class="font-bold text-green-900 mb-3 text-lg">📅 Período del Contrato</h4>
                    <div class="grid md:grid-cols-2 gap-4">
                      <div>
                        <p class="text-green-800"><strong>Inicio:</strong> {{ dayjs(form.fecha_inicio).format('DD/MM/YYYY') }}</p>
                        <p class="text-green-800"><strong>Finalización:</strong> {{ dayjs(form.fecha_fin).format('DD/MM/YYYY') }}</p>
                      </div>
                      <div>
                        <p class="text-green-800"><strong>Duración:</strong> {{ diasContrato }} día{{ diasContrato !== 1 ? 's' : '' }}</p>
                        <p class="text-green-800"><strong>Total:</strong> ${{ costoTotal }}</p>
                      </div>
                    </div>
                  </div>

                  <!-- Resumen Pagos -->
                  <div class="bg-yellow-50 rounded-xl p-6">
                    <h4 class="font-bold text-yellow-900 mb-3 text-lg">💳 Configuración de Pagos</h4>
                    <div class="grid md:grid-cols-2 gap-4" v-if="distribucionPagos.pagos.length > 0">
                      <div>
                        <p class="text-yellow-800"><strong>Frecuencia:</strong> {{ frecuenciasValidas.find(f => f.id == form.frecuencia_pago_id)?.nombre }}</p>
                        <p class="text-yellow-800"><strong>Número de pagos:</strong> {{ distribucionPagos.pagos.length }}</p>
                      </div>
                      <div>
                        <p class="text-yellow-800"><strong>Total del contrato:</strong> ${{ costoTotal }}</p>
                        <p class="text-yellow-800"><strong>Distribuido en:</strong> {{ distribucionPagos.pagos.length }} pago{{ distribucionPagos.pagos.length !== 1 ? 's' : '' }}</p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Pago de Garantía -->
                <div class="bg-red-50 border-2 border-red-200 rounded-xl p-6">
                  <h4 class="font-bold text-red-900 mb-4 text-xl text-center">🛡️ Pago de Garantía Inmediato</h4>
                  <div class="text-center mb-6">
                    <div class="text-5xl font-bold text-red-600" v-if="vehiculoActual">
                      ${{ vehiculoActual.monto_garantia }}
                    </div>
                    <div class="text-red-700 mt-2 text-lg">
                      La garantía debe pagarse inmediatamente al confirmar el contrato
                    </div>
                    <div class="text-sm text-red-600 mt-2">
                      Esta garantía será devuelta al finalizar el contrato sin daños al vehículo
                    </div>
                  </div>
                  
                  <div class="flex items-start space-x-4">
                    <input v-model="form.pago_garantia_acepta" 
                           type="checkbox" 
                           class="w-6 h-6 text-red-600 focus:ring-red-500 border-gray-300 rounded mt-1"
                           required>
                    <div>
                      <label class="text-lg font-semibold text-red-800 cursor-pointer">
                        Acepto pagar la garantía de manera inmediata al confirmar este contrato
                      </label>
                      <p class="text-sm text-red-600 mt-2">
                        Entiendo que debo proceder al área de pagos inmediatamente después de confirmar este contrato.
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Navigation Buttons -->
              <div class="flex justify-between mt-10 pt-6 border-t-2 border-gray-100">
                <button v-if="currentStep > 1" 
                        @click="previousStep" 
                        type="button"
                        class="px-8 py-4 border-2 border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                  ← Anterior
                </button>
                <div v-else></div>
                
                <button v-if="currentStep < totalSteps" 
                        @click="nextStep" 
                        :disabled="!canProceedToStep"
                        type="button"
                        class="px-8 py-4 bg-green-600 text-white rounded-xl disabled:opacity-50 disabled:cursor-not-allowed hover:bg-green-700 transition-colors font-medium">
                  Siguiente →
                </button>
                
                <button v-if="currentStep === totalSteps" 
                        type="submit"
                        :disabled="!canProceedToStep"
                        class="px-10 py-4 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl disabled:opacity-50 disabled:cursor-not-allowed hover:from-green-700 hover:to-green-800 transition-all font-bold text-lg shadow-lg">
                  🚗 Confirmar Contrato
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>