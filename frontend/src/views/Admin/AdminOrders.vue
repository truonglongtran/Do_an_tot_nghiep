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
              v-model="tempStatuses[order.id].settled_status"
              @change="confirmUpdateStatus(order, 'settled_status', $event.target.value)"
              class="border rounded px-2 py-1"
              :disabled="isUpdating"
            >
              <option value="" disabled>Chọn trạng thái</option>
              <option value="unsettled">Chưa thanh toán</option>
              <option value="settled">Đã thanh toán</option>
            </select>
          </td>
          <td class="px-4 py-2 border text-center">
            <select
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
          </td>
          <td class="px-4 py-2 border text-center">
            <select
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
          </td>
          <td class="px-4 py-2 border text-center">
            <button
              @click="viewOrderDetails(order)"
              class="text-blue-600 hover:underline mr-2"
              :disabled="isUpdating"
            >
              Chi tiết
            </button>
            <button
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
    <div
      v-if="showOrderDetailsModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center"
    >
      <div class="bg-white p-6 rounded-lg max-w-2xl w-full">
        <h2 class="text-xl font-bold mb-4">Chi tiết đơn hàng #{{ selectedOrder.id }}</h2>
        <p><strong>Người mua:</strong> {{ selectedOrder.buyer?.email || 'N/A' }}</p>
        <p><strong>Người bán:</strong> {{ selectedOrder.seller?.email || 'N/A' }}</p>
        <p><strong>Tổng tiền:</strong> {{ formatCurrency(selectedOrder.total_amount) }}</p>
        <p><strong>Thanh toán:</strong> {{ statusText.settled[selectedOrder.settled_status] }}</p>
        <p><strong>Vận chuyển:</strong> {{ statusText.shipping[selectedOrder.shipping_status] }}</p>
        <p><strong>Đơn hàng:</strong> {{ statusText.order[selectedOrder.order_status] }}</p>
        <p><strong>Ngày tạo:</strong> {{ formatDate(selectedOrder.created_at) }}</p>
        <h3 class="font-semibold mt-4">Sản phẩm</h3>
        <table class="min-w-full border">
          <thead>
            <tr>
              <th class="px-4 py-2 border">Sản phẩm</th>
              <th class="px-4 py-2 border">Màu</th>
              <th class="px-4 py-2 border">Kích cỡ</th>
              <th class="px-4 py-2 border">Số lượng</th>
              <th class="px-4 py-2 border">Giá</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in selectedOrder.items" :key="item.id">
              <td class="px-4 py-2 border">{{ item.product?.name || 'N/A' }}</td>
              <td class="px-4 py-2 border">{{ item.product_variant?.color || 'N/A' }}</td>
              <td class="px-4 py-2 border">{{ item.product_variant?.size || 'N/A' }}</td>
              <td class="px-4 py-2 border text-center">{{ item.quantity }}</td>
              <td class="px-4 py-2 border text-right">{{ formatCurrency(item.product_variant?.price || 0) }}</td>
            </tr>
          </tbody>
        </table>
        <button
          @click="showOrderDetailsModal = false"
          class="mt-4 bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700"
          :disabled="isUpdating"
        >
          Đóng
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import FilterSearch from './component/AdminFilterSearch.vue';
import ConfirmModal from './component/AdminConfirmModal.vue';

