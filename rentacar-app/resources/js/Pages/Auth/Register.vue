<script setup>
import GuestLayout    from '@/Layouts/GuestLayout.vue'
import InputError     from '@/Components/InputError.vue'
import InputLabel     from '@/Components/InputLabel.vue'
import PrimaryButton  from '@/Components/PrimaryButton.vue'
import TextInput      from '@/Components/TextInput.vue'
import { Head, useForm } from '@inertiajs/vue3'

const form = useForm({
  name:                       '',
  apellido:                   '',
  ci:                         '',
  domicilio:                  '',
  telefono:                   '',
  email:                      '',
  password:                   '',
  password_confirmation:      '',
  documento_frontal:          null,
  documento_trasero:          null,
})

const submit = () => {
  // CORREGIDO: Usar form.post sin enctype, Inertia maneja automáticamente los archivos
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
    onError: (errors) => {
      console.log('Errores de validación:', errors)
    },
    onSuccess: () => {
      console.log('Registro exitoso')
    }
  })
}

const handleFileChange = (field, event) => {
  const file = event.target.files[0]
  if (file) {
    // Validar tamaño del archivo (2MB máximo)
    if (file.size > 2 * 1024 * 1024) {
      alert('El archivo debe ser menor a 2MB')
      event.target.value = ''
      return
    }
    
    // Validar tipo de archivo
    if (!file.type.startsWith('image/')) {
      alert('Solo se permiten archivos de imagen')
      event.target.value = ''
      return
    }
    
    form[field] = file
    console.log(`Archivo ${field} seleccionado:`, file.name)
  }
}
</script>

