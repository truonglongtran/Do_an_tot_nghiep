<template>
    <div class="p-6">
      <h1 class="text-2xl font-bold">Bảng điều khiển Người mua</h1>
      <p>Xin chào, Người mua!</p>
      <button @click="logout" class="mt-4 bg-red-500 text-white p-2 rounded">Đăng xuất</button>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  import { useRouter } from 'vue-router';
  
  export default {
    name: 'BuyerDashboard',
    setup() {
      const router = useRouter();
      return { router };
    },
    methods: {
      async logout() {
        try {
          await axios.post('http://localhost:8000/api/buyer/logout', {}, {
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
          });
          localStorage.removeItem('token');
          localStorage.removeItem('role');
          this.router.push('/buyer/login');
        } catch (error) {
          console.error('Đăng xuất thất bại:', error);
        }
      },
    },
  };
  </script>