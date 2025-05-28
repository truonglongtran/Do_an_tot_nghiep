<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
  >
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
      <h2 class="text-xl font-bold mb-4">
        {{ editingPayment ? 'Cập nhật thanh toán' : 'Thêm thanh toán' }}
      </h2>
      <form @submit.prevent="submitForm" class="space-y-4">
        <div>
          <label for="order_id" class="block text-sm font-medium text-gray-700"
            >ID Đơn hàng</label
          >
          <input
            v-model="form.order_id"
            type="number"
            id="order_id"
            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"
            :class="{ 'border-red-500': errors.order_id }"
            :disabled="readOnly"
            placeholder="Nhập ID đơn hàng"
            required
          />
          <p v-if="errors.order_id" class="text-red-500 text-sm mt-1">
            {{ errors.order_id }}
          </p>
        </div>
        <div>
          <label for="amount" class="block text-sm font-medium text-gray-700"
            >Số tiền (VND)</label
          >
          <input
            v-model.number="form.amount"
            type="number"
            id="amount"
            step="0.01"
            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"
            :class="{ 'border-red-500': errors.amount }"
            :disabled="readOnly"
            placeholder="Nhập số tiền"
            required
          />
          <p v-if="errors.amount" class="text-red-500 text-sm mt-1">
            {{ errors.amount }}
          </p>
        </div>
        <div>
          <label for="payment_method" class="block text-sm font-medium text-gray-700"
            >Phương thức thanh toán</label
          >
          <select
            v-model="form.payment_method"
            id="payment_method"
            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"
            :class="{ 'border-red-500': errors.payment_method }"
            :disabled="readOnly"
            required
          >
            <option value="" disabled>Chọn phương thức</option>
            <option value="cash">Tiền mặt</option>
            <option value="bank_transfer">Chuyển khoản ngân hàng</option>
            <option value="credit_card">Thẻ tín dụng</option>
            <option value="mobile_payment">Thanh toán di động</option>
          </select>
          <p v-if="errors.payment_method" class="text-red-500 text-sm mt-1">
            {{ errors.payment_method }}
          </p>
        </div>
        <div>
          <label for="status" class="block text-sm font-medium text-gray-700"
            >Trạng thái</label
          >
          <select
            v-model="form.status"
            id="status"
            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"
            :class="{ 'border-red-500': errors.status }"
            :disabled="readOnly"
            required
          >
            <option value="" disabled>Chọn trạng thái</option>
            <option value="success">Thành công</option>
            <option value="failed">Thất bại</option>
            <option value="refund">Hoàn tiền</option>
          </select>
          <p v-if="errors.status" class="text-red-500 text-sm mt-1">
            {{ errors.status }}
          </p>
        </div>
        <div class="flex justify-end space-x-2">
          <button
            type="button"
            @click="$emit('close')"
            class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400"
          >
            Hủy
          </button>
          <button
            v-if="!readOnly"
            type="submit"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
          >
            {{ editingPayment ? 'Cập nhật' : 'Thêm' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PaymentForm',
  props: {
    show: {
      type: Boolean,
      required: true,
    },
    payment: {
      type: Object,
      default: null,
    },
    readOnly: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      form: {
        order_id: '',
        amount: null,
        payment_method: '',
        status: '',
      },
      errors: {},
      editingPayment: this.payment,
    };
  },
  created() {
    if (this.payment) {
      this.form = {
        order_id: this.payment.order_id || '',
        amount: this.payment.amount || null,
        payment_method: this.payment.payment_method || '',
        status: this.payment.status || '',
      };
      console.log('Form initialized in created:', this.form);
    }
  },
  watch: {
    payment(newPayment) {
      console.log('Payment prop changed:', newPayment);
      if (newPayment) {
        this.editingPayment = newPayment;
        this.form = {
          order_id: newPayment.order_id || '',
          amount: newPayment.amount || null,
          payment_method: newPayment.payment_method || '',
          status: newPayment.status || '',
        };
        console.log('Form updated in watch:', this.form);
      } else {
        this.resetForm();
      }
    },
    show(newShow) {
      if (!newShow) {
        this.resetForm();
        this.errors = {};
      }
    },
  },
  methods: {
    resetForm() {
      this.form = {
        order_id: '',
        amount: null,
        payment_method: '',
        status: '',
      };
      this.errors = {};
    },
    validateForm() {
      this.errors = {};
      let isValid = true;

      if (!this.form.order_id) {
        this.errors.order_id = 'ID đơn hàng là bắt buộc.';
        isValid = false;
      }
      if (!this.form.amount || this.form.amount <= 0) {
        this.errors.amount = 'Số tiền phải lớn hơn 0.';
        isValid = false;
      }
      if (!this.form.payment_method) {
        this.errors.payment_method = 'Vui lòng chọn phương thức thanh toán.';
        isValid = false;
      }
      if (!this.form.status) {
        this.errors.status = 'Vui lòng chọn trạng thái.';
        isValid = false;
      }

      return isValid;
    },
    submitForm() {
      if (this.validateForm()) {
        this.$emit('submit', { ...this.form });
        this.resetForm();
      }
    },
  },
};
</script>

<style scoped>
.fixed {
  transition: opacity 0.3s ease;
}
input,
select {
  transition: border-color 0.3s ease;
}
.border-red-500 {
  border-color: #ef4444;
}
button {
  transition: background-color 0.3s ease;
}
</style>