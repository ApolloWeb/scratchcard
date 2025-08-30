<template>
  <div>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
      <div class="container">
        <a class="navbar-brand" href="/admin">Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="nav">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item"><router-link class="nav-link" to="/">Dashboard</router-link></li>
            <li class="nav-item"><router-link class="nav-link" to="/prize-tiers">Prize Tiers</router-link></li>
            <li class="nav-item"><router-link class="nav-link" to="/generation-batches">Generation Batches</router-link></li>
            <li class="nav-item"><router-link class="nav-link" to="/play-sessions">Play Sessions</router-link></li>
          </ul>

          <div class="d-flex">
            <button class="btn btn-outline-secondary" @click="logout">Logout</button>
          </div>
        </div>
      </div>
    </nav>

    <div class="container py-4">
      <router-view />
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';

const router = useRouter();

function logout() {
  if (confirm('Logout?')) {
    window.axios.post('/admin/logout').then(() => {
      window.location.href = '/admin/login';
    }).catch(() => {
      window.location.href = '/admin/login';
    });
  }
}
</script>
