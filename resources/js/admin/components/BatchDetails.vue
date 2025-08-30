<template>
  <div class="batch-details">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="h3 mb-0">Batch Details</h1>
        <p class="text-muted mb-0">{{ batch?.name || 'Unnamed Batch' }}</p>
      </div>
      <div class="d-flex gap-2">
        <a 
          v-if="batch?.status === 'COMPLETED'"
          :href="`/api/admin/batches/${id}/export`" 
          class="btn btn-success"
        >
          <i class="bi bi-download"></i> Download CSV
        </a>
        <router-link to="/tickets" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left"></i> Back to Tickets
        </router-link>
      </div>
    </div>

    <!-- Batch Overview -->
    <div class="row mb-4" v-if="batch">
      <div class="col-md-8">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="mb-0">Batch Information</h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-sm-6">
                <dl class="row">
                  <dt class="col-sm-5">Batch ID:</dt>
                  <dd class="col-sm-7"><code>{{ batch.id }}</code></dd>
                  
                  <dt class="col-sm-5">Status:</dt>
                  <dd class="col-sm-7">
                    <span class="badge" :class="getStatusClass(batch.status)">
                      {{ batch.status }}
                    </span>
                  </dd>
                  
                  <dt class="col-sm-5">Win Ratio:</dt>
                  <dd class="col-sm-7">{{ batch.win_numerator }}/{{ batch.win_denominator }}</dd>
                  
                  <dt class="col-sm-5">Code Length:</dt>
                  <dd class="col-sm-7">{{ batch.code_length }} characters</dd>
                </dl>
              </div>
              <div class="col-sm-6">
                <dl class="row">
                  <dt class="col-sm-5">Requested:</dt>
                  <dd class="col-sm-7">{{ batch.requested_count }}</dd>
                  
                  <dt class="col-sm-5">Created:</dt>
                  <dd class="col-sm-7">{{ batch.created_count }}</dd>
                  
                  <dt class="col-sm-5">Started:</dt>
                  <dd class="col-sm-7">{{ formatDate(batch.created_at) }}</dd>
                  
                  <dt class="col-sm-5">Updated:</dt>
                  <dd class="col-sm-7">{{ formatDate(batch.updated_at) }}</dd>
                </dl>
              </div>
            </div>
            
            <div v-if="batch.error" class="alert alert-danger mt-3">
              <strong>Error:</strong> {{ batch.error }}
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-4">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="mb-0">Progress</h5>
          </div>
          <div class="card-body">
            <div class="text-center mb-3">
              <div class="display-6 fw-bold" :class="getProgressTextClass(batch.status)">
                {{ getProgress(batch) }}%
              </div>
              <div class="text-muted">{{ batch.created_count }}/{{ batch.requested_count }}</div>
            </div>
            
            <div class="progress mb-3" style="height: 20px;">
              <div 
                class="progress-bar"
                :class="getProgressClass(batch.status)"
                :style="{ width: getProgress(batch) + '%' }"
              ></div>
            </div>

            <div v-if="batch.status === 'RUNNING'" class="text-center">
              <div class="spinner-border spinner-border-sm text-primary me-2"></div>
              <small class="text-muted">Generation in progress...</small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Prize Tiers Snapshot -->
    <div class="card mb-4" v-if="batch?.settings_snapshot?.prize_tiers">
      <div class="card-header">
        <h5 class="mb-0">Prize Tiers (Snapshot)</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Prize</th>
                <th>Amount</th>
                <th>Weight</th>
                <th>Probability</th>
                <th>Expected Count</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="tier in batch.settings_snapshot.prize_tiers" :key="tier.id">
                <td>{{ tier.label }}</td>
                <td>£{{ (tier.amount_minor / 100).toFixed(2) }}</td>
                <td>{{ tier.weight }}</td>
                <td>{{ getPrizeProb(tier) }}%</td>
                <td>{{ getExpectedPrizeCount(tier) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4" v-if="statistics">
      <div class="col-md-3">
        <div class="card text-bg-primary">
          <div class="card-body text-center">
            <h4>{{ statistics.total_tickets || 0 }}</h4>
            <p class="mb-0">Total Tickets</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-bg-success">
          <div class="card-body text-center">
            <h4>{{ statistics.winning_tickets || 0 }}</h4>
            <p class="mb-0">Winning Tickets</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-bg-danger">
          <div class="card-body text-center">
            <h4>{{ statistics.losing_tickets || 0 }}</h4>
            <p class="mb-0">Losing Tickets</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-bg-warning text-dark">
          <div class="card-body text-center">
            <h4>£{{ ((statistics.total_payout || 0) / 100).toFixed(2) }}</h4>
            <p class="mb-0">Total Payout</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Tickets -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Recent Tickets</h5>
        <router-link 
          :to="{ name: 'Tickets', query: { batch_id: id } }" 
          class="btn btn-outline-primary btn-sm"
        >
          View All Tickets
        </router-link>
      </div>
      <div class="card-body">
        <div v-if="loadingTickets" class="text-center py-4">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        
        <div v-else-if="recentTickets.length === 0" class="text-center py-4 text-muted">
          <p>No tickets found for this batch</p>
        </div>
        
        <div v-else class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
              <tr>
                <th>Code</th>
                <th>Status</th>
                <th>Outcome</th>
                <th>Prize</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="ticket in recentTickets" :key="ticket.id">
                <td><code>{{ ticket.code }}</code></td>
                <td>
                  <span class="badge" :class="getTicketStatusClass(ticket.status)">
                    {{ ticket.status }}
                  </span>
                </td>
                <td>
                  <span class="badge" :class="getOutcomeClass(ticket.outcome)">
                    {{ ticket.outcome }}
                  </span>
                </td>
                <td>
                  <div v-if="ticket.outcome === 'WIN' && ticket.prize_tier">
                    {{ ticket.prize_tier.label }}
                  </div>
                  <span v-else class="text-muted">-</span>
                </td>
                <td>{{ formatDate(ticket.created_at) }}</td>
                <td>
                  <a 
                    :href="`/t/${ticket.code}`" 
                    target="_blank"
                    class="btn btn-outline-primary btn-sm"
                  >
                    <i class="bi bi-box-arrow-up-right"></i>
                  </a>
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
import { ref, onMounted, onUnmounted, computed } from 'vue'
import api from '../services/api'

export default {
  name: 'BatchDetails',
  props: {
    id: {
      type: String,
      required: true
    }
  },
  setup(props) {
    const batch = ref(null)
    const statistics = ref(null)
    const recentTickets = ref([])
    const loadingTickets = ref(true)
    let pollingInterval = null

    const totalWeight = computed(() => {
      if (!batch.value?.settings_snapshot?.prize_tiers) return 0
      return batch.value.settings_snapshot.prize_tiers.reduce((sum, tier) => sum + tier.weight, 0)
    })

    const expectedWins = computed(() => {
      if (!batch.value) return 0
      return Math.round((batch.value.requested_count * batch.value.win_numerator) / batch.value.win_denominator)
    })

    const fetchBatchDetails = async () => {
      try {
        const response = await api.get(`/admin/batches/${props.id}`)
        batch.value = response.data
        
        // Start polling if batch is still running
        if (response.data.status === 'RUNNING' || response.data.status === 'PENDING') {
          startPolling()
        } else {
          stopPolling()
        }
      } catch (error) {
        console.error('Error fetching batch details:', error)
      }
    }

    const fetchStatistics = async () => {
      try {
        // This would be a custom endpoint for batch statistics
        // For now, we'll calculate from the recent tickets
        const response = await api.get(`/admin/batches/${props.id}/tickets?per_page=1000`)
        const tickets = response.data.data || []
        
        statistics.value = {
          total_tickets: tickets.length,
          winning_tickets: tickets.filter(t => t.outcome === 'WIN').length,
          losing_tickets: tickets.filter(t => t.outcome === 'LOSE').length,
          total_payout: tickets.reduce((sum, t) => sum + (t.payout_minor || 0), 0)
        }
      } catch (error) {
        console.error('Error fetching statistics:', error)
      }
    }

    const fetchRecentTickets = async () => {
      try {
        loadingTickets.value = true
        const response = await api.get(`/admin/batches/${props.id}/tickets?per_page=10`)
        recentTickets.value = response.data.data || []
      } catch (error) {
        console.error('Error fetching recent tickets:', error)
      } finally {
        loadingTickets.value = false
      }
    }

    const startPolling = () => {
      if (pollingInterval) return
      
      pollingInterval = setInterval(() => {
        fetchBatchDetails()
      }, 3000)
    }

    const stopPolling = () => {
      if (pollingInterval) {
        clearInterval(pollingInterval)
        pollingInterval = null
      }
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
        'PENDING': 'bg-warning',
        'RUNNING': 'bg-info progress-bar-striped progress-bar-animated',
        'COMPLETED': 'bg-success',
        'FAILED': 'bg-danger'
      }
      return classes[status] || 'bg-secondary'
    }

    const getProgressTextClass = (status) => {
      const classes = {
        'COMPLETED': 'text-success',
        'FAILED': 'text-danger',
        'RUNNING': 'text-info'
      }
      return classes[status] || 'text-secondary'
    }

    const getProgress = (batch) => {
      if (!batch || batch.requested_count === 0) return 0
      return Math.round((batch.created_count / batch.requested_count) * 100)
    }

    const getTicketStatusClass = (status) => {
      const classes = {
        'NEW': 'bg-secondary',
        'SCRATCHING': 'bg-warning text-dark',
        'REVEALED': 'bg-success',
        'EXPIRED': 'bg-danger'
      }
      return classes[status] || 'bg-secondary'
    }

    const getOutcomeClass = (outcome) => {
      return outcome === 'WIN' ? 'bg-success' : 'bg-danger'
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

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    onMounted(async () => {
      await fetchBatchDetails()
      await Promise.all([
        fetchStatistics(),
        fetchRecentTickets()
      ])
    })

    onUnmounted(() => {
      stopPolling()
    })

    return {
      batch,
      statistics,
      recentTickets,
      loadingTickets,
      getStatusClass,
      getProgressClass,
      getProgressTextClass,
      getProgress,
      getTicketStatusClass,
      getOutcomeClass,
      getPrizeProb,
      getExpectedPrizeCount,
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

.progress-bar-animated {
  animation: progress-bar-stripes 1s linear infinite;
}

dt {
  font-weight: 600;
}

code {
  font-size: 0.875rem;
}
</style>
