<template>
    <div class="p-6">
      <h1 class="text-2xl font-bold">Bảng điều khiển Người bán</h1>
      <p>Xin chào, Người bán!</p>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  import { useRouter } from 'vue-router';
  
  export default {
    name: 'SellerDashboard',
    setup() {
      const router = useRouter();
      return { router };
    },
    methods: {
      async logout() {
        try {
          await axios.post('http://localhost:8000/api/seller/logout', {}, {
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
          });
          localStorage.removeItem('token');
          localStorage.removeItem('role');
          this.router.push('/seller/login');
        } catch (error) {
          console.error('Đăng xuất thất bại:', error);
        }
      },
    },
  };
  </script>