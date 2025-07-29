<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
  contrato: Object,
  vehiculos: Array,
  garantes: Array,
  nro_cuentas: Array,
  frecuencia_pagos: Array,
  clausulas: Array,
});

// ✅ CAMPOS EDITABLES (solo información administrativa)
const estado = ref(props.contrato.estado);
const fecha_fin = ref(props.contrato.fecha_fin); // Solo si el contrato está activo

// ✅ CAMPOS NO EDITABLES (información contractual crítica)
const fecha_inicio = ref(props.contrato.fecha_inicio);
const frecuencia_pago_id = ref(props.contrato.frecuencia_pago_id);
const nro_cuenta_id = ref(props.contrato.nro_cuenta_id);
const garante_id = ref(props.contrato.garante_id);
const vehiculo_id = ref(props.contrato.vehiculo_id);

// Clausulas seleccionadas (solo lectura)
const clausula_ids = ref(props.contrato.contrato_clausulas.map(cl => cl.id));

// Sincronizamos cuando el contrato cambie
watch(() => props.contrato, c => {
  estado.value = c.estado;
  fecha_fin.value = c.fecha_fin;
  fecha_inicio.value = c.fecha_inicio;
  frecuencia_pago_id.value = c.frecuencia_pago_id;
  nro_cuenta_id.value = c.nro_cuenta_id;
  garante_id.value = c.garante_id;
  vehiculo_id.value = c.vehiculo_id;
  clausula_ids.value = c.contrato_clausulas.map(cl => cl.id);
});

// ✅ Función para enviar solo cambios permitidos
const enviar = () => {
  // Mostrar confirmación para cambios críticos
  if (fecha_fin.value !== props.contrato.fecha_fin) {
    Swal.fire({
      title: '¿Estás seguro?',
      text: 'Cambiar la fecha de fin del contrato puede tener implicaciones legales.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, actualizar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        actualizarContrato();
      }
    });
  } else {
    actualizarContrato();
  }
};

const actualizarContrato = () => {
  router.put(`/contratos/${props.contrato.id}`, {
    estado: estado.value,
    fecha_fin: fecha_fin.value, // Solo si realmente es necesario permitir este cambio
  }, {
    onSuccess: () => Swal.fire({
      title: '¡Actualizado!',
      text: 'El contrato ha sido actualizado correctamente.',
      icon: 'success'
    }).then(() => router.visit('/contratos')),
    onError: (errors) => {
      console.error('Errores:', errors);
      Swal.fire('Error', 'Problema al actualizar el contrato.', 'error');
    },
  });
};

