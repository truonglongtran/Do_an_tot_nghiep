<template>
  <div class="p-8 space-y-6">
    <h1 class="text-2xl font-bold">Quản lý Sản phẩm</h1>
    <div class="flex justify-between items-center">
      <FilterSearch
        :filters="filters"
        :searchPlaceholder="'Tìm kiếm theo tên sản phẩm...'"
        v-model:currentFilter="currentFilter"
        v-model:searchQuery="searchQuery"
        @search="applySearch"
      />
      <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        Thêm Sản phẩm
      </button>
    </div>
    <p v-if="filteredProducts.length === 0" class="text-center text-gray-500">
      Không tìm thấy sản phẩm nào.
    </p>
    <table v-else class="min-w-full table-auto border border-gray-300 eds-table">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-4 py-2 border" style="width: 50px;">
            <div class="eds-table__cell">
              <span class="eds-table__cell-label">Chọn</span>
            </div>
          </th>
          <th class="px-4 py-2 border" style="min-width: 1000.107px;">
            <div class="eds-table__cell">
              <div class="eds-order-group product-variation-header">
                <div class="list-header-item product-variation-padding" style="width: 561.248px;">
                  <div class="list-header-item-content">
                    <span class="list-header-item-label">Tên sản phẩm</span>
                  </div>
                </div>
                <div class="list-header-item product-variation-padding" style="width: 150.825px;">
                  <div class="list-header-item-content show-order">
                    <span class="list-header-item-label">Doanh số</span>
                    <div class="list-header-item-action">
                      <div class="list-header-item-tips">
                        <div class="eds-popover eds-popover--light">
                          <div class="eds-popover__ref">
                            <i class="eds-icon action-icon">
                              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M8,1 C11.8659932,1 15,4.13400675 15,8 C15,11.8659932 11.8659932,15 8,15 C4.13400675,15 1,11.8659932 1,8 C1,4.13400675 4.13400675,1 8,1 Z M8,2 C4.6862915,2 2,4.6862915 2,8 C2,11.3137085 4.6862915,14 8,14 C11.3137085,14 14,11.3137085 14,8 C14,4.6862915 11.3137085,2 8,2 Z M7.98750749,10.2375075 C8.40172105,10.2375075 8.73750749,10.5732939 8.73750749,10.9875075 C8.73750749,11.401721 8.40172105,11.7375075 7.98750749,11.7375075 C7.57329392,11.7375075 7.38461538,11.401721 7.38461538,10.9875075 C7.38461538,10.5732939 7.57329392,10.2375075 7.98750749,10.2375075 Z M8.11700238,4.60513307 C9.97011776,4.60513307 10.7745841,6.50497267 9.94298079,7.72186504 C9.76926425,7.97606597 9.56587088,8.14546785 9.27050506,8.31454843 L9.11486938,8.39945305 L8.95824852,8.47993747 C8.56296349,8.68261431 8.49390831,8.75808648 8.49390831,9.0209925 C8.49390831,9.29713488 8.27005069,9.5209925 7.99390831,9.5209925 C7.71776594,9.5209925 7.49390831,9.29713488 7.49390831,9.0209925 C7.49390831,8.34166619 7.7650409,7.99681515 8.35913594,7.6662627 L8.76655168,7.45066498 C8.9424056,7.3502536 9.04307851,7.26633638 9.11735517,7.1576467 C9.52116165,6.56675314 9.11397414,5.60513307 8.11700238,5.60513307 C7.41791504,5.60513307 6.82814953,6.01272878 6.75715965,6.55275918 L6.75,6.66244953 L6.74194433,6.75232516 C6.69960837,6.98557437 6.49545989,7.16244953 6.25,7.16244953 C5.97385763,7.16244953 5.75,6.9385919 5.75,6.66244953 C5.75,5.44256682 6.87194406,4.60513307 8.11700238,4.60513307 Z"></path>
                              </svg>
                            </i>
                          </div>
                          <div class="eds-popper eds-popover__popper eds-popover__popper--light with-arrow" style="max-width: 320px; display: none;">
                            <div class="eds-popover__content">
                              Thông tin doanh số của sản phẩm đã bao gồm cả doanh số của từng phân loại (kể cả những phân loại đã bị xóa)
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="list-header-item product-variation-padding" style="width: 137.517px;">
                  <div class="list-header-item-content show-order">
                    <span class="list-header-item-label">Giá</span>
                  </div>
                </div>
                <div class="list-header-item product-variation-padding" style="width: 137.517px;">
                  <div class="list-header-item-content show-order">
                    <span class="list-header-item-label">Kho hàng</span>
                    <div class="list-header-item-action">
                      <div class="list-header-item-tips">
                        <div class="eds-popover eds-popover--light">
                          <div class="eds-popover__ref">
                            <i class="eds-icon action-icon">
                              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M8,1 C11.8659932,1 15,4.13400675 15,8 C15,11.8659932 11.8659932,15 8,15 C4.13400675,15 1,11.8659932 1,8 C1,4.13400675 4.13400675,1 8,1 Z M8,2 C4.6862915,2 2,4.6862915 2,8 C2,11.3137085 4.6862915,14 8,14 C11.3137085,14 14,11.3137085 14,8 C14,4.6862915 11.3137085,2 8,2 Z M7.98750749,10.2375075 C8.40172105,10.2375075 8.73750749,10.5732939 8.73750749,10.9875075 C8.73750749,11.401721 8.40172105,11.7375075 7.98750749,11.7375075 C7.57329392,11.7375075 7.38461538,11.401721 7.38461538,10.9875075 C7.38461538,10.5732939 7.57329392,10.2375075 7.98750749,10.2375075 Z M8.11700238,4.60513307 C9.97011776,4.60513307 10.7745841,6.50497267 9.94298079,7.72186504 C9.76926425,7.97606597 9.56587088,8.14546785 9.27050506,8.31454843 L9.11486938,8.39945305 L8.95824852,8.47993747 C8.56296349,8.68261431 8.49390831,8.75808648 8.49390831,9.0209925 C8.49390831,9.29713488 8.27005069,9.5209925 7.99390831,9.5209925 C7.71776594,9.5209925 7.49390831,9.29713488 7.49390831,9.0209925 C7.49390831,8.34166619 7.7650409,7.99681515 8.35913594,7.6662627 L8.76655168,7.45066498 C8.9424056,7.3502536 9.04307851,7.26633638 9.11735517,7.1576467 C9.52116165,6.56675314 9.11397414,5.60513307 8.11700238,5.60513307 C7.41791504,5.60513307 6.82814953,6.01272878 6.75715965,6.55275918 L6.75,6.66244953 L6.74194433,6.75232516 C6.69960837,6.98557437 6.49545989,7.16244953 6.25,7.16244953 C5.97385763,7.16244953 5.75,6.9385919 5.75,6.66244953 C5.75,5.44256682 6.87194406,4.60513307 8.11700238,4.60513307 Z"></path>
                              </svg>
                            </i>
                          </div>
                          <div class="eds-popper eds-popover__popper eds-popover__popper--light with-arrow" style="max-width: 320px; display: none;">
                            <div class="eds-popover__content">
                              Tồn kho là tổng số lượng sản phẩm có sẵn để bán, bao gồm cả số lượng hàng được đăng ký khuyến mãi.
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="list-header-item product-variation-padding" style="width: 120px;">
                  <div class="list-header-item-content show-order">
                    <span class="list-header-item-label">Trạng thái</span>
                  </div>
                </div>
              </div>
            </div>
          </th>
          <th class="px-4 py-2 border" style="width: 124px;">
            <div class="eds-table__cell">
              <div class="list-header-item product-variation-padding">
                <div class="list-header-item-content">
                  <span class="list-header-item-label">Chất Lượng Nội Dung</span>
                  <div class="list-header-item-action">
                    <div class="list-header-item-tips">
                      <div class="eds-popover eds-popover--light">
                        <div class="eds-popover__ref">
                          <i class="eds-icon action-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
                              <path fill-rule="evenodd" d="M8,1 C11.8659932,1 15,4.13400675 15,8 C15,11.8659932 11.8659932,15 8,15 C4.13400675,15 1,11.8659932 1,8 C1,4.13400675 4.13400675,1 8,1 Z M8,2 C4.6862915,2 2,4.6862915 2,8 C2,11.3137085 4.6862915,14 8,14 C11.3137085,14 14,11.3137085 14,8 C14,4.6862915 11.3137085,2 8,2 Z M7.98750749,10.2375075 C8.40172105,10.2375075 8.73750749,10.5732939 8.73750749,10.9875075 C8.73750749,11.401721 8.40172105,11.7375075 7.98750749,11.7375075 C7.57329392,11.7375075 7.38461538,11.401721 7.38461538,10.9875075 C7.38461538,10.5732939 7.57329392,10.2375075 7.98750749,10.2375075 Z M8.11700238,4.60513307 C9.97011776,4.60513307 10.7745841,6.50497267 9.94298079,7.72186504 C9.76926425,7.97606597 9.56587088,8.14546785 9.27050506,8.31454843 L9.11486938,8.39945305 L8.95824852,8.47993747 C8.56296349,8.68261431 8.49390831,8.75808648 8.49390831,9.0209925 C8.49390831,9.29713488 8.27005069,9.5209925 7.99390831,9.5209925 C7.71776594,9.5209925 7.49390831,9.29713488 7.49390831,9.0209925 C7.49390831,8.34166619 7.7650409,7.99681515 8.35913594,7.6662627 L8.76655168,7.45066498 C8.9424056,7.3502536 9.04307851,7.26633638 9.11735517,7.1576467 C9.52116165,6.56675314 9.11397414,5.60513307 8.11700238,5.60513307 C7.41791504,5.60513307 6.82814953,6.01272878 6.75715965,6.55275918 L6.75,6.66244953 L6.74194433,6.75232516 C6.69960837,6.98557437 6.49545989,7.16244953 6.25,7.16244953 C5.97385763,7.16244953 5.75,6.9385919 5.75,6.66244953 C5.75,5.44256682 6.87194406,4.60513307 8.11700238,4.60513307 Z"></path>
                            </svg>
                          </i>
                        </div>
                        <div class="eds-popper eds-popover__popper eds-popover__popper--light with-arrow" style="max-width: 320px; display: none;">
                          <div class="eds-popover__content">
                            Chất lượng nội dung bao gồm chất lượng hình ảnh, mô tả, thông tin sản phẩm,... Chất lượng nội dung sản phẩm càng cao sẽ đem đến càng nhiều lượng truy cập và doanh số cho sản phẩm.
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="product in filteredProducts"
          :key="product.id"
          class="hover:bg-gray-50 transition"
        >
          <td class="px-4 py-2 border text-center align-middle" style="width: 50px;">
            <input
              type="checkbox"
              :name="'product_' + product.id"
              :value="product.id"
              class="eds-checkbox__input"
            >
          </td>
          <td class="px-4 py-2 border">
            <table class="w-full border-collapse" style="table-layout: auto; min-width: 720px;">
              <tr class="product-header">
                <td class="py-2">
                  <div class="flex items-center">
                    <img
                      :src="product.variants[0]?.image_url || 'https://via.placeholder.com/50'"
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
                      <p class="text-sm text-gray-600">SKU: -</p>
                      <p class="text-sm text-gray-600">ID: {{ product.id }}</p>
                    </div>
                  </div>
                </td>
                <td class="py-2 text-center">
                  <p v-if="product.variants.length" class="text-sm text-gray-600">
                    Tổng: 0
                  </p>
                  <p v-else class="text-sm text-gray-600">0</p>
                </td>
                <td class="py-2 text-center">
                  <p v-if="product.variants.length" class="text-sm text-gray-600">
                    {{ product.variants.length > 1
                      ? `₫${formatPrice(Math.min(...product.variants.map(v => v.price)))} - ₫${formatPrice(Math.max(...product.variants.map(v => v.price)))}`
                      : `₫${formatPrice(product.variants[0]?.price)}` }}
                  </p>
                  <p v-else class="text-sm text-gray-600">₫{{ formatPrice(product.price) }}</p>
                </td>
                <td class="py-2 text-center">
                  <p v-if="product.variants.length" class="text-sm text-gray-600">
                    Tổng: {{ product.variants.reduce((sum, v) => sum + v.stock, 0) }}
                  </p>
                  <p v-else class="text-sm text-gray-600">{{ product.stock }}</p>
                </td>
                <td class="py-2 text-center">
                  <select
                    v-model="product.status"
                    @change="updateProductStatus(product.id, $event.target.value)"
                    class="w-32 text-sm border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                  >
                    <option value="pending">Chờ duyệt</option>
                    <option value="approved">Đã duyệt</option>
                    <option value="banned">Bị cấm</option>
                  </select>
                </td>
              </tr>
              <tr
                v-for="variant in product.variants"
                :key="variant.id"
                class="variant-row"
              >
                <td class="py-2" style="padding-left: 1rem;">
                  <div class="flex items-center variant-content">
                    <img
                      :src="variant.image_url"
                      :alt="variant.color"
                      class="w-12 h-12 mr-2"
                    >
                    <div>
                      <p class="text-sm">{{ variant.color || 'N/A' }}</p>
                      <p class="text-sm text-gray-600">Kích thước: {{ variant.size || 'N/A' }}</p>
                      <p class="text-sm text-gray-600">SKU: {{ variant.sku || '-' }}</p>
                      <p class="text-sm text-gray-600">Giá: ₫{{ formatPrice(variant.price) }}</p>
                      <p class="text-sm text-gray-600">Tồn kho: {{ variant.stock }}</p>
                    </div>
                  </div>
                </td>
                <td class="py-2 text-center">
                  <p class="text-sm text-gray-600">{{ variant.color || 'N/A' }} ({{ variant.size || 'N/A' }}): 0</p>
                </td>
                <td class="py-2 text-center">
                  <p class="text-sm text-gray-600">₫{{ formatPrice(variant.price) }}</p>
                </td>
                <td class="py-2 text-center">
                  <p class="text-sm text-gray-600">{{ variant.stock }}</p>
                </td>
                <td class="py-2 text-center">
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input
                      type="checkbox"
                      :checked="variant.status === 'active'"
                      @change="updateVariantStatus(product.id, variant.id, $event.target.checked ? 'active' : 'inactive')"
                      class="sr-only"
                    >
                    <div class="w-10 h-5 bg-gray-300 rounded-full transition duration-200 ease-in-out" :class="{ 'bg-blue-600': variant.status === 'active' }"></div>
                    <div class="absolute w-3 h-3 bg-white rounded-full transition duration-200 ease-in-out" :class="{ 'translate-x-5': variant.status === 'active', 'translate-x-1': variant.status !== 'active' }"></div>
                  </label>
                </td>
              </tr>
            </table>
          </td>
          <td class="px-4 py-2 border text-center align-middle" style="width: 124px;">
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
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import axios from 'axios';
import FilterSearch from './component/AdminFilterSearch.vue';

