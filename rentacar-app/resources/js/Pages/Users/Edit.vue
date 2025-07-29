<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    user: Object,
    roles: Array,
});

const name = ref(props.user.name);
const apellido = ref(props.user.apellido);
const ci = ref(props.user.ci);
const domicilio = ref(props.user.domicilio);
const telefono = ref(props.user.telefono);
const email = ref(props.user.email);
const selectedRoles = ref(props.user.roles.map(r => r.name));
const processing = ref(false);

const enviar = () => {
    processing.value = true;

    router.put(`/users/${props.user.id}`, {
        name: name.value,
        apellido: apellido.value,
        ci: ci.value,
        domicilio: domicilio.value,
        telefono: telefono.value,
        email: email.value,
        roles: selectedRoles.value,
    }, {
        onSuccess: () => {
            Swal.fire({
                title: '¡Usuario actualizado!',
                text: 'Los datos del usuario fueron actualizados correctamente.',
                icon: 'success',
            }).then(() => {
                router.visit('/users');
            });
        },
        onFinish: () => {
            processing.value = false;
        }
    });
};
</script>

<template>
    <Head title="Editar Usuario" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-main">Editar Usuario</h2>
        </template>

        <div class="py-12 text-main">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="p-8 rounded-lg shadow-lg card-bg">
                    <!-- Título del formulario -->
                    <div class="mb-8 text-center">
                        <h1 class="font-bold text-main" style="font-size: calc(1em + 0.5rem);">
                            Modificar Usuario
                        </h1>
                        <p class="text-main opacity-70 mt-2" style="font-size: calc(1em - 0.125rem);">
                            Actualice la información del usuario seleccionado
                        </p>
                    </div>

                    <form @submit.prevent="enviar" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nombre -->
                            <div>
                                <label class="block font-semibold text-main mb-2" style="font-size: calc(1em - 0.075rem);">
                                    Nombre *
                                </label>
                                <input 
                                    v-model="name" 
                                    type="text" 
                                    required
                                    class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    style="font-size: inherit;" 
                                />
                            </div>

                            <!-- Apellido -->
                            <div>
                                <label class="block font-semibold text-main mb-2" style="font-size: calc(1em - 0.075rem);">
                                    Apellido *
                                </label>
                                <input 
                                    v-model="apellido" 
                                    type="text" 
                                    required
                                    class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    style="font-size: inherit;" 
                                />
                            </div>

                            <!-- CI -->
                            <div>
                                <label class="block font-semibold text-main mb-2" style="font-size: calc(1em - 0.075rem);">
                                    Cédula de Identidad *
                                </label>
                                <input 
                                    v-model="ci" 
                                    type="number" 
                                    required
                                    class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    style="font-size: inherit;" 
                                />
                            </div>

                            <!-- Teléfono -->
                            <div>
                                <label class="block font-semibold text-main mb-2" style="font-size: calc(1em - 0.075rem);">
                                    Teléfono *
                                </label>
                                <input 
                                    v-model="telefono" 
                                    type="number" 
                                    required
                                    class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    style="font-size: inherit;" 
                                />
                            </div>

                            <!-- Domicilio -->
                            <div class="md:col-span-2">
                                <label class="block font-semibold text-main mb-2" style="font-size: calc(1em - 0.075rem);">
                                    Domicilio *
                                </label>
                                <input 
                                    v-model="domicilio" 
                                    type="text" 
                                    required
                                    class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    style="font-size: inherit;" 
                                />
                            </div>

                            <!-- Email -->
                            <div class="md:col-span-2">
                                <label class="block font-semibold text-main mb-2" style="font-size: calc(1em - 0.075rem);">
                                    Correo Electrónico *
                                </label>
                                <input 
                                    v-model="email" 
                                    type="email" 
                                    required
                                    class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    style="font-size: inherit;" 
                                />
                            </div>
                        </div>

                        <!-- Roles -->
                        <div class="border-t border-opacity-20 border-gray-300 pt-6">
                            <label class="block font-semibold text-main mb-4" style="font-size: calc(1em + 0.125rem);">
                                Asignación de Roles *
                            </label>
                            <p class="text-main opacity-70 mb-4" style="font-size: calc(1em - 0.125rem);">
                                Marque los roles que el usuario desempeñará en el sistema
                            </p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div v-for="role in roles" :key="role.id" 
                                     class="card-bg border border-opacity-20 border-gray-300 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <label class="flex items-start space-x-3 cursor-pointer">
                                        <input 
                                            type="checkbox" 
                                            :value="role.name" 
                                            v-model="selectedRoles"
                                            class="mt-1 rounded focus:ring-2 focus:ring-blue-500" 
                                        />
                                        <div>
                                            <div class="font-medium text-main" style="font-size: inherit;">
                                                {{ role.name }}
                                            </div>
                                            <div class="text-main opacity-60 mt-1" style="font-size: calc(1em - 0.15rem);">
                                                <span v-if="role.name === 'Administrador'">Acceso completo al sistema</span>
                                                <span v-else-if="role.name === 'Empleado Operativo'">Gestión operativa diaria</span>
                                                <span v-else-if="role.name === 'Cliente'">Acceso limitado como cliente</span>
                                                <span v-else>Permisos específicos del rol</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="flex flex-col sm:flex-row justify-center gap-4 pt-8">
                            <button 
                                type="submit" 
                                :disabled="processing"
                                class="px-8 py-3 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors font-medium shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                                style="font-size: inherit;">
                                <span v-if="processing">Guardando...</span>
                                <span v-else>Guardar Cambios</span>
                            </button>

                            <a 
                                href="/users" 
                                class="px-8 py-3 text-white bg-gray-500 rounded-lg hover:bg-gray-600 transition-colors font-medium shadow-md hover:shadow-lg text-center"
                                style="font-size: inherit;">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
