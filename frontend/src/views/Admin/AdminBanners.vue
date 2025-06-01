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
    <GenericDetailsModal
      :show="showDetailModal"
      :data="selectedBanner"
      :fields="bannerFields"
      title="Chi tiết banner"
      @close="closeDetailModal"
    />

    <!-- Modal for Adding/Editing/Deleting Banner -->
    <FormModal
      v-if="showBannerModal"
      :show="showBannerModal"
      :title="modalTitle"
      :fields="modalFields"
      :initialData="modalInitialData"
      :isEdit="modalMode === 'edit'"
      @close="closeBannerModal"
      @submit="handleBannerFormSubmit"
      ref="formModal"
    />
  </div>
</template>

<script>
import axios from 'axios';
import FilterSearch from './component/AdminFilterSearch.vue';
import GenericDetailsModal from './component/GenericDetailsModal.vue';
import FormModal from './component/FormModal.vue';

export default {
  name: 'AdminBanners',
  components: { FilterSearch, GenericDetailsModal, FormModal },
  data() {
    return {
      banners: [],
      positions: [],
      errorMessage: '',
      searchQuery: '',
      positionFilter: 'all',
      showDetailModal: false,
      showBannerModal: false,
      selectedBanner: null,
      modalMode: null,
      modalTitle: '',
      modalFields: [],
      modalInitialData: {},
      bannerFields: [
        { label: 'Tiêu đề', key: 'title', type: 'text' },
        { label: 'Hình ảnh', key: 'img_url', type: 'image' },
        { label: 'Liên kết', key: 'link_url', type: 'link' },
        {
          label: 'Vị trí',
          key: 'position',
          type: 'custom',
          customFormat: (value) => this.formatPosition(value),
        },
        { label: 'Ngày bắt đầu', key: 'start_date', type: 'date' },
        { label: 'Ngày kết thúc', key: 'end_date', type: 'date' },
        { label: 'Ngày tạo', key: 'created_at', type: 'date' },
      ],
    };
  },
  computed: {
    bannerFormFields() {
      return [
        {
          name: 'title',
          label: 'Tiêu đề',
          type: 'text',
          placeholder: 'Nhập tiêu đề banner',
          required: true,
          rules: [
            {
              validator: (value) => value.trim().length > 0,
              message: 'Tiêu đề không được để trống',
            },
          ],
        },
        {
          name: 'img_url',
          label: 'URL hình ảnh',
          type: 'url',
          placeholder: 'Nhập URL hình ảnh (http:// hoặc https://)',
          required: true,
          rules: [
            {
              validator: (value) => /^https?:\/\/[^\s/$.?#].[^\s]*$/.test(value),
              message: 'URL hình ảnh không hợp lệ',
            },
          ],
        },
        {
          name: 'link_url',
          label: 'URL liên kết',
          type: 'url',
          placeholder: 'Nhập URL liên kết (http:// hoặc https://)',
          required: false,
          rules: [
            {
              validator: (value) => !value || /^https?:\/\/[^\s/$.?#].[^\s]*$/.test(value),
              message: 'URL liên kết không hợp lệ',
            },
          ],
        },
        {
          name: 'position',
          label: 'Vị trí',
          type: 'select',
          placeholder: 'Chọn vị trí',
          required: true,
          options: this.positions.map((pos) => ({
            value: pos,
            label: this.formatPosition(pos),
          })),
          defaultValue: this.positions.length > 0 ? this.positions[0] : '',
        },
        {
          name: 'start_date',
          label: 'Ngày bắt đầu',
          type: 'datetime-local',
          required: true,
          rules: [
            {
              validator: (value) => value && new Date(value) <= new Date(this.modalInitialData.end_date || '9999-12-31'),
              message: 'Ngày bắt đầu phải trước ngày kết thúc',
            },
          ],
        },
        {
          name: 'end_date',
          label: 'Ngày kết thúc',
          type: 'datetime-local',
          required: true,
          rules: [
            {
              validator: (value) => value && new Date(value) >= new Date(this.modalInitialData.start_date || '1000-01-01'),
              message: 'Ngày kết thúc phải sau ngày bắt đầu',
            },
          ],
        },
      ];
    },
    deleteFormFields() {
      return [
        {
          name: 'confirm',
          label: `Bạn có chắc muốn xóa banner "${this.selectedBanner?.title || 'N/A'}" không?`,
          type: 'text',
          placeholder: '',
          required: false,
          disabled: true,
        },
      ];
    },
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
    async handleBannerFormSubmit(form) {
      console.log('Form data:', form, 'Mode:', this.modalMode);
      const token = localStorage.getItem('token');
      if (!token) {
        alert('Vui lòng đăng nhập lại.');
        this.$router.push('/admin/login');
        return;
      }
      try {
        let response;
        if (this.modalMode === 'add') {
          response = await axios.post(
            'http://localhost:8000/api/admin/banners',
            form,
            { headers: { Authorization: `Bearer ${token}` } }
          );
          this.banners.push(response.data.banner);
          alert('Thêm banner thành công');
        } else if (this.modalMode === 'edit') {
          response = await axios.put(
            `http://localhost:8000/api/admin/banners/${this.selectedBanner.id}`,
            form,
            { headers: { Authorization: `Bearer ${token}` } }
          );
          const index = this.banners.findIndex(b => b.id === this.selectedBanner.id);
          this.banners.splice(index, 1, response.data.banner);
          alert('Cập nhật banner thành công');
        } else if (this.modalMode === 'delete') {
          await axios.delete(
            `http://localhost:8000/api/admin/banners/${this.selectedBanner.id}`,
            { headers: { Authorization: `Bearer ${token}` } }
          );
          this.banners = this.banners.filter(b => b.id !== this.selectedBanner.id);
          alert('Xóa banner thành công');
        }
        this.closeBannerModal();
        this.errorMessage = '';
      } catch (error) {
        console.error('Error handling banner form:', error.response || error);
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
    formatPosition(position) {
      const positions = {
        homepage: 'Trang chủ',
        sidebar: 'Thanh bên',
        footer: 'Chân trang',
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
      this.modalMode = 'add';
      this.modalTitle = 'Thêm banner';
      this.modalFields = this.bannerFormFields;
      this.modalInitialData = {
        position: this.positions.length > 0 ? this.positions[0] : '',
      };
      this.showBannerModal = true;
      if (this.$refs.formModal) {
        this.$refs.formModal.initializeForm(this.modalInitialData);
      }
    },
    openEditModal(banner) {
      this.modalMode = 'edit';
      this.modalTitle = 'Sửa banner';
      this.modalFields = this.bannerFormFields;
      this.selectedBanner = { ...banner };
      this.modalInitialData = {
        id: banner.id,
        title: banner.title || '',
        img_url: banner.img_url || '',
        link_url: banner.link_url || '',
        position: banner.position || this.positions[0] || '',
        start_date: banner.start_date ? new Date(banner.start_date).toISOString().slice(0, 16) : '',
        end_date: banner.end_date ? new Date(banner.end_date).toISOString().slice(0, 16) : '',
      };
      this.showBannerModal = true;
      if (this.$refs.formModal) {
        this.$refs.formModal.initializeForm(this.modalInitialData);
      }
    },
    openDeleteModal(banner) {
      this.modalMode = 'delete';
      this.modalTitle = 'Xác nhận xóa';
      this.modalFields = this.deleteFormFields;
      this.selectedBanner = { ...banner };
      this.modalInitialData = { confirm: '' };
      this.showBannerModal = true;
      if (this.$refs.formModal) {
        this.$refs.formModal.initializeForm(this.modalInitialData);
      }
    },
    closeBannerModal() {
      this.showBannerModal = false;
      this.modalMode = null;
      this.modalTitle = '';
      this.modalFields = [];
      this.modalInitialData = {};
      this.selectedBanner = null;
    },
  },
};
</script>