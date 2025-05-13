<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50"
  >
    <div class="bg-white rounded-lg p-6 w-full max-w-md relative">
      <!-- Nút đóng -->
      <button
        @click="handleCancel"
        class="absolute top-2 right-2 text-gray-500 hover:text-gray-700"
        aria-label="Đóng"
      >
        <svg
          class="w-5 h-5"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M6 18L18 6M6 6l12 12"
          ></path>
        </svg>
      </button>
      <!-- Tiêu đề -->
      <h3 class="text-lg font-bold mb-4">{{ title }}</h3>
      <!-- Nội dung -->
      <p class="mb-6 text-gray-600">{{ message }}</p>
      <!-- Nút hành động -->
      <div class="flex justify-end gap-4">
        <button
          @click="handleCancel"
          class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition"
        >
          {{ cancelText }}
        </button>
        <button
          @click="handleConfirm"
          class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition"
        >
          {{ confirmText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ConfirmModal',
  props: {
    show: {
      type: Boolean,
      required: true,
    },
    title: {
      type: String,
      default: 'Xác nhận',
    },
    message: {
      type: String,
      required: true,
    },
    confirmText: {
      type: String,
      default: 'Xác nhận',
    },
    cancelText: {
      type: String,
      default: 'Hủy',
    },
  },
  emits: ['confirm', 'cancel'],
  methods: {
    handleConfirm() {
      this.$emit('confirm');
    },
    handleCancel() {
      this.$emit('cancel');
    },
  },
};
</script>

<style scoped>
/* Đảm bảo modal có z-index cao */
.fixed {
  z-index: 1000;
}
</style>