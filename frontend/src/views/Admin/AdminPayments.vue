<template>
  <div class="p-8 space-y-6">
    <h1 class="text-2xl font-bold">Quản lý thanh toán</h1>
    <div class="flex justify-between items-center">
      <FilterSearch
        :filters="filters"
        :searchPlaceholder="'Tìm kiếm theo ID đơn hàng...'"
        v-model:currentFilter="currentFilter"
        v-model:searchQuery="searchQuery"
        @search="applySearch"
      />
    </div>
    <GenericDetailsModal
      :show="showDetailModal"
      :data="selectedPayment"
      :fields="paymentFields"
      title="Chi tiết thanh toán"
      @close="closeDetailModal"
    />
    <p v-if="filteredPayments.length === 0" class="text-center text-gray-500">
      Không tìm thấy thanh toán nào.
    </p>
    <table v-else class="min-w-full table-auto border border-gray-300">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-4 py-2 border">STT</th>
          <th class="px-4 py-2 border">ID Đơn hàng</th>
          <th class="px-4 py-2 border">Số tiền</th>
          <th class="px-4 py-2 border">Phương thức</th>
          <th class="px-4 py-2 border">Trạng thái</th>
          <th class="px-4 py-2 border">Hành động</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="(payment, index) in filteredPayments"
          :key="payment.id"
          class="hover:bg-gray-50 transition"
        >
          <td class="px-4 py-2 border text-center">{{ index + 1 }}</td>
          <td class="px-4 py-2 border text-center">{{ payment.order_id || 'N/A' }}</td>
          <td class="px-4 py-2 border text-center">{{ formatAmount(payment.amount) }}</td>
          <td class="px-4 py-2 border text-center capitalize">{{ payment.payment_method || 'N/A' }}</td>
          <td class="px-4 py-2 border text-center">
            <div class="flex items-center justify-center">
              <select
                v-if="hasPermission('updateStatus')"
                v-model="payment.tempStatus"
                @change="confirmToggleStatus(payment)"
                class="border border-gray-300 rounded-md p-1 focus:ring-blue-500 focus:border-blue-500 status-select"
                :class="{
                  'text-green-600': payment.tempStatus === 'success',
                  'text-red-600': payment.tempStatus === 'failed',
                  'text-yellow-600': payment.tempStatus === 'refund'
                }"
              >
                <option value="success">Thành công</option>
                <option value="failed">Thất bại</option>
                <option value="refund">Hoàn tiền</option>
              </select>
              <span
                v-else
                :class="{
                  'text-green-600': payment.status === 'success',
                  'text-red-600': payment.status === 'failed',
                  'text-yellow-600': payment.status === 'refund'
                }"
              >
                {{ statusText[payment.status] || 'N/A' }}
              </span>
            </div>
          </td>
          <td class="px-4 py-2 border text-center space-x-2">
            <button
              @click="openDetailModal(payment)"
              class="text-blue-600 hover:underline"
            >
              Chi tiết
            </button>
            <button
              v-if="hasPermission('update')"
              @click="openEditForm(payment)"
              class="text-green-600 hover:underline"
            >
              Sửa
            </button>
            <button
              v-if="hasPermission('delete')"
              @click="openConfirmModal('delete', payment)"
              class="text-red-600 hover:underline"
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
  </div>
</template>

<script>
import axios from 'axios';
import { useRouter } from 'vue-router';
import FilterSearch from './component/AdminFilterSearch.vue';
import ConfirmModal from './component/AdminConfirmModal.vue';
import GenericDetailsModal from './component/GenericDetailsModal.vue';

