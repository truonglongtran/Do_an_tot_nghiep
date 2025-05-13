<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50"
  >
    <div class="bg-white rounded-lg p-6 w-full max-w-md relative">
      <button
        @click="$emit('close')"
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
      <h3 class="text-lg font-bold mb-4">Thêm admin mới</h3>
      <form @submit.prevent="handleSubmit">
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Email</label>
          <input
            v-model="form.email"
            type="email"
            class="w-full border rounded p-2"
            required
          />
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Mật khẩu</label>
          <input
            v-model="form.password"
            type="password"
            class="w-full border rounded p-2"
            required
          />
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Vai trò</label>
          <select
            v-model="form.role"
            class="w-full border rounded p-2"
            required
          >
            <option value="admin">Admin</option>
            <option value="moderator">Moderator</option>
          </select>
        </div>
        <div class="flex justify-end gap-4">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400"
          >
            Hủy
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
          >
            Thêm
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminForm',
  props: {
    show: { type: Boolean, default: false },
  },
  emits: ['submit', 'close'],
  data() {
    return {
      form: {
        email: '',
        password: '',
        role: 'admin',
      },
    };
  },
  methods: {
    handleSubmit() {
      this.$emit('submit', { ...this.form });
      this.resetForm();
    },
    resetForm() {
      this.form = {
        email: '',
        password: '',
        role: 'admin',
      };
    },
  },
};
</script>

<style scoped>
.fixed { z-index: 1000; }
</style>