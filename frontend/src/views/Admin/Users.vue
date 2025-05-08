<template>
    <div class="users">
      <h1>Quản lý người dùng</h1>
      <ul>
        <li v-for="user in users" :key="user.id">
          {{ user.name }} - {{ user.role }}
        </li>
      </ul>
    </div>
  </template>
  
  <script>
  export default {
    name: 'AdminUsers',
    data() {
      return {
        users: [
          { id: 1, name: 'Nguyễn Văn A', role: 'seller' },
          { id: 2, name: 'Trần Thị B', role: 'buyer' },
        ],
      };
    },
  };
  </script>
  
  <style scoped>
  .users {
    padding: 2rem;
  }
  </style>
  