import './bootstrap';
import { createApp } from 'vue';
import router from './admin/router';
import AdminApp from './admin/AdminApp.vue';

// Create and mount the admin app
const app = createApp(AdminApp);
app.use(router);
app.mount('#admin-app');
