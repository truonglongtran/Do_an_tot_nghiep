<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Báo cáo</h1>
    <AdminFilterSearch
      :report-types="reportTypes"
      :shop-names="shopNames"
      @update:filters="fetchReports"
    />
    <div v-if="error" class="bg-red-100 text-red-700 p-4 rounded mb-4">
      {{ error }}
    </div>
    <table class="w-full bg-white rounded shadow">
      <thead>
        <tr>
          <th class="p-2">ID</th>
          <th class="p-2">Loại báo cáo</th>
          <th class="p-2">Loại báo cáo</th>
          <th class="p-2">File</th>
          <th class="p-2">Ngày tạo</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="report in reports" :key="report.id">
          <td class="p-2">{{ report.id }}</td>
          <td class="p-2">{{ report.report_type }}</td>
          <td class="p-2">{{ report.shop_name }}</td>
          <td class="p-2">
            <a :href="report.file_url" class="text-blue-500 hover:underline" download>Tải xuống</a>
          </td>
          <td class="p-2">{{ formatDate(report.created_at) }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import axios from 'axios';
import { ref } from 'vue';
import AdminFilterSearch from './component/AdminFilterSearch.vue';

export default {
  name: 'AdminReports',
  components: { AdminFilterSearch },
  setup() {
    const reports = ref([]);
    const reportTypes = ref([]);
    const shopNames = ref([]);
    const error = ref(null);

    const fetchReports = async (filters = {}) => {
      try {
        error.value = null;
        const response = await axios.get('/admin/reports', {
          params: filters,
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        reports.value = response.data.reports;
        reportTypes.value = response.data.report_types;
        shopNames.value = response.data.shop_names;
      } catch (err) {
        error.value = err.response?.data?.error || 'Lỗi khi lấy báo cáo';
        console.error('Fetch reports error:', err);
      }
    };

    const formatDate = (date) => {
      return date ? new Date(date).toLocaleDateString('vi-VN') : 'N/A';
    };

    fetchReports();

    return { reports, reportTypes, shopNames, error, fetchReports, formatDate };
  },
};
</script>