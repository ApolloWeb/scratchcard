<template>
  <div>
    <h2>Play Session</h2>
    <div v-if="session">
      <p><strong>Code:</strong> {{ session.code }}</p>
      <p><strong>Batch:</strong> {{ session.batch_id }}</p>
      <p><strong>Status:</strong> {{ session.status }}</p>
      <p><strong>Outcome:</strong> {{ session.outcome }}</p>
      <p><strong>Payout:</strong> {{ session.payout_minor }}</p>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
export default { props:['id'], data(){ return { session:null } }, async mounted(){ const res = await axios.get(`/admin/play-sessions/${this.id}`); this.session = res.data } };
</script>
