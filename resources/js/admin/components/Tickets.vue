<template>
  <div class="tickets">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3 mb-0">Tickets</h1>
      <router-link to="/generate" class="btn btn-primary">
        <i class="bi bi-plus"></i> Generate New Batch
      </router-link>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row">
          <div class="col-md-3">
            <label for="batch_select" class="form-label">Batch</label>
            <select class="form-select" id="batch_select" v-model="filters.batch_id" @change="loadTickets">
              <option value="">All Batches</option>
              <option v-for="batch in batches" :key="batch.id" :value="batch.id">
                {{ batch.name || `Batch ${batch.id.slice(-8)}` }}
              </option>
            </select>
          </div>
          <div class="col-md-2">
            <label for="status_filter" class="form-label">Status</label>
            <select class="form-select" id="status_filter" v-model="filters.status" @change="loadTickets">
              <option value="">All Status</option>
              <option value="NEW">New</option>
              <option value="SCRATCHING">Scratching</option>
              <option value="REVEALED">Revealed</option>
              <option value="EXPIRED">Expired</option>
            </select>
          </div>
          <div class="col-md-2">
            <label for="outcome_filter" class="form-label">Outcome</label>
            <select class="form-select" id="outcome_filter" v-model="filters.outcome" @change="loadTickets">
              <option value="">All Outcomes</option>
              <option value="WIN">Win</option>
              <option value="LOSE">Lose</option>
            </select>
          </div>
          <div class="col-md-3">
            <label for="search" class="form-label">Search Code</label>
            <input 
              type="text" 
              class="form-control" 
              id="search"
              v-model="filters.search"
              placeholder="Enter ticket code"
              @input="debounceSearch"
            >
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-outline-secondary w-100" @click="clearFilters">
              <i class="bi bi-arrow-clockwise"></i> Reset
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Export Actions -->
    <div v-if="filters.batch_id" class="card mb-4">
      <div class="card-body">
        <div class="d-flex gap-2">
          <a 
            :href="`/api/admin/batches/${filters.batch_id}/export`" 
            class="btn btn-success"
          >
            <i class="bi bi-download"></i> Download Full Batch CSV
          </a>
          <router-link 
            :to="`/batches/${filters.batch_id}`" 
            class="btn btn-outline-primary"
          >
            <i class="bi bi-eye"></i> View Batch Details
          </router-link>
        </div>
      </div>
    </div>

    <!-- Tickets Table -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          Tickets 
          <span v-if="pagination.total" class="text-muted">
            ({{ pagination.total }} total)
          </span>
        </h5>
        <div class="form-text">
          Showing {{ pagination.from || 0 }} to {{ pagination.to || 0 }} of {{ pagination.total || 0 }}
        </div>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center py-4">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        
        <div v-else-if="tickets.length === 0" class="text-center py-4 text-muted">
          <i class="bi bi-ticket-perforated fs-1 mb-3"></i>
          <p>No tickets found</p>
          <router-link to="/generate" class="btn btn-primary">
            Generate Your First Batch
          </router-link>
        </div>
        
        <div v-else>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead class="table-light">
                <tr>
                  <th>Code</th>
                  <th>Status</th>
                  <th>Outcome</th>
                  <th>Prize</th>
                  <th>Progress</th>
                  <th>Created</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="ticket in tickets" :key="ticket.id">
                  <td>
                    <code class="fw-bold">{{ ticket.code }}</code>
                    <br>
                    <small class="text-muted">{{ ticket.id.slice(-8) }}</small>
                  </td>
                  <td>
                    <span class="badge" :class="getStatusClass(ticket.status)">
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
                      <br>
                      <small class="text-success">£{{ (ticket.payout_minor / 100).toFixed(2) }}</small>
                    </div>
                    <span v-else class="text-muted">-</span>
                  </td>
                  <td>
                    <div class="progress" style="width: 60px; height: 20px;">
                      <div 
                        class="progress-bar bg-info" 
                        :style="{ width: ticket.scratch_pct + '%' }"
                      ></div>
                    </div>
                    <small class="text-muted">{{ ticket.scratch_pct }}%</small>
                  </td>
                  <td>{{ formatDate(ticket.created_at) }}</td>
                  <td>
                    <a 
                      :href="`/t/${ticket.code}`" 
                      target="_blank"
                      class="btn btn-outline-primary btn-sm"
                      title="Open Ticket"
                    >
                      <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <nav v-if="pagination.last_page > 1" class="mt-4">
            <ul class="pagination justify-content-center">
              <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                <button class="page-link" @click="changePage(pagination.current_page - 1)">
                  Previous
                </button>
              </li>
              
              <li 
                v-for="page in getVisiblePages()" 
                :key="page"
                class="page-item" 
                :class="{ active: page === pagination.current_page }"
              >
                <button class="page-link" @click="changePage(page)">
                  {{ page }}
                </button>
              </li>
              
              <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                <button class="page-link" @click="changePage(pagination.current_page + 1)">
                  Next
                </button>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import api from '../services/api'

