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
              <a :href="partner.api_url" target="_blank" class="text-blue-600 hover:underline">
                {{ partner.api_url }}
              </a>
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
                @click="openDeleteModal(partner)"
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
    <div
      v-if="showDetailModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="closeDetailModal"
    >
      <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6 relative">
        <button
          @click="closeDetailModal"
          class="absolute top-4 right-4 text-gray-500 hover:text-gray-700"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <h3 class="text-xl font-bold text-gray-800 mb-4">Chi tiết đối tác vận chuyển</h3>
        <div v-if="selectedPartner" class="space-y-4">
          <div>
            <h4 class="text-md font-semibold text-gray-700">Tên</h4>
            <p class="text-gray-600">{{ selectedPartner.name || 'N/A' }}</p>
          </div>
          <div>
            <h4 class="text-md font-semibold text-gray-700">API URL</h4>
            <a :href="selectedPartner.api_url" target="_blank" class="text-blue-600 hover:underline">
              {{ selectedPartner.api_url || 'N/A' }}
            </a>
          </div>
          <div>
            <h4 class="text-md font-semibold text-gray-700">Trạng thái</h4>
            <p class="text-gray-600">{{ formatStatus(selectedPartner.status) }}</p>
          </div>
          <div>
            <h4 class="text-md font-semibold text-gray-700">Cửa hàng liên kết</h4>
            <p v-if="selectedPartner.shops && selectedPartner.shops.length > 0" class="text-gray-600">
              {{ selectedPartner.shops.join(', ') }}
            </p>
            <p v-else class="text-gray-600">Không có cửa hàng</p>
          </div>
          <div>
            <h4 class="text-md font-semibold text-gray-700">Ngày tạo</h4>
            <p class="text-gray-600">{{ formatDate(selectedPartner.created_at) }}</p>
          </div>
        </div>
        <div class="mt-6 flex justify-end">
          <button
            @click="closeDetailModal"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
          >
            Đóng
          </button>
        </div>
      </div>
    </div>

    <!-- Modal for Adding Partner -->
    <div
      v-if="showAddModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="closeAddModal"
    >
      <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full p-6 relative">
        <button
          @click="closeAddModal"
          class="absolute top-4 right-4 text-gray-500 hover:text-gray-700"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <h3 class="text-xl font-bold text-gray-800 mb-4">Thêm đối tác vận chuyển</h3>
        <form @submit.prevent="addPartner">
          <div class="space-y-4">
            <div>
              <label class="block text-gray-700">Tên</label>
              <input
                v-model="newPartner.name"
                type="text"
                required
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-gray-700">API URL</label>
              <input
                v-model="newPartner.api_url"
                type="url"
                required
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-gray-700">Trạng thái</label>
              <select
                v-model="newPartner.status"
                required
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="active">Hoạt động</option>
                <option value="inactive">Ngừng hoạt động</option>
              </select>
            </div>
          </div>
          <div class="mt-6 flex justify-end space-x-2">
            <button
              type="button"
              @click="closeAddModal"
              class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
            >
              Hủy
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
            >
              Thêm
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal for Editing Partner -->
    <div
      v-if="showEditModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="closeEditModal"
    >
      <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full p-6 relative">
        <button
          @click="closeEditModal"
          class="absolute top-4 right-4 text-gray-500 hover:text-gray-700"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <h3 class="text-xl font-bold text-gray-800 mb-4">Sửa đối tác vận chuyển</h3>
        <form @submit.prevent="updatePartner">
          <div class="space-y-4">
            <div>
              <label class="block text-gray-700">Tên</label>
              <input
                v-model="editPartner.name"
                type="text"
                required
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-gray-700">API URL</label>
              <input
                v-model="editPartner.api_url"
                type="url"
                required
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-gray-700">Trạng thái</label>
              <select
                v-model="editPartner.status"
                required
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="active">Hoạt động</option>
                <option value="inactive">Ngừng hoạt động</option>
              </select>
            </div>
          </div>
          <div class="mt-6 flex justify-end space-x-2">
            <button
              type="button"
              @click="closeEditModal"
              class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
            >
              Hủy
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
            >
              Lưu
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal for Deleting Partner -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="closeDeleteModal"
    >
      <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
        <button
          @click="closeDeleteModal"
          class="absolute top-4 right-4 text-gray-500 hover:text-gray-700"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <h3 class="text-xl font-bold text-gray-800 mb-4">Xác nhận xóa</h3>
        <p class="text-gray-600 mb-4">
          Bạn có chắc muốn xóa đối tác "{{ selectedPartner?.name }}" không?
        </p>
        <div class="flex justify-end space-x-2">
          <button
            @click="closeDeleteModal"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
          >
            Hủy
          </button>
          <button
            @click="deletePartner"
            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
          >
            Xóa
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import FilterSearch from './component/AdminFilterSearch.vue';

