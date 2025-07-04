<template>
  <aside class="w-64 bg-gray-800 text-white h-full flex flex-col">
    <div class="p-4 text-xl font-bold">Bảng điều khiển Seller</div>
    <nav class="flex-1">
      <ul>
        <li v-for="menu in menuItems" :key="menu.label" class="border-b border-gray-700">
          <button
            @click="toggleMenu(menu.label)"
            class="block w-full text-left p-4 hover:bg-gray-700 flex justify-between items-center"
          >
            {{ menu.label }}
            <svg
              class="w-4 h-4"
              :class="{ 'rotate-180': openMenus[menu.label] }"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </button>
          <ul v-if="openMenus[menu.label]" class="bg-gray-900">
            <li v-for="item in menu.subItems" :key="item.to" class="pl-8">
              <router-link
                :to="item.to"
                class="block p-4 hover:bg-gray-700"
                active-class="bg-gray-700"
              >
                {{ item.label }}
              </router-link>
            </li>
          </ul>
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
import { ref } from 'vue';

export default {
  name: 'SellerSidebar',
  setup() {
    const router = useRouter();
    const openMenus = ref({});

    const menuItems = [
      {
        label: 'Quản lý đơn hàng',
        subItems: [
          { to: '/seller/orders', label: 'Tất cả' },
          { to: '/seller/orders/delivery', label: 'Bàn giao đơn hàng' },
          { to: '/seller/orders/returns', label: 'Đơn trả hàng/Hoàn tiền/Đơn hủy' },
          { to: '/seller/shipping/settings', label: 'Cài đặt vận chuyển' },
        ],
      },
      {
        label: 'Quản lý sản phẩm',
        subItems: [
          { to: '/seller/products/all', label: 'Tất cả sản phẩm' },
          { to: '/seller/products/add', label: 'Thêm sản phẩm' },
        ],
      },
      {
        label: 'Chăm sóc khách hàng',
        subItems: [
          { to: '/seller/customer-service/chat', label: 'Quản lý chat' },
          { to: '/seller/customer-service/reviews', label: 'Quản lý đánh giá' },
        ],
      },
      {
        label: 'Tài chính',
        subItems: [
          { to: '/seller/finance/revenue', label: 'Doanh thu' },
        ],
      },
      {
        label: 'Quản lý shop',
        subItems: [
          { to: '/seller/shop/profile', label: 'Hồ sơ shop' },
          { to: '/seller/shop/settings', label: 'Thiết lập shop' },
          { to: '/seller/shop/decoration', label: 'Trang trí shop' },
        ],
      },
    ];

    const toggleMenu = (label) => {
      openMenus.value[label] = !openMenus.value[label];
    };

    const logout = async () => {
      const loginType = localStorage.getItem('loginType') || 'seller';
      const token = localStorage.getItem('token');

      if (!token) {
        localStorage.removeItem('token');
        localStorage.removeItem('email');
        localStorage.removeItem('loginType');
        router.push(`/${loginType}/login`);
        alert('Không tìm thấy token. Đã chuyển hướng đến trang đăng nhập.');
        return;
      }

      try {
        const response = await axios.post(
          `/${loginType}/logout`,
          {},
          {
            headers: {
              Authorization: `Bearer ${token}`,
              Accept: 'application/json',
            },
          }
        );
        localStorage.removeItem('token');
        localStorage.removeItem('email');
        localStorage.removeItem('loginType');
        alert('Đăng xuất thành công');
        router.push(`/${loginType}/login`);
      } catch (error) {
        localStorage.removeItem('token');
        localStorage.removeItem('email');
        localStorage.removeItem('loginType');
        alert('Đăng xuất thất bại: ' + (error.response?.data?.message || 'Lỗi không xác định'));
        router.push(`/${loginType}/login`);
      }
    };

    return { menuItems, logout, openMenus, toggleMenu };
  },
};
</script>