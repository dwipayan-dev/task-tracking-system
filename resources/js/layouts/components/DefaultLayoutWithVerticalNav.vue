<script setup>
import { useTheme } from 'vuetify'
import VerticalNavSectionTitle from '@/@layouts/components/VerticalNavSectionTitle.vue'
import upgradeBannerDark from '@images/pro/upgrade-banner-dark.png'
import upgradeBannerLight from '@images/pro/upgrade-banner-light.png'
import VerticalNavLayout from '@layouts/components/VerticalNavLayout.vue'
import VerticalNavLink from '@layouts/components/VerticalNavLink.vue'

// Components
import Footer from '@/layouts/components/Footer.vue'
import NavbarThemeSwitcher from '@/layouts/components/NavbarThemeSwitcher.vue'
import UserProfile from '@/layouts/components/UserProfile.vue'
import { showPopup } from '../../helper'
import { URL } from '../../helper'
import { request } from '../../helper'
import axios from 'axios';
import router from '@/router'
import { onBeforeUnmount } from 'vue';

const handleBeforeUnload = () => {
  // request('get', URL + "/api/logout")
  // localStorage.removeItem('APP_TOKEN')
  // localStorage.removeItem('USER_NAME')
  // localStorage.removeItem('ROLE_NAME')
  // localStorage.removeItem('PERMISSIONS')
  // fetch('/api/logout', {
  //   method: 'GET',
  //   credentials: 'same-origin' // Send cookies with the request
  // });
};
window.addEventListener('beforeunload', handleBeforeUnload);
onBeforeUnmount(() => {
  window.removeEventListener('beforeunload', handleBeforeUnload);
});

const vuetifyTheme = useTheme()

const upgradeBanner = computed(() => {
  return vuetifyTheme.global.name.value === 'light' ? upgradeBannerLight : upgradeBannerDark
})
const attendance = ref('0')
const available = ref(0)
const role = ref('admin')
const permission = ref([])

onMounted(async () => {
  await fetchUserData()
})

const fetchUserData = async () => {
  const token = localStorage.getItem('APP_TOKEN')
  const headers = {
    headers: {
      Authorization: 'Bearer ' + token,
      Accept: 'application/json',
    }
  }

  axios.get(URL + "/api/user-details", headers)
    .then(async response => {
      role.value = response.data.result.roles[0]['name'];
      permission.value = response.data.result.roles[0]['permissions'];
      available.value = response.data.result.availability;
      attendance.value = response.data.result.get_attendance == null ? '0' : response.data.result.get_attendance.status;
      localStorage.setItem('PERMISSIONS', JSON.stringify(response.data.result.roles[0]['permissions']))
    })
    .catch(async error => {
      await showPopup('Error', error.response.data.message, error.response.data.status)
      if (error.response.status == 401) {
        localStorage.removeItem('APP_TOKEN')
        localStorage.removeItem('USER_NAME')
        localStorage.removeItem('ROLE_NAME')
        localStorage.removeItem('PERMISSIONS')
        router.push({ path: '/' })
      }
    });
};

const changeAvailability = async () => {
  const token = localStorage.getItem('APP_TOKEN')
  const headers = {
    headers: {
      Authorization: 'Bearer ' + token,
      Accept: 'application/json',
    }
  }
  const request = {
    "availability": available.value
  }
  axios.post(URL + "/api/change-employee-availability", request, headers)
    .then(async response => {
      await showPopup(response.data.status, response.data.message, response.data.status)
    })
    .catch(async error => {
      await showPopup('Error', error.response.data.message, error.response.data.status)
    });
};

const changeAttendance = async () => {
  const token = localStorage.getItem('APP_TOKEN')
  const headers = {
    headers: {
      Authorization: 'Bearer ' + token,
      Accept: 'application/json',
    }
  }
  const request = {
    "status": attendance.value
  }
  axios.post(URL + "/api/change-employee-attendance", request, headers)
    .then(async response => {
      await showPopup(response.data.status, response.data.message, response.data.status)
    })
    .catch(async error => {
      await showPopup('Error', error.response.data.message, error.response.data.status)
    });
};

