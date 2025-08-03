<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
  registro: Object,
  vehiculos: Array,
  mantenimientos: Array,
});

const $page = usePage();

const hasPermission = computed(() => (permission) => {
  const user = $page.props.auth.user;
  if (user?.roles?.some(r => r.name === 'Administrador')) return true;
  return user?.permissions?.includes(permission) || false;
});

const form = useForm({
  fecha: props.registro.fecha || '',
  monto: props.registro.monto || '',
  vehiculo_id: props.registro.vehiculo_id || '',
  mantenimiento_id: props.registro.mantenimiento_id || '',
});

const submit = async () => {
  if (!hasPermission('mantenimientos.editar')) {
    Swal.fire('Error', 'No tienes permiso para editar registros', 'error');
    return;
  }

  form.put(`/registro-mantenimientos/${props.registro.id}`, {
    onSuccess: () => {
      Swal.fire('OK', 'Registro actualizado correctamente', 'success');
    },
    onError: () => {
      Swal.fire('Error', 'Revisa los campos marcados', 'error');
    },
    preserveScroll: true,
  });
};
</script>

<template>
  <Head :title="`Editar Registro #${registro.id}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-main">
          Editar Registro de Mantenimiento
        </h2>
        <button
          @click="router.visit('/registro-mantenimientos')"
          class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors"
        >
          ← Volver
        </button>
      </div>
    </template>

    <div class="py-12 text-main">
      <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
        <div class="p-6 rounded-lg shadow-lg card-bg">
          <h1 class="font-bold text-main mb-4" style="font-size: calc(1em + 0.5rem);">
            🛠️ Editar Registro #{{ registro.id }}
          </h1>

          <div class="grid grid-cols-1 gap-6">
            <!-- Vehículo -->
            <div>
              <label class="block font-medium mb-1">Vehículo</label>
              <select
                v-model="form.vehiculo_id"
                class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 transition-colors"
              >
                <option value="">Seleccione un vehículo</option>
                <option v-for="v in vehiculos" :key="v.id" :value="v.id">
                  {{ v.tipo }} - {{ v.marca }} {{ v.modelo }} ({{ v.placa }})
                </option>
              </select>
              <div v-if="form.errors.vehiculo_id" class="text-red-600 text-sm mt-1">
                {{ form.errors.vehiculo_id }}
              </div>
            </div>

            <!-- Mantenimiento -->
            <div>
              <label class="block font-medium mb-1">Mantenimiento</label>
              <select
                v-model="form.mantenimiento_id"
                class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 transition-colors"
              >
                <option value="">Seleccione un mantenimiento</option>
                <option v-for="m in mantenimientos" :key="m.id" :value="m.id">
                  {{ m.nombre }}
                </option>
              </select>
              <div v-if="form.errors.mantenimiento_id" class="text-red-600 text-sm mt-1">
                {{ form.errors.mantenimiento_id }}
              </div>
            </div>

            <!-- Fecha -->
            <div>
              <label class="block font-medium mb-1">Fecha</label>
              <input
                type="date"
                v-model="form.fecha"
                class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 transition-colors"
              />
              <div v-if="form.errors.fecha" class="text-red-600 text-sm mt-1">
                {{ form.errors.fecha }}
              </div>
            </div>

            <!-- Monto -->
            <div>
              <label class="block font-medium mb-1">Monto</label>
              <input
                type="number"
                step="0.01"
                v-model="form.monto"
                placeholder="0.00"
                class="w-full p-3 border rounded-lg card-bg text-main focus:ring-2 focus:ring-blue-500 transition-colors"
              />
              <div v-if="form.errors.monto" class="text-red-600 text-sm mt-1">
                {{ form.errors.monto }}
              </div>
            </div>
          </div>

          <div class="mt-6 flex gap-4">
            <button
              @click.prevent="submit"
              :disabled="form.processing"
              class="flex-1 px-6 py-3 bg-yellow-600 text-white font-medium rounded-lg hover:bg-yellow-700 transition-colors shadow"
            >
              Guardar Cambios
            </button>
            <button
              @click="router.visit('/registro-mantenimientos')"
              type="button"
              class="px-6 py-3 bg-gray-200 text-gray-800 font-medium rounded-lg hover:bg-gray-300 transition-colors"
            >
              Cancelar
            </button>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
