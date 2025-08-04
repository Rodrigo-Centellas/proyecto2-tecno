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

          <!-- Buscador -->
          <div class="mb-4 max-w-md">
            <input
              v-model="search"
              type="text"
              placeholder="Buscar por id, cliente o vehiculo..."
              class="border rounded px-3 py-1 w-full"
            />
          </div>

          <!-- Tabla de pagos -->
          <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left font-medium text-gray-900 border">ID</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-900 border">Tipo</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-900 border">Método de Pago</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-900 border">Cliente</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-900 border">Vehículo</th>
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

                  <!-- Método de Pago -->
                  <td class="px-4 py-3 border">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                          :class="{
                            'bg-blue-100 text-blue-800': pago.metodo_pago === 'qr',
                            'bg-green-100 text-green-800': pago.metodo_pago === 'efectivo',
                          }">
                      {{ pago.metodo_pago }}
                    </span>
                  </td>

                  <!-- Cliente -->
                  <td class="px-4 py-3 border">
                    <div class="flex flex-col space-y-1">
                      <div class="font-semibold text-gray-900">
                        {{ obtenerNombreCliente(pago) }}
                      </div>
                      
                      <!-- Indicador de verificación -->
                      <div v-if="mostrarEstadoVerificacion(pago)" class="flex items-center">
                        <span v-if="estaVerificado(pago)" 
                              class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                          <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                          </svg>
                          Verificado
                        </span>
                        <span v-else 
                              class="inline-flex items-center px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                          <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                          </svg>
                          Pendiente verificación
                        </span>
                      </div>
                    </div>
                  </td>
                  
                  <!-- Vehículo -->
                  <td class="px-4 py-3 border">
                    <div class="font-semibold text-gray-900">
                      {{ obtenerPlacaVehiculo(pago) }}
                    </div>
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
                      
                      <!-- Botón Ver Detalle -->
                      <button
                        @click="verDetalle(pago)"
                        class="inline-flex items-center px-3 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Ver Detalle
                      </button>
                      
                      <!-- Botón Pagar (con validación de verificación) -->
                      <button
                        v-if="pago.estado === 'pendiente'"
                        @click="manejarPago(pago)"
                        :disabled="!puedeRealizarPago(pago)"
                        :class="[
                          'inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors shadow-sm',
                          puedeRealizarPago(pago) 
                            ? 'bg-blue-600 text-white hover:bg-blue-700' 
                            : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                        ]"
                        :title="obtenerTituloPago(pago)">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Pagar
                      </button>
                      
                      <!-- Badge de estado pagado -->
                      <span v-else-if="pago.estado === 'pagado'" 
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-green-700 bg-green-100 rounded-lg">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Pagado
                      </span>
                      
                      <!-- Badge de estado procesando -->
                      <span v-else-if="pago.estado === 'procesando'"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-700 bg-blue-100 rounded-lg">
                        <svg class="animate-spin w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        Procesando
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
                  <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                    No hay pagos registrados
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Swal from 'sweetalert2';
import { useBaseUrl } from '@/composables/useBaseUrl';
const { url } = useBaseUrl();

const props = defineProps({
  pagos: Array,
  filters: Object,
});

const page = usePage();
const search = ref(props.filters?.search || '');

watch(search, (val) => {
  router.get(url('/pagos'), { search: val }, { preserveState: true, replace: true });
});

// Funciones de navegación
const verDetalle = (pago) => {
  router.visit(url(`/pagos/${pago.id}`));
};

const irAPagar = (pago) => {
  router.visit(url(`/pagos/${pago.id}/pagar`));
};

// Funciones de formateo
const formatearMonto = (monto) => {
  return parseFloat(monto).toFixed(2);
};

// CORRECCIÓN: Función para obtener el usuario REAL con el campo verificado
const obtenerUsuarioPago = (pago) => {
  // PRIORIDAD CORRECTA: Buscar primero en las estructuras ORIGINALES
  // que tienen todos los campos, incluyendo 'verificado'
  
  // Para pagos de reserva: buscar en reserva.user
  if (pago.reserva?.user) {
    return pago.reserva.user;
  }
  
  // Para pagos de contrato: buscar en contrato.users (estructura original)
  // NOTA: Aquí necesitarías que el backend incluya el usuario completo
  // en lugar de solo name, email, apellido
  if (pago.contrato_id && pago.contrato?.user) {
    return pago.contrato.user;
  }
  
  // Fallback: estructura normalizada (pero sin campo verificado)
  if (pago.contrato?.users?.[0]) {
    return pago.contrato.users[0];
  }
  
  return null;
};

const obtenerNombreCliente = (pago) => {
  const usuario = obtenerUsuarioPago(pago);
  return usuario?.name || 'Cliente Desconocido';
};

const obtenerPlacaVehiculo = (pago) => {
  if (pago.contrato?.vehiculo?.placa) {
    return pago.contrato.vehiculo.placa;
  }
  if (pago.reserva?.vehiculo?.placa) {
    return pago.reserva.vehiculo.placa;
  }
  return 'Vehículo Desconocido';
};

// Funciones de verificación (CORREGIDAS)
const estaVerificado = (pago) => {
  const usuario = obtenerUsuarioPago(pago);
  if (!usuario) {
    return false;
  }
  
  // Verificar el campo 'verificado' del usuario REAL
  const verificado = usuario.verificado;
  if (!verificado) return false;
  
  // Verificar si está aprobado (case insensitive)
  return verificado.toString().toLowerCase().trim() === 'aprobado';
};

const mostrarEstadoVerificacion = (pago) => {
  // Solo mostrar estado de verificación para pagos pendientes
  return pago.estado === 'pendiente';
};

const puedeRealizarPago = (pago) => {
  // Solo puede pagar si el estado es pendiente Y el usuario está verificado
  return pago.estado === 'pendiente' && estaVerificado(pago);
};

const obtenerTituloPago = (pago) => {
  if (pago.estado !== 'pendiente') {
    return `Este pago está en estado: ${pago.estado}`;
  }
  
  if (!estaVerificado(pago)) {
    return 'El usuario debe estar verificado como "Aprobado" para poder realizar el pago';
  }
  
  return 'Realizar pago';
};

// Manejar click en el botón de pago
const manejarPago = (pago) => {
  if (!puedeRealizarPago(pago)) {
    Swal.fire({
      title: 'Usuario no verificado',
      text: 'El usuario debe estar verificado como "Aprobado" para poder realizar pagos.',
      icon: 'warning',
      confirmButtonText: 'Entendido'
    });
    return;
  }
  
  irAPagar(pago);
};
</script>