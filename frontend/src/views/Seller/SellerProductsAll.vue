<template>
  <div class="p-8 space-y-6">
    <h1 class="text-2xl font-bold">Quản lý Sản phẩm</h1>
    <!-- Tích hợp FilterSearch component -->
    <FilterSearch
      :filters="filters"
      :search-placeholder="'Tìm kiếm sản phẩm...'"
      v-model:currentFilter="currentFilter"
      v-model:searchQuery="search"
      @search="debouncedApplySearch"
    />
    <div class="overflow-x-auto">
      <p v-if="filteredProducts.length === 0" class="text-center text-gray-500">Không tìm thấy sản phẩm nào.</p>
      <table v-else class="min-w-full table-auto border border-gray-300 eds-table">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-4 py-2 border-b" style="width: 50px;">
              <div class="eds-table__cell">
                <span class="eds-table__cell-label">Chọn</span>
              </div>
            </th>
            <th class="px-4 py-2 border-b" style="width: 1050px;">
              <div class="eds-table__cell">
                <div class="product-variation-header flex">
                  <div class="list-header-item" style="width: 350px; padding: 0.5rem;">
                    <span class="list-header-item-label">Tên sản phẩm</span>
                  </div>
                  <div class="list-header-item" style="width: 150px; padding: 0.5rem; text-align: center;">
                    <span class="list-header-item-label">Doanh số</span>
                  </div>
                  <div class="list-header-item" style="width: 150px; padding: 0.5rem; text-align: center;">
                    <span class="list-header-item-label">Giá</span>
                  </div>
                  <div class="list-header-item" style="width: 150px; padding: 0.5rem; text-align: center;">
                    <span class="list-header-item-label">Kho hàng</span>
                  </div>
                  <div class="list-header-item" style="width: 150px; padding: 0.5rem; text-align: center;">
                    <span class="list-header-item-label">Trạng thái</span>
                  </div>
                  <div class="list-header-item" style="width: 100px; padding: 0.5rem; text-align: center;">
                    <span class="list-header-item-label">Hành động</span>
                  </div>
                </div>
              </div>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in filteredProducts" :key="product.id" class="hover:bg-gray-50 transition">
            <td class="px-4 py-2 border-b text-center align-middle" style="width: 50px;">
              <input type="checkbox" :name="'product_' + product.id" :value="product.id" class="eds-checkbox__input" v-model="selectedProducts">
            </td>
            <td class="px-4 py-2 border-b" style="width: 1050px;">
              <table class="w-full border-collapse" style="table-layout: fixed; width: 1050px;">
                <tr class="product-header">
                  <td style="width: 350px; padding: 0.5rem;">
                    <div class="flex items-center">
                      <img :src="getImageUrl(product.thumbnail)" :alt="product.name || 'Product'" class="w-16 h-16 mr-4 object-cover" @error="handleImageError($event, product.thumbnail)">
                      <div>
                        <a :href="'/portal/product/' + product.id" class="text-blue-600 hover:underline product-name-wrap" target="_blank">
                          {{ product.name || 'N/A' }}
                        </a>
                        <p class="text-sm text-gray-600">ID: {{ product.id }}</p>
                      </div>
                    </div>
                  </td>
                  <td style="width: 150px; padding: 0.5rem; text-align: center;">
                    <p class="text-sm text-gray-600">Tổng: {{ product.total_sales || 0 }}</p>
                  </td>
                  <td style="width: 150px; padding: 0.5rem; text-align: center;">
                    <p class="text-sm text-gray-600">
                      {{ product.variants.length > 1 ? `₫${formatCurrency(product.price_min)} - ₫${formatCurrency(product.price_max)}` : `₫${formatCurrency(product.price_min || 0)}` }}
                    </p>
                  </td>
                  <td style="width: 150px; padding: 0.5rem; text-align: center;">
                    <p class="text-sm text-gray-600">Tổng: {{ product.total_stock || 0 }}</p>
                  </td>
                  <td style="width: 150px; padding: 0.5rem; text-align: center;">
                    <p class="text-sm text-gray-600">{{ statusText[product.status] || 'N/A' }}</p>
                  </td>
                  <td style="width: 100px; padding: 0.5rem; text-align: center;">
                    <button @click="editProduct(product.id)" class="text-blue-500 hover:underline">Sửa</button>
                    <button @click="deleteProduct(product.id)" class="text-red-500 hover:underline ml-2">Xóa</button>
                  </td>
                </tr>
                <tr v-for="variant in product.variants" :key="variant.id" class="variant-row">
                  <td style="width: 350px; padding: 0.5rem 0.5rem 0.5rem 2rem;">
                    <div class="flex items-center variant-content">
                      <img :src="getImageUrl(variant.image_url)" :alt="getPrimaryAttribute(variant) || 'Variant'" class="w-12 h-12 mr-2 object-cover" @error="handleImageError($event, variant.image_url)">
                      <div>
                        <p class="text-sm">
                          {{ getPrimaryAttribute(variant) || 'Không có thuộc tính' }}
                        </p>
                        <p v-for="attr in getOtherAttributes(variant)" :key="attr.name" class="text-sm text-gray-600">
                          {{ attr.name }}: {{ attr.value }}
                        </p>
                        <p class="text-sm text-gray-600">SKU: {{ variant.sku || '-' }}</p>
                        <p class="text-sm text-gray-600">Giá: ₫{{ formatCurrency(variant.price || 0) }}</p>
                        <p class="text-sm text-gray-600">Tồn kho: {{ variant.stock || 0 }}</p>
                      </div>
                    </div>
                  </td>
                  <td style="width: 150px; padding: 0.5rem; text-align: center;">
                    <p class="text-sm text-gray-600">
                      {{ getPrimaryAttribute(variant) || 'N/A' }}: {{ variant.sales || 0 }}
                    </p>
                  </td>
                  <td style="width: 150px; padding: 0.5rem; text-align: center;">
                    <p class="text-sm text-gray-600">₫{{ formatCurrency(variant.price || 0) }}</p>
                  </td>
                  <td style="width: 150px; padding: 0.5rem; text-align: center;">
                    <p class="text-sm text-gray-600">{{ variant.stock || 0 }}</p>
                  </td>
                  <td style="width: 150px; padding: 0.5rem; text-align: center;">
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input
                        type="checkbox"
                        class="sr-only peer"
                        :checked="variant.status === 'active'"
                        @click.prevent="openToggleConfirmModal(variant, variant.status === 'active' ? 'inactive' : 'active')"
                        :disabled="variant.isLoading"
                      />
                      <div
                        class="w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-green-600 transition-colors duration-200"
                        :class="{ 'opacity-50 cursor-not-allowed': variant.isLoading }"
                      >
                        <div
                          class="w-5 h-5 bg-white rounded-full shadow-md transform transition-transform duration-200"
                          :class="{ 'translate-x-6': variant.status === 'active' }"
                        ></div>
                      </div>
                    </label>
                  </td>
                  <td style="width: 100px; padding: 0; text-align: center;"></td>
                </tr>
              </table>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal xác nhận -->
    <SellerConfirmModal
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
import FilterSearch from './component/SellerFilterSearch.vue';
import SellerConfirmModal from './component/SellerConfirmModal.vue';

