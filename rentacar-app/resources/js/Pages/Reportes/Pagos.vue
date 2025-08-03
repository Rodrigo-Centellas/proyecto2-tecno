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
  tiposContrato: { // en realidad son tipos de pago
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
const metodoPago = ref(props.filters.metodo_pago || '');
const estado = ref(props.filters.estado || '');

// Construir query params
const construirParametros = () => {
  const params = new URLSearchParams();
  if (desde.value) params.append('desde', desde.value);
  if (hasta.value) params.append('hasta', hasta.value);
  if (usuarioId.value) params.append('usuario_id', usuarioId.value);
  if (vehiculoId.value) params.append('vehiculo_id', vehiculoId.value);
  if (tipoPago.value) params.append('tipo_pago', tipoPago.value);
  if (metodoPago.value) params.append('metodo_pago', metodoPago.value);
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
  metodoPago.value = '';
  estado.value = '';
};

const validarFiltros = () => {
  if (!desde.value && !hasta.value && !usuarioId.value && !vehiculoId.value &&
      !tipoPago.value && !metodoPago.value && !estado.value) {
    alert('Por favor selecciona al menos un filtro antes de generar el reporte.');
    return false;
  }
  return true;
};

const exportarPdfValidado = () => {
  if (validarFiltros()) exportarPdf();
};

const exportarExcelValidado = () => {
  if (validarFiltros()) exportarExcel();
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
            <h1 class="font-bold text-main mb-2" style="font-size: calc(1em + 0.5rem);">
              Generar Reporte de Pagos
            </h1>
            <p class="text-main opacity-70" style="font-size: calc(1em - 0.125rem);">
              Selecciona los filtros para personalizar tu reporte
            </p>
          </div>

          <!-- Filtros -->
          <div class="grid gap-6 lg:grid-cols-3 mb-6">
            <!-- Fecha desde -->
            <div>
              <label class="block font-semibold text-main mb-2">Desde:</label>
              <input type="date" v-model="desde" class="w-full p-3 border rounded-lg" />
            </div>
            <div>
              <label class="block font-semibold text-main mb-2">Hasta:</label>
              <input type="date" v-model="hasta" class="w-full p-3 border rounded-lg" />
            </div>
            <div>
              <label class="block font-semibold text-main mb-2">Usuario:</label>
              <select v-model="usuarioId" class="w-full p-3 border rounded-lg">
                <option value="">Todos los usuarios</option>
                <option v-for="u in usuarios" :key="u.id" :value="u.id">{{ u.nombre_completo }}</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold text-main mb-2">Vehículo:</label>
              <select v-model="vehiculoId" class="w-full p-3 border rounded-lg">
                <option value="">Todos los vehículos</option>
                <option v-for="v in vehiculos" :key="v.id" :value="v.id">{{ v.descripcion }}</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold text-main mb-2">Tipo de Pago:</label>
              <select v-model="tipoPago" class="w-full p-3 border rounded-lg">
                <option value="">Todos</option>
                <option v-for="tipo in tiposContrato" :key="tipo" :value="tipo">{{ tipo }}</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold text-main mb-2">Método de Pago:</label>
              <select v-model="metodoPago" class="w-full p-3 border rounded-lg">
                <option value="">Todos</option>
                <option value="qr">QR</option>
                <option value="efectivo">Efectivo</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold text-main mb-2">Estado:</label>
              <select v-model="estado" class="w-full p-3 border rounded-lg">
                <option value="">Todos</option>
                <option value="pendiente">Pendiente</option>
                <option value="pagado">Pagado</option>
                <option value="procesando">Procesando</option>
                <option value="fallido">Fallido</option>
                <option value="vencido">Vencido</option>
                <option value="cancelado">Cancelado</option>
              </select>
            </div>
          </div>

          <!-- Acciones -->
          <div class="flex flex-col sm:flex-row gap-4 mb-6">
            <button
              @click="exportarPdfValidado"
              class="flex-1 px-6 py-3 bg-red-600 text-white rounded-lg flex items-center justify-center space-x-2"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
              <span>Descargar PDF</span>
            </button>
            <button
              @click="exportarExcelValidado"
              class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg flex items-center justify-center space-x-2"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 11-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
              </svg>
              <span>Descargar Excel</span>
            </button>
            <button
              @click="limpiarFiltros"
              class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg"
            >
              🔄 Limpiar filtros
            </button>
          </div>

          <!-- Ayuda -->
          <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
            <h4 class="font-semibold mb-2">💡 Información del Reporte</h4>
            <ul class="list-disc ml-5 text-sm">
              <li>Incluye: Fecha, Usuario, Vehículo, Tipo de Pago, Método de Pago, Fuente, Monto y Estado.</li>
              <li>Puedes combinar filtros para afinar resultados.</li>
              <li>Se requiere al menos un filtro para generar el reporte.</li>
              <li>El archivo lleva fecha y hora en el nombre.</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
