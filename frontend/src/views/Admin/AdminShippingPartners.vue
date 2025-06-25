<template>
  <div class="p-8 bg-gray-50 min-h-screen">
    <div class="mx-auto">
      <div class="flex items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Quản lý đối tác vận chuyển</h2>
      </div>
      <div class="flex justify-between items-center mb-6">
        <FilterSearch
          :filters="filters"
          :searchPlaceholder="'Tìm theo tên đối tác...'"
          v-model="statusFilter"
          v-model:search="searchQuery"
          @search="applySearch"
        />
        <button
          v-if="hasPermission('create')"
          @click="openAddModal"
          class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
        >
          Thêm đối tác
        </button>
      </div>
      <p v-if="errorMessage" class="text-center text-red-500 mt-4">
        {{ errorMessage }}
      </p>
      <p v-else-if="filteredPartners.length === 0" class="text-center text-gray-500 mt-4">
        Không tìm thấy đối tác vận chuyển nào.
      </p>
      <table v-else class="table-auto w-full border bg-white shadow-sm rounded-lg overflow-hidden">
        <thead class="bg-gray-200">
          <tr>
            <th class="p-3 border-b text-left text-gray-700">STT</th>
            <th class="p-3 border-b text-left text-gray-700">Tên</th>
            <th class="p-3 border-b text-left text-gray-700">API URL</th>
            <th class="p-3 border-b text-left text-gray-700">Trạng thái</th>
            <th class="p-3 border-b text-left text-gray-700">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(partner, index) in filteredPartners" :key="partner.id" class="hover:bg-gray-50">
            <td class="p-3 border-b">{{ index + 1 }}</td>
            <td class="p-3 border-b">{{ partner.name || 'N/A' }}</td>
            <td class="p-3 border-b">
              <a v-if="partner.api_url" :href="partner.api_url" target="_blank" class="text-blue-600 hover:underline">
                {{ truncateUrl(partner.api_url) }}
              </a>
              <span v-else>N/A</span>
            </td>
            <td class="p-3 border-b">
              <label class="relative inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  class="sr-only peer"
                  :checked="partner.isActive"
                  @click.prevent="openToggleConfirmModal(partner, !partner.isActive)"
                  :disabled="!hasPermission('update') || partner.isLoading"
                />
                <div
                  class="w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-green-600 transition-colors duration-200"
                  :class="{ 'opacity-50 cursor-not-allowed': !hasPermission('update') || partner.isLoading }"
                >
                  <div
                    class="w-5 h-5 bg-white rounded-full shadow-md transform transition-transform duration-200"
                    :class="{ 'translate-x-6': partner.isActive }"
                  ></div>
                </div>
              </label>
            </td>
            <td class="p-3 border-b text-center space-x-2">
              <button
                @click="openDetailModal(partner)"
                class="text-blue-600 hover:underline"
              >
                Xem chi tiết
              </button>
              <button
                v-if="hasPermission('update')"
                @click="openEditModal(partner)"
                class="text-green-600 hover:underline"
              >
                Sửa
              </button>
              <button
                v-if="hasPermission('delete')"
                @click="openConfirmModal('delete', partner)"
                class="text-red-600 hover:underline"
              >
                Xóa
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal for Viewing Details -->
    <GenericDetailsModal
      :show="showDetailModal"
      :data="selectedPartner"
      :fields="partnerFields"
      title="Chi tiết đối tác vận chuyển"
      @close="closeDetailModal"
    />

    <!-- Modal for Adding/Editing Partner -->
    <FormModal
      v-if="showPartnerModal"
      :show="showPartnerModal"
      :title="editingPartner ? 'Sửa đối tác vận chuyển' : 'Thêm đối tác vận chuyển'"
      :fields="partnerFormFields"
      :initialData="editingPartner"
      :isEdit="!!editingPartner"
      @close="closePartnerModal"
      @submit="handlePartnerFormSubmit"
      ref="formModal"
    />

    <!-- Modal for Confirmation -->
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
import { useRouter } from 'vue-router';
import FilterSearch from './component/AdminFilterSearch.vue';
import ConfirmModal from './component/AdminConfirmModal.vue';
import GenericDetailsModal from './component/GenericDetailsModal.vue';
import FormModal from './component/FormModal.vue';

