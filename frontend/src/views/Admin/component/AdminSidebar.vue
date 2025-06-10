<template>
  <aside class="w-64 bg-gray-800 text-white h-full flex flex-col">
    <div class="p-4 text-xl font-bold">Bảng điều khiển Admin</div>
    <nav class="flex-1">
      <ul>
        <li v-for="item in filteredMenuItems" :key="item.to">
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

    // Danh sách các mục menu
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
      { to: '/admin/reviews', label: 'Quản lý đánh giá' },
      { to: '/admin/reports', label: 'Quản lý báo cáo' },
      { to: '/admin/shipping-partners', label: 'Quản lý vận chuyển' },
      { to: '/admin/banners', label: 'Quản lý banner' },
    ];

    const hasPermission = (path) => {
      const role = localStorage.getItem('role');
      console.log('Kiểm tra quyền cho path:', path, 'với role:', role);

      if (path === '/admin/dashboard' && ['superadmin', 'admin', 'moderator'].includes(role)) {
        console.log('Tổng quan được phép cho role:', role);
        return true;
      }

      const matchedRoute = router.getRoutes().find((r) => r.path === path);
      if (!matchedRoute) {
        console.warn('Không tìm thấy route:', path);
        return false;
      }

      if (!matchedRoute.meta || !matchedRoute.meta.permissions) {
        console.warn('Route không có meta.permissions:', path);
        return false;
      }

      const hasViewPermission = matchedRoute.meta.permissions[role]?.includes('view') || false;
      console.log('Quyền view cho', path, ':', hasViewPermission);
      return hasViewPermission;
    };

    const filteredMenuItems = menuItems.filter((item) => {
      const allowed = hasPermission(item.to);
      console.log('Menu item:', item.label, 'Allowed:', allowed);
      return allowed;
    });

    const logout = async () => {
      const loginType = localStorage.getItem('loginType') || 'admin';
      const token = localStorage.getItem('token');
      console.log('Token khi đăng xuất:', token);
      console.log('Login type:', loginType);

      if (!token) {
        localStorage.removeItem('token');
        localStorage.removeItem('role');
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
        console.log('Phản hồi đăng xuất:', response.data);
        localStorage.removeItem('token');
        localStorage.removeItem('role');
        localStorage.removeItem('loginType');
        alert('Đăng xuất thành công');
        router.push(`/${loginType}/login`);
      } catch (error) {
        console.error('Lỗi đăng xuất:', error);
        localStorage.removeItem('token');
        localStorage.removeItem('role');
        localStorage.removeItem('loginType');
        alert('Đăng xuất thất bại: ' + (error.response?.data?.message || 'Lỗi không xác định'));
        router.push(`/${loginType}/login`);
      }
    };

    return { logout, filteredMenuItems };
  },
};
</script>