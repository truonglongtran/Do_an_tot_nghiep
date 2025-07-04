<template>
  <div class="p-6 space-y-6">
    <h1 class="text-2xl font-bold">{{ isEditMode ? 'Sửa sản phẩm' : 'Thêm sản phẩm' }}</h1>
    
    <form @submit.prevent="showConfirmModal('submit')">
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
          <div>
            <label class="block">Ảnh chính sản phẩm</label>
            <input type="file" accept="image/jpeg,image/png,image/webp" @change="handleMainImageUpload" class="w-full p-2 border rounded" :required="!isEditMode" />
            <img v-if="form.main_image_preview" :src="getImageUrl(form.main_image_preview)" class="w-20 h-20 object-cover mt-2" @error="handleImageError($event, form.main_image_preview)" />
          </div>
          <div>
            <label class="block">Ảnh phụ sản phẩm</label>
            <input type="file" accept="image/jpeg,image/png,image/webp" multiple @change="handleAdditionalImagesUpload" class="w-full p-2 border rounded" />
            <div class="flex flex-wrap gap-2 mt-2">
              <div v-for="(url, index) in form.additional_images_preview" :key="index" class="relative">
                <img :src="getImageUrl(url)" class="w-20 h-20 object-cover" @error="handleImageError($event, url)" />
                <button type="button" @click="removeAdditionalImage(index)" class="absolute top-0 right-0 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center">×</button>
              </div>
            </div>
          </div>
          <!-- Trạng thái sản phẩm (chỉ hiển thị khi sửa) -->
          <div v-if="isEditMode">
            <label class="block">Trạng thái sản phẩm</label>
            <select v-model="form.status" class="w-full p-2 border rounded" required>
              <option value="pending">Chờ duyệt</option>
              <option value="approved">Đã duyệt</option>
              <option value="banned">Bị cấm</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Danh mục và thuộc tính/biến thể -->
      <div class="border p-4 rounded mt-4">
        <h2 class="text-lg font-semibold mb-4">Danh mục & Thông tin sản phẩm</h2>
        <div class="space-y-4">
          <div>
            <label class="block">Danh mục sản phẩm</label>
            <select v-model="form.category_id" @change="fetchAttributes" class="w-full p-2 border rounded" required>
              <option value="">Chọn danh mục</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
            </select>
          </div>
          <div v-if="form.category_id">
            <div v-if="attributes.length > 0">
              <label class="block">Thuộc tính</label>
              <div class="space-y-2">
                <label v-for="attribute in attributes" :key="attribute.id" class="flex items-center">
                  <input type="checkbox" v-model="selectedAttributes[attribute.id]" :value="attribute.id" @change="updateSelectedAttributes" />
                  <span class="ml-2">{{ attribute.name }}</span>
                </label>
              </div>
              <div v-if="Object.keys(selectedAttributes).some(key => selectedAttributes[key])" class="space-y-4 mt-4">
                <div class="overflow-x-auto">
                  <table class="min-w-full border table-fixed">
                    <thead>
                      <tr>
                        <th class="px-4 py-2 border w-40">Hình ảnh</th>
                        <th v-for="attribute in selectedAttributesList" :key="attribute.id" class="px-4 py-2 border w-32">{{ attribute.name }}</th>
                        <th class="px-4 py-2 border w-40">SKU</th>
                        <th class="px-4 py-2 border w-32">Giá</th>
                        <th class="px-4 py-2 border w-32">Tồn kho</th>
                        <th class="px-4 py-2 border w-24">Hành động</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(variant, index) in form.variants" :key="index">
                        <td class="px-2 py-1 border">
                          <input type="file" accept="image/jpeg,image/png,image/webp" @change="handleVariantImageUpload($event, index)" class="w-full p-1 border rounded text-sm" />
                          <img v-if="variant.image_preview" :src="getImageUrl(variant.image_preview)" class="w-12 h-12 object-cover mx-auto mt-2" @error="handleImageError($event, variant.image_preview)" />
                        </td>
                        <td v-for="attribute in selectedAttributesList" :key="attribute.id" class="px-2 py-1 border">
                          <select 
                            v-model="variant.attributes[attribute.id]" 
                            class="w-full p-1 border rounded text-sm"
                            :class="{ 'border-red-500': !variant.attributes[attribute.id] }"
                            required
                          >
                            <option value="">Chọn {{ attribute.name }}</option>
                            <option v-for="value in attribute.values" :key="value.id" :value="value.id">{{ value.value }}</option>
                          </select>
                        </td>
                        <td class="px-2 py-1 border">
                          <input v-model="variant.sku" class="w-full p-1 border rounded text-sm" required />
                        </td>
                        <td class="px-2 py-1 border">
                          <input v-model="variant.price" type="number" class="w-full p-1 border rounded text-sm" required min="0" />
                        </td>
                        <td class="px-2 py-1 border">
                          <input v-model="variant.stock" type="number" class="w-full p-1 border rounded text-sm" required min="0" />
                        </td>
                        <td class="px-2 py-1 border text-center">
                          <button type="button" @click="showConfirmModal('removeVariant', index)" class="bg-red-500 text-white px-2 py-1 rounded text-sm">Xóa</button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <div class="mt-4">
                    <button type="button" @click="addManualVariant" class="bg-green-500 text-white p-2 rounded">+ Thêm biến thể</button>
                  </div>
                </div>
              </div>
            </div>
            <div v-else>
              <label class="block">SKU</label>
              <input v-model="form.sku" class="w-full p-2 border rounded" required />
              <label class="block mt-2">Giá</label>
              <input v-model="form.price" type="number" class="w-full p-2 border rounded" required min="0" />
              <label class="block mt-2">Tồn kho</label>
              <input v-model="form.stock" type="number" class="w-full p-2 border rounded" required min="0" />
              <label class="block mt-2">Hình ảnh biến thể</label>
              <input type="file" accept="image/jpeg,image/png,image/webp" @change="handleSingleVariantImageUpload" class="w-full p-2 border rounded" />
              <img v-if="form.image_preview" :src="getImageUrl(form.image_preview)" class="w-20 h-20 object-cover mt-2" @error="handleImageError($event, form.image_preview)" />
            </div>
          </div>
        </div>
      </div>

      <!-- Nút hành động -->
      <div class="flex space-x-4 mt-6">
        <button
          type="submit"
          class="bg-blue-500 text-white p-2 rounded"
          :disabled="isLoading || (!form.main_image && !isEditMode) || (attributes.length > 0 && !form.variants.length)"
        >
          {{ isLoading ? 'Đang xử lý...' : (isEditMode ? 'Cập nhật' : 'Đăng bán') }}
        </button>
        <button
          type="button"
          @click="showConfirmModal('cancel')"
          class="bg-gray-300 text-gray-800 p-2 rounded hover:bg-gray-400"
        >
          Hủy
        </button>
      </div>
    </form>

    <!-- Confirm Modal -->
    <ConfirmModal
      :show="modal.show"
      :title="modal.title"
      :message="modal.message"
      :confirmText="modal.confirmText"
      :cancelText="modal.cancelText"
      @confirm="handleModalConfirm"
      @cancel="handleModalCancel"
    />
  </div>
