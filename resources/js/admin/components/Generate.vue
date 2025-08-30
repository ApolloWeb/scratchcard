<template>
  <div class="generate">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3 mb-0">Generate Tickets</h1>
      <router-link to="/tickets" class="btn btn-outline-secondary">
        <i class="bi bi-ticket-perforated"></i> View All Tickets
      </router-link>
    </div>

    <!-- Generation Form -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Create New Batch</h5>
      </div>
      <div class="card-body">
        <form @submit.prevent="generateBatch">
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="name" class="form-label">Batch Name</label>
              <input 
                type="text" 
                class="form-control" 
                id="name"
                v-model="form.name"
                placeholder="Optional batch name"
              >
            </div>
            <div class="col-md-3">
              <label for="count" class="form-label">Number of Tickets</label>
              <input 
                type="number" 
                class="form-control" 
                id="count"
                v-model.number="form.count"
                min="1"
                max="50000"
                required
              >
            </div>
            <div class="col-md-3">
              <label for="code_length" class="form-label">Code Length</label>
              <select class="form-select" id="code_length" v-model.number="form.code_length">
                <option value="8">8 characters</option>
                <option value="9">9 characters</option>
                <option value="10">10 characters</option>
              </select>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Win Ratio</label>
              <div class="input-group">
                <input 
                  type="number" 
                  class="form-control" 
                  v-model.number="form.win_numerator"
                  min="1"
                  placeholder="Numerator"
                  required
                >
                <span class="input-group-text">in</span>
                <input 
                  type="number" 
                  class="form-control" 
                  v-model.number="form.win_denominator"
                  min="1"
                  max="10000"
                  placeholder="Denominator"
                  required
                >
              </div>
              <div class="form-text">
                Win probability: {{ winProbability }}%
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Expected Outcomes</label>
              <div class="bg-light p-3 rounded">
                <div class="row">
                  <div class="col-6">
                    <strong class="text-success">Wins:</strong> {{ expectedWins }}
                  </div>
                  <div class="col-6">
                    <strong class="text-danger">Losses:</strong> {{ expectedLosses }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Prize Tiers Preview -->
          <div class="mb-3" v-if="prizeTiers.length > 0">
            <label class="form-label">Prize Distribution (for wins)</label>
            <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Prize</th>
                    <th>Weight</th>
                    <th>Probability</th>
                    <th>Expected Count</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="tier in prizeTiers" :key="tier.id">
                    <td>{{ tier.label }}</td>
                    <td>{{ tier.weight }}</td>
                    <td>{{ getPrizeProb(tier) }}%</td>
                    <td>{{ getExpectedPrizeCount(tier) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div v-if="prizeTiers.length === 0" class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>No prize tiers configured!</strong> 
            You need to create at least one prize tier before generating tickets.
            <router-link to="/prize-tiers" class="alert-link ms-2">Configure Prize Tiers</router-link>
          </div>

          <button 
            type="submit" 
            class="btn btn-primary btn-lg"
            :disabled="generating || prizeTiers.length === 0"
          >
            <span v-if="generating" class="spinner-border spinner-border-sm me-2"></span>
            <i v-else class="bi bi-play-fill me-2"></i>
            Generate {{ form.count }} Tickets
          </button>
        </form>
      </div>
    </div>

    <!-- Generation Progress -->
    <div v-if="currentBatch" class="card">
      <div class="card-header">
        <h5 class="mb-0">Generation Progress</h5>
      </div>
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span>{{ currentBatch.name || 'Unnamed Batch' }}</span>
          <span class="badge" :class="getStatusClass(currentBatch.status)">
            {{ currentBatch.status }}
          </span>
        </div>
        
        <div class="progress mb-3" style="height: 25px;">
          <div 
            class="progress-bar progress-bar-striped"
            :class="getProgressClass(currentBatch.status)"
            :style="{ width: getProgress(currentBatch) + '%' }"
          >
            {{ currentBatch.created_count }}/{{ currentBatch.requested_count }}
          </div>
        </div>

        <div v-if="currentBatch.status === 'FAILED'" class="alert alert-danger">
          <strong>Generation Failed:</strong> {{ currentBatch.error }}
        </div>

        <div v-if="currentBatch.status === 'COMPLETED'" class="d-flex gap-2">
          <router-link 
            :to="`/batches/${currentBatch.id}`" 
            class="btn btn-outline-primary"
          >
            <i class="bi bi-eye"></i> View Details
          </router-link>
          <a 
            :href="`/api/admin/batches/${currentBatch.id}/export`" 
            class="btn btn-outline-success"
          >
            <i class="bi bi-download"></i> Download CSV
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import api from '../services/api'

export default {
  name: 'Generate',
  setup() {
    const generating = ref(false)
    const prizeTiers = ref([])
    const currentBatch = ref(null)
    let progressInterval = null

    const form = ref({
      name: '',
      count: 1000,
      win_numerator: 1,
      win_denominator: 10,
      code_length: 8
    })

    const winProbability = computed(() => {
      if (form.value.win_denominator === 0) return 0
      return ((form.value.win_numerator / form.value.win_denominator) * 100).toFixed(2)
    })

    const expectedWins = computed(() => {
      if (!form.value.count || form.value.win_denominator === 0) return 0
      return Math.round((form.value.count * form.value.win_numerator) / form.value.win_denominator)
    })

    const expectedLosses = computed(() => {
      return form.value.count - expectedWins.value
    })

    const totalWeight = computed(() => {
      return prizeTiers.value.reduce((sum, tier) => sum + tier.weight, 0)
    })

    const fetchPrizeTiers = async () => {
      try {
        const response = await api.get('/admin/prize-tiers')
        prizeTiers.value = response.data
      } catch (error) {
        console.error('Error fetching prize tiers:', error)
      }
    }

    const generateBatch = async () => {
      try {
        generating.value = true
        const response = await api.post('/admin/generate', form.value)
        currentBatch.value = response.data
        
        // Start polling for progress
        startProgressPolling(currentBatch.value.id)
        
      } catch (error) {
        console.error('Error generating batch:', error)
        alert('Error generating batch')
      } finally {
        generating.value = false
      }
    }

    const startProgressPolling = (batchId) => {
      progressInterval = setInterval(async () => {
        try {
          const response = await api.get(`/admin/batches/${batchId}`)
          currentBatch.value = response.data
          
          if (response.data.status === 'COMPLETED' || response.data.status === 'FAILED') {
            clearInterval(progressInterval)
            progressInterval = null
          }
        } catch (error) {
          console.error('Error polling batch status:', error)
          clearInterval(progressInterval)
          progressInterval = null
        }
      }, 2000)
    }

    const getStatusClass = (status) => {
      const classes = {
        'PENDING': 'bg-warning text-dark',
        'RUNNING': 'bg-info',
        'COMPLETED': 'bg-success',
        'FAILED': 'bg-danger'
      }
      return classes[status] || 'bg-secondary'
    }

    const getProgressClass = (status) => {
      if (status === 'RUNNING') return 'progress-bar-animated'
      return ''
    }

    const getProgress = (batch) => {
      if (batch.requested_count === 0) return 0
      return Math.round((batch.created_count / batch.requested_count) * 100)
    }

    const getPrizeProb = (tier) => {
      if (totalWeight.value === 0) return 0
      return ((tier.weight / totalWeight.value) * 100).toFixed(1)
    }

    const getExpectedPrizeCount = (tier) => {
      if (totalWeight.value === 0) return 0
      const prizeProb = tier.weight / totalWeight.value
      return Math.round(expectedWins.value * prizeProb)
    }

    onMounted(() => {
      fetchPrizeTiers()
    })

    onUnmounted(() => {
      if (progressInterval) {
        clearInterval(progressInterval)
      }
    })

    return {
      generating,
      prizeTiers,
      currentBatch,
      form,
      winProbability,
      expectedWins,
      expectedLosses,
      generateBatch,
      getStatusClass,
      getProgressClass,
      getProgress,
      getPrizeProb,
      getExpectedPrizeCount
    }
  }
}
</script>

<style scoped>
.card {
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  border: 1px solid rgba(0, 0, 0, 0.125);
}

.progress-bar-animated {
  animation: progress-bar-stripes 1s linear infinite;
}

@keyframes progress-bar-stripes {
  0% {
    background-position: 1rem 0;
  }
  100% {
    background-position: 0 0;
  }
}
</style>
