<template>
  <div class="p-8 space-y-6">
    <div class=" mx-auto">
      <h2 class="text-2xl font-bold text-gray-800 mb-6">
        Quản lý báo cáo
      </h2>
      <FilterSearch
        :filters="filters"
        :searchPlaceholder="'Tìm theo URL báo cáo...'"
        :showDateRange="true"
        v-model:currentFilter="reportTypeFilter"
        v-model:searchQuery="searchQuery"
        v-model:startDate="startDate"
        v-model:endDate="endDate"
        @search="applySearch"
      />
      <p v-if="errorMessage" class="text-center text-red-500 mt-4">
        {{ errorMessage }}
      </p>
      <p v-else-if="filteredReports.length === 0" class="text-center text-gray-500 mt-4">
        Không tìm thấy báo cáo nào.
      </p>
      <table v-else class="table-auto w-full border bg-white shadow-sm rounded-lg overflow-hidden">
        <thead class="bg-gray-200">
          <tr>
            <th class="p-3 border-b text-left text-gray-700">STT</th>
            <th class="p-3 border-b text-left text-gray-700">Loại báo cáo</th>
            <th class="p-3 border-b text-left text-gray-700">URL tệp</th>
            <th class="p-3 border-b text-left text-gray-700">Ngày tạo</th>
            <th class="p-3 border-b text-left text-gray-700">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(report, index) in filteredReports" :key="report.id" class="hover:bg-gray-50">
            <td class="p-3 border-b">{{ index + 1 }}</td>
            <td class="p-3 border-b">{{ report.report_type || 'N/A' }}</td>
            <td class="p-3 border-b">
              <a :href="report.file_url" target="_blank" class="text-blue-600 hover:underline">
                Tải xuống
              </a>
            </td>
            <td class="p-3 border-b">{{ formatDate(report.created_at) }}</td>
            <td class="p-3 border-b text-center">
              <button
                @click="openReportModal(report)"
                class="text-blue-600 hover:underline"
              >
                Xem chi tiết
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal for Report Details -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="closeReportModal"
    >
      <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6 relative">
        <button
          @click="closeReportModal"
          class="absolute top-4 right-4 text-gray-500 hover:text-gray-700"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <h3 class="text-xl font-bold text-gray-800 mb-4">
          Chi tiết báo cáo
        </h3>
        <div v-if="selectedReport" class="space-y-4">
          <div>
            <h4 class="text-md font-semibold text-gray-700">Loại báo cáo</h4>
            <p class="text-gray-600">{{ selectedReport.report_type || 'N/A' }}</p>
          </div>
          <div>
            <h4 class="text-md font-semibold text-gray-700">URL tệp</h4>
            <a :href="selectedReport.file_url" target="_blank" class="text-blue-600 hover:underline">
              Tải xuống
            </a>
          </div>
          <div>
            <h4 class="text-md font-semibold text-gray-700">Ngày tạo</h4>
            <p class="text-gray-600">{{ formatDate(selectedReport.created_at) }}</p>
          </div>
        </div>
        <div class="mt-6 flex justify-end">
          <button
            @click="closeReportModal"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
          >
            Đóng
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import FilterSearch from './component/AdminFilterSearch.vue';

export default {
  name: 'AdminReports',
  components: { FilterSearch },
  data() {
    return {
      reports: [],
      reportTypes: [],
      errorMessage: '',
      searchQuery: '',
      reportTypeFilter: 'all',
      startDate: '',
      endDate: '',
      showModal: false,
      selectedReport: null,
    };
  },
  computed: {
    filters() {
      return [
        { key: 'all', label: 'Tất cả báo cáo', count: this.reports.length },
        ...this.reportTypes.map(type => ({
          key: type,
          label: type,
          count: this.reports.filter(r => r.report_type === type).length,
        })),
      ];
    },
    filteredReports() {
      const q = this.searchQuery.toLowerCase().trim();
      return this.reports.filter(report => {
        const matchesSearch = !q || (report.file_url || '').toLowerCase().includes(q);
        const matchesType = this.reportTypeFilter === 'all' || report.report_type === this.reportTypeFilter;
        let matchesDate = true;
        if (this.startDate || this.endDate) {
          const reportDate = new Date(report.created_at);
          if (this.startDate) {
            const start = new Date(this.startDate);
            matchesDate = matchesDate && reportDate >= start;
          }
          if (this.endDate) {
            const end = new Date(this.endDate);
            end.setHours(23, 59, 59, 999); // Include entire end day
            matchesDate = matchesDate && reportDate <= end;
          }
        }
        return matchesSearch && matchesType && matchesDate;
      });
    },
  },
  async mounted() {
    await this.fetchReports();
  },
  methods: {
    async fetchReports() {
      const token = localStorage.getItem('token');
      try {
        if (!token) {
          throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
        }
        const params = {};
        if (this.reportTypeFilter !== 'all') params.report_type = this.reportTypeFilter;
        if (this.startDate) params.start_date = this.startDate;
        if (this.endDate) params.end_date = this.endDate;
        const response = await axios.get('http://localhost:8000/api/admin/reports', {
          headers: { Authorization: `Bearer ${token}` },
          params,
        });
        this.reports = response.data.reports;
        this.reportTypes = response.data.report_types;
        this.errorMessage = '';
      } catch (error) {
        console.error('Error fetching reports:', error.response || error);
        this.errorMessage = error.response?.status === 401
          ? 'Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.'
          : 'Không thể tải danh sách báo cáo. Vui lòng thử lại sau.';
        if (error.response?.status === 401) {
          this.$router.push('/admin/login');
        }
      }
    },
    formatDate(date) {
      if (!date) return 'N/A';
      return new Date(date).toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
      });
    },
    applySearch() {
      this.fetchReports();
    },
    openReportModal(report) {
      this.selectedReport = report;
      this.showModal = true;
    },
    closeReportModal() {
      this.showModal = false;
      this.selectedReport = null;
    },
  },
};
</script>