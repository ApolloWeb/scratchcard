<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Prize Tiers</h2>
      <router-link to="/admin/prize-tiers/create" class="btn btn-primary">Create</router-link>
    </div>
    <table class="table">
      <thead><tr><th>Label</th><th>Amount</th><th>Weight</th><th></th></tr></thead>
      <tbody>
        <tr v-for="p in prizes.data" :key="p.id">
          <td>{{ p.label }}</td>
          <td>{{ p.amount_minor }}</td>
          <td>{{ p.weight }}</td>
          <td><router-link :to="`/admin/prize-tiers/${p.id}/edit`" class="btn btn-sm btn-outline-secondary">Edit</router-link></td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import axios from 'axios';
export default { data(){ return { prizes: { data: [] } } }, mounted(){ this.fetch() }, methods:{ async fetch(){ const res = await axios.get('/admin/prize-tiers'); this.prizes = res.data } } };
</script>
