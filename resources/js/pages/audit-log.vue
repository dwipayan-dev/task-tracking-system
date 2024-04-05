<script setup>
import { useRoute } from 'vue-router'
import TaskLog from '@/views/pages/audit-log/TaskLog.vue'
import AttendanceLog from '@/views/pages/audit-log/AttendanceLog.vue'
const route = useRoute()
const activeTab = ref(route.params.tab)
const tabs = []

onMounted(() => {
    checkPermission()
})

const checkPermission = async () => {
    if (JSON.parse(localStorage.getItem('PERMISSIONS')).some(item => item['name'] === 'audit_log-read')) {
        tabs.push({
            title: 'Task Log',
            icon: 'bx-book-open',
            tab: 'taskLog',
        }, {
            title: 'Attendance Log',
            icon: 'bx-edit-alt',
            tab: 'attendanceLog',
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
            <VWindowItem value="taskLog">
                <TaskLog />
            </VWindowItem>

            <VWindowItem value="attendanceLog">
                <AttendanceLog />
            </VWindowItem>
        </VWindow>
    </div>
</template>
