import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import VueMultiselect from 'vue-multiselect';
import './assets/main.css'; 
import axios from 'axios';

axios.defaults.baseURL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';

const app = createApp(App);

app.component('multiselect', VueMultiselect);
app.use(router);
app.mount('#app');