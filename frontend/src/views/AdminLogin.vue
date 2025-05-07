<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
      <h2 class="text-2xl font-bold mb-6 text-center">Đăng nhập Admin</h2>
      <form @submit.prevent="login">
        <div class="mb-4">
          <label class="block text-gray-700">Email</label>
          <input
            v-model="form.email"
            type="email"
            class="w-full p-2 border rounded"
            :class="{ 'border-red-500': errors.email }"
            required
          />
          <p v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</p>
        </div>
        <div class="mb-6">
          <label class="block text-gray-700">Mật khẩu</label>
          <input
            v-model="form.password"
            type="password"
            class="w-full p-2 border rounded"
            :class="{ 'border-red-500': errors.password }"
            required
          />
          <p v-if="errors.password" class="text-red-500 text-sm mt-1">{{ errors.password }}</p>
        </div>
        <button
          type="submit"
          class="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600 disabled:bg-blue-300"
          :disabled="isLoading"
        >
          {{ isLoading ? 'Đang đăng nhập...' : 'Đăng nhập' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { useRouter } from 'vue-router';

export default {
  name: 'AdminLogin',
  setup() {
    const router = useRouter();
    return { router };
  },
  data() {
    return {
      form: {
        email: '',
        password: '',
      },
      errors: {
        email: '',
        password: '',
      },
      isLoading: false,
    };
  },
  methods: {
    async login() {
      this.isLoading = true;
      this.errors = { email: '', password: '' };

      try {
        const response = await axios.post('http://localhost:8000/api/admin/login', this.form);
        localStorage.setItem('admin-token', response.data.token);
        this.router.push('/admin/dashboard');
      } catch (error) {
        const message = error.response?.data?.message || 'Đã có lỗi xảy ra';
        if (message.includes('Thông tin đăng nhập')) {
          this.errors.email = 'Email hoặc mật khẩu không đúng';
          this.errors.password = 'Email hoặc mật khẩu không đúng';
        } else {
          this.errors.email = message;
        }
      } finally {
        this.isLoading = false;
      }
    },
  },
};
</script>