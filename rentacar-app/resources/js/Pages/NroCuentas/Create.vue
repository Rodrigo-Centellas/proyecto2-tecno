<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const form = useForm({
  banco: '',
  nro_cuenta: '',
  es_activa: 1, // por defecto activa
})

const submit = () => form.post(route('nro-cuentas.store'))
</script>

<template>
  <Head title="Crear Número de Cuenta" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-main">
        Crear Número de Cuenta
      </h2>
    </template>

    <div class="py-12 text-main">
      <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
        <div class="p-8 rounded-lg shadow-lg card-bg space-y-6">
          <h1 class="font-bold text-2xl">Nuevo Número de Cuenta</h1>

          <form @submit.prevent="submit" class="space-y-6">

            <!-- Banco -->
            <div>
              <label class="block font-semibold mb-2">Banco *</label>
              <input
                v-model="form.banco"
                type="text"
                required
                class="w-full p-3 border rounded-lg"
              />
              <p v-if="form.errors.banco" class="text-red-500 text-sm mt-1">
                {{ form.errors.banco }}
              </p>
            </div>

            <!-- Número de Cuenta -->
            <div>
              <label class="block font-semibold mb-2">Número de Cuenta *</label>
              <input
                v-model="form.nro_cuenta"
                type="number"
                required
                class="w-full p-3 border rounded-lg"
              />
              <p v-if="form.errors.nro_cuenta" class="text-red-500 text-sm mt-1">
                {{ form.errors.nro_cuenta }}
              </p>
            </div>

            <!-- Es Activa (radios) -->
            <div>
              <label class="block font-semibold mb-2">¿Activa?</label>
              <div class="flex gap-4">
                <label class="inline-flex items-center">
                  <input type="radio" v-model="form.es_activa" :value="1" class="form-radio" />
                  <span class="ml-2">Sí</span>
                </label>
                <label class="inline-flex items-center">
                  <input type="radio" v-model="form.es_activa" :value="0" class="form-radio" />
                  <span class="ml-2">No</span>
                </label>
              </div>
              <p v-if="form.errors.es_activa" class="text-red-500 text-sm mt-1">
                {{ form.errors.es_activa }}
              </p>
            </div>

            <!-- Botones -->
            <div class="flex gap-4">
              <button type="submit" class="px-6 py-3 bg-green-600 text-white rounded-lg">
                Guardar
              </button>
              <Link href="{ route('nro-cuentas.index') }" class="underline">
                Cancelar
              </Link>
            </div>

          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
