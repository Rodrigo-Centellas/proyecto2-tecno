<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';

// Props
const props = defineProps({
  user: Object,
});

// Formateo de fechas
const formatearFecha = (fecha) => {
  if (!fecha) return 'N/A';
  return new Date(fecha).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Lightbox
const selectedImage = ref(null);
const openImage = (path) => {
  selectedImage.value = path;
};
const closeImage = () => {
  selectedImage.value = null;
};

// Tema
const stored = localStorage.getItem('theme') || 'auto';
const theme = ref(stored);
const effectiveTheme = computed(() => {
  if (theme.value === 'auto') {
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }
  return theme.value;
});

const applyTheme = () => {
  const root = document.documentElement;
  if (effectiveTheme.value === 'dark') {
    root.classList.add('dark');
  } else {
    root.classList.remove('dark');
  }
};

onMounted(() => {
  applyTheme();
  const media = window.matchMedia('(prefers-color-scheme: dark)');
  const listener = () => {
    if (theme.value === 'auto') applyTheme();
  };
  if (media.addEventListener) media.addEventListener('change', listener);
  else media.addListener(listener);
});

watch(effectiveTheme, () => applyTheme());

// Clases dinámicas
const containerBg = computed(() => ({
  'bg-white': effectiveTheme.value === 'light',
  'bg-gray-800 text-gray-100': effectiveTheme.value === 'dark',
  'rounded-lg': true,
}));

const sectionBg = computed(() => ({
  'card-bg bg-white': effectiveTheme.value === 'light',
  'card-bg bg-gray-800 text-gray-100': effectiveTheme.value === 'dark',
}));

const headingColor = computed(() => ({
  'text-gray-900': effectiveTheme.value === 'light',
  'text-gray-100': effectiveTheme.value === 'dark',
}));

const subtleText = computed(() => ({
  'text-gray-500': effectiveTheme.value === 'light',
  'text-gray-300': effectiveTheme.value === 'dark',
}));

const badgeVerifiedClass = (status) => {
  if (status === 'aprobado') return 'bg-green-100 text-green-800';
  if (status === 'rechazado') return 'bg-red-100 text-red-800';
  if (status === 'falta_informacion') return 'bg-yellow-100 text-yellow-800';
  // pendiente u otro
  return 'bg-gray-100 text-gray-800';
};
</script>

<template>

  <Head :title="`Usuario: ${user.name} ${user.apellido}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
        <div class="flex items-center space-x-3">
          <Link href="/users" class="hover:opacity-80">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          </Link>
          <div>
            <h2 class="font-bold" :class="headingColor" style="font-size: calc(1em + 0.5rem);">
              {{ user.name }} {{ user.apellido }}
            </h2>
          </div>
        </div>

        <Link :href="`/users/${user.id}/edit`"
          class="flex items-center space-x-2 px-6 py-2 font-medium rounded-lg shadow-md hover:shadow-lg transition-shadow"
          :class="effectiveTheme.value === 'dark' ? 'bg-gray-700 text-white' : 'bg-white text-gray-900'"
          style="font-size: inherit;">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
        <span>Editar</span>
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-4xl sm:px-6 lg:px-8 space-y-6">
        <!-- Información Personal -->
        <section :class="['overflow-hidden', sectionBg]">
          <header class="px-6 py-3 border-b border-opacity-20">
            <h3 class="font-semibold" :class="headingColor" style="font-size: calc(1em + 0.125rem);">
              Información Personal
            </h3>
          </header>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block font-medium mb-1" :class="subtleText" style="font-size: calc(1em - 0.075rem);">
                  Nombre Completo
                </label>
                <div class="font-semibold" :class="headingColor" style="font-size: calc(1em + 0.125rem);">
                  {{ user.name }} {{ user.apellido }}
                </div>
              </div>
              <div>
                <label class="block font-medium mb-1" :class="subtleText" style="font-size: calc(1em - 0.075rem);">
                  Cédula de Identidad
                </label>
                <div style="font-size: inherit;">{{ user.ci || 'No registrada' }}</div>
              </div>
              <div>
                <label class="block font-medium mb-1" :class="subtleText" style="font-size: calc(1em - 0.075rem);">
                  Email
                </label>
                <div>{{ user.email }}</div>
                <div class="flex items-center mt-1" style="font-size: calc(1em - 0.15rem);">
                  <template v-if="user.email_verified_at">
                    <div class="flex items-center text-green-600">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                      <span>Correo verificado</span>
                    </div>
                  </template>
                  <template v-else>
                    <div class="flex items-center text-orange-600">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <span>Correo no verificado</span>
                    </div>
                  </template>
                </div>
              </div>
              <div>
                <label class="block font-medium mb-1" :class="subtleText" style="font-size: calc(1em - 0.075rem);">
                  Teléfono
                </label>
                <div>{{ user.telefono || 'No registrado' }}</div>
              </div>
              <div class="md:col-span-2">
                <label class="block font-medium mb-1" :class="subtleText" style="font-size: calc(1em - 0.075rem);">
                  Domicilio
                </label>
                <div>{{ user.domicilio || 'No registrado' }}</div>
              </div>

            </div>
          </div>
        </section>

        <!-- Roles y Permisos -->
        <section v-if="user.roles?.length" :class="['overflow-hidden', sectionBg]">
          <header class="px-6 py-3 border-b border-opacity-20">
            <h3 class="font-semibold" :class="headingColor" style="font-size: calc(1em + 0.125rem);">
              Roles y Permisos
            </h3>
          </header>
          <div class="p-6">
            <div class="flex flex-wrap gap-3">
              <span v-for="role in user.roles" :key="role.id"
                class="inline-flex items-center px-3 py-1 rounded-full font-medium"
                :class="effectiveTheme.value === 'dark' ? 'bg-gray-700 text-gray-100' : 'bg-gray-100 text-gray-800'"
                style="font-size: calc(1em - 0.15rem);">
                {{ role.name }}
              </span>
            </div>
          </div>
        </section>

        <!-- Documentos -->
        <section :class="['overflow-hidden', sectionBg]">
          <header class="px-6 py-3 border-b border-opacity-20">
            <div class="flex justify-between items-center">
              <h3 class="font-semibold" :class="headingColor" style="font-size: calc(1em + 0.125rem);">
                Documentos
              </h3>
              <div>
                <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium"
                  :class="badgeVerifiedClass(user.verificado)">
                  {{ user.verificado ? user.verificado.replace('_', ' ') : 'pendiente' }}
                </span>
              </div>
            </div>
          </header>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <template v-for="doc in [
                { label: 'Documento Frontal', path: user.documento_frontal_path },
                { label: 'Documento Trasero', path: user.documento_trasero_path },
              ]" :key="doc.label">
                <div class="text-center">
                  <label class="block font-medium mb-3" :class="subtleText" style="font-size: calc(1em - 0.075rem);">
                    {{ doc.label }}
                  </label>
                  <div v-if="doc.path" class="space-y-2">
                    <img :src="`/storage/${doc.path}`" :alt="doc.label"
                      class="w-full max-w-xs mx-auto rounded-lg border shadow-sm hover:shadow-md transition-shadow cursor-pointer"
                      @click="openImage(doc.path)" />
                    <p class="text-xs" :class="subtleText">Click para ampliar</p>
                  </div>
                  <div v-else class="flex flex-col items-center justify-center h-32 rounded-lg border-2 border-dashed"
                    :class="effectiveTheme.value === 'dark' ? 'border-gray-600 bg-gray-700' : 'border-gray-300 bg-gray-50'">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="text-sm" :class="subtleText">No subido</span>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </section>

        <!-- Información del Sistema -->
        <section :class="['overflow-hidden', sectionBg]">
          <header class="px-6 py-3 border-b border-opacity-20">
            <h3 class="font-semibold" :class="headingColor" style="font-size: calc(1em + 0.125rem);">
              Información del Sistema
            </h3>
          </header>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block font-medium mb-1" :class="subtleText" style="font-size: calc(1em - 0.075rem);">
                  ID del Usuario
                </label>
                <div>#{{ user.id }}</div>
              </div>
              <div>
                <label class="block font-medium mb-1" :class="subtleText" style="font-size: calc(1em - 0.075rem);">
                  Fecha de Registro
                </label>
                <div>{{ formatearFecha(user.created_at) }}</div>
              </div>
              <div>
                <label class="block font-medium mb-1" :class="subtleText" style="font-size: calc(1em - 0.075rem);">
                  Última Actualización
                </label>
                <div>{{ formatearFecha(user.updated_at) }}</div>
              </div>
              <div v-if="user.email_verified_at">
                <label class="block font-medium mb-1" :class="subtleText" style="font-size: calc(1em - 0.075rem);">
                  Email Verificado
                </label>
                <div>{{ formatearFecha(user.email_verified_at) }}</div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>

    <!-- Lightbox -->
    <div v-if="selectedImage" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50"
      @click="closeImage">
      <img :src="`/storage/${selectedImage}`" alt="Ampliado" class="max-w-full max-h-full rounded-lg shadow-lg" />
    </div>
  </AuthenticatedLayout>
</template>
