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
      <!-- Removed "Thêm Sản phẩm" button -->
    </div>
    <div class="overflow-x-auto">
      <p v-if="filteredProducts.length === 0" class="text-center text-gray-500">
        Không tìm thấy sản phẩm nào.
      </p>
      <table v-else class="table-auto border border-gray-300 eds-table">
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
                  <div class="list-header-item" style="width: 450px; padding: 0.5rem;">
                    <span class="list-header-item-label">Tên sản phẩm</span>
                  </div>
                  <div class="list-header-item" style="width: 150px; padding: 0.5rem; text-align: center;">
                    <span class="list-header-item-label">Doanh số</span>
                    <div class="list-header-item-action inline-block ml-1">
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
                  <div class="list-header-item" style="width: 150px; padding: 0.5rem; text-align: center;">
                    <span class="list-header-item-label">Giá</span>
                  </div>
                  <div class="list-header-item" style="width: 150px; padding: 0.5rem; text-align: center;">
                    <span class="list-header-item-label">Kho hàng</span>
                    <div class="list-header-item-action inline-block ml-1">
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
                  <div class="list-header-item" style="width: 150px; padding: 0.5rem; text-align: center;">
                    <span class="list-header-item-label">Trạng thái</span>
                  </div>
                </div>
              </div>
            </th>
            <th class="px-4 py-2 border">
              <div class="eds-table__cell">
                <div class="list-header-item" style="padding: 0.5rem;">
                  <span class="list-header-item-label">Chất Lượng Nội Dung</span>
                  <div class="list-header-item-action inline-block ml-1">
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
            </th>
            <th v-if="hasPermission('delete')" class="px-4 py-2 border">
              <div class="eds-table__cell">
                <span class="list-header-item-label">Hành động</span>
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
            <td class="px-4 py-2 border" style="width: 1050px;">
              <table class="w-full border-collapse" style="table-layout: fixed; width: 1050px;">
                <tr class="product-header">
                  <td style="width: 450px; padding: 0.5rem;">
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
                    <p v-else class="text-sm text-gray-600">₫{{ formatPrice(product.price) }}</p>
                  </td>
                  <td style="width: 150px; padding: 0.5rem; text-align: center;">
                    <p v-if="product.variants.length" class="text-sm text-gray-600">
                      Tổng: {{ product.variants.reduce((sum, v) => sum + v.stock, 0) }}
                    </p>
                    <p v-else class="text-sm text-gray-600">{{ product.stock }}</p>
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
                <tr
                  v-for="variant in product.variants"
                  :key="variant.id"
                  class="variant-row"
                >
                  <td style="width: 450px; padding: 0.5rem 0.5rem 0.5rem 1rem;">
                    <div class="flex items-center variant-content">
                      <img
                        :src="variant.image_url || 'https://via.placeholder.com/50'"
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
                  <td style="width: 150px; padding: 0.5rem; text-align: center;">
                    <p class="text-sm text-gray-600">{{ variant.color || 'N/A' }} ({{ variant.size || 'N/A' }}): 0</p>
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
                        :aria-label="`Toggle status for variant ${variant.color || 'N/A'} (${variant.size || 'N/A'})`"
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
      searchQuery: '',
      currentFilter: 'all',
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
        return matchQuery && matchFilter;
      });
    },
  },
  async mounted() {
    if (!this.hasPermission('view')) {
      this.$router.push('/admin/reviews');
      return;
    }
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
    async fetchProducts() {
      const token = localStorage.getItem('token');
      try {
        if (!token) throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        const response = await axios.get('/admin/products', {
          headers: { Authorization: `Bearer ${token}` },
          params: {
            search: this.searchQuery,
            status: this.currentFilter !== 'all' ? this.currentFilter : null,
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
          `/admin/products/${productId}/status`,
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
          `/admin/variants/${variantId}/status`,
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
        await axios.delete(`/admin/products/${productId}`, {
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
      return new Intl.NumberFormat('vi-VN', { minimumFractionDigits: 2 }).format(price || 0);
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