export default {
  name: 'AdminProducts',
  components: { FilterSearch },
  data() {
    return {
      products: [],
      allProducts: [],
      searchQuery: '',
      currentFilter: 'all',
      statusText: {
        pending: 'Chờ duyệt',
        approved: 'Đã duyệt',
        banned: 'Bị cấm',
      },
      showQualityPopover: null,
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
        return matchQuery && matchFilter;
      });
    },
  },
  async mounted() {
    await this.fetchProducts();
  },
  methods: {
    async fetchProducts() {
      const token = localStorage.getItem('token');
      try {
        if (!token) {
          throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        }
        console.log('Đang lấy danh sách sản phẩm với token:', token);
        const response = await axios.get('http://localhost:8000/api/admin/products', {
          headers: { Authorization: `Bearer ${token}` },
          params: {
            search: this.searchQuery,
            status: this.currentFilter !== 'all' ? this.currentFilter : null,
          },
        });
        console.log('Danh sách sản phẩm:', response.data);
        this.products = response.data;
        this.allProducts = response.data;
      } catch (error) {
        console.error('Lỗi khi tải danh sách sản phẩm:', error);
        if (error.response) {
          console.error('Dữ liệu phản hồi:', error.response.data);
          console.error('Trạng thái:', error.response.status);
          console.error('Header:', error.response.headers);
          alert('Không thể tải danh sách sản phẩm: ' + (error.response.data.error || error.message));
        } else {
          alert('Không thể tải danh sách sản phẩm: ' + error.message);
        }
        if (error.response?.status === 401) {
          localStorage.removeItem('token');
          localStorage.removeItem('role');
          localStorage.removeItem('loginType');
          this.$router.push('/admin/login');
        }
      }
    },
    async updateProductStatus(productId, newStatus) {
      const token = localStorage.getItem('token');
      try {
        if (!token) {
          throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        }
        console.log(`Cập nhật trạng thái sản phẩm ${productId} thành ${newStatus}`);
        const response = await axios.patch(
          `http://localhost:8000/api/admin/products/${productId}`,
          { status: newStatus },
          { headers: { Authorization: `Bearer ${token}` } }
        );
        console.log('Cập nhật thành công:', response.data);
        this.allProducts = this.allProducts.map(product =>
          product.id === productId ? { ...product, status: newStatus } : product
        );
      } catch (error) {
        console.error('Lỗi khi cập nhật trạng thái sản phẩm:', error);
        if (error.response) {
          console.error('Dữ liệu phản hồi:', error.response.data);
          console.error('Trạng thái:', error.response.status);
          alert('Không thể cập nhật trạng thái: ' + (error.response.data.error || error.message));
        } else {
          alert('Không thể cập nhật trạng thái: ' + error.message);
        }
        if (error.response?.status === 401) {
          localStorage.removeItem('token');
          localStorage.removeItem('role');
          localStorage.removeItem('loginType');
          this.$router.push('/admin/login');
        }
        this.allProducts = this.allProducts.map(product =>
          product.id === productId ? { ...product, status: this.allProducts.find(p => p.id === productId).status } : product
        );
      }
    },
    async updateVariantStatus(productId, variantId, newStatus) {
      const token = localStorage.getItem('token');
      try {
        if (!token) {
          throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        }
        console.log(`Cập nhật trạng thái biến thể ${variantId} thành ${newStatus}`);
        const response = await axios.patch(
          `http://localhost:8000/api/admin/variants/${variantId}`,
          { status: newStatus },
          { headers: { Authorization: `Bearer ${token}` } }
        );
        console.log('Cập nhật thành công:', response.data);
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
      } catch (error) {
        console.error('Lỗi khi cập nhật trạng thái biến thể:', error);
        if (error.response) {
          console.error('Dữ liệu phản hồi:', error.response.data);
          console.error('Trạng thái:', error.response.status);
          alert('Không thể cập nhật trạng thái: ' + (error.response.data.error || error.message));
        } else {
          alert('Không thể cập nhật trạng thái: ' + error.message);
        }
        if (error.response?.status === 401) {
          localStorage.removeItem('token');
          localStorage.removeItem('role');
          localStorage.removeItem('loginType');
          this.$router.push('/admin/login');
        }
        this.allProducts = this.allProducts.map(product =>
          product.id === productId
            ? {
                ...product,
                variants: product.variants.map(variant =>
                  variant.id === variantId
                    ? { ...variant, status: this.allProducts.find(p => p.id === productId).variants.find(v => v.id === variantId).status }
                    : variant
                ),
              }
            : product
        );
      }
    },
    applySearch() {
      console.log('Áp dụng tìm kiếm với từ khóa:', this.searchQuery, 'bộ lọc:', this.currentFilter);
      this.fetchProducts();
    },
    filterCountByStatus(status) {
      return this.allProducts.filter((product) => product.status === status).length;
    },
    formatPrice(price) {
      return new Intl.NumberFormat('vi-VN', { minimumFractionDigits: 2 }).format(price);
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
}
.eds-table__cell {
  display: flex;
  align-items: center;
}
.product-variation-header {
  display: flex;
  flex-wrap: nowrap;
  width: 1000.107px;
}
.list-header-item {
  display: inline-flex;
  align-items: center;
  box-sizing: border-box;
}
.list-header-item-content {
  display: flex;
  align-items: center;
}
.list-header-item-label {
  font-weight: 600;
  font-size: 0.875rem;
}
.list-header-item-action {
  margin-left: 0.5rem;
}
.product-variation-padding {
  padding: 0.5rem 0;
}
.text-sm {
  font-size: 0.75rem;
}
.product-header, .variant-row {
  border-bottom: 1px solid #e5e7eb;
}
.product-header td, .variant-row td {
  vertical-align: middle;
  box-sizing: border-box;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.py-2 {
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
}
.quick-edit-icon {
  display: none;
}
.cursor-pointer:hover .quick-edit-icon {
  display: inline;
}
.variant-content {
  max-width: calc(400px - 48px - 1rem); /* Hình ảnh 48px + thụt lề 1rem */
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>