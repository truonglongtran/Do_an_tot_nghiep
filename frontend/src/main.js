import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import VueMultiselect from 'vue-multiselect';
import './assets/main.css'; 

const app = createApp(App);

app.component('multiselect', VueMultiselect);
app.use(router);
app.mount('#app');