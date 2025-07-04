<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg w-full max-w-2xl max-h-[80vh] overflow-y-auto">
      <h2 class="text-xl font-bold mb-4">{{ title }}</h2>
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div v-for="field in fields" :key="field.name" class="mb-4" v-show="isFieldVisible(field)">
          <label class="block text-sm font-medium text-gray-700">{{ field.label }}</label>
          <div v-if="field.type === 'file'" class="mt-1">
            <input
              type="file"
              :accept="field.accept || 'image/*'"
              @change="handleFileChange($event, field)"
              :required="field.required && isFieldApplicable(field) && !isEdit"
              :disabled="!isFieldApplicable(field)"
              class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
              :class="{ 'border-red-500': errors[field.name] && touched[field.name] }"
            />
            <img
              v-if="form[field.name] && typeof form[field.name] === 'string' && isEdit"
              :src="getImageUrl(form[field.name])"
              alt="Preview"
              class="mt-2 w-32 h-32 object-cover rounded"
              @error="handleImageError($event, field)"
            />
            <img
              v-else-if="previewUrl[field.name]"
              :src="previewUrl[field.name]"
              alt="Preview"
              class="mt-2 w-32 h-32 object-cover rounded"
              @error="handleImageError($event, field)"
            />
          </div>
          <input
            v-else-if="['text', 'email', 'password', 'number', 'date', 'url', 'datetime-local'].includes(field.type)"
            v-model="form[field.name]"
            :type="field.type"
            :placeholder="field.placeholder"
            :required="field.required && isFieldApplicable(field)"
            :disabled="!isFieldApplicable(field)"
            @input="touchField(field); validateField(field)"
            @blur="touchField(field); validateField(field)"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 h-10 px-3 py-2 text-sm"
            :class="{ 'border-red-500': errors[field.name] && touched[field.name] }"
          />
          <select
            v-else-if="field.type === 'select'"
            v-model="form[field.name]"
            :required="field.required && isFieldApplicable(field)"
            :disabled="!isFieldApplicable(field)"
            @change="touchField(field); validateField(field); field.onChange ? field.onChange($event.target.value) : null"
            @blur="touchField(field); validateField(field)"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 h-10 px-3 py-2 text-sm"
            :class="{ 'border-red-500': errors[field.name] && touched[field.name] }"
          >
            <option v-if="field.placeholder" value="" disabled>
              {{ field.placeholder }}
            </option>
            <option v-for="option in field.options" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
          <multiselect
            v-else-if="field.type === 'multiselect'"
            v-model="form[field.name]"
            :options="field.options"
            :multiple="true"
            :placeholder="field.placeholder"
            :label="field.labelKey"
            :track-by="field.trackBy"
            :required="field.required && isFieldApplicable(field)"
            :disabled="!isFieldApplicable(field)"
            class="mt-1"
            :searchable="true"
            :close-on-select="false"
            :custom-label="option => option.label"
            :select-label="'Chọn'"
            :deselect-label="'Bỏ chọn'"
            @input="handleMultiselectInput(field, $event)"
            @blur="touchField(field); validateField(field)"
          >
            <template #noResult>{{ field.noResultText || 'Không tìm thấy kết quả' }}</template>
            <template #noOptions>{{ field.emptyOptionsText || 'Không có tùy chọn nào' }}</template>
          </multiselect>
          <div v-else-if="field.type === 'checkbox'" class="mt-1 flex items-center">
            <input
              v-model="form[field.name]"
              type="checkbox"
              :disabled="!isFieldApplicable(field)"
              class="h-4 w-4 text-blue-500 focus:ring-blue-500 border-gray-300 rounded"
              @change="touchField(field); validateField(field)"
            />
            <label class="ml-2 text-sm text-gray-700">{{ field.label }}</label>
          </div>
          <div v-else-if="field.type === 'checkbox-group'" class="mt-1 max-h-48 overflow-y-auto border border-gray-300 rounded-md p-2">
            <label v-for="option in field.options" :key="option.value" class="flex items-center">
              <input
                type="checkbox"
                :value="option.value"
                v-model="form[field.name]"
                :disabled="!isFieldApplicable(field)"
                class="h-4 w-4 text-blue-500 focus:ring-blue-500 border-gray-300 rounded"
                @change="touchField(field); validateField(field)"
              />
              <span class="ml-2 text-sm text-gray-700">{{ option.label || 'N/A' }}</span>
            </label>
          </div>
          <p v-if="errors[field.name] && touched[field.name]" class="text-red-500 text-sm mt-1">
            {{ errors[field.name] }}
          </p>
          <p v-else-if="field.type === 'multiselect' && field.options && !field.options.length" class="text-red-500 text-sm mt-1">
            {{ field.emptyOptionsText || 'Không có tùy chọn nào' }}
          </p>
        </div>
        <div class="flex justify-end space-x-2">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400"
          >
            Hủy
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
            :disabled="isSubmitting || hasErrors"
          >
            {{ isEdit ? 'Cập nhật' : modalMode === 'delete' ? 'Xác nhận' : 'Thêm' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import Multiselect from 'vue-multiselect';

export default {
  name: 'FormModal',
  components: { Multiselect },
  props: {
    show: { type: Boolean, required: true },
    title: { type: String, required: true },
    fields: { type: Array, required: true },
    initialData: { type: Object, default: () => ({}) },
    isEdit: { type: Boolean, default: false },
    modalMode: { type: String, default: null },
  },
  data() {
    return {
      form: {},
      errors: {},
      touched: {},
      isSubmitting: false,
      previewUrl: {},
    };
  },
  computed: {
    hasErrors() {
      return Object.keys(this.errors).some(key => this.errors[key] && this.touched[key]);
    },
  },
  watch: {
    initialData: {
      immediate: true,
      handler(newData) {
        console.log('FormModal: Initializing with data:', newData, 'isEdit:', this.isEdit);
        this.initializeForm(newData);
      },
    },
    fields: {
      immediate: true,
      handler(newFields) {
        if (newFields && newFields.length) {
          console.log('FormModal: Fields updated, reinitializing form');
          this.initializeForm(this.initialData);
        }
      },
    },
    'form.voucher_type': {
      handler() {
        this.fields.forEach(field => {
          this.validateField(field);
        });
      },
    },
  },
  methods: {
    initializeForm(data) {
      console.log('FormModal: Initializing form with data:', data);
      const formData = {};
      this.errors = {};
      this.touched = {};
      this.previewUrl = {};

      this.fields.forEach((field) => {
        if (field.type === 'multiselect') {
          const options = field.options || [];
          formData[field.name] = (data && data[field.name] && Array.isArray(data[field.name]))
            ? data[field.name]
                .map(id => {
                  const parsedId = parseInt(id, 10);
                  const option = options.find(opt => opt.value === parsedId);
                  return option || { value: parsedId, label: `Unknown (${id})` };
                })
                .filter(opt => opt.value !== undefined && !isNaN(opt.value))
            : field.defaultValue || [];
        } else if (field.type === 'checkbox-group') {
          formData[field.name] = (data && data[field.name] && Array.isArray(data[field.name]))
            ? data[field.name].map(id => parseInt(id, 10)).filter(id => !isNaN(id))
            : field.defaultValue || [];
        } else if (field.type === 'checkbox') {
          formData[field.name] = (data && data[field.name] !== undefined)
            ? !!data[field.name]
            : field.defaultValue || false;
        } else if (field.type === 'file') {
          formData[field.name] = (data && data[field.name]) || '';
          if (data[field.name]) {
            this.previewUrl[field.name] = this.getImageUrl(data[field.name]);
          }
        } else {
          formData[field.name] = (data && data[field.name] !== undefined)
            ? data[field.name]
            : field.defaultValue || '';
        }
      });
      this.form = { ...formData };
      console.log('FormModal: Form initialized:', this.form);
    },
    handleMultiselectInput(field, value) {
      console.log(`FormModal: Multiselect input for ${field.name}:`, value);
      this.form[field.name] = value || [];
      this.validateField(field);
    },
    getImageUrl(imgUrl) {
      if (!imgUrl) {
        console.warn('No image URL provided, using placeholder');
        return 'https://via.placeholder.com/150?text=Ảnh+Không+Tìm+Thấy';
      }
      if (/^https?:\/\//.test(imgUrl)) {
        return `${imgUrl}?t=${new Date().getTime()}`;
      }
      const baseUrl = import.meta.env.VITE_STORAGE_BASE_URL || 'http://localhost:8000/storage';
      const cleanImgUrl = imgUrl.replace(/^\/?(storage\/)?/, '');
      const finalUrl = `${baseUrl}/${cleanImgUrl}?t=${new Date().getTime()}`;
      return finalUrl;
    },
    handleFileChange(event, field) {
      const file = event.target.files[0];
      this.touched[field.name] = true;
      if (file) {
        const validImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!validImageTypes.includes(file.type)) {
          this.errors[field.name] = 'Vui lòng chọn file ảnh (JPEG, PNG, GIF hoặc WEBP).';
          return;
        }
        if (file.size > 5 * 1024 * 1024) {
          this.errors[field.name] = 'Kích thước file không được vượt quá 5MB.';
          return;
        }
        this.form[field.name] = file;
        this.previewUrl[field.name] = URL.createObjectURL(file);
        this.errors[field.name] = '';
      } else {
        this.form[field.name] = '';
        this.previewUrl[field.name] = '';
        this.errors[field.name] = field.required && !this.isEdit ? `${field.label} là bắt buộc.` : '';
      }
      this.validateField(field);
    },
    handleImageError(event, field) {
      console.error('Error loading image:', {
        field_label: field.label,
        img_url: this.form[field.name] || this.previewUrl[field.name],
        attempted_url: event.target.src,
      });
      event.target.src = 'https://via.placeholder.com/150?text=Ảnh+Không+Tìm+Thấy';
    },
    touchField(field) {
      this.touched[field.name] = true;
    },
    isFieldApplicable(field) {
      if (field.name === 'shop_ids') return this.form.voucher_type === 'shop';
      if (field.name === 'product_ids') return this.form.voucher_type === 'product';
      if (field.name === 'shipping_partner_ids' || field.name === 'shipping_only') return this.form.voucher_type === 'shipping';
      return !field.showWhen || field.showWhen();
    },
    isFieldVisible(field) {
      return this.isFieldApplicable(field) || ['code', 'voucher_type', 'discount_type', 'discount_value', 'min_order_amount', 'usage_limit', 'start_date', 'end_date'].includes(field.name);
    },
    validateField(field) {
      if (!this.touched[field.name] && !this.isSubmitting) return;
      const value = this.form[field.name];
      this.errors[field.name] = '';

      if (field.required && this.isFieldApplicable(field)) {
        if (field.type === 'multiselect' && (!value || !value.length)) {
          this.errors[field.name] = `Vui lòng chọn ít nhất một ${field.label.toLowerCase()}.`;
          return;
        }
        if (field.type === 'checkbox-group' && (!value || !value.length)) {
          this.errors[field.name] = `Vui lòng chọn ít nhất một ${field.label.toLowerCase()}.`;
          return;
        }
        if (field.type !== 'multiselect' && field.type !== 'checkbox-group' && (!value && value !== 0)) {
          this.errors[field.name] = `${field.label} là bắt buộc.`;
          return;
        }
      }

      if (field.type === 'multiselect' && value && value.length) {
        value.forEach(item => {
          const id = typeof item === 'object' ? parseInt(item.value, 10) : parseInt(item, 10);
          if (isNaN(id)) {
            this.errors[field.name] = `Giá trị trong ${field.label} phải là số nguyên.`;
          }
        });
      }

      if (field.rules && value !== undefined && value !== '') {
        for (const rule of field.rules) {
          if (!rule.validator(value)) {
            this.errors[field.name] = rule.message;
            break;
          }
        }
      }
    },
    handleSubmit() {
      console.log('FormModal: Submitting form:', this.form);
      this.isSubmitting = true;
      let valid = true;
      this.fields.forEach(field => {
        this.touched[field.name] = true;
        this.validateField(field);
        if (this.errors[field.name]) valid = false;
      });

      if (!valid) {
        this.isSubmitting = false;
        console.log('FormModal: Validation errors:', this.errors);
        alert('Vui lòng kiểm tra các lỗi trong biểu mẫu.');
        return;
      }

      const formData = new FormData();
      const voucherType = this.form.voucher_type;

      this.fields.forEach(field => {
        // Skip non-applicable fields based on voucher_type
        if (voucherType === 'platform' && ['shop_ids', 'product_ids', 'shipping_partner_ids', 'shipping_only'].includes(field.name)) {
          return;
        }
        if (voucherType === 'shop' && ['product_ids', 'shipping_partner_ids', 'shipping_only'].includes(field.name)) {
          return;
        }
        if (voucherType === 'product' && ['shop_ids', 'shipping_partner_ids', 'shipping_only'].includes(field.name)) {
          return;
        }
        if (voucherType === 'shipping' && ['shop_ids', 'product_ids'].includes(field.name)) {
          return;
        }

        if (field.type === 'multiselect') {
          const value = this.form[field.name] || [];
          value.forEach(item => {
            const id = parseInt(typeof item === 'object' ? item.value : item, 10);
            if (!isNaN(id)) {
              // Explicitly append as a number to ensure backend receives an integer
              formData.append(`${field.name}[]`, id.toString());
            } else {
              console.warn(`Skipping invalid ${field.name} value:`, item);
            }
          });
        } else if (field.type === 'checkbox-group') {
          const value = this.form[field.name] || [];
          value.forEach(item => {
            const id = parseInt(item, 10);
            if (!isNaN(id)) {
              formData.append(`${field.name}[]`, id.toString());
            } else {
              console.warn(`Skipping invalid ${field.name} value:`, item);
            }
          });
        } else if (field.type === 'checkbox') {
          formData.append(field.name, this.form[field.name] ? '1' : '0');
        } else if (field.type === 'file' && this.form[field.name] instanceof File) {
          formData.append(field.name, this.form[field.name]);
        } else {
          formData.append(field.name, this.form[field.name] || '');
        }
      });

      // Log FormData for debugging
      for (let [key, value] of formData.entries()) {
        console.log(`FormData: ${key} = ${value instanceof File ? value.name : value}`);
      }

      this.$emit('submit', formData);
      this.isSubmitting = false;
    },
  },
};
</script>

<style scoped>
.max-h-\[80vh\] {
  max-height: 80vh;
}
.max-h-48 {
  max-height: 12rem;
}
.overflow-y-auto {
  scrollbar-width: thin;
  scrollbar-color: #9ca3af #f1f5f9;
}
.overflow-y-auto::-webkit-scrollbar {
  width: 8px;
}
.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #9ca3af;
  border-radius: 4px;
}
.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #6b7280;
}
</style>