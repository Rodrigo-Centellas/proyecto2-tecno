<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const form = useForm({
  descripcion: '',
  activa: 1, // por defecto activa = true
})

const submit = () => {
  form.post(route('clausulas.store'))
}
</script>

<template>
  <Head title="Crear Cláusula" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-main">
        Crear Cláusula
      </h2>
    </template>

    <div class="py-12 max-w-md mx-auto text-main">
      <div class="card-bg p-8 rounded-2xl shadow-lg">
        <h1 class="font-bold mb-6 text-2xl">Nueva Cláusula</h1>

        <form @submit.prevent="submit" class="space-y-6">

          <!-- Descripción -->
          <div>
            <label class="block font-semibold mb-2">Descripción</label>
            <input
              v-model="form.descripcion"
              type="text"
              placeholder="Ej. El vehículo debe devolverse limpio"
              class="w-full p-3 border rounded-lg card-bg focus:ring-2 focus:ring-blue-500"
            />
            <p v-if="form.errors.descripcion" class="text-red-500 text-sm mt-1">
              {{ form.errors.descripcion }}
            </p>
          </div>

          <!-- Activa con radios -->
          <div>
            <label class="block font-semibold mb-2">Activa</label>
            <div class="flex gap-4">
              <label class="inline-flex items-center">
                <input
                  type="radio"
                  v-model="form.activa"
                  :value="1"
                  class="form-radio h-4 w-4"
                />
                <span class="ml-2">Sí</span>
              </label>
              <label class="inline-flex items-center">
                <input
                  type="radio"
                  v-model="form.activa"
                  :value="0"
                  class="form-radio h-4 w-4"
                />
                <span class="ml-2">No</span>
              </label>
            </div>
            <p v-if="form.errors.activa" class="text-red-500 text-sm mt-1">
              {{ form.errors.activa }}
            </p>
          </div>

          <!-- Botón Guardar -->
          <div class="flex gap-4">
            <button
              type="submit"
              class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
              Guardar
            </button>
            <Link
              :href="route('clausulas.index')"
              class="self-center text-sm underline opacity-80 hover:opacity-100"
            >
              Cancelar
            </Link>
          </div>

        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
