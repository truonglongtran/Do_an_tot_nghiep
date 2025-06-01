<template>
  <div class="p-8 space-y-6">
    <h1 class="text-2xl font-bold">Quản lý khiếu nại</h1>
    <div class="flex justify-between items-center">
      <FilterSearch
        :filters="filters"
        :searchPlaceholder="'Tìm kiếm theo lý do...'"
        v-model:currentFilter="currentFilter"
        v-model:searchQuery="searchQuery"
        @search="applySearch"
      />
    </div>
    <p v-if="filteredDisputes.length === 0" class="text-center text-gray-500">
      Không tìm thấy khiếu nại nào.
    </p>
    <table v-else class="min-w-full table-auto border border-gray-300">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-4 py-2 border">STT</th>
          <th class="px-4 py-2 border">Mã đơn hàng</th>
          <th class="px-4 py-2 border">Người mua</th>
          <th class="px-4 py-2 border">Người bán</th>
          <th class="px-4 py-2 border">Lý do</th>
          <th class="px-4 py-2 border">Trạng thái</th>
          <th class="px-4 py-2 border">Hành động</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="(dispute, index) in filteredDisputes"
          :key="dispute.id"
          class="hover:bg-gray-50 transition"
        >
          <td class="px-4 py-2 border text-center">{{ index + 1 }}</td>
          <td class="px-4 py-2 border text-center">{{ dispute.order_id || 'N/A' }}</td>
          <td class="px-4 py-2 border text-center">{{ dispute.buyer?.email || 'N/A' }}</td>
          <td class="px-4 py-2 border text-center">{{ dispute.seller?.email || 'N/A' }}</td>
          <td class="px-4 py-2 border text-center">{{ dispute.reason || 'N/A' }}</td>
          <td class="px-4 py-2 border text-center">
            <select
              v-model="dispute.tempStatus"
              @change="confirmToggleStatus(dispute)"
              class="border rounded px-2 py-1"
              :class="{
                'text-yellow-600': dispute.tempStatus === 'open',
                'text-green-600': dispute.tempStatus === 'resolved',
                'text-red-600': dispute.tempStatus === 'rejected'
              }"
            >
              <option value="open">Mở</option>
              <option value="resolved">Đã giải quyết</option>
              <option value="rejected">Từ chối</option>
            </select>
          </td>
          <td class="px-4 py-2 border text-center space-x-2">
            <button
              @click="openDetailsModal(dispute)"
              class="text-blue-600 hover:underline"
            >
              Chi tiết
            </button>
            <button
              @click="openConfirmModal('delete', dispute)"
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

    <!-- Modal chi tiết -->
    <GenericDetailsModal
      :show="showDetailsModal"
      :data="selectedDispute"
      :fields="disputeFields"
      title="Chi tiết khiếu nại"
      @close="closeDetailsModal"
    />
  </div>
</template>

<script>
import axios from 'axios';
import FilterSearch from './component/AdminFilterSearch.vue';
import ConfirmModal from './component/AdminConfirmModal.vue';
import GenericDetailsModal from './component/GenericDetailsModal.vue';

