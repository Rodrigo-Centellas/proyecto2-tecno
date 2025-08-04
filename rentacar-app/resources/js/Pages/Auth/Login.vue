<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch, computed, onMounted } from 'vue';
import { useBaseUrl } from '@/composables/useBaseUrl';
const { url } = useBaseUrl();
defineProps({
  canResetPassword: Boolean,
  status: String,
});

const form = useForm({
  email: '',
  password: '',
  remember: false,
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

const setTheme = (t) => {
  theme.value = t;
  localStorage.setItem('theme', t);
  applyThemeClass();
};

const applyThemeClass = () => {
  const root = document.documentElement;
  if (effectiveTheme.value === 'dark') {
    root.classList.add('dark');
  } else {
    root.classList.remove('dark');
  }
};

// Escuchar cambios en preferencia si está en auto
onMounted(() => {
  applyThemeClass();
  const media = window.matchMedia('(prefers-color-scheme: dark)');
  const listener = (e) => {
    if (theme.value === 'auto') applyThemeClass();
  };
  media.addEventListener ? media.addEventListener('change', listener) : media.addListener(listener);
});

// Reaplicar clase cuando cambie effectiveTheme
watch(effectiveTheme, () => {
  applyThemeClass();
});

const submit = () => {
  form.post(route(url('/login')), {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <GuestLayout>
    <Head title="Iniciar sesión" />

    <div class="max-w-md mx-auto mt-12">
      <!-- Switch de tema -->
      <div class="flex justify-end mb-4 space-x-2">
        <button
          :class="['px-3 py-1 rounded-md text-sm font-medium', theme === 'light' ? 'bg-gray-200 text-gray-800' : 'border']"
          @click="setTheme('light')"
          aria-label="Tema claro"
        >
          ☀️
        </button>
        <button
          :class="['px-3 py-1 rounded-md text-sm font-medium', theme === 'dark' ? 'bg-gray-700 text-white' : 'border']"
          @click="setTheme('dark')"
          aria-label="Tema oscuro"
        >
          🌙
        </button>
        <button
          :class="['px-3 py-1 rounded-md text-sm font-medium', theme === 'auto' ? 'bg-blue-500 text-white' : 'border']"
          @click="setTheme('auto')"
          aria-label="Tema automático"
        >
          Auto
        </button>
      </div>

      <div
        class="overflow-hidden card-bg px-6 py-8 shadow-lg rounded-2xl text-main"
        :class="{
          'bg-white': effectiveTheme === 'light',
          'bg-[#1f2937]': effectiveTheme === 'dark',
          'text-gray-900': effectiveTheme === 'light',
          'text-gray-100': effectiveTheme === 'dark'
        }"
        style="font-size: inherit;"
      >
        <div class="text-center mb-6">
          <h1 class="text-2xl font-bold">Iniciar sesión</h1>
          <p class="text-sm opacity-80">Ingresa tus credenciales para continuar</p>
        </div>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
          {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <!-- Email -->
          <div>
            <InputLabel for="email" value="Correo electrónico" />
            <TextInput
              id="email"
              type="email"
              class="mt-1 block w-full"
              v-model="form.email"
              required
              autofocus
              autocomplete="username"
              :class="{
                'bg-gray-100': effectiveTheme === 'light',
                'bg-gray-800 text-white': effectiveTheme === 'dark'
              }"
            />
            <InputError class="mt-2" :message="form.errors.email" />
          </div>

          <!-- Password -->
          <div>
            <InputLabel for="password" value="Contraseña" />
            <TextInput
              id="password"
              type="password"
              class="mt-1 block w-full"
              v-model="form.password"
              required
              autocomplete="current-password"
              :class="{
                'bg-gray-100': effectiveTheme === 'light',
                'bg-gray-800 text-white': effectiveTheme === 'dark'
              }"
            />
            <InputError class="mt-2" :message="form.errors.password" />
          </div>

          <!-- Remember -->
          <div class="flex items-center">
            <Checkbox name="remember" v-model:checked="form.remember" />
            <label class="ms-2 text-sm" :class="effectiveTheme === 'dark' ? 'text-gray-300' : 'text-gray-600'">
              Recuérdame
            </label>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-between">
            <div>
              <Link
                v-if="canResetPassword"
                :href="route('password.request')"
                class="text-sm underline hover:no-underline"
                :class="effectiveTheme === 'dark' ? 'text-gray-200' : 'text-gray-700'"
              >
                ¿Olvidaste tu contraseña?
              </Link>
            </div>
            <div>
              <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Iniciar sesión
              </PrimaryButton>
            </div>
          </div>
        </form>
      </div>
    </div>
  </GuestLayout>
</template>

<style scoped>
.card-bg {
  transition: background-color .3s, color .3s;
}
</style>
