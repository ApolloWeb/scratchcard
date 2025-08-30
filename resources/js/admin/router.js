import { createRouter, createWebHistory } from 'vue-router'
import Dashboard from './components/Dashboard.vue'
import PrizeTiers from './components/PrizeTiers.vue'
import Generate from './components/Generate.vue'
import Tickets from './components/Tickets.vue'
import BatchDetails from './components/BatchDetails.vue'

const routes = [
  {
    path: '/',
    name: 'Dashboard',
    component: Dashboard
  },
  {
    path: '/prize-tiers',
    name: 'PrizeTiers',
    component: PrizeTiers
  },
  {
    path: '/generate',
    name: 'Generate',
    component: Generate
  },
  {
    path: '/tickets',
    name: 'Tickets',
    component: Tickets
  },
  {
    path: '/batches/:id',
    name: 'BatchDetails',
    component: BatchDetails,
    props: true
  }
]

const router = createRouter({
  history: createWebHistory('/admin'),
  routes
})

export default router
