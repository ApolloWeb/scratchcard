import './bootstrap';
import { createApp } from 'vue';

// Check if we're on the admin page or player page
if (document.getElementById('admin-app')) {
  // Admin app
  import('./admin/router').then(({ default: router }) => {
    import('./admin/AdminApp.vue').then(({ default: AdminApp }) => {
      const app = createApp(AdminApp);
      app.use(router);
      app.mount('#admin-app');
    });
  });
} else if (document.getElementById('app')) {
  // Player app
  import('./player/TicketPlayer.vue').then(({ default: TicketPlayer }) => {
    const app = createApp(TicketPlayer, {
      code: window.ticketCode || ''
    });
    app.mount('#app');
  });
}
