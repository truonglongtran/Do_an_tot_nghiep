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
    <div class="flex justify-end mb-4">
      <button @click="$router.push('/seller/products/add')" class="bg-blue-500 text-white p-2 rounded">Thêm sản phẩm</button>
    </div>
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
                      <img :src="variant.image_url || 'https://placehold.co/50x50'" :alt="getPrimaryAttribute(variant) || 'Variant'" class="w-12 h-12 mr-2 object-cover">
                      <div>
                        <p class="text-sm" :class="{ 'text-red-500': isInvalidAttribute(variant) }">
                          {{ getPrimaryAttribute(variant) || 'Không có thuộc tính' }}
                        </p>
                        <p v-for="attr in getOtherAttributes(variant)" :key="attr.name" class="text-sm text-gray-600" :class="{ 'text-red-500': isInvalidAttributeValue(attr) }">
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
                      <input type="checkbox" v-model="variant.status" @change="toggleVariantStatus(variant)" :true-value="'active'" :false-value="'inactive'" class="sr-only peer">
                      <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600">
                        <div class="w-5 h-5 bg-white rounded-full shadow-md transform peer-checked:translate-x-5 transition-transform"></div>
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
  </div>
</template>

<script>
import axios from 'axios';
import FilterSearch from './component/SellerFilterSearch.vue';

