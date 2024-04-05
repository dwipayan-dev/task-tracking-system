<template>
    <VRow>
        <VCol cols="6" style="padding-bottom: 20px;">
        </VCol>
        <VCol cols="2">
        </VCol>
        <VCol cols="2">
        </VCol>
        <VCol cols="1">
        </VCol>
        <VCol cols="1" style="line-height: 2.5;text-align: end;">
            <VTooltip activator="parent" location="top">Refresh</VTooltip>
            <VIcon size="30" color="success" class="mr-2" :icon="'bx-refresh'" @click="Reload" />
        </VCol>
    </VRow>
    <VRow>
        <VCol cols="8">
            <VTable fixed-header density="compact">
                <thead>
                    <tr>
                        <th class="text-uppercase">
                            #
                        </th>
                        <th>
                            Role Name
                        </th>
                        <th style="width: 50%;">
                            Permissions
                        </th>
                        <th>
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="(item, index) in roles" :key="item.index" v-if="roles.length > 0">
                        <td>
                            {{ ++index }}
                        </td>
                        <td class="text-center">
                            {{ item.display_name }}
                        </td>
                        <td class="text-center">
                            <VBadge v-for="per in item.get_permissions" size="small"
                                :content="per.get_permissions_name.display_name.replace(/_/g, ' ')" inline>
                            </VBadge>
                        </td>
                        <td class="text-center">
                            <VIcon size="15" class="mr-2" :icon="'bx-edit'"
                                @click="this.$router.push('edit-role-permission/' + item.id)" v-if="edit_permission" />
                            <VIcon size="15" :icon="'bx-trash'" @click="DeleteRole(item.id)" v-if="delete_permission" />
                        </td>
                    </tr>
                    <tr v-else>
                        <td colspan="3" class="text-center" style="color:rgb(255,62,29);">
                            <b>No Records Found</b>
                        </td>
                    </tr>
                </tbody>
            </VTable>
        </VCol>
        <VCol cols="4">
            <VCard class="text-center text-sm-start">
                <VCardItem>
                    <VCardTitle>
                        Role & Permissions
                    </VCardTitle>
                </VCardItem>
                <VCardText>
                    User roles and permissions enable a more personalized experience for end-users by tailoring
                    their access based on individual needs. By granting specific privileges according to each role,
                    you can create a customized environment where users have access to the most relevant resources,
                    improving their user experience.
                    
                </VCardText>
                <v-card-actions>
                    <div class="text-decoration-none" style="cursor: pointer;color: #3B71CA">Learn More</div><VIcon class="ml-2" :icon="'bx-tab'"
                        color="#3B71CA" />
                </v-card-actions>
            </VCard>
        </VCol>
    </VRow>
    <Loader :isLoading="loading"></Loader>

</template>

<script>
import { showPopup } from '../../../helper'
import Loader from "./../../../components/Loader.vue";
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    data() {
        return {
            loading: false,
            delete_permission: false,
            edit_permission: false,
            roles: [],
        }
    },
    mounted() {
        this.allRole()
        this.checkPermission()

    },
    methods: {
        checkPermission() {
            if (JSON.parse(localStorage.getItem('PERMISSIONS')).some(item => item['name'] === 'role-update')) {
                this.edit_permission = true
            } else {
                this.edit_permission = false
            }

            if (JSON.parse(localStorage.getItem('PERMISSIONS')).some(item => item['name'] === 'role-delete')) {
                this.delete_permission = true
            } else {
                this.delete_permission = false
            }
        },
        async allRole() {
            this.loading = true
            const token = localStorage.getItem('APP_TOKEN')
            const headers = {
                headers: {
                    Authorization: 'Bearer ' + token,
                    Accept: 'application/json',
                }
            }
            axios.get(this.$hostname + "/api/role-permission", headers)
                .then(async response => {
                    this.roles = response.data.result;
                    this.loading = false
                })
                .catch(async error => {
                    this.loading = false
                    await showPopup('Error', error.response.data.message, error.response.data.status)
                });
        },
        Reload() {
            this.allRole()
        },
        DeleteRole(id) {
            Swal.fire({
                title: 'Do you want to delete this role?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, continue!',
                cancelButtonText: 'No, cancel!',
                reverseButtons: true
            }).then(async (result) => {
                if (result.isConfirmed) {
                    this.loading = true
                    const token = localStorage.getItem('APP_TOKEN')
                    const headers = {
                        headers: {
                            Authorization: 'Bearer ' + token,
                            Accept: 'application/json',
                        }
                    }
                    const request_data = {
                        'role_id': id,
                    }
                    axios.post(this.$hostname + "/api/role-permission/delete", request_data, headers)
                        .then(async response => {
                            this.loading = false
                            await showPopup(response.data.status, response.data.message, response.data.status)
                            this.allRole()

                        })
                        .catch(async error => {
                            this.loading = false
                            await showPopup('Error', error.response.data.message, error.response.data.status)
                        });
                }
            });
        },

    },
    components: {
        Loader
    }
}
</script>
