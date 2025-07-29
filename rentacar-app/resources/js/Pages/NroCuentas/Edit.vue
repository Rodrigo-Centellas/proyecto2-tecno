<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  nroCuenta: Object,
})

const form = useForm({
  banco:      props.nroCuenta.banco,
  nro_cuenta: props.nroCuenta.nro_cuenta,
  es_activa:  props.nroCuenta.es_activa ? 1 : 0,
})

const submit = () =>
  form.put(route('nro-cuentas.update', props.nroCuenta.id))
</script>

<template>
  <Head :title="`Editar Número de Cuenta #${props.nroCuenta.id}`" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-main">
        Editar Número de Cuenta
      </h2>
    </template>

    <div class="py-12 text-main">
      <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
        <div class="p-8 rounded-lg shadow-lg card-bg space-y-6">
          <h1 class="font-bold text-2xl">
            Editar Cuenta #{{ props.nroCuenta.id }}
          </h1>

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
              <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg">
                Actualizar
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
