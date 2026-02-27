<template>
  <v-app theme="dark">
    <router-view v-slot="{ Component, route }">
      <component :is="Component" />
    </router-view>
  </v-app>
</template>

<script setup>
// App root component
</script>
