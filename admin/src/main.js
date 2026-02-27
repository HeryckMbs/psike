import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import vuetify from './plugins/vuetify'
import './assets/styles/admin-theme.css'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)
app.use(vuetify)

// Tratamento de erros
app.config.errorHandler = (err, instance, info) => {
  console.error('Vue Error:', err, info)
}

app.mount('#app')
