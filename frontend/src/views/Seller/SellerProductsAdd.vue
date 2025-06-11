<template>
  <div class="p-6 space-y-6">
    <h1 class="text-2xl font-bold">Thêm sản phẩm</h1>
    
    <form @submit.prevent="submitProduct">
      <!-- Thông tin cơ bản -->
      <div class="border p-4 rounded">
        <h2 class="text-lg font-semibold mb-4">Thông tin cơ bản</h2>
        <div class="space-y-4">
          <div>
            <label class="block">Tên sản phẩm</label>
            <input v-model="form.name" type="text" class="w-full p-2 border rounded" required />
          </div>
          <div>
            <label class="block">Mô tả</label>
            <textarea v-model="form.description" class="w-full p-2 border rounded" rows="5"></textarea>
          </div>
        </div>
      </div>

      <!-- Hình ảnh -->
      <div class="border p-4 rounded">
        <h2 class="text-lg font-semibold mb-4">Hình ảnh</h2>
        <input type="file" multiple @change="handleImageUpload" accept="image/*" class="mb-4" />
        <div class="flex space-x-2">
          <img v-for="url in form.images" :key="url" :src="url" class="w-20 h-20 object-cover" />
        </div>
      </div>

      <!-- Biến thể -->
      <div class="border p-4 rounded">
        <h2 class="text-lg font-semibold mb-4">Biến thể</h2>
        <div class="space-y-4">
          <div>
            <label class="block">Màu sắc (cách nhau bằng dấu phẩy)</label>
            <input v-model="form.colors" type="text" class="w-full p-2 border rounded" placeholder="Đỏ, Xanh, Vàng" />
          </div>
          <div>
            <label class="block">Kích thước (cách nhau bằng dấu phẩy)</label>
            <input v-model="form.sizes" type="text" class="w-full p-2 border rounded" placeholder="S, M, L" />
          </div>
          <button type="button" @click="generateVariants" class="bg-blue-500 text-white p-2 rounded">Tạo biến thể</button>
          <table v-if="form.variants.length" class="min-w-full border">
            <thead>
              <tr>
                <th class="px-4 py-2 border">Màu</th>
                <th class="px-4 py-2 border">Kích thước</th>
                <th class="px-4 py-2 border">SKU</th>
                <th class="px-4 py-2 border">Giá</th>
                <th class="px-4 py-2 border">Tồn kho</th>
                <th class="px-4 py-2 border">Hình ảnh</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(variant, index) in form.variants" :key="index">
                <td class="px-4 py-2 border">{{ variant.color }}</td>
                <td class="px-4 py-2 border">{{ variant.size }}</td>
                <td class="px-4 py-2 border">
                  <input v-model="variant.sku" class="w-full p-1 border" required />
                </td>
                <td class="px-4 py-2 border">
                  <input v-model="variant.price" type="number" class="w-full p-1 border" required />
                </td>
                <td class="px-4 py-2 border">
                  <input v-model="variant.stock" type="number" class="w-full p-1 border" required />
                </td>
                <td class="px-4 py-2 border">
                  <input type="file" @change="e => variant.image = e.target.files[0]" accept="image/*" />
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Vận chuyển -->
      <div class="border p-4 rounded">
        <h2 class="text-lg font-semibold mb-4">Vận chuyển</h2>
        <div v-if="shippingPartners.length === 0" class="text-gray-500">Không có đơn vị vận chuyển.</div>
        <div v-else class="space-y-2">
          <label v-for="partner in shippingPartners" :key="partner.id" class="flex items-center">
            <input type="checkbox" v-model="form.shipping_partners" :value="partner.id" />
            <span class="ml-2">{{ partner.name }}</span>
          </label>
        </div>
      </div>

      <!-- Nút hành động -->
      <div class="flex space-x-4">
        <button type="submit" class="bg-blue-500 text-white p-2 rounded">Đăng bán</button>
        <button type="button" @click="saveDraft" class="bg-gray-500 text-white p-2 rounded">Lưu nháp</button>
      </div>
    </form>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'SellerProductsAdd',
  data() {
    return {
      form: {
        name: '',
        description: '',
        images: [],
        colors: '',
        sizes: '',
        variants: [],
        shipping_partners: [],
      },
      shippingPartners: [],
      isLoading: false,
    };
  },
  methods: {
    async fetchShippingPartners() {
      try {
        const token = localStorage.getItem('token');
        const response = await axios.get('http://localhost:8000/api/seller/shipping-partners', {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.shippingPartners = response.data.data || [];
      } catch (error) {
        alert('Lỗi khi tải đơn vị vận chuyển: ' + (error.response?.data?.message || 'Lỗi không xác định'));
      }
    },
    handleImageUpload(e) {
      const files = Array.from(e.target.files);
      this.form.images = files.map(file => URL.createObjectURL(file));
    },
    generateVariants() {
      const colors = this.form.colors.split(',').map(c => c.trim()).filter(c => c);
      const sizes = this.form.sizes.split(',').map(s => s.trim()).filter(s => s);
      this.form.variants = [];
      colors.forEach(color => {
        sizes.forEach(size => {
          this.form.variants.push({
            color,
            size,
            sku: '',
            price: 0,
            stock: 0,
            image: null,
          });
        });
      });
    },
    async submitProduct() {
      this.isLoading = true;
      try {
        const token = localStorage.getItem('token');
        const formData = new FormData();
        formData.append('name', this.form.name);
        formData.append('description', this.form.description);
        this.form.variants.forEach((variant, index) => {
          formData.append(`variants[${index}][color]`, variant.color);
          formData.append(`variants[${index}][size]`, variant.size);
          formData.append(`variants[${index}][sku]`, variant.sku);
          formData.append(`variants[${index}][price]`, variant.price);
          formData.append(`variants[${index}][stock]`, variant.stock);
          if (variant.image) {
            formData.append(`variants[${index}][image]`, variant.image);
          }
        });
        this.form.shipping_partners.forEach((partnerId, index) => {
          formData.append(`shipping_partners[${index}]`, partnerId);
        });

        await axios.post('http://localhost:8000/api/seller/products', formData, {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'multipart/form-data',
          },
        });
        alert('Thêm sản phẩm thành công');
        this.$router.push('/seller/products/all');
      } catch (error) {
        alert('Lỗi khi thêm sản phẩm: ' + (error.response?.data?.message || 'Lỗi không xác định'));
      } finally {
        this.isLoading = false;
      }
    },
    saveDraft() {
    },
  },
  mounted() {
    this.fetchShippingPartners();
  },
};
</script>