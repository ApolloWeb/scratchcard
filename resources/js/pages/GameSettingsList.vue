<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Game Settings</h2>
      <router-link to="/admin/game-settings/create" class="btn btn-primary">Create</router-link>
    </div>
    <table class="table"><thead><tr><th>Win Rate</th><th></th></tr></thead>
      <tbody>
        <tr v-for="g in settings.data" :key="g.id">
          <td>{{ g.win_numerator }}/{{ g.win_denominator }}</td>
          <td><router-link :to="`/admin/game-settings/${g.id}/edit`" class="btn btn-sm btn-outline-secondary">Edit</router-link></td>
        </tr>
      </tbody></table>
  </div>
</template>

<script>
import axios from 'axios';
export default { data(){ return { settings:{ data: [] } } }, mounted(){ this.fetch() }, methods:{ async fetch(){ const res = await axios.get('/admin/game-settings'); this.settings = res.data } } };
</script>
