<template>
  <div class="p-8 space-y-6">
    <h1 class="text-2xl font-bold">Quản lý người dùng</h1>
    <div class="flex justify-between items-center">
      <FilterSearch
        :filters="filters"
        :searchPlaceholder="'Tìm kiếm theo email...'"
        v-model:currentFilter="currentFilter"
        v-model:searchQuery="searchQuery"
        @search="applySearch"
      />
      <button
        v-if="hasPermission('create')"
        @click="openAddForm"
        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
      >
        Thêm người dùng
      </button>
    </div>
    <FormModal
      v-if="showFormModal"
      :show="showFormModal"
      :title="editingUser ? 'Chỉnh sửa người dùng' : 'Thêm người dùng'"
      :fields="userFormFields"
      :initialData="editingUser"
      :isEdit="!!editingUser"
      @close="showFormModal = false"
      @submit="handleUserFormSubmit"
    />
    <p v-if="filteredUsers.length === 0" class="text-center text-gray-500">
      Không tìm thấy người dùng nào.
    </p>
    <table v-else class="min-w-full table-auto border border-gray-300">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-4 py-2 border">STT</th>
          <th class="px-4 py-2 border">Email</th>
          <th class="px-4 py-2 border">Vai trò</th>
          <th class="px-4 py-2 border">Trạng thái</th>
          <th v-if="hasPermission('delete')" class="px-4 py-2 border">Hành động</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="(user, index) in filteredUsers"
          :key="user.id">
          <td class="px-4 py-2 border text-center">{{ index + 1 }}</td>
          <td class="px-4 py-2 border text-center">{{ user.email || 'N/A' }}</td>
          <td class="px-4 py-2 border capitalize text-center">{{ user.role || 'N/A' }}</td>
          <td class="px-4 py-2 border">
            <div class="flex items-center gap-3 justify-center">
              <label v-if="hasPermission('updateStatus')" class="relative inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  class="sr-only peer"
                  v-model="user.tempStatus"
                  @change="confirmToggleStatus(user)"
                />
                <div
                  class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-green-500 transition"
                ></div>
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
          <td v-if="hasPermission('delete')" class="px-4 py-2 border text-center">
            <button
              @click="openConfirmModal('delete', user)"
              class="text-red-600 hover:underline"
            >
              Xóa
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Modal xác nhận -->
    <ConfirmModal
      :show="showConfirmModal"
      :title="confirmTitle"
      :message="confirmMessage"
      :confirmText="'Xác nhận'"
      :cancelText="'Hủy'"
      @confirm="handleConfirm"
      @cancel="handleCancel"
    />
  </div>
</template>

<script>
import axios from 'axios';
import FilterSearch from './component/AdminFilterSearch.vue';
import ConfirmModal from './component/AdminConfirmModal.vue';
import FormModal from './component/FormModal.vue';

