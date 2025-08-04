<template>
  <Head :title="`Pago #${pago.id}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-xl font-semibold leading-tight text-main">
            Detalle del Pago #{{ pago.id }}
          </h2>
        </div>
        <button
          @click="$inertia.visit(url('/pagos')"
          class="inline-flex items-center px-4 py-2 rounded-lg transition-colors font-medium shadow-sm bg-muted hover:bg-muted-hover text-button"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Volver al Listado
        </button>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Contenedor del pago -->
        <div class="p-6 mb-6 rounded-2xl shadow-lg card-bg transition-colors">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Información del Pago -->
            <div class="space-y-4">
              <h3 class="text-lg font-semibold mb-4 text-main">Información del Pago</h3>

              <div class="flex justify-between">
                <span class="text-sm text-secondary">ID:</span>
                <span class="font-mono font-semibold text-main">#{{ pago.id }}</span>
              </div>

              <div class="flex justify-between">
                <span class="text-sm text-secondary">Tipo:</span>
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                      :class="{
                        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': pago.tipo_pago === 'contrato',
                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': pago.tipo_pago === 'garantia',
                        'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300': pago.tipo_pago === 'reserva'
                      }">
                  {{ pago.tipo_pago }}
                </span>
              </div>

              <div class="flex justify-between">
                <span class="text-sm text-secondary">Método de pago:</span>
                <span class="font-semibold capitalize text-main">
                  {{ pago.metodo_pago || 'qr' }}
                </span>
              </div>

              <div class="flex justify-between">
                <span class="text-sm text-secondary">Monto:</span>
                <span class="text-2xl font-bold text-primary">
                  Bs. {{ formatearMonto(pago.monto) }}
                </span>
              </div>

              <div class="flex justify-between">
                <span class="text-sm text-secondary">Estado:</span>
                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
                      :class="{
                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': pago.estado === 'pagado',
                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': pago.estado === 'pendiente',
                        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': pago.estado === 'procesando',
                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': pago.estado === 'fallido',
                        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': pago.estado === 'vencido'
                      }">
                  {{ pago.estado }}
                </span>
              </div>

              <div class="flex justify-between">
                <span class="text-sm text-secondary">Fecha de Creación:</span>
                <span class="font-medium text-main">
                  {{ formatearFecha(pago.created_at) }}
                </span>
              </div>

              <div v-if="pago.pagofacil_transaction_id" class="flex justify-between">
                <span class="text-sm text-secondary">ID Transacción:</span>
                <span class="font-mono text-sm text-main">
                  {{ pago.pagofacil_transaction_id }}
                </span>
              </div>
            </div>

            <!-- Detalles Asociados -->
            <div class="space-y-4">
              <h3 class="text-lg font-semibold mb-4 text-main">Detalles Asociados</h3>

              <div class="rounded-lg p-4 bg-muted-surface transition-colors">
                <h4 class="font-medium mb-2 text-main">Cliente</h4>
                <p class="text-base text-main">
                  {{ pago.contrato?.users?.[0]?.name || 'Cliente Desconocido' }}
                </p>
                <p class="text-sm text-secondary">
                  {{ pago.contrato?.users?.[0]?.email || '' }}
                </p>
              </div>

              <div class="rounded-lg p-4 bg-muted-surface transition-colors">
                <h4 class="font-medium mb-2 text-main">Vehículo</h4>
                <p class="text-base text-main">
                  {{ pago.contrato?.vehiculo?.placa || 'Vehículo Desconocido' }}
                </p>
                <p class="text-sm text-secondary">
                  {{ pago.contrato?.vehiculo?.marca || '' }}
                  {{ pago.contrato?.vehiculo?.modelo || '' }}
                  {{ pago.contrato?.vehiculo?.tipo || '' }}
                </p>
              </div>

              <div v-if="pago.contrato_id" class="rounded-lg p-4 bg-muted-surface transition-colors">
                <h4 class="font-medium mb-2 text-main">Contrato</h4>
                <p class="text-base text-main">ID: #{{ pago.contrato_id }}</p>
              </div>

              <div v-if="pago.reserva_id" class="rounded-lg p-4 bg-muted-surface transition-colors">
                <h4 class="font-medium mb-2 text-main">Reserva</h4>
                <p class="text-base text-main">ID: #{{ pago.reserva_id }}</p>
              </div>
            </div>

          </div>
        </div>
        <!-- sin acciones extra -->
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useBaseUrl } from '@/composables/useBaseUrl';
const { url } = useBaseUrl();

const props = defineProps({
  pago: Object,
});

const formatearMonto = (monto) => parseFloat(monto).toFixed(2);
const formatearFecha = (fecha) =>
  new Date(fecha).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
</script>
