import './bootstrap';

import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import AdminApp from './pages/AdminApp.vue';
import AdminDashboard from './pages/AdminDashboard.vue';
import UsersList from './pages/UsersList.vue';
import UserForm from './pages/UserForm.vue';
import PrizeTiersList from './pages/PrizeTiersList.vue';
import PrizeTierForm from './pages/PrizeTierForm.vue';
import BatchesList from './pages/BatchesList.vue';
import BatchForm from './pages/BatchForm.vue';
import PlaySessionsList from './pages/PlaySessionsList.vue';
import PlaySessionDetail from './pages/PlaySessionDetail.vue';
import GameSettingsList from './pages/GameSettingsList.vue';
import GameSettingForm from './pages/GameSettingForm.vue';

const routes = [
    { path: '/admin', component: AdminDashboard },
    { path: '/admin/users', component: UsersList },
    { path: '/admin/users/create', component: UserForm },
    { path: '/admin/users/:id/edit', component: UserForm, props: true },

    { path: '/admin/prize-tiers', component: PrizeTiersList },
    { path: '/admin/prize-tiers/create', component: PrizeTierForm },
    { path: '/admin/prize-tiers/:id/edit', component: PrizeTierForm, props: true },

    { path: '/admin/batches', component: BatchesList },
    { path: '/admin/batches/create', component: BatchForm },
    { path: '/admin/batches/:id/edit', component: BatchForm, props: true },

    { path: '/admin/play-sessions', component: PlaySessionsList },
    { path: '/admin/play-sessions/:id', component: PlaySessionDetail, props: true },

    { path: '/admin/game-settings', component: GameSettingsList },
    { path: '/admin/game-settings/create', component: GameSettingForm },
    { path: '/admin/game-settings/:id/edit', component: GameSettingForm, props: true },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

const app = createApp(AdminApp);
app.use(router);
app.mount('#admin-app');
