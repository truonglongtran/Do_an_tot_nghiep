<template>
  <div class="p-8 space-y-6">
    <div class="mx-auto">
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
              <a v-if="report.file_url" :href="report.file_url" target="_blank" class="text-blue-600 hover:underline">
                Tải xuống
              </a>
              <span v-else>N/A</span>
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
    <GenericDetailsModal
      :show="showModal"
      :data="selectedReport"
      :fields="reportFields"
      title="Chi tiết báo cáo"
      @close="closeReportModal"
    />
  </div>
</template>

<script>
import axios from 'axios';
import FilterSearch from './component/AdminFilterSearch.vue';
import GenericDetailsModal from './component/GenericDetailsModal.vue';

export default {
  name: 'AdminReports',
  components: { FilterSearch, GenericDetailsModal },
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
      reportFields: [
        { label: 'Loại báo cáo', key: 'report_type', type: 'text' },
        { label: 'URL tệp', key: 'file_url', type: 'link' },
        { label: 'Ngày tạo', key: 'created_at', type: 'date' },
      ],
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
        if (this.searchQuery) params.file_url = this.searchQuery;
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
      return new Date(date).toLocaleString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
      });
    },
    applySearch() {
      this.fetchReports();
    },
    openReportModal(report) {
      this.selectedReport = { ...report };
      this.showModal = true;
    },
    closeReportModal() {
      this.showModal = false;
      this.selectedReport = null;
    },
  },
};
</script>