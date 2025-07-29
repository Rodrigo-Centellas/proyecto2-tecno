<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  clausula: {
    type: Object,
    required: true,
  },
})

const form = useForm({
  descripcion: props.clausula.descripcion,
  activa:      props.clausula.activa ? 1 : 0,
})

const submit = () => {
  form.put(route('clausulas.update', props.clausula.id))
}
</script>

<template>
  <Head title="Editar Cláusula" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-main">
        Editar Cláusula
      </h2>
    </template>

    <div class="py-12 max-w-md mx-auto text-main">
      <div class="card-bg p-6 rounded-2xl shadow-lg">
        <form @submit.prevent="submit" class="space-y-6">

          <!-- Descripción -->
          <div>
            <label class="block font-semibold mb-2">
              Descripción
            </label>
            <input
              v-model="form.descripcion"
              type="text"
              class="w-full p-3 border rounded-lg card-bg focus:ring-2 focus:ring-blue-500"
            />
            <p v-if="form.errors.descripcion" class="text-red-500 text-sm mt-1">
              {{ form.errors.descripcion }}
            </p>
          </div>

          <!-- Activa con radios -->
          <div>
            <label class="block font-semibold mb-2">
              Activa
            </label>
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

          <!-- Botones -->
          <div class="flex gap-4">
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-md"
            >
              Actualizar
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
