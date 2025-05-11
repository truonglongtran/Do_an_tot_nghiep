<template>
  <div v-if="showConfirmModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white p-6 rounded shadow-md">
      <h2 class="text-xl font-semibold mb-4">Xác nhận</h2>
      <p class="mb-4">{{ confirmMessage }}</p>
      <div class="flex justify-end">
        <button @click="confirmAction" class="bg-red-600 text-white px-4 py-2 rounded mr-2">Xác nhận</button>
        <button @click="cancelAction" class="bg-gray-300 px-4 py-2 rounded">Hủy</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ['showConfirmModal', 'confirmMessage'],
  methods: {
    confirmAction() {
      this.$emit('confirm');
    },
    cancelAction() {
      this.$emit('cancel');
    },
  },
};
</script>