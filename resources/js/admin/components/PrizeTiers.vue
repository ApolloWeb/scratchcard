<template>
  <div class="prize-tiers">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3 mb-0">Prize Tiers</h1>
      <button class="btn btn-primary" @click="showCreateForm = !showCreateForm">
        <i class="bi bi-plus"></i> Add Prize Tier
      </button>
    </div>

    <!-- Create Form -->
    <div v-if="showCreateForm" class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Create New Prize Tier</h5>
      </div>
      <div class="card-body">
        <form @submit.prevent="createPrizeTier">
          <div class="row">
            <div class="col-md-4">
              <label for="label" class="form-label">Label</label>
              <input 
                type="text" 
                class="form-control" 
                id="label"
                v-model="newTier.label"
                placeholder="e.g., £1"
                required
              >
            </div>
            <div class="col-md-4">
              <label for="amount_minor" class="form-label">Amount (pence)</label>
              <input 
                type="number" 
                class="form-control" 
                id="amount_minor"
                v-model.number="newTier.amount_minor"
                min="1"
                placeholder="100"
                required
              >
            </div>
            <div class="col-md-4">
              <label for="weight" class="form-label">Weight</label>
              <input 
                type="number" 
                class="form-control" 
                id="weight"
                v-model.number="newTier.weight"
                min="1"
                placeholder="10"
                required
              >
            </div>
          </div>
          <div class="mt-3">
            <button type="submit" class="btn btn-success" :disabled="creating">
              <span v-if="creating" class="spinner-border spinner-border-sm me-2"></span>
              Create Prize Tier
            </button>
            <button type="button" class="btn btn-secondary ms-2" @click="cancelCreate">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Prize Tiers List -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Current Prize Tiers</h5>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center py-4">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        
        <div v-else-if="prizeTiers.length === 0" class="text-center py-4 text-muted">
          <i class="bi bi-trophy fs-1 mb-3"></i>
          <p>No prize tiers configured yet</p>
          <button class="btn btn-primary" @click="showCreateForm = true">
            Create Your First Prize Tier
          </button>
        </div>
        
        <div v-else class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
              <tr>
                <th>Label</th>
                <th>Amount</th>
                <th>Weight</th>
                <th>Probability</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="tier in prizeTiers" :key="tier.id">
                <td>
                  <strong>{{ tier.label }}</strong>
                </td>
                <td>£{{ (tier.amount_minor / 100).toFixed(2) }}</td>
                <td>{{ tier.weight }}</td>
                <td>{{ getProbabilityPercentage(tier) }}%</td>
                <td>
                  <button 
                    class="btn btn-outline-danger btn-sm"
                    @click="deletePrizeTier(tier)"
                    :disabled="deleting === tier.id"
                  >
                    <span v-if="deleting === tier.id" class="spinner-border spinner-border-sm me-1"></span>
                    <i v-else class="bi bi-trash"></i>
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
          
          <div class="mt-3">
            <div class="alert alert-info">
              <i class="bi bi-info-circle me-2"></i>
              <strong>Total Weight:</strong> {{ totalWeight }} 
              | Probabilities are calculated relative to other winning tiers
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import api from '../services/api'

export default {
  name: 'PrizeTiers',
  setup() {
    const loading = ref(true)
    const creating = ref(false)
    const deleting = ref(null)
    const showCreateForm = ref(false)
    const prizeTiers = ref([])
    
    const newTier = ref({
      label: '',
      amount_minor: null,
      weight: null
    })

    const totalWeight = computed(() => {
      return prizeTiers.value.reduce((sum, tier) => sum + tier.weight, 0)
    })

    const fetchPrizeTiers = async () => {
      try {
        loading.value = true
        const response = await api.get('/admin/prize-tiers')
        prizeTiers.value = response.data
      } catch (error) {
        console.error('Error fetching prize tiers:', error)
        alert('Error loading prize tiers')
      } finally {
        loading.value = false
      }
    }

    const createPrizeTier = async () => {
      try {
        creating.value = true
        await api.post('/admin/prize-tiers', newTier.value)
        
        // Reset form and refresh list
        newTier.value = { label: '', amount_minor: null, weight: null }
        showCreateForm.value = false
        await fetchPrizeTiers()
        
        alert('Prize tier created successfully!')
      } catch (error) {
        console.error('Error creating prize tier:', error)
        alert('Error creating prize tier')
      } finally {
        creating.value = false
      }
    }

    const deletePrizeTier = async (tier) => {
      if (!confirm(`Are you sure you want to delete "${tier.label}"?`)) {
        return
      }

      try {
        deleting.value = tier.id
        await api.delete(`/admin/prize-tiers/${tier.id}`)
        await fetchPrizeTiers()
        alert('Prize tier deleted successfully!')
      } catch (error) {
        console.error('Error deleting prize tier:', error)
        alert('Error deleting prize tier')
      } finally {
        deleting.value = null
      }
    }

    const cancelCreate = () => {
      newTier.value = { label: '', amount_minor: null, weight: null }
      showCreateForm.value = false
    }

    const getProbabilityPercentage = (tier) => {
      if (totalWeight.value === 0) return 0
      return ((tier.weight / totalWeight.value) * 100).toFixed(1)
    }

    onMounted(() => {
      fetchPrizeTiers()
    })

    return {
      loading,
      creating,
      deleting,
      showCreateForm,
      prizeTiers,
      newTier,
      totalWeight,
      createPrizeTier,
      deletePrizeTier,
      cancelCreate,
      getProbabilityPercentage
    }
  }
}
</script>

<style scoped>
.card {
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  border: 1px solid rgba(0, 0, 0, 0.125);
}

.alert {
  border: none;
  border-radius: 0.375rem;
}
</style>
