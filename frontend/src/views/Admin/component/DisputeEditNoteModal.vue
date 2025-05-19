<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
  >
    <div class="bg-white rounded-lg p-6 w-full max-w-lg">
      <h2 class="text-xl font-bold mb-4">Sửa ghi chú quản trị</h2>
      <form @submit.prevent="handleSubmit">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Ghi chú quản trị</label>
          <textarea
            v-model="adminNote"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            rows="4"
          ></textarea>
        </div>
        <div class="flex justify-end space-x-2">
          <button
            type="button"
            @click="$emit('close')"
            class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600"
          >
            Hủy
          </button>
          <button
            type="submit"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
          >
            Lưu
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DisputeEditNoteModal',
  props: {
    show: Boolean,
    dispute: Object,
  },
  data() {
    return {
      adminNote: '',
    };
  },
  watch: {
    dispute(newDispute) {
      this.adminNote = newDispute?.admin_note || '';
    },
  },
  methods: {
    handleSubmit() {
      this.$emit('submit', this.adminNote);
    },
  },
};
</script>