<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    vehiculos: Array,
    mantenimientos: Array,
});

const vehiculo_id = ref('');
const mantenimiento_id = ref('');
const fecha = ref('');
const monto = ref('');
const processing = ref(false);

const enviar = () => {
    processing.value = true;
    
    router.post('/registro-mantenimientos', {
        vehiculo_id: vehiculo_id.value,
        mantenimiento_id: mantenimiento_id.value,
        fecha: fecha.value,
        monto: monto.value,
    }, {
        onSuccess: () => {
            Swal.fire({
                title: '¡Registrado!',
                text: 'El mantenimiento fue registrado correctamente.',
                icon: 'success',
            }).then(() => {
                router.visit('/registro-mantenimientos');
            });
        },
        onFinish: () => {
            processing.value = false;
        }
    });
};
</script>

<template>
    <Head title="Registrar Mantenimiento" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-main">Registrar Mantenimiento</h2>
        </template>

        <div class="py-12 text-main">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="p-8 shadow-lg rounded-lg card-bg">
                    <!-- Título del formulario -->
                    <div class="mb-8 text-center">
                        <h1 class="font-bold text-main" style="font-size: calc(1em + 0.5rem);">
                            Registrar Nuevo Mantenimiento
                        </h1>
                        <p class="text-main opacity-70 mt-2" style="font-size: calc(1em - 0.125rem);">
                            Complete la información del mantenimiento realizado
                        </p>
                    </div>

                    <form @submit.prevent="enviar" class="space-y-6">
                        <!-- Selección de Vehículo -->
                        <div>
                            <label class="block font-semibold text-main mb-3" style="font-size: calc(1em - 0.075rem);">
                                Vehículo *
                            </label>
                            <select 
                                v-model="vehiculo_id" 
                                required
                                class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                style="font-size: inherit;">
                                <option disabled value="">Seleccione un vehículo</option>
                                <option v-for="v in props.vehiculos" :key="v.id" :value="v.id">
                                    {{ v.tipo }} - {{ v.marca }} {{ v.modelo }} (Placa: {{ v.placa }})
                                </option>
                            </select>
                            <p class="text-main opacity-50 mt-1" style="font-size: calc(1em - 0.2rem);">
                                Seleccione el vehículo al que se le realizó el mantenimiento
                            </p>
                        </div>

                        <!-- Tipo de Mantenimiento -->
                        <div>
                            <label class="block font-semibold text-main mb-3" style="font-size: calc(1em - 0.075rem);">
                                Tipo de Mantenimiento *
                            </label>
                            <select 
                                v-model="mantenimiento_id" 
                                required
                                class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                style="font-size: inherit;">
                                <option disabled value="">Seleccione un tipo de mantenimiento</option>
                                <option v-for="m in props.mantenimientos" :key="m.id" :value="m.id">
                                    {{ m.nombre }}
                                </option>
                            </select>
                            <p class="text-main opacity-50 mt-1" style="font-size: calc(1em - 0.2rem);">
                                Especifique el tipo de mantenimiento realizado
                            </p>
                        </div>

                        <!-- Fecha del Mantenimiento -->
                        <div>
                            <label class="block font-semibold text-main mb-3" style="font-size: calc(1em - 0.075rem);">
                                Fecha del Mantenimiento *
                            </label>
                            <input 
                                v-model="fecha" 
                                type="date" 
                                required
                                :max="new Date().toISOString().split('T')[0]"
                                class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                style="font-size: inherit;" 
                            />
                            <p class="text-main opacity-50 mt-1" style="font-size: calc(1em - 0.2rem);">
                                Fecha en que se realizó el mantenimiento
                            </p>
                        </div>

                        <!-- Monto del Mantenimiento -->
                        <div>
                            <label class="block font-semibold text-main mb-3" style="font-size: calc(1em - 0.075rem);">
                                Monto (Bs) *
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-main opacity-60">Bs</span>
                                <input 
                                    v-model="monto" 
                                    type="number" 
                                    step="0.01"
                                    min="0"
                                    required
                                    placeholder="0.00"
                                    class="w-full p-3 pl-8 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                    style="font-size: inherit;" 
                                />
                            </div>
                            <p class="text-main opacity-50 mt-1" style="font-size: calc(1em - 0.2rem);">
                                Costo total del mantenimiento realizado
                            </p>
                        </div>

                        <!-- Botones de acción -->
                        <div class="flex flex-col sm:flex-row justify-center gap-4 pt-6">
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
                                <span v-else>Guardar Registro</span>
                            </button>
                            
                            <a 
                                href="/registro-mantenimientos" 
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