export default {
  name: 'AdminUsers',
  components: { FilterSearch, ConfirmModal, FormModal },
  data() {
    return {
      users: [],
      allUsers: [],
      searchQuery: '',
      currentFilter: 'all',
      roles: [],
      statusText: {
        active: 'Hoạt động',
        banned: 'Đã bị chặn',
      },
      showConfirmModal: false,
      confirmAction: null,
      confirmUser: null,
      confirmTitle: 'Xác nhận',
      confirmMessage: '',
      newStatus: null,
      originalStatus: null,
      showFormModal: false,
      editingUser: null,
    };
  },
  computed: {
    userFormFields() {
      return [
        {
          name: 'email',
          label: 'Email',
          type: 'email',
          placeholder: 'Email',
          required: true,
        },
        {
          name: 'password',
          label: 'Mật khẩu',
          type: 'password',
          placeholder: 'Mật khẩu',
          required: !this.editingUser,
        },
        {
          name: 'role',
          label: 'Vai trò',
          type: 'select',
          options: this.roles.map((role) => ({
            value: role,
            label: role.charAt(0).toUpperCase() + role.slice(1),
          })),
          placeholder: 'Chọn vai trò',
          required: true,
        },
      ];
    },
    filters() {
      const statusFilters = [
        { key: 'all', label: 'Tất cả', count: this.allUsers.length },
        ...['active', 'banned'].map((s) => ({
          key: s,
          label: this.statusText[s],
          count: this.filterCountByStatus(s),
        })),
      ];
      const roleFilters = this.roles.map((r) => ({
        key: r,
        label: r.charAt(0).toUpperCase() + r.slice(1),
        count: this.filterCountByRole(r),
      }));
      return [...statusFilters, ...roleFilters];
    },
    filteredUsers() {
      const q = this.searchQuery.toLowerCase();
      return this.allUsers.filter((user) => {
        const matchQuery =
          (user.email || '').toLowerCase().includes(q) ||
          (user.role || '').toLowerCase().includes(q);
        const matchFilter =
          this.currentFilter === 'all' ||
          user.status === this.currentFilter ||
          user.role === this.currentFilter;
        return matchQuery && matchFilter;
      });
    },
  },
  async mounted() {
    // Kiểm tra quyền truy cập
    if (!this.hasPermission('view')) {
      this.$router.push('/admin/reviews');
      return;
    }
    await this.fetchUsers();
  },
  methods: {
    hasPermission(action) {
      const role = localStorage.getItem('role');
      const permissions = {
        superadmin: ['view', 'create', 'update', 'delete', 'updateStatus'],
        admin: ['view', 'update', 'updateStatus'],
        moderator: [],
      };
      return permissions[role]?.includes(action) || false;
    },
    async fetchUsers() {
      const token = localStorage.getItem('token');
      console.log('Token:', token);
      try {
        if (!token) {
          throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        }
        const response = await axios.get('/admin/users', {
          headers: { Authorization: `Bearer ${token}` },
        });
        console.log('Phản hồi API:', response.data); // Debug dữ liệu trả về

        // Kiểm tra response.data có phải mảng không
        if (!Array.isArray(response.data)) {
          console.error('Dữ liệu trả về không phải mảng:', response.data);
          throw new Error('Dữ liệu người dùng không đúng định dạng.');
        }

        this.users = response.data.map((user) => ({
          ...user,
          tempStatus: user.status === 'active',
        }));
        this.allUsers = this.users;
        this.roles = this.extractRoles(response.data);
      } catch (error) {
        console.error('Lỗi khi tải danh sách người dùng:', error);
        alert('Không thể tải danh sách người dùng: ' + (error.message || 'Lỗi không xác định.'));
        if (error.response?.status === 401 || error.response?.status === 403) {
          this.$router.push('/admin/login');
        }
      }
    },
    extractRoles(users) {
      return Array.from(new Set(users.map((user) => user.role).filter(Boolean)));
    },
    async handleUserFormSubmit(form) {
      const token = localStorage.getItem('token');
      if (!token) {
        alert('Vui lòng đăng nhập lại.');
        this.$router.push('/admin/login');
        return;
      }
      if (!this.hasPermission(this.editingUser ? 'update' : 'create')) {
        alert('Bạn không có quyền thực hiện hành động này.');
        this.showFormModal = false;
        return;
      }
      try {
        const response = this.editingUser
          ? await axios.put(
              `/admin/users/${this.editingUser.id}`,
              form,
              { headers: { Authorization: `Bearer ${token}` } }
            )
          : await axios.post(
              '/admin/users',
              { ...form, status: 'active' },
              { headers: { Authorization: `Bearer ${token}` } }
            );
        if (this.editingUser) {
          Object.assign(this.editingUser, response.data);
        } else {
          this.users.push({
            ...response.data,
            tempStatus: response.data.status === 'active',
          });
          this.allUsers = [...this.users];
        }
        this.showFormModal = false;
        alert(
          this.editingUser
            ? 'Cập nhật người dùng thành công'
            : 'Thêm người dùng thành công'
        );
      } catch (error) {
        console.error('Lỗi khi xử lý form người dùng:', error);
        if (error.response?.status === 422) {
          const errors = error.response.data.errors;
          let errorMessage = 'Lỗi xác thực:\n';
          for (const field in errors) {
            errorMessage += `- ${field}: ${errors[field].join(', ')}\n`;
          }
          alert(errorMessage);
        } else if (error.response?.status === 403) {
          alert('Bạn không có quyền thực hiện hành động này.');
        } else {
          alert('Lỗi: ' + (error.response?.data?.message || error.message));
        }
      }
    },
    async updateStatus(user) {
      const token = localStorage.getItem('token');
      if (!this.hasPermission('updateStatus')) {
        alert('Bạn không có quyền cập nhật trạng thái.');
        user.status = this.originalStatus;
        user.tempStatus = this.originalStatus === 'active';
        return;
      }
      try {
        await axios.put(
          `/admin/users/${user.id}/status`,
          { status: this.newStatus },
          { headers: { Authorization: `Bearer ${token}` } }
        );
        user.status = this.newStatus;
        user.tempStatus = this.newStatus === 'active';
        alert('Cập nhật trạng thái thành công');
      } catch (error) {
        console.error('Lỗi cập nhật trạng thái:', error);
        alert('Cập nhật trạng thái thất bại');
        user.status = this.originalStatus;
        user.tempStatus = this.originalStatus === 'active';
      }
    },
    async deleteUser(userId) {
      const token = localStorage.getItem('token');
      if (!this.hasPermission('delete')) {
        alert('Bạn không có quyền xóa người dùng.');
        return;
      }
      try {
        await axios.delete(`/admin/users/${userId}`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.users = this.users.filter((user) => user.id !== userId);
        this.allUsers = this.allUsers.filter((user) => user.id !== userId);
        alert('Đã xóa người dùng thành công');
      } catch (error) {
        console.error('Lỗi khi xóa người dùng:', error);
        alert('Xóa người dùng thất bại');
        if (error.response?.status === 403) {
          alert('Bạn không có quyền xóa người dùng.');
        }
      }
    },
    confirmToggleStatus(user) {
      const newStatus = user.tempStatus ? 'active' : 'banned';
      user.tempStatus = user.status === 'active';
      this.openConfirmModal('status', user, newStatus);
    },
    openConfirmModal(action, user, newStatus = null) {
      this.confirmAction = action;
      this.confirmUser = user;
      if (action === 'delete') {
        this.confirmTitle = 'Xác nhận xóa';
        this.confirmMessage = `Bạn có chắc chắn muốn xóa người dùng "${user.email || 'N/A'}" không?`;
      } else if (action === 'status') {
        this.originalStatus = user.status;
        this.newStatus = newStatus;
        this.confirmTitle = 'Xác nhận đổi trạng thái';
        this.confirmMessage = `Bạn có chắc chắn muốn đổi trạng thái người dùng "${
          user.email || 'N/A'
        }" thành "${this.statusText[newStatus]}" không?`;
      }
      this.showConfirmModal = true;
    },
    async handleConfirm() {
      if (this.confirmAction === 'delete') {
        await this.deleteUser(this.confirmUser.id);
      } else if (this.confirmAction === 'status') {
        await this.updateStatus(this.confirmUser);
      }
      this.resetModal();
    },
    handleCancel() {
      if (this.confirmAction === 'status' && this.confirmUser) {
        this.confirmUser.status = this.originalStatus;
        this.confirmUser.tempStatus = this.originalStatus === 'active';
      }
      this.resetModal();
    },
    resetModal() {
      this.showConfirmModal = false;
      this.confirmAction = null;
      this.confirmUser = null;
      this.confirmTitle = 'Xác nhận';
      this.confirmMessage = '';
      this.newStatus = null;
      this.originalStatus = null;
    },
    openAddForm() {
      this.editingUser = null;
      this.showFormModal = true;
    },
    openEditForm(user) {
      this.editingUser = { ...user };
      this.showFormModal = true;
    },
    applySearch() {
      console.log('Apply User Search:', this.searchQuery);
    },
    filterCountByStatus(status) {
      return this.allUsers.filter((user) => user.status === status).length;
    },
    filterCountByRole(role) {
      return this.allUsers.filter((user) => user.role === role).length;
    },
  },
};
</script>

<style scoped>
.transition-transform {
  transition: transform 0.3s ease;
}
.transition {
  transition: background-color 0.3s ease;
}
</style>