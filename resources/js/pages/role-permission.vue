<script setup>
import { useRoute } from 'vue-router'
import AllRole from '@/views/pages/role/AllRole.vue'
import AddRole from '@/views/pages/role/AddRole.vue'

const route = useRoute()
const activeTab = ref(route.params.tab)

const tabs = []

onMounted(() => {
  checkPermission()
})

const checkPermission = async () => {
  if (JSON.parse(localStorage.getItem('PERMISSIONS')).some(item => item['name'] === 'role-read')) {
    tabs.push({
      title: 'All Role',
      icon: 'bx-cube',
      tab: 'allRole',
    })
  }
  if (JSON.parse(localStorage.getItem('PERMISSIONS')).some(item => item['name'] === 'role-create')) {
    tabs.push({
      title: 'Add Role',
      icon: 'bx-plus',
      tab: 'addRole',
    })
  }
};

</script>

<template>
  <div>
    <VTabs v-model="activeTab" show-arrows>
      <VTab v-for="item in tabs" :key="item.icon" :value="item.tab">
        <VIcon size="20" start :icon="item.icon" />
        {{ item.title }}
      </VTab>
    </VTabs>
    <VDivider />

    <VWindow v-model="activeTab" class="mt-5 disable-tab-transition">
      <!-- allUser -->
      <VWindowItem value="allRole">
        <AllRole />
      </VWindowItem>

      <!-- addUser -->
      <VWindowItem value="addRole">
        <AddRole />
      </VWindowItem>

    </VWindow>
  </div>
</template>
