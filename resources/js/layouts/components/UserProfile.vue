<template>
  <VBadge dot location="bottom right" offset-x="3" offset-y="3" color="success" bordered>
    <VAvatar class="cursor-pointer" color="primary" variant="tonal">
      <VImg :src="avatar1" />

      <!-- SECTION Menu -->
      <VMenu activator="parent" width="230" location="bottom end" offset="14px">
        <VList>
          <!-- 👉 User Avatar & Name -->
          <VListItem>
            <template #prepend>
              <VListItemAction start>
                <VBadge dot location="bottom right" offset-x="3" offset-y="3" color="success">
                  <VAvatar color="primary" variant="tonal">
                    <VImg :src="avatar1" />
                  </VAvatar>
                </VBadge>
              </VListItemAction>
            </template>

            <VListItemTitle class="font-weight-semibold">
              {{ user_name }}
            </VListItemTitle>
            <VListItemSubtitle>{{ role_name }}</VListItemSubtitle>
          </VListItem>
          <VDivider class="my-2" />

          <!-- 👉 Profile -->
          <!-- <VListItem link>
            <template #prepend>
              <VIcon
                class="me-2"
                icon="bx-user"
                size="22"
              />
            </template>

            <VListItemTitle>Profile</VListItemTitle>
          </VListItem> -->

          <!-- 👉 Settings -->
          <!-- <VListItem link>
            <template #prepend>
              <VIcon
                class="me-2"
                icon="bx-cog"
                size="22"
              />
            </template>

            <VListItemTitle>Settings</VListItemTitle>
          </VListItem> -->

          <!-- 👉 Pricing -->
          <!-- <VListItem link>
            <template #prepend>
              <VIcon
                class="me-2"
                icon="bx-dollar"
                size="22"
              />
            </template>

            <VListItemTitle>Pricing</VListItemTitle>
          </VListItem> -->

          <!-- 👉 FAQ -->
          <!-- <VListItem link>
            <template #prepend>
              <VIcon
                class="me-2"
                icon="bx-help-circle"
                size="22"
              />
            </template>

            <VListItemTitle>FAQ</VListItemTitle>
          </VListItem> -->

          <!-- Divider -->
          <!-- <VDivider class="my-2" /> -->

          <!-- 👉 Logout -->
          <VListItem @click="handleLogout">
            <template #prepend>
              <VIcon class="me-2" icon="bx-log-out" size="22" />
            </template>

            <VListItemTitle>Logout</VListItemTitle>
          </VListItem>
        </VList>
      </VMenu>
      <!-- !SECTION -->
    </VAvatar>
  </VBadge>
</template>

<script>
import avatar1 from '@images/avatars/avatar-1.png'
import { request } from '../../helper'
import Swal from 'sweetalert2';
export default {
  data() {
    return {
      avatar1: avatar1,
      user_name: localStorage.getItem('USER_NAME'),
      role_name: localStorage.getItem('ROLE_NAME'),
      url: this.$hostname + '/api/logout',
      // currentComponent: markRaw(dashboard)
    }
  },
  methods: {
    // toggleLoading() {
    //     this.loading = !this.loading;
    // },
    // loadComponent(component) {
    //     this.currentComponent = component;
    // },
    async handleLogout() {
      Swal.fire({
        title: 'Do you want to Logout?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, continue!',
        cancelButtonText: 'No, cancel!',
        reverseButtons: true  // Reverse the order of the cancel and confirm buttons
      }).then(async (result) => {
        if (result.isConfirmed) {
          const response = await request('post', this.url)
          if (response.data.status == "success") {
            localStorage.removeItem('APP_TOKEN')
            localStorage.removeItem('USER_NAME')
            localStorage.removeItem('ROLE_NAME')
            localStorage.removeItem('PERMISSIONS')
            this.$router.push('/')
          }
        }
      });
    }
  },
}
</script>
