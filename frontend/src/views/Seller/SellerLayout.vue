<template>
  <div class="flex flex-col h-screen">
    <SellerHeader />
    <div class="flex flex-1 overflow-hidden">
      <SellerSidebar />
      <main class="flex-1 p-6 overflow-y-auto bg-gray-100">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script>
import SellerHeader from './component/SellerHeader.vue';
import SellerSidebar from './component/SellerSidebar.vue';

export default {
  name: 'SellerLayout',
  components: {
    SellerHeader,
    SellerSidebar,
  },
};
</script>