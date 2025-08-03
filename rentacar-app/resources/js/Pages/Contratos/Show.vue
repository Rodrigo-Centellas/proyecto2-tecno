<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import dayjs from 'dayjs';
import 'dayjs/locale/es';

dayjs.locale('es');

const props = defineProps({
  contrato: Object,
  contratoData: Object,
});

const $page = usePage();

// Permisos / roles
const hasPermission = computed(() => (permission) => {
  const user = $page.props.auth.user;
  if (user?.roles?.some(role => role.name === 'Administrador')) return true;
  return user?.permissions?.includes(permission) || false;
});

// Cliente no debe ver editar si solo tiene rol Cliente
const isClienteOnly = computed(() => {
  const user = $page.props.auth.user;
  if (!user) return false;
  const roles = user?.roles?.map(r => r.name) || [];
  return roles.length === 1 && roles[0] === 'Cliente';
});

// Formateos
const formatDate = (date) => {
  return dayjs(date).format('DD [de] MMMM YYYY');
};

// Función de impresión
const imprimirContrato = () => {
  // Configurar opciones de impresión antes de imprimir
  const originalTitle = document.title;
  document.title = `Contrato-${props.contrato.id}`;
  
  // Agregar estilos específicos para impresión
  const printStyles = `
    <style>
      @media print {
        @page {
          margin: 2cm;
          size: A4;
        }
        body {
          font-family: 'Times New Roman', serif;
          font-size: 12pt;
          line-height: 1.4;
          color: black;
        }
        .print-container {
          max-width: none;
          margin: 0;
          padding: 0;
        }
        .no-print {
          display: none !important;
        }
        .print-page-break {
          page-break-before: always;
        }
        h1, h2, h3 {
          color: black;
        }
        .signature-section {
          margin-top: 3cm;
          page-break-inside: avoid;
        }
      }
    </style>
  `;
  
  // Insertar estilos en el head
  const head = document.getElementsByTagName('head')[0];
  const styleElement = document.createElement('div');
  styleElement.innerHTML = printStyles;
  head.appendChild(styleElement);
  
  // Imprimir
  window.print();
  
  // Limpiar después de imprimir
  setTimeout(() => {
    document.title = originalTitle;
    head.removeChild(styleElement);
  }, 1000);
};

// Función para descargar como PDF (opcional)
const descargarPDF = () => {
  // Esta función requeriría una librería como jsPDF o html2pdf
  // Por ahora, usamos la función de impresión del navegador
  // que permite "Guardar como PDF"
  alert('Use la función "Imprimir" y seleccione "Guardar como PDF" en las opciones de impresión.');
};
</script>

