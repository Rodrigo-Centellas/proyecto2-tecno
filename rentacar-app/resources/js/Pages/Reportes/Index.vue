<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
  <Head title="Reportes" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-main" style="font-size: calc(1em + 0.25rem);">
        Módulo de Reportes
      </h2>
    </template>

    <div class="py-12 text-main">
      <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
        <div class="p-8 rounded-lg shadow-lg card-bg">
          <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <!-- Tarjeta: Reporte de Pagos -->
            <Link
              href="/reportes/pagos"
              class="block p-6 bg-white border border-gray-200 rounded-lg shadow card-bg"
              style="text-decoration: none;"
            >
              <h3 class="mb-2 font-bold text-blue-600" style="font-size: calc(1em + 0.25rem);">
                Reporte de Pagos
              </h3>
              <p class="text-main opacity-80" style="font-size: calc(1em - 0.075rem);">
                Generar informes filtrados de pagos por fechas y usuario chofer.
              </p>
            </Link>

            <!-- Tarjeta adicional: Ejemplo -->
            <Link
              href="/reportes/otro"
              class="block p-6 bg-white border border-gray-200 rounded-lg shadow card-bg"
              style="text-decoration: none;"
            >
              <h3 class="mb-2 font-bold text-blue-600" style="font-size: calc(1em + 0.25rem);">
                Otro Reporte
              </h3>
              <p class="text-main opacity-80" style="font-size: calc(1em - 0.075rem);">
                Descripción del otro tipo de reporte disponible.
              </p>
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
