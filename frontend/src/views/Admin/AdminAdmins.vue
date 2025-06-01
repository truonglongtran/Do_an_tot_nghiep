<template>
  <div class="p-8 space-y-6">
    <h1 class="text-2xl font-bold">Quản lý Admin</h1>
    <div class="flex justify-between items-center">
      <FilterSearch
        :filters="filters"
        :searchPlaceholder="'Tìm kiếm theo email...'"
        v-model:currentFilter="currentFilter"
        v-model:searchQuery="searchQuery"
        @search="applySearch"
      />
      <button
        @click="openAddForm"
        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
      >
        Thêm Admin
      </button>
    </div>
    <FormModal
      v-if="showFormModal"
      :show="showFormModal"
      :title="editingAdmin ? 'Chỉnh sửa Admin' : 'Thêm Admin'"
      :fields="adminFormFields"
      :initialData="editingAdmin"
      :isEdit="!!editingAdmin"
      @close="showFormModal = false"
      @submit="handleAdminFormSubmit"
      ref="formModal"
    />
    <p v-if="filteredAdmins.length === 0" class="text-center text-gray-500">
      Không tìm thấy admin nào.
    </p>
    <table v-else class="min-w-full table-auto border border-gray-300">
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
          v-for="(admin, index) in filteredAdmins"
          :key="admin.id"
          class="hover:bg-gray-50 transition"
        >
          <td class="px-4 py-2 border text-center">{{ index + 1 }}</td>
          <td class="px-4 py-2 border text-center">{{ admin.email || 'N/A' }}</td>
          <td class="px-4 py-2 border capitalize text-center">{{ admin.role || 'N/A' }}</td>
          <td class="px-4 py-2 border">
            <div class="flex items-center gap-3 justify-center">
              <label class="relative inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  class="sr-only peer"
                  v-model="admin.tempStatus"
                  @change="confirmToggleStatus(admin)"
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
                  'text-green-600 font-medium': admin.status === 'active',
                  'text-red-600 font-medium': admin.status === 'inactive'
                }"
              >
                {{ admin.status === 'active' ? 'Hoạt động' : 'Không hoạt động' }}
              </span>
            </div>
          </td>
          <td class="px-4 py-2 border text-center">
            <button
              @click="openEditForm(admin)"
              class="text-blue-600 hover:underline mr-2"
            >
              Sửa
            </button>
            <button
              @click="openConfirmModal('delete', admin)"
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
  name: 'AdminAdmins',
  components: { FilterSearch, ConfirmModal, FormModal },
  data() {
    return {
      admins: [],
      allAdmins: [],
      searchQuery: '',
      currentFilter: 'all',
      roles: [],
      statusText: {
        active: 'Hoạt động',
        inactive: 'Không hoạt động',
      },
      showConfirmModal: false,
      confirmAction: null,
      confirmAdmin: null,
      confirmTitle: 'Xác nhận',
      confirmMessage: '',
      newStatus: null,
      originalStatus: null,
      showFormModal: false,
      editingAdmin: null,
    };
  },
  computed: {
    adminFormFields() {
      return [
        {
          name: 'email',
          label: 'Email',
          type: 'email',
          placeholder: 'Nhập email',
          required: true,
          rules: [
            {
              validator: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
              message: 'Email không hợp lệ',
            },
          ],
        },
        {
          name: 'password',
          label: 'Mật khẩu',
          type: 'password',
          placeholder: 'Nhập mật khẩu',
          required: !this.editingAdmin,
          rules: [
            {
              validator: (value) => !value || value.length >= 6,
              message: 'Mật khẩu phải có ít nhất 6 ký tự',
            },
          ],
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
          defaultValue: 'admin',
        },
      ];
    },
    filters() {
      const statusFilters = [
        { key: 'all', label: 'Tất cả', count: this.allAdmins.length },
        ...['active', 'inactive'].map((s) => ({
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
      const filters = [...statusFilters, ...roleFilters];
      console.log('Admin Filters:', filters);
      return filters;
    },
    filteredAdmins() {
      const q = this.searchQuery.toLowerCase();
      console.log('Filtering Admins:', q, 'Filter:', this.currentFilter);
      const result = this.allAdmins.filter((admin) => {
        const matchQuery =
          (admin.email || '').toLowerCase().includes(q) ||
          (admin.role || '').toLowerCase().includes(q);
        const matchFilter =
          this.currentFilter === 'all' ||
          admin.status === this.currentFilter ||
          admin.role === this.currentFilter;
        return matchQuery && matchFilter;
      });
      console.log('Filtered Admins:', result);
      return result;
    },
  },
  async mounted() {
    await this.fetchAdmins();
  },
  methods: {
    async fetchAdmins() {
      const token = localStorage.getItem('token');
      console.log('Token:', token);
      try {
        if (!token) {
          throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        }
        const response = await axios.get('http://localhost:8000/api/admin/admins', {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.admins = response.data.map((admin) => ({
          ...admin,
          tempStatus: admin.status === 'active',
        }));
        this.allAdmins = this.admins;
        this.roles = this.extractRoles(response.data);
        console.log('Admins:', this.admins);
        console.log('All Admins:', this.allAdmins);
        console.log('Roles:', this.roles);
      } catch (error) {
        console.error('Lỗi khi tải danh sách admin:', error);
        alert('Không thể tải danh sách admin.');
        if (error.response?.status === 401) {
          this.$router.push('/admin/login');
        }
      }
    },
    extractRoles(admins) {
      const set = new Set(admins.map((admin) => admin.role).filter(Boolean));
      const roles = Array.from(set).filter(role => ['admin', 'moderator'].includes(role));
      if (!roles.includes('admin')) {
        roles.push('admin');
      }
      console.log('Roles:', roles);
      return roles;
    },
    async handleAdminFormSubmit(form) {
      console.log('Form data:', form);
      const token = localStorage.getItem('token');
      if (!token) {
        alert('Vui lòng đăng nhập lại.');
        this.$router.push('/admin/login');
        return;
      }
      try {
        const response = this.editingAdmin
          ? await axios.put(
              `http://localhost:8000/api/admin/admins/${this.editingAdmin.id}`,
              form,
              { headers: { Authorization: `Bearer ${token}` } }
            )
          : await axios.post(
              'http://localhost:8000/api/admin/admins',
              { email: form.email, password: form.password, role: form.role },
              { headers: { Authorization: `Bearer ${token}` } }
            );
        if (this.editingAdmin) {
          Object.assign(this.editingAdmin, response.data.admin || response.data);
        } else {
          this.admins.push({
            ...response.data,
            tempStatus: response.data.status === 'active',
          });
          this.allAdmins = [...this.admins];
        }
        this.showFormModal = false;
        alert(this.editingAdmin ? 'Cập nhật admin thành công' : 'Thêm admin thành công');
      } catch (error) {
        console.error('Lỗi khi xử lý form admin:', error);
        console.error('Response data:', error.response?.data);
        if (error.response?.status === 422) {
          const errors = error.response.data.details || error.response.data.errors;
          let errorMessage = 'Lỗi xác thực:\n';
          for (const field in errors) {
            errorMessage += `- ${field}: ${errors[field].join(', ')}\n`;
          }
          alert(errorMessage);
        } else {
          alert('Lỗi server: ' + (error.response?.data?.error || error.message));
        }
      }
    },
    async updateStatus(admin) {
      const token = localStorage.getItem('token');
      try {
        await axios.put(
          `http://localhost:8000/api/admin/admins/${admin.id}/status`,
          { status: this.newStatus },
          { headers: { Authorization: `Bearer ${token}` } }
        );
        admin.status = this.newStatus;
        admin.tempStatus = this.newStatus === 'active';
        alert('Cập nhật trạng thái thành công');
      } catch (error) {
        console.error('Lỗi cập nhật trạng thái:', error);
        alert('Cập nhật trạng thái thất bại: ' + (error.response?.data?.error || error.message));
        admin.tempStatus = this.originalStatus === 'active';
      }
    },
    async deleteAdmin(adminId) {
      const token = localStorage.getItem('token');
      try {
        await axios.delete(`http://localhost:8000/api/admin/admins/${adminId}`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.admins = this.admins.filter((admin) => admin.id !== adminId);
        this.allAdmins = this.allAdmins.filter((admin) => admin.id !== adminId);
        alert('Đã xóa admin thành công');
      } catch (error) {
        console.error('Lỗi khi xóa admin:', error);
        alert('Xóa admin thất bại: ' + (error.response?.data?.error || error.message));
      }
    },
    confirmToggleStatus(admin) {
      const newStatus = admin.tempStatus ? 'active' : 'inactive';
      this.openConfirmModal('status', admin, newStatus);
    },
    openConfirmModal(action, admin, newStatus = null) {
      this.confirmAction = action;
      this.confirmAdmin = admin;
      if (action === 'delete') {
        this.confirmTitle = 'Xác nhận xóa';
        this.confirmMessage = `Bạn có chắc chắn muốn xóa admin "${admin.email || 'N/A'}" không?`;
      } else if (action === 'status') {
        this.originalStatus = admin.status;
        this.newStatus = newStatus;
        this.confirmTitle = 'Xác nhận đổi trạng thái';
        this.confirmMessage = `Bạn có chắc chắn muốn đổi trạng thái admin "${
          admin.email || 'N/A'
        }" thành "${this.statusText[newStatus]}" không?`;
      }
      this.showConfirmModal = true;
    },
    async handleConfirm() {
      if (this.confirmAction === 'delete') {
        await this.deleteAdmin(this.confirmAdmin.id);
      } else if (this.confirmAction === 'status') {
        await this.updateStatus(this.confirmAdmin);
      }
      this.resetModal();
    },
    handleCancel() {
      if (this.confirmAction === 'status' && this.confirmAdmin) {
        this.confirmAdmin.tempStatus = this.originalStatus === 'active';
      }
      this.resetModal();
    },
    resetModal() {
      this.showConfirmModal = false;
      this.confirmAction = null;
      this.confirmAdmin = null;
      this.confirmTitle = 'Xác nhận';
      this.confirmMessage = '';
      this.newStatus = null;
      this.originalStatus = null;
    },
    openAddForm() {
      this.editingAdmin = null;
      this.showFormModal = true;
      if (this.$refs.formModal) {
        this.$refs.formModal.initializeForm({});
      }
    },
    openEditForm(admin) {
      this.editingAdmin = {
        id: admin.id,
        email: admin.email || '',
        role: admin.role || 'admin',
      };
      this.showFormModal = true;
      if (this.$refs.formModal) {
        this.$refs.formModal.initializeForm(this.editingAdmin);
      }
    },
    applySearch() {
      console.log('Apply Admin Search:', this.searchQuery);
    },
    filterCountByStatus(status) {
      const count = this.allAdmins.filter((admin) => admin.status === status).length;
      console.log(`Count Status (${status}):`, count);
      return count;
    },
    filterCountByRole(role) {
      const count = this.allAdmins.filter((admin) => admin.role === role).length;
      console.log(`Count Role (${role}):`, count);
      return count;
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