export default {
  name: 'AdminDisputes',
  components: { FilterSearch, ConfirmModal, GenericDetailsModal },
  data() {
    return {
      disputes: [],
      allDisputes: [],
      searchQuery: '',
      currentFilter: 'all',
      statusText: {
        open: 'Mở',
        resolved: 'Đã giải quyết',
        rejected: 'Từ chối',
      },
      showConfirmModal: false,
      confirmAction: null,
      confirmDispute: null,
      confirmTitle: 'Xác nhận',
      confirmMessage: '',
      newStatus: null,
      originalStatus: null,
      showDetailsModal: false,
      selectedDispute: null,
      disputeFields: [
        { label: 'ID', key: 'id', type: 'text' },
        { label: 'Mã đơn hàng', key: 'order_id', type: 'text' },
        { label: 'Người mua', key: 'buyer.email', type: 'text' },
        { label: 'Người bán', key: 'seller.email', type: 'text' },
        { label: 'Lý do', key: 'reason', type: 'text' },
        {
          label: 'Trạng thái',
          key: 'status',
          type: 'custom',
          customFormat: (value) => this.statusText[value] || 'N/A',
        },
        { label: 'Ghi chú quản trị', key: 'admin_note', type: 'text' },
        { label: 'Ngày tạo', key: 'created_at', type: 'date' },
        { label: 'Ngày cập nhật', key: 'updated_at', type: 'date' },
      ],
    };
  },
  computed: {
    filters() {
      const statusFilters = [
        { key: 'all', label: 'Tất cả', count: this.allDisputes.length },
        ...['open', 'resolved', 'rejected'].map((s) => ({
          key: s,
          label: this.statusText[s],
          count: this.filterCountByStatus(s),
        })),
      ];
      return statusFilters;
    },
    filteredDisputes() {
      const q = this.searchQuery.toLowerCase();
      console.log('Filtering Disputes:', q, 'Filter:', this.currentFilter);
      return this.allDisputes.filter((dispute) => {
        const matchQuery =
          (dispute.reason || '').toLowerCase().includes(q) ||
          (dispute.buyer?.email || '').toLowerCase().includes(q) ||
          (dispute.seller?.email || '').toLowerCase().includes(q);
        const matchFilter =
          this.currentFilter === 'all' || dispute.status === this.currentFilter;
        return matchQuery && matchFilter;
      });
    },
  },
  async mounted() {
    await this.fetchDisputes();
  },
  methods: {
    async fetchDisputes() {
      const token = localStorage.getItem('token');
      try {
        if (!token) {
          throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        }
        const response = await axios.get('http://localhost:8000/api/admin/disputes', {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.disputes = response.data.map((dispute) => ({
          ...dispute,
          tempStatus: dispute.status,
        }));
        this.allDisputes = this.disputes;
        console.log('Disputes:', this.disputes);
      } catch (error) {
        console.error('Lỗi khi tải danh sách khiếu nại:', error);
        alert('Không thể tải danh sách khiếu nại.');
        if (error.response?.status === 401) {
          this.$router.push('/admin/login');
        }
      }
    },
    async updateStatus(dispute) {
      const token = localStorage.getItem('token');
      try {
        await axios.put(
          `http://localhost:8000/api/admin/disputes/${dispute.id}/status`,
          { status: this.newStatus },
          { headers: { Authorization: `Bearer ${token}` } }
        );
        dispute.status = this.newStatus;
        dispute.tempStatus = this.newStatus;
        alert('Cập nhật trạng thái khiếu nại thành công');
      } catch (error) {
        console.error('Lỗi cập nhật trạng thái:', error);
        alert('Cập nhật trạng thái thất bại');
        dispute.status = this.originalStatus;
        dispute.tempStatus = this.originalStatus;
      }
    },
    async updateAdminNote(dispute, note) {
      const token = localStorage.getItem('token');
      try {
        await axios.put(
          `http://localhost:8000/api/admin/disputes/${dispute.id}/status`,
          { status: dispute.status, admin_note: note },
          { headers: { Authorization: `Bearer ${token}` } }
        );
        dispute.admin_note = note;
        alert('Cập nhật ghi chú thành công');
      } catch (error) {
        console.error('Lỗi cập nhật ghi chú:', error);
        alert('Cập nhật ghi chú thất bại');
      }
    },
    async deleteDispute(disputeId) {
      const token = localStorage.getItem('token');
      try {
        await axios.delete(`http://localhost:8000/api/admin/disputes/${disputeId}`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.disputes = this.disputes.filter((dispute) => dispute.id !== disputeId);
        this.allDisputes = this.allDisputes.filter((dispute) => dispute.id !== disputeId);
        alert('Đã xóa khiếu nại thành công');
      } catch (error) {
        console.error('Lỗi khi xóa khiếu nại:', error);
        alert('Xóa khiếu nại thất bại');
      }
    },
    confirmToggleStatus(dispute) {
      const newStatus = dispute.tempStatus;
      dispute.tempStatus = dispute.status;
      this.openConfirmModal('status', dispute, newStatus);
    },
    openConfirmModal(action, dispute, newStatus = null) {
      this.confirmAction = action;
      this.confirmDispute = dispute;
      if (action === 'delete') {
        this.confirmTitle = 'Xác nhận xóa';
        this.confirmMessage = `Bạn có chắc chắn muốn xóa khiếu nại với lý do "${dispute.reason || 'N/A'}" không?`;
      } else if (action === 'status') {
        this.originalStatus = dispute.status;
        this.newStatus = newStatus;
        this.confirmTitle = 'Xác nhận đổi trạng thái';
        this.confirmMessage = `Bạn có chắc chắn muốn đổi trạng thái khiếu nại "${dispute.reason || 'N/A'}" thành "${this.statusText[newStatus]}" không?`;
      }
      this.showConfirmModal = true;
    },
    async handleConfirm() {
      if (this.confirmAction === 'delete') {
        await this.deleteDispute(this.confirmDispute.id);
      } else if (this.confirmAction === 'status') {
        await this.updateStatus(this.confirmDispute);
      }
      this.resetModal();
    },
    handleCancel() {
      if (this.confirmAction === 'status' && this.confirmDispute) {
        this.confirmDispute.status = this.originalStatus;
        this.confirmDispute.tempStatus = this.originalStatus;
      }
      this.resetModal();
    },
    resetModal() {
      this.showConfirmModal = false;
      this.confirmAction = null;
      this.confirmDispute = null;
      this.confirmTitle = 'Xác nhận';
      this.confirmMessage = '';
      this.newStatus = null;
      this.originalStatus = null;
    },
    openDetailsModal(dispute) {
      this.selectedDispute = dispute;
      this.showDetailsModal = true;
    },
    closeDetailsModal() {
      this.showDetailsModal = false;
      this.selectedDispute = null;
    },
    applySearch() {
      console.log('Apply Dispute Search:', this.searchQuery);
    },
    filterCountByStatus(status) {
      const count = this.allDisputes.filter((dispute) => dispute.status === status).length;
      console.log(`Count Status (${status}):`, count);
      return count;
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