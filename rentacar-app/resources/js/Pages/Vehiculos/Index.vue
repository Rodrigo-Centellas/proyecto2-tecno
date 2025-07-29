<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage} from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    vehiculos: Object,
    filters: Object,
});

const $page = usePage();

const hasPermission = computed(() => (permission) => {
  const user = $page.props.auth.user;
  if (user?.roles?.some(role => role.name === 'Administrador')) return true;
  return user?.permissions?.includes(permission) || false;
});

const search = ref(props.filters.search || '');

watch(search, (value) => {
    router.get('/vehiculos', { search: value }, { preserveState: true, replace: true });
});

const eliminar = (id) => {
    Swal.fire({
        title: '¿Eliminar vehículo?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('vehiculos.destroy', id));
        }
    });
};
</script>

<template>
    <Head title="Vehículos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-main">Vehículos</h2>
        </template>

        <div class="py-12 text-main">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="p-6 shadow-sm rounded-lg card-bg">
                    <!-- Encabezado -->
                    <div class="flex items-center justify-between mb-6">
                        <h1 class="font-bold text-main" style="font-size: calc(1em + 0.5rem);">Lista de Vehículos</h1>
                        <a v-if="hasPermission('vehiculos.crear')"
                           :href="route('vehiculos.create')"
                           class="px-4 py-2 rounded text-white bg-blue-600 hover:bg-blue-700 transition font-medium"
                           style="font-size: calc(1em - 0.125rem);">
                            Crear Vehículo
                        </a>
                    </div>

                    <!-- Filtro -->
                    <div class="mb-4 max-w-md">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Buscar por placa o modelo..."
                            class="border rounded px-3 py-2 w-full shadow-sm focus:ring focus:ring-blue-200 focus:outline-none card-bg text-main"
                            style="font-size: calc(1em - 0.125rem);"
                        />
                    </div>

                    <!-- Tabla -->
                    <div class="overflow-x-auto">
                        <table class="w-full mt-4 border text-main">
                            <thead class="border-b card-bg">
                                <tr>
                                    <th class="px-3 py-2 text-left text-main font-semibold" style="font-size: calc(1em - 0.125rem);">ID</th>
                                    <th class="px-3 py-2 text-left text-main font-semibold" style="font-size: calc(1em - 0.125rem);">Imagen</th>
                                    <th class="px-3 py-2 text-left text-main font-semibold" style="font-size: calc(1em - 0.125rem);">Placa</th>
                                    <th class="px-3 py-2 text-left text-main font-semibold" style="font-size: calc(1em - 0.125rem);">Marca</th>
                                    <th class="px-3 py-2 text-left text-main font-semibold" style="font-size: calc(1em - 0.125rem);">Modelo</th>
                                    <th class="px-3 py-2 text-left text-main font-semibold" style="font-size: calc(1em - 0.125rem);">Tipo</th>
                                    <th class="px-3 py-2 text-left text-main font-semibold" style="font-size: calc(1em - 0.125rem);">Estado</th>
                                    <th class="px-3 py-2 text-left text-main font-semibold" style="font-size: calc(1em - 0.125rem);">Acciones</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr v-for="v in vehiculos.data" :key="v.id" class="border-b border-opacity-20 border-gray-300 hover:bg-opacity-50 hover:card-bg transition-colors">
                                    <td class="px-3 py-2 text-main" style="font-size: inherit;">{{ v.id }}</td>
                                    <td class="px-3 py-2">
                                        <img
                                            v-if="v.url_imagen"
                                            :src="v.url_imagen"
                                            alt="Foto"
                                            class="w-16 h-12 object-cover rounded"
                                        />
                                        <span v-else class="text-main opacity-50 italic" style="font-size: calc(1em - 0.2rem);">Sin imagen</span>
                                    </td>
                                    <td class="px-3 py-2 text-main font-medium" style="font-size: inherit;">{{ v.placa }}</td>
                                    <td class="px-3 py-2 text-main" style="font-size: inherit;">{{ v.marca }}</td>
                                    <td class="px-3 py-2 text-main" style="font-size: inherit;">{{ v.modelo }}</td>
                                    <td class="px-3 py-2 text-main" style="font-size: inherit;">{{ v.tipo }}</td>
                                    <td class="px-3 py-2">
                                        <span :class="{
                                            'text-green-600 font-semibold': v.estado === 'Disponible',
                                            'text-yellow-500 font-semibold': v.estado === 'Reservado',
                                            'text-red-600 font-semibold': v.estado === 'En mantenimiento',
                                            'text-gray-500 font-semibold': v.estado === 'Retirado',
                                        }"
                                        style="font-size: inherit;">
                                            {{ v.estado }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 space-x-2">
                                        <a v-if="hasPermission('vehiculos.editar')" 
                                           :href="route('vehiculos.edit', v.id)" 
                                           class="text-blue-500 hover:underline"
                                           style="font-size: calc(1em - 0.125rem);">
                                            Editar
                                        </a>
                                        <button v-if="hasPermission('vehiculos.eliminar')" 
                                                @click="eliminar(v.id)" 
                                                class="text-red-500 hover:underline"
                                                style="font-size: calc(1em - 0.125rem);">
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>

                                <tr v-if="vehiculos.data.length === 0">
                                    <td colspan="8" class="text-center py-4 text-main opacity-60" style="font-size: inherit;">
                                        No se encontraron resultados.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="flex justify-between mt-4">
                        <button
                            :disabled="!vehiculos.prev_page_url"
                            @click="router.get(vehiculos.prev_page_url, {}, { preserveState: true })"
                            :class="[
                                'px-3 py-1 rounded transition font-medium',
                                vehiculos.prev_page_url 
                                    ? 'card-bg text-main hover:opacity-80 border border-opacity-20 border-gray-300' 
                                    : 'bg-gray-300 text-gray-500 cursor-not-allowed opacity-50'
                            ]"
                            style="font-size: calc(1em - 0.125rem);">
                            Anterior
                        </button>

                        <!-- Información de página actual -->
                        <div class="flex items-center text-main opacity-70" style="font-size: calc(1em - 0.125rem);">
                            <span>Página {{ vehiculos.current_page || 1 }} de {{ vehiculos.last_page || 1 }}</span>
                        </div>

                        <button
                            :disabled="!vehiculos.next_page_url"
                            @click="router.get(vehiculos.next_page_url, {}, { preserveState: true })"
                            :class="[
                                'px-3 py-1 rounded transition font-medium',
                                vehiculos.next_page_url 
                                    ? 'card-bg text-main hover:opacity-80 border border-opacity-20 border-gray-300' 
                                    : 'bg-gray-300 text-gray-500 cursor-not-allowed opacity-50'
                            ]"
                            style="font-size: calc(1em - 0.125rem);">
                            Siguiente
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>