<template>
  <div class="p-6 space-y-6">
    <h1 class="text-2xl font-bold">{{ pageTitle }}</h1>
    <div class="flex justify-between items-center">
      <FilterSearch
        :filters="filters"
        :searchPlaceholder="'Tìm kiếm theo email người mua...'"
        v-model:currentFilter="currentFilter"
        v-model:searchQuery="searchQuery"
        @search="applySearch"
      />
    </div>
    <div v-if="filteredOrders.length === 0" class="text-center text-gray-500">
      Không tìm thấy đơn hàng nào.
    </div>
    <table v-else class="min-w-full table-auto border border-gray-300">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-4 py-2 border">ID</th>
          <th class="px-4 py-2 border">Người mua</th>
          <th class="px-4 py-2 border">Tổng tiền</th>
          <th class="px-4 py-2 border">Trạng thái đơn</th>
          <th class="px-4 py-2 border">Vận chuyển</th>
          <th class="px-4 py-2 border">Hành động</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="order in filteredOrders" :key="order.id" class="hover:bg-gray-50 border-b">
          <td class="px-4 py-2 border text-center">{{ order.id }}</td>
          <td class="px-4 py-2 border text-center">{{ order.buyer?.email || 'N/A' }}</td>
          <td class="px-4 py-2 border text-right">{{ formatCurrency(order.total_amount) }}</td>
          <td class="px-4 py-2 border text-center">{{ statusText.order[order.order_status] || 'N/A' }}</td>
          <td class="px-4 py-2 border text-center">{{ statusText.shipping[order.shipping_status] || 'N/A' }}</td>
          <td class="px-4 py-2 border text-center">
            <button
              @click="openDetailModal(order)"
              class="text-blue-600 hover:underline"
            >
              Chi tiết
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <GenericDetailsModal
      :show="showOrderDetailsModal"
      :data="selectedOrder"
      :fields="orderFields"
      :title="`Chi tiết đơn hàng #${selectedOrder?.id || ''}`"
      @close="closeDetailModal"
    />
  </div>
</template>

<script>
import axios from 'axios';
import { useRoute } from 'vue-router';
import FilterSearch from './component/SellerFilterSearch.vue';
import GenericDetailsModal from './GenericDetailsModal.vue';

