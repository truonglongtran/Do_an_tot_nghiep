<template>
  <div class="flex items-center flex-wrap gap-4">
    <!-- Bộ lọc -->
    <div class="flex items-center space-x-2 flex-wrap gap-2">
      <button
        v-for="f in filters"
        :key="f.key"
        @click="$emit('update:currentFilter', f.key)"
        :class="{
          'bg-blue-600 text-white': currentFilter === f.key,
          'bg-gray-200 text-gray-700': currentFilter !== f.key
        }"
        class="px-3 py-1 rounded hover:bg-blue-500 hover:text-white transition"
      >
        {{ f.label }} ({{ f.count }})
      </button>
    </div>
    <!-- Ô tìm kiếm và lọc ngày -->
    <div class="flex items-center space-x-2 flex-wrap gap-2">
      <!-- Ô tìm kiếm -->
      <input
        :value="searchQuery"
        @input="$emit('update:searchQuery', $event.target.value)"
        type="text"
        :placeholder="searchPlaceholder"
        class="border px-3 py-1 rounded w-64 focus:outline-none focus:ring-2 focus:ring-blue-500"
      />
      <!-- Lọc theo ngày -->
      <div v-if="showDateRange" class="flex items-center space-x-2">
        <input
          :value="startDate"
          @input="$emit('update:startDate', $event.target.value)"
          type="date"
          class="border px-3 py-1 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <span class="text-gray-600">đến</span>
        <input
          :value="endDate"
          @input="$emit('update:endDate', $event.target.value)"
          type="date"
          class="border px-3 py-1 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>
      <!-- Nút Tìm kiếm -->
      <button
        @click="$emit('search')"
        class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 flex items-center"
      >
        <svg
          class="w-5 h-5 mr-1"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
          />
        </svg>
        Tìm
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FilterSearch',
  props: {
    filters: {
      type: Array,
      required: true,
      validator: (filters) =>
        filters.every((f) => 'key' in f && 'label' in f && 'count' in f),
    },
    searchPlaceholder: {
      type: String,
      default: 'Tìm kiếm...',
    },
    currentFilter: {
      type: String,
      required: true,
    },
    searchQuery: {
      type: String,
      default: '',
    },
    showDateRange: {
      type: Boolean,
      default: false,
    },
    startDate: {
      type: String,
      default: '',
    },
    endDate: {
      type: String,
      default: '',
    },
  },
};
</script>