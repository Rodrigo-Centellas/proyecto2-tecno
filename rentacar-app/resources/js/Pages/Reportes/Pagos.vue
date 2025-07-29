<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  usuarios: {
    type: Array,
    default: () => []
  },
  vehiculos: {
    type: Array,
    default: () => []
  },
  tiposContrato: {
    type: Array,
    default: () => []
  },
  filters: {
    type: Object,
    default: () => ({})
  }
});

// Filtros reactivos
const desde = ref(props.filters.desde || '');
const hasta = ref(props.filters.hasta || '');
const usuarioId = ref(props.filters.usuario_id || '');
const vehiculoId = ref(props.filters.vehiculo_id || '');
const tipoPago = ref(props.filters.tipo_pago || '');
const tipoContrato = ref(props.filters.tipo_contrato || '');
const estado = ref(props.filters.estado || '');

// Función para construir parámetros
const construirParametros = () => {
  const params = new URLSearchParams();
  
  if (desde.value) params.append('desde', desde.value);
  if (hasta.value) params.append('hasta', hasta.value);
  if (usuarioId.value) params.append('usuario_id', usuarioId.value);
  if (vehiculoId.value) params.append('vehiculo_id', vehiculoId.value);
  if (tipoPago.value) params.append('tipo_pago', tipoPago.value);
  if (tipoContrato.value) params.append('tipo_contrato', tipoContrato.value);
  if (estado.value) params.append('estado', estado.value);
  
  return params.toString();
};

const exportarPdf = () => {
  const params = construirParametros();
  window.open(`/reportes/pagos/pdf?${params}`, '_blank');
};

const exportarExcel = () => {
  const params = construirParametros();
  window.open(`/reportes/pagos/excel?${params}`, '_blank');
};

const limpiarFiltros = () => {
  desde.value = '';
  hasta.value = '';
  usuarioId.value = '';
  vehiculoId.value = '';
  tipoPago.value = '';
  tipoContrato.value = '';
  estado.value = '';
};

// Validar que al menos un filtro esté seleccionado
const validarFiltros = () => {
  if (!desde.value && !hasta.value && !usuarioId.value && !vehiculoId.value && 
      !tipoPago.value && !tipoContrato.value && !estado.value) {
    alert('Por favor selecciona al menos un filtro antes de generar el reporte.');
    return false;
  }
  return true;
};

const exportarPdfValidado = () => {
  if (validarFiltros()) {
    exportarPdf();
  }
};

const exportarExcelValidado = () => {
  if (validarFiltros()) {
    exportarExcel();
  }
};
</script>