const isValueInArray = (value) => {
  return permission.value.some(item => item['name'] === value)
};
</script>

<template>
  <VerticalNavLayout>
    <!-- 👉 navbar -->
    <template #navbar="{ toggleVerticalOverlayNavActive }">
      <div class="d-flex h-100 align-center">
        <!-- 👉 Vertical nav toggle in overlay mode -->
        <IconBtn class="ms-n3 d-lg-none" @click="toggleVerticalOverlayNavActive(true)">
          <VIcon icon="bx-menu" />
        </IconBtn>

        <!-- 👉 Search -->
        <div class="d-flex align-center cursor-pointer" style="user-select: none;">
          <h2 class="leading-normal">
            Task Tracking System
          </h2>
        </div>
        <VSpacer />
        <v-btn-toggle mandatory v-if="role != 'admin'" v-model="available" color="info" group variant="outlined"
          rounded="xl" divided>
          <v-btn :value="0" color="success" :disabled="available === 0 ? true : false" @click="changeAvailability">
            Available
          </v-btn>
          <v-btn :value="1" color="error" :disabled="available === 1 ? true : false" @click="changeAvailability">
            Not Available
          </v-btn>
        </v-btn-toggle>
        <VSpacer />
        <v-btn-toggle mandatory v-if="role != 'admin'" v-model="attendance" color="info" group variant="outlined"
          rounded="xl" divided>
          <v-btn value="0" color="error"
            :disabled="attendance === '0' || attendance === '1' || attendance === '2' ? true : false">
            Absent
          </v-btn>

          <v-btn value="1" color="success" :disabled="attendance === '1' ? true : false" @click="changeAttendance">
            Present
          </v-btn>

          <v-btn value="2" color="warning" :disabled="attendance === '2' ? true : false" @click="changeAttendance">
            Workfrom Home
          </v-btn>
        </v-btn-toggle>
        <VSpacer />

        <NavbarThemeSwitcher class="me-2" />
        <UserProfile />
      </div>
    </template>

    <template #vertical-nav-content>
      <VerticalNavLink :item="{
      title: 'Dashboard',
      icon: 'bx-home',
      to: '/admin/dashboard',
    }" v-if="isValueInArray('dashboard-read')" />
      <VerticalNavLink :item="{
      title: 'Employee',
      icon: 'bx-user',
      to: '/admin/employee',
    }" v-if="isValueInArray('employee-read') || isValueInArray('employee-create')" />
      <VerticalNavLink :item="{
      title: 'Role and Permission',
      icon: 'bx-cube',
      to: '/admin/role-permission',
    }" v-if="isValueInArray('role-read') || isValueInArray('role-create')" />
      <VerticalNavLink :item="{
      title: 'All Task',
      icon: 'bxs-file',
      to: '/admin/all-task',
    }" v-if="isValueInArray('all_task-read') || isValueInArray('all_task-create')" />
      <VerticalNavLink :item="{
      title: 'Manage Task',
      icon: 'bxs-report',
      to: '/admin/manage-task',
    }" v-if="isValueInArray('manage_task-read') || isValueInArray('manage_task-create')" />
      <VerticalNavLink :item="{
      title: 'Audit Log',
      icon: 'bxl-audible',
      to: 'audit-log',
    }" v-if="isValueInArray('audit_log-read')" />

      <VerticalNavLink :item="{
      title: 'Video Chat',
      icon: 'bx-video',
      to: 'video-chat',
    }" />

    </template>
    <slot />
    <!-- 👉 Footer -->

    <template #footer>
      <Footer />
    </template>
  </VerticalNavLayout>
</template>

<style lang="scss" scoped>
.meta-key {
  border: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 6px;
  block-size: 1.5625rem;
  line-height: 1.3125rem;
  padding-block: 0.125rem;
  padding-inline: 0.25rem;
}

.v-btn--disabled {
  opacity: 1
}
</style>