<template>
  <GuestLayout>
    <Head title="Register" />

    <!-- CORREGIDO: Removido enctype, Inertia lo maneja automáticamente -->
    <form
      @submit.prevent="submit"
      class="space-y-6 text-main"
      style="font-size: inherit"
    >
      <!-- Información Personal -->
      <div class="card-bg p-6 rounded-2xl shadow-lg">
        <h3 class="text-xl font-semibold mb-4 text-main" style="font-size: calc(1em + 0.125rem);">
          👤 Información Personal
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Nombre -->
          <div>
            <InputLabel for="name" value="Nombre *" class="text-main font-semibold" style="font-size: calc(1em - 0.075rem);" />
            <TextInput
              id="name"
              v-model="form.name"
              type="text"
              class="mt-1 block w-full card-bg text-main"
              style="font-size: inherit;"
              required autofocus
            />
            <InputError :message="form.errors.name" class="mt-2" />
          </div>
          <!-- Apellido -->
          <div>
            <InputLabel for="apellido" value="Apellido *" class="text-main font-semibold" style="font-size: calc(1em - 0.075rem);" />
            <TextInput
              id="apellido"
              v-model="form.apellido"
              type="text"
              class="mt-1 block w-full card-bg text-main"
              style="font-size: inherit;"
              required
            />
            <InputError :message="form.errors.apellido" class="mt-2" />
          </div>
          <!-- CI -->
          <div>
            <InputLabel for="ci" value="Cédula de Identidad *" class="text-main font-semibold" style="font-size: calc(1em - 0.075rem);" />
            <TextInput
              id="ci"
              v-model="form.ci"
              type="text"
              class="mt-1 block w-full card-bg text-main"
              style="font-size: inherit;"
              required
            />
            <InputError :message="form.errors.ci" class="mt-2" />
          </div>
          <!-- Teléfono -->
          <div>
            <InputLabel for="telefono" value="Teléfono *" class="text-main font-semibold" style="font-size: calc(1em - 0.075rem);" />
            <TextInput
              id="telefono"
              v-model="form.telefono"
              type="text"
              class="mt-1 block w-full card-bg text-main"
              style="font-size: inherit;"
              required
            />
            <InputError :message="form.errors.telefono" class="mt-2" />
          </div>
        </div>
        <!-- Domicilio -->
        <div class="mt-4">
          <InputLabel for="domicilio" value="Domicilio *" class="text-main font-semibold" style="font-size: calc(1em - 0.075rem);" />
          <TextInput
            id="domicilio"
            v-model="form.domicilio"
            type="text"
            class="mt-1 block w-full card-bg text-main"
            style="font-size: inherit;"
            required
          />
          <InputError :message="form.errors.domicilio" class="mt-2" />
        </div>
      </div>

      <!-- Credenciales de Acceso -->
      <div class="card-bg p-6 rounded-2xl shadow-lg">
        <h3 class="text-xl font-semibold mb-4 text-main" style="font-size: calc(1em + 0.125rem);">
          🔐 Credenciales de Acceso
        </h3>
        <div class="mb-4">
          <InputLabel for="email" value="Correo Electrónico *" class="text-main font-semibold" style="font-size: calc(1em - 0.075rem);" />
          <TextInput
            id="email"
            v-model="form.email"
            type="email"
            class="mt-1 block w-full card-bg text-main"
            style="font-size: inherit;"
            required autocomplete="username"
          />
          <InputError :message="form.errors.email" class="mt-2" />
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <InputLabel for="password" value="Contraseña *" class="text-main font-semibold" style="font-size: calc(1em - 0.075rem);" />
            <TextInput
              id="password"
              v-model="form.password"
              type="password"
              class="mt-1 block w-full card-bg text-main"
              style="font-size: inherit;"
              required autocomplete="new-password"
            />
            <InputError :message="form.errors.password" class="mt-2" />
          </div>
          <div>
            <InputLabel for="password_confirmation" value="Confirmar Contraseña *" class="text-main font-semibold" style="font-size: calc(1em - 0.075rem);" />
            <TextInput
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              class="mt-1 block w-full card-bg text-main"
              style="font-size: inherit;"
              required autocomplete="new-password"
            />
            <InputError :message="form.errors.password_confirmation" class="mt-2" />
          </div>
        </div>
      </div>

      <!-- Documentación Requerida -->
      <div class="card-bg p-6 rounded-2xl shadow-lg">
        <h3 class="text-xl font-semibold mb-4 text-main" style="font-size: calc(1em + 0.125rem);">
          📄 Documentación Requerida
        </h3>
        <p class="text-main opacity-70 mb-4" style="font-size: calc(1em - 0.125rem);">
          Suba las imágenes de su documento de identidad (ambas caras):
        </p>

        <!-- Documento Frontal -->
        <div class="mb-4">
          <InputLabel for="documento_frontal" value="Documento Frontal *" class="text-main font-semibold" style="font-size: calc(1em - 0.075rem);" />
          <input
            id="documento_frontal"
            type="file"
            accept="image/*"
            @change="handleFileChange('documento_frontal', $event)"
            class="mt-1 block w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
            style="font-size: inherit;"
            required
          />
          <p class="text-main opacity-60 text-xs mt-1">
            📌 Formatos permitidos: JPG, PNG | Tamaño máximo: 2MB
          </p>
          <InputError :message="form.errors.documento_frontal" class="mt-2" />
        </div>

        <!-- Documento Trasero -->
        <div class="mb-4">
          <InputLabel for="documento_trasero" value="Documento Trasero *" class="text-main font-semibold" style="font-size: calc(1em - 0.075rem);" />
          <input
            id="documento_trasero"
            type="file"
            accept="image/*"
            @change="handleFileChange('documento_trasero', $event)"
            class="mt-1 block w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 focus:border-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
            style="font-size: inherit;"
            required
          />
          <p class="text-main opacity-60 text-xs mt-1">
            📌 Formatos permitidos: JPG, PNG | Tamaño máximo: 2MB
          </p>
          <InputError :message="form.errors.documento_trasero" class="mt-2" />
        </div>
      </div>

      <!-- Botón de Registro -->
      <div class="flex justify-center pt-4">
        <PrimaryButton
          :disabled="form.processing"
          :class="{ 'opacity-50': form.processing }"
          class="px-8 py-3 shadow-md hover:shadow-lg transition-shadow"
          style="font-size: inherit"
        >
          <span v-if="form.processing">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Procesando...
          </span>
          <span v-else>
            🚗 Registrarse
          </span>
        </PrimaryButton>
      </div>

      <!-- Nota Legal -->
      <div class="card-bg p-4 rounded-lg border border-opacity-20 border-gray-300 mt-4">
        <p class="text-main opacity-70 text-center" style="font-size: calc(1em - 0.15rem);">
          📋 Al registrarse acepta nuestros términos y condiciones.<br>
          ✅ Sus documentos serán verificados por nuestro equipo.<br>
          🔒 Su información está protegida y será tratada de forma confidencial.
        </p>
      </div>

      <!-- Debug Info (solo para desarrollo) -->
      <div v-if="Object.keys(form.errors).length > 0" class="card-bg p-4 rounded-lg border border-red-300 bg-red-50">
        <h4 class="text-red-800 font-semibold mb-2">Errores de validación:</h4>
        <ul class="text-red-700 text-sm">
          <li v-for="(error, field) in form.errors" :key="field">
            <strong>{{ field }}:</strong> {{ error }}
          </li>
        </ul>
      </div>
    </form>
  </GuestLayout>
</template>