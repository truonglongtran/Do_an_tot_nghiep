<template>
    <div class="flex h-screen bg-gray-100">
      <!-- Sidebar -->
      <div class="w-64 bg-gray-800 text-white flex flex-col">
        <div class="p-4 text-xl font-bold">Bảng điều khiển Admin</div>
        <nav class="flex-1">
          <ul>
            <li>
              <router-link
                to="/admin/dashboard"
                class="block p-4 hover:bg-gray-700"
                active-class="bg-gray-700"
              >
                Tổng quan
              </router-link>
            </li>
            <li>
              <router-link
                to="/admin/users"
                class="block p-4 hover:bg-gray-700"
                active-class="bg-gray-700"
              >
                Quản lý người dùng
              </router-link>
            </li>
            <li>
              <router-link
                to="/admin/shops"
                class="block p-4 hover:bg-gray-700"
                active-class="bg-gray-700"
              >
                Quản lý cửa hàng/người bán
              </router-link>
            </li>
            <li>
              <router-link
                to="/admin/admins"
                class="block p-4 hover:bg-gray-700"
                active-class="bg-gray-700"
              >
                Quản lý admin
              </router-link>
            </li>
            <li>
              <router-link
                to="/admin/products"
                class="block p-4 hover:bg-gray-700"
                active-class="bg-gray-700"
              >
                Quản lý sản phẩm
              </router-link>
            </li>
            <li>
              <router-link
                to="/admin/orders"
                class="block p-4 hover:bg-gray-700"
                active-class="bg-gray-700"
              >
                Quản lý đơn hàng
              </router-link>
            </li>
            <li>
              <router-link
                to="/admin/disputes"
                class="block p-4 hover:bg-gray-700"
                active-class="bg-gray-700"
              >
                Quản lý khiếu nại
              </router-link>
            </li>
            <li>
              <router-link
                to="/admin/vouchers"
                class="block p-4 hover:bg-gray-700"
                active-class="bg-gray-700"
              >
                Quản lý voucher/khuyến mãi
              </router-link>
            </li>
            <li>
              <router-link
                to="/admin/payments"
                class="block p-4 hover:bg-gray-700"
                active-class="bg-gray-700"
              >
                Quản lý thanh toán
              </router-link>
            </li>
            <li>
              <router-link
                to="/admin/reports"
                class="block p-4 hover:bg-gray-700"
                active-class="bg-gray-700"
              >
                Quản lý báo cáo
              </router-link>
            </li>
            <li>
              <router-link
                to="/admin/shipping"
                class="block p-4 hover:bg-gray-700"
                active-class="bg-gray-700"
              >
                Quản lý vận chuyển
              </router-link>
            </li>
            <li>
              <router-link
                to="/admin/banners"
                class="block p-4 hover:bg-gray-700"
                active-class="bg-gray-700"
              >
                Quản lý banner
              </router-link>
            </li>
          </ul>
        </nav>
        <div class="p-4">
          <button
            @click="logout"
            class="w-full bg-red-500 text-white p-2 rounded hover:bg-red-600"
          >
            Đăng xuất
          </button>
        </div>
      </div>
  
      <!-- Nội dung chính -->
      <div class="flex-1 flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow p-4 flex justify-between items-center">
          <h1 class="text-xl font-bold">Bảng điều khiển Admin</h1>
          <div>Superadmin</div>
        </header>
  
        <!-- Nội dung -->
        <main class="flex-1 p-6 overflow-y-auto">
          <router-view />
        </main>
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  import { useRouter } from 'vue-router';
  
  export default {
    name: 'AdminDashboard',
    setup() {
      const router = useRouter();
      return { router };
    },
    methods: {
      async logout() {
        try {
          await axios.post(
            'http://localhost:8000/api/admin/logout',
            {},
            {
              headers: {
                Authorization: `Bearer ${localStorage.getItem('admin-token')}`,
              },
            }
          );
          localStorage.removeItem('admin-token');
          this.router.push('/admin/login');
        } catch (error) {
          console.error('Đăng xuất thất bại:', error);
        }
      },
    },
  };
  </script>