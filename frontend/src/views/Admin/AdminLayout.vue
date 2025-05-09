<template>
    <div class="flex flex-col h-screen">
      <AdminHeader />
      <div class="flex flex-1 overflow-hidden">
        <AdminSidebar />
        <main class="flex-1 p-6 overflow-y-auto bg-gray-100">
          <router-view />
        </main>
      </div>
    </div>
  </template>
  
  <script>
  import AdminHeader from './component/AdminHeader.vue';
  import AdminSidebar from './component/AdminSidebar.vue';
  
  export default {
    name: 'AdminLayout',
    components: {
      AdminHeader,
      AdminSidebar,
    },
  };
  </script>
  