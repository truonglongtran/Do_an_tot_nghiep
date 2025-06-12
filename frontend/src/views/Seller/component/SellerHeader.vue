<template>
  <header class="bg-white shadow p-4 flex justify-between items-center w-full">
    <h1 class="text-xl font-bold">Bảng điều khiển Seller</h1>
    <div>{{ currentEmail || 'Không xác định' }}</div>
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
    // Debug: Log email on component mount
    console.log('SellerHeader email:', this.currentEmail);
  },
};
</script>