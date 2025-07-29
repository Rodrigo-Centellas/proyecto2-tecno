<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div
    class="flex min-h-screen flex-col items-center
           bg-gray-100 dark:bg-gray-900
           pt-6 sm:justify-center sm:pt-0
           text-main"
    style="font-size: inherit"
  >
    <!-- Logo -->
    <div>
      <Link href="/" class="flex items-center space-x-2">
        <ApplicationLogo
          class="h-20 w-20 fill-current text-main opacity-80"
          style="font-size: inherit"
        />
        <span
          class="font-bold"
          style="font-size: calc(1em + 0.5rem)"
        >
          Tu App
        </span>
      </Link>
    </div>

    <!-- Content Container -->
    <div
      class="mt-6 w-full max-w-md mx-auto overflow-hidden
             card-bg px-6 py-4 shadow-lg rounded-2xl text-main"
      style="font-size: inherit"
    >
      <slot />
    </div>
  </div>
</template>
