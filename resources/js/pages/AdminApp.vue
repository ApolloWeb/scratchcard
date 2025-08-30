<template>
  <div>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
      <div class="container-fluid">
        <router-link to="/admin" class="navbar-brand">Admin</router-link>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"> 
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav me-auto">
            <li class="nav-item"><router-link to="/admin" class="nav-link">Dashboard</router-link></li>
            <li class="nav-item"><router-link to="/admin/users" class="nav-link">Users</router-link></li>
            <li class="nav-item"><router-link to="/admin/prize-tiers" class="nav-link">Prize Tiers</router-link></li>
            <li class="nav-item"><router-link to="/admin/batches" class="nav-link">Batches</router-link></li>
            <li class="nav-item"><router-link to="/admin/play-sessions" class="nav-link">Play Sessions</router-link></li>
            <li class="nav-item"><router-link to="/admin/game-settings" class="nav-link">Game Settings</router-link></li>
          </ul>
          <div class="d-flex">
            <button class="btn btn-outline-secondary" @click="logout">Logout</button>
          </div>
        </div>
      </div>
    </nav>
    <div class="container mt-4">
      <router-view />
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'AdminApp',
  methods: {
    async logout() {
      try {
        await axios.post('/admin/logout');
        window.location.href = '/admin/login';
      } catch (e) {
        console.error(e);
      }
    }
  }
};
</script>