export default {
  name: 'AdminPayments',
  components: { FilterSearch, ConfirmModal, GenericDetailsModal },
  setup() {
    const router = useRouter();
    return { router };
  },
  data() {
    return {
      payments: [],
      allPayments: [],
      searchQuery: '',
      currentFilter: 'all',
      paymentMethods: [],
      statusText: {
        success: 'Thành công',
        failed: 'Thất bại',
        refund: 'Hoàn tiền',
      },
      showConfirmModal: false,
      confirmAction: null,
      confirmPayment: null,
      confirmTitle: 'Xác nhận',
      confirmMessage: '',
      newStatus: null,
      originalStatus: null,
      showFormModal: false,
      editingPayment: null,
      isViewMode: false,
      showDetailModal: false,
      selectedPayment: null,
      paymentFields: [
        { label: 'ID Đơn hàng', key: 'order_id', type: 'text' },
        {
          label: 'Số tiền',
          key: 'amount',
          type: 'custom',
          customFormat: (value) => this.formatAmount(value),
        },
        { label: 'Phương thức', key: 'payment_method', type: 'text' },
        {
          label: 'Trạng thái',
          key: 'status',
          type: 'custom',
          customFormat: (value) => this.statusText[value] || 'N/A',
        },
      ],
    };
  },
  computed: {
    filters() {
      const statusFilters = [
        { key: 'all', label: 'Tất cả', count: this.allPayments.length },
        ...['success', 'failed', 'refund'].map((s) => ({
          key: s,
          label: this.statusText[s],
          count: this.filterCountByStatus(s),
        })),
      ];
      const methodFilters = this.paymentMethods.map((m) => ({
        key: m,
        label: m.charAt(0).toUpperCase() + m.slice(1),
        count: this.filterCountByMethod(m),
      }));
      return [...statusFilters, ...methodFilters];
    },
    filteredPayments() {
      const q = this.searchQuery.toLowerCase();
      return this.allPayments.filter((payment) => {
        const matchQuery = String(payment.order_id || '').toLowerCase().includes(q);
        const matchFilter =
          this.currentFilter === 'all' ||
          payment.status === this.currentFilter ||
          payment.payment_method === this.currentFilter;
        return matchQuery && matchFilter;
      });
    },
  },
  async mounted() {
    console.log('Mounted AdminPayments, Role:', localStorage.getItem('role'));
    console.log('Has view permission:', this.hasPermission('view'));
    await this.fetchPayments();
  },
  methods: {
    hasPermission(action) {
      const role = localStorage.getItem('role');
      const matchedRoute = this.router.getRoutes().find((r) => r.path === '/admin/payments');
      if (!matchedRoute || !matchedRoute.meta || !matchedRoute.meta.permissions) {
        console.warn('Không tìm thấy meta.permissions cho /admin/payments');
        return false;
      }
      const hasPermission = matchedRoute.meta.permissions[role]?.includes(action) || false;
      console.log(`Quyền ${action} cho role ${role}:`, hasPermission);
      return hasPermission;
    },
    async fetchPayments() {
      const token = localStorage.getItem('token');
      console.log('Token:', token);
      try {
        if (!token) {
          throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        }
        const response = await axios.get('http://localhost:8000/api/admin/payments', {
          headers: { Authorization: `Bearer ${token}` },
        });
        console.log('Phản hồi API:', response.data);
        if (!Array.isArray(response.data)) {
          console.error('Dữ liệu trả về không phải mảng:', response.data);
          throw new Error('Dữ liệu thanh toán không đúng định dạng.');
        }
        this.payments = response.data.map((payment) => ({
          ...payment,
          order_id: payment.order_id || '',
          amount: payment.amount || null,
          payment_method: payment.payment_method || '',
          status: payment.status || '',
          tempStatus: payment.status || '',
        }));
        this.allPayments = this.payments;
        this.paymentMethods = this.extractPaymentMethods(response.data);
      } catch (error) {
        console.error('Lỗi khi tải danh sách thanh toán:', error);
        alert('Không thể tải danh sách thanh toán: ' + (error.message || 'Lỗi không xác định.'));
        if (error.response?.status === 401 || error.response?.status === 403) {
          console.warn('Lỗi xác thực hoặc quyền, chuyển hướng về login');
          this.$router.push('/admin/login');
        }
      }
    },
    extractPaymentMethods(payments) {
      const set = new Set(payments.map((payment) => payment.payment_method).filter(Boolean));
      return Array.from(set);
    },
    async updateStatus(payment) {
      const token = localStorage.getItem('token');
      if (!this.hasPermission('updateStatus')) {
        alert('Bạn không có quyền cập nhật trạng thái.');
        payment.tempStatus = this.originalStatus;
        return;
      }
      try {
        await axios.put(
          `http://localhost:8000/api/admin/payments/${payment.id}/status`,
          { status: this.newStatus },
          { headers: { Authorization: `Bearer ${token}` } }
        );
        payment.status = this.newStatus;
        payment.tempStatus = this.newStatus;
        alert('Cập nhật trạng thái thành công');
      } catch (error) {
        console.error('Lỗi cập nhật trạng thái:', error);
        alert('Cập nhật trạng thái thất bại: ' + (error.response?.data?.message || error.message));
        payment.tempStatus = this.originalStatus;
      }
    },
    async deletePayment(paymentId) {
      const token = localStorage.getItem('token');
      if (!this.hasPermission('delete')) {
        alert('Bạn không có quyền xóa thanh toán.');
        return;
      }
      try {
        await axios.delete(`http://localhost:8000/api/admin/payments/${paymentId}`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.payments = this.payments.filter((payment) => payment.id !== paymentId);
        this.allPayments = this.allPayments.filter((payment) => payment.id !== paymentId);
        alert('Đã xóa thanh toán thành công');
      } catch (error) {
        console.error('Lỗi khi xóa thanh toán:', error);
        alert('Xóa thanh toán thất bại: ' + (error.response?.data?.message || error.message));
        if (error.response?.status === 403) {
          alert('Bạn không có quyền xóa thanh toán.');
        }
      }
    },
    confirmToggleStatus(payment) {
      const newStatus = payment.tempStatus;
      if (newStatus === payment.status) return;
      this.openConfirmModal('status', payment, newStatus);
    },
    openConfirmModal(action, payment, newStatus = null) {
      if (action === 'delete' && !this.hasPermission('delete')) {
        alert('Bạn không có quyền xóa thanh toán.');
        return;
      }
      if (action === 'status' && !this.hasPermission('updateStatus')) {
        alert('Bạn không có quyền cập nhật trạng thái.');
        return;
      }
      this.confirmAction = action;
      this.confirmPayment = payment;
      if (action === 'delete') {
        this.confirmTitle = 'Xác nhận xóa';
        this.confirmMessage = `Bạn có chắc chắn muốn xóa thanh toán cho đơn hàng "${payment.order_id || 'N/A'}" không?`;
      } else if (action === 'status') {
        this.originalStatus = payment.status;
        this.newStatus = newStatus;
        this.confirmTitle = 'Xác nhận đổi trạng thái';
        this.confirmMessage = `Bạn có chắc chắn muốn đổi trạng thái thanh toán cho đơn hàng "${
          payment.order_id || 'N/A'
        }" thành "${this.statusText[newStatus]}" không?`;
      }
      this.showConfirmModal = true;
    },
    async handleConfirm() {
      if (this.confirmAction === 'delete') {
        await this.deletePayment(this.confirmPayment.id);
      } else if (this.confirmAction === 'status') {
        await this.updateStatus(this.confirmPayment);
      }
      this.resetModal();
    },
    handleCancel() {
      if (this.confirmAction === 'status' && this.confirmPayment) {
        this.confirmPayment.tempStatus = this.originalStatus;
      }
      this.resetModal();
    },
    resetModal() {
      this.showConfirmModal = false;
      this.confirmAction = null;
      this.confirmPayment = null;
      this.confirmTitle = 'Xác nhận';
      this.confirmMessage = '';
      this.newStatus = null;
      this.originalStatus = null;
    },
    openDetailModal(payment) {
      this.selectedPayment = { ...payment };
      this.showDetailModal = true;
    },
    closeDetailModal() {
      this.showDetailModal = false;
      this.selectedPayment = null;
    },
    openEditForm(payment) {
      if (!this.hasPermission('update')) {
        alert('Bạn không có quyền sửa thanh toán.');
        return;
      }
      this.editingPayment = {
        id: payment.id,
        order_id: payment.order_id || '',
        amount: payment.amount || null,
        payment_method: payment.payment_method || '',
        status: payment.status || '',
      };
      this.isViewMode = false;
      this.showFormModal = true;
    },
    applySearch() {
      // Filtering handled by filteredPayments computed property
    },
    filterCountByStatus(status) {
      return this.allPayments.filter((payment) => payment.status === status).length;
    },
    filterCountByMethod(method) {
      return this.allPayments.filter((payment) => payment.payment_method === method).length;
    },
    formatAmount(amount) {
      if (amount == null) return 'N/A';
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
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
select {
  transition: border-color 0.3s ease;
}
</style>