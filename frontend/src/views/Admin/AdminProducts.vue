<template>
  <div class="p-8 space-y-6">
    <h1 class="text-2xl font-bold">Quản lý Sản phẩm</h1>
    <div class="flex justify-between items-center">
      <FilterSearch
        :filters="filters"
        :search-placeholder="'Tìm kiếm sản phẩm...'"
        v-model:current-filter="currentFilter"
        v-model:search-query="searchQuery"
        @update:current-filter="debouncedFetchProducts"
        @search="debouncedFetchProducts"
      />
      <select v-model="selectedCategory" @change="debouncedFetchProducts" class="p-2 border rounded">
        <option value="">Tất cả danh mục</option>
        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
      </select>
    </div>
    <div class="overflow-x-auto">
      <p v-if="products.length === 0" class="text-center text-gray-500">Không tìm thấy sản phẩm nào.</p>
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
          <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50 transition">
            <td class="px-4 py-2 border-b text-center align-middle" style="width: 50px;">
              <input type="checkbox" :name="'product_' + product.id" :value="product.id" class="eds-checkbox__input">
            </td>
            <td class="px-4 py-2 border-b" style="width: 1050px;">
              <table class="w-full border-collapse" style="table-layout: fixed; width: 1050px;">
                <tr class="product-header">
                  <td style="width: 350px; padding: 0.5rem;">
                    <div class="flex items-center">
                      <img :src="product.thumbnail || 'https://placehold.co/50x50'" :alt="product.name || 'Product'" class="w-16 h-16 mr-4 object-cover">
                      <div>
                        <a :href="'/portal/product/' + product.id" class="text-blue-600 hover:underline product-name-wrap" target="_blank">
                          {{ product.name || 'N/A' }}
                        </a>
                        <p class="text-sm text-gray-600">Danh mục: {{ product.category?.name || 'N/A' }}</p>
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
                    <select
                      v-if="hasPermission('updateStatus')"
                      :value="product.status"
                      @change="confirmUpdateProductStatus(product.id, $event.target.value)"
                      class="border rounded px-2 py-1"
                    >
                      <option value="pending">Chờ duyệt</option>
                      <option value="approved">Đã duyệt</option>
                      <option value="banned">Bị cấm</option>
                    </select>
                    <p v-else class="text-sm text-gray-600">{{ statusText[product.status] || 'N/A' }}</p>
                  </td>
                  <td style="width: 100px; padding: 0.5rem; text-align: center;">
                    <button v-if="hasPermission('delete')" @click="confirmDelete(product.id)" class="text-red-600 hover:underline">Xóa</button>
                  </td>
                </tr>
                <tr v-for="variant in product.variants" :key="variant.id" class="variant-row">
                  <td style="width: 350px; padding: 0.5rem 0.5rem 0.5rem 2rem;">
                    <div class="flex items-center variant-content">
                      <img :src="variant.image_url || 'https://placehold.co/50x50'" :alt="variant.sku || 'Variant'" class="w-12 h-12 mr-2 object-cover">
                      <div>
                        <p class="text-sm">
                          {{ variant.attributes?.length ? 
                             variant.attributes.map(attr => `${attr.name}: ${attr.value}`).join(', ') : 
                             'Không có thuộc tính' }}
                        </p>
                        <p class="text-sm text-gray-600">SKU: {{ variant.sku || '-' }}</p>
                        <p class="text-sm text-gray-600">Giá: ₫{{ formatCurrency(variant.price || 0) }}</p>
                        <p class="text-sm text-gray-600">Tồn kho: {{ variant.stock || 0 }}</p>
                      </div>
                    </div>
                  </td>
                  <td style="width: 150px; padding: 0.5rem; text-align: center;">
                    <p class="text-sm text-gray-600">{{ variant.sales || 0 }}</p>
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
                        v-model="variant.isActive"
                        @change="toggleVariantStatus(variant)"
                        class="sr-only peer"
                      />
                      <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-green-600 transition-colors duration-200">
                        <div class="w-5 h-5 bg-white rounded-full shadow-md transform transition-transform duration-200" :class="{ 'translate-x-6': variant.isActive }"></div>
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
      statusCounts: { all: 0, pending: 0, approved: 0, banned: 0 },
      categories: [],
      searchQuery: '',
      currentFilter: 'all',
      selectedCategory: '',
      statusText: {
        pending: 'Chờ duyệt',
        approved: 'Đã duyệt',
        banned: 'Bị cấm',
      },
      showConfirmModal: false,
      confirmMessage: '',
      confirmCallback: null,
      pendingStatusUpdate: null,
      debouncedFetchProducts: null,
    };
  },
  computed: {
    filters() {
      return [
        { key: 'all', label: 'Tất cả', count: this.statusCounts.all },
        { key: 'pending', label: 'Chờ duyệt', count: this.statusCounts.pending },
        { key: 'approved', label: 'Đã duyệt', count: this.statusCounts.approved },
        { key: 'banned', label: 'Bị cấm', count: this.statusCounts.banned },
      ];
    },
  },
  created() {
    const debounce = (func, wait) => {
      let timeout;
      return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
      };
    };
    this.debouncedFetchProducts = debounce(this.fetchProducts, 300);
  },
  async mounted() {
    if (!this.hasPermission('view')) {
      this.$router.push('/admin/reviews');
      return;
    }
    await this.fetchCategories();
    await this.fetchProducts();
    await this.fetchStatusCounts();
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
        this.categories = response.data.data || response.data || [];
      } catch (error) {
        console.error('Error fetching categories:', error);
        alert('Không thể tải danh mục sản phẩm');
      }
    },
    async fetchProducts() {
      const token = localStorage.getItem('token');
      try {
        if (!token) throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        console.log('Fetching products with params:', {
          search: this.searchQuery.trim(),
          status: this.currentFilter !== 'all' ? this.currentFilter : null,
          category_id: this.selectedCategory || null,
        });
        const response = await axios.get('http://localhost:8000/api/admin/products', {
          headers: { Authorization: `Bearer ${token}` },
          params: {
            search: this.searchQuery.trim() || null,
            status: this.currentFilter !== 'all' ? this.currentFilter : null,
            category_id: this.selectedCategory || null,
            per_page: 9999,
          },
        });
        this.products = (response.data.data || []).map(product => ({
          ...product,
          variants: (product.variants || []).map(variant => ({
            ...variant,
            isActive: this.normalizeStatus(variant.status) === 'active',
            status: variant.status,
          })),
        }));
        console.log('Fetched products:', JSON.parse(JSON.stringify(this.products)));
      } catch (error) {
        this.handleAuthError(error, 'Không thể tải danh sách sản phẩm');
      }
    },
    async fetchStatusCounts() {
      const token = localStorage.getItem('token');
      try {
        if (!token) throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        const response = await axios.get('http://localhost:8000/api/admin/products', {
          headers: { Authorization: `Bearer ${token}` },
          params: { per_page: 9999 },
        });
        const products = response.data.data || [];
        this.statusCounts = {
          all: products.length,
          pending: products.filter(p => p.status === 'pending').length,
          approved: products.filter(p => p.status === 'approved').length,
          banned: products.filter(p => p.status === 'banned').length,
        };
        console.log('Status counts:', this.statusCounts);
      } catch (error) {
        console.error('Error fetching status counts:', error);
      }
    },
    normalizeStatus(status) {
      if ([true, 1, '1', 'active', 'true', 'on'].includes(status)) return 'active';
      if ([false, 0, '0', 'inactive', 'false', null, ''].includes(status)) return 'inactive';
      console.warn('Unknown variant status:', status);
      return 'inactive';
    },
    async confirmUpdateProductStatus(productId, newStatus) {
      this.confirmMessage = `Bạn có chắc chắn muốn thay đổi trạng thái sản phẩm thành "${this.statusText[newStatus]}"?`;
      this.showConfirmModal = true;
      this.pendingStatusUpdate = { type: 'product', productId, newStatus };
      this.confirmCallback = () => this.executeUpdateProductStatus(productId, newStatus);
    },
    async executeUpdateProductStatus(productId, newStatus) {
      const token = localStorage.getItem('token');
      try {
        if (!token) throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        const response = await axios.patch(
          `http://localhost:8000/api/admin/products/${productId}/status`,
          { status: newStatus },
          { headers: { Authorization: `Bearer ${token}` } }
        );
        if (response.data.success) {
          this.products = this.products.map(product =>
            product.id === productId ? { ...product, status: newStatus } : product
          );
          await this.fetchStatusCounts();
          alert('Cập nhật trạng thái sản phẩm thành công');
        } else {
          throw new Error(response.data.error || 'Lỗi không xác định');
        }
      } catch (error) {
        this.handleAuthError(error, 'Không thể cập nhật trạng thái sản phẩm');
      } finally {
        this.pendingStatusUpdate = null;
        this.showConfirmModal = false;
      }
    },
    async toggleVariantStatus(variant) {
      const token = localStorage.getItem('token');
      const newStatus = variant.isActive ? 'active' : 'inactive';
      const originalIsActive = variant.isActive;
      console.log('Toggling variant:', variant.id, 'isActive:', variant.isActive, 'status:', newStatus);
      try {
        if (!token) throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        const response = await axios.patch(
          `http://localhost:8000/api/admin/variants/${variant.id}/status`,
          { status: newStatus },
          { headers: { Authorization: `Bearer ${token}` } }
        );
        if (response.data.success) {
          variant.status = newStatus;
          console.log('API success:', response.data);
          alert('Cập nhật trạng thái biến thể thành công');
        } else {
          throw new Error(response.data.message || 'Lỗi không xác định');
        }
      } catch (error) {
        console.error('Error updating variant status:', error);
        alert('Lỗi khi cập nhật trạng thái biến thể: ' + (error.response?.data?.message || error.message));
        variant.isActive = originalIsActive;
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
        const response = await axios.delete(`http://localhost:8000/api/admin/products/${productId}`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        if (response.data.success) {
          this.products = this.products.filter(product => product.id !== productId);
          await this.fetchStatusCounts();
          alert('Xóa sản phẩm thành công');
        } else {
          throw new Error(response.data.error || 'Lỗi không xác định');
        }
      } catch (error) {
        this.handleAuthError(error, 'Không thể xóa sản phẩm');
      } finally {
        this.showConfirmModal = false;
      }
    },
    handleConfirm() {
      if (this.confirmCallback) {
        this.confirmCallback();
      }
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
      const msg = error.response?.data?.message || error.message || defaultMsg;
      alert(`${defaultMsg}: ${msg}`);
      if (error.response?.status === 401) {
        localStorage.removeItem('token');
        localStorage.removeItem('role');
        this.$router.push('/admin/login');
      }
    },
    formatCurrency(value) {
      return new Intl.NumberFormat('vi-VN', { minimumFractionDigits: 0 }).format(value || 0);
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
  max-width: 250px;
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
.variant-content {
  max-width: calc(350px - 48px - 1rem);
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>