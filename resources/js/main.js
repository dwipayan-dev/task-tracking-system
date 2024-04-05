/* eslint-disable import/order */
import '@/@iconify/icons-bundle'
import App from './App.vue'
import vuetify from '@/plugins/vuetify'
import { loadFonts } from '@/plugins/webfontloader'
import router from '@/router'
import '@core-scss/template/index.scss'
import '@layouts/styles/index.scss'
import '@styles/styles.scss'
import { createPinia } from 'pinia'
import { createApp } from 'vue'
import { URL } from "./helper"

loadFonts()


// Create vue app
const app = createApp(App)


// Use plugins
app.use(vuetify)
app.use(createPinia())
app.use(router)

// Mount vue app
app.mount('#app')
app.config.globalProperties.$hostname = URL


import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;
const token = localStorage.getItem('APP_TOKEN')
window.Echo = new Echo({
    auth: {
        headers: {
            Authorization: 'Bearer ' + token,
            Accept: 'application/json',
        }
    },
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    wsHost: import.meta.env.VITE_PUSHER_HOST ?? `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
    wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
    wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

