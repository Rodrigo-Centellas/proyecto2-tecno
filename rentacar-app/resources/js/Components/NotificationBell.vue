<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref, watch } from 'vue';

const props = defineProps({
    notificaciones: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');

watch(search, (val) => {
    router.get('/notificaciones', { search: val }, { preserveState: true, replace: true });
});
</script>

<template>
    <Head title="Notificaciones" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-main">
                Notificaciones
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="p-6 shadow-sm rounded-lg card-bg text-main">
                    <h1 class="text-2xl font-bold mb-4">Lista de Notificaciones</h1>

                    <div class="mb-4 max-w-md">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Buscar por título o mensaje..."
                            class="border rounded px-3 py-1 w-full"
                        />
                    </div>

                    <table class="w-full mt-4 border text-main">
                        <thead :style="{ backgroundColor: 'var(--thead-bg)' }" class="border-b">
                            <tr>
                                <th class="px-2 py-1 border text-left">ID</th>
                                <th class="px-2 py-1 border text-left">Título</th>
                                <th class="px-2 py-1 border text-left">Mensaje</th>
                                <th class="px-2 py-1 border text-left">Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="n in notificaciones" :key="n.id" class="border-b border-gray-200">
                                <td class="px-2 py-1">{{ n.id }}</td>
                                <td class="px-2 py-1">{{ n.titulo }}</td>
                                <td class="px-2 py-1">{{ n.mensaje }}</td>
                                <td class="px-2 py-1">{{ new Date(n.created_at).toLocaleString() }}</td>
                            </tr>
                            <tr v-if="notificaciones.length === 0">
                                <td colspan="4" class="text-center py-4">No hay notificaciones</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
