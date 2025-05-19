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
    <PaymentForm
      v-if="showFormModal"
      :payment="editingPayment"
      :show="showFormModal"
      :readOnly="isViewMode"
      @close="showFormModal = false"
      @submit="handlePaymentFormSubmit"
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
                v-model="payment.tempStatus"
                @change="confirmToggleStatus(payment)"
                class="border border-gray-300 rounded-md p-1 focus:ring-blue-500 focus:border-blue-500 status-select"
              >
                <option value="success" :class="{'text-green-600': payment.tempStatus === 'success'}">Thành công</option>
                <option value="failed" :class="{'text-red-600': payment.tempStatus === 'failed'}">Thất bại</option>
                <option value="refund" :class="{'text-yellow-600': payment.tempStatus === 'refund'}">Hoàn tiền</option>
              </select>
            </div>
          </td>
          <td class="px-4 py-2 border text-center space-x-2">
            <button
              @click="openViewDetails(payment)"
              class="text-blue-600 hover:underline"
            >
              Chi tiết
            </button>
            <button
              @click="openEditForm(payment)"
              class="text-green-600 hover:underline"
            >
              Sửa
            </button>
            <button
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
import FilterSearch from './component/AdminFilterSearch.vue';
import ConfirmModal from './component/AdminConfirmModal.vue';
import PaymentForm from './component/PaymentForm.vue';

export default {
  name: 'AdminPayments',
  components: { FilterSearch, ConfirmModal, PaymentForm },
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
      const filters = [...statusFilters, ...methodFilters];
      console.log('Payment Filters:', filters);
      return filters;
    },
    filteredPayments() {
      const q = this.searchQuery.toLowerCase();
      console.log('Filtering Payments:', q, 'Filter:', this.currentFilter);
      const result = this.allPayments.filter((payment) => {
        const matchQuery = String(payment.order_id || '').toLowerCase().includes(q);
        const matchFilter =
          this.currentFilter === 'all' ||
          payment.status === this.currentFilter ||
          payment.payment_method === this.currentFilter;
        return matchQuery && matchFilter;
      });
      console.log('Filtered Payments:', result);
      return result;
    },
  },
  async mounted() {
    await this.fetchPayments();
  },
  methods: {
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
        console.log('Payments:', this.payments);
        console.log('All Payments:', this.allPayments);
        console.log('Payment Methods:', this.paymentMethods);
      } catch (error) {
        console.error('Lỗi khi tải danh sách thanh toán:', error);
        alert('Không thể tải danh sách thanh toán.');
        if (error.response?.status === 401) {
          this.$router.push('/admin/login');
        }
      }
    },
    extractPaymentMethods(payments) {
      const set = new Set(payments.map((payment) => payment.payment_method).filter(Boolean));
      const methods = Array.from(set);
      console.log('Payment Methods:', methods);
      return methods;
    },
    async handlePaymentFormSubmit(form) {
      const token = localStorage.getItem('token');
      if (!token) {
        alert('Vui lòng đăng nhập lại.');
        this.$router.push('/admin/login');
        return;
      }
      try {
        const response = await axios.put(
          `http://localhost:8000/api/admin/payments/${this.editingPayment.id}`,
          form,
          { headers: { Authorization: `Bearer ${token}` } }
        );
        Object.assign(this.editingPayment, response.data);
        this.showFormModal = false;
        alert('Cập nhật thanh toán thành công');
      } catch (error) {
        console.error('Lỗi khi xử lý form thanh toán:', error);
        if (error.response?.status === 422) {
          const errors = error.response.data.errors;
          let errorMessage = 'Lỗi xác thực:\n';
          for (const field in errors) {
            errorMessage += `- ${field}: ${errors[field].join(', ')}\n`;
          }
          alert(errorMessage);
        } else {
          alert('Lỗi: ' + (error.response?.data?.message || error.message));
        }
      }
    },
    async updateStatus(payment) {
      const token = localStorage.getItem('token');
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
        alert('Cập nhật trạng thái thất bại');
        payment.tempStatus = this.originalStatus;
      }
    },
    async deletePayment(paymentId) {
      const token = localStorage.getItem('token');
      try {
        await axios.delete(`http://localhost:8000/api/admin/payments/${paymentId}`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.payments = this.payments.filter((payment) => payment.id !== paymentId);
        this.allPayments = this.allPayments.filter((payment) => payment.id !== paymentId);
        alert('Đã xóa thanh toán thành công');
      } catch (error) {
        console.error('Lỗi khi xóa thanh toán:', error);
        alert('Xóa thanh toán thất bại');
      }
    },
    confirmToggleStatus(payment) {
      const newStatus = payment.tempStatus;
      if (newStatus === payment.status) return;
      this.openConfirmModal('status', payment, newStatus);
    },
    openConfirmModal(action, payment, newStatus = null) {
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
    openViewDetails(payment) {
      this.editingPayment = {
        id: payment.id,
        order_id: payment.order_id || '',
        amount: payment.amount || null,
        payment_method: payment.payment_method || '',
        status: payment.status || '',
      };
      this.isViewMode = true;
      this.showFormModal = true;
    },
    openEditForm(payment) {
      this.editingPayment = {
        id: payment.id,
        order_id: payment.order_id || '',
        amount: payment.amount || null,
        payment_method: payment.payment_method || '',
        status: payment.status || '',
      };
      console.log('Opening edit form with payment:', this.editingPayment);
      this.isViewMode = false;
      this.showFormModal = true;
    },
    applySearch() {
      console.log('Apply Payment Search:', this.searchQuery);
    },
    filterCountByStatus(status) {
      const count = this.allPayments.filter((payment) => payment.status === status).length;
      console.log(`Count Status (${status}):`, count);
      return count;
    },
    filterCountByMethod(method) {
      const count = this.allPayments.filter((payment) => payment.payment_method === method).length;
      console.log(`Count Method (${method}):`, count);
      return count;
    },
    formatAmount(amount) {
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