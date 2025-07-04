<template>
  <div class="relative w-full">
    <div class="overflow-hidden">
      <!-- Wrapper với các slide -->
      <div
        class="flex"
        :class="{
          'transition-transform duration-500 ease-in-out': !isHardJump,
          'transition-none': isHardJump
        }"
        :style="{ transform: `translateX(-${(currentIndex + 1) * 100}%)` }"
      >
        <!-- Bản sao cuối -->
        <a
          :href="sortedBanners[sortedBanners.length - 1].link_url"
          class="flex-shrink-0 w-full"
        >
          <img
            :src="sortedBanners[sortedBanners.length - 1].img_url"
            :alt="sortedBanners[sortedBanners.length - 1].title"
            class="w-full h-48 object-cover rounded-lg"
          />
        </a>

        <!-- Slide thật -->
        <a
          v-for="banner in sortedBanners"
          :key="banner.id"
          :href="banner.link_url"
          class="flex-shrink-0 w-full"
        >
          <img
            :src="banner.img_url"
            :alt="banner.title"
            class="w-full h-48 object-cover rounded-lg"
          />
        </a>

        <!-- Bản sao đầu -->
        <a
          :href="sortedBanners[0].link_url"
          class="flex-shrink-0 w-full"
        >
          <img
            :src="sortedBanners[0].img_url"
            :alt="sortedBanners[0].title"
            class="w-full h-48 object-cover rounded-lg"
          />
        </a>
      </div>
    </div>

    <!-- Nút điều hướng -->
    <button
      v-if="sortedBanners.length > 1"
      @click="prevSlide"
      class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-full"
    >
      ←
    </button>
    <button
      v-if="sortedBanners.length > 1"
      @click="nextSlide"
      class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-full"
    >
      →
    </button>

    <!-- Dots -->
    <div v-if="sortedBanners.length > 1" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2">
      <span
        v-for="(banner, index) in sortedBanners"
        :key="banner.id"
        :class="{ 'bg-orange-500': currentIndex === index, 'bg-gray-300': currentIndex !== index }"
        class="w-3 h-3 rounded-full cursor-pointer"
        @click="goToSlide(index)"
      ></span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BannerCarousel',
  props: {
    banners: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      currentIndex: 0,
      isTransitioning: false,
      isHardJump: false,
      intervalId: null,
    };
  },
  computed: {
    sortedBanners() {
      return [...this.banners].sort((a, b) => (a.display_order || 0) - (b.display_order || 0));
    },
  },
  mounted() {
    if (this.sortedBanners.length > 1) {
      this.startAutoSlide();
    }
  },
  beforeUnmount() {
    this.stopAutoSlide();
  },
  methods: {
    nextSlide() {
      if (this.isTransitioning || this.sortedBanners.length <= 1) return;
      this.isTransitioning = true;
      this.currentIndex++;

      setTimeout(() => {
        this.isTransitioning = false;

        if (this.currentIndex >= this.sortedBanners.length) {
          this.isHardJump = true;
          this.currentIndex = 0;
          this.$nextTick(() => {
            this.isHardJump = false;
          });
        }
      }, 500);
    },
    prevSlide() {
      if (this.isTransitioning || this.sortedBanners.length <= 1) return;
      this.isTransitioning = true;
      this.currentIndex--;

      setTimeout(() => {
        this.isTransitioning = false;

        if (this.currentIndex < 0) {
          this.isHardJump = true;
          this.currentIndex = this.sortedBanners.length - 1;
          this.$nextTick(() => {
            this.isHardJump = false;
          });
        }
      }, 500);
    },
    goToSlide(index) {
      if (this.isTransitioning || this.currentIndex === index) return;
      this.isTransitioning = true;
      this.currentIndex = index;
      setTimeout(() => {
        this.isTransitioning = false;
      }, 500);
    },
    startAutoSlide() {
      this.intervalId = setInterval(() => {
        this.nextSlide();
      }, 5000);
    },
    stopAutoSlide() {
      if (this.intervalId) clearInterval(this.intervalId);
    },
  },
};
</script>

<style scoped>
/* Tailwind CSS đã xử lý phần lớn style */
</style>
