<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    placa: '',
    marca: '',
    modelo: '',
    estado: '',
    monto_garantia: '',
    precio_dia: '',
    tipo: '',
    url_imagen: null,
});

const submit = () => {
    form.post(route('vehiculos.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Registrar Vehículo" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-main">Nuevo Vehículo</h2>
        </template>

        <div class="py-12 text-main">
            <div class="w-full max-w-4xl px-6 py-8 mx-auto rounded-lg shadow-lg card-bg">
                <!-- Título del formulario -->
                <div class="mb-6 text-center">
                    <h1 class="font-bold text-main" style="font-size: calc(1em + 0.5rem);">
                        Registrar Nuevo Vehículo
                    </h1>
                    <p class="text-main opacity-70 mt-2" style="font-size: calc(1em - 0.125rem);">
                        Complete la información del vehículo que desea agregar a la flota
                    </p>
                </div>

                <form @submit.prevent="submit" enctype="multipart/form-data"
                    class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    
                    <!-- Placa -->
                    <div>
                        <label class="block font-medium text-main mb-2" style="font-size: calc(1em - 0.075rem);">
                            Placa *
                        </label>
                        <input 
                            type="text" 
                            v-model="form.placa" 
                            placeholder="Ej: ABC-1234"
                            class="w-full border rounded-md px-3 py-2 card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            style="font-size: inherit;" 
                        />
                        <div v-if="form.errors.placa" class="text-red-600 mt-1" style="font-size: calc(1em - 0.2rem);">
                            {{ form.errors.placa }}
                        </div>
                    </div>

                    <!-- Marca -->
                    <div>
                        <label class="block font-medium text-main mb-2" style="font-size: calc(1em - 0.075rem);">
                            Marca *
                        </label>
                        <select 
                            v-model="form.marca" 
                            class="w-full border rounded-md px-3 py-2 card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            style="font-size: inherit;">
                            <option disabled value="">Seleccione una marca</option>
                            <option value="Toyota">Toyota</option>
                            <option value="Hyundai">Hyundai</option>
                            <option value="Kia">Kia</option>
                            <option value="Nissan">Nissan</option>
                            <option value="Chevrolet">Chevrolet</option>
                            <option value="Ford">Ford</option>
                            <option value="Volkswagen">Volkswagen</option>
                            <option value="Mazda">Mazda</option>
                            <option value="Suzuki">Suzuki</option>
                            <option value="Renault">Renault</option>
                            <option value="Honda">Honda</option>
                            <option value="Otro">Otro</option>
                        </select>
                        <div v-if="form.errors.marca" class="text-red-600 mt-1" style="font-size: calc(1em - 0.2rem);">
                            {{ form.errors.marca }}
                        </div>
                    </div>

                    <!-- Modelo -->
                    <div>
                        <label class="block font-medium text-main mb-2" style="font-size: calc(1em - 0.075rem);">
                            Modelo *
                        </label>
                        <input 
                            type="text" 
                            v-model="form.modelo" 
                            placeholder="Ej: Corolla, Accent, Rio"
                            class="w-full border rounded-md px-3 py-2 card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            style="font-size: inherit;" 
                        />
                        <div v-if="form.errors.modelo" class="text-red-600 mt-1" style="font-size: calc(1em - 0.2rem);">
                            {{ form.errors.modelo }}
                        </div>
                    </div>

                    <!-- Estado -->
                    <div>
                        <label class="block font-medium text-main mb-2" style="font-size: calc(1em - 0.075rem);">
                            Estado *
                        </label>
                        <select 
                            v-model="form.estado" 
                            class="w-full border rounded-md px-3 py-2 card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            style="font-size: inherit;">
                            <option disabled value="">Seleccione un estado</option>
                            <option value="Disponible">✅ Disponible</option>
                            <option value="Reservado">⏰ Reservado</option>
                            <option value="En uso">🚗 En uso</option>
                            <option value="En mantenimiento">🔧 En mantenimiento</option>
                            <option value="Retirado">❌ Retirado</option>
                        </select>
                        <div v-if="form.errors.estado" class="text-red-600 mt-1" style="font-size: calc(1em - 0.2rem);">
                            {{ form.errors.estado }}
                        </div>
                    </div>

                    <!-- Monto Garantía -->
                    <div>
                        <label class="block font-medium text-main mb-2" style="font-size: calc(1em - 0.075rem);">
                            Monto Garantía (Bs) *
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-main opacity-60">Bs</span>
                            <input 
                                type="number" 
                                v-model="form.monto_garantia"
                                placeholder="0.00"
                                min="0"
                                step="0.01"
                                class="w-full border rounded-md px-8 py-2 card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                style="font-size: inherit;" 
                            />
                        </div>
                        <div v-if="form.errors.monto_garantia" class="text-red-600 mt-1" style="font-size: calc(1em - 0.2rem);">
                            {{ form.errors.monto_garantia }}
                        </div>
                    </div>

                    <!-- Precio por Día -->
                    <div>
                        <label class="block font-medium text-main mb-2" style="font-size: calc(1em - 0.075rem);">
                            Precio por Día (Bs) *
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-main opacity-60">Bs</span>
                            <input 
                                type="number" 
                                v-model="form.precio_dia"
                                placeholder="0.00"
                                min="0"
                                step="0.01"
                                class="w-full border rounded-md px-8 py-2 card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                style="font-size: inherit;" 
                            />
                        </div>
                        <div v-if="form.errors.precio_dia" class="text-red-600 mt-1" style="font-size: calc(1em - 0.2rem);">
                            {{ form.errors.precio_dia }}
                        </div>
                    </div>

                    <!-- Tipo -->
                    <div>
                        <label class="block font-medium text-main mb-2" style="font-size: calc(1em - 0.075rem);">
                            Tipo de Vehículo *
                        </label>
                        <select 
                            v-model="form.tipo" 
                            class="w-full border rounded-md px-3 py-2 card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            style="font-size: inherit;">
                            <option disabled value="">Seleccione un tipo</option>
                            <option value="Sedán">🚗 Sedán</option>
                            <option value="Hatchback">🚙 Hatchback</option>
                            <option value="SUV">🚐 SUV</option>
                            <option value="Pickup">🛻 Pickup</option>
                            <option value="Van">🚐 Van</option>
                        </select>
                        <div v-if="form.errors.tipo" class="text-red-600 mt-1" style="font-size: calc(1em - 0.2rem);">
                            {{ form.errors.tipo }}
                        </div>
                    </div>

                    <!-- Imagen del Vehículo -->
                    <div>
                        <label class="block font-medium text-main mb-2" style="font-size: calc(1em - 0.075rem);">
                            Imagen del Vehículo
                        </label>
                        <div class="relative">
                            <input 
                                type="file" 
                                @change="e => form.url_imagen = e.target.files[0]"
                                accept="image/*"
                                class="w-full text-main border border-gray-300 rounded-md px-3 py-2 card-bg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                style="font-size: calc(1em - 0.125rem);" 
                            />
                        </div>
                        <p class="text-main opacity-50 mt-1" style="font-size: calc(1em - 0.25rem);">
                            Formatos permitidos: JPG, PNG, GIF (máx. 2MB)
                        </p>
                        <div v-if="form.errors.url_imagen" class="text-red-600 mt-1" style="font-size: calc(1em - 0.2rem);">
                            {{ form.errors.url_imagen }}
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex flex-col sm:flex-row justify-center gap-4 mt-8 md:col-span-2">
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="px-8 py-3 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors font-medium shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                            style="font-size: inherit;">
                            <span v-if="form.processing">
                                <svg class="inline w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Registrando...
                            </span>
                            <span v-else>✅ Registrar Vehículo</span>
                        </button>
                        
                        <a 
                            href="/vehiculos" 
                            class="px-8 py-3 text-white bg-gray-500 rounded-lg hover:bg-gray-600 transition-colors font-medium shadow-md hover:shadow-lg text-center"
                            style="font-size: inherit;">
                            ❌ Cancelar
                        </a>
                    </div>

                    <!-- Campos obligatorios -->
                    <div class="md:col-span-2 mt-4 p-3 card-bg rounded-lg border border-opacity-20 border-gray-300">
                        <p class="text-main opacity-60 text-center" style="font-size: calc(1em - 0.2rem);">
                            <span class="text-red-500">*</span> Campos obligatorios
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>