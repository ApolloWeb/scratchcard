import './bootstrap';

const router = createRouter({
    history: createWebHistory(),
    routes,
});

const app = createApp(AdminApp);
app.use(router);
app.mount('#admin-app');