<template>
  <Head title="Reporte de Pagos" />

  <AuthenticatedLayout>
    <template #header>
      <h2
        class="text-xl font-semibold leading-tight text-main"
        style="font-size: calc(1em + 0.25rem);"
      >
        Reporte de Pagos
      </h2>
    </template>

    <div class="py-12 text-main">
      <div class="mx-auto max-w-6xl sm:px-6 lg:px-8">
        <div class="p-8 rounded-lg shadow-lg card-bg">
          <!-- Título -->
          <div class="mb-8 text-center">
            <h1
              class="font-bold text-main mb-2"
              style="font-size: calc(1em + 0.5rem);"
            >
              Generar Reporte de Pagos
            </h1>
            <p class="text-main opacity-70" style="font-size: calc(1em - 0.125rem);">
              Selecciona los filtros para personalizar tu reporte
            </p>
          </div>

          <!-- Filtros de Fechas -->
          <div class="mb-8">
            <h3 class="font-semibold text-main mb-4 border-b border-opacity-20 border-gray-300 pb-2" 
                style="font-size: calc(1em + 0.125rem);">
              📅 Filtros de Fecha
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label
                  class="block font-semibold text-main mb-2"
                  style="font-size: calc(1em - 0.075rem);"
                >
                  Desde:
                </label>
                <input
                  type="date"
                  v-model="desde"
                  class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 transition-colors"
                  style="font-size: inherit;"
                />
              </div>

              <div>
                <label
                  class="block font-semibold text-main mb-2"
                  style="font-size: calc(1em - 0.075rem);"
                >
                  Hasta:
                </label>
                <input
                  type="date"
                  v-model="hasta"
                  class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 transition-colors"
                  style="font-size: inherit;"
                />
              </div>
            </div>
          </div>

          <!-- Filtros Avanzados -->
          <div class="mb-8">
            <h3 class="font-semibold text-main mb-4 border-b border-opacity-20 border-gray-300 pb-2" 
                style="font-size: calc(1em + 0.125rem);">
              🔍 Filtros Avanzados
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <!-- Usuario -->
              <div>
                <label
                  class="block font-semibold text-main mb-2"
                  style="font-size: calc(1em - 0.075rem);"
                >
                  Usuario:
                </label>
                <select
                  v-model="usuarioId"
                  class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 transition-colors"
                  style="font-size: inherit;"
                >
                  <option value="">Todos los usuarios</option>
                  <option v-for="usuario in usuarios" :key="usuario.id" :value="usuario.id">
                    {{ usuario.nombre_completo }}
                  </option>
                </select>
              </div>

              <!-- Vehículo -->
              <div>
                <label
                  class="block font-semibold text-main mb-2"
                  style="font-size: calc(1em - 0.075rem);"
                >
                  Vehículo:
                </label>
                <select
                  v-model="vehiculoId"
                  class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 transition-colors"
                  style="font-size: inherit;"
                >
                  <option value="">Todos los vehículos</option>
                  <option v-for="vehiculo in vehiculos" :key="vehiculo.id" :value="vehiculo.id">
                    {{ vehiculo.descripcion }}
                  </option>
                </select>
              </div>

              <!-- Tipo de Pago -->
              <div>
                <label
                  class="block font-semibold text-main mb-2"
                  style="font-size: calc(1em - 0.075rem);"
                >
                  Tipo de Pago:
                </label>
                <select
                  v-model="tipoPago"
                  class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 transition-colors"
                  style="font-size: inherit;"
                >
                  <option value="">Todos los tipos</option>
                  <option value="contrato">Contrato</option>
                  <option value="garantia">Garantía</option>
                  <option value="reserva">Reserva</option>
                </select>
              </div>

              <!-- Tipo de Contrato -->
              <div>
                <label
                  class="block font-semibold text-main mb-2"
                  style="font-size: calc(1em - 0.075rem);"
                >
                  Tipo de Contrato:
                </label>
                <select
                  v-model="tipoContrato"
                  class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 transition-colors"
                  style="font-size: inherit;"
                >
                  <option value="">Todos los contratos</option>
                  <option v-for="tipo in tiposContrato" :key="tipo" :value="tipo">
                    {{ tipo }}
                  </option>
                </select>
              </div>

              <!-- Estado -->
              <div>
                <label
                  class="block font-semibold text-main mb-2"
                  style="font-size: calc(1em - 0.075rem);"
                >
                  Estado del Pago:
                </label>
                <select
                  v-model="estado"
                  class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 transition-colors"
                  style="font-size: inherit;"
                >
                  <option value="">Todos los estados</option>
                  <option value="pendiente">Pendiente</option>
                  <option value="pagado">Pagado</option>
                  <option value="procesando">Procesando</option>
                  <option value="fallido">Fallido</option>
                  <option value="vencido">Vencido</option>
                  <option value="cancelado">Cancelado</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Botones de Acción -->
          <div class="border-t border-opacity-20 border-gray-300 pt-6">
            <div class="flex flex-col sm:flex-row gap-4">
              <button
                @click="exportarPdfValidado"
                class="flex-1 px-6 py-3 text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors font-medium shadow-md hover:shadow-lg flex items-center justify-center space-x-2"
                style="font-size: calc(1em - 0.075rem);"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
                <span>Descargar PDF</span>
              </button>
              
              <button
                @click="exportarExcelValidado"
                class="flex-1 px-6 py-3 text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors font-medium shadow-md hover:shadow-lg flex items-center justify-center space-x-2"
                style="font-size: calc(1em - 0.075rem);"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 11-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
                <span>Descargar Excel</span>
              </button>
              
              <button
                @click="limpiarFiltros"
                class="px-6 py-3 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors font-medium shadow-md hover:shadow-lg"
                style="font-size: calc(1em - 0.075rem);"
              >
                🔄 Limpiar Filtros
              </button>
            </div>
          </div>

          <!-- Información de ayuda -->
          <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
            <h4 class="font-semibold text-blue-800 dark:text-blue-200 mb-2" style="font-size: calc(1em - 0.05rem);">
              💡 Información del Reporte
            </h4>
            <ul class="text-blue-700 dark:text-blue-300 text-sm space-y-1" style="font-size: calc(1em - 0.125rem);">
              <li>• El reporte incluye: Fecha, Usuario, Vehículo, Tipo de Pago, Tipo de Contrato, Monto y Estado</li>
              <li>• Puedes combinar múltiples filtros para obtener datos más específicos</li>
              <li>• Los reportes se generan con la fecha y hora actual en el nombre del archivo</li>
              <li>• Debes seleccionar al menos un filtro para generar el reporte</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>