export default {
  name: 'AdminShippingPartners',
  components: { FilterSearch },
  data() {
    return {
      partners: [],
      statuses: [],
      errorMessage: '',
      searchQuery: '',
      statusFilter: 'all',
      showDetailModal: false,
      showAddModal: false,
      showEditModal: false,
      showDeleteModal: false,
      selectedPartner: null,
      newPartner: { name: '', api_url: '', status: 'active' },
      editPartner: { id: null, name: '', api_url: '', status: 'active' },
    };
  },
  computed: {
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
    async addPartner() {
      const token = localStorage.getItem('token');
      try {
        const response = await axios.post('http://localhost:8000/api/admin/shipping-partners', this.newPartner, {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.partners.push(response.data.partner);
        this.closeAddModal();
        this.errorMessage = '';
      } catch (error) {
        console.error('Error adding shipping partner:', error.response || error);
        this.errorMessage = error.response?.data?.error || 'Không thể thêm đối tác.';
      }
    },
    async updatePartner() {
      const token = localStorage.getItem('token');
      try {
        const response = await axios.put(`http://localhost:8000/api/admin/shipping-partners/${this.editPartner.id}`, this.editPartner, {
          headers: { Authorization: `Bearer ${token}` },
        });
        const index = this.partners.findIndex(p => p.id === this.editPartner.id);
        this.partners.splice(index, 1, response.data.partner);
        this.closeEditModal();
        this.errorMessage = '';
      } catch (error) {
        console.error('Error updating shipping partner:', error.response || error);
        this.errorMessage = error.response?.data?.error || 'Không thể sửa đối tác.';
      }
    },
    async deletePartner() {
      const token = localStorage.getItem('token');
      try {
        await axios.delete(`http://localhost:8000/api/admin/shipping-partners/${this.selectedPartner.id}`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.partners = this.partners.filter(p => p.id !== this.selectedPartner.id);
        this.closeDeleteModal();
        this.errorMessage = '';
      } catch (error) {
        console.error('Error deleting shipping partner:', error.response || error);
        this.errorMessage = error.response?.data?.error || 'Không thể xóa đối tác.';
      }
    },
    formatStatus(status) {
      return status === 'active' ? 'Hoạt động' : 'Ngừng hoạt động';
    },
    formatDate(date) {
      if (!date) return 'N/A';
      return new Date(date).toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
      });
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
      this.newPartner = { name: '', api_url: '', status: 'active' };
      this.showAddModal = true;
    },
    closeAddModal() {
      this.showAddModal = false;
    },
    openEditModal(partner) {
      this.editPartner = { ...partner };
      this.showEditModal = true;
    },
    closeEditModal() {
      this.showEditModal = false;
      this.editPartner = { id: null, name: '', api_url: '', status: 'active' };
    },
    openDeleteModal(partner) {
      this.selectedPartner = { ...partner };
      this.showDeleteModal = true;
    },
    closeDeleteModal() {
      this.showDeleteModal = false;
      this.selectedPartner = null;
    },
  },
};
</script>