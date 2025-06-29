<template>
  <header class="bg-white shadow p-4 flex justify-between items-center w-full">
    <h1 class="text-xl font-bold">Bảng điều khiển Seller</h1>
    <div class="flex items-center space-x-4">
      <span>{{ currentEmail || 'Không xác định' }}</span>
      <router-link
        to="/seller/buyer-mode"
        class="text-gray-600 hover:text-orange-500"
        @click="switchToBuyerMode"
      >
        Chuyển sang chế độ Người mua
      </router-link>
    </div>
  </header>
</template>

<script>
export default {
  name: 'SellerHeader',
  computed: {
    currentEmail() {
      const email = localStorage.getItem('email');
      if (!email) {
        console.warn('No email found in localStorage');
        return null; // Return null to trigger fallback in template
      }
      return email;
    },
  },
  mounted() {
    console.log('SellerHeader email:', this.currentEmail);
  },
  methods: {
    switchToBuyerMode() {
      localStorage.setItem('loginType', 'buyer');
      window.dispatchEvent(new Event('storage'));
      this.$router.push('/');
    },
  },
};
</script>