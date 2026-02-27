<template>
  <header class="page-header">
    <div class="container">
      <router-link to="/" class="header-logo">
        <img src="/assets/images/logo.png" alt="Psiké Deloun Arts Logo">
      </router-link>
      <nav class="header-nav">
        <router-link to="/" :class="{ active: $route.name === 'Home' }">Início</router-link>
        <router-link to="/produtos" :class="{ active: $route.name === 'Products' }">Produtos</router-link>
        <router-link to="/carrinho" :class="{ active: $route.name === 'Cart' }">Carrinho</router-link>
        <a href="/admin/login" class="header-login-btn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; margin-right: 6px;">
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/>
          </svg>
          Login Admin
        </a>
      </nav>
    </div>
  </header>
</template>

<script setup>
// Header component
</script>
