<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { format } from 'date-fns';

const props = defineProps({
  reserva: Object,
});

const formatFecha = (fecha) =>
  format(new Date(fecha), 'dd/MM/yyyy');
</script>

<template>
  <Head title="Detalle de Reserva" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-main">
        Detalle de Reserva
      </h2>
    </template>

    <div class="py-12 text-main">
      <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
        <div class="p-8 rounded-lg shadow-lg card-bg space-y-6">
          <h1 class="font-bold text-3xl">
            Reserva #{{ reserva.id }}
          </h1>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <p class="text-sm opacity-75">Vehículo</p>
              <p class="font-medium">{{ reserva.vehiculo?.tipo }}</p>
            </div>
            <div>
              <p class="text-sm opacity-75">Usuario</p>
              <p class="font-medium">{{ reserva.user?.name }}</p>
            </div>
            <div>
              <p class="text-sm opacity-75">Email</p>
              <p>{{ reserva.user?.email }}</p>
            </div>
            <div>
              <p class="text-sm opacity-75">Fecha</p>
              <p>{{ formatFecha(reserva.fecha) }}</p>
            </div>
            <div>
              <p class="text-sm opacity-75">Estado</p>
              <span
                class="inline-block px-2 py-1 rounded bg-yellow-100 text-yellow-800"
                style="font-size: calc(1em - 0.075rem);"
              >
                {{ reserva.estado }}
              </span>
            </div>
            <div>
              <p class="text-sm opacity-75">Precio/día</p>
              <p class="font-bold text-green-600">
                ${{ reserva.vehiculo?.precio_dia }}
              </p>
            </div>
            <div>
              <p class="text-sm opacity-75">Garantía</p>
              <p class="font-bold text-blue-600">
                ${{ reserva.vehiculo?.monto_garantia }}
              </p>
            </div>
          </div>

          <a
            href="/reservas"
            class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
            style="font-size: inherit;"
          >
            ← Volver a Reservas
          </a>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
