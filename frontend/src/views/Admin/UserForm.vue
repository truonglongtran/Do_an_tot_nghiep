<!-- src/components/UserForm.vue -->
<template>
  <div class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg w-full max-w-md">
      <h2 class="text-xl font-bold mb-4">
        {{ isEdit ? 'Chỉnh sửa người dùng' : 'Thêm người dùng' }}
      </h2>
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <input
          v-model="form.email"
          type="email"
          placeholder="Email"
          class="border px-3 py-2 rounded w-full"
          required
        />
        <input
          v-model="form.password"
          type="password"
          placeholder="Mật khẩu"
          class="border px-3 py-2 rounded w-full"
          :required="!isEdit"
        />
        <select v-model="form.role" class="border px-3 py-2 rounded w-full">
            <option value="buyer">Người mua</option>
            <option value="seller">Người bán</option>
        </select>
        <div class="flex justify-end gap-2">
          <button
            type="button"
            class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400"
            @click="$emit('close')"
          >
            Hủy
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
          >
            {{ isEdit ? 'Lưu' : 'Thêm' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UserForm',
  props: {
    user: {
      type: Object,
      default: null,
    },
  },
  data() {
    return {
      form: {
        email: '',
        password: '',
        role: 'buyer',
      },
    };
  },
  computed: {
    isEdit() {
      return !!this.user;
    },
  },
  mounted() {
    if (this.user) {
      this.form.email = this.user.email;
      this.form.role = this.user.role;
    }
  },
  methods: {
    handleSubmit() {
      this.$emit('submit', { ...this.form });
    },
  },
};
</script>
