<template>
  <div class="container mx-auto px-4 py-8 flex items-center justify-center min-h-screen">
    <div class="bg-white border rounded-lg shadow-lg p-6 text-center max-w-md w-full">
      <svg class="w-16 h-16 mx-auto text-green-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
      </svg>
      <h1 class="text-2xl font-bold text-gray-800 mb-2">Đặt hàng thành công!</h1>
      <p class="text-gray-600 mb-4">{{ successMessage }}</p>
      <router-link
        to="/buyer/orders"
        class="inline-block bg-orange-500 text-white px-6 py-2 rounded-lg hover:bg-orange-600 transition-colors"
      >
        Xem đơn hàng
      </router-link>
    </div>
  </div>
</template>

<script>
export default {
  name: 'OrderSuccess',
  data() {
    return {
      successMessage: 'Các đơn hàng đã được tạo thành công.',
    };
  },
  created() {
    // Retrieve success message from route query if provided
    if (this.$route.query.message) {
      this.successMessage = this.$route.query.message;
    }
  },
};
</script>

<style scoped>
/* Tailwind handles most styling, no additional CSS needed */
</style>