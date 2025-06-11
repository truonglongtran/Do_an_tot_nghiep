<template>
  <header class="bg-white shadow p-4 flex justify-between items-center w-full">
    <h1 class="text-xl font-bold">Bảng điều khiển Seller</h1>
    <div>{{ currentEmail }}</div>
  </header>
</template>

<script>
export default {
  name: 'SellerHeader',
  computed: {
    currentEmail() {
      return localStorage.getItem('email') || 'Unknown';
    },
  },
};
</script>