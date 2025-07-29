<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref } from 'vue';

const props = defineProps({
    garantes: Array,
    filters: Object, // Para mantener el estado del filtro
});

const search = ref(props.filters?.search || '');

const eliminar = (id) => {
    Swal.fire({
        title: '¿Eliminar?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
    }).then(result => {
        if (result.isConfirmed) {
            router.delete(`/garantes/${id}`, {
                onSuccess: () => {
                    Swal.fire('Eliminado', 'Garante eliminado correctamente', 'success');
                }
            });
        }
    });
};

const buscar = () => {
    router.get('/garantes', { search: search.value }, { preserveState: true, replace: true });
};
</script>

<template>
    <Head title="Garantes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Garantes</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="p-6 bg-white rounded-lg shadow-sm">
                    <div class="flex justify-between mb-4">
                        <h1 class="text-2xl font-bold">Lista de Garantes</h1>
                        <a href="/garantes/create" class="px-4 py-2 text-white bg-blue-500 rounded">Nuevo</a>
                    </div>

                    <!-- Buscador -->
                    <div class="mb-4">
                        <form @submit.prevent="buscar" class="flex gap-2">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Buscar por nombre, apellido o CI"
                                class="px-4 py-2 border rounded w-full max-w-md"
                            />
                            <button type="submit" class="px-4 py-2 text-white bg-gray-800 rounded hover:bg-gray-700">
                                Buscar
                            </button>
                        </form>
                    </div>

                    <table class="w-full border">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-2 py-1 border">ID</th>
                                <th class="px-2 py-1 border">Nombre</th>
                                <th class="px-2 py-1 border">Apellido</th>
                                <th class="px-2 py-1 border">CI</th>
                                <th class="px-2 py-1 border">Domicilio</th>
                                <th class="px-2 py-1 border">Teléfono</th>
                                <th class="px-2 py-1 border">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="g in garantes" :key="g.id">
                                <td class="px-2 py-1 border">{{ g.id }}</td>
                                <td class="px-2 py-1 border">{{ g.nombre }}</td>
                                <td class="px-2 py-1 border">{{ g.apellido }}</td>
                                <td class="px-2 py-1 border">{{ g.ci }}</td>
                                <td class="px-2 py-1 border">{{ g.domicilio }}</td>
                                <td class="px-2 py-1 border">{{ g.telefono }}</td>
                                <td class="px-2 py-1 border">
                                    <a :href="`/garantes/${g.id}/edit`" class="mr-2 text-blue-500">Editar</a>
                                    <button @click="eliminar(g.id)" class="text-red-500">Eliminar</button>
                                </td>
                            </tr>
                            <tr v-if="garantes.length === 0">
                                <td colspan="7" class="text-center py-4">No hay registros</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