export default {
  name: 'SellerProductsAll',
  components: {
    FilterSearch,
    SellerConfirmModal,
  },
  data() {
    return {
      allProducts: [],
      selectedProducts: [],
      search: '',
      currentFilter: 'all',
      filters: [
        { key: 'all', label: 'Tất cả', count: 0 },
        { key: 'pending', label: 'Chờ duyệt', count: 0 },
        { key: 'approved', label: 'Đã duyệt', count: 0 },
        { key: 'banned', label: 'Bị cấm', count: 0 },
      ],
      statusText: {
        pending: 'Chờ duyệt',
        approved: 'Đã duyệt',
        banned: 'Bị cấm',
      },
      showConfirmModal: false,
      confirmTitle: '',
      confirmMessage: '',
      confirmAction: null,
      selectedVariant: null,
      newStatus: null,
      placeholderImage: 'https://placehold.co/150',
      searchTimeout: null, // Thêm thuộc tính để lưu timeout của search
    };
  },
  computed: {
    filteredProducts() {
      return this.allProducts.filter(product => {
        const searchLower = this.search.trim().toLowerCase();
        const matchSearch = searchLower === '' ||
          product.name?.toLowerCase().includes(searchLower) ||
          product.variants.some(variant => variant.sku?.toLowerCase().includes(searchLower));
        const matchStatus = this.currentFilter === 'all' || product.status === this.currentFilter;
        return matchSearch && matchStatus;
      });
    },
    filterCounts() {
      return {
        all: this.allProducts.length,
        pending: this.allProducts.filter(p => p.status === 'pending').length,
        approved: this.allProducts.filter(p => p.status === 'approved').length,
        banned: this.allProducts.filter(p => p.status === 'banned').length,
      };
    },
  },
  created() {
    // Không cần debounce từ lodash nữa
    this.debouncedApplySearch = this.debounce(this.applySearch, 300);
  },
  async mounted() {
    await this.fetchProducts();
    this.updateFilterCounts();
  },
  watch: {
    currentFilter() {
      this.applySearch();
    },
  },
  methods: {
    // Hàm debounce tự tạo
    debounce(func, delay) {
      return (...args) => {
        if (this.searchTimeout) clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
          func.apply(this, args);
        }, delay);
      };
    },
    
    async fetchProducts() {
      try {
        const token = localStorage.getItem('token');
        if (!token) {
          throw new Error('No token found. Please login again.');
        }
        console.log('Fetching products with params:', { search: this.search, status: this.currentFilter });
        const response = await axios.get('http://localhost:8000/api/seller/products', {
          headers: { Authorization: `Bearer ${token}` },
          params: {
            search: this.search,
            status: this.currentFilter === 'all' ? '' : this.currentFilter,
          },
        });
        this.allProducts = (response.data.data || []).map(product => ({
          ...product,
          variants: product.variants.map(variant => ({
            ...variant,
            isLoading: false,
          })),
        }));
        this.updateFilterCounts();
      } catch (error) {
        console.error('Error fetching products:', error.response?.data || error.message);
        this.allProducts = [];
        alert('Lỗi khi lấy danh sách sản phẩm: ' + (error.response?.data?.message || error.message));
      }
    },
    updateFilterCounts() {
      this.filters = this.filters.map(filter => ({
        ...filter,
        count: this.filterCounts[filter.key] || 0,
      }));
    },
    applySearch() {
      this.fetchProducts();
    },
    getImageUrl(imgUrl) {
      if (!imgUrl) return this.placeholderImage;
      if (/^blob:/.test(imgUrl)) return imgUrl; // Preview ảnh cục bộ
      const baseUrl = import.meta.env.VITE_STORAGE_BASE_URL || 'http://localhost:8000/storage';
      const cleanImgUrl = imgUrl.replace(/^\/?(storage\/)?/, '');
      return `${baseUrl}/${cleanImgUrl}?t=${new Date().getTime()}`;
    },
    handleImageError(event, imgUrl) {
      console.error('Lỗi tải ảnh:', { img_url: imgUrl, attempted_url: event.target.src });
      event.target.src = this.placeholderImage;
    },
    formatCurrency(value) {
      return value ? value.toLocaleString('vi-VN') : '0';
    },
    getPrimaryAttribute(variant) {
      return variant.color || variant.attributes?.[0]?.value || null;
    },
    getOtherAttributes(variant) {
      return variant.attributes?.slice(1) || [];
    },
    editProduct(id) {
      this.$router.push(`/seller/products/edit/${id}`);
    },
    deleteProduct(id) {
      this.confirmTitle = 'Xác nhận xóa sản phẩm';
      this.confirmMessage = 'Bạn có chắc chắn muốn xóa sản phẩm này? Hành động này không thể hoàn tác.';
      this.confirmAction = 'delete';
      this.selectedVariant = { id };
      this.showConfirmModal = true;
    },
    openToggleConfirmModal(variant, newStatus) {
      this.confirmTitle = `Xác nhận ${newStatus === 'active' ? 'kích hoạt' : 'tắt'} biến thể`;
      this.confirmMessage = `Bạn có chắc chắn muốn ${newStatus === 'active' ? 'kích hoạt' : 'tắt'} biến thể này?`;
      this.confirmAction = 'toggleVariant';
      this.selectedVariant = variant;
      this.newStatus = newStatus;
      this.showConfirmModal = true;
    },
    async handleConfirm() {
      if (this.confirmAction === 'delete') {
        await this.deleteProductConfirm(this.selectedVariant.id);
      } else if (this.confirmAction === 'toggleVariant') {
        await this.updateVariantStatus(this.selectedVariant, this.newStatus);
      }
      this.showConfirmModal = false;
      this.confirmAction = null;
      this.selectedVariant = null;
      this.newStatus = null;
    },
    handleCancel() {
      this.showConfirmModal = false;
      this.confirmAction = null;
      this.selectedVariant = null;
      this.newStatus = null;
    },
    async deleteProductConfirm(id) {
      try {
        const token = localStorage.getItem('token');
        if (!token) {
          throw new Error('No token found. Please login again.');
        }
        await axios.delete(`http://localhost:8000/api/seller/products/${id}`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.allProducts = this.allProducts.filter(product => product.id !== id);
        this.updateFilterCounts();
        alert('Xóa sản phẩm thành công');
      } catch (error) {
        console.error('Error deleting product:', error.response?.data || error.message);
        alert('Lỗi khi xóa sản phẩm: ' + (error.response?.data?.message || error.message));
      }
    },
    async updateVariantStatus(variant, status) {
      try {
        const token = localStorage.getItem('token');
        if (!token) {
          throw new Error('No token found. Please login again.');
        }
        variant.isLoading = true;
        await axios.put(`http://localhost:8000/api/seller/products/variants/${variant.id}/status`, 
          { status },
          {
            headers: { Authorization: `Bearer ${token}` },
          }
        );
        variant.status = status;
        alert(`Đã ${status === 'active' ? 'kích hoạt' : 'tắt'} biến thể thành công`);
      } catch (error) {
        console.error('Error updating variant status:', error.response?.data || error.message);
        alert('Lỗi khi cập nhật trạng thái biến thể: ' + (error.response?.data?.message || error.message));
      } finally {
        variant.isLoading = false;
      }
    },
  },
};

</script>

<style scoped>
.eds-table {
  border-collapse: collapse;
}
.eds-table__cell {
  display: flex;
  align-items: center;
  justify-content: center;
}
.product-variation-header {
  width: 100%;
}
.list-header-item {
  flex-shrink: 0;
}
.product-name-wrap {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.variant-row {
  border-top: 1px solid #e5e7eb;
}
.variant-content {
  padding-left: 1rem;
}
</style>