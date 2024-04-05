<script setup>
import { useRoute } from 'vue-router'
import Dashboard from '@/views/pages/manage-task/Dashboard.vue'
import AddTask from '@/views/pages/manage-task/AddTask.vue'
const route = useRoute()
const activeTab = ref(route.params.tab)

const tabs = []

onMounted(() => {
  checkPermission()
})

const checkPermission = async () => {
  if (JSON.parse(localStorage.getItem('PERMISSIONS')).some(item => item['name'] === 'manage_task-read')) {
    tabs.push({
      title: 'Dashboard',
      icon: 'bx-home',
      tab: 'dashboard',
    })
  }
  if (JSON.parse(localStorage.getItem('PERMISSIONS')).some(item => item['name'] === 'manage_task-create')) {
    tabs.push({
      title: 'Add Task',
      icon: 'bx-plus',
      tab: 'addTask',
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
      <VWindowItem value="dashboard">
        <Dashboard />
      </VWindowItem>

      <!-- addUser -->
      <VWindowItem value="addTask">
        <AddTask />
      </VWindowItem>

    </VWindow>
  </div>
</template>
