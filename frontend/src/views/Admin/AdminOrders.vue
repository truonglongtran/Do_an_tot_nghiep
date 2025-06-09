<template>
  <div class="p-8 space-y-6">
    <h1 class="text-2xl font-bold">Quản lý đơn hàng</h1>
    <div class="flex justify-between items-center">
      <FilterSearch
        :filters="filters"
        :searchPlaceholder="'Tìm kiếm theo email người mua/người bán...'"
        v-model:currentFilter="currentFilter"
        v-model:searchQuery="searchQuery"
        @search="applySearch"
      />
    </div>
    <p v-if="filteredOrders.length === 0" class="text-center text-gray-500">
      Không tìm thấy đơn hàng nào.
    </p>
    <table v-else class="min-w-full table-auto border border-gray-300">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-4 py-2 border">STT</th>
          <th class="px-4 py-2 border">Người mua</th>
          <th class="px-4 py-2 border">Người bán</th>
          <th class="px-4 py-2 border">Tổng tiền</th>
          <th class="px-4 py-2 border">Thanh toán</th>
          <th class="px-4 py-2 border">Vận chuyển</th>
          <th class="px-4 py-2 border">Đơn hàng</th>
          <th class="px-4 py-2 border">Hành động</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="(order, index) in filteredOrders"
          :key="order.id"
          class="hover:bg-gray-50 transition"
        >
          <td class="px-4 py-2 border text-center">{{ index + 1 }}</td>
          <td class="px-4 py-2 border text-center">{{ order.buyer?.email || 'N/A' }}</td>
          <td class="px-4 py-2 border text-center">{{ order.seller?.email || 'N/A' }}</td>
          <td class="px-4 py-2 border text-center">{{ formatCurrency(order.total_amount) }}</td>
          <td class="px-4 py-2 border text-center">
            <select
              v-if="hasPermission('updateStatus')"
              v-model="tempStatuses[order.id].settled_status"
              @change="confirmUpdateStatus(order, 'settled_status', $event.target.value)"
              class="border rounded px-2 py-1"
              :disabled="isUpdating"
            >
              <option value="" disabled>Chọn trạng thái</option>
              <option value="unsettled">Chưa thanh toán</option>
              <option value="settled">Đã thanh toán</option>
            </select>
            <span v-else>{{ statusText.settled[order.settled_status] || 'N/A' }}</span>
          </td>
          <td class="px-4 py-2 border text-center">
            <select
              v-if="hasPermission('updateStatus')"
              v-model="tempStatuses[order.id].shipping_status"
              @change="confirmUpdateStatus(order, 'shipping_status', $event.target.value)"
              class="border rounded px-2 py-1"
              :disabled="isUpdating"
            >
              <option value="" disabled>Chọn trạng thái</option>
              <option value="pending">Chờ xử lý</option>
              <option value="processing">Đang xử lý</option>
              <option value="shipping">Đang giao</option>
              <option value="delivered">Đã giao</option>
              <option value="failed">Thất bại</option>
              <option value="return">Trả hàng</option>
            </select>
            <span v-else>{{ statusText.shipping[order.shipping_status] || 'N/A' }}</span>
          </td>
          <td class="px-4 py-2 border text-center">
            <select
              v-if="hasPermission('updateStatus')"
              v-model="tempStatuses[order.id].order_status"
              @change="confirmUpdateStatus(order, 'order_status', $event.target.value)"
              class="border rounded px-2 py-1"
              :disabled="isUpdating"
            >
              <option value="" disabled>Chọn trạng thái</option>
              <option value="pending">Chờ xác nhận</option>
              <option value="paid">Đã thanh toán</option>
              <option value="canceled">Đã hủy</option>
            </select>
            <span v-else>{{ statusText.order[order.order_status] || 'N/A' }}</span>
          </td>
          <td class="px-4 py-2 border text-center">
            <button
              @click="openDetailModal(order)"
              class="text-blue-600 hover:underline mr-2"
              :disabled="isUpdating"
            >
              Chi tiết
            </button>
            <button
              v-if="hasPermission('delete')"
              @click="openConfirmModal('delete', order)"
              class="text-red-600 hover:underline"
              :disabled="isUpdating"
            >
              Xóa
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Modal xác nhận -->
    <ConfirmModal
      :show="showConfirmModal"
      :title="confirmTitle"
      :message="confirmMessage"
      :confirmText="'Xác nhận'"
      :cancelText="'Hủy'"
      @confirm="handleConfirm"
      @cancel="handleCancel"
    />

    <!-- Modal chi tiết đơn hàng -->
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
import { useRouter } from 'vue-router';
import FilterSearch from './component/AdminFilterSearch.vue';
import ConfirmModal from './component/AdminConfirmModal.vue';
import GenericDetailsModal from './component/GenericDetailsModal.vue';

