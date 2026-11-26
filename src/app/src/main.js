import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import { useUserStore } from './stores/useUserStore'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

const auth = useUserStore(pinia)
auth.initialize().finally(() => {
  app.mount('#app')
})
