<template>
  <div class="p-8 space-y-6">
    <h1 class="text-2xl font-bold">Quản lý người dùng</h1>

    <!-- Container cho bộ lọc, tìm kiếm và nút Thêm người dùng -->
    <div class="flex justify-between items-center">
      <!-- Bộ lọc và tìm kiếm -->
      <div class="flex items-center space-x-2">
        <!-- Nút lọc -->
        <button
          @click="setFilter('all')"
          :class="{
            'bg-blue-600 text-white': filter === 'all',
            'bg-gray-200 text-gray-700': filter !== 'all'
          }"
          class="px-3 py-1 rounded hover:bg-blue-500 hover:text-white transition"
        >
          Tất cả ({{ countUsers('all') }})
        </button>
        <button
          @click="setFilter('buyer')"
          :class="{
            'bg-blue-600 text-white': filter === 'buyer',
            'bg-gray-200 text-gray-700': filter !== 'buyer'
          }"
          class="px-3 py-1 rounded hover:bg-blue-500 hover:text-white transition"
        >
          Người mua ({{ countUsers('buyer') }})
        </button>
        <button
          @click="setFilter('seller')"
          :class="{
            'bg-blue-600 text-white': filter === 'seller',
            'bg-gray-200 text-gray-700': filter !== 'seller'
          }"
          class="px-3 py-1 rounded hover:bg-blue-500 hover:text-white transition"
        >
          Người bán ({{ countUsers('seller') }})
        </button>
        <button
          @click="setFilter('active')"
          :class="{
            'bg-blue-600 text-white': filter === 'active',
            'bg-gray-200 text-gray-700': filter !== 'active'
          }"
          class="px-3 py-1 rounded hover:bg-blue-500 hover:text-white transition"
        >
          Hoạt động ({{ countUsers('active') }})
        </button>
        <button
          @click="setFilter('banned')"
          :class="{
            'bg-blue-600 text-white': filter === 'banned',
            'bg-gray-200 text-gray-700': filter !== 'banned'
          }"
          class="px-3 py-1 rounded hover:bg-blue-500 hover:text-white transition"
        >
          Đã bị chặn ({{ countUsers('banned') }})
        </button>
        <!-- Ô tìm kiếm và nút Tìm kiếm -->
        <div class="flex items-center space-x-2">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Tìm kiếm theo email..."
            class="border px-3 py-1 rounded w-64 focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
          <button
            @click="applySearch"
            class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 flex items-center"
          >
            <svg
              class="w-5 h-5 mr-1"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
              />
            </svg>
            Tìm
          </button>
        </div>
      </div>
      <!-- Nút Thêm người dùng -->
      <button
        @click="openAddForm"
        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
      >
        Thêm người dùng
      </button>
    </div>

    <UserForm
      v-if="showFormModal"
      :user="editingUser"
      @close="showFormModal = false"
      @submit="handleUserFormSubmit"
    />

    <!-- Bảng người dùng -->
    <table class="min-w-full table-auto border border-gray-300">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-4 py-2 border">STT</th>
          <th class="px-4 py-2 border">Email</th>
          <th class="px-4 py-2 border">Vai trò</th>
          <th class="px-4 py-2 border">Trạng thái</th>
          <th class="px-4 py-2 border">Hành động</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="(user, index) in filteredUsers"
          :key="user.id"
          class="hover:bg-gray-50 transition"
        >
          <td class="px-4 py-2 border text-center">{{ index + 1 }}</td>
          <td class="px-4 py-2 border text-center">{{ user.email }}</td>
          <td class="px-4 py-2 border capitalize text-center">{{ user.role }}</td>
          <td class="px-4 py-2 border">
            <div class="flex items-center gap-3 justify-center">
              <label class="relative inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  class="sr-only peer"
                  :checked="user.status === 'active'"
                  @change="() => confirmToggleStatus(user)"
                />
                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-green-500 transition"></div>
                <div
                  class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full shadow-md peer-checked:translate-x-full transition-transform"
                ></div>
              </label>
              <span
                :class="{
                  'text-green-600 font-medium': user.status === 'active',
                  'text-red-600 font-medium': user.status === 'banned'
                }"
              >
                {{ user.status === 'active' ? 'Hoạt động' : 'Đã bị chặn' }}
              </span>
            </div>
          </td>
          <td class="px-4 py-2 border text-center">
            <button
              @click="confirmDelete(user)"
              class="text-red-600 hover:underline"
            >
              Xóa
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import axios from 'axios';
import UserForm from './UserForm.vue';

