<template>
  <div class="min-h-screen">
    <div class="mx-auto">
      <div class="flex items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Quản lý banner</h2>
      </div>
      <div class="flex justify-between items-center mb-6">
        <FilterSearch
          :filters="filters"
          :searchPlaceholder="'Tìm theo tiêu đề banner...'"
          v-model:currentFilter="positionFilter"
          v-model:searchQuery="searchQuery"
          @search="applySearch"
        />
        <button
          @click="openAddModal"
          class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
        >
          Thêm banner
        </button>
      </div>
      
      <p v-if="errorMessage" class="text-center text-red-500 mt-4">
        {{ errorMessage }}
      </p>
      <p v-else-if="filteredBanners.length === 0" class="text-center text-gray-500 mt-4">
        Không tìm thấy banner nào.
      </p>
      <table v-else class="table-auto w-full border bg-white shadow-sm rounded-lg overflow-hidden">
        <thead class="bg-gray-200">
          <tr>
            <th class="p-3 border-b text-left text-gray-700">STT</th>
            <th class="p-3 border-b text-left text-gray-700">Tiêu đề</th>
            <th class="p-3 border-b text-left text-gray-700">Hình ảnh</th>
            <th class="p-3 border-b text-left text-gray-700">Liên kết</th>
            <th class="p-3 border-b text-left text-gray-700">Vị trí</th>
            <th class="p-3 border-b text-left text-gray-700">Ngày bắt đầu</th>
            <th class="p-3 border-b text-left text-gray-700">Ngày kết thúc</th>
            <th class="p-3 border-b text-left text-gray-700">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(banner, index) in filteredBanners" :key="banner.id" class="hover:bg-gray-50">
            <td class="p-3 border-b">{{ index + 1 }}</td>
            <td class="p-3 border-b">{{ banner.title || 'N/A' }}</td>
            <td class="p-3 border-b">
              <img :src="banner.img_url" alt="Banner" class="w-16 h-16 object-cover rounded" @error="handleImageError($event, banner)" />
            </td>
            <td class="p-3 border-b">
              <a v-if="banner.link_url" :href="banner.link_url" target="_blank" class="text-blue-600 hover:underline">
                {{ truncateUrl(banner.link_url) }}
              </a>
              <span v-else>N/A</span>
            </td>
            <td class="p-3 border-b">{{ formatPosition(banner.position) }}</td>
            <td class="p-3 border-b">{{ formatDate(banner.start_date) }}</td>
            <td class="p-3 border-b">{{ formatDate(banner.end_date) }}</td>
            <td class="p-3 border-b text-center space-x-2">
              <button
                @click="openDetailModal(banner)"
                class="text-blue-600 hover:underline"
              >
                Xem chi tiết
              </button>
              <button
                @click="openEditModal(banner)"
                class="text-green-600 hover:underline"
              >
                Sửa
              </button>
              <button
                @click="openDeleteModal(banner)"
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
        <h3 class="text-xl font-bold text-gray-800 mb-4">Chi tiết banner</h3>
        <div v-if="selectedBanner" class="space-y-4">
          <div>
            <h4 class="text-md font-semibold text-gray-700">Tiêu đề</h4>
            <p class="text-gray-600">{{ selectedBanner.title || 'N/A' }}</p>
          </div>
          <div>
            <h4 class="text-md font-semibold text-gray-700">Hình ảnh</h4>
            <img :src="selectedBanner.img_url" alt="Banner" class="w-full max-h-64 object-contain rounded" @error="handleImageError($event, selectedBanner)" />
          </div>
          <div>
            <h4 class="text-md font-semibold text-gray-700">Liên kết</h4>
            <a v-if="selectedBanner.link_url" :href="selectedBanner.link_url" target="_blank" class="text-blue-600 hover:underline">
              {{ selectedBanner.link_url }}
            </a>
            <p v-else class="text-gray-600">N/A</p>
          </div>
          <div>
            <h4 class="text-md font-semibold text-gray-700">Vị trí</h4>
            <p class="text-gray-600">{{ formatPosition(selectedBanner.position) }}</p>
          </div>
          <div>
            <h4 class="text-md font-semibold text-gray-700">Ngày bắt đầu</h4>
            <p class="text-gray-600">{{ formatDate(selectedBanner.start_date) }}</p>
          </div>
          <div>
            <h4 class="text-md font-semibold text-gray-700">Ngày kết thúc</h4>
            <p class="text-gray-600">{{ formatDate(selectedBanner.end_date) }}</p>
          </div>
          <div>
            <h4 class="text-md font-semibold text-gray-700">Ngày tạo</h4>
            <p class="text-gray-600">{{ formatDate(selectedBanner.created_at) }}</p>
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

    <!-- Modal for Adding Banner -->
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
        <h3 class="text-xl font-bold text-gray-800 mb-4">Thêm banner</h3>
        <form @submit.prevent="addBanner">
          <div class="space-y-4">
            <div>
              <label class="block text-gray-700">Tiêu đề</label>
              <input
                v-model="newBanner.title"
                type="text"
                required
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-gray-700">URL hình ảnh</label>
              <input
                v-model="newBanner.img_url"
                type="url"
                required
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-gray-700">URL liên kết</label>
              <input
                v-model="newBanner.link_url"
                type="url"
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-gray-700">Vị trí</label>
              <select
                v-model="newBanner.position"
                required
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option v-for="position in positions" :key="position" :value="position">
                  {{ formatPosition(position) }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-gray-700">Ngày bắt đầu</label>
              <input
                v-model="newBanner.start_date"
                type="datetime-local"
                required
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-gray-700">Ngày kết thúc</label>
              <input
                v-model="newBanner.end_date"
                type="datetime-local"
                required
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
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

    <!-- Modal for Editing Banner -->
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
        <h3 class="text-xl font-bold text-gray-800 mb-4">Sửa banner</h3>
        <form @submit.prevent="updateBanner">
          <div class="space-y-4">
            <div>
              <label class="block text-gray-700">Tiêu đề</label>
              <input
                v-model="editBanner.title"
                type="text"
                required
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-gray-700">URL hình ảnh</label>
              <input
                v-model="editBanner.img_url"
                type="url"
                required
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-gray-700">URL liên kết</label>
              <input
                v-model="editBanner.link_url"
                type="url"
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-gray-700">Vị trí</label>
              <select
                v-model="editBanner.position"
                required
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option v-for="position in positions" :key="position" :value="position">
                  {{ formatPosition(position) }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-gray-700">Ngày bắt đầu</label>
              <input
                v-model="editBanner.start_date"
                type="datetime-local"
                required
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-gray-700">Ngày kết thúc</label>
              <input
                v-model="editBanner.end_date"
                type="datetime-local"
                required
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
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

    <!-- Modal for Deleting Banner -->
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
          Bạn có chắc muốn xóa banner "{{ selectedBanner?.title }}" không?
        </p>
        <div class="flex justify-end space-x-2">
          <button
            @click="closeDeleteModal"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
          >
            Hủy
          </button>
          <button
            @click="deleteBanner"
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
  name: 'AdminBanners',
  components: { FilterSearch },
  data() {
    return {
      banners: [],
      positions: [],
      errorMessage: '',
      searchQuery: '',
      positionFilter: 'all',
      showDetailModal: false,
      showAddModal: false,
      showEditModal: false,
      showDeleteModal: false,
      selectedBanner: null,
      newBanner: {
        title: '',
        img_url: '',
        link_url: '',
        position: '',
        start_date: '',
        end_date: '',
      },
      editBanner: {
        id: null,
        title: '',
        img_url: '',
        link_url: '',
        position: '',
        start_date: '',
        end_date: '',
      },
    };
  },
  computed: {
    filters() {
      return [
        { key: 'all', label: 'Tất cả vị trí', count: this.banners.length },
        ...this.positions.map(position => ({
          key: position,
          label: this.formatPosition(position),
          count: this.banners.filter(b => b.position === position).length,
        })),
      ];
    },
    filteredBanners() {
      const q = this.searchQuery.toLowerCase().trim();
      return this.banners.filter(banner => {
        const matchesSearch = !q || (banner.title || '').toLowerCase().includes(q);
        const matchesPosition = this.positionFilter === 'all' || banner.position === this.positionFilter;
        return matchesSearch && matchesPosition;
      });
    },
  },
  async mounted() {
    await this.fetchBanners();
  },
  methods: {
    async fetchBanners() {
      const token = localStorage.getItem('token');
      try {
        if (!token) {
          throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        }
        const params = {};
        if (this.positionFilter !== 'all') params.position = this.positionFilter;
        if (this.searchQuery) params.title = this.searchQuery;
        console.log('Fetching banners with:', { token, params });
        const response = await axios.get('http://localhost:8000/api/admin/banners', {
          headers: { Authorization: `Bearer ${token}` },
          params,
        });
        console.log('Response:', response.data);
        this.banners = response.data.banners;
        this.positions = response.data.positions;
        this.errorMessage = '';
        // Set default position for newBanner if positions are available
        if (this.positions.length > 0) {
          this.newBanner.position = this.positions[0];
          this.editBanner.position = this.positions[0];
        }
      } catch (error) {
        console.error('Error fetching banners:', error.response || error);
        this.errorMessage = error.response?.status === 401
          ? 'Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.'
          : 'Không thể tải danh sách banner. Vui lòng thử lại sau.';
        if (error.response?.status === 401) {
          this.$router.push('/admin/login');
        }
      }
    },
    async addBanner() {
      const token = localStorage.getItem('token');
      try {
        const bannerData = { ...this.newBanner };
        const response = await axios.post('http://localhost:8000/api/admin/banners', bannerData, {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.banners.push(response.data.banner);
        this.closeAddModal();
        this.errorMessage = '';
      } catch (error) {
        console.error('Error adding banner:', error.response || error);
        this.errorMessage = error.response?.data?.error || 'Không thể thêm banner.';
      }
    },
    async updateBanner() {
      const token = localStorage.getItem('token');
      try {
        const bannerData = { ...this.editBanner };
        delete bannerData.id;
        const response = await axios.put(`http://localhost:8000/api/admin/banners/${this.editBanner.id}`, bannerData, {
          headers: { Authorization: `Bearer ${token}` },
        });
        const index = this.banners.findIndex(b => b.id === this.editBanner.id);
        this.banners.splice(index, 1, response.data.banner);
        this.closeEditModal();
        this.errorMessage = '';
      } catch (error) {
        console.error('Error updating banner:', error.response || error);
        this.errorMessage = error.response?.data?.error || 'Không thể sửa banner.';
      }
    },
    async deleteBanner() {
      const token = localStorage.getItem('token');
      try {
        await axios.delete(`http://localhost:8000/api/admin/banners/${this.selectedBanner.id}`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.banners = this.banners.filter(b => b.id !== this.selectedBanner.id);
        this.closeDeleteModal();
        this.errorMessage = '';
      } catch (error) {
        console.error('Error deleting banner:', error.response || error);
        this.errorMessage = error.response?.data?.error || 'Không thể xóa banner.';
      }
    },
    formatPosition(position) {
      // Map API position values to Vietnamese display names
      const positions = {
        homepage: 'Trang chủ',
        sidebar: 'Thanh bên',
        footer: 'Chân trang',
        // Add more mappings as needed based on banner_display_locations
      };
      return positions[position] || position || 'N/A';
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
    handleImageError(event, banner) {
      event.target.src = 'https://via.placeholder.com/150?text=Image+Not+Found';
      banner.img_url = event.target.src;
    },
    applySearch() {
      this.fetchBanners();
    },
    openDetailModal(banner) {
      this.selectedBanner = { ...banner };
      this.showDetailModal = true;
    },
    closeDetailModal() {
      this.showDetailModal = false;
      this.selectedBanner = null;
    },
    openAddModal() {
      this.newBanner = {
        title: '',
        img_url: '',
        link_url: '',
        position: this.positions.length > 0 ? this.positions[0] : '',
        start_date: '',
        end_date: '',
      };
      this.showAddModal = true;
    },
    closeAddModal() {
      this.showAddModal = false;
    },
    openEditModal(banner) {
      this.editBanner = {
        id: banner.id,
        title: banner.title,
        img_url: banner.img_url,
        link_url: banner.link_url,
        position: banner.position,
        start_date: banner.start_date ? new Date(banner.start_date).toISOString().slice(0, 16) : '',
        end_date: banner.end_date ? new Date(banner.end_date).toISOString().slice(0, 16) : '',
      };
      this.showEditModal = true;
    },
    closeEditModal() {
      this.showEditModal = false;
      this.editBanner = {
        id: null,
        title: '',
        img_url: '',
        link_url: '',
        position: this.positions.length > 0 ? this.positions[0] : '',
        start_date: '',
        end_date: '',
      };
    },
    openDeleteModal(banner) {
      this.selectedBanner = { ...banner };
      this.showDeleteModal = true;
    },
    closeDeleteModal() {
      this.showDeleteModal = false;
      this.selectedBanner = null;
    },
  },
};
</script>