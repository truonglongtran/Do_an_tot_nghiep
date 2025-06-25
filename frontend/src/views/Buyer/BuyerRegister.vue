<!-- src/views/Buyer/BuyerRegister.vue -->
<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-orange-500 mb-4">Đăng ký</h1>
    <form @submit.prevent="register" class="max-w-md mx-auto space-y-4">
      <input v-model="form.email" type="email" placeholder="Email" class="border rounded-lg p-2 w-full" required />
      <input v-model="form.phone_number" type="text" placeholder="Số điện thoại" class="border rounded-lg p-2 w-full" required />
      <input v-model="form.password" type="password" placeholder="Mật khẩu" class="border rounded-lg p-2 w-full" required />
      <input v-model="form.password_confirmation" type="password" placeholder="Xác nhận mật khẩu" class="border rounded-lg p-2 w-full" required />
      <button type="submit" class="bg-orange-500 text-white px-4 py-2 rounded-lg w-full">Đăng ký</button>
    </form>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'BuyerRegister',
  data() {
    return {
      form: {
        email: '',
        phone_number: '',
        password: '',
        password_confirmation: '',
      },
    };
  },
  methods: {
    async register() {
      try {
        const response = await axios.post('/api/buyer/register', this.form);
        localStorage.setItem('token', response.data.token);
        localStorage.setItem('role', 'buyer');
        localStorage.setItem('email', this.form.email);
        localStorage.setItem('user_id', response.data.user.id);
        this.$router.push('/');
      } catch (error) {
        console.error('Error registering:', error);
        alert('Đăng ký thất bại');
      }
    },
  },
};
</script>

<style scoped>
/* Không cần style vì Tailwind CSS đã xử lý */
</style>