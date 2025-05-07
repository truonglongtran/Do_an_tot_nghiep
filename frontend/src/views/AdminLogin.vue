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
              required
            />
          </div>
          <div class="mb-6">
            <label class="block text-gray-700">Mật khẩu</label>
            <input
              v-model="form.password"
              type="password"
              class="w-full p-2 border rounded"
              required
            />
          </div>
          <button
            type="submit"
            class="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600"
          >
            Đăng nhập
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
      };
    },
    methods: {
      async login() {
        try {
          const response = await axios.post('http://localhost:8000/api/admin/login', this.form);
          localStorage.setItem('admin-token', response.data.token);
          this.router.push('/admin/dashboard');
        } catch (error) {
          console.error('Đăng nhập thất bại:', error.response?.data?.message || error.message);
          alert('Thông tin đăng nhập không hợp lệ');
        }
      },
    },
  };
  </script>