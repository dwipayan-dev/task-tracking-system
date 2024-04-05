<script setup>
import ErrorHeader from '@/components/ErrorHeader.vue'
import misc404 from '@images/pages/404.png'
</script>

<template>
  <div class="misc-wrapper">
    <ErrorHeader
      error-code="403"
      error-title="Access Denied ✋"
      error-description="You don't have the permission to view this page."
    />

    <!-- 👉 Image -->
    <div class="misc-avatar w-100 text-center">
      <VImg
        :src="misc404"
        alt="Coming Soon"
        :max-width="800"
        class="mx-auto"
      />
      <VBtn
        to="/admin/dashboard"
        class="mt-10"
      >
        Back to Home
      </VBtn>
    </div>
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/misc.scss";
</style>
