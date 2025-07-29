<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps({
    vehiculo: Object,
})

// Inicializamos el formulario directamente con los datos del vehículo
const form = useForm({
    placa: props.vehiculo.placa ?? '',
    marca: props.vehiculo.marca ?? '',
    modelo: props.vehiculo.modelo ?? '',
    estado: props.vehiculo.estado ?? '',
    monto_garantia: props.vehiculo.monto_garantia ?? '',
    precio_dia: props.vehiculo.precio_dia ?? '',
    tipo: props.vehiculo.tipo ?? '',
    url_imagen: null, // Para nueva imagen
})

const submit = () => {
    const data = new FormData()
    data.append('_method', 'PUT')
    data.append('placa', form.placa)
    data.append('marca', form.marca)
    data.append('modelo', form.modelo)
    data.append('estado', form.estado)
    data.append('monto_garantia', form.monto_garantia)
    data.append('precio_dia', form.precio_dia)
    data.append('tipo', form.tipo)

    if (form.url_imagen) {
        data.append('url_imagen', form.url_imagen)
    }

    form.transform(() => data).submit('post', route('vehiculos.update', props.vehiculo.id), {
        method: 'post', // Laravel interpreta PUT con _method si es formData
        onError: (errors) => console.error(errors),
        preserveScroll: true,
    })
}
</script>

<template>
    <Head title="Editar Vehículo" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-main">Editar Vehículo</h2>
        </template>

        <div class="py-12 text-main">
            <div class="w-full max-w-4xl px-6 py-8 mx-auto rounded-lg shadow-lg card-bg">
                <!-- Título del formulario -->
                <div class="mb-6 text-center">
                    <h1 class="font-bold text-main" style="font-size: calc(1em + 0.5rem);">
                        Actualizar Información del Vehículo
                    </h1>
                    <p class="text-main opacity-70 mt-2" style="font-size: calc(1em - 0.125rem);">
                        Modifica los datos del vehículo según sea necesario
                    </p>
                </div>

                <form @submit.prevent="submit" enctype="multipart/form-data"
                    class="grid grid-cols-1 gap-6 md:grid-cols-2">

                    <!-- Placa -->
                    <div>
                        <label class="block font-medium text-main mb-2" style="font-size: calc(1em - 0.075rem);">
                            Placa
                        </label>
                        <input 
                            type="text" 
                            v-model="form.placa" 
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
                            Marca
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
                            Modelo
                        </label>
                        <input 
                            type="text" 
                            v-model="form.modelo" 
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
                            Estado
                        </label>
                        <select 
                            v-model="form.estado" 
                            class="w-full border rounded-md px-3 py-2 card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            style="font-size: inherit;">
                            <option disabled value="">Seleccione un estado</option>
                            <option value="Disponible">Disponible</option>
                            <option value="Reservado">Reservado</option>
                            <option value="En uso">En uso</option>
                            <option value="En mantenimiento">En mantenimiento</option>
                            <option value="Retirado">Retirado</option>
                        </select>
                        <div v-if="form.errors.estado" class="text-red-600 mt-1" style="font-size: calc(1em - 0.2rem);">
                            {{ form.errors.estado }}
                        </div>
                    </div>

                    <!-- Monto Garantía -->
                    <div>
                        <label class="block font-medium text-main mb-2" style="font-size: calc(1em - 0.075rem);">
                            Monto Garantía (Bs)
                        </label>
                        <input 
                            type="number" 
                            v-model="form.monto_garantia"
                            class="w-full border rounded-md px-3 py-2 card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            style="font-size: inherit;" 
                        />
                        <div v-if="form.errors.monto_garantia" class="text-red-600 mt-1" style="font-size: calc(1em - 0.2rem);">
                            {{ form.errors.monto_garantia }}
                        </div>
                    </div>

                    <!-- Precio por Día -->
                    <div>
                        <label class="block font-medium text-main mb-2" style="font-size: calc(1em - 0.075rem);">
                            Precio por Día (Bs)
                        </label>
                        <input 
                            type="number" 
                            v-model="form.precio_dia"
                            class="w-full border rounded-md px-3 py-2 card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            style="font-size: inherit;" 
                        />
                        <div v-if="form.errors.precio_dia" class="text-red-600 mt-1" style="font-size: calc(1em - 0.2rem);">
                            {{ form.errors.precio_dia }}
                        </div>
                    </div>

                    <!-- Tipo -->
                    <div>
                        <label class="block font-medium text-main mb-2" style="font-size: calc(1em - 0.075rem);">
                            Tipo
                        </label>
                        <select 
                            v-model="form.tipo" 
                            class="w-full border rounded-md px-3 py-2 card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            style="font-size: inherit;">
                            <option disabled value="">Seleccione un tipo</option>
                            <option value="Sedán">Sedán</option>
                            <option value="Hatchback">Hatchback</option>
                            <option value="SUV">SUV</option>
                            <option value="Pickup">Pickup</option>
                            <option value="Van">Van</option>
                        </select>
                        <div v-if="form.errors.tipo" class="text-red-600 mt-1" style="font-size: calc(1em - 0.2rem);">
                            {{ form.errors.tipo }}
                        </div>
                    </div>

                    <!-- Imagen actual -->
                    <div>
                        <label class="block font-medium text-main mb-2" style="font-size: calc(1em - 0.075rem);">
                            Imagen actual
                        </label>
                        <div class="border border-dashed border-gray-300 rounded-lg p-4 text-center">
                            <img 
                                v-if="props.vehiculo.url_imagen" 
                                :src="props.vehiculo.url_imagen"
                                class="object-cover w-32 h-20 rounded-lg mx-auto shadow-sm" 
                            />
                            <div v-else class="text-main opacity-50 py-4" style="font-size: calc(1em - 0.125rem);">
                                <svg class="w-8 h-8 mx-auto mb-2 text-main opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                No hay imagen guardada
                            </div>
                        </div>
                    </div>

                    <!-- Cambiar Imagen -->
                    <div>
                        <label class="block font-medium text-main mb-2" style="font-size: calc(1em - 0.075rem);">
                            Cambiar Imagen
                        </label>
                        <input 
                            type="file" 
                            @change="e => form.url_imagen = e.target.files[0]"
                            accept="image/*"
                            class="w-full text-main border border-gray-300 rounded-md px-3 py-2 card-bg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            style="font-size: calc(1em - 0.125rem);" 
                        />
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
                            <span v-if="form.processing">Actualizando...</span>
                            <span v-else>✅ Actualizar Vehículo</span>
                        </button>
                        
                        <a 
                            href="/vehiculos" 
                            class="px-8 py-3 text-white bg-gray-500 rounded-lg hover:bg-gray-600 transition-colors font-medium shadow-md hover:shadow-lg text-center"
                            style="font-size: inherit;">
                            ❌ Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>