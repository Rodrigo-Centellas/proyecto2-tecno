<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({ clausulas: Array });
const eliminar = id => {
  Swal.fire({
    title: '¿Eliminar cláusula?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
  }).then(r => r.isConfirmed && router.delete(route('clausulas.destroy', id)));
};
</script>

<template>

  <Head title="Cláusulas" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-main" style="font-size:calc(1em+0.25rem)">
        Cláusulas
      </h2>
    </template>

    <div class="py-12 max-w-4xl mx-auto text-main">
      <div class="card-bg p-6 rounded-2xl shadow-lg">
        <div class="flex justify-between items-center mb-6">
          <h1 class="font-bold" style="font-size:calc(1em+0.5rem)">Listado de Cláusulas</h1>
          <Link href="/clausulas/create" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-md"
            style="font-size:inherit">
          Nueva Cláusula
          </Link>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full min-w-[800px] text-base border-collapse">
            <thead class="border-b" :style="{ backgroundColor: 'var(--thead-bg)' }">
              <tr>
                <th class="px-6 py-4 text-left font-semibold border">ID</th>
                <th class="px-6 py-4 text-left font-semibold border">Descripción</th>
                <th class="px-6 py-4 text-left font-semibold border">Activa</th>
                <th class="px-6 py-4 text-left font-semibold border">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="cl in props.clausulas" :key="cl.id" class="hover:bg-gray-50 dark:hover:bg-white/5">
                <td class="border px-6 py-4">{{ cl.id }}</td>
                <td class="border px-6 py-4">{{ cl.descripcion }}</td>
                <td class="border px-6 py-4">{{ cl.activa }}</td>
                <td class="border px-6 py-4">
                  <div class="flex items-center space-x-4">
                    <Link :href="route('clausulas.edit', cl.id)" class="text-blue-400 hover:underline"
                      style="font-size:inherit">
                    Editar
                    </Link>
                    <button @click="eliminar(cl.id)" class="text-red-400 hover:underline" style="font-size:inherit">
                      Eliminar
                    </button>
                  </div>
                </td>

              </tr>

              <tr v-if="!props.clausulas.length">
                <td colspan="3" class="py-6 text-center opacity-70">
                  No hay cláusulas registradas
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
