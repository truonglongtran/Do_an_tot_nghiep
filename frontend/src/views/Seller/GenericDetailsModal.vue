<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    @click.self="closeModal"
  >
    <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6 relative">
      <button
        class="absolute top-4 right-4 text-gray-500 hover:text-gray-700"
        @click="closeModal"
      >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
      <h2 class="text-xl font-bold mb-4">{{ title }}</h2>
      <div v-if="data && fields && fields.length" class="space-y-4">
        <p v-for="(field, index) in fields" :key="index" class="text-gray-700 flex items-start">
          <strong class="font-semibold w-40 shrink-0">{{ field.label }}:</strong>
          <span class="value">
            <template v-if="field.type === 'text'">
              {{ getFieldValue(data, field.key) || 'Không có' }}
            </template>
            <template v-else-if="field.type === 'date'">
              {{ formatDate(getFieldValue(data, field.key)) }}
            </template>
            <template v-else-if="field.type === 'image'">
              <img
                :src="getFieldValue(data, field.key)"
                alt="Field Image"
                class="w-full max-h-64 object-contain rounded mt-2"
                @error="handleImageError($event, field)"
              />
            </template>
            <template v-else-if="field.type === 'link'">
              <a
                v-if="getFieldValue(data, field.key)"
                :href="getFieldValue(data, field.key)"
                target="_blank"
                class="text-blue-600 hover:underline"
              >
                {{ truncateUrl(getFieldValue(data, field.key)) }}
              </a>
              <span v-else>Không có</span>
            </template>
            <template v-else-if="field.type === 'custom'">
              <template v-if="field.customFormat">
                <div
                  v-if="isHtmlString(field.customFormat(getFieldValue(data, field.key), data))"
                  v-html="sanitizeHtml(field.customFormat(getFieldValue(data, field.key), data))"
                ></div>
                <ul v-else-if="Array.isArray(field.customFormat(getFieldValue(data, field.key), data))" class="list-disc pl-5">
                  <li
                    v-for="(item, idx) in field.customFormat(getFieldValue(data, field.key), data)"
                    :key="idx"
                  >
                    <img
                      v-if="isImageUrl(item)"
                      :src="item"
                      alt="Field Image"
                      class="w-full max-h-64 object-contain rounded mt-2"
                      @error="handleImageError($event, field)"
                    />
                    <span v-else>{{ item || 'Không có' }}</span>
                  </li>
                </ul>
                <span v-else>{{ field.customFormat(getFieldValue(data, field.key), data) || 'Không có' }}</span>
              </template>
              <span v-else>{{ getFieldValue(data, field.key) || 'Không có' }}</span>
            </template>
            <template v-else-if="field.type === 'list'">
              {{ formatListField(data, field) || 'Không có' }}
            </template>
            <template v-else-if="field.type === 'boolean'">
              {{ field.customFormat ? field.customFormat(getFieldValue(data, field.key)) : (getFieldValue(data, field.key) ? 'Có' : 'Không') }}
            </template>
            <span v-else>Không có</span>
          </span>
        </p>
      </div>
      <div v-else class="text-center text-gray-500">
        Không có dữ liệu để hiển thị.
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'GenericDetailsModal',
  props: {
    show: {
      type: Boolean,
      required: true,
    },
    data: {
      type: Object,
      default: null,
    },
    fields: {
      type: Array,
      required: true,
    },
    title: {
      type: String,
      default: 'Chi tiết',
    },
  },
  methods: {
    closeModal() {
      this.$emit('close');
    },
    getFieldValue(obj, key) {
      if (!obj || !key) return null;
      const value = key.split('.').reduce((o, k) => (o && o[k] !== undefined ? o[k] : null), obj);
      if (Array.isArray(value) && value.length === 0) return null;
      if (typeof value === 'object' && value !== null && Object.keys(value).length === 0) return null;
      return value;
    },
    formatDate(date) {
      if (!date) return 'Không có';
      try {
        return new Intl.DateTimeFormat('vi-VN', {
          dateStyle: 'medium',
          timeStyle: 'short',
        }).format(new Date(date));
      } catch {
        return 'Không có';
      }
    },
    formatListField(data, field) {
      const value = this.getFieldValue(data, field.key);
      if (!value || !Array.isArray(value)) return 'Không có';
      
      // Nếu mảng chứa các chuỗi, nối trực tiếp
      if (value.length > 0 && typeof value[0] === 'string') {
        return value.filter(item => item).join(', ') || 'Không có';
      }
      
      // Nếu mảng chứa các đối tượng, lấy giá trị theo listItemKey
      if (!field.listItemKey) return 'Cấu hình thiếu listItemKey';
      return value
        .map(item => this.getFieldValue(item, field.listItemKey) || 'N/A')
        .filter(item => item && item !== 'N/A')
        .join(', ') || 'Không có';
    },
    truncateUrl(url) {
      if (!url) return 'Không có';
      return url.length > 30 ? url.substring(0, 27) + '...' : url;
    },
    handleImageError(event, field) {
      event.target.src = '/path/to/placeholder-image.jpg';
      console.warn(`Failed to load image for field ${field.label}: ${event.target.src}`);
    },
    isImageUrl(value) {
      if (typeof value !== 'string') return false;
      return value.match(/\.(jpeg|jpg|png|gif|webp)$/i) !== null;
    },
    isHtmlString(value) {
      if (typeof value !== 'string') return false;
      return value.trim().startsWith('<') && value.trim().endsWith('>');
    },
    sanitizeHtml(html) {
      return html.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
                 .replace(/on\w+="[^"]*"/gi, '');
    },
  },
};
</script>

<style scoped>
/* Ensure spacing between label and value */
p {
  display: flex;
  align-items: flex-start;
  line-height: 1.5;
}

strong {
  width: 160px; /* Fixed width for labels to align values */
  flex-shrink: 0; /* Prevent label from shrinking */
}

.value {
  flex-grow: 1; /* Allow value to take remaining space */
  margin-left: 12px; /* Add spacing between label and value */
}

/* Style for lists to ensure consistent spacing */
ul {
  margin-top: 4px;
  margin-bottom: 4px;
}

/* Ensure images and links align properly */
img, a {
  display: block;
}
</style>