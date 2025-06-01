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
          v-model:currentFilter="statusFilter"
          v-model:searchQuery="searchQuery"
          @search="applySearch"
        />
        <button
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
            <td class="p-3 border-b">{{ formatStatus(partner.status) }}</td>
            <td class="p-3 border-b text-center space-x-2">
              <button
                @click="openDetailModal(partner)"
                class="text-blue-600 hover:underline"
              >
                Xem chi tiết
              </button>
              <button
                @click="openEditModal(partner)"
                class="text-green-600 hover:underline"
              >
                Sửa
              </button>
              <button
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
import FilterSearch from './component/AdminFilterSearch.vue';
import ConfirmModal from './component/AdminConfirmModal.vue';
import GenericDetailsModal from './component/GenericDetailsModal.vue';
import FormModal from './component/FormModal.vue';

export default {
  name: 'AdminShippingPartners',
  components: { FilterSearch, ConfirmModal, GenericDetailsModal, FormModal },
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
    await this.fetchPartners();
  },
  methods: {
    async fetchPartners() {
      const token = localStorage.getItem('token');
      try {
        if (!token) {
          throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        }
        const params = {};
        if (this.statusFilter !== 'all') params.status = this.statusFilter;
        if (this.searchQuery) params.name = this.searchQuery;
        const response = await axios.get('http://localhost:8000/api/admin/shipping-partners/all', {
          headers: { Authorization: `Bearer ${token}` },
          params,
        });
        this.partners = response.data.partners;
        this.statuses = response.data.statuses;
        this.errorMessage = '';
      } catch (error) {
        console.error('Error fetching shipping partners:', error.response || error);
        this.errorMessage = error.response?.status === 401
          ? 'Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.'
          : 'Không thể tải danh sách đối tác. Vui lòng thử lại sau.';
        if (error.response?.status === 401) {
          this.$router.push('/admin/login');
        }
      }
    },
    async handlePartnerFormSubmit(form) {
      console.log('Form data:', form);
      const token = localStorage.getItem('token');
      if (!token) {
        alert('Vui lòng đăng nhập lại.');
        this.$router.push('/admin/login');
        return;
      }
      try {
        let response;
        if (this.editingPartner) {
          // Update partner
          response = await axios.put(
            `http://localhost:8000/api/admin/shipping-partners/${this.editingPartner.id}`,
            form,
            { headers: { Authorization: `Bearer ${token}` } }
          );
          const index = this.partners.findIndex(p => p.id === this.editingPartner.id);
          this.partners.splice(index, 1, response.data.partner);
          alert('Cập nhật đối tác thành công');
        } else {
          // Add partner
          response = await axios.post(
            'http://localhost:8000/api/admin/shipping-partners',
            form,
            { headers: { Authorization: `Bearer ${token}` } }
          );
          this.partners.push(response.data.partner);
          alert('Thêm đối tác thành công');
        }
        this.closePartnerModal();
        this.errorMessage = '';
      } catch (error) {
        console.error('Error handling partner form:', error.response || error);
        if (error.response?.status === 422) {
          const errors = error.response.data.details || error.response.data.errors;
          let errorMessage = 'Lỗi xác thực:\n';
          for (const field in errors) {
            errorMessage += `- ${field}: ${errors[field].join(', ')}\n`;
          }
          alert(errorMessage);
        } else {
          alert('Lỗi: ' + (error.response?.data?.error || error.message));
        }
      }
    },
    async deletePartner() {
      const token = localStorage.getItem('token');
      try {
        await axios.delete(`http://localhost:8000/api/admin/shipping-partners/${this.selectedPartner.id}`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.partners = this.partners.filter(p => p.id !== this.selectedPartner.id);
        this.errorMessage = '';
      } catch (error) {
        console.error('Error deleting shipping partner:', error.response || error);
        this.errorMessage = error.response?.data?.error || 'Không thể xóa đối tác.';
      }
    },
    formatStatus(status) {
      return status === 'active' ? 'Hoạt động' : status === 'inactive' ? 'Ngừng hoạt động' : 'N/A';
    },
    formatDate(date) {
      if (!date) return 'N/A';
      return new Date(date).toLocaleString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
      });
    },
    truncateUrl(url) {
      if (!url) return 'N/A';
      return url.length > 30 ? url.substring(0, 27) + '...' : url;
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
      this.editingPartner = null;
      this.showPartnerModal = true;
      if (this.$refs.formModal) {
        this.$refs.formModal.initializeForm({});
      }
    },
    openEditModal(partner) {
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
      this.confirmAction = action;
      this.selectedPartner = { ...partner };
      if (action === 'delete') {
        this.confirmTitle = 'Xác nhận xóa';
        this.confirmMessage = `Bạn có chắc chắn muốn xóa đối tác "${partner.name || 'N/A'}" không?`;
      }
      this.showConfirmModal = true;
    },
    async handleConfirm() {
      if (this.confirmAction === 'delete') {
        await this.deletePartner();
      }
      this.resetModal();
    },
    handleCancel() {
      this.resetModal();
    },
    resetModal() {
      this.showConfirmModal = false;
      this.confirmAction = null;
      this.selectedPartner = null;
      this.confirmTitle = 'Xác nhận';
      this.confirmMessage = '';
    },
  },
};
</script>