export default {
  name: 'AdminOrders',
  components: { FilterSearch, ConfirmModal },
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
    };
  },
  computed: {
    filters() {
      const statusTypes = [
        { field: 'settled_status', values: ['unsettled', 'settled'] },
        { field: 'shipping_status', values: ['pending', 'processing', 'shipping', 'delivered', 'failed', 'return'] },
        { field: 'order_status', values: ['pending', 'paid', 'canceled'] },
      ];
      const statusFilters = statusTypes.flatMap(type =>
        type.values.map(value => ({
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
      return this.allOrders.filter(order => {
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
    await this.fetchOrders();
  },
  methods: {
    async fetchOrders() {
      const token = localStorage.getItem('token');
      try {
        if (!token) {
          throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        }
        const response = await axios.get('http://localhost:8000/api/admin/orders', {
          headers: { Authorization: `Bearer ${token}` },
        });
        console.log('Raw API Response:', response.data);
        this.orders = response.data.map(order => {
          const settledStatus = order.settled_status && ['unsettled', 'settled'].includes(order.settled_status) ? order.settled_status : 'unsettled';
          const shippingStatus = order.shipping_status && ['pending', 'processing', 'shipping', 'delivered', 'failed', 'return'].includes(order.shipping_status) ? order.shipping_status : 'pending';
          const orderStatus = order.order_status && ['pending', 'paid', 'canceled'].includes(order.order_status) ? order.order_status : 'pending';
          const result = {
            ...order,
            settled_status: settledStatus,
            shipping_status: shippingStatus,
            order_status: orderStatus,
          };
          // Khởi tạo tempStatuses cho đơn hàng (thay this.$set)
          this.tempStatuses[order.id] = {
            settled_status: settledStatus,
            shipping_status: shippingStatus,
            order_status: orderStatus,
          };
          console.log('Processed Order:', {
            id: order.id,
            settled_status: result.settled_status,
            shipping_status: result.shipping_status,
            order_status: result.order_status,
            tempStatuses: this.tempStatuses[order.id],
          });
          return result;
        });
        this.allOrders = [...this.orders];
        console.log('Fetched Orders:', this.orders);
      } catch (error) {
        console.error('Lỗi khi tải danh sách đơn hàng:', error.response?.data || error.message);
        alert('Không thể tải danh sách đơn hàng.');
        if (error.response?.status === 401) {
          this.$router.push('/admin/login');
        }
      }
    },
    async updateStatus(order) {
      this.isUpdating = true;
      const token = localStorage.getItem('token');
      try {
        if (!this.newStatus) {
          throw new Error('Trạng thái mới không hợp lệ');
        }
        const payload = {
          [this.statusField]: this.newStatus,
        };
        const endpoint = `http://localhost:8000/api/admin/orders/${order.id}/${this.statusField.replace('_status', '-status')}`;
        console.log('Update Status Payload:', { orderId: order.id, endpoint, payload });
        const response = await axios.put(endpoint, payload, {
          headers: { Authorization: `Bearer ${token}` },
        });
        console.log('Update Status Response:', response.data);
        // Cập nhật orders và allOrders
        this.orders = this.orders.map(o => o.id === order.id ? { ...o, ...response.data.order } : o);
        this.allOrders = this.allOrders.map(o => o.id === order.id ? { ...o, ...response.data.order } : o);
        // Cập nhật tempStatuses (thay this.$set)
        this.tempStatuses[order.id][this.statusField] = this.newStatus;
        alert('Cập nhật trạng thái thành công');
      } catch (error) {
        console.error('Lỗi cập nhật trạng thái:', error.response?.data || error.message);
        alert('Cập nhật trạng thái thất bại: ' + (error.response?.data?.message || error.message));
        // Khôi phục trạng thái tạm thời (thay this.$set)
        this.tempStatuses[order.id][this.statusField] = this.originalStatus;
      } finally {
        this.isUpdating = false;
      }
    },
    async deleteOrder(orderId) {
      this.isUpdating = true;
      const token = localStorage.getItem('token');
      try {
        await axios.delete(`http://localhost:8000/api/admin/orders/${orderId}`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.orders = this.orders.filter(order => order.id !== orderId);
        this.allOrders = this.allOrders.filter(order => order.id !== orderId);
        // Xóa tempStatuses cho đơn hàng (thay this.$delete)
        delete this.tempStatuses[orderId];
        alert('Đã xóa đơn hàng thành công');
      } catch (error) {
        console.error('Lỗi khi xóa đơn hàng:', error.response?.data || error.message);
        alert('Xóa đơn hàng thất bại');
      } finally {
        this.isUpdating = false;
      }
    },
    confirmUpdateStatus(order, field, newStatus) {
      console.log('Confirm Update Status:', { orderId: order.id, field, newStatus, tempStatuses: this.tempStatuses[order.id] });
      
      // Bỏ qua nếu giá trị không hợp lệ hoặc không thay đổi
      if (!newStatus || newStatus === order[field]) {
        console.log('No change or invalid status, ignoring:', { orderId: order.id, field, newStatus });
        this.tempStatuses[order.id][field] = order[field];
        return;
      }

      // Chuẩn hóa newStatus
      newStatus = String(newStatus).trim().toLowerCase();

      // Xác định danh sách trạng thái hợp lệ
      const validStatuses = {
        settled_status: ['unsettled', 'settled'],
        shipping_status: ['pending', 'processing', 'shipping', 'delivered', 'failed', 'return'],
        order_status: ['pending', 'paid', 'canceled'],
      }[field];

      if (!validStatuses.includes(newStatus)) {
        alert(`Vui lòng chọn trạng thái hợp lệ cho ${this.getStatusLabel(field)}. Các giá trị hợp lệ: ${validStatuses.join(', ')}`);
        this.tempStatuses[order.id][field] = order[field];
        console.log('Invalid status detected:', { orderId: order.id, field, newStatus, validStatuses });
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
        console.log('Open Confirm Modal:', { action, orderId: order.id, field, newStatus, originalStatus: this.originalStatus });
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
        // Khôi phục trạng thái (thay this.$set)
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
    viewOrderDetails(order) {
      this.selectedOrder = order;
      this.showOrderDetailsModal = true;
    },
    filterCountByStatus(field, value) {
      return this.allOrders.filter(order => order[field] === value).length;
    },
    getStatusLabel(field) {
      return {
        settled_status: 'thanh toán',
        shipping_status: 'vận chuyển',
        order_status: 'đơn hàng',
      }[field];
    },
    formatCurrency(amount) {
      if (!amount || isNaN(amount)) {
        return '0 ₫';
      }
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    },
    formatDate(date) {
      return new Intl.DateTimeFormat('vi-VN', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(date));
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