export default {
  name: 'AdminUsers',
  components: {
    UserForm,
  },
  data() {
    return {
      users: [],
      newUser: {
        email: '',
        password: '',
        role: 'buyer',
      },
      showFormModal: false,
      editingUser: null,
      filter: 'all', // all, buyer, seller, active, banned
      searchQuery: '', // Từ khóa nhập vào ô tìm kiếm
      appliedSearchQuery: '', // Từ khóa được áp dụng sau khi nhấn Tìm kiếm
    };
  },
  computed: {
    filteredUsers() {
      return this.users.filter((user) => {
        // Lọc theo từ khóa tìm kiếm đã áp dụng
        const matchesSearch = this.appliedSearchQuery
          ? user.email.toLowerCase().includes(this.appliedSearchQuery.toLowerCase())
          : true;
        // Lọc theo bộ lọc
        const matchesFilter =
          this.filter === 'all' ||
          (this.filter === 'buyer' && user.role === 'buyer') ||
          (this.filter === 'seller' && user.role === 'seller') ||
          (this.filter === 'active' && user.status === 'active') ||
          (this.filter === 'banned' && user.status === 'banned');
        return matchesSearch && matchesFilter;
      });
    },
  },
  async created() {
    await this.fetchUsers();
  },
  methods: {
    async fetchUsers() {
      try {
        const token = localStorage.getItem('token');
        const response = await axios.get('http://localhost:8000/api/admin/users', {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.users = response.data;
      } catch (error) {
        console.error('Lỗi khi lấy danh sách người dùng:', error);
      }
    },
    async addUser() {
      try {
        const token = localStorage.getItem('token');
        const response = await axios.post(
          'http://localhost:8000/api/admin/users',
          this.newUser,
          {
            headers: { Authorization: `Bearer ${token}` },
          }
        );
        this.users.push(response.data);
        this.newUser = { email: '', password: '', role: 'buyer' };
      } catch (error) {
        alert('Lỗi khi thêm người dùng');
        console.error(error);
      }
    },
    async confirmToggleStatus(user) {
      const confirmMsg = `Bạn có chắc muốn ${
        user.status === 'active' ? 'chặn' : 'mở lại'
      } người dùng này không?`;
      if (confirm(confirmMsg)) {
        await this.toggleStatus(user);
      }
    },
    async toggleStatus(user) {
      const newStatus = user.status === 'active' ? 'banned' : 'active';
      try {
        const token = localStorage.getItem('token');
        await axios.put(
          `http://localhost:8000/api/admin/users/${user.id}/status`,
          { status: newStatus },
          {
            headers: { Authorization: `Bearer ${token}` },
          }
        );
        user.status = newStatus;
      } catch (error) {
        alert('Không thể cập nhật trạng thái.');
        console.error(error);
      }
    },
    async confirmDelete(user) {
      if (confirm(`Bạn có chắc muốn xóa người dùng "${user.email}" không?`)) {
        try {
          const token = localStorage.getItem('token');
          await axios.delete(`http://localhost:8000/api/admin/users/${user.id}`, {
            headers: { Authorization: `Bearer ${token}` },
          });
          this.users = this.users.filter((u) => u.id !== user.id);
        } catch (error) {
          alert('Không thể xóa người dùng.');
          console.error(error);
        }
      }
    },
    openAddForm() {
      this.editingUser = null;
      this.showFormModal = true;
    },
    editUser(user) {
      this.editingUser = user;
      this.showFormModal = true;
    },
    async handleUserFormSubmit(form) {
      const token = localStorage.getItem('token');
      if (!token) {
        alert('Vui lòng đăng nhập lại.');
        this.$router.push('/admin/login');
        return;
      }
      try {
        const response = this.editingUser
          ? await axios.put(
              `http://localhost:8000/api/admin/users/${this.editingUser.id}`,
              form,
              { headers: { Authorization: `Bearer ${token}` } }
            )
          : await axios.post(
              'http://localhost:8000/api/admin/users',
              form,
              { headers: { Authorization: `Bearer ${token}` } }
            );
        if (this.editingUser) {
          Object.assign(this.editingUser, response.data);
        } else {
          this.users.push(response.data);
        }
        this.showFormModal = false;
      } catch (err) {
        if (err.response?.status === 422) {
          const errors = err.response.data.errors;
          let errorMessage = 'Lỗi xác thực:\n';
          for (const field in errors) {
            errorMessage += `- ${field}: ${errors[field].join(', ')}\n`;
          }
          alert(errorMessage);
        } else {
          alert('Lỗi: ' + (err.response?.data?.message || err.message));
        }
        console.error(err);
      }
    },
    setFilter(value) {
      this.filter = value;
    },
    applySearch() {
      this.appliedSearchQuery = this.searchQuery;
    },
    countUsers(filter) {
      const filteredUsers = this.users.filter((user) => {
        // Lọc theo từ khóa tìm kiếm đã áp dụng
        const matchesSearch = this.appliedSearchQuery
          ? user.email.toLowerCase().includes(this.appliedSearchQuery.toLowerCase())
          : true;
        // Lọc theo bộ lọc
        const matchesFilter =
          filter === 'all' ||
          (filter === 'buyer' && user.role === 'buyer') ||
          (filter === 'seller' && user.role === 'seller') ||
          (filter === 'active' && user.status === 'active') ||
          (filter === 'banned' && user.status === 'banned');
        return matchesSearch && matchesFilter;
      });
      return filteredUsers.length;
    },
  },
};
</script>