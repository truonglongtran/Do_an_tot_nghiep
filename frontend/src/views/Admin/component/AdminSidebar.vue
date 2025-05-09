<template>
    <aside class="w-64 bg-gray-800 text-white h-full flex flex-col">
      <div class="p-4 text-xl font-bold">Bảng điều khiển Admin</div>
      <nav class="flex-1">
        <ul>
          <li v-for="item in menuItems" :key="item.to">
            <router-link
              :to="item.to"
              class="block p-4 hover:bg-gray-700"
              active-class="bg-gray-700"
            >
              {{ item.label }}
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
    </aside>
  </template>
  
  <script>
  import axios from 'axios';
  import { useRouter } from 'vue-router';
  
  export default {
    name: 'AdminSidebar',
    setup() {
      const router = useRouter();
      const logout = async () => {
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
          router.push('/admin/login');
        } catch (error) {
          console.error('Đăng xuất thất bại:', error);
        }
      };
  
      const menuItems = [
        { to: '/admin/dashboard', label: 'Tổng quan' },
        { to: '/admin/users', label: 'Quản lý người dùng' },
        { to: '/admin/shops', label: 'Quản lý cửa hàng/người bán' },
        { to: '/admin/admins', label: 'Quản lý admin' },
        { to: '/admin/products', label: 'Quản lý sản phẩm' },
        { to: '/admin/orders', label: 'Quản lý đơn hàng' },
        { to: '/admin/disputes', label: 'Quản lý khiếu nại' },
        { to: '/admin/vouchers', label: 'Quản lý voucher/khuyến mãi' },
        { to: '/admin/payments', label: 'Quản lý thanh toán' },
        { to: '/admin/reports', label: 'Quản lý báo cáo' },
        { to: '/admin/shipping', label: 'Quản lý vận chuyển' },
        { to: '/admin/banners', label: 'Quản lý banner' },
      ];
  
      return { logout, menuItems };
    },
  };
  </script>
  