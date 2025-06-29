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
          Không có địa chỉ nào được lưu. Vui lòng thêm địa chỉ ở trang quản lý tài khoản.
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
        </div>
      </div>

      <!-- Đơn hàng theo shop -->
      <div v-for="(shopData, shopId) in groupedCarts" :key="shopId" class="border rounded-lg p-4">
        <h2 class="text-lg font-semibold text-gray-700 mb-2">
          Cửa hàng: {{ shopData.carts[0].product_variant?.product?.shop?.shop_name || 'Không xác định' }}
        </h2>

        <!-- Sản phẩm đã chọn -->
        <div class="space-y-4">
          <div v-for="cart in shopData.carts" :key="cart.id" class="flex items-center space-x-4">
            <img
              :src="getImageUrl(cart.product_variant?.image_url)"
              :alt="cart.product_variant?.product?.name || 'Sản phẩm không xác định'"
              class="w-16 h-16 object-cover rounded"
              @error="handleImageError($event, cart.product_variant?.image_url, 'order_create_' + cart.id)"
            />
            <div class="flex-1">
              <p class="font-semibold">{{ cart.product_variant?.product?.name || 'Sản phẩm không xác định' }}</p>
              <p class="text-gray-600">Cửa hàng: {{ cart.product_variant?.product?.shop?.shop_name || 'Không xác định' }}</p>
              <p class="text-orange-500">{{ formatPrice(cart.product_variant?.price || 0) }}đ x {{ cart.quantity }}</p>
            </div>
          </div>
        </div>

        <!-- Phương thức vận chuyển -->
        <div class="mt-4">
          <h3 class="text-md font-semibold text-gray-700 mb-2">Phương thức vận chuyển</h3>
          <div v-if="shopData.shippingMethods.length === 0" class="text-gray-600">
            Không có phương thức vận chuyển khả dụng.
          </div>
          <div v-else>
            <div v-for="method in shopData.shippingMethods" :key="method.id" class="flex items-center space-x-4 mb-2">
              <input
                type="radio"
                :id="'shipping-' + shopId + '-' + method.id"
                v-model="shopData.selectedShippingMethodId"
                :value="method.id"
                class="h-5 w-5 text-orange-500"
                @change="fetchVouchers(shopId)"
              />
              <label :for="'shipping-' + shopId + '-' + method.id" class="flex-1">
                <p class="font-semibold">{{ method.name }}</p>
                <p class="text-gray-600">Phí: {{ formatPrice(method.price) }}đ</p>
                <p class="text-gray-600">{{ method.description }}</p>
              </label>
            </div>
          </div>
        </div>

        <!-- Voucher selection -->
        <div class="mt-4">
          <h3 class="text-md font-semibold text-gray-700 mb-2">Chọn Voucher</h3>
          <div class="space-y-4">
            <div>
              <h4 class="font-semibold">Voucher vận chuyển</h4>
              <select v-model="shopData.selectedVouchers.shipping" @change="calculateTotal(shopId)" class="w-full border rounded p-2">
                <option :value="null">Không sử dụng</option>
                <option v-for="voucher in shopData.shippingVouchers" :key="voucher.id" :value="voucher.id">
                  {{ voucher.code }} - {{ voucher.discount_type === 'fixed' ? formatPrice(voucher.discount_value) + 'đ' : voucher.discount_value + '%' }}
                </option>
              </select>
            </div>
            <div>
              <h4 class="font-semibold">Voucher sản phẩm</h4>
              <select v-model="shopData.selectedVouchers.product" @change="calculateTotal(shopId)" class="w-full border rounded p-2">
                <option :value="null">Không sử dụng</option>
                <option v-for="voucher in shopData.productVouchers" :key="voucher.id" :value="voucher.id">
                  {{ voucher.code }} - {{ voucher.discount_type === 'fixed' ? formatPrice(voucher.discount_value) + 'đ' : voucher.discount_value + '%' }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <!-- Tổng kết đơn hàng của shop -->
        <div class="mt-4">
          <h3 class="text-md font-semibold text-gray-700 mb-2">Tổng kết</h3>
          <div class="space-y-2">
            <p class="flex justify-between">
              <span>Tổng tiền hàng:</span>
              <span>{{ formatPrice(shopData.totalPrice) }}đ</span>
            </p>
            <p class="flex justify-between">
              <span>Phí vận chuyển:</span>
              <span>{{ formatPrice(shopData.selectedShippingPrice) }}đ</span>
            </p>
            <p class="flex justify-between">
              <span>Giảm giá:</span>
              <span>{{ formatPrice(shopData.totalDiscount) }}đ</span>
            </p>
            <p class="flex justify-between text-lg font-semibold text-orange-500">
              <span>Tổng thanh toán:</span>
              <span>{{ formatPrice(shopData.total) }}đ</span>
            </p>
          </div>
        </div>
      </div>

      <!-- Tổng thanh toán tất cả đơn hàng -->
      <div class="border rounded-lg p-4">
        <h2 class="text-lg font-semibold text-gray-700 mb-2">Tổng thanh toán tất cả</h2>
        <p class="text-lg font-semibold text-orange-500 flex justify-between">
          <span>Tổng cộng:</span>
          <span>{{ formatPrice(totalAllOrders) }}đ</span>
        </p>
        <button
          @click="placeOrders"
          :disabled="!isFormValid || loading"
          class="mt-4 w-full bg-orange-600 text-white px-4 py-2 rounded-lg disabled:bg-gray-300"
        >
          Đặt tất cả đơn hàng
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
      groupedCarts: {}, // { shopId: { carts, owner_id, shippingMethods, selectedShippingMethodId, shippingVouchers, productVouchers, selectedVouchers, totalPrice, selectedShippingPrice, totalDiscount, total } }
      addresses: [],
      selectedAddressId: null,
      selectedPaymentMethod: 'COD',
      loading: true,
      error: null,
    };
  },
  computed: {
    totalAllOrders() {
      return Object.values(this.groupedCarts).reduce((sum, shop) => sum + shop.total, 0);
    },
    isFormValid() {
      return (
        Object.keys(this.groupedCarts).length > 0 &&
        this.selectedAddressId &&
        Object.values(this.groupedCarts).every(shop => shop.selectedShippingMethodId)
      );
    },
  },
  async created() {
    await this.fetchData();
    for (const shopId of Object.keys(this.groupedCarts)) {
      await this.fetchVouchers(shopId);
      this.calculateTotal(shopId);
    }
  },
  methods: {
    getImageUrl(imgUrl) {
      if (!imgUrl) {
        console.warn('Không có đường dẫn ảnh, sử dụng ảnh placeholder');
        return 'https://via.placeholder.com/100?text=Ảnh+Không+Tìm+Thấy';
      }
      if (/^https?:\/\//.test(imgUrl)) {
        console.log('Sử dụng URL bên ngoài:', imgUrl);
        return `${imgUrl}?t=${new Date().getTime()}`;
      }
      const baseUrl = import.meta.env.VITE_STORAGE_BASE_URL || 'http://localhost:8000/storage';
      const cleanImgUrl = imgUrl.replace(/^\/?(storage\/)?/, '');
      const finalUrl = `${baseUrl}/${cleanImgUrl}?t=${new Date().getTime()}`;
      console.log('Đường dẫn ảnh đã tạo:', finalUrl);
      return finalUrl;
    },
    handleImageError(event, imgUrl, type) {
      console.error(`Lỗi tải ảnh ${type}:`, {
        img_url: imgUrl,
        attempted_url: event.target.src,
        storage_base_url: import.meta.env.VITE_STORAGE_BASE_URL,
      });
      event.target.src = 'https://via.placeholder.com/100?text=Ảnh+Không+Tìm+Thấy';
    },
    async fetchData() {
      this.loading = true;
      this.error = null;
      try {
        const cartsByShop = JSON.parse(this.$route.query.cartsByShop || '{}');
        if (Object.keys(cartsByShop).length === 0) {
          this.error = 'Không có sản phẩm nào được chọn.';
          return;
        }

        // Fetch carts for all shops
        const allCartIds = Object.values(cartsByShop).flatMap(shop => shop.cart_ids);
        const cartResponse = await axios.get('/buyer/cart', {
          params: { cart_ids: allCartIds },
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        console.log('Cart response:', JSON.stringify(cartResponse.data, null, 2));
        if (!cartResponse.data.carts || !Array.isArray(cartResponse.data.carts)) {
          this.error = 'Dữ liệu giỏ hàng không hợp lệ.';
          return;
        }

        // Group carts by shop
        this.groupedCarts = Object.keys(cartsByShop).reduce((acc, shopId) => {
          const shop = cartsByShop[shopId];
          const shopCarts = cartResponse.data.carts.filter(cart => shop.cart_ids.includes(cart.id));
          if (shopCarts.length === 0) return acc;

          const invalidCarts = shopCarts.filter(cart => !cart.product_variant?.id || !cart.product_variant?.product?.shop?.owner_id);
          if (invalidCarts.length > 0) {
            console.warn('Giỏ hàng không hợp lệ:', JSON.stringify(invalidCarts, null, 2));
            this.error = 'Một số sản phẩm trong giỏ hàng không hợp lệ. Vui lòng kiểm tra lại.';
            return acc;
          }

          acc[shopId] = {
            owner_id: shop.owner_id,
            carts: shopCarts,
            shippingMethods: [],
            selectedShippingMethodId: null,
            shippingVouchers: [],
            productVouchers: [],
            selectedVouchers: { shipping: null, product: null },
            totalPrice: shopCarts.reduce((sum, cart) => sum + Number(cart.product_variant?.price || 0) * Number(cart.quantity || 0), 0),
            selectedShippingPrice: 15000, // Default
            totalDiscount: 0,
            total: 0,
          };
          return acc;
        }, {});

        if (Object.keys(this.groupedCarts).length === 0) {
          this.error = 'Không tìm thấy sản phẩm được chọn trong giỏ hàng.';
          return;
        }

        // Fetch addresses
        const addressResponse = await axios.get('/buyer/addresses', {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        console.log('Address response:', JSON.stringify(addressResponse.data, null, 2));
        this.addresses = addressResponse.data.addresses || [];
        if (this.addresses.length === 0) {
          this.error = 'Không có địa chỉ nào được lưu. Vui lòng thêm địa chỉ ở trang quản lý tài khoản.';
          return;
        }

        const defaultAddress = this.addresses.find(addr => addr.is_default);
        this.selectedAddressId = defaultAddress ? defaultAddress.id : this.addresses[0]?.id;

        if (!this.selectedAddressId || !this.addresses.some(addr => addr.id === this.selectedAddressId)) {
          this.error = 'Địa chỉ giao hàng không hợp lệ. Vui lòng chọn lại địa chỉ.';
          return;
        }

        // Fetch shipping methods for each shop
        for (const shopId of Object.keys(this.groupedCarts)) {
          const shop = this.groupedCarts[shopId];
          const shippingResponse = await axios.get('/buyer/shipping-methods', {
            params: { owner_id: shop.owner_id },
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
          });
          console.log(`Phản hồi phương thức vận chuyển cho shop ${shopId}:`, shippingResponse.data);
          shop.shippingMethods = shippingResponse.data.methods || [];
          if (shop.shippingMethods.length === 0) {
            this.error = `Không có phương thức vận chuyển khả dụng cho cửa hàng ${shop.carts[0].product_variant?.product?.shop?.shop_name || 'Không xác định'}.`;
            return;
          }
          shop.selectedShippingMethodId = shop.shippingMethods[0]?.id;
          shop.selectedShippingPrice = shop.shippingMethods[0]?.price || 15000;
        }
      } catch (error) {
        console.error('Lỗi tải dữ liệu đơn hàng:', error.response?.data || error.message);
        this.error = error.response?.data?.message || 'Lỗi tải dữ liệu. Vui lòng thử lại.';
      } finally {
        this.loading = false;
      }
    },
    async fetchVouchers(shopId) {
      const shop = this.groupedCarts[shopId];
      if (!shop.owner_id) {
        console.warn(`Không thể tải voucher: owner_id chưa được thiết lập cho shop ${shopId}.`);
        this.error = 'Không thể tải voucher do thiếu thông tin người bán.';
        return;
      }

      try {
        const productIds = shop.carts
          .map(cart => cart.product_variant?.product?.id)
          .filter(id => id !== null && id !== undefined);
        if (productIds.length === 0) {
          console.warn('Không tìm thấy ID sản phẩm hợp lệ.');
          this.error = 'Không tìm thấy sản phẩm hợp lệ để tải voucher.';
          return;
        }

        const response = await axios.get('/buyer/vouchers/available', {
          params: {
            owner_id: shop.owner_id,
            product_ids: productIds,
            subtotal: shop.totalPrice,
          },
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        console.log(`Phản hồi voucher cho shop ${shopId}:`, response.data);
        shop.shippingVouchers = response.data.shipping_vouchers || [];
        shop.productVouchers = response.data.product_vouchers || [];
      } catch (error) {
        console.error(`Lỗi khi tải voucher cho shop ${shopId}:`, error.response?.data || error.message);
        this.error = error.response?.data?.message || 'Lỗi tải voucher. Vui lòng thử lại.';
      }
    },
    calculateTotal(shopId) {
      const shop = this.groupedCarts[shopId];
      shop.totalDiscount = 0;
      shop.total = shop.totalPrice + shop.selectedShippingPrice;

      if (shop.selectedVouchers.shipping) {
        const voucher = shop.shippingVouchers.find(v => v.id === shop.selectedVouchers.shipping);
        if (voucher) {
          if (voucher.discount_type === 'fixed') {
            shop.totalDiscount += Math.min(shop.selectedShippingPrice, voucher.discount_value);
          } else {
            shop.totalDiscount += (shop.selectedShippingPrice * voucher.discount_value) / 100;
          }
        }
      }

      if (shop.selectedVouchers.product) {
        const voucher = shop.productVouchers.find(v => v.id === shop.selectedVouchers.product);
        if (voucher) {
          if (voucher.discount_type === 'fixed') {
            shop.totalDiscount += voucher.discount_value;
          } else {
            shop.totalDiscount += (shop.totalPrice * voucher.discount_value) / 100;
          }
        }
      }

      shop.total = shop.totalPrice + shop.selectedShippingPrice - shop.totalDiscount;
      if (shop.total < 0) shop.total = 0;
      this.$forceUpdate();
    },
    async placeOrders() {
      if (!this.isFormValid) {
        this.error = 'Vui lòng chọn đầy đủ thông tin địa chỉ và phương thức vận chuyển cho tất cả cửa hàng.';
        return;
      }

      this.loading = true;
      this.error = null;
      try {
        const ordersData = Object.values(this.groupedCarts).map(shop => ({
          cart_ids: shop.carts.map(cart => cart.id),
          address_id: this.selectedAddressId,
          shipping_method_id: shop.selectedShippingMethodId,
          payment_method: this.selectedPaymentMethod,
          shipping_voucher_id: shop.selectedVouchers.shipping,
          product_voucher_id: shop.selectedVouchers.product,
        }));
        console.log('Orders data sent:', JSON.stringify(ordersData, null, 2));
        const response = await axios.post('/buyer/orders/bulk-create', ordersData, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        console.log('Orders created:', response.data);
        // Redirect to OrderSuccess.vue with the success message
        this.$router.push({
          path: '/buyer/order-success',
          query: { message: response.data.message },
        });
      } catch (error) {
        console.error('Error placing orders:', error.response?.data || error);
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