// Helper para mostrar información de solo lectura
const formatearFecha = (fecha) => {
  return new Date(fecha).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const obtenerNombreVehiculo = () => {
  const vehiculo = props.vehiculos.find(v => v.id === vehiculo_id.value);
  return vehiculo ? `${vehiculo.marca} ${vehiculo.modelo} - ${vehiculo.placa}` : 'No asignado';
};

const obtenerNombreCuenta = () => {
  const cuenta = props.nro_cuentas.find(c => c.id === nro_cuenta_id.value);
  return cuenta ? `${cuenta.banco} - ${cuenta.nro_cuenta}` : 'No asignada';
};

const obtenerNombreFrecuencia = () => {
  const frecuencia = props.frecuencia_pagos.find(f => f.id === frecuencia_pago_id.value);
  return frecuencia ? `${frecuencia.nombre} (${frecuencia.frecuencia_dias} días)` : 'No asignada';
};
</script>

<template>

  <Head :title="`Editar Contrato #${props.contrato.id}`" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          Editar Contrato #{{ props.contrato.id }}
        </h2>
        <span class="px-3 py-1 text-sm rounded-full" :class="{
          'bg-green-100 text-green-800': props.contrato.estado === 'vigente',
          'bg-blue-100 text-blue-800': props.contrato.estado === 'cumplido',
          'bg-red-100 text-red-800': props.contrato.estado === 'vencido',
          'bg-yellow-100 text-yellow-800': props.contrato.estado === 'pendiente de aprobacion'
        }">
          {{ props.contrato.estado }}
        </span>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">

        <!-- Advertencia de Seguridad -->
        <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                  clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-amber-700">
                <strong>Precaución:</strong> Solo se pueden modificar campos administrativos.
                Los términos contractuales (vehículo, cuenta bancaria, etc.) están protegidos por razones legales.
              </p>
            </div>
          </div>
        </div>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">

          <!-- Sección de Campos Editables -->
          <div class="bg-blue-50 px-6 py-3 border-b">
            <h3 class="text-lg font-medium text-blue-900">📝 Información Editable</h3>
          </div>

          <form @submit.prevent="enviar" class="p-6 space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Estado del Contrato -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Estado del Contrato</label>
                <select v-model="estado"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200">
                  <option value="" disabled>-- Seleccionar estado --</option>
                  <option value="vigente">Vigente</option>
                  <option value="cumplido">Cumplido</option>
                  <option value="vencido">Vencido</option>
                  <option value="pendiente de aprobacion">Pendiente de Aprobación</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Actualiza el estado administrativo del contrato</p>
              </div>

              <!-- Fecha Fin (con precaución) -->

            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
              <button type="button" @click="router.visit('/contratos')"
                class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition duration-200">
                Cancelar
              </button>
              <button type="submit"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>Actualizar Contrato</span>
              </button>
            </div>
          </form>
        </div>

        <!-- Sección de Información No Editable -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden mt-6">

          <div class="bg-gray-50 px-6 py-3 border-b">
            <h3 class="text-lg font-medium text-gray-900">🔒 Información Contractual (Solo Lectura)</h3>
          </div>

          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

              <!-- Fecha de Inicio -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Inicio</label>
                <div class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 text-gray-700">
                  {{ formatearFecha(fecha_inicio) }}
                </div>
                <p class="text-xs text-gray-500 mt-1">No se puede modificar después de la creación</p>
              </div>

              <!-- Fecha de Finalización -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Fin</label>
                <div class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 text-gray-700">
                  {{ formatearFecha(fecha_fin) }}
                </div>
                <p class="text-xs text-gray-500 mt-1">No se puede modificar después de la creación</p>
              </div>

              <!-- Vehículo Asignado -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Vehículo Asignado</label>
                <div class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 text-gray-700">
                  {{ obtenerNombreVehiculo() }}
                </div>
                <p class="text-xs text-gray-500 mt-1">Cambios requieren nuevo contrato</p>
              </div>

              <!-- Cuenta Bancaria -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cuenta de Pago</label>
                <div class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 text-gray-700">
                  {{ obtenerNombreCuenta() }}
                </div>
                <p class="text-xs text-gray-500 mt-1">Información financiera protegida</p>
              </div>

              <!-- Frecuencia de Pago -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Frecuencia de Pago</label>
                <div class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 text-gray-700">
                  {{ obtenerNombreFrecuencia() }}
                </div>
                <p class="text-xs text-gray-500 mt-1">Términos contractuales fijos</p>
              </div>

            </div>

            <!-- Cláusulas del Contrato -->
            <div class="mt-6">
              <label class="block text-sm font-medium text-gray-700 mb-3">Cláusulas del Contrato</label>
              <div class="bg-gray-50 rounded-lg p-4 max-h-48 overflow-y-auto">
                <div v-if="clausula_ids.length === 0" class="text-gray-500 text-sm">
                  No hay cláusulas asociadas a este contrato
                </div>
                <div v-else class="space-y-2">
                  <div v-for="(clausulaId, index) in clausula_ids" :key="clausulaId"
                    class="flex items-start space-x-3 p-2 bg-white rounded border">
                    <span
                      class="flex-shrink-0 w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-medium">
                      {{ index + 1 }}
                    </span>
                    <p class="text-sm text-gray-700">
                      {{props.clausulas.find(c => c.id === clausulaId)?.descripcion || 'Cláusula no encontrada'}}
                    </p>
                  </div>
                </div>
              </div>
              <p class="text-xs text-gray-500 mt-2">Las cláusulas no pueden modificarse una vez firmado el contrato</p>
            </div>

          </div>
        </div>

        <!-- Nota Legal -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mt-6">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                  clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">Nota Legal Importante</h3>
              <p class="text-sm text-red-700 mt-1">
                Este contrato es un documento legal vinculante. Para modificaciones mayores (vehículo, términos de pago,
                cláusulas),
                se requiere la creación de un nuevo contrato o una adenda legal firmada por ambas partes.
              </p>
            </div>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>