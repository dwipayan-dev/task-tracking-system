<script setup>
import { useRoute } from 'vue-router'
import AllUser from '@/views/pages/users/AllUser.vue'
import AddUser from '@/views/pages/users/AddUser.vue'

const route = useRoute()
const activeTab = ref(route.params.tab)

const tabs = []

onMounted(() => {
  checkPermission()
})

const checkPermission = async () => {
  if (JSON.parse(localStorage.getItem('PERMISSIONS')).some(item => item['name'] === 'employee-read')) {
    tabs.push({
      title: 'All Employee',
      icon: 'bx-user',
      tab: 'allUser',
    })
  }
  if (JSON.parse(localStorage.getItem('PERMISSIONS')).some(item => item['name'] === 'employee-create')) {
    tabs.push({
      title: 'Add Employee',
      icon: 'bx-plus',
      tab: 'addUser',
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
      <VWindowItem value="allUser">
        <AllUser />
      </VWindowItem>

      <!-- addUser -->
      <VWindowItem value="addUser">
        <AddUser />
      </VWindowItem>

    </VWindow>
  </div>
</template>
