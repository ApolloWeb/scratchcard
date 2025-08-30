<template>
  <div>
    <h2 v-if="!id">Create Batch</h2>
    <h2 v-else>Edit Batch</h2>
    <form @submit.prevent="save">
      <div class="mb-3"><label class="form-label">Name</label><input v-model="form.name" class="form-control"/></div>
      <div class="mb-3"><label class="form-label">Count</label><input v-model.number="form.count" class="form-control"/></div>
      <div class="mb-3"><label class="form-label">Decide At</label><select v-model="form.decide_at" class="form-select"><option value="generation">generation</option><option value="reveal">reveal</option></select></div>
      <button class="btn btn-primary">Save</button>
      <router-link to="/admin/batches" class="btn btn-link">Cancel</router-link>
    </form>
  </div>
</template>

<script>
import axios from 'axios';
export default { props:['id'], data(){ return { form:{ name:'', count:10, decide_at:'generation' } } }, async mounted(){ if(this.id){ const res=await axios.get(`/admin/batches/${this.id}`); this.form=res.data } }, methods:{ async save(){ if(this.id) await axios.put(`/admin/batches/${this.id}`, this.form); else await axios.post('/admin/batches', this.form); this.$router.push('/admin/batches') } } };
</script>
