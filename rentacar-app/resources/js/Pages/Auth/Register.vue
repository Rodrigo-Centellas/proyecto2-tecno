<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import { useBaseUrl } from '@/composables/useBaseUrl';
const { url } = useBaseUrl();
const form = useForm({
  name: '',
  apellido: '',
  ci: '',
  domicilio: '',
  telefono: '',
  email: '',
  password: '',
  password_confirmation: '',
  documento_frontal: null,
  documento_trasero: null,
});

// Tema: 'auto' usa preferencia del sistema, 'light' o 'dark' forzado.
const stored = localStorage.getItem('theme') || 'auto';
const theme = ref(stored); // 'auto' | 'light' | 'dark'

const effectiveTheme = computed(() => {
  if (theme.value === 'auto') {
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }
  return theme.value;
});

const applyThemeClass = () => {
  const root = document.documentElement;
  if (effectiveTheme.value === 'dark') {
    root.classList.add('dark');
  } else {
    root.classList.remove('dark');
  }
};

const setTheme = (t) => {
  theme.value = t;
  localStorage.setItem('theme', t);
  applyThemeClass();
};

onMounted(() => {
  applyThemeClass();
  const media = window.matchMedia('(prefers-color-scheme: dark)');
  const listener = () => {
    if (theme.value === 'auto') applyThemeClass();
  };
  if (media.addEventListener) {
    media.addEventListener('change', listener);
  } else {
    media.addListener(listener);
  }
});

watch(effectiveTheme, () => {
  applyThemeClass();
});

