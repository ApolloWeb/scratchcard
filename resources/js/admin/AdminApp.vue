<template>
  <div id="admin-app">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container-fluid">
        <router-link class="navbar-brand" to="/">
          <i class="bi bi-gem"></i> Scratch Card Admin
        </router-link>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav me-auto">
            <li class="nav-item">
              <router-link class="nav-link" to="/" exact-active-class="active">
                <i class="bi bi-speedometer2"></i> Dashboard
              </router-link>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" to="/prize-tiers" active-class="active">
                <i class="bi bi-trophy"></i> Prize Tiers
              </router-link>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" to="/generate" active-class="active">
                <i class="bi bi-plus-circle"></i> Generate
              </router-link>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" to="/tickets" active-class="active">
                <i class="bi bi-ticket-perforated"></i> Tickets
              </router-link>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <main class="container-fluid py-4">
      <router-view />
    </main>
  </div>
</template>

<script>
export default {
  name: 'AdminApp'
}
</script>

<style scoped>
.navbar-brand {
  font-weight: bold;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

main {
  min-height: calc(100vh - 76px);
  background-color: #f8f9fa;
}
</style>
