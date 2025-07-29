<script setup>
import { ref, watch, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    registros: Array,
    filters: Object,
});

const $page = usePage();

const hasPermission = computed(() => (permission) => {
  const user = $page.props.auth.user;
  if (user?.roles?.some(role => role.name === 'Administrador')) return true;
  return user?.permissions?.includes(permission) || false;
});

const search = ref(props.filters?.search || '');

// Escucha cambios en el input y realiza la consulta GET con filtro
watch(search, (val) => {
    router.get('/registro-mantenimientos', { search: val }, { preserveState: true, replace: true });
});

const eliminar = (id) => {
    Swal.fire({
        title: '¿Eliminar registro de mantenimiento?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/registro-mantenimientos/${id}`);
        }
    });
};
</script>

<template>
    <Head title="Registros de Mantenimientos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-main">
                Registros de Mantenimientos
            </h2>
        </template>

        <div class="py-12 text-main">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="p-6 shadow-sm rounded-lg card-bg">
                    <!-- Encabezado -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                        <div>
                            <h1 class="font-bold text-main" style="font-size: calc(1em + 0.5rem);">
                                📋 Lista de Registros de Mantenimiento
                            </h1>
                            <p class="text-main opacity-70 mt-1" style="font-size: calc(1em - 0.125rem);">
                                Historial completo de mantenimientos realizados
                            </p>
                        </div>
                        <a v-if="hasPermission('mantenimientos.crear')"
                           href="/registro-mantenimientos/create" 
                           class="px-4 py-2 text-white bg-blue-500 rounded-lg hover:bg-blue-600 transition-colors font-medium shadow-md hover:shadow-lg"
                           style="font-size: calc(1em - 0.125rem);">
                            ✅ Nuevo Registro
                        </a>
                    </div>

                    <!-- Buscador mejorado -->
                    <div class="mb-6">
                        <label class="block font-medium text-main mb-2" style="font-size: calc(1em - 0.075rem);">
                            🔍 Buscar registro:
                        </label>
                        <input
                            type="text"
                            v-model="search"
                            placeholder="Buscar por vehículo, tipo de mantenimiento, fecha o monto..."
                            class="w-full px-4 py-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-sm"
                            style="font-size: inherit;"
                        />
                    </div>

                    <!-- Tabla responsiva -->
                    <div class="overflow-x-auto">
                        <table class="w-full border text-main">
                            <thead class="border-b card-bg">
                                <tr>
                                    <th class="px-4 py-3 border text-left text-main font-semibold" style="font-size: calc(1em - 0.125rem);">
                                        ID
                                    </th>
                                    <th class="px-4 py-3 border text-left text-main font-semibold" style="font-size: calc(1em - 0.125rem);">
                                        🚗 Vehículo
                                    </th>
                                    <th class="px-4 py-3 border text-left text-main font-semibold" style="font-size: calc(1em - 0.125rem);">
                                        🔧 Mantenimiento
                                    </th>
                                    <th class="px-4 py-3 border text-left text-main font-semibold" style="font-size: calc(1em - 0.125rem);">
                                        📅 Fecha
                                    </th>
                                    <th class="px-4 py-3 border text-left text-main font-semibold" style="font-size: calc(1em - 0.125rem);">
                                        💰 Monto
                                    </th>
                                    <th class="px-4 py-3 border text-left text-main font-semibold" style="font-size: calc(1em - 0.125rem);">
                                        ⚙️ Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="r in registros" :key="r.id" 
                                    class="border-b border-opacity-20 border-gray-300 hover:bg-opacity-50 hover:card-bg transition-colors">
                                    <td class="px-4 py-3 text-main font-medium" style="font-size: inherit;">
                                        #{{ r.id }}
                                    </td>
                                    <td class="px-4 py-3 text-main" style="font-size: inherit;">
                                        <div class="flex flex-col">
                                            <span class="font-medium">{{ r.vehiculo?.tipo || 'N/A' }}</span>
                                            <span class="text-main opacity-60" style="font-size: calc(1em - 0.2rem);">
                                                {{ r.vehiculo?.placa || 'Sin placa' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-main" style="font-size: inherit;">
                                        <span class="inline-block px-2 py-1 rounded-full bg-blue-100 text-blue-800 font-medium"
                                              style="font-size: calc(1em - 0.15rem);">
                                            {{ r.mantenimiento?.nombre || 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-main" style="font-size: inherit;">
                                        {{ r.fecha ? new Date(r.fecha).toLocaleDateString('es-BO') : 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 text-main" style="font-size: inherit;">
                                        <span class="font-semibold text-green-600">
                                            Bs {{ r.monto ? Number(r.monto).toFixed(2) : '0.00' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex space-x-2">
                                            <a v-if="hasPermission('mantenimientos.ver')"
                                               :href="`/registro-mantenimientos/${r.id}`"
                                               class="text-blue-500 hover:underline"
                                               title="Ver detalles"
                                               style="font-size: calc(1em - 0.125rem);">
                                                👁️ Ver
                                            </a>
                                            <a v-if="hasPermission('mantenimientos.editar')"
                                               :href="`/registro-mantenimientos/${r.id}/edit`"
                                               class="text-yellow-500 hover:underline"
                                               title="Editar registro"
                                               style="font-size: calc(1em - 0.125rem);">
                                                ✏️ Editar
                                            </a>
                                            <button v-if="hasPermission('mantenimientos.eliminar')" 
                                                    @click="eliminar(r.id)" 
                                                    class="text-red-500 hover:underline"
                                                    title="Eliminar registro"
                                                    style="font-size: calc(1em - 0.125rem);">
                                                🗑️ Eliminar
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Fila cuando no hay registros -->
                                <tr v-if="registros.length === 0">
                                    <td colspan="6" class="text-center py-8 text-main opacity-60">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 text-main opacity-30 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <p class="font-medium" style="font-size: calc(1em + 0.125rem);">
                                                No hay registros de mantenimiento
                                            </p>
                                            <p class="text-main opacity-50 mt-1" style="font-size: calc(1em - 0.125rem);">
                                                Los registros aparecerán aquí cuando se realicen mantenimientos
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Información adicional -->
                    <div class="mt-6 p-4 card-bg rounded-lg border border-opacity-20 border-gray-300">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                            <div class="text-main opacity-70" style="font-size: calc(1em - 0.125rem);">
                                📊 Total de registros: <span class="font-semibold">{{ registros.length }}</span>
                            </div>
                            <div class="text-main opacity-60 mt-2 sm:mt-0" style="font-size: calc(1em - 0.2rem);">
                                Última actualización: {{ new Date().toLocaleString('es-BO') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>