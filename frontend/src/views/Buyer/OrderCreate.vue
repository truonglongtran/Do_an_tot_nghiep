<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-orange-500 mb-4">Tạo đơn hàng</h1>
    <div v-if="loading" class="text-center">
      <svg class="animate-spin w-8 h-8 mx-auto text-orange-500" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
      </svg>
    </div>
    <div v-else-if="error" class="text-red-500 text-center mb-4">
      {{ error }}
    </div>
    <div v-else class="space-y-6">
      <!-- Địa chỉ nhận hàng -->
      <div class="border rounded-lg p-4">
        <h2 class="text-lg font-semibold text-gray-700 mb-2">Địa chỉ nhận hàng</h2>
        <div v-if="addresses.length === 0" class="text-gray-600">
          Chưa có địa chỉ. Vui lòng thêm địa chỉ mới.
          <router-link to="/addresses" class="text-orange-500 hover:underline">Thêm địa chỉ</router-link>
        </div>
        <div v-else>
          <div v-for="address in addresses" :key="address.id" class="flex items-start space-x-4 mb-2">
            <input
              type="radio"
              :id="'address-' + address.id"
              v-model="selectedAddressId"
              :value="address.id"
              class="h-5 w-5 text-orange-500 mt-1"
            />
            <label :for="'address-' + address.id" class="flex-1">
              <p class="font-semibold">{{ address.recipient_name }} {{ address.is_default ? '(Mặc định)' : '' }}</p>
              <p class="text-gray-600">Số điện thoại: {{ address.phone_number }}</p>
              <p class="text-gray-600">{{ address.address_line }}, {{ address.ward }}, {{ address.district }}, {{ address.city }}</p>
            </label>
          </div>
          <router-link to="/addresses" class="text-orange-500 hover:underline">Thay đổi địa chỉ</router-link>
        </div>
      </div>

      <!-- Sản phẩm đã chọn -->
      <div class="border rounded-lg p-4">
        <h2 class="text-lg font-semibold text-gray-700 mb-2">Sản phẩm</h2>
        <div v-if="selectedCarts.length === 0" class="text-gray-600">
          Không có sản phẩm nào được chọn.
        </div>
        <div v-else class="space-y-4">
          <div v-for="cart in selectedCarts" :key="cart.id" class="flex items-center space-x-4">
            <img
              :src="cart.product_variant?.image_url || 'https://via.placeholder.com/100'"
              :alt="cart.product_variant?.product?.name || 'Sản phẩm không xác định'"
              class="w-16 h-16 object-cover rounded"
            />
            <div class="flex-1">
              <p class="font-semibold">{{ cart.product_variant?.product?.name || 'Sản phẩm không xác định' }}</p>
              <p class="text-gray-600">Cửa hàng: {{ cart.product_variant?.product?.shop?.shop_name || 'Không xác định' }}</p>
              <p class="text-orange-500">{{ formatPrice(cart.product_variant?.price || 0) }}đ x {{ cart.quantity }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Phương thức vận chuyển -->
      <div class="border rounded-lg p-4">
        <h2 class="text-lg font-semibold text-gray-700 mb-2">Phương thức vận chuyển</h2>
        <div v-if="shippingMethods.length === 0" class="text-gray-600">
          Không có phương thức vận chuyển khả dụng.
        </div>
        <div v-else>
          <div v-for="method in shippingMethods" :key="method.id" class="flex items-center space-x-4 mb-2">
            <input
              type="radio"
              :id="'shipping-' + method.id"
              v-model="selectedShippingMethodId"
              :value="method.id"
              class="h-5 w-5 text-orange-500"
              @change="fetchVouchers"
            />
            <label :for="'shipping-' + method.id" class="flex-1">
              <p class="font-semibold">{{ method.name }}</p>
              <p class="text-gray-600">Phí: {{ formatPrice(method.price) }}đ</p>
              <p class="text-gray-600">{{ method.description }}</p>
            </label>
          </div>
        </div>
      </div>

      <!-- Voucher selection -->
      <div class="border rounded-lg p-4">
        <h2 class="text-lg font-semibold text-gray-700 mb-2">Chọn Voucher</h2>
        <div class="space-y-4">
          <div>
            <h3 class="font-semibold">Voucher vận chuyển</h3>
            <select v-model="selectedVouchers.shipping" @change="calculateTotal" class="w-full border rounded p-2">
              <option :value="null">Không sử dụng</option>
              <option v-for="voucher in shippingVouchers" :key="voucher.id" :value="voucher.id">
                {{ voucher.code }} - {{ voucher.discount_type === 'fixed' ? formatPrice(voucher.discount_value) + 'đ' : voucher.discount_value + '%' }}
              </option>
            </select>
          </div>
          <div>
            <h3 class="font-semibold">Voucher sản phẩm</h3>
            <select v-model="selectedVouchers.product" @change="calculateTotal" class="w-full border rounded p-2">
              <option :value="null">Không sử dụng</option>
              <option v-for="voucher in productVouchers" :key="voucher.id" :value="voucher.id">
                {{ voucher.code }} - {{ voucher.discount_type === 'fixed' ? formatPrice(voucher.discount_value) + 'đ' : voucher.discount_value + '%' }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Phương thức thanh toán -->
      <div class="border rounded-lg p-4">
        <h2 class="text-lg font-semibold text-gray-700 mb-2">Phương thức thanh toán</h2>
        <div class="flex items-center space-x-4">
          <input
            type="radio"
            id="payment-cod"
            v-model="selectedPaymentMethod"
            value="COD"
            class="h-5 w-5 text-orange-500"
            checked
          />
          <label for="payment-cod" class="flex-1">
            <p class="font-semibold">Thanh toán khi nhận hàng (COD)</p>
            <p class="text-gray-600">Trả tiền mặt khi nhận hàng</p>
          </label>
        </div>
      </div>

      <!-- Tổng kết -->
      <div class="border rounded-lg p-4">
        <h2 class="text-lg font-semibold text-gray-700 mb-2">Tổng kết</h2>
        <div class="space-y-2">
          <p class="flex justify-between">
            <span>Tổng tiền hàng:</span>
            <span>{{ formatPrice(totalPrice) }}đ</span>
          </p>
          <p class="flex justify-between">
            <span>Phí vận chuyển:</span>
            <span>{{ formatPrice(selectedShippingPrice) }}đ</span>
          </p>
          <p class="flex justify-between">
            <span>Giảm giá:</span>
            <span>{{ formatPrice(totalDiscount) }}đ</span>
          </p>
          <p class="flex justify-between text-lg font-semibold text-orange-500">
            <span>Tổng thanh toán:</span>
            <span>{{ formatPrice(total) }}đ</span>
          </p>
        </div>
        <button
          @click="placeOrder"
          :disabled="!isFormValid || loading"
          class="mt-4 w-full bg-orange-600 text-white px-4 py-2 rounded-lg disabled:bg-gray-300"
        >
          Đặt hàng ngay
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'OrderCreate',
  data() {
    return {
      owner_id: null,
      selectedCarts: [],
      addresses: [],
      selectedAddressId: null,
      shippingMethods: [],
      selectedShippingMethodId: null,
      shippingVouchers: [],
      productVouchers: [],
      selectedVouchers: {
        shipping: null,
        product: null,
      },
      selectedPaymentMethod: 'COD',
      loading: true,
      error: null,
      totalDiscount: 0,
      total: 0,
    };
  },
  computed: {
    totalPrice() {
      return this.selectedCarts.reduce((sum, cart) => {
        const price = Number(cart.product_variant?.price || 0);
        const quantity = Number(cart.quantity || 0);
        return sum + price * quantity;
      }, 0);
    },
    selectedShippingPrice() {
      const method = this.shippingMethods.find(m => m.id === this.selectedShippingMethodId);
      return method ? Number(method.price) : 15000;
    },
    isFormValid() {
      return (
        this.selectedCarts.length > 0 &&
        this.selectedAddressId &&
        this.selectedShippingMethodId &&
        this.selectedPaymentMethod === 'COD'
      );
    },
  },
  async created() {
    await this.fetchData();
    if (this.owner_id && this.selectedCarts.length > 0) {
      await this.fetchVouchers();
      this.calculateTotal();
    }
  },
  methods: {
    async fetchData() {
      this.loading = true;
      this.error = null;
      try {
        const cartIds = this.$route.query.cartIds ? this.$route.query.cartIds.split(',').map(Number) : [];
        if (cartIds.length === 0) {
          this.error = 'Không có sản phẩm nào được chọn.';
          return;
        }

        const cartResponse = await axios.get('/buyer/cart', {
          params: { cart_ids: cartIds },
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        console.log('Cart response:', JSON.stringify(cartResponse.data, null, 2));
        if (!cartResponse.data.carts || !Array.isArray(cartResponse.data.carts)) {
          this.error = 'Dữ liệu giỏ hàng không hợp lệ.';
          return;
        }

        this.selectedCarts = cartResponse.data.carts.filter(cart => cartIds.includes(cart.id));
        if (this.selectedCarts.length === 0) {
          this.error = 'Không tìm thấy sản phẩm được chọn trong giỏ hàng.';
          return;
        }

        const invalidCarts = this.selectedCarts.filter(cart => !cart.product_variant?.id);
        if (invalidCarts.length > 0) {
          console.warn('Giỏ hàng không hợp lệ:', JSON.stringify(invalidCarts, null, 2));
          this.error = 'Một số sản phẩm trong giỏ hàng không hợp lệ. Vui lòng kiểm tra lại.';
          return;
        }

        const ownerIds = [...new Set(this.selectedCarts.map(cart => {
          if (!cart.product_variant?.product?.shop?.owner_id) {
            console.warn('Mục giỏ hàng không hợp lệ:', JSON.stringify(cart, null, 2));
            return null;
          }
          return cart.product_variant.product.shop.owner_id;
        }).filter(id => id !== null))];

        if (ownerIds.length !== 1) {
          this.error = ownerIds.length === 0 ? 'Không tìm thấy người bán cho sản phẩm.' : 'Tất cả sản phẩm phải thuộc cùng một người bán.';
          return;
        }
        this.owner_id = ownerIds[0];

        const addressResponse = await axios.get('/buyer/addresses', {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        console.log('Address response:', JSON.stringify(addressResponse.data, null, 2));
        this.addresses = addressResponse.data.addresses || [];
        if (this.addresses.length === 0) {
          this.error = 'Vui lòng thêm địa chỉ giao hàng trước khi tạo đơn hàng.';
          return;
        }

        const defaultAddress = this.addresses.find(addr => addr.is_default);
        this.selectedAddressId = defaultAddress ? defaultAddress.id : this.addresses[0]?.id;

        if (!this.selectedAddressId || !this.addresses.some(addr => addr.id === this.selectedAddressId)) {
          this.error = 'Địa chỉ giao hàng không hợp lệ. Vui lòng chọn lại địa chỉ.';
          return;
        }

        const shippingResponse = await axios.get('/buyer/shipping-methods', {
          params: { owner_id: this.owner_id },
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        console.log('Phản hồi phương thức vận chuyển:', shippingResponse.data);
        this.shippingMethods = shippingResponse.data.methods || [];
        if (this.shippingMethods.length === 0) {
          this.error = 'Không có phương thức vận chuyển khả dụng cho người bán này.';
          return;
        }
        this.selectedShippingMethodId = this.shippingMethods[0]?.id;
      } catch (error) {
        console.error('Lỗi tải dữ liệu đơn hàng:', error.response?.data || error.message);
        this.error = error.response?.data?.message || 'Lỗi tải dữ liệu. Vui lòng thử lại.';
      } finally {
        this.loading = false;
      }
    },
    async fetchVouchers() {
      if (!this.owner_id) {
        console.warn('Không thể tải voucher: owner_id chưa được thiết lập.');
        this.error = 'Không thể tải voucher do thiếu thông tin người bán.';
        return;
      }

      try {
        const productIds = this.selectedCarts
          .map(cart => cart.product_variant?.product?.id)
          .filter(id => id !== null && id !== undefined);
        if (productIds.length === 0) {
          console.warn('Không tìm thấy ID sản phẩm hợp lệ.');
          this.error = 'Không tìm thấy sản phẩm hợp lệ để tải voucher.';
          return;
        }

        const response = await axios.get('/buyer/vouchers/available', {
          params: {
            owner_id: this.owner_id,
            product_ids: productIds,
            subtotal: this.totalPrice,
          },
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        console.log('Phản hồi voucher:', response.data);
        this.shippingVouchers = response.data.shipping_vouchers || [];
        this.productVouchers = response.data.product_vouchers || [];
      } catch (error) {
        console.error('Lỗi khi tải voucher:', error.response?.data || error.message);
        this.error = error.response?.data?.message || 'Lỗi tải voucher. Vui lòng thử lại.';
      }
    },
    calculateTotal() {
      this.totalDiscount = 0;
      this.total = this.totalPrice + this.selectedShippingPrice;

      if (this.selectedVouchers.shipping) {
        const voucher = this.shippingVouchers.find(v => v.id === this.selectedVouchers.shipping);
        if (voucher) {
          if (voucher.discount_type === 'fixed') {
            this.totalDiscount += Math.min(this.selectedShippingPrice, voucher.discount_value);
          } else {
            this.totalDiscount += (this.selectedShippingPrice * voucher.discount_value) / 100;
          }
        }
      }

      if (this.selectedVouchers.product) {
        const voucher = this.productVouchers.find(v => v.id === this.selectedVouchers.product);
        if (voucher) {
          if (voucher.discount_type === 'fixed') {
            this.totalDiscount += voucher.discount_value;
          } else {
            this.totalDiscount += (this.totalPrice * voucher.discount_value) / 100;
          }
        }
      }

      this.total = this.totalPrice + this.selectedShippingPrice - this.totalDiscount;
      if (this.total < 0) this.total = 0;
    },
    async placeOrder() {
      if (!this.isFormValid) {
        this.error = 'Vui lòng chọn đầy đủ thông tin địa chỉ, phương thức vận chuyển và thanh toán.';
        return;
      }

      this.loading = true;
      this.error = null;
      try {
        const orderData = {
          cart_ids: this.selectedCarts.map(cart => cart.id),
          address_id: this.selectedAddressId,
          shipping_method_id: this.selectedShippingMethodId,
          payment_method: this.selectedPaymentMethod,
          shipping_voucher_id: this.selectedVouchers.shipping,
          product_voucher_id: this.selectedVouchers.product,
        };
        console.log('Order data sent:', JSON.stringify(orderData, null, 2));
        const response = await axios.post('/buyer/orders/create', orderData, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        console.log('Order created:', response.data);
        this.$router.push('/buyer/orders');
      } catch (error) {
        console.error('Error placing order:', error.response?.data || error);
        this.error = error.response?.data?.error || 'Lỗi khi tạo đơn hàng. Vui lòng thử lại.';
      } finally {
        this.loading = false;
      }
    },
    formatPrice(price) {
      return Number(price).toLocaleString('vi-VN');
    },
  },
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #f97316;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #ea580c;
}
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: #f97316 #f1f1f1;
}
</style>