export default {
  name: 'SellerOrders',
  components: { FilterSearch, GenericDetailsModal },
  setup() {
    return { route: useRoute() };
  },
  data() {
    return {
      orders: [],
      allOrders: [],
      searchQuery: '',
      currentFilter: 'all',
      showOrderDetailsModal: false,
      selectedOrder: null,
      statusText: {
        order: {
          pending: 'Chờ xác nhận',
          paid: 'Đã thanh toán',
          canceled: 'Đã hủy',
        },
        shipping: {
          pending: 'Chờ xử lý',
          processing: 'Đang xử lý',
          shipping: 'Đang giao',
          delivered: 'Đã giao',
          failed: 'Thất bại',
          return: 'Trả hàng',
        },
      },
      orderFields: [
        { label: 'ID Đơn hàng', key: 'id', type: 'text' },
        { label: 'Email người mua', key: 'buyer.email', type: 'text' },
        {
          label: 'Tổng tiền',
          key: 'total_amount',
          type: 'custom',
          customFormat: (value) => this.formatCurrency(value || 0),
        },
        {
          label: 'Trạng thái đơn',
          key: 'order_status',
          type: 'custom',
          customFormat: (value) => this.statusText.order[value] || 'N/A',
        },
        {
          label: 'Trạng thái vận chuyển',
          key: 'shipping_status',
          type: 'custom',
          customFormat: (value) => this.statusText.shipping[value] || 'N/A',
        },
        {
          label: 'Ngày tạo',
          key: 'created_at',
          type: 'date',
          customFormat: (value) => this.formatDate(value),
        },
        {
          label: 'Sản phẩm',
          key: 'items',
          type: 'custom',
          customFormat: (items) => {
            if (!items || !Array.isArray(items) || items.length === 0) {
              return 'Không có sản phẩm';
            }
            return `
              <table class="min-w-full border">
                <thead>
                  <tr>
                    <th class="px-4 py-2 border-b bg-gray-50 text-left">Sản phẩm</th>
                    <th class="px-4 py-2 border-b bg-gray-50 text-left">Thuộc tính</th>
                    <th class="px-4 py-2 border-b bg-gray-50 text-center">Số lượng</th>
                    <th class="px-4 py-2 border-b bg-gray-50 text-right">Giá</th>
                  </tr>
                </thead>
                <tbody>
                  ${items
                    .map((item) => {
                      const attributes = item.product_variant?.variant_attributes?.length
                        ? item.product_variant.variant_attributes
                            .map((attr) => {
                              console.log('Processing attribute for item:', item.id, 'attr:', JSON.stringify(attr));
                              if (!attr.attribute || !attr.attribute_value) {
                                return 'Thuộc tính không hợp lệ';
                              }
                              return `${attr.attribute.name}: ${attr.attribute_value.value}`;
                            })
                            .filter(Boolean)
                            .join(', ') || 'Không có thuộc tính'
                        : 'Không có thuộc tính';
                      return `
                        <tr>
                          <td class="px-4 py-2 border">${item.product?.name || 'N/A'}</td>
                          <td class="px-4 py-2 border">${attributes}</td>
                          <td class="px-4 py-2 border text-center">${item.quantity || 0}</td>
                          <td class="px-4 py-2 border text-right">${this.formatCurrency(item.product_variant?.price || item.price || 0)}</td>
                        </tr>
                      `;
                    })
                    .join('')}
                </tbody>
              </table>
            `;
          },
        },
      ],
    };
  },
  computed: {
    pageTitle() {
      return this.route.path.includes('/delivery') ? 'Bàn giao đơn hàng' :
             this.route.path.includes('/returns') ? 'Đơn trả hàng/Hoàn tiền/Đơn hủy' :
             'Quản lý đơn hàng';
    },
    filterParams() {
      if (this.route.path.includes('/delivery')) {
        return { shipping_status: 'shipping,delivered' };
      } else if (this.route.path.includes('/returns')) {
        return { filter_type: 'returns' };
      }
      return {};
    },
    filters() {
      const statusTypes = this.route.path.includes('/delivery')
        ? [{ field: 'shipping_status', values: ['shipping', 'delivered'] }]
        : this.route.path.includes('/returns')
        ? [
            { field: 'shipping_status', values: ['return'] },
            { field: 'order_status', values: ['canceled'] },
          ]
        : [
            { field: 'order_status', values: ['pending', 'paid', 'canceled'] },
            { field: 'shipping_status', values: ['pending', 'processing', 'shipping', 'delivered', 'failed', 'return'] },
          ];

      const statusFilters = statusTypes.flatMap((type) =>
        type.values.map((value) => ({
          key: `${type.field}:${value}`,
          label: this.statusText[type.field.replace('_status', '')][value],
          count: this.filterCountByStatus(type.field, value),
        }))
      );

      // Tính số lượng cho mục "Tất cả" dựa trên điều kiện màn hình
      let allOrdersCount = this.allOrders.length;
      if (this.route.path.includes('/delivery')) {
        allOrdersCount = this.allOrders.filter((order) =>
          ['shipping', 'delivered'].includes(order.shipping_status)
        ).length;
      } else if (this.route.path.includes('/returns')) {
        allOrdersCount = this.allOrders.filter(
          (order) => order.shipping_status === 'return' || order.order_status === 'canceled'
        ).length;
      }

      return [
        { key: 'all', label: 'Tất cả', count: allOrdersCount },
        ...statusFilters,
      ];
    },
    filteredOrders() {
      const q = this.searchQuery.toLowerCase();
      return this.allOrders.filter((order) => {
        const matchQuery = (order.buyer?.email || '').toLowerCase().includes(q);

        let matchFilter = false;
        if (this.currentFilter === 'all') {
          // Áp dụng điều kiện mặc định theo màn hình
          if (this.route.path.includes('/delivery')) {
            matchFilter = ['shipping', 'delivered'].includes(order.shipping_status);
          } else if (this.route.path.includes('/returns')) {
            matchFilter = order.shipping_status === 'return' || order.order_status === 'canceled';
          } else {
            matchFilter = true; // Màn Quản lý đơn hàng: hiển thị tất cả
          }
        } else if (this.currentFilter.includes(':')) {
          // Áp dụng bộ lọc trạng thái cụ thể
          const [field, value] = this.currentFilter.split(':');
          matchFilter = order[field] === value;
        }

        return matchQuery && matchFilter;
      });
    },
  },
  methods: {
    async fetchOrders() {
      try {
        const token = localStorage.getItem('token');
        if (!token) {
          alert('Vui lòng đăng nhập lại');
          return;
        }

        const response = await axios.get('http://localhost:8000/api/seller/orders', {
          params: this.filterParams,
          headers: { Authorization: `Bearer ${token}` },
        });

        this.orders = response.data.data || [];
        this.allOrders = [...this.orders];
        console.log('Fetched seller orders:', JSON.stringify(this.orders, null, 2));
      } catch (error) {
        console.error('Error fetching orders:', error);
        alert('Không thể tải dữ liệu: ' + (error.response?.data?.message || 'Lỗi hệ thống'));
      }
    },
    filterCountByStatus(field, value) {
      return this.allOrders.filter((order) => order[field] === value).length;
    },
    formatCurrency(amount) {
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(Number(amount) || 0);
    },
    formatDate(date) {
      if (!date) return 'N/A';
      return new Intl.DateTimeFormat('vi-VN', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(date));
    },
    openDetailModal(order) {
      this.selectedOrder = { ...order };
      this.showOrderDetailsModal = true;
      console.log('Opening modal for order:', order.id, 'items:', JSON.stringify(order.items, null, 2));
    },
    closeDetailModal() {
      this.showOrderDetailsModal = false;
      this.selectedOrder = null;
    },
    applySearch() {
      console.log('Apply Seller Search:', this.searchQuery);
    },
  },
  watch: {
    'route.path': {
      handler() {
        this.fetchOrders();
        this.currentFilter = 'all';
        this.searchQuery = '';
      },
      immediate: true,
    },
  },
  mounted() {
    this.fetchOrders();
  },
};
</script>