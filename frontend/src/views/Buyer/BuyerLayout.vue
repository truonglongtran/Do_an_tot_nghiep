<template>
  <div class="min-h-screen flex flex-col">
    <Header :is-logged-in="isLoggedIn" :user="user" />
    <main class="flex-1 pt-16"> <!-- Thêm pt-16 (~64px) để tránh header che -->
      <router-view />
    </main>
    <Footer />
    <!-- Bottom Navigation Bar (Mobile) -->
    <nav class="md:hidden fixed bottom-0 w-full bg-white shadow-lg flex justify-around py-2">
      <router-link to="/" class="flex flex-col items-center text-gray-600 hover:text-orange-500">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7m-9 5v6h4v-6m-6 0h6" />
        </svg>
        <span class="text-xs">Trang chủ</span>
      </router-link>
      <router-link to="/category/all" class="flex flex-col items-center text-gray-600 hover:text-orange-500">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
        </svg>
        <span class="text-xs">Danh mục</span>
      </router-link>
      <router-link to="/cart" class="flex flex-col items-center text-gray-600 hover:text-orange-500">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <span class="text-xs">Giỏ hàng</span>
      </router-link>
      <router-link to="/buyer/messages" class="flex flex-col items-center text-gray-600 hover:text-orange-500">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5v-4l7-7 7 7v4h-4" />
        </svg>
        <span class="text-xs">Tin nhắn</span>
      </router-link>
      <router-link to="/buyer/dashboard" class="flex flex-col items-center text-gray-600 hover:text-orange-500">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
        <span class="text-xs">Tài khoản</span>
      </router-link>
    </nav>
  </div>
</template>

<script>
import Header from './component/Header.vue';
import Footer from './component/Footer.vue';

export default {
  components: { Header, Footer },
  computed: {
    isLoggedIn() {
      return !!localStorage.getItem('token');
    },
    user() {
      return {
        email: localStorage.getItem('email') || '',
        avatar_url: localStorage.getItem('avatar_url') || 'https://via.placeholder.com/50',
        username: localStorage.getItem('username') || 'Người dùng',
      };
    },
  },
};
</script>

<style scoped>
/* Không cần style vì Tailwind CSS đã xử lý */
</style>