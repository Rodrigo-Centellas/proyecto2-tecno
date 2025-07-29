<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

// Props recibidos de Inertia
const props = defineProps({
  user: Object,
});

// Función para formatear fechas
const formatearFecha = (fecha) => {
  return new Date(fecha).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Estado para el lightbox
const selectedImage = ref(null);

// Abrir y cerrar el modal de imagen
const openImage = (path) => {
  selectedImage.value = path;
};
const closeImage = () => {
  selectedImage.value = null;
};
</script>

<template>
  <Head :title="`Usuario: ${user.name} ${user.apellido}`" />

  <AuthenticatedLayout>
    <template #header>
      <!-- Header con botón de volver y editar -->
      <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
        <div class="flex items-center space-x-3">
          <Link href="/users" class="text-main hover:text-main-dark">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </Link>
          <div class="p-2 rounded-lg card-bg">
            <svg class="w-6 h-6 text-main" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
          <h2 class="font-bold text-main" style="font-size: calc(1em + 0.5rem);">
            {{ user.name }} {{ user.apellido }}
          </h2>
        </div>

        <Link
          :href="`/users/${user.id}/edit`"
          class="flex items-center space-x-2 px-6 py-2 font-medium rounded-lg shadow-md card-bg hover:shadow-lg transition-shadow"
          style="font-size: inherit;"
        >
          <svg class="w-5 h-5 text-main" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
          <span class="text-main">Editar</span>
        </Link>
      </div>
    </template>

    <div class="py-12 text-main">
      <div class="mx-auto max-w-4xl sm:px-6 lg:px-8 space-y-6">

        <!-- Información Personal -->
        <section class="rounded-lg shadow-lg card-bg overflow-hidden">
          <header class="px-6 py-3 border-b border-opacity-20">
            <h3 class="font-semibold" style="font-size: calc(1em + 0.125rem);">
              👤 Información Personal
            </h3>
          </header>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block font-medium mb-1" style="font-size: calc(1em - 0.075rem);">Nombre Completo</label>
                <div class="font-semibold" style="font-size: calc(1em + 0.125rem);">
                  {{ user.name }} {{ user.apellido }}
                </div>
              </div>
              <div>
                <label class="block font-medium mb-1" style="font-size: calc(1em - 0.075rem);">Cédula de Identidad</label>
                <div style="font-size: inherit;">
                  {{ user.ci || 'No registrada' }}
                </div>
              </div>
              <div>
                <label class="block font-medium mb-1" style="font-size: calc(1em - 0.075rem);">Email</label>
                <div style="font-size: inherit;">{{ user.email }}</div>
                <div
                  v-if="user.email_verified_at"
                  class="flex items-center mt-1 text-green-600"
                  style="font-size: calc(1em - 0.15rem);"
                >
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M5 13l4 4L19 7" />
                  </svg>
                  Verificado
                </div>
                <div
                  v-else
                  class="flex items-center mt-1 text-orange-600"
                  style="font-size: calc(1em - 0.15rem);"
                >
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Sin verificar
                </div>
              </div>
              <div>
                <label class="block font-medium mb-1" style="font-size: calc(1em - 0.075rem);">Teléfono</label>
                <div style="font-size: inherit;">{{ user.telefono || 'No registrado' }}</div>
              </div>
              <div class="md:col-span-2">
                <label class="block font-medium mb-1" style="font-size: calc(1em - 0.075rem);">Domicilio</label>
                <div style="font-size: inherit;">{{ user.domicilio || 'No registrado' }}</div>
              </div>
            </div>
          </div>
        </section>

        <!-- Roles y Permisos -->
        <section v-if="user.roles?.length" class="rounded-lg shadow-lg card-bg overflow-hidden">
          <header class="px-6 py-3 border-b border-opacity-20">
            <h3 class="font-semibold" style="font-size: calc(1em + 0.125rem);">
              🔐 Roles y Permisos
            </h3>
          </header>
          <div class="p-6">
            <div class="flex flex-wrap gap-3">
              <span
                v-for="role in user.roles"
                :key="role.id"
                class="inline-flex items-center px-3 py-1 rounded-full font-medium card-bg"
                style="font-size: calc(1em - 0.15rem);"
              >
                {{ role.name }}
              </span>
            </div>
          </div>
        </section>

        <!-- Documentos -->
        <section class="rounded-lg shadow-lg card-bg overflow-hidden">
          <header class="px-6 py-3 border-b border-opacity-20">
            <h3 class="font-semibold" style="font-size: calc(1em + 0.125rem);">
              📄 Documentos
            </h3>
          </header>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <template v-for="doc in [
                { label: 'Documento Frontal', path: user.documento_frontal_path },
                { label: 'Documento Trasero', path: user.documento_trasero_path },
              ]" :key="doc.label">
                <div class="text-center">
                  <label class="block font-medium mb-3" style="font-size: calc(1em - 0.075rem);">
                    {{ doc.label }}
                  </label>
                  <div v-if="doc.path" class="space-y-2">
                    <img
                      :src="`/storage/${doc.path}`"
                      :alt="doc.label"
                      class="w-full max-w-xs mx-auto rounded-lg border shadow-sm hover:shadow-md transition-shadow cursor-pointer"
                      @click="openImage(doc.path)"
                    />
                    <p class="text-xs text-gray-500">Click para ampliar</p>
                  </div>
                  <div
                    v-else
                    class="flex flex-col items-center justify-center h-32 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50"
                  >
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="text-sm text-gray-500">No subido</span>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </section>

        <!-- Información del Sistema -->
        <section class="rounded-lg shadow-lg card-bg overflow-hidden">
          <header class="px-6 py-3 border-b border-opacity-20">
            <h3 class="font-semibold" style="font-size: calc(1em + 0.125rem);">
              ℹ️ Información del Sistema
            </h3>
          </header>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block font-medium mb-1" style="font-size: calc(1em - 0.075rem);">
                  ID del Usuario
                </label>
                <div style="font-size: inherit;">#{{ user.id }}</div>
              </div>
              <div>
                <label class="block font-medium mb-1" style="font-size: calc(1em - 0.075rem);">
                  Fecha de Registro
                </label>
                <div style="font-size: inherit;">{{ formatearFecha(user.created_at) }}</div>
              </div>
              <div>
                <label class="block font-medium mb-1" style="font-size: calc(1em - 0.075rem);">
                  Última Actualización
                </label>
                <div style="font-size: inherit;">{{ formatearFecha(user.updated_at) }}</div>
              </div>
              <div v-if="user.email_verified_at">
                <label class="block font-medium mb-1" style="font-size: calc(1em - 0.075rem);">
                  Email Verificado
                </label>
                <div style="font-size: inherit;">{{ formatearFecha(user.email_verified_at) }}</div>
              </div>
            </div>
          </div>
        </section>

      </div>
    </div>

    <!-- Modal / Lightbox -->
    <div
      v-if="selectedImage"
      class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50"
      @click="closeImage"
    >
      <img
        :src="`/storage/${selectedImage}`"
        alt="Ampliado"
        class="max-w-full max-h-full rounded-lg shadow-lg"
      />
    </div>
  </AuthenticatedLayout>
</template>
