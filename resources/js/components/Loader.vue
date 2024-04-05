<!-- Loader.vue -->

<template>
  <div v-if="isLoading" class="loader-container">
    <svg viewBox="-2000 -1000 4000 2000">
      <path id="inf" d="M354-354A500 500 0 1 1 354 354L-354-354A500 500 0 1 0-354 354z"></path>
      <use xlink:href="#inf" stroke-dasharray="1570 5143" stroke-dashoffset="6713px"></use>
    </svg>
  </div>
</template>
  
<script>
export default {
  props: {
    isLoading: {
      type: Boolean,
      required: true
    }
  }
};
</script>
  
<style scoped>
.loader-container {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

svg {
  max-width: 12em;
  border-radius: 3px;
  /* box-shadow: 2px 2px 5px #000; */
  /* background: #111; */
  fill: none;
  stroke: grey;
  stroke-linecap: round;
  stroke-width: 8%
}

use {
  stroke: #111;
  animation: a 2s linear infinite
}

@keyframes a {
  to {
    stroke-dashoffset: 0px
  }
}
</style>
  