export default {
  name: 'AdminShippingPartners',
  components: { FilterSearch, ConfirmModal, GenericDetailsModal, FormModal },
  setup() {
    const router = useRouter();
    return { router };
  },
  data() {
    return {
      partners: [],
      statuses: [],
      errorMessage: '',
      searchQuery: '',
      statusFilter: 'all',
      showDetailModal: false,
      showPartnerModal: false,
      showConfirmModal: false,
      selectedPartner: null,
      editingPartner: null,
      confirmAction: null,
      confirmTitle: 'Xác nhận',
      confirmMessage: '',
      newIsActive: null,
      partnerFields: [
        { label: 'Tên', key: 'name', type: 'text' },
        { label: 'API URL', key: 'api_url', type: 'link' },
        {
          label: 'Trạng thái',
          key: 'status',
          type: 'custom',
          customFormat: (value) => this.formatStatus(value),
        },
        {
          label: 'Cửa hàng liên kết',
          key: 'shops',
          type: 'list',
          listItemKey: 'name',
        },
        { label: 'Ngày tạo', key: 'created_at', type: 'date' },
      ],
    };
  },
  computed: {
    partnerFormFields() {
      return [
        {
          name: 'name',
          label: 'Tên',
          type: 'text',
          placeholder: 'Nhập tên đối tác',
          required: true,
          rules: [
            {
              validator: (value) => value.trim().length > 0,
              message: 'Tên không được để trống',
            },
          ],
        },
        {
          name: 'api_url',
          label: 'API URL',
          type: 'url',
          placeholder: 'Nhập URL API (http:// hoặc https://)',
          required: true,
          rules: [
            {
              validator: (value) => /^https?:\/\/[^\s/$.?#].[^\s]*$/.test(value),
              message: 'URL không hợp lệ',
            },
          ],
        },
        {
          name: 'status',
          label: 'Trạng thái',
          type: 'select',
          placeholder: 'Chọn trạng thái',
          required: true,
          options: [
            { value: 'active', label: 'Hoạt động' },
            { value: 'inactive', label: 'Ngừng hoạt động' },
          ],
          defaultValue: 'active',
        },
      ];
    },
    filters() {
      return [
        { key: 'all', label: 'Tất cả trạng thái', count: this.partners.length },
        ...this.statuses.map(status => ({
          key: status,
          label: this.formatStatus(status),
          count: this.partners.filter(p => p.status === status).length,
        })),
      ];
    },
    filteredPartners() {
      const q = this.searchQuery.toLowerCase().trim();
      return this.partners.filter(partner => {
        const matchesSearch = !q || (partner.name || '').toLowerCase().includes(q);
        const matchesStatus = this.statusFilter === 'all' || partner.status === this.statusFilter;
        return matchesSearch && matchesStatus;
      });
    },
  },
  async mounted() {
    console.log('Mounted AdminShippingPartners');
    console.log('Role:', localStorage.getItem('role'));
    console.log('Has view permission:', this.hasPermission('view'));
    console.log('Has update permission:', this.hasPermission('update'));
    await this.fetchPartners();
  },
  methods: {
    hasPermission(action) {
      const role = localStorage.getItem('role') || 'viewer';
      const matchedRoute = this.router.getRoutes().find((r) => r.path === '/admin/shipping-partners');
      if (!matchedRoute || !matchedRoute.meta || !matchedRoute.meta.permissions) {
        console.warn('Không tìm thấy meta.permissions cho /admin/shipping-partners');
        return false;
      }
      const permissions = matchedRoute.meta.permissions[role] || [];
      const hasPermission = permissions.includes(action);
      console.log(`Checking permission for action "${action}" and role "${role}":`, hasPermission, 'Permissions:', permissions);
      return hasPermission;
    },
    async fetchPartners() {
      const token = localStorage.getItem('token');
      try {
        if (!token) {
          throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        }
        const params = {};
        if (this.statusFilter !== 'all') params.status = this.statusFilter;
        if (this.searchQuery) params.name = this.searchQuery;
        const response = await axios.get('/admin/shipping-partners/all', {
          headers: { Authorization: `Bearer ${token}` },
          params,
        });
        this.partners = response.data.partners.map(partner => ({
          ...partner,
          isActive: partner.status === 'active',
          isLoading: false,
        }));
        this.statuses = response.data.statuses || ['active', 'inactive'];
        this.errorMessage = '';
        this.resetLoadingStates();
      } catch (error) {
        console.error('Lỗi khi tải đối tác:', error);
        this.errorMessage = error.response?.status === 401
          ? 'Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.'
          : 'Không thể tải danh sách đối tác: ' + (error.message || 'Lỗi không xác định.');
        this.resetLoadingStates();
        if (error.response?.status === 401 || error.response?.status === 403) {
          this.$router.push('/admin/login');
        }
      }
    },
    resetLoadingStates() {
      console.log('Resetting loading states for all partners');
      this.partners = this.partners.map(partner => ({
        ...partner,
        isLoading: false,
      }));
    },
    async toggleStatus(partner) {
      console.log('Toggling status for partner:', partner.id, 'isLoading:', partner.isLoading, 'New isActive:', this.newIsActive);
      const token = localStorage.getItem('token');
      if (!token) {
        this.$toast.error('Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.');
        this.$router.push('/admin/login');
        return;
      }
      if (!this.hasPermission('update')) {
        this.$toast.error('Bạn không có quyền cập nhật trạng thái.');
        return;
      }
      if (this.newIsActive === null) {
        console.error('newIsActive is null');
        this.$toast.error('Lỗi: Trạng thái không hợp lệ.');
        return;
      }
      const newStatus = this.newIsActive ? 'active' : 'inactive';
      const originalIsActive = partner.isActive;
      partner.isLoading = true;
      try {
        await axios.put(
          `/admin/shipping-partners/${partner.id}`,
          { status: newStatus },
          { headers: { Authorization: `Bearer ${token}` } }
        );
        partner.status = newStatus;
        partner.isActive = this.newIsActive;
        this.$toast.success('Cập nhật trạng thái thành công');
      } catch (error) {
        console.error('Lỗi cập nhật trạng thái:', error);
        this.$toast.error('Cập nhật trạng thái thất bại: ' + (error.response?.data?.message || error.message));
        partner.isActive = originalIsActive;
      } finally {
        partner.isLoading = false;
        this.newIsActive = null;
        console.log('Set isLoading to false for partner:', partner.id);
      }
    },
    async handlePartnerFormSubmit(form) {
      if (!this.hasPermission(this.editingPartner ? 'update' : 'create')) {
        this.$toast.error(`Bạn không có quyền ${this.editingPartner ? 'cập nhật' : 'tạo'} đối tác.`);
        return;
      }
      const token = localStorage.getItem('token');
      try {
        let response;
        if (this.editingPartner) {
          response = await axios.put(
            `/admin/shipping-partners/${this.editingPartner.id}`,
            form,
            { headers: { Authorization: `Bearer ${token}` } }
          );
          const index = this.partners.findIndex(p => p.id === this.editingPartner.id);
          this.partners.splice(index, 1, {
            ...response.data.partner,
            isActive: response.data.partner.status === 'active',
            isLoading: false,
          });
          this.$toast.success('Cập nhật đối tác thành công');
        } else {
          response = await axios.post(
            '/admin/shipping-partners',
            form,
            { headers: { Authorization: `Bearer ${token}` } }
          );
          this.partners.push({
            ...response.data.partner,
            isActive: response.data.partner.status === 'active',
            isLoading: false,
          });
          this.$toast.success('Thêm đối tác thành công');
        }
        this.closePartnerModal();
        this.errorMessage = null;
      } catch (error) {
        console.error('Lỗi xử lý form:', error);
        if (error.response?.status === 422) {
          const errors = error.response.data.errors || {};
          let errorMessage = 'Lỗi xác thực:\n';
          for (const [field, messages] of Object.entries(errors)) {
            errorMessage += `- ${field}: ${messages.join(', ')}\n`;
          }
          this.$toast.error(errorMessage);
        } else {
          this.$toast.error('Lỗi: ' + (error.response?.data?.message || error.message));
        }
      }
    },
    async deletePartner() {
      const token = localStorage.getItem('token');
      if (!this.hasPermission('delete')) {
        this.$toast.error('Bạn không có quyền xóa đối tác.');
        return;
      }
      try {
        await axios.delete(`/admin/shipping-partners/${this.selectedPartner.id}`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.partners = this.partners.filter(p => p.id !== this.selectedPartner.id);
        this.errorMessage = '';
        this.$toast.success('Xóa đối tác thành công');
      } catch (error) {
        console.error('Lỗi xóa đối tác:', error);
        this.errorMessage = error.response?.data?.message || 'Không thể xóa đối tác.';
        this.$toast.error('Xóa đối tác thất bại: ' + (error.response?.data?.message || error.message));
      }
    },
    formatStatus(status) {
      return status === 'active' ? 'Hoạt động' : status === 'inactive' ? 'Ngừng hoạt động' : 'N/A';
    },
    formatDate(date) {
      if (!date) return 'N/A';
      return new Intl.DateTimeFormat('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
      }).format(new Date(date));
    },
    truncateUrl(url) {
      if (!url) return 'N/A';
      return url.length > 30 ? url.slice(0, 27) + '...' : url;
    },
    applySearch() {
      this.fetchPartners();
    },
    openDetailModal(partner) {
      this.selectedPartner = { ...partner };
      this.showDetailModal = true;
    },
    closeDetailModal() {
      this.showDetailModal = false;
      this.selectedPartner = null;
    },
    openAddModal() {
      if (!this.hasPermission('create')) {
        this.$toast.error('Bạn không có quyền tạo đối tác.');
        return;
      }
      this.editingPartner = null;
      this.showPartnerModal = true;
      if (this.$refs.formModal) {
        this.$refs.formModal.initializeForm({});
      }
    },
    openEditModal(partner) {
      if (!this.hasPermission('update')) {
        this.$toast.error('Bạn không có quyền sửa đối tác.');
        return;
      }
      this.editingPartner = {
        id: partner.id,
        name: partner.name || '',
        api_url: partner.api_url || '',
        status: partner.status || 'active',
      };
      this.showPartnerModal = true;
      if (this.$refs.formModal) {
        this.$refs.formModal.initializeForm(this.editingPartner);
      }
    },
    closePartnerModal() {
      this.showPartnerModal = false;
      this.editingPartner = null;
    },
    openConfirmModal(action, partner) {
      if (action === 'delete' && !this.hasPermission('delete')) {
        this.$toast.error('Bạn không có quyền xóa đối tác.');
        return;
      }
      this.confirmAction = action;
      this.selectedPartner = { ...partner };
      if (action === 'delete') {
        this.confirmTitle = 'Xác nhận xóa';
        this.confirmMessage = `Bạn có chắc chắn muốn xóa đối tác "${partner.name || 'N/A'}" không?`;
      }
      this.showConfirmModal = true;
      console.log('Confirm modal opened for delete:', { confirmAction: this.confirmAction, selectedPartner: this.selectedPartner });
    },
    openToggleConfirmModal(partner, newIsActive) {
    console.log('Opening toggle confirm modal for partner:', partner.id, 'Permission:', this.hasPermission('update'), 'New state:', newIsActive);
    if (!this.hasPermission('update')) {
      this.$toast.error('Bạn không có quyền cập nhật trạng thái.');
      return;
    }
    this.confirmAction = 'toggleStatus';
    this.selectedPartner = { ...partner };
    this.newIsActive = newIsActive;
    this.confirmTitle = 'Xác nhận thay đổi trạng thái';
    this.confirmMessage = `Bạn có chắc chắn muốn thay đổi trạng thái của "${partner.name || 'N/A'}" thành ${newIsActive ? 'Hoạt động' : 'Ngừng hoạt động'}?`;
    this.showConfirmModal = true;
  },

  async handleConfirm() {
    console.log('Handling confirm action:', this.confirmAction, 'Selected partner:', this.selectedPartner, 'New isActive:', this.newIsActive);
    try {
      if (!this.selectedPartner || !this.confirmAction) {
        console.error('Missing selectedPartner or confirmAction');
        this.$toast.error('Lỗi: Thiếu thông tin xác nhận.');
        return;
      }
      if (this.confirmAction === 'toggleStatus') {
        const partner = this.partners.find(p => p.id === this.selectedPartner.id);
        if (!partner) {
          console.error('Partner not found in partners array:', this.selectedPartner.id);
          this.$toast.error('Lỗi: Không tìm thấy đối tác.');
          return;
        }
        await this.toggleStatus(partner);
      } else if (this.confirmAction === 'delete') {
        await this.deletePartner();
      } else {
        console.error('Invalid confirm action:', this.confirmAction);
        this.$toast.error('Lỗi: Hành động không hợp lệ.');
      }
    } catch (error) {
      console.error('Error in handleConfirm:', error);
      this.$toast.error('Lỗi xác nhận: ' + (error.message || 'Lỗi không xác định.'));
    } finally {
      this.resetModal();
    }
  },

  handleCancel() {
    console.log('Handling cancel action:', this.confirmAction);
    // Không cần khôi phục trạng thái partner.isActive vì nó chưa được thay đổi
    this.resetModal();
  },

  async toggleStatus(partner) {
    console.log('Toggling status for partner:', partner.id, 'isLoading:', partner.isLoading, 'New isActive:', this.newIsActive);
    const token = localStorage.getItem('token');
    if (!token) {
      this.$toast.error('Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.');
      this.$router.push('/admin/login');
      return;
    }
    if (!this.hasPermission('update')) {
      this.$toast.error('Bạn không có quyền cập nhật trạng thái.');
      return;
    }
    if (this.newIsActive === null) {
      console.error('newIsActive is null');
      this.$toast.error('Lỗi: Trạng thái không hợp lệ.');
      return;
    }
    const newStatus = this.newIsActive ? 'active' : 'inactive';
    partner.isLoading = true;
    try {
      await axios.put(
        `/admin/shipping-partners/${partner.id}`,
        { status: newStatus },
        { headers: { Authorization: `Bearer ${token}` } }
      );
      partner.status = newStatus;
      partner.isActive = this.newIsActive; // Chỉ cập nhật sau khi API thành công
      this.$toast.success('Cập nhật trạng thái thành công');
    } catch (error) {
      console.error('Lỗi cập nhật trạng thái:', error);
      this.$toast.error('Cập nhật trạng thái thất bại: ' + (error.response?.data?.message || error.message));
    } finally {
      partner.isLoading = false;
      this.newIsActive = null;
    }
  },

  resetModal() {
    console.log('Resetting modal state');
    this.showConfirmModal = false;
    this.confirmAction = null;
    this.selectedPartner = null;
    this.newIsActive = null;
    this.confirmTitle = 'Xác nhận';
    this.confirmMessage = '';
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