<template>
  <Head :title="`Contrato #${contrato.id}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between no-print">
        <div class="flex items-center space-x-3">
          <Link href="/contratos" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </Link>
          <div class="bg-blue-100 p-2 rounded-lg">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-gray-900">Contrato de Alquiler #{{ contrato.id }}</h2>
        </div>

        <div class="flex space-x-3">
          <!-- Botón de Imprimir -->
          <button
            @click="imprimirContrato"
            class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors flex items-center space-x-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Imprimir</span>
          </button>

          <!-- Botón de Descargar PDF -->
          <button
            @click="descargarPDF"
            class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors flex items-center space-x-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>PDF</span>
          </button>

          <!-- Editar solo si no es cliente único -->
          <Link
            v-if="!isClienteOnly && hasPermission('contratos.editar')"
            :href="`/contratos/${contrato.id}/edit`"
            class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition-colors flex items-center space-x-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            <span>Editar</span>
          </Link>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 print-container">

        <!-- Portada / Información inicial -->
        <section class="rounded-lg shadow-lg card-bg overflow-hidden print:shadow-none print:rounded-none">
          <div class="p-6">
            <div class="text-center border-b pb-6 mb-6">
              <p class="text-right text-sm mb-2">La Paz, {{ contratoData.fecha_actual }}</p>
              <h1 class="text-3xl font-bold mb-2 uppercase">CONTRATO DE ARRENDAMIENTO DE VEHÍCULO</h1>
              <p class="text-lg">Número de Contrato: <strong>{{ contratoData.numero_contrato }}</strong></p>
            </div>

            <!-- Definiciones -->
            <div class="mb-6">
              <h3 class="font-semibold text-lg mb-2">1. Definiciones</h3>
              <div class="ml-4 space-y-1 text-sm">
                <p><strong>Arrendatario:</strong> la persona o personas que suscriben el contrato como usuarios ({{ contrato.users.map(u => u.name + ' ' + u.apellido).join(', ') }}).</p>
                <p><strong>Propietario:</strong> RENTACAR EMPRESA S.R.L., representada por su Gerente General.</p>
                <p><strong>Vehículo:</strong> el bien descrito en la cláusula correspondiente, de propiedad del arrendador.</p>
                <p><strong>Periodo:</strong> el lapso comprendido entre la fecha de inicio y fin detallados en este contrato.</p>
              </div>
            </div>

            <!-- Partes -->
            <div class="space-y-4 mb-6">
              <h3 class="font-semibold text-lg">2. Comparecen</h3>
              <div class="ml-4">
                <p><strong>2.1.- EL PROPIETARIO:</strong> RENTACAR EMPRESA S.R.L., con NIT N° 1234567890, representada por su Gerente General.</p>
                <div v-if="contrato.users && contrato.users.length > 0" class="mt-2">
                  <p><strong>2.2.- EL ARRENDATARIO:</strong> {{ contrato.users[0].name }} {{ contrato.users[0].apellido }}, mayor de edad, con C.I. N° {{ contrato.users[0].ci }}, con domicilio en {{ contrato.users[0].domicilio }}.</p>
                </div>
              </div>
            </div>

            <!-- Identificación del vehículo -->
            <div class="space-y-3 mb-6">
              <h3 class="font-semibold text-lg">3. Identificación del Vehículo</h3>
              <div class="ml-4" v-if="contrato.vehiculo">
                <p>El vehículo objeto del presente contrato es marca <strong>{{ contrato.vehiculo.marca?.toUpperCase() }}</strong>, modelo <strong>{{ contrato.vehiculo.modelo?.toUpperCase() || 'N/A' }}</strong>, tipo <strong>{{ contrato.vehiculo.tipo?.toUpperCase() }}</strong>, placa de control <strong>{{ contrato.vehiculo.placa }}</strong>, libre de gravámenes y en condiciones operativas.</p>
              </div>
            </div>

            <!-- Objeto -->
            <div class="space-y-3 mb-6">
              <h3 class="font-semibold text-lg">4. Objeto del Contrato</h3>
              <div class="ml-4">
                <p>El propietario otorga en calidad de arrendamiento el vehículo descrito al arrendatario para uso autorizado exclusivamente dentro del territorio nacional boliviano, sin posibilidad de subarrendar, prestar o pignorar sin autorización expresa y escrita.</p>
              </div>
            </div>

            <!-- Plazo y precio -->
            <div class="space-y-3 mb-6">
              <h3 class="font-semibold text-lg">5. Plazo y Precio del Arrendamiento</h3>
              <div class="ml-4 space-y-2">
                <p><strong>5.1.-</strong> El plazo es de <strong>{{ contratoData.duracion_total }} día{{ contratoData.duracion_total !== 1 ? 's' : '' }}</strong> desde el <strong>{{ contratoData.fecha_inicio_formateada }}</strong> hasta el <strong>{{ contratoData.fecha_fin_formateada }}</strong>.</p>
                <p><strong>5.2.-</strong> El arrendatario pagará <strong>Bs. {{ contrato.vehiculo?.precio_dia }} ({{ contratoData.precio_dia_texto }})</strong> por día conforme a la frecuencia acordada.</p>
                <p><strong>5.3.-</strong> El monto total del contrato es de <strong>Bs. {{ contratoData.monto_total }} ({{ contratoData.monto_total_texto }})</strong>.</p>
              </div>
            </div>

            <!-- Obligaciones del arrendatario -->
            <div class="space-y-3 mb-6">
              <h3 class="font-semibold text-lg">6. Obligaciones del Arrendatario</h3>
              <div class="ml-4 space-y-2">
                <p><strong>6.1.-</strong> Revisar el vehículo y reportar irregularidades al momento de entrega.</p>
                <p><strong>6.2.-</strong> Responder por daños, multas o mal uso.</p>
                <p><strong>6.3.-</strong> Asumir sanciones de tránsito generadas durante el arrendamiento.</p>
                <p><strong>6.4.-</strong> Queda prohibido pignorar el vehículo sin autorización, de lo contrario se aplicará rescisión inmediata.</p>
                <p><strong>6.5.-</strong> Mantener el vehículo limpio y operable durante todo el periodo.</p>
              </div>
            </div>

            <!-- Garantía económica -->
            <div class="space-y-3 mb-6">
              <h3 class="font-semibold text-lg">7. Garantía Económica</h3>
              <div class="ml-4">
                <p><strong>7.1.-</strong> El arrendatario entrega en calidad de garantía la suma de <strong>Bs. {{ contrato.vehiculo?.monto_garantia }} ({{ contratoData.monto_garantia_texto }})</strong>, que será restituida si el vehículo se devuelve en buen estado y sin deudas pendientes.</p>
              </div>
            </div>

            <!-- Información de pago -->
            <div class="space-y-3 mb-6" v-if="contrato.nrocuenta">
              <h3 class="font-semibold text-lg">8. Información de Pago</h3>
              <div class="ml-4">
                <p><strong>8.1.-</strong> Los pagos se harán mediante depósito a la cuenta:</p>
                <div class="ml-4 mt-2 p-3 bg-gray-100 rounded print:bg-white print:border print:border-black">
                  <p><strong>Banco:</strong> {{ contrato.nrocuenta.banco }}</p>
                  <p><strong>Número de cuenta:</strong> {{ contrato.nrocuenta.nro_cuenta }}</p>
                  <p><strong>Titular:</strong> RentaCar Empresa S.R.L.</p>
                </div>
              </div>
            </div>

            <!-- Cláusulas adicionales dinámicas -->
            <div class="space-y-3 mb-6" v-if="contrato.contrato_clausulas && contrato.contrato_clausulas.length > 0">
              <h3 class="font-semibold text-lg">9. Cláusulas Adicionales</h3>
              <div class="ml-4 space-y-2">
                <div v-for="(clausula, index) in contrato.contrato_clausulas" :key="clausula.id">
                  <p><strong>9.{{ index + 1 }}.-</strong> {{ clausula.descripcion }}</p>
                </div>
              </div>
            </div>

            <!-- Anexos -->
            <div class="mb-6">
              <h3 class="font-semibold text-lg mb-2">10. Anexos</h3>
              <div class="ml-4 text-sm">
                <p>Se adjuntan como anexos:</p>
                <ul class="list-disc ml-6">
                  <li>Copias de cédula de identidad del arrendatario.</li>
                  <li>Fotografías del vehículo al momento de entrega.</li>
                  <li>Otros documentos acordados entre las partes.</li>
                </ul>
              </div>
            </div>

            <!-- Firma y metadata -->
            <div class="mt-12 space-y-8 signature-section">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="text-center">
                  <div class="border-t-2 border-black pt-2 mt-16">
                    <p class="font-bold">EL PROPIETARIO</p>
                    <p class="text-sm">RENTACAR EMPRESA S.R.L.</p>
                    <p class="text-sm">Gerente General</p>
                    <p class="text-sm">C.I.: _______________</p>
                  </div>
                </div>
                <div class="text-center" v-if="contrato.users && contrato.users.length > 0">
                  <div class="border-t-2 border-black pt-2 mt-16">
                    <p class="font-bold">EL ARRENDATARIO</p>
                    <p class="text-sm">{{ contrato.users[0].name }} {{ contrato.users[0].apellido }}</p>
                    <p class="text-sm">C.I.: {{ contrato.users[0].ci }}</p>
                  </div>
                </div>
              </div>

              <div class="border-t border-gray-200 pt-6 mt-6 text-sm text-gray-600 print:border-black">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <p><strong>Fecha de creación:</strong></p>
                    <p>{{ contratoData.fecha_actual }}</p>
                  </div>
                  <div>
                    <p><strong>Estado del contrato:</strong></p>
                    <p class="capitalize">{{ contrato.estado }}</p>
                  </div>
                  <div>
                    <p><strong>Duración total:</strong></p>
                    <p>{{ contratoData.duracion_total }} día{{ contratoData.duracion_total !== 1 ? 's' : '' }}</p>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </section>

        <!-- Información de pantalla (no impresa) -->
        <div class="mt-6 no-print">
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div>
                <p class="text-sm font-medium text-blue-700">ID del Contrato:</p>
                <p class="text-gray-900">#{{ contrato.id }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-blue-700">Estado:</p>
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                      :class="{
                        'bg-green-100 text-green-800': contrato.estado === 'activo',
                        'bg-yellow-100 text-yellow-800': contrato.estado === 'pendiente',
                        'bg-red-100 text-red-800': contrato.estado === 'vencido',
                        'bg-gray-100 text-gray-800': contrato.estado === 'finalizado'
                      }">
                  {{ contrato.estado }}
                </span>
              </div>
              <div v-if="contrato.vehiculo">
                <p class="text-sm font-medium text-blue-700">Vehículo:</p>
                <p class="text-gray-900">{{ contrato.vehiculo.marca }} {{ contrato.vehiculo.modelo }} - {{ contrato.vehiculo.placa }}</p>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
@media print {
  .no-print {
    display: none !important;
  }
  
  body {
    margin: 0;
    padding: 0;
    font-family: 'Times New Roman', serif;
    font-size: 12pt;
    line-height: 1.4;
    color: black;
  }
  
  .print-container {
    max-width: none;
    margin: 0;
    padding: 0;
  }
  
  .signature-section {
    page-break-inside: avoid;
    margin-top: 3cm;
  }
  
  h1, h2, h3 {
    color: black;
  }
  
  @page {
    margin: 2cm;
    size: A4;
  }
}

/* Estilos adicionales para mejorar la impresión */
.print\:shadow-none {
  box-shadow: none !important;
}

.print\:rounded-none {
  border-radius: 0 !important;
}

.print\:bg-white {
  background-color: white !important;
}

.print\:border {
  border: 1px solid black !important;
}

.print\:border-black {
  border-color: black !important;
}
</style>