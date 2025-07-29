<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
  canResetPassword: Boolean,
  status: String,
});

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <GuestLayout>
    <Head title="Log in" />

    <div
      v-if="status"
      class="mb-4 text-sm font-medium text-green-600"
      style="font-size: inherit"
    >
      {{ status }}
    </div>

    <form
      @submit.prevent="submit"
      class="mt-6 w-full max-w-md mx-auto overflow-hidden
             card-bg px-6 py-4 shadow-lg rounded-2xl text-main"
      style="font-size: inherit"
    >
      <!-- Email -->
      <div>
        <InputLabel for="email" value="Email" />

        <TextInput
          id="email"
          type="email"
          class="mt-1 block w-full"
          v-model="form.email"
          required
          autofocus
          autocomplete="username"
        />

        <InputError
          class="mt-2"
          :message="form.errors.email"
        />
      </div>

      <!-- Password -->
      <div class="mt-4">
        <InputLabel for="password" value="Password" />

        <TextInput
          id="password"
          type="password"
          class="mt-1 block w-full"
          v-model="form.password"
          required
          autocomplete="current-password"
        />

        <InputError
          class="mt-2"
          :message="form.errors.password"
        />
      </div>

      <!-- Remember Me -->
      <div class="mt-4 block" style="font-size: inherit">
        <label class="flex items-center">
          <Checkbox name="remember" v-model:checked="form.remember" />
          <span class="ms-2 text-sm text-gray-600">Remember me</span>
        </label>
      </div>

      <!-- Actions -->
      <div
        class="mt-4 flex items-center justify-end"
        style="font-size: inherit"
      >
        <Link
          v-if="canResetPassword"
          :href="route('password.request')"
          class="rounded-md text-sm text-gray-600 underline
                 hover:text-gray-900 focus:outline-none
                 focus:ring-2 focus:ring-indigo-500
                 focus:ring-offset-2"
          style="font-size: inherit"
        >
          Forgot your password?
        </Link>

        <PrimaryButton
          class="ms-4"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
          style="font-size: inherit"
        >
          Log in
        </PrimaryButton>
      </div>
    </form>
  </GuestLayout>
</template>
