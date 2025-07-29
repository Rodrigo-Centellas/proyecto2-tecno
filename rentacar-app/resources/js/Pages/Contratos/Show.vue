<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// Importar estilos CSS para impresión
import '../../../css/contrato-print.css';

const props = defineProps({
  contrato: Object,
  contratoData: Object,
});

const imprimirContrato = () => {
  window.print();
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
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
          </Link>
          <div class="bg-blue-100 p-2 rounded-lg">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-gray-900">Contrato de Alquiler #{{ contrato.id }}</h2>
        </div>
        
        <div class="flex space-x-3">
          <button @click="imprimirContrato" 
                  class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            <span>Imprimir</span>
          </button>
          
          <Link :href="`/contratos/${contrato.id}/edit`"
                class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition-colors flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            <span>Editar</span>
          </Link>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Documento del Contrato -->
        <div class="print-area bg-white shadow-2xl rounded-2xl overflow-hidden print:shadow-none print:rounded-none">
          
          <!-- Contenido del Documento -->
          <div class="p-8 space-y-6 print:space-y-4">
            
            <!-- Header -->
            <div class="text-center border-b-2 border-gray-300 pb-6">
              <p class="text-right text-sm mb-4">La Paz, {{ contratoData.fecha_actual }}</p>
              <h1 class="text-3xl font-bold mb-2 uppercase">CONTRATO DE ARRENDAMIENTO DE VEHÍCULO</h1>
              <p class="text-lg">Entre: <strong>PROPIETARIO – ARRENDATARIO</strong></p>
              <p class="text-sm mt-2">Número de Contrato: {{ contratoData.numero_contrato }}</p>
            </div>

            <!-- COMPARECEN -->
            <div class="space-y-4">
              <h2 class="text-xl font-bold text-center">COMPARECEN:</h2>
              
              <div class="space-y-4">
                <h3 class="text-lg font-bold">PRIMERA. - PARTES CONTRATANTES</h3>
                
                <div class="ml-4 space-y-3">
                  <div>
                    <p><strong>1.1.- EL PROPIETARIO:</strong> <strong>RENTACAR EMPRESA S.R.L.</strong>, 
                    con NIT N° 1234567890, representada por su Gerente General, declara ser el legítimo 
                    propietario del vehículo objeto del presente contrato.</p>
                  </div>
                  
                  <div v-if="contrato.users && contrato.users.length > 0">
                    <p><strong>1.2.- EL ARRENDATARIO:</strong> {{ contrato.users[0].name }} {{ contrato.users[0].apellido }}, 
                    mayor de edad, con C.I. N° {{ contrato.users[0].ci }}, con domicilio en {{ contrato.users[0].domicilio }}, 
                    quien usa el vehículo para fines personales, bajo las condiciones del presente contrato.</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- IDENTIFICACIÓN DEL VEHÍCULO -->
            <div class="space-y-3">
              <h3 class="text-lg font-bold">SEGUNDA. - IDENTIFICACIÓN DEL VEHÍCULO</h3>
              <div class="ml-4" v-if="contrato.vehiculo">
                <p class="text-justify">EL PROPIETARIO declara ser dueño del vehículo marca 
                <strong>{{ contrato.vehiculo.marca?.toUpperCase() }}</strong>, 
                modelo <strong>{{ contrato.vehiculo.modelo?.toUpperCase() || 'N/A' }}</strong>, 
                tipo <strong>{{ contrato.vehiculo.tipo?.toUpperCase() }}</strong>, 
                placa de control <strong>{{ contrato.vehiculo.placa }}</strong>, 
                libre de gravámenes y en condiciones operativas.</p>
              </div>
            </div>

            <!-- OBJETO DEL CONTRATO -->
            <div class="space-y-3">
              <h3 class="text-lg font-bold">TERCERA. - OBJETO DEL CONTRATO</h3>
              <div class="ml-4">
                <p class="text-justify">EL PROPIETARIO otorga en calidad de arrendamiento el vehículo 
                descrito al ARRENDATARIO, para uso autorizado exclusivamente dentro del territorio 
                nacional boliviano, dando como límites de circulación las fronteras nacionales, 
                sin posibilidad de subarrendar, prestar, pignorar o entregar el vehículo como garantía 
                de deuda o prenda sin autorización expresa y escrita de EL PROPIETARIO.</p>
              </div>
            </div>

            <!-- PLAZO Y PRECIO -->
            <div class="space-y-3">
              <h3 class="text-lg font-bold">CUARTA. - PLAZO Y PRECIO DEL ARRENDAMIENTO</h3>
              <div class="ml-4 space-y-2">
                <p><strong>4.1.-</strong> El plazo del presente contrato es de <strong>{{ contratoData.duracion_total }} día{{ contratoData.duracion_total !== 1 ? 's' : '' }}</strong> 
                a partir del <strong>{{ contratoData.fecha_inicio_formateada }}</strong> 
                hasta el <strong>{{ contratoData.fecha_fin_formateada }}</strong>.</p>
                
                <p><strong>4.2.-</strong> EL ARRENDATARIO pagará 
                <strong>Bs. {{ contrato.vehiculo?.precio_dia }} ({{ contratoData.precio_dia_texto }})</strong> 
                por día, mediante depósito {{ contrato.frecuencia_pago?.nombre?.toLowerCase() || 'según frecuencia acordada' }} 
                a la cuenta bancaria de EL PROPIETARIO.</p>
                
                <p><strong>4.3.-</strong> El monto total del contrato asciende a 
                <strong>Bs. {{ contratoData.monto_total }} ({{ contratoData.monto_total_texto }})</strong>.</p>
              </div>
            </div>

            <!-- OBLIGACIONES DEL ARRENDATARIO -->
            <div class="space-y-3">
              <h3 class="text-lg font-bold">QUINTA. - OBLIGACIONES DEL ARRENDATARIO</h3>
              <div class="ml-4 space-y-2">
                <p><strong>5.1.-</strong> Revisar el vehículo y reportar fallas o irregularidades al momento de la entrega.</p>
                <p><strong>5.2.-</strong> Responder por cualquier daño, accidente o multa ocasionada por su acción, omisión, mal uso, estado de ebriedad o negligencia.</p>
                <p><strong>5.3.-</strong> Asumir todas las sanciones de tránsito generadas durante el periodo de alquiler.</p>
                <p><strong>5.4.-</strong> Queda absolutamente prohibido pignorar, empeñar o entregar el vehículo como garantía a terceros, bajo pena de rescisión inmediata del contrato y denuncia penal por apropiación indebida conforme al art. 345 del Código Penal Boliviano.</p>
                <p><strong>5.5.-</strong> Mantener el vehículo en condiciones adecuadas de limpieza y funcionamiento durante todo el período de arrendamiento.</p>
              </div>
            </div>

            <!-- GARANTÍA ECONÓMICA -->
            <div class="space-y-3">
              <h3 class="text-lg font-bold">SEXTA. - GARANTÍA ECONÓMICA</h3>
              <div class="ml-4">
                <p><strong>6.1.-</strong> EL ARRENDATARIO entrega en calidad de garantía la suma de 
                <strong>Bs. {{ contrato.vehiculo?.monto_garantia }} ({{ contratoData.monto_garantia_texto }})</strong>, 
                que será restituida únicamente si devuelve el vehículo en buen estado y sin multas pendientes. 
                En caso de daños, el importe será descontado proporcionalmente.</p>
              </div>
            </div>

            <!-- INFORMACIÓN BANCARIA -->
            <div class="space-y-3" v-if="contrato.nro_cuenta">
              <h3 class="text-lg font-bold">SÉPTIMA. - INFORMACIÓN DE PAGO</h3>
              <div class="ml-4">
                <p><strong>7.1.-</strong> Los pagos se realizarán mediante depósito a la cuenta bancaria:</p>
                <div class="ml-4 mt-2 p-3 bg-gray-100 rounded">
                  <p><strong>Banco:</strong> {{ contrato.nro_cuenta.banco }}</p>
                  <p><strong>Número de cuenta:</strong> {{ contrato.nro_cuenta.nro_cuenta }}</p>
                  <p><strong>Titular:</strong> RentaCar Empresa S.R.L.</p>
                </div>
              </div>
            </div>

            <!-- PROHIBICIÓN DE USO ILÍCITO -->
            <div class="space-y-3">
              <h3 class="text-lg font-bold">OCTAVA. - PROHIBICIÓN DE USO ILÍCITO</h3>
              <div class="ml-4 space-y-2">
                <p><strong>8.1.-</strong> Queda estrictamente prohibido el uso del vehículo para actividades ilícitas como contrabando, narcotráfico, robo, trata y tráfico de personas, conforme al art. 20, 23 y 106 del Código Penal Boliviano.</p>
                <p><strong>8.2.-</strong> En caso de que el vehículo se vea involucrado en hechos delictivos, EL ARRENDATARIO asume toda la responsabilidad civil y penal, deslindando completamente a EL PROPIETARIO.</p>
              </div>
            </div>

            <!-- CLÁUSULAS ADICIONALES -->
            <div class="space-y-3" v-if="contrato.contrato_clausulas && contrato.contrato_clausulas.length > 0">
              <h3 class="text-lg font-bold">NOVENA. - CLÁUSULAS ADICIONALES</h3>
              <div class="ml-4 space-y-3">
                <div v-for="(clausula, index) in contrato.contrato_clausulas" :key="clausula.id">
                  <p><strong>9.{{ index + 1 }}.-</strong> {{ clausula.descripcion }}</p>
                </div>
              </div>
            </div>

            <!-- RESCISIÓN DEL CONTRATO -->
            <div class="space-y-3">
              <h3 class="text-lg font-bold">DÉCIMA. - RESCISIÓN DEL CONTRATO</h3>
              <div class="ml-4 space-y-2">
                <p><strong>10.1.-</strong> EL PROPIETARIO podrá rescindir el presente contrato en caso de:</p>
                <div class="ml-4">
                  <p>a) Incumplimiento en el pago de la renta;</p>
                  <p>b) Mal uso del vehículo;</p>
                  <p>c) Uso en actividades no autorizadas;</p>
                  <p>d) Prenda o intento de transferencia del vehículo sin autorización expresa;</p>
                  <p>e) Incumplimiento de tiempo de Contrato.</p>
                </div>
                <p><strong>10.2.-</strong> En caso de rescisión por incumplimiento, se aplicará una penalidad según las condiciones establecidas en las cláusulas adicionales.</p>
              </div>
            </div>

            <!-- ENTREGA Y DEVOLUCIÓN -->
            <div class="space-y-3">
              <h3 class="text-lg font-bold">DÉCIMA PRIMERA. - ENTREGA Y DEVOLUCIÓN</h3>
              <div class="ml-4 space-y-2">
                <p><strong>11.1.-</strong> El vehículo será entregado en condiciones operativas, con accesorios completos. Su devolución deberá realizarse en igual estado.</p>
                <p><strong>11.2.-</strong> Se levantará un acta de entrega-recepción detallando el estado del vehículo, la cual será firmada por ambas partes.</p>
                <p><strong>11.3.-</strong> La devolución tardía generará un costo adicional de Bs. {{ contrato.vehiculo?.precio_dia || 0 }} por día de retraso.</p>
              </div>
            </div>

            <!-- JURISDICCIÓN Y LEY APLICABLE -->
            <div class="space-y-3">
              <h3 class="text-lg font-bold">DÉCIMA SEGUNDA. - JURISDICCIÓN Y LEY APLICABLE</h3>
              <div class="ml-4 space-y-2">
                <p><strong>12.1.-</strong> Para todos los efectos legales del presente contrato, las partes se someten a la jurisdicción de los tribunales de la ciudad de La Paz, renunciando expresamente a cualquier otro fuero que pudiera corresponderles.</p>
                <p><strong>12.2.-</strong> El presente contrato se rige por las leyes del Estado Plurinacional de Bolivia.</p>
              </div>
            </div>

            <!-- ACEPTACIÓN Y CONFORMIDAD -->
            <div class="space-y-3">
              <h3 class="text-lg font-bold">DÉCIMA TERCERA. - ACEPTACIÓN Y CONFORMIDAD</h3>
              <div class="ml-4">
                <p><strong>13.1.-</strong> Las partes declaran haber leído íntegramente el presente contrato, estar conformes con su contenido y lo suscriben en señal de aceptación y conformidad.</p>
              </div>
            </div>

            <!-- FIRMAS -->
            <div class="mt-12 space-y-8">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Firma del Propietario -->
                <div class="text-center space-y-4">
                  <div class="border-t-2 border-black pt-2">
                    <p class="font-bold">EL PROPIETARIO</p>
                    <p class="text-sm">RENTACAR EMPRESA S.R.L.</p>
                    <p class="text-sm">Gerente General</p>
                    <p class="text-sm">C.I.: _______________</p>
                  </div>
                </div>

                <!-- Firma del Arrendatario -->
                <div class="text-center space-y-4" v-if="contrato.users && contrato.users.length > 0">
                  <div class="border-t-2 border-black pt-2">
                    <p class="font-bold">EL ARRENDATARIO</p>
                    <p class="text-sm">{{ contrato.users[0].name }} {{ contrato.users[0].apellido }}</p>
                    <p class="text-sm">C.I.: {{ contrato.users[0].ci }}</p>
                  </div>
                </div>
              </div>

              <!-- Información adicional del contrato -->
              <div class="border-t-2 border-gray-200 pt-6 mt-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600">
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
        </div>

        <!-- Información adicional para pantalla (no se imprime) -->
        <div class="mt-8 no-print">
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-blue-900 mb-4">Información del Contrato</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div>
                <p class="text-sm font-medium text-blue-700">ID del Contrato:</p>
                <p class="text-gray-900">{{ contrato.id }}</p>
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
  
  .print-area {
    box-shadow: none !important;
    border-radius: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
  }
  
  body {
    margin: 0;
    padding: 0;
  }
}
</style>