const submit = () => {
  form.post(route(url('register')), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};

const handleFileChange = (field, event) => {
  const file = event.target.files[0];
  if (file) {
    if (file.size > 2 * 1024 * 1024) {
      alert('El archivo debe ser menor a 2MB');
      event.target.value = '';
      return;
    }
    if (!file.type.startsWith('image/')) {
      alert('Solo se permiten archivos de imagen');
      event.target.value = '';
      return;
    }
    form[field] = file;
  }
};
</script>

<template>
  <GuestLayout>
    <Head title="Registro" />

    <div class="max-w-xl mx-auto mt-12">
      <!-- Selector de tema -->
      <div class="flex justify-end mb-4 space-x-2">
        <button
          :class="['px-3 py-1 rounded-md text-sm font-medium', theme === 'light' ? 'bg-gray-200 text-gray-800' : 'border']"
          @click="setTheme('light')"
          aria-label="Tema claro"
        >
          Claro
        </button>
        <button
          :class="['px-3 py-1 rounded-md text-sm font-medium', theme === 'dark' ? 'bg-gray-700 text-white' : 'border']"
          @click="setTheme('dark')"
          aria-label="Tema oscuro"
        >
          Oscuro
        </button>
        <button
          :class="['px-3 py-1 rounded-md text-sm font-medium', theme === 'auto' ? 'bg-blue-500 text-white' : 'border']"
          @click="setTheme('auto')"
          aria-label="Tema automático"
        >
          Auto
        </button>
      </div>

      <form @submit.prevent="submit" class="space-y-6" style="font-size: inherit;">
        <!-- Contenedor general con tema aplicado -->
        <div
          class="card-bg p-6 rounded-2xl shadow-lg"
          :class="{
            'bg-white text-gray-900': effectiveTheme === 'light',
            'bg-[#1f2937] text-gray-100': effectiveTheme === 'dark'
          }"
        >
          <!-- Sección: Información Personal -->
          <div class="mb-8">
            <h3 class="text-xl font-semibold mb-4" style="font-size: calc(1em + 0.125rem);">
              Información Personal
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <InputLabel for="name" value="Nombre *" />
                <TextInput
                  id="name"
                  v-model="form.name"
                  type="text"
                  class="mt-1 block w-full"
                  required
                  autofocus
                  :class="{
                    'bg-gray-100': effectiveTheme === 'light',
                    'bg-gray-800 text-white': effectiveTheme === 'dark'
                  }"
                />
                <InputError :message="form.errors.name" class="mt-2" />
              </div>

              <div>
                <InputLabel for="apellido" value="Apellido *" />
                <TextInput
                  id="apellido"
                  v-model="form.apellido"
                  type="text"
                  class="mt-1 block w-full"
                  required
                  :class="{
                    'bg-gray-100': effectiveTheme === 'light',
                    'bg-gray-800 text-white': effectiveTheme === 'dark'
                  }"
                />
                <InputError :message="form.errors.apellido" class="mt-2" />
              </div>

              <div>
                <InputLabel for="ci" value="Cédula de Identidad *" />
                <TextInput
                  id="ci"
                  v-model="form.ci"
                  type="text"
                  class="mt-1 block w-full"
                  required
                  :class="{
                    'bg-gray-100': effectiveTheme === 'light',
                    'bg-gray-800 text-white': effectiveTheme === 'dark'
                  }"
                />
                <InputError :message="form.errors.ci" class="mt-2" />
              </div>

              <div>
                <InputLabel for="telefono" value="Teléfono *" />
                <TextInput
                  id="telefono"
                  v-model="form.telefono"
                  type="text"
                  class="mt-1 block w-full"
                  required
                  :class="{
                    'bg-gray-100': effectiveTheme === 'light',
                    'bg-gray-800 text-white': effectiveTheme === 'dark'
                  }"
                />
                <InputError :message="form.errors.telefono" class="mt-2" />
              </div>
            </div>

            <div class="mt-4">
              <InputLabel for="domicilio" value="Domicilio *" />
              <TextInput
                id="domicilio"
                v-model="form.domicilio"
                type="text"
                class="mt-1 block w-full"
                required
                :class="{
                  'bg-gray-100': effectiveTheme === 'light',
                  'bg-gray-800 text-white': effectiveTheme === 'dark'
                }"
              />
              <InputError :message="form.errors.domicilio" class="mt-2" />
            </div>
          </div>

          <!-- Sección: Credenciales -->
          <div class="mb-8">
            <h3 class="text-xl font-semibold mb-4" style="font-size: calc(1em + 0.125rem);">
              Credenciales de Acceso
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <InputLabel for="email" value="Correo Electrónico *" />
                <TextInput
                  id="email"
                  v-model="form.email"
                  type="email"
                  class="mt-1 block w-full"
                  required
                  autocomplete="username"
                  :class="{
                    'bg-gray-100': effectiveTheme === 'light',
                    'bg-gray-800 text-white': effectiveTheme === 'dark'
                  }"
                />
                <InputError :message="form.errors.email" class="mt-2" />
              </div>
              <div>
                <InputLabel for="password" value="Contraseña *" />
                <TextInput
                  id="password"
                  v-model="form.password"
                  type="password"
                  class="mt-1 block w-full"
                  required
                  autocomplete="new-password"
                  :class="{
                    'bg-gray-100': effectiveTheme === 'light',
                    'bg-gray-800 text-white': effectiveTheme === 'dark'
                  }"
                />
                <InputError :message="form.errors.password" class="mt-2" />
              </div>
              <div>
                <InputLabel for="password_confirmation" value="Confirmar Contraseña *" />
                <TextInput
                  id="password_confirmation"
                  v-model="form.password_confirmation"
                  type="password"
                  class="mt-1 block w-full"
                  required
                  autocomplete="new-password"
                  :class="{
                    'bg-gray-100': effectiveTheme === 'light',
                    'bg-gray-800 text-white': effectiveTheme === 'dark'
                  }"
                />
                <InputError :message="form.errors.password_confirmation" class="mt-2" />
              </div>
            </div>
          </div>

          <!-- Sección: Documentación -->
          <div class="mb-6">
            <h3 class="text-xl font-semibold mb-4" style="font-size: calc(1em + 0.125rem);">
              Documentación Requerida
            </h3>
            <p class="opacity-70 mb-4" style="font-size: calc(1em - 0.125rem);">
              Suba las imágenes de su documento de identidad (ambas caras):
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <InputLabel for="documento_frontal" value="Documento Frontal *" />
                <input
                  id="documento_frontal"
                  type="file"
                  accept="image/*"
                  @change="handleFileChange('documento_frontal', $event)"
                  class="mt-1 block w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  required
                />
                <p class="opacity-60 text-xs mt-1">
                  Formatos permitidos: JPG, PNG | Tamaño máximo: 2MB
                </p>
                <InputError :message="form.errors.documento_frontal" class="mt-2" />
              </div>

              <div>
                <InputLabel for="documento_trasero" value="Documento Trasero *" />
                <input
                  id="documento_trasero"
                  type="file"
                  accept="image/*"
                  @change="handleFileChange('documento_trasero', $event)"
                  class="mt-1 block w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  required
                />
                <p class="opacity-60 text-xs mt-1">
                  Formatos permitidos: JPG, PNG | Tamaño máximo: 2MB
                </p>
                <InputError :message="form.errors.documento_trasero" class="mt-2" />
              </div>
            </div>
          </div>

          <!-- Botón de envío -->
          <div class="flex justify-center mt-6">
            <PrimaryButton
              :disabled="form.processing"
              :class="{ 'opacity-50': form.processing }"
              class="px-8 py-3 shadow-md hover:shadow-lg transition-shadow"
            >
              <span v-if="form.processing">
                Procesando...
              </span>
              <span v-else>
                Registrarse
              </span>
            </PrimaryButton>
          </div>

          <!-- Nota legal -->
          <div class="mt-6 p-4 rounded-lg border border-opacity-20 border-gray-300">
            <p class="opacity-70 text-center" style="font-size: calc(1em - 0.15rem);">
              Al registrarse acepta nuestros términos y condiciones. Sus documentos serán verificados y su información será tratada de forma confidencial.
            </p>
          </div>

          <!-- Errores detallados (solo desarrollo) -->
          <div v-if="Object.keys(form.errors).length > 0" class="mt-4 p-4 rounded-lg border border-red-300 bg-red-50">
            <h4 class="text-red-800 font-semibold mb-2">Errores de validación:</h4>
            <ul class="text-red-700 text-sm list-disc pl-5">
              <li v-for="(error, field) in form.errors" :key="field">
                <strong>{{ field }}:</strong> {{ error }}
              </li>
            </ul>
          </div>
        </div>
      </form>
    </div>
  </GuestLayout>
</template>

<style scoped>
.card-bg {
  transition: background-color .3s, color .3s;
}
</style>