export default {
  name: 'SellerProductsAll',
  components: {
    FilterSearch
  },
  data() {
    return {
      allProducts: [],
      search: '',
      currentFilter: 'all',
      filters: [
        { key: 'all', label: 'Tất cả', count: 0 },
        { key: 'pending', label: 'Chờ duyệt', count: 0 },
        { key: 'approved', label: 'Đã duyệt', count: 0 },
        { key: 'banned', label: 'Bị cấm', count: 0 }
      ],
      statusText: {
        pending: 'Chờ duyệt',
        approved: 'Đã duyệt',
        banned: 'Bị cấm'
      },
      debouncedApplySearch: null,
      validAttributeValues: {} // Sẽ được tải từ API
    };
  },
  computed: {
    filteredProducts() {
      console.log('Computing filteredProducts with:', { search: this.search, currentFilter: this.currentFilter });
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
        banned: this.allProducts.filter(p => p.status === 'banned').length
      };
    }
  },
  created() {
    const debounce = (func, wait) => {
      let timeout;
      return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
      };
    };
    this.debouncedApplySearch = debounce(this.applySearch, 300);
  },
  async mounted() {
    await this.fetchValidAttributes();
    await this.fetchProducts();
    this.updateFilterCounts();
  },
  watch: {
    currentFilter(newValue) {
      console.log('currentFilter changed to:', newValue);
      this.applySearch();
    }
  },
  methods: {
    async fetchValidAttributes() {
      try {
        const token = localStorage.getItem('token');
        const response = await axios.get('http://localhost:8000/api/seller/categories/attributes', {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.validAttributeValues = response.data.data.reduce((map, attr) => ({
          ...map,
          [attr.name.toLowerCase()]: attr.values.map(v => v.value.toLowerCase())
        }), {});
        console.log('Valid attribute values loaded:', this.validAttributeValues);
      } catch (error) {
        console.error('Error fetching valid attributes:', error);
      }
    },
    async fetchProducts() {
      console.log('Fetching all products');
      const token = localStorage.getItem('token');
      try {
        if (!token) {
          throw new Error('No token found. Please login again.');
        }
        const response = await axios.get('http://localhost:8000/api/seller/products', {
          headers: { Authorization: `Bearer ${token}` }
        });
        console.log('API response:', response.data);
        this.allProducts = response.data.data || [];
        this.updateFilterCounts();
      } catch (error) {
        console.error('Error fetching products:', error);
        this.allProducts = [];
        alert('Lỗi khi lấy danh sách sản phẩm: ' + (error.response?.data?.message || error.message));
      }
    },
    updateFilterCounts() {
      this.filters = this.filters.map(filter => ({
        ...filter,
        count: this.filterCounts[filter.key] || 0
      }));
      console.log('Updated filter counts:', this.filters);
    },
    applySearch() {
      console.log('Applying search with:', { search: this.search, currentFilter: this.currentFilter });
      this.updateFilterCounts();
    },
    async toggleVariantStatus(variant) {
      try {
        const token = localStorage.getItem('token');
        await axios.put(`http://localhost:8000/api/seller/variants/${variant.id}/status`, {
          status: variant.status
        }, {
          headers: { Authorization: `Bearer ${token}` }
        });
        alert('Cập nhật trạng thái biến thể thành công');
        await this.fetchProducts();
      } catch (error) {
        console.error('Error updating variant status:', error);
        alert('Lỗi khi cập nhật trạng thái biến thể: ' + (error.response?.data?.message || error.message));
        variant.status = variant.status === 'active' ? 'inactive' : 'active';
      }
    },
    editProduct(id) {
      this.$router.push(`/seller/products/edit/${id}`);
    },
    async deleteProduct(id) {
      if (!confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
        return;
      }
      try {
        const token = localStorage.getItem('token');
        await axios.delete(`http://localhost:8000/api/seller/products/${id}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        alert('Xóa sản phẩm thành công');
        await this.fetchProducts();
      } catch (error) {
        console.error('Error deleting product:', error);
        alert('Lỗi khi xóa sản phẩm: ' + (error.response?.data?.message || error.message));
      }
    },
    formatCurrency(value) {
      return new Intl.NumberFormat('vi-VN', { minimumFractionDigits: 0 }).format(value || 0);
    },
    getPrimaryAttribute(variant) {
      if (!variant.attributes || !Array.isArray(variant.attributes)) {
        return null;
      }
      const priorities = ['màu sắc', 'kích cỡ', 'chất liệu'];
      for (const priority of priorities) {
        const attr = variant.attributes.find(a => a.name?.toLowerCase() === priority);
        if (attr && attr.value) {
          return attr.value;
        }
      }
      return null;
    },
    getOtherAttributes(variant) {
      if (!variant.attributes || !Array.isArray(variant.attributes)) {
        return [];
      }
      const primaryValue = this.getPrimaryAttribute(variant);
      return variant.attributes
        .filter(attr => {
          const isPrimary = primaryValue && attr.value === primaryValue && attr.name?.toLowerCase() === this.getPrimaryAttributeName(variant);
          return !isPrimary && attr.value;
        })
        .map(attr => ({
          name: attr.name,
          value: attr.value
        }));
    },
    getPrimaryAttributeName(variant) {
      if (!variant.attributes || !Array.isArray(variant.attributes)) {
        return null;
      }
      const priorities = ['màu sắc', 'kích cỡ', 'chất liệu'];
      for (const priority of priorities) {
        const attr = variant.attributes.find(a => a.name?.toLowerCase() === priority);
        if (attr && attr.value) {
          return attr.name?.toLowerCase();
        }
      }
      return null;
    },
    isInvalidAttribute(variant) {
      const primaryValue = this.getPrimaryAttribute(variant);
      if (!primaryValue) return false;
      const attributeName = this.getPrimaryAttributeName(variant);
      const validValues = this.validAttributeValues[attributeName] || [];
      return !validValues.includes(primaryValue.toLowerCase());
    },
    isInvalidAttributeValue(attr) {
      const validValues = this.validAttributeValues[attr.name.toLowerCase()] || [];
      return !validValues.includes(attr.value.toLowerCase());
    }
  }
};
</script>

<style scoped>
.toggle-switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}
.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: 0.4s;
  border-radius: 34px;
}
.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  transition: 0.4s;
  border-radius: 50%;
}
input:checked + .slider {
  background-color: #2196F3;
}
input:checked + .slider:before {
  transform: translateX(26px);
}
</style>