<template>
  <div class="p-8 space-y-6">
    <h1 class="text-2xl font-bold">Quản lý Sản phẩm</h1>
    <div class="overflow-x-auto">
      <p v-if="products.length === 0" class="text-center text-gray-500">
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
                  <div
                    class="list-header-item"
                    style="width: 150px; padding: 0.5rem; text-align: center;"
                  >
                    <span class="list-header-item-label">Doanh số</span>
                    <div class="list-header-item-action inline-block ml-1">
                      <div class="eds-popover eds-popover--light">
                        <div class="eds-popover__ref">
                          <i class="eds-icon action-icon">
                            <svg
                              xmlns="http://www.w3.org/2000/svg"
                              viewBox="0 0 16 16"
                            >
                              <path
                                fill-rule="evenodd"
                                d="M8,1 C11.8659932,1 15,4.13400675 15,8 C15,11.8659932 11.8659932,15 8,15 C4.13400675,15 1,11.8659932 1,8 C1,4.13400675 4.13400675,1 8,1 Z M8,2 C4.6862915,2 2,4.6862915 2,8 C2,11.3137085 4.6862915,14 8,14 C11.3137085,14 14,11.3137085 14,8 C14,4.6862915 11.3137085,2 8,2 Z M7.98750749,10.2375075 C8.40172105,10.2375075 8.73750749,10.5732939 8.73750749,10.9875075 C8.73750749,11.401721 8.40172105,11.7375075 7.98750749,11.7375075 C7.57329392,11.7375075 7.38461538,11.401721 7.38461538,10.9875075 C7.38461538,10.5732939 7.57329392,10.2375075 7.98750749,10.2375075 Z M8.11700238,4.60513307 C9.97011776,4.60513307 10.7745841,6.50497267 9.94298079,7.72186504 C9.76926425,7.97606597 9.56587088,8.14546785 9.27050506,8.31454843 L9.11486938,8.39945305 L8.95824852,8.47993747 C8.56296349,8.68261431 8.49390831,8.75808648 8.49390831,9.0209925 C8.49390831,9.29713488 8.27005069,9.5209925 7.99390831,9.5209925 C7.71776594,9.5209925 7.49390831,9.29713488 7.49390831,9.0209925 C7.49390831,8.34166619 7.7650409,7.99681515 8.35913594,7.6662627 L8.76655168,7.45066498 C8.9424056,7.3502536 9.04307851,7.26633638 9.11735517,7.1576467 C9.52116165,6.56675314 9.11397414,5.60513307 8.11700238,5.60513307 C7.41791504,5.60513307 6.82814953,6.01272878 6.75715965,6.55275918 L6.75,6.66244953 L6.74194433,6.75232516 C6.69960837,6.98557437 6.49545989,7.16244953 6.25,7.16244953 C5.97385763,7.16244953 5.75,6.9385919 5.75,6.66244953 C5.75,5.44256682 6.87194406,4.60513307 8.11700238,4.60513307 Z"
                              ></path>
                            </svg>
                          </i>
                        </div>
                        <div
                          class="eds-popper eds-popover__popper eds-popover__popper--light with-arrow"
                          style="max-width: 320px; display: none;"
                        >
                          <div class="eds-popover__content">
                            Thông tin doanh số của sản phẩm đã bao gồm cả doanh số của từng phân loại (kể cả những phân loại đã bị xóa)
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div
                    class="list-header-item"
                    style="width: 150px; padding: 0.5rem; text-align: center;"
                  >
                    <span class="list-header-item-label">Giá</span>
                  </div>
                  <div
                    class="list-header-item"
                    style="width: 150px; padding: 0.5rem; text-align: center;"
                  >
                    <span class="list-header-item-label">Kho hàng</span>
                    <div class="list-header-item-action inline-block ml-1">
                      <div class="eds-popover eds-popover--light">
                        <div class="eds-popover__ref">
                          <i class="eds-icon action-icon">
                            <svg
                              xmlns="http://www.w3.org/2000/svg"
                              viewBox="0 0 16 16"
                            >
                              <path
                                fill-rule="evenodd"
                                d="M8,1 C11.8659932,1 15,4.13400675 15,8 C15,11.8659932 11.8659932,15 8,15 C4.13400675,15 1,11.8659932 1,8 C1,4.13400675 4.13400675,1 8,1 Z M8,2 C4.6862915,2 2,4.6862915 2,8 C2,11.3137085 4.6862915,14 8,14 C11.3137085,14 14,11.3137085 14,8 C14,4.6862915 11.3137085,2 8,2 Z M7.98750749,10.2375075 C8.40172105,10.2375075 8.73750749,10.5732939 8.73750749,10.9875075 C8.73750749,11.401721 8.40172105,11.7375075 7.98750749,11.7375075 C7.57329392,11.7375075 7.38461538,11.401721 7.38461538,10.9875075 C7.38461538,10.5732939 7.57329392,10.2375075 7.98750749,10.2375075 Z M8.11700238,4.60513307 C9.97011776,4.60513307 10.7745841,6.50497267 9.94298079,7.72186504 C9.76926425,7.97606597 9.56587088,8.14546785 9.27050506,8.31454843 L9.11486938,8.39945305 L8.95824852,8.47993747 C8.56296349,8.68261431 8.49390831,8.75808648 8.49390831,9.0209925 C8.49390831,9.29713488 8.27005069,9.5209925 7.99390831,9.5209925 C7.71776594,9.5209925 7.49390831,9.29713488 7.49390831,9.0209925 C7.49390831,8.34166619 7.7650409,7.99681515 8.35913594,7.6662627 L8.76655168,7.45066498 C8.9424056,7.3502536 9.04307851,7.26633638 9.11735517,7.1576467 C9.52116165,6.56675314 9.11397414,5.60513307 8.11700238,5.60513307 C7.41791504,5.60513307 6.82814953,6.01272878 6.75715965,6.55275918 L6.75,6.66244953 L6.74194433,6.75232516 C6.69960837,6.98557437 6.49545989,7.16244953 6.25,7.16244953 C5.97385763,7.16244953 5.75,6.9385919 5.75,6.66244953 C5.75,5.44256682 6.87194406,4.60513307 8.11700238,4.60513307 Z"
                              ></path>
                            </svg>
                          </i>
                        </div>
                        <div
                          class="eds-popper eds-popover__popper eds-popover__popper--light with-arrow"
                          style="max-width: 320px; display: none;"
                        >
                          <div class="eds-popover__content">
                            Tồn kho là tổng số lượng sản phẩm có sẵn để bán, bao gồm cả số lượng hàng được đăng ký khuyến mãi.
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div
                    class="list-header-item"
                    style="width: 150px; padding: 0.5rem; text-align: center;"
                  >
                    <span class="list-header-item-label">Trạng thái</span>
                  </div>
                </div>
              </div>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="product in products"
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
              <table
                class="w-full border-collapse"
                style="table-layout: fixed; width: 1050px;"
              >
                <tr class="product-header">
                  <td style="width: 450px; padding: 0.5rem;">
                    <div class="flex items-center">
                      <img
                        :src="
                          product.variants[0]?.image_url ||
                          product.thumbnail ||
                          'https://via.placeholder.com/50'
                        "
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
                        <p class="text-sm text-gray-600">
                          SKU: {{ product.sku || '-' }}
                        </p>
                        <p class="text-sm text-gray-600">ID: {{ product.id }}</p>
                      </div>
                    </div>
                  </td>
                  <td style="width: 150px; padding: 0.5rem; text-align: center;">
                    <p v-if="product.variants.length" class="text-sm text-gray-600">
                      Tổng: {{ product.total_sales || 0 }}
                    </p>
                    <p v-else class="text-sm text-gray-600">
                      {{ product.total_sales || 0 }}
                    </p>
                  </td>
                  <td style="width: 150px; padding: 0.5rem; text-align: center;">
                    <p v-if="product.variants.length" class="text-sm text-gray-600">
                      {{
                        product.variants.length > 1
                          ? `₫${formatPrice(
                              Math.min(...product.variants.map((v) => v.price))
                            )} - ₫${formatPrice(
                              Math.max(...product.variants.map((v) => v.price))
                            )}`
                          : `₫${formatPrice(product.variants[0]?.price)}`
                      }}
                    </p>
                    <p v-else class="text-sm text-gray-600">
                      ₫{{ formatPrice(product.price_min) }}
                    </p>
                  </td>
                  <td style="width: 150px; padding: 0.5rem; text-align: center;">
                    <p v-if="product.variants.length" class="text-sm text-gray-600">
                      Tổng:
                      {{ product.variants.reduce((sum, v) => sum + v.stock, 0) }}
                    </p>
                    <p v-else class="text-sm text-gray-600">
                      {{ product.total_stock }}
                    </p>
                  </td>
                  <td style="width: 150px; padding: 0.5rem; text-align: center;">
                    <p class="text-sm text-gray-600">
                      {{ statusText[product.status] }}
                    </p>
                  </td>
                </tr>
                <tr
                  v-for="variant in product.variants"
                  :key="variant.id"
                  class="variant-row"
                >
                  <td
                    style="width: 450px; padding: 0.5rem 0.5rem 0.5rem 2rem;"
                  >
                    <div class="flex items-center variant-content">
                      <img
                        :src="
                          variant.image_url || 'https://via.placeholder.com/50'
                        "
                        :alt="variant.color"
                        class="w-12 h-12 mr-2"
                      >
                      <div>
                        <p class="text-sm">{{ variant.color || 'N/A' }}</p>
                        <p class="text-sm text-gray-600">
                          Kích thước: {{ variant.size || 'N/A' }}
                        </p>
                        <p class="text-sm text-gray-600">
                          SKU: {{ variant.sku || '-' }}
                        </p>
                        <p class="text-sm text-gray-600">
                          Giá: ₫{{ formatPrice(variant.price) }}
                        </p>
                        <p class="text-sm text-gray-600">
                          Tồn kho: {{ variant.stock }}
                        </p>
                      </div>
                    </div>
                  </td>
                  <td style="width: 150px; padding: 0.5rem; text-align: center;">
                    <p class="text-sm text-gray-600">
                      {{ variant.color || 'N/A' }} ({{ variant.size || 'N/A' }}):
                      {{ variant.sales || 0 }}
                    </p>
                  </td>
                  <td style="width: 150px; padding: 0.5rem; text-align: center;">
                    <p class="text-sm text-gray-600">
                      ₫{{ formatPrice(variant.price) }}
                    </p>
                  </td>
                  <td style="width: 150px; padding: 0.5rem; text-align: center;">
                    <p class="text-sm text-gray-600">{{ variant.stock }}</p>
                  </td>
                  <td style="width: 150px; padding: 0.5rem; text-align: center;">
                    <p class="text-sm text-gray-600">
                      {{ variant.status === 'active' ? 'Kích hoạt' : 'Không kích hoạt' }}
                    </p>
                  </td>
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

export default {
  name: 'SellerProducts',
  data() {
    return {
      products: [],
      statusText: {
        pending: 'Chờ duyệt',
        approved: 'Đã duyệt',
        banned: 'Bị cấm',
      },
    };
  },
  async mounted() {
    await this.fetchProducts();
  },
  methods: {
    async fetchProducts() {
      const token = localStorage.getItem('token');
      try {
        if (!token) throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        const response = await axios.get('/seller/products', {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.products = response.data.data || [];
      } catch (error) {
        console.error('Không thể tải danh sách sản phẩm:', error);
        this.products = [];
      }
    },
    formatPrice(price) {
      return new Intl.NumberFormat('vi-VN', { minimumFractionDigits: 2 }).format(price || 0);
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
.product-header,
.variant-row {
  border-bottom: 1px solid #e5e7eb;
}
.product-header td,
.variant-row td {
  vertical-align: middle;
  box-sizing: border-box;
}
.variant-content {
  max-width: calc(400px - 48px - 1rem);
  overflow: hidden;
  text-overflow: ellipsis;
}
.eds-checkbox__input {
  width: 1rem;
  height: 1rem;
  cursor: pointer;
}
</style>