export default {
  name: 'AdminOrders',
  components: { FilterSearch, ConfirmModal, GenericDetailsModal },
  setup() {
    const router = useRouter();
    return { router };
  },
  data() {
    return {
      orders: [],
      allOrders: [],
      tempStatuses: {},
      searchQuery: '',
      currentFilter: 'all',
      statusText: {
        settled: {
          unsettled: 'Chưa thanh toán',
          settled: 'Đã thanh toán',
        },
        shipping: {
          pending: 'Chờ xử lý',
          processing: 'Đang xử lý',
          shipping: 'Đang giao',
          delivered: 'Đã giao',
          failed: 'Thất bại',
          return: 'Trả hàng',
        },
        order: {
          pending: 'Chờ xác nhận',
          paid: 'Đã thanh toán',
          canceled: 'Đã hủy',
        },
      },
      showConfirmModal: false,
      confirmAction: null,
      confirmOrder: null,
      confirmTitle: 'Xác nhận',
      confirmMessage: '',
      statusField: null,
      newStatus: null,
      originalStatus: null,
      showOrderDetailsModal: false,
      selectedOrder: null,
      isUpdating: false,
      orderFields: [
        { label: 'Người mua', key: 'buyer.email', type: 'text' },
        { label: 'Người bán', key: 'seller.email', type: 'text' },
        {
          label: 'Tổng tiền',
          key: 'total_amount',
          type: 'custom',
          customFormat: (value) => this.formatCurrency(value),
        },
        {
          label: 'Thanh toán',
          key: 'settled_status',
          type: 'custom',
          customFormat: (value) => this.statusText.settled[value] || 'N/A',
        },
        {
          label: 'Vận chuyển',
          key: 'shipping_status',
          type: 'custom',
          customFormat: (value) => this.statusText.shipping[value] || 'N/A',
        },
        {
          label: 'Đơn hàng',
          key: 'order_status',
          type: 'custom',
          customFormat: (value) => this.statusText.order[value] || 'N/A',
        },
        {
          label: 'Ngày tạo',
          key: 'created_at',
          type: 'date',
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
                    <th class="px-4 py-2 border-b bg-gray-50 text-left">Màu</th>
                    <th class="px-4 py-2 border-b bg-gray-50 text-left relative">Kích cỡ</th>
                    <th class="px-4 py-2 border-b bg-gray-50 text-center">Số lượng</th>
                    <th class="px-4 py-2 border-b bg-gray-50 text-right">Giá</th>
                  </tr>
                </thead>
                <tbody>
                  ${items
                    .map(
                      (item) => `
                        <tr>
                          <td class="px-4 py-2 border">${item.product?.name || 'N/A'}</td>
                          <td class="px-4 py-2 border">${item.product_variant?.color || 'N/A'}</td>
                          <td class="px-4 py-2 border">${item.product_variant?.size || 'N/A'}</td>
                          <td class="px-4 py-2 border text-center">${item.quantity || 0}</td>
                          <td class="px-4 py-2 border text-right">${this.formatCurrency(item.product_variant?.price || 0)}</td>
                        </tr>
                      `
                    )
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
    filters() {
      const statusTypes = [
        { field: 'settled_status', values: ['unsettled', 'settled'] },
        { field: 'shipping_status', values: ['pending', 'processing', 'shipping', 'delivered', 'failed', 'return'] },
        { field: 'order_status', values: ['pending', 'paid', 'canceled'] },
      ];
      const statusFilters = statusTypes.flatMap((type) =>
        type.values.map((value) => ({
          key: `${type.field}:${value}`,
          label: this.statusText[type.field.replace('_status', '')][value],
          count: this.filterCountByStatus(type.field, value),
        }))
      );
      return [
        { key: 'all', label: 'Tất cả', count: this.allOrders.length },
        ...statusFilters,
      ];
    },
    filteredOrders() {
      const q = this.searchQuery.toLowerCase();
      return this.allOrders.filter((order) => {
        const matchQuery =
          (order.buyer?.email || '').toLowerCase().includes(q) ||
          (order.seller?.email || '').toLowerCase().includes(q);
        const matchFilter =
          this.currentFilter === 'all' ||
          (this.currentFilter.includes(':') &&
            order[this.currentFilter.split(':')[0]] === this.currentFilter.split(':')[1]);
        return matchQuery && matchFilter;
      });
    },
  },
  async mounted() {
    console.log('Mounted AdminOrders, Role:', localStorage.getItem('role'));
    console.log('Has view permission:', this.hasPermission('view'));
    await this.fetchOrders();
  },
  methods: {
    hasPermission(action) {
      const role = localStorage.getItem('role');
      const matchedRoute = this.router.getRoutes().find((r) => r.path === '/admin/orders');
      if (!matchedRoute || !matchedRoute.meta || !matchedRoute.meta.permissions) {
        console.warn('Không tìm thấy meta.permissions cho /admin/orders');
        return false;
      }
      const hasPermission = matchedRoute.meta.permissions[role]?.includes(action) || false;
      console.log(`Quyền ${action} cho role ${role}:`, hasPermission);
      return hasPermission;
    },
    async fetchOrders() {
      const token = localStorage.getItem('token');
      console.log('Token:', token);
      try {
        if (!token) {
          throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        }
        const response = await axios.get('/admin/orders', {
          headers: { Authorization: `Bearer ${token}` },
        });
        console.log('Phản hồi API:', response.data);
        if (!Array.isArray(response.data)) {
          console.error('Dữ liệu trả về không phải mảng:', response.data);
          throw new Error('Dữ liệu đơn hàng không đúng định dạng.');
        }
        this.orders = response.data.map((order) => {
          const settledStatus = order.settled_status && ['unsettled', 'settled'].includes(order.settled_status) ? order.settled_status : 'unsettled';
          const shippingStatus = order.shipping_status && ['pending', 'processing', 'shipping', 'delivered', 'failed', 'return'].includes(order.shipping_status) ? order.shipping_status : 'pending';
          const orderStatus = order.order_status && ['pending', 'paid', 'canceled'].includes(order.order_status) ? order.order_status : 'pending';
          const result = {
            ...order,
            settled_status: settledStatus,
            shipping_status: shippingStatus,
            order_status: orderStatus,
          };
          this.tempStatuses[order.id] = {
            settled_status: settledStatus,
            shipping_status: shippingStatus,
            order_status: orderStatus,
          };
          return result;
        });
        this.allOrders = [...this.orders];
      } catch (error) {
        console.error('Lỗi khi tải danh sách đơn hàng:', error.response?.data || error.message);
        alert('Không thể tải danh sách đơn hàng: ' + (error.message || 'Lỗi không xác định.'));
        if (error.response?.status === 401 || error.response?.status === 403) {
          console.warn('Lỗi xác thực hoặc quyền, chuyển hướng về login');
          this.$router.push('/admin/login');
        }
      }
    },
    async updateStatus(order) {
      this.isUpdating = true;
      const token = localStorage.getItem('token');
      if (!this.hasPermission('updateStatus')) {
        alert('Bạn không có quyền cập nhật trạng thái.');
        this.tempStatuses[order.id][this.statusField] = this.originalStatus;
        this.isUpdating = false;
        return;
      }
      try {
        if (!this.newStatus) {
          throw new Error('Trạng thái mới không hợp lệ');
        }
        const payload = { [this.statusField]: this.newStatus };
        const endpoint = `/admin/orders/${order.id}/${this.statusField.replace('_status', '-status')}`;
        const response = await axios.put(endpoint, payload, {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.orders = this.orders.map((o) => (o.id === order.id ? { ...o, ...response.data.order } : o));
        this.allOrders = this.allOrders.map((o) => (o.id === order.id ? { ...o, ...response.data.order } : o));
        this.tempStatuses[order.id][this.statusField] = this.newStatus;
        alert('Cập nhật trạng thái thành công');
      } catch (error) {
        console.error('Lỗi cập nhật trạng thái:', error.response?.data || error.message);
        alert('Cập nhật trạng thái thất bại: ' + (error.response?.data?.message || error.message));
        this.tempStatuses[order.id][this.statusField] = this.originalStatus;
      } finally {
        this.isUpdating = false;
      }
    },
    async deleteOrder(orderId) {
      this.isUpdating = true;
      const token = localStorage.getItem('token');
      if (!this.hasPermission('delete')) {
        alert('Bạn không có quyền xóa đơn hàng.');
        this.isUpdating = false;
        return;
      }
      try {
        await axios.delete(`/admin/orders/${orderId}`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.orders = this.orders.filter((order) => order.id !== orderId);
        this.allOrders = this.allOrders.filter((order) => order.id !== orderId);
        delete this.tempStatuses[orderId];
        alert('Đã xóa đơn hàng thành công');
      } catch (error) {
        console.error('Lỗi khi xóa đơn hàng:', error.response?.data || error.message);
        alert('Xóa đơn hàng thất bại');
        if (error.response?.status === 403) {
          alert('Bạn không có quyền xóa đơn hàng.');
        }
      } finally {
        this.isUpdating = false;
      }
    },
    confirmUpdateStatus(order, field, newStatus) {
      if (!newStatus || newStatus === order[field]) {
        this.tempStatuses[order.id][field] = order[field];
        return;
      }
      newStatus = String(newStatus).trim().toLowerCase();
      const validStatuses = {
        settled_status: ['unsettled', 'settled'],
        shipping_status: ['pending', 'processing', 'shipping', 'delivered', 'failed', 'return'],
        order_status: ['pending', 'paid', 'canceled'],
      }[field];
      if (!validStatuses.includes(newStatus)) {
        alert(`Vui lòng chọn trạng thái hợp lệ cho ${this.getStatusLabel(field)}.`);
        this.tempStatuses[order.id][field] = order[field];
        return;
      }
      this.openConfirmModal('status', order, field, newStatus);
    },
    openConfirmModal(action, order, field = null, newStatus = null) {
      this.confirmAction = action;
      this.confirmOrder = order;
      if (action === 'delete') {
        this.confirmTitle = 'Xác nhận xóa';
        this.confirmMessage = `Bạn có chắc chắn muốn xóa đơn hàng #${order.id} không?`;
      } else if (action === 'status') {
        this.statusField = field;
        this.originalStatus = order[field];
        this.newStatus = newStatus;
        this.confirmTitle = 'Xác nhận đổi trạng thái';
        this.confirmMessage = `Bạn có chắc chắn muốn đổi trạng thái ${this.getStatusLabel(field)} của đơn hàng #${order.id} thành "${this.statusText[field.replace('_status', '')][newStatus]}" không?`;
      }
      this.showConfirmModal = true;
    },
    async handleConfirm() {
      if (this.confirmAction === 'delete') {
        await this.deleteOrder(this.confirmOrder.id);
      } else if (this.confirmAction === 'status') {
        await this.updateStatus(this.confirmOrder);
      }
      this.resetModal();
    },
    handleCancel() {
      if (this.confirmAction === 'status' && this.confirmOrder) {
        this.tempStatuses[this.confirmOrder.id][this.statusField] = this.originalStatus;
      }
      this.resetModal();
    },
    resetModal() {
      this.showConfirmModal = false;
      this.confirmAction = null;
      this.confirmOrder = null;
      this.confirmTitle = 'Xác nhận';
      this.confirmMessage = '';
      this.statusField = null;
      this.newStatus = null;
      this.originalStatus = null;
    },
    openDetailModal(order) {
      this.selectedOrder = { ...order };
      this.showOrderDetailsModal = true;
    },
    closeDetailModal() {
      this.showOrderDetailsModal = false;
      this.selectedOrder = null;
    },
    filterCountByStatus(field, value) {
      return this.allOrders.filter((order) => order[field] === value).length;
    },
    getStatusLabel(field) {
      return {
        settled_status: 'thanh toán',
        shipping_status: 'vận chuyển',
        order_status: 'đơn hàng',
      }[field];
    },
    formatCurrency(amount) {
      if (!amount || isNaN(amount)) return '0 ₫';
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    },
    formatDate(date) {
      if (!date) return 'N/A';
      return new Intl.DateTimeFormat('vi-VN', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(date));
    },
    applySearch() {
      console.log('Apply Order Search:', this.searchQuery);
    },
  },
};
</script>

<style scoped>
.transition-transform {
  transition: transform 0.3s ease;
}
.transition {
  transition: background-color 0.3s ease;
}
</style>