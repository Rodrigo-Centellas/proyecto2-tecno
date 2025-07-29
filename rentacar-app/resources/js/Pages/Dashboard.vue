<script setup>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';

const props = defineProps({
  reservasPorEstado: Array,
  vehiculosPorEstado: Array,
  ingresosPorMes: Array,
  topVehiculos: Array,
});

// Refs para los canvas
const reservasChartRef     = ref(null);
const vehiculosChartRef    = ref(null);
const ingresosChartRef     = ref(null);
const topVehiculosChartRef = ref(null);

onMounted(() => {
  // 1. Pie chart: Reservas por estado
  new Chart(reservasChartRef.value, {
    type: 'pie',
    data: {
      labels: props.reservasPorEstado.map(r => r.estado),
      datasets: [{
        data:   props.reservasPorEstado.map(r => r.total),
        backgroundColor: ['#4ade80','#facc15','#f87171','#60a5fa','#a78bfa']
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { position: 'bottom' } }
    }
  });

  // 2. Bar chart: Vehículos por estado
  new Chart(vehiculosChartRef.value, {
    type: 'bar',
    data: {
      labels: props.vehiculosPorEstado.map(v => v.estado),
      datasets: [{
        label: 'Cantidad',
        data: props.vehiculosPorEstado.map(v => v.total),
        backgroundColor: '#60a5fa'
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true }
      },
      plugins: { legend: { display: false } }
    }
  });

  // 3. Line chart: Ingresos por mes
  new Chart(ingresosChartRef.value, {
    type: 'line',
    data: {
      labels: props.ingresosPorMes.map(i => i.mes),
      datasets: [{
        label: 'Ingresos $',
        data: props.ingresosPorMes.map(i => i.total),
        fill: false,
        borderColor: '#34d399',
        tension: 0.3,
        pointBackgroundColor: '#34d399'
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true }
      }
    }
  });

  // 4. Horizontal bar: Top 5 vehículos más reservados
  new Chart(topVehiculosChartRef.value, {
    type: 'bar',
    data: {
      labels: props.topVehiculos.map(v => v.vehiculo.tipo),
      datasets: [{
        label: 'Reservas',
        data: props.topVehiculos.map(v => v.total),
        backgroundColor: '#f472b6'
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      scales: {
        x: { beginAtZero: true }
      },
      plugins: { legend: { display: false } }
    }
  });
});
</script>

<template>
  <Head title="Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-main">Dashboard</h2>
    </template>

    <div class="py-12 space-y-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
        <!-- Reservas por Estado -->
        <div class="p-6 card-bg shadow-sm rounded-lg text-main">
          <h3 class="text-lg font-semibold mb-4">Reservas por Estado</h3>
          <canvas ref="reservasChartRef"></canvas>
        </div>

        <!-- Vehículos por Estado -->
        <div class="p-6 card-bg shadow-sm rounded-lg text-main">
          <h3 class="text-lg font-semibold mb-4">Vehículos por Estado</h3>
          <canvas ref="vehiculosChartRef"></canvas>
        </div>

        <!-- Ingresos por Mes -->
        <div class="p-6 card-bg shadow-sm rounded-lg text-main">
          <h3 class="text-lg font-semibold mb-4">Ingresos Totales por Mes</h3>
          <canvas ref="ingresosChartRef"></canvas>
        </div>

        <!-- Top 5 Vehículos Más Reservados -->
        <div class="p-6 card-bg shadow-sm rounded-lg text-main">
          <h3 class="text-lg font-semibold mb-4">Top 5 Vehículos Más Reservados</h3>
          <canvas ref="topVehiculosChartRef"></canvas>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
