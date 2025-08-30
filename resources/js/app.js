import './bootstrap';
import { createApp } from 'vue';
import router from './admin/router';
import AdminApp from './admin/AdminApp.vue';

const app = createApp(AdminApp);
app.use(router);
app.mount('#admin-app');
