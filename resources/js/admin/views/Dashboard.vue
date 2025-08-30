<template>
  <div>
    <h3>Dashboard</h3>
    <p>Quick stats</p>
    <div class="row">
      <div class="col-md-4">
        <div class="card mb-3">
          <div class="card-body">
            <h5 class="card-title">Prize Tiers</h5>
            <p class="card-text">{{ stats.prizeTiers }}</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card mb-3">
          <div class="card-body">
            <h5 class="card-title">Generation Batches</h5>
            <p class="card-text">{{ stats.batches }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
const stats = ref({ prizeTiers: '-', batches: '-' });

onMounted(async () => {
  try {
    const [tiers, batches] = await Promise.all([
      window.axios.get('/admin/prize-tiers'),
      window.axios.get('/admin/generation-batches'),
    ]);
    stats.value.prizeTiers = tiers.data.length;
    stats.value.batches = batches.data.length;
  } catch (e) {
    // ignore
  }
});
</script>
