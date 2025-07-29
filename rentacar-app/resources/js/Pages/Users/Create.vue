<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';

const name = ref('');
const apellido = ref('');
const ci = ref('');
const domicilio = ref('');
const telefono = ref('');
const email = ref('');
const password = ref('');
const rolesSeleccionados = ref([]);
const processing = ref(false);

const props = defineProps({
    roles: Array,
});

const enviar = () => {
    processing.value = true;
    
    router.post('/users', {
        name: name.value,
        apellido: apellido.value,
        ci: ci.value,
        domicilio: domicilio.value,
        telefono: telefono.value,
        email: email.value,
        password: password.value,
        roles: rolesSeleccionados.value,
    }, {
        onSuccess: () => {
            Swal.fire({
                title: 'Usuario creado',
                text: 'El usuario ha sido registrado correctamente.',
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
    <Head title="Crear Usuario" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-main">Crear Usuario</h2>
        </template>

        <div class="py-12 text-main">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="p-8 rounded-lg shadow-lg card-bg">
                    <!-- Título del formulario -->
                    <div class="mb-8 text-center">
                        <h1 class="font-bold text-main" style="font-size: calc(1em + 0.5rem);">
                            Registrar Nuevo Usuario
                        </h1>
                        <p class="text-main opacity-70 mt-2" style="font-size: calc(1em - 0.125rem);">
                            Complete la información personal del usuario que desea agregar al sistema
                        </p>
                    </div>

                    <form @submit.prevent="enviar" class="space-y-6">
                        <!-- Información Personal -->
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
                                    placeholder="Ej: Juan Carlos"
                                    class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
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
                                    placeholder="Ej: Pérez García"
                                    class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                    style="font-size: inherit;" 
                                />
                            </div>
                        </div>

                        <!-- Documentación y Contacto -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Cédula de Identidad -->
                            <div>
                                <label class="block font-semibold text-main mb-2" style="font-size: calc(1em - 0.075rem);">
                                    Cédula de Identidad *
                                </label>
                                <input 
                                    v-model="ci" 
                                    type="number" 
                                    required
                                    placeholder="Ej: 12345678"
                                    min="1000000"
                                    max="99999999"
                                    class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                    style="font-size: inherit;" 
                                />
                                <p class="text-main opacity-50 mt-1" style="font-size: calc(1em - 0.2rem);">
                                    Número de documento de identidad válido
                                </p>
                            </div>

                            <!-- Teléfono -->
                            <div>
                                <label class="block font-semibold text-main mb-2" style="font-size: calc(1em - 0.075rem);">
                                    Teléfono *
                                </label>
                                <input 
                                    v-model="telefono" 
                                    type="tel" 
                                    required
                                    placeholder="Ej: 70123456"
                                    pattern="[0-9]{8}"
                                    class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                    style="font-size: inherit;" 
                                />
                                <p class="text-main opacity-50 mt-1" style="font-size: calc(1em - 0.2rem);">
                                    Número de celular de 8 dígitos
                                </p>
                            </div>
                        </div>

                        <!-- Domicilio -->
                        <div>
                            <label class="block font-semibold text-main mb-2" style="font-size: calc(1em - 0.075rem);">
                                Domicilio *
                            </label>
                            <input 
                                v-model="domicilio" 
                                type="text" 
                                required
                                placeholder="Ej: Av. Principal #123, Zona Central"
                                class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                style="font-size: inherit;" 
                            />
                            <p class="text-main opacity-50 mt-1" style="font-size: calc(1em - 0.2rem);">
                                Dirección completa de residencia
                            </p>
                        </div>

                        <!-- Credenciales de Acceso -->
                        <div class="border-t border-opacity-20 border-gray-300 pt-6">
                            <h3 class="font-semibold text-main mb-4" style="font-size: calc(1em + 0.125rem);">
                                Credenciales de Acceso
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Email -->
                                <div>
                                    <label class="block font-semibold text-main mb-2" style="font-size: calc(1em - 0.075rem);">
                                        Correo Electrónico *
                                    </label>
                                    <input 
                                        v-model="email" 
                                        type="email" 
                                        required
                                        placeholder="usuario@ejemplo.com"
                                        class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                        style="font-size: inherit;" 
                                    />
                                    <p class="text-main opacity-50 mt-1" style="font-size: calc(1em - 0.2rem);">
                                        Será utilizado para iniciar sesión
                                    </p>
                                </div>

                                <!-- Contraseña -->
                                <div>
                                    <label class="block font-semibold text-main mb-2" style="font-size: calc(1em - 0.075rem);">
                                        Contraseña *
                                    </label>
                                    <input 
                                        v-model="password" 
                                        type="password" 
                                        required
                                        minlength="6"
                                        placeholder="Mínimo 6 caracteres"
                                        class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                        style="font-size: inherit;" 
                                    />
                                    <p class="text-main opacity-50 mt-1" style="font-size: calc(1em - 0.2rem);">
                                        Contraseña temporal (el usuario puede cambiarla)
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Asignación de Roles -->
                        <div class="border-t border-opacity-20 border-gray-300 pt-6">
                            <label class="block font-semibold text-main mb-4" style="font-size: calc(1em + 0.125rem);">
                                Asignación de Roles *
                            </label>
                            <p class="text-main opacity-70 mb-4" style="font-size: calc(1em - 0.125rem);">
                                Seleccione los roles que tendrá el usuario en el sistema
                            </p>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div v-for="rol in roles" :key="rol.id" 
                                     class="card-bg border border-opacity-20 border-gray-300 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <label class="flex items-start space-x-3 cursor-pointer">
                                        <input 
                                            type="checkbox" 
                                            :value="rol.name" 
                                            v-model="rolesSeleccionados"
                                            class="mt-1 rounded focus:ring-2 focus:ring-blue-500" 
                                        />
                                        <div>
                                            <div class="font-medium text-main" style="font-size: inherit;">
                                                {{ rol.name }}
                                            </div>
                                            <div class="text-main opacity-60 mt-1" style="font-size: calc(1em - 0.15rem);">
                                                <span v-if="rol.name === 'Administrador'">Acceso completo al sistema</span>
                                                <span v-else-if="rol.name === 'Empleado Operativo'">Gestión operativa diaria</span>
                                                <span v-else-if="rol.name === 'Cliente'">Acceso limitado como cliente</span>
                                                <span v-else>Permisos específicos del rol</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="flex flex-col sm:flex-row justify-center gap-4 pt-8">
                            <button 
                                type="submit" 
                                :disabled="processing"
                                class="px-8 py-3 text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors font-medium shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                                style="font-size: inherit;">
                                <span v-if="processing">
                                    <svg class="inline w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Guardando...
                                </span>
                                <span v-else>Crear Usuario</span>
                            </button>
                            
                            <a 
                                href="/users" 
                                class="px-8 py-3 text-white bg-gray-500 rounded-lg hover:bg-gray-600 transition-colors font-medium shadow-md hover:shadow-lg text-center"
                                style="font-size: inherit;">
                                Cancelar
                            </a>
                        </div>

                        <!-- Información de campos obligatorios -->
                        <div class="mt-6 p-4 card-bg rounded-lg border border-opacity-20 border-gray-300">
                            <p class="text-main opacity-60 text-center" style="font-size: calc(1em - 0.2rem);">
                                <span class="text-red-500">*</span> Campos obligatorios
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>