export default {
  name: 'Tickets',
  setup() {
    const loading = ref(true)
    const tickets = ref([])
    const batches = ref([])
    const pagination = ref({})
    
    const filters = ref({
      batch_id: '',
      status: '',
      outcome: '',
      search: ''
    })

    let searchTimeout = null

    const loadBatches = async () => {
      try {
        const response = await api.get('/admin/batches?per_page=100')
        batches.value = response.data.data || []
      } catch (error) {
        console.error('Error loading batches:', error)
      }
    }

    const loadTickets = async (page = 1) => {
      try {
        loading.value = true
        
        const params = new URLSearchParams({
          page: page.toString(),
          per_page: '20'
        })

        // Add filters if they have values
        Object.entries(filters.value).forEach(([key, value]) => {
          if (value) {
            if (key === 'batch_id') {
              // For batch_id, we need to construct the URL differently
              // This will be handled by the backend route
            } else {
              params.append(key, value)
            }
          }
        })

        let url = '/admin/batches'
        if (filters.value.batch_id) {
          url = `/admin/batches/${filters.value.batch_id}/tickets`
        } else {
          // If no specific batch, we need an endpoint for all tickets
          // For now, we'll load from the first batch or show empty
          if (batches.value.length > 0) {
            url = `/admin/batches/${batches.value[0].id}/tickets`
          }
        }

        const response = await api.get(`${url}?${params.toString()}`)
        tickets.value = response.data.data || []
        pagination.value = {
          current_page: response.data.current_page || 1,
          last_page: response.data.last_page || 1,
          total: response.data.total || 0,
          from: response.data.from || 0,
          to: response.data.to || 0
        }
      } catch (error) {
        console.error('Error loading tickets:', error)
        tickets.value = []
      } finally {
        loading.value = false
      }
    }

    const changePage = (page) => {
      if (page >= 1 && page <= pagination.value.last_page) {
        loadTickets(page)
      }
    }

    const debounceSearch = () => {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => {
        loadTickets(1)
      }, 500)
    }

    const clearFilters = () => {
      filters.value = {
        batch_id: '',
        status: '',
        outcome: '',
        search: ''
      }
      loadTickets(1)
    }

    const getStatusClass = (status) => {
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

    const getVisiblePages = () => {
      const current = pagination.value.current_page
      const last = pagination.value.last_page
      const pages = []
      
      const start = Math.max(1, current - 2)
      const end = Math.min(last, current + 2)
      
      for (let i = start; i <= end; i++) {
        pages.push(i)
      }
      
      return pages
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
      await loadBatches()
      if (batches.value.length > 0) {
        filters.value.batch_id = batches.value[0].id
      }
      await loadTickets()
    })

    return {
      loading,
      tickets,
      batches,
      pagination,
      filters,
      loadTickets,
      changePage,
      debounceSearch,
      clearFilters,
      getStatusClass,
      getOutcomeClass,
      getVisiblePages,
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

.progress {
  background-color: #e9ecef;
}

code {
  font-size: 0.875rem;
}
</style>
