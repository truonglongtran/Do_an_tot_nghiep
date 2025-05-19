<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
  >
    <div class="bg-white rounded-lg p-6 w-full max-w-lg">
      <h2 class="text-xl font-bold mb-4">Chi tiết khiếu nại</h2>
      <div class="space-y-4">
        <p><strong>ID:</strong> {{ dispute?.id || 'N/A' }}</p>
        <p><strong>Mã đơn hàng:</strong> {{ dispute?.order_id || 'N/A' }}</p>
        <p><strong>Người mua:</strong> {{ dispute?.buyer?.email || 'N/A' }}</p>
        <p><strong>Người bán:</strong> {{ dispute?.seller?.email || 'N/A' }}</p>
        <p><strong>Lý do:</strong> {{ dispute?.reason || 'N/A' }}</p>
        <p><strong>Trạng thái:</strong> {{ statusText[dispute?.status] || 'N/A' }}</p>
        <p><strong>Ghi chú quản trị:</strong> {{ dispute?.admin_note || 'N/A' }}</p>
        <p><strong>Ngày tạo:</strong> {{ formatDate(dispute?.created_at) || 'N/A' }}</p>
        <p><strong>Ngày cập nhật:</strong> {{ formatDate(dispute?.updated_at) || 'N/A' }}</p>
      </div>
      <div class="mt-6 flex justify-end">
        <button
          @click="$emit('close')"
          class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600"
        >
          Đóng
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DisputeDetailsModal',
  props: {
    show: Boolean,
    dispute: Object,
  },
  data() {
    return {
      statusText: {
        open: 'Mở',
        resolved: 'Đã giải quyết',
        rejected: 'Từ chối',
      },
    };
  },
  methods: {
    formatDate(date) {
      if (!date) return 'N/A';
      return new Date(date).toLocaleString('vi-VN');
    },
  },
};
</script>