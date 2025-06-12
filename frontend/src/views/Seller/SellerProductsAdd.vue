<template>
  <div class="p-6 space-y-6">
    <h1 class="text-2xl font-bold">Thêm sản phẩm</h1>
    
    <form @submit.prevent="submitProduct">
      <!-- Thông tin cơ bản -->
      <div class="border p-4 rounded">
        <h2 class="text-lg font-semibold mb-4">Thông tin cơ bản</h2>
        <div class="space-y-4">
          <div>
            <label class="block">Danh mục sản phẩm</label>
            <select v-model="form.category_id" @change="fetchAttributes" class="w-full p-2 border rounded" required>
              <option value="">Chọn danh mục</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
            </select>
          </div>
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
          <img v-for="image in form.images" :key="image.url" :src="image.url" class="w-20 h-20 object-cover" />
        </div>
      </div>

      <!-- Biến thể -->
      <div class="border p-4 rounded">
        <h2 class="text-lg font-semibold mb-4">Biến thể</h2>
        <label class="flex items-center">
          <input type="checkbox" v-model="form.hasVariants" @change="resetVariants" />
          <span class="ml-2">Sản phẩm có biến thể</span>
        </label>
        <div v-if="form.hasVariants" class="space-y-4 mt-4">
          <!-- Chọn giá trị thuộc tính -->
          <div v-for="attribute in attributes" :key="attribute.id">
            <label class="block">{{ attribute.name }}</label>
            <select v-model="selectedAttributes[attribute.id]" multiple class="w-full p-2 border rounded">
              <option v-for="value in attribute.values" :key="value.id" :value="value.id">{{ value.value }}</option>
            </select>
          </div>
          <button type="button" @click="generateVariants" class="bg-blue-500 text-white p-2 rounded">Tạo biến thể</button>
          <table v-if="form.variants.length" class="min-w-full border">
            <thead>
              <tr>
                <th v-for="attribute in attributes" :key="attribute.id" class="px-4 py-2 border">{{ attribute.name }}</th>
                <th class="px-4 py-2 border">SKU</th>
                <th class="px-4 py-2 border">Giá</th>
                <th class="px-4 py-2 border">Tồn kho</th>
                <th class="px-4 py-2 border">Hình ảnh</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(variant, index) in form.variants" :key="index">
                <td v-for="attr in variant.attributes" :key="attr.attribute_id" class="px-4 py-2 border">
                  {{ attr.value }}
                </td>
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
        <div v-else class="space-y-4 mt-4">
          <div>
            <label class="block">SKU</label>
            <input v-model="form.singleVariant.sku" class="w-full p-2 border rounded" required />
          </div>
          <div>
            <label class="block">Giá</label>
            <input v-model="form.singleVariant.price" type="number" class="w-full p-2 border rounded" required />
          </div>
          <div>
            <label class="block">Tồn kho</label>
            <input v-model="form.singleVariant.stock" type="number" class="w-full p-2 border rounded" required />
          </div>
          <div>
            <label class="block">Hình ảnh</label>
            <input type="file" @change="e => form.singleVariant.image = e.target.files[0]" accept="image/*" />
          </div>
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
        <button type="submit" class="bg-blue-500 text-white p-2 rounded" :disabled="isLoading">
          {{ isLoading ? 'Đang đăng...' : 'Đăng bán' }}
        </button>
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
        category_id: '',
        name: '',
        description: '',
        images: [],
        hasVariants: false,
        singleVariant: { sku: '', price: 0, stock: 0, image: null },
        variants: [],
        shipping_partners: [],
      },
      categories: [],
      attributes: [],
      selectedAttributes: {},
      shippingPartners: [],
      isLoading: false,
    };
  },
  methods: {
    async fetchCategories() {
      try {
        const token = localStorage.getItem('token');
        const response = await axios.get('http://localhost:8000/api/seller/categories', {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.categories = response.data.data || [];
      } catch (error) {
        alert('Lỗi khi tải danh mục: ' + (error.response?.data?.message || 'Lỗi'));
      }
    },
    async fetchAttributes() {
      if (!this.form.category_id) {
        this.attributes = [];
        this.selectedAttributes = {};
        this.form.variants = [];
        return;
      }
      try {
        const token = localStorage.getItem('token');
        const response = await axios.get(`http://localhost:8000/api/seller/categories/${this.form.category_id}/attributes`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.attributes = response.data.data || [];
        this.selectedAttributes = {};
        this.form.variants = [];
      } catch (error) {
        alert('Lỗi khi tải thuộc tính: ' + (error.response?.data?.message || 'Lỗi'));
      }
    },
    async fetchShippingPartners() {
      try {
        const token = localStorage.getItem('token');
        const response = await axios.get('http://localhost:8000/api/seller/shipping-partners', {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.shippingPartners = response.data.data || [];
      } catch (error) {
        alert('Lỗi khi tải đơn vị vận chuyển: ' + (error.response?.data?.message || 'Lỗi'));
      }
    },
    handleImageUpload(e) {
      const files = Array.from(e.target.files);
      this.form.images = files.map(file => ({
        file,
        url: URL.createObjectURL(file),
      }));
    },
    resetVariants() {
      this.form.variants = [];
      this.selectedAttributes = {};
      if (!this.form.hasVariants) {
        this.form.singleVariant = { sku: '', price: 0, stock: 0, image: null };
      }
    },
    generateVariants() {
      const combinations = this.getAttributeCombinations();
      this.form.variants = combinations.map(combo => ({
        attributes: combo.map(attrValueId => {
          const attribute = this.attributes.find(attr => attr.values.some(v => v.id === attrValueId));
          const value = attribute.values.find(v => v.id === attrValueId);
          return {
            attribute_id: attribute.id,
            attribute_value_id: attrValueId,
            value: value.value,
          };
        }),
        sku: '',
        price: '',
        stock: 0,
        image: null,
      }));
    },
    getAttributeCombinations() {
      const valueLists = Object.values(this.selectedAttributes).filter(v => v.length > 0);
      if (!valueLists.length) return [];
      return valueLists.reduce((acc, curr) => {
        return acc.length === 0
          ? curr.map(v => [v])
          : acc.flatMap(combo => curr.map(v => [...combo, v]));
      }, []);
    },
    async submitProduct() {
      this.isLoading = true;
      try {
        const token = localStorage.getItem('token');
        const formData = new FormData();
        formData.append('name', this.form.name);
        formData.append('description', this.form.description);
        formData.append('category_id', this.form.category_id);
        formData.append('shop_id', localStorage.getItem('shop_id')); // Giả định lấy shop_id từ localStorage
        formData.append('images', JSON.stringify(this.form.images.map(img => img.url)));

        if (this.form.hasVariants) {
          this.form.variants.forEach((variant, index) => {
            variant.attributes.forEach((attr, attrIndex) => {
              formData.append(`variants[${index}][attributes][${attrIndex}][attribute_id]`, attr.attribute_id);
              formData.append(`variants[${index}][attributes][${attrIndex}][attribute_value_id]`, attr.attribute_value_id);
            });
            formData.append(`variants[${index}][sku]`, variant.sku);
            formData.append(`variants[${index}][price]`, variant.price);
            formData.append(`variants[${index}][stock]`, variant.stock);
            if (variant.image) {
              formData.append(`variants[${index}][image]`, variant.image);
            }
          });
        } else {
          formData.append('variants[0][sku]', this.form.singleVariant.sku);
          formData.append('variants[0][price]', this.form.singleVariant.price);
          formData.append('variants[0][stock]', this.form.singleVariant.stock);
          if (this.form.singleVariant.image) {
            formData.append('variants[0][image]', this.form.singleVariant.image);
          }
        }

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
        this.$router.push('/seller/products');
      } catch (error) {
        console.error('Lỗi khi thêm sản phẩm:', error);
        alert('Lỗi khi thêm sản phẩm: ' + (error.response?.data?.message || 'Lỗi không xác định'));
      } finally {
        this.isLoading = false;
      }
    },
    saveDraft() {
      alert('Lưu nháp thành công');
    },
  },
  mounted() {
    this.fetchCategories();
    this.fetchShippingPartners();
  },
};
</script>