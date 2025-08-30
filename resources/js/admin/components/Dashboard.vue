<template>
  <div class="dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3 mb-0">Dashboard</h1>
      <button class="btn btn-outline-secondary" @click="refreshData">
        <i class="bi bi-arrow-clockwise"></i> Refresh
      </button>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4" v-if="summary">
      <div class="col-md-3">
        <div class="card text-bg-primary">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h4 class="card-title">{{ summary.batches || 0 }}</h4>
                <p class="card-text">Total Batches</p>
              </div>
              <i class="bi bi-collection fs-1 opacity-50"></i>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-3">
        <div class="card text-bg-info">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h4 class="card-title">{{ summary.tickets || 0 }}</h4>
                <p class="card-text">Total Tickets</p>
              </div>
              <i class="bi bi-ticket-perforated fs-1 opacity-50"></i>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-3">
        <div class="card text-bg-success">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h4 class="card-title">{{ summary.wins || 0 }}</h4>
                <p class="card-text">Winning Tickets</p>
              </div>
              <i class="bi bi-trophy fs-1 opacity-50"></i>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-3">
        <div class="card text-bg-secondary">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h4 class="card-title">{{ summary.loses || 0 }}</h4>
                <p class="card-text">Losing Tickets</p>
              </div>
              <i class="bi bi-x-circle fs-1 opacity-50"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Batches -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Recent Batches</h5>
        <router-link to="/generate" class="btn btn-primary btn-sm">
          <i class="bi bi-plus"></i> Generate New
        </router-link>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center py-4">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        
        <div v-else-if="recentBatches.length === 0" class="text-center py-4 text-muted">
          <i class="bi bi-inbox fs-1 mb-3"></i>
          <p>No batches created yet</p>
          <router-link to="/generate" class="btn btn-primary">
            Create Your First Batch
          </router-link>
        </div>
        
        <div v-else class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
              <tr>
                <th>Name</th>
                <th>Status</th>
                <th>Win Ratio</th>
                <th>Progress</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="batch in recentBatches" :key="batch.id">
                <td>
                  <strong>{{ batch.name || 'Unnamed Batch' }}</strong>
                  <br>
                  <small class="text-muted">{{ batch.id }}</small>
                </td>
                <td>
                  <span class="badge" :class="getStatusClass(batch.status)">
                    {{ batch.status }}
                  </span>
                </td>
                <td>{{ batch.win_numerator }}/{{ batch.win_denominator }}</td>
                <td>
                  <div class="progress" style="height: 20px;">
                    <div 
                      class="progress-bar" 
                      :class="getProgressClass(batch.status)"
                      :style="{ width: getProgress(batch) + '%' }"
                    >
                      {{ batch.created_count }}/{{ batch.requested_count }}
                    </div>
                  </div>
                </td>
                <td>{{ formatDate(batch.created_at) }}</td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <router-link 
                      :to="`/batches/${batch.id}`" 
                      class="btn btn-outline-primary"
                      title="View Details"
                    >
                      <i class="bi bi-eye"></i>
                    </router-link>
                    <a 
                      v-if="batch.status === 'COMPLETED'"
                      :href="`/api/admin/batches/${batch.id}/export`" 
                      class="btn btn-outline-success"
                      title="Download CSV"
                    >
                      <i class="bi bi-download"></i>
                    </a>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import api from '../services/api'

export default {
  name: 'Dashboard',
  setup() {
    const loading = ref(true)
    const summary = ref(null)
    const recentBatches = ref([])

    const fetchData = async () => {
      try {
        loading.value = true
        const [summaryResponse, batchesResponse] = await Promise.all([
          api.get('/admin/summary'),
          api.get('/admin/batches?per_page=10')
        ])
        
        summary.value = summaryResponse.data
        recentBatches.value = batchesResponse.data.data || []
      } catch (error) {
        console.error('Error fetching dashboard data:', error)
      } finally {
        loading.value = false
      }
    }

    const refreshData = () => {
      fetchData()
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
      const classes = {
        'RUNNING': 'bg-info',
        'COMPLETED': 'bg-success',
        'FAILED': 'bg-danger'
      }
      return classes[status] || ''
    }

    const getProgress = (batch) => {
      if (batch.requested_count === 0) return 0
      return Math.round((batch.created_count / batch.requested_count) * 100)
    }

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    onMounted(() => {
      fetchData()
    })

    return {
      loading,
      summary,
      recentBatches,
      refreshData,
      getStatusClass,
      getProgressClass,
      getProgress,
      formatDate
    }
  }
}
</script>

<style scoped>
.card {
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  border: 1px solid rgba(0, 0, 0, 0.125);
}

.card.text-bg-primary,
.card.text-bg-info,
.card.text-bg-success,
.card.text-bg-secondary {
  border: none;
}

.progress {
  background-color: #e9ecef;
}
</style>
