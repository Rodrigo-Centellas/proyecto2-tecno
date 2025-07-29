<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    permissions: Array,
});

const name = ref('');
const selectedPermissions = ref([]);

const enviar = () => {
    router.post('/roles', {
        name: name.value,
        permissions: selectedPermissions.value,
    }, {
        onSuccess: () => {
            Swal.fire({
                title: '¡Rol creado!',
                text: 'El rol fue registrado correctamente.',
                icon: 'success',
            }).then(() => {
                router.visit('/roles');
            });
        },
        onError: (errors) => {
            Swal.fire({
                title: 'Error',
                text: Object.values(errors).join('\n'),
                icon: 'error',
            });
        }
    });
};
</script>

<template>
    <Head title="Crear Rol" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-main">Crear Rol</h2>
        </template>

        <div class="py-12 text-main">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="p-6 rounded-lg shadow-sm card-bg">
                    <h1 class="mb-4 text-2xl font-bold">Nuevo Rol</h1>

                    <form @submit.prevent="enviar">
                        <div class="mb-4">
                            <label class="block font-semibold">Nombre del Rol:</label>
                            <input v-model="name" type="text" placeholder="Ej: Chofer" class="w-full p-2 text-black bg-white border rounded dark:bg-gray-800 dark:text-white" />
                        </div>

                        <div class="mb-4">
                            <label class="block mb-2 font-semibold">Permisos:</label>
                            <div class="grid grid-cols-2 gap-2 md:grid-cols-3">
                                <div v-for="permiso in props.permissions" :key="permiso.id">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" :value="permiso.id" v-model="selectedPermissions" class="mr-2">
                                        {{ permiso.id }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="px-4 py-2 text-white bg-green-500 rounded hover:bg-green-600">
                            Guardar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
