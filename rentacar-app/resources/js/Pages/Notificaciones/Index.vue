<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
  notificaciones: {
    type: Object, // Cambiado a Object porque viene paginado
    default: () => ({ data: [] })
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  tipos: {
    type: Object,
    default: () => ({})
  }
});

const search = ref(props.filters?.search || '');

// Computed para extraer los datos del objeto paginado
const notificacionesSeguras = computed(() => {
  // Si es un objeto paginado, usar .data, si es array, usar directamente
  if (props.notificaciones && props.notificaciones.data) {
    return props.notificaciones.data;
  }
  return Array.isArray(props.notificaciones) ? props.notificaciones : [];
});

watch(search, (val) => {
  router.get('/notificaciones', { search: val }, { preserveState: true, replace: true });
});
</script>

<template>
  <Head title="Notificaciones" />

  <AuthenticatedLayout>
    <template #header>
      <h2
        class="text-xl font-semibold leading-tight text-main"
        style="font-size: calc(1em + 0.25rem);"
      >
        Notificaciones
      </h2>
    </template>

    <div class="py-12 text-main">
      <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
        <div class="p-8 rounded-lg shadow-lg card-bg space-y-6">
          <h1
            class="font-bold text-main"
            style="font-size: calc(1em + 0.5rem);"
          >
            Lista de Notificaciones
          </h1>

          <div class="max-w-md">
            <input
              v-model="search"
              type="text"
              placeholder="Buscar por título o mensaje..."
              class="w-full p-3 border rounded-lg card-bg focus:ring-2 focus:ring-blue-500 transition-colors"
              style="font-size: calc(1em - 0.075rem);"
            />
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full text-sm border-collapse">
              <thead class="border-b" :style="{ backgroundColor: 'var(--thead-bg)' }">
                <tr>
                  <th
                    class="px-4 py-3 text-left font-medium text-main border"
                    style="font-size: calc(1em - 0.075rem);"
                  >
                    ID
                  </th>
                  <th
                    class="px-4 py-3 text-left font-medium text-main border"
                    style="font-size: calc(1em - 0.075rem);"
                  >
                    Tipo
                  </th>
                  <th
                    class="px-4 py-3 text-left font-medium text-main border"
                    style="font-size: calc(1em - 0.075rem);"
                  >
                    Mensaje
                  </th>
                  <th
                    class="px-4 py-3 text-left font-medium text-main border"
                    style="font-size: calc(1em - 0.075rem);"
                  >
                    Usuario
                  </th>
                  <th
                    class="px-4 py-3 text-left font-medium text-main border"
                    style="font-size: calc(1em - 0.075rem);"
                  >
                    Fecha
                  </th>
                  <th
                    class="px-4 py-3 text-left font-medium text-main border"
                    style="font-size: calc(1em - 0.075rem);"
                  >
                    Tiempo
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr
                  v-for="n in notificacionesSeguras"
                  :key="n.id"
                  class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                >
                  <td class="px-4 py-3 border" style="font-size: inherit;">
                    {{ n.id }}
                  </td>
                  <td class="px-4 py-3 border" style="font-size: inherit;">
                    <span :class="['px-2 py-1 text-xs rounded-full', `bg-${n.color}-100 text-${n.color}-800`]">
                      {{ n.icono }} {{ n.tipo }}
                    </span>
                  </td>
                  <td
                    class="px-4 py-3 border whitespace-pre-line"
                    style="font-size: inherit;"
                  >
                    {{ n.mensaje }}
                  </td>
                  <td class="px-4 py-3 border" style="font-size: inherit;">
                    {{ n.user?.name || 'Sistema' }}
                  </td>
                  <td class="px-4 py-3 border" style="font-size: inherit;">
                    {{ new Date(n.fecha).toLocaleDateString() }}
                  </td>
                  <td class="px-4 py-3 border text-sm opacity-70" style="font-size: inherit;">
                    {{ n.tiempo_transcurrido || new Date(n.created_at).toLocaleString() }}
                  </td>
                </tr>
                <tr v-if="notificacionesSeguras.length === 0">
                  <td
                    colspan="6"
                    class="py-8 text-center opacity-70"
                    style="font-size: calc(1em - 0.1rem);"
                  >
                    No hay notificaciones
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Paginación -->
          <div v-if="notificaciones.links" class="mt-6">
            <nav class="flex justify-center">
              <div class="flex space-x-1">
                <template v-for="link in notificaciones.links" :key="link.label">
                  <button
                    v-if="link.url"
                    @click="router.visit(link.url)"
                    :class="[
                      'px-3 py-2 text-sm rounded-md transition-colors',
                      link.active 
                        ? 'bg-blue-600 text-white' 
                        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                    ]"
                    v-html="link.label"
                  ></button>
                  <span
                    v-else
                    :class="[
                      'px-3 py-2 text-sm rounded-md',
                      'bg-gray-100 text-gray-400 cursor-not-allowed'
                    ]"
                    v-html="link.label"
                  ></span>
                </template>
              </div>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>