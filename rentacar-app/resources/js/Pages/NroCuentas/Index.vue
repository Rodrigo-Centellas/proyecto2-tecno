<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref } from 'vue'
import Swal from 'sweetalert2'

// Props recibidos de Inertia
const props = defineProps({
  nroCuentas: Array,
  filters: Object,
})

// Estado del input de búsqueda
const search = ref(props.filters.search || '')

// Función para buscar manteniendo estado y reemplazo de URL
const buscar = () => {
  router.get(route('nro-cuentas.index'), { search: search.value }, {
    preserveState: true,
    replace: true,
  })
}

// Función para eliminar con confirmación
const eliminar = id => {
  Swal.fire({
    title: '¿Eliminar?',
    text: 'Esta acción no se puede deshacer.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
  }).then(result => {
    if (result.isConfirmed) {
      router.delete(route('nro-cuentas.destroy', id), {
        onSuccess: () => {
          Swal.fire('Eliminado', 'Registro eliminado.', 'success')
        }
      })
    }
  })
}
</script>

<template>
  <Head title="Números de Cuenta" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-main">Números de Cuenta</h2>
    </template>

    <div class="py-12 text-main">
      <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
        <div class="p-8 rounded-lg shadow-lg card-bg space-y-6">
          <div class="flex justify-between items-center">
            <h1 class="font-bold text-2xl">Listado de Números de Cuenta</h1>
            <Link
              href="/nro-cuentas/create"
              class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              Nuevo
            </Link>
          </div>

          <form @submit.prevent="buscar" class="flex gap-4 max-w-md">
            <input
              v-model="search"
              type="text"
              placeholder="Buscar por banco o número"
              class="flex-1 p-3 border rounded-lg card-bg focus:ring-2 focus:ring-blue-500 transition-colors"
            />
            <button
              type="submit"
              class="px-6 py-3 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition-colors"
            >
              Buscar
            </button>
          </form>

          <div class="overflow-x-auto">
            <table class="min-w-full text-sm border-collapse">
              <thead class="border-b bg-gray-100">
                <tr>
                  <th class="p-3 text-left">ID</th>
                  <th class="p-3 text-left">Banco</th>
                  <th class="p-3 text-left">Número</th>
                  <th class="p-3 text-left">Activo</th>
                  <th class="p-3 text-left">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y">
                <tr v-for="c in nroCuentas" :key="c.id">
                  <td class="p-3">{{ c.id }}</td>
                  <td class="p-3">{{ c.banco }}</td>
                  <td class="p-3">{{ c.nro_cuenta }}</td>
                  <td class="p-3">
                    <span v-if="c.es_activa" class="text-green-600 font-semibold">Sí</span>
                    <span v-else class="text-red-600 font-semibold">No</span>
                  </td>
                  <td class="p-3 flex gap-2">
                    <Link
                      :href="`/nro-cuentas/${c.id}/edit`"
                      class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition-colors"
                    >
                      Editar
                    </Link>
                    <button
                      @click="eliminar(c.id)"
                      class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition-colors"
                    >
                      Eliminar
                    </button>
                  </td>
                </tr>
                <tr v-if="nroCuentas.length === 0">
                  <td colspan="5" class="p-8 text-center opacity-50">No hay registros</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
