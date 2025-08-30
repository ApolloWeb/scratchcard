import { createRouter, createWebHistory } from 'vue-router';
import Login from './views/Login.vue';
import Dashboard from './views/Dashboard.vue';
import PrizeTiers from './views/PrizeTiers.vue';
import GenerationBatches from './views/GenerationBatches.vue';
import PlaySessions from './views/PlaySessions.vue';

const routes = [
  { path: '/admin/login', name: 'login', component: Login },
  { path: '/admin', name: 'dashboard', component: Dashboard },
  { path: '/admin/prize-tiers', name: 'prize-tiers', component: PrizeTiers },
  { path: '/admin/generation-batches', name: 'generation-batches', component: GenerationBatches },
  { path: '/admin/play-sessions', name: 'play-sessions', component: PlaySessions },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