</template>

<script>
import axios from 'axios';
import ConfirmModal from './component/SellerConfirmModal.vue';

export default {
  name: 'SellerProductsAddEdit',
  components: {
    ConfirmModal,
  },
  data() {
    return {
      isEditMode: false,
      form: {
        id: null,
        category_id: '',
        name: '',
        description: '',
        main_image: null,
        main_image_preview: null,
        additional_images: [],
        additional_images_preview: [],
        variants: [],
        status: 'pending',
        sku: '',
        price: 0,
        stock: 0,
        image: null,
        image_preview: null,
      },
      categories: [],
      attributes: [],
      selectedAttributes: {},
      isLoading: false,
      placeholderImage: 'https://placehold.co/150',
      modal: {
        show: false,
        title: '',
        message: '',
        confirmText: 'Xác nhận',
        cancelText: 'Hủy',
        action: null,
        data: null,
      },
    };
  },
  computed: {
    selectedAttributesList() {
      return this.attributes.filter(attr => this.selectedAttributes[attr.id]);
    },
  },
  async mounted() {
    await this.fetchCategories();
    if (this.$route.params.id) {
      this.isEditMode = true;
      await this.fetchProduct(this.$route.params.id);
    }
  },
  methods: {
    async fetchCategories() {
      try {
        const token = localStorage.getItem('token');
        if (!token) throw new Error('No token found in localStorage');
        const response = await axios.get('http://localhost:8000/api/seller/categories', {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.categories = response.data.data || [];
      } catch (error) {
        console.error('Error fetching categories:', error.response?.data || error.message);
        alert('Lỗi khi tải danh mục: ' + (error.response?.data?.message || 'Lỗi hệ thống'));
      }
    },
    async fetchAttributes() {
      if (!this.form.category_id) {
        this.attributes = [];
        this.selectedAttributes = {};
        if (!this.isEditMode) this.form.variants = [];
        return;
      }
      try {
        const token = localStorage.getItem('token');
        const response = await axios.get(`http://localhost:8000/api/seller/categories/${this.form.category_id}/attributes`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.attributes = response.data.data || [];
        if (!this.isEditMode) {
          this.selectedAttributes = {};
          this.form.variants = [];
        }
      } catch (error) {
        console.error('Error fetching attributes:', error.response?.data || error.message);
        alert('Lỗi khi tải thuộc tính: ' + (error.response?.data?.message || 'Lỗi hệ thống'));
      }
    },
    async fetchProduct(id) {
      try {
        this.form = {
          id: null,
          category_id: '',
          name: '',
          description: '',
          main_image: null,
          main_image_preview: null,
          additional_images: [],
          additional_images_preview: [],
          variants: [],
          status: 'pending',
          sku: '',
          price: 0,
          stock: 0,
          image: null,
          image_preview: null,
        };
        const token = localStorage.getItem('token');
        if (!token) throw new Error('No token found in localStorage');
        const response = await axios.get(`http://localhost:8000/api/seller/products/${id}`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        const product = response.data.data || {};

        this.form = {
          id: product.id || null,
          category_id: product.category?.id || '',
          name: product.name || '',
          description: product.description || '',
          main_image: null,
          main_image_preview: product.images?.[0] || this.placeholderImage,
          additional_images: [],
          additional_images_preview: product.images?.slice(1) || [],
          variants: product.variants && product.variants.length
            ? product.variants.map(variant => ({
                id: variant.id || null,
                attributes: variant.attributes?.reduce((acc, attr) => {
                  acc[attr.attribute_id] = attr.attribute_value_id;
                  return acc;
                }, {}) || {},
                sku: variant.sku || '',
                price: variant.price || 0,
                stock: variant.stock || 0,
                image: null,
                image_preview: variant.image_url || this.placeholderImage,
                status: variant.status || 'active',
              }))
            : [],
          status: product.status || 'pending',
          sku: product.variants?.length ? product.variants[0]?.sku : product.sku || '',
          price: product.variants?.length ? product.variants[0]?.price : product.price || 0,
          stock: product.variants?.length ? product.variants[0]?.stock : product.stock || 0,
          image: null,
          image_preview: product.variants?.length ? product.variants[0]?.image_url : product.image || this.placeholderImage,
        };

        await this.fetchAttributes();
        if (this.form.variants.length && this.attributes.length) {
          this.attributes.forEach(attr => {
            if (this.form.variants.some(variant => variant.attributes[attr.id])) {
              this.selectedAttributes[attr.id] = true;
            }
          });
        }
      } catch (error) {
        console.error('Error fetching product:', error.response?.data || error.message);
        alert('Lỗi khi tải sản phẩm: ' + (error.response?.data?.message || 'Lỗi hệ thống'));
      }
    },
    handleMainImageUpload(event) {
      const file = event.target.files[0];
      if (file) {
        this.form.main_image = file;
        this.form.main_image_preview = URL.createObjectURL(file);
      }
    },
    handleAdditionalImagesUpload(event) {
      const files = Array.from(event.target.files);
      files.forEach(file => {
        this.form.additional_images.push(file);
        this.form.additional_images_preview.push(URL.createObjectURL(file));
      });
    },
    removeAdditionalImage(index) {
      this.form.additional_images.splice(index, 1);
      this.form.additional_images_preview.splice(index, 1);
    },
    handleVariantImageUpload(event, index) {
      const file = event.target.files[0];
      if (file) {
        this.form.variants[index].image = file;
        this.form.variants[index].image_preview = URL.createObjectURL(file);
      }
    },
    handleSingleVariantImageUpload(event) {
      const file = event.target.files[0];
      if (file) {
        this.form.image = file;
        this.form.image_preview = URL.createObjectURL(file);
      }
    },
    getImageUrl(imgUrl) {
      if (!imgUrl) return this.placeholderImage;
      if (/^blob:/.test(imgUrl)) return imgUrl; // Preview ảnh cục bộ
      const baseUrl = import.meta.env.VITE_STORAGE_BASE_URL || 'http://localhost:8000/storage';
      const cleanImgUrl = imgUrl.replace(/^\/?(storage\/)?/, '');
      return `${baseUrl}/${cleanImgUrl}?t=${new Date().getTime()}`;
    },
    handleImageError(event, imgUrl) {
      console.error('Lỗi tải ảnh:', { img_url: imgUrl, attempted_url: event.target.src });
      event.target.src = this.placeholderImage;
    },
    showConfirmModal(action, data = null) {
      this.modal = {
        show: true,
        action,
        data,
        title: this.getModalTitle(action),
        message: this.getModalMessage(action),
        confirmText: 'Xác nhận',
        cancelText: 'Hủy',
      };
    },
    getModalTitle(action) {
      switch (action) {
        case 'submit':
          return this.isEditMode ? 'Xác nhận cập nhật' : 'Xác nhận đăng bán';
        case 'cancel':
          return 'Xác nhận hủy';
        case 'removeVariant':
          return 'Xác nhận xóa biến thể';
        default:
          return 'Xác nhận';
      }
    },
    getModalMessage(action) {
      switch (action) {
        case 'submit':
          return this.isEditMode
            ? 'Bạn có chắc chắn muốn cập nhật sản phẩm này?'
            : 'Bạn có chắc chắn muốn đăng bán sản phẩm này?';
        case 'cancel':
          return 'Bạn có chắc chắn muốn hủy và quay lại danh sách sản phẩm? Dữ liệu chưa lưu sẽ bị mất.';
        case 'removeVariant':
          return 'Bạn có chắc chắn muốn xóa biến thể này?';
        default:
          return 'Bạn có chắc chắn muốn thực hiện hành động này?';
      }
    },
    handleModalConfirm() {
      switch (this.modal.action) {
        case 'submit':
          this.submitProduct();
          break;
        case 'cancel':
          this.$router.push('/seller/products/all');
          break;
        case 'removeVariant':
          this.removeVariant(this.modal.data);
          break;
      }
      this.modal.show = false;
    },
    handleModalCancel() {
      this.modal.show = false;
      this.modal.action = null;
      this.modal.data = null;
    },
    removeVariant(index) {
      this.form.variants.splice(index, 1);
    },
    updateSelectedAttributes() {
      const prevVariants = [...this.form.variants];
      this.form.variants = [];
      if (Object.keys(this.selectedAttributes).some(key => this.selectedAttributes[key])) {
        if (prevVariants.length) {
          prevVariants.forEach(variant => {
            const newVariant = {
              ...variant,
              attributes: Object.fromEntries(
                this.selectedAttributesList.map(attr => [attr.id, variant.attributes[attr.id] || null])
              ),
            };
            if (Object.values(newVariant.attributes).every(val => val)) {
              this.form.variants.push(newVariant);
            }
          });
        }
        this.addManualVariant();
      }
    },
    addManualVariant() {
      if (!Object.keys(this.selectedAttributes).some(key => this.selectedAttributes[key])) {
        alert('Vui lòng chọn ít nhất một thuộc tính!');
        return;
      }
      const newVariant = {
        id: null,
        attributes: Object.fromEntries(
          this.selectedAttributesList.map(attr => [attr.id, null])
        ),
        sku: `SKU-${Date.now()}`,
        price: 0,
        stock: 0,
        image: null,
        image_preview: this.placeholderImage,
        status: 'active',
      };
      this.form.variants.push(newVariant);
    },
    validateVariant(variant) {
      const attrCombo = Object.values(variant.attributes).join('-');
      return !this.form.variants.some(v => 
        v !== variant && 
        Object.values(v.attributes).join('-') === attrCombo);
    },
    async submitProduct() {
      if (!this.form.main_image && !this.isEditMode) {
        alert('Vui lòng chọn ảnh chính cho sản phẩm!');
        return;
      }
      if (this.attributes.length > 0 && !this.form.variants.length) {
        alert('Vui lòng thêm ít nhất một biến thể!');
        return;
      }
      if (this.form.variants.some(v => Object.values(v.attributes).some(val => !val))) {
        alert('Vui lòng chọn đầy đủ giá trị thuộc tính cho tất cả biến thể!');
        return;
      }
      if (!this.form.category_id) {
        alert('Vui lòng chọn danh mục sản phẩm!');
        return;
      }
      this.isLoading = true;
      try {
        const token = localStorage.getItem('token');
        const formData = new FormData();
        formData.append('name', this.form.name);
        formData.append('description', this.form.description || '');
        formData.append('category_id', this.form.category_id);
        formData.append('status', this.isEditMode ? this.form.status : 'pending');
        if (this.form.main_image) {
          formData.append('main_image', this.form.main_image);
        }
        this.form.additional_images.forEach((file, index) => {
          formData.append(`additional_images[${index}]`, file);
        });

        if (this.attributes.length > 0) {
          this.form.variants.forEach((variant, index) => {
            if (variant.id) {
              formData.append(`variants[${index}][id]`, variant.id);
            }
            Object.entries(variant.attributes).forEach(([attrId, valueId], attrIndex) => {
              formData.append(`variants[${index}][attributes][${attrIndex}][attribute_id]`, attrId);
              formData.append(`variants[${index}][attributes][${attrIndex}][attribute_value_id]`, valueId);
            });
            formData.append(`variants[${index}][sku]`, variant.sku);
            formData.append(`variants[${index}][price]`, variant.price);
            formData.append(`variants[${index}][stock]`, variant.stock);
            formData.append(`variants[${index}][status]`, variant.status);
            if (variant.image) {
              formData.append(`variants[${index}][image]`, variant.image);
            }
          });
        } else {
          formData.append('sku', this.form.sku);
          formData.append('price', this.form.price);
          formData.append('stock', this.form.stock);
          if (this.form.image) {
            formData.append('image', this.form.image);
          }
        }

        const url = this.isEditMode
          ? `http://localhost:8000/api/seller/products/${this.form.id}`
          : 'http://localhost:8000/api/seller/products';
        const method = this.isEditMode ? 'put' : 'post';

        const response = await axios[method](url, formData, {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'multipart/form-data',
          },
        });

        alert(this.isEditMode ? 'Cập nhật sản phẩm thành công' : 'Thêm sản phẩm thành công');
        this.$router.push('/seller/products');
      } catch (error) {
        console.error('Lỗi khi lưu sản phẩm:', error.response?.data || error.message);
        alert('Lỗi khi lưu sản phẩm: ' + (error.response?.data?.message || 'Lỗi hệ thống'));
      } finally {
        this.isLoading = false;
      }
    },
  },
};
</script>

<style scoped>
.table-fixed {
  table-layout: fixed;
}
th, td {
  border: 1px solid #d1d5db;
  vertical-align: middle;
}
th {
  background-color: #f3f4f6;
  font-weight: 500;
  text-align: center;
}
.overflow-x-auto {
  max-width: 100%;
}
input, select {
  font-size: 0.8rem;
}
</style>