<template>
  <div class="p-8 space-y-6">
    <h1 class="text-2xl font-bold">Quản lý Sản phẩm</h1>
    <div class="flex justify-between items-center">
      <FilterSearch
        :filters="filters"
        :searchPlaceholder="'Tìm kiếm theo tên sản phẩm...'"
        v-model="currentFilter"
        v-model:search="searchQuery"
        @search="applySearch"
      />
      <select v-model="selectedCategory" @change="applySearch" class="p-2 border rounded">
        <option value="">Tất cả danh mục</option>
        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
      </select>
    </div>
    <div class="overflow-x-auto">
      <p v-if="filteredProducts.length === 0" class="text-center text-gray-500">
        Không tìm thấy sản phẩm nào.
      </p>
        <table v-else class="table-auto border border-collapse collapse eds-table">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-4 py-2 border" style="width: 50px;">
                <div class="eds-table__cell">
                  <span class="eds-table__cell-label">Chọn</span>
                </div>
              </th>
              <th class="px-4 py-2 border" style="width: 1050px;">
                <div class="eds-table__cell">
                  <div class="product-variation-header flex">
                    <div class="list-header-item" style="width: 600px; padding: 0.5rem;">
                      <span class="text-sm font-semibold">Sản phẩm</span>
                    </div>
                    <div class="list-header-item" style="width: 100px; padding: 0.5rem; text-align: center;">
                      <span class="text-sm font-semibold">Doanh số</span>
                    </div>
                    <div class="list-header-item" style="width: 150px; padding: 0.5rem; text-align: center;">
                      <span class="text-sm font-semibold">Giá</span>
                    </div>
                    <div class="list-header-item" style="width: 100px; padding: 0.5rem; text-align: center;">
                      <span class="text-sm font-semibold">Kho</span>
                    </div>
                    <div class="list-header-item" style="width: 100px; padding: 0.5rem; text-align: center;">
                      <span class="text-sm font-semibold">Trạng thái</span>
                    </div>
                  </div>
                </div>
              </th>
              <th class="px-4 py-2 border">
                <span class="text-sm font-semibold">Chất lượng</span>
              </th>
              <th v-if="hasPermission('delete')" class="px-4 py-2 border">
                <span class="text-sm font-semibold">Hành động</span>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="product in filteredProducts" :key="product.id" class="hover:bg-gray-50 transition">
              <td class="px-4 py-2 border text-center align-middle">
                <input type="checkbox"
                :name="'product_' + product.id"
                :value="product.id"
                class="eds-checkbox__input"
              />
              </td>
              <td class="px-4 py-2 border">
                <table class="w-full border-collapse" style="table-layout: fixed; width: 1000px;">
                  <tr class="product-header">
                    <td style="width: 600px; padding: 0.5rem;">
                      <div class="flex items-center">
                        <img
                          :src="product.variants[0]?.image_url || 'https://via.placeholder.com/asset'"
                          :alt="product.name"
                          class="w-16 h-16 mr-4"
                        >
                          <div>
                          <a
                            :href="'/portal/product/' + product.id"
                            class="text-blue-600 hover:underline product-name-wrap"
                            target="_blank"
                          >
                            {{ product.name || 'N/A' }}
                          </a>
                          <p class="text-sm text-gray-600">Danh mục: {{ product.category?.name || 'N/A' }}</p>
                          <p class="text-sm text-gray-600">ID: {{ product.id }}</p>
                        </div>
                      </div>
                    </td>
                    <td style="width: 150px; padding: 0.5rem; text-align: center;">
                      <p v-if="product.variants.length" class="text-sm text-gray-600">
                        Tổng: 0
                      </p>
                      <p v-else class="text-sm text-gray-600">0</p>
                    </td>
                    <td style="width: 150px; padding: 0.5rem; text-align: center;">
                      <p v-if="product.variants.length" class="text-sm text-gray-600">
                        {{ product.variants.length > 1
                          ? `₫${formatPrice(Math.min(...product.variants.map(v => v.price)))} - ₫${formatPrice(Math.max(...product.variants.map(v => v.price)))}`
                          : `₫${formatPrice(product.variants[0]?.price)}` }}
                      </p>
                      <p v-else class="text-sm text-gray-600">N/A</p>
                    </td>
                    <td style="width: 150px; padding: 0.5rem; text-align: center;">
                      <p v-if="product.variants.length" class="text-sm text-gray-600">
                        Tổng: {{ product.variants.reduce((sum, v) => sum + v.stock, 0) }}
                      </p>
                      <p v-else class="text-sm text-gray-600">0</p>
                    </td>
                    <td style="width: 150px; padding: 0.5rem; text-align: center;">
                      <select
                        :value="product.status"
                        @change="confirmUpdateProductStatus(product.id, $event.target.value)"
                        class="w-32 text-sm border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                      >
                        <option value="pending">Chờ duyệt</option>
                        <option value="approved">Đã duyệt</option>
                        <option value="banned">Bị cấm</option>
                      </select>
                    </td>
                  </tr>
                  <tr v-for="variant in product.variants" :key="variant.id" class="variant-row">
                    <td style="width: 600px; padding: 0.5rem 0.5rem 0.5rem 1rem;">
                      <div class="flex items-center variant-content">
                        <img
                          :src="variant.image_url || 'https://via.placeholder.com/50'"
                          alt="Variant"
                          class="w-12 h-12 mr-2"
                        >
                          <div>
                          <p class="text-sm">{{ variant.attributes?.map(attr => `${attr.attribute_name}: ${attr.attribute_value}`).join(', ') || 'Không có thuộc tính' }}</p>
                          <p class="text-sm text-gray-600">SKU: {{ variant.sku || '-' }}</p>
                          <p class="text-sm text-gray-600">Giá: ₫{{ formatPrice(variant.price) }}</p>
                          <p class="text-sm text-gray-600">Tồn kho: {{ variant.stock }}</p>
                        </div>
                      </div>
                    </td>
                    <td style="width: 150px; padding: 0.5rem; text-align: center;">
                      <p class="text-sm text-gray-600">0</p>
                    </td>
                    <td style="width: 150px; padding: 0.5rem; text-align: center;">
                      <p class="text-sm text-gray-600">₫{{ formatPrice(variant.price) }}</p>
                    </td>
                    <td style="width: 150px; padding: 0.5rem; text-align: center;">
                      <p class="text-sm text-gray-600">{{ variant.stock }}</p>
                    </td>
                    <td style="width: 150px; padding: 0.5rem; text-align: center;">
                      <label class="relative inline-flex items-center cursor-pointer">
                        <input
                          type="checkbox"
                          :checked="variant.status === 'active'"
                          @change="confirmUpdateVariantStatus(product.id, variant.id, $event.target.checked ? 'active' : 'inactive')"
                          class="sr-only"
                          :aria-label="`Toggle status for variant ${variant.id}`"
                        />
                        <div class="w-10 h-5 rounded-full transition duration-200 ease-in-out" :class="{ 'bg-green-600': variant.status === 'active', 'bg-gray-300': variant.status !== 'active' }"></div>
                        <div class="absolute w-3 h-3 bg-white rounded-full transition duration-200 ease-in-out" :class="{ 'translate-x-5': variant.status === 'active', 'translate-x-1': variant.status !== 'active' }"></div>
                      </label>
                    </td>
                  </tr>
                </table>
              </td>
              <td class="px-4 py-2 border text-center align-middle">
                <div class="relative">
                  <span
                    class="text-green-600 cursor-pointer"
                    @click="toggleQualityPopover(product.id)"
                  >
                    Đạt chuẩn
                  </span>
                  <div
                    v-if="showQualityPopover === product.id"
                    class="absolute z-10 bg-white border shadow-lg p-4 mt-2 rounded w-64 left-0"
                  >
                    <p class="font-semibold">Nhiệm vụ để đạt Xuất sắc:</p>
                    <ul class="list-disc pl-4 text-sm">
                      <li>Thêm video sản phẩm</li>
                      <li>Thêm thương hiệu</li>
                      <li>Tên sản phẩm có ít nhất 25~100 ký tự</li>
                    </ul>
                    <button class="mt-2 bg-blue-600 text-white px-2 py-1 rounded text-sm">
                      Cập nhật ngay
                    </button>
                  </div>
                  <p class="text-sm text-gray-600">Có 2 yếu tố cần điều chỉnh</p>
                </div>
              </td>
              <td v-if="hasPermission('delete')" class="px-4 py-2 border text-center align-middle">
                <button
                  @click="confirmDelete(product.id)"
                  class="text-red-600 hover:text-red-800"
                  :disabled="deletingProduct === product.id"
                  :aria-label="`Xóa sản phẩm ${product.name || 'N/A'}`"
                >
                  <svg
                    v-if="deletingProduct !== product.id"
                    class="w-5 h-5 inline"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  >
                    <path d="M3 6h18"></path>
                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                    <line x1="10" y1="11" x2="10" y2="17"></line>
                    <line x1="14" y1="11" x2="14" y2="17"></line>
                  </svg>
                  <span v-else class="text-sm">Đang xóa...</span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <ConfirmModal
        :show="showConfirmModal"
        :message="confirmMessage"
        @confirm="handleConfirm"
        @cancel="handleCancel"
      />
    </div>
</template>

<script>
import axios from 'axios';
import FilterSearch from './component/AdminFilterSearch.vue';
import ConfirmModal from './component/AdminConfirmModal.vue';

export default {
  name: 'AdminProducts',
  components: { FilterSearch, ConfirmModal },
  data() {
    return {
      products: [],
      allProducts: [],
      categories: [],
      searchQuery: '',
      currentFilter: 'all',
      selectedCategory: '',
      statusText: {
        pending: 'Chờ duyệt',
        approved: 'Đã duyệt',
        banned: 'Bị cấm',
      },
      showQualityPopover: null,
      deletingProduct: null,
      showConfirmModal: false,
      confirmMessage: '',
      confirmCallback: null,
      pendingStatusUpdate: null,
    };
  },
  computed: {
    filters() {
      return [
        { key: 'all', label: 'Tất cả', count: this.allProducts.length },
        { key: 'pending', label: 'Chờ duyệt', count: this.filterCountByStatus('pending') },
        { key: 'approved', label: 'Đã duyệt', count: this.filterCountByStatus('approved') },
        { key: 'banned', label: 'Bị cấm', count: this.filterCountByStatus('banned') },
      ];
    },
    filteredProducts() {
      const q = this.searchQuery.toLowerCase();
      return this.allProducts.filter((product) => {
        const matchQuery = (product.name || '').toLowerCase().includes(q);
        const matchFilter = this.currentFilter === 'all' || product.status === this.currentFilter;
        const matchCategory = !this.selectedCategory || product.category_id === parseInt(this.selectedCategory);
        return matchQuery && matchFilter && matchCategory;
      });
    },
  },
  async mounted() {
    if (!this.hasPermission('view')) {
      this.$router.push('/admin/reviews');
      return;
    }
    await this.fetchCategories();
    await this.fetchProducts();
  },
  methods: {
    hasPermission(action) {
      const role = localStorage.getItem('role');
      const permissions = {
        superadmin: ['view', 'updateStatus', 'delete'],
        admin: ['view', 'updateStatus'],
        moderator: ['view', 'updateStatus'],
      };
      return permissions[role]?.includes(action) || false;
    },
    async fetchCategories() {
      try {
        const token = localStorage.getItem('token');
        const response = await axios.get('http://localhost:8000/api/admin/categories', {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.categories = response.data.data || [];
      } catch (error) {
        console.error('Error fetching categories:', error);
        alert('Không thể tải danh mục sản phẩm');
      }
    },
    async fetchProducts() {
      const token = localStorage.getItem('token');
      try {
        if (!token) throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        const response = await axios.get('http://localhost:8000/api/admin/products', {
          headers: { Authorization: `Bearer ${token}` },
          params: {
            search: this.searchQuery,
            status: this.currentFilter !== 'all' ? this.currentFilter : null,
            category_id: this.selectedCategory || null,
          },
        });
        this.products = response.data;
        this.allProducts = response.data;
      } catch (error) {
        this.handleAuthError(error, 'Không thể tải danh sách sản phẩm');
      }
    },
    confirmUpdateProductStatus(productId, newStatus) {
      this.confirmMessage = `Bạn có chắc chắn muốn thay đổi trạng thái sản phẩm thành "${this.statusText[newStatus]}"?`;
      this.showConfirmModal = true;
      this.pendingStatusUpdate = { type: 'product', productId, newStatus };
      this.confirmCallback = () => this.executeUpdateProductStatus(productId, newStatus);
    },
    async executeUpdateProductStatus(productId, newStatus) {
      const token = localStorage.getItem('token');
      try {
        if (!token) throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        await axios.patch(
          `http://localhost:8000/api/admin/products/${productId}/status`,
          { status: newStatus },
          { headers: { Authorization: `Bearer ${token}` } }
        );
        this.allProducts = this.allProducts.map(product =>
          product.id === productId ? { ...product, status: newStatus } : product
        );
        alert('Cập nhật trạng thái sản phẩm thành công');
      } catch (error) {
        this.handleAuthError(error, 'Không thể cập nhật trạng thái sản phẩm');
      } finally {
        this.pendingStatusUpdate = null;
      }
    },
    confirmUpdateVariantStatus(productId, variantId, newStatus) {
      this.confirmMessage = `Bạn có chắc chắn muốn thay đổi trạng thái biến thể thành "${newStatus === 'active' ? 'Kích hoạt' : 'Không kích hoạt'}"?`;
      this.showConfirmModal = true;
      this.pendingStatusUpdate = { type: 'variant', productId, variantId, newStatus };
      this.confirmCallback = () => this.executeUpdateVariantStatus(productId, variantId, newStatus);
    },
    async executeUpdateVariantStatus(productId, variantId, newStatus) {
      const token = localStorage.getItem('token');
      try {
        if (!token) throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        await axios.patch(
          `http://localhost:8000/api/admin/variants/${variantId}/status`,
          { status: newStatus },
          { headers: { Authorization: `Bearer ${token}` } }
        );
        this.allProducts = this.allProducts.map(product =>
          product.id === productId
            ? {
                ...product,
                variants: product.variants.map(variant =>
                  variant.id === variantId ? { ...variant, status: newStatus } : variant
                ),
              }
            : product
        );
        alert('Cập nhật trạng thái biến thể thành công');
      } catch (error) {
        this.handleAuthError(error, 'Không thể cập nhật trạng thái biến thể');
      } finally {
        this.pendingStatusUpdate = null;
      }
    },
    confirmDelete(productId) {
      this.confirmMessage = 'Bạn có chắc chắn muốn xóa sản phẩm này?';
      this.showConfirmModal = true;
      this.confirmCallback = () => this.deleteProduct(productId);
    },
    async deleteProduct(productId) {
      const token = localStorage.getItem('token');
      if (!this.hasPermission('delete')) {
        alert('Bạn không có quyền xóa sản phẩm.');
        return;
      }
      try {
        if (!token) throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        this.deletingProduct = productId;
        await axios.delete(`http://localhost:8000/api/admin/products/${productId}`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.allProducts = this.allProducts.filter(product => product.id !== productId);
        alert('Xóa sản phẩm thành công');
      } catch (error) {
        this.handleAuthError(error, 'Không thể xóa sản phẩm');
      } finally {
        this.deletingProduct = null;
        this.showConfirmModal = false;
      }
    },
    handleConfirm() {
      if (this.confirmCallback) this.confirmCallback();
      this.confirmCallback = null;
      this.showConfirmModal = false;
    },
    handleCancel() {
      this.pendingStatusUpdate = null;
      this.confirmCallback = null;
      this.showConfirmModal = false;
    },
    handleAuthError(error, defaultMsg) {
      console.error(defaultMsg, error);
      const msg = error.response?.data?.error || error.message || defaultMsg;
      alert(`${defaultMsg}: ${msg}`);
      if (error.response?.status === 401) {
        localStorage.removeItem('token');
        localStorage.removeItem('role');
        localStorage.removeItem('loginType');
        this.$router.push('/admin/login');
      }
    },
    applySearch() {
      this.fetchProducts();
    },
    filterCountByStatus(status) {
      return this.allProducts.filter((product) => product.status === status).length;
    },
    formatPrice(price) {
      return new Intl.NumberFormat('vi-VN', { minimumFractionDigits: 0 }).format(price || 0);
    },
    toggleQualityPopover(productId) {
      this.showQualityPopover = this.showQualityPopover === productId ? null : productId;
    },
  },
};
</script>

<style scoped>
.transition {
  transition: background-color 0.3s ease;
}
.product-name-wrap {
  display: inline-block;
  max-width: 400px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.eds-table {
  border-collapse: collapse;
  box-sizing: border-box;
  width: 100%;
  min-width: 1100px;
}
.eds-table__cell {
  display: flex;
  align-items: center;
  justify-content: center;
}
.product-variation-header {
  display: flex;
  flex-wrap: nowrap;
}
.list-header-item {
  display: flex;
  align-items: center;
  justify-content: center;
  box-sizing: border-box;
}
.list-header-item-label {
  font-weight: 600;
  font-size: 0.875rem;
}
.list-header-item-action {
  display: inline-flex;
  align-items: center;
}
.text-sm {
  font-size: 0.875rem;
}
.product-header, .variant-row {
  border-bottom: 1px solid #e5e7eb;
}
.product-header td, .variant-row td {
  vertical-align: middle;
  box-sizing: border-box;
}
.quick-edit-icon {
  display: none;
}
.cursor-pointer:hover .quick-edit-icon {
  display: inline;
}
.variant-content {
  max-width: calc(400px - 48px - 1rem);
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>