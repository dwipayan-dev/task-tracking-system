<template>
    <div>
        <VIcon size="40" class="mr-2" @click="this.$router.go(-1)" :icon="'bx-arrow-back'" />
        <VWindow>

            <VCard title="">
                <VForm @submit.prevent="UpdateRole" ref="form">
                    <VCardText>
                        <VRow>
                            <!-- 👉 Role Name -->
                            <VCol md="6" cols="12">

                                <VTextField label="Role Name" v-model="role_name" placeholder="Admin" type="text"
                                    disabled="" />
                            </VCol>
                        </VRow>
                    </VCardText>
                    <VDivider />

                    <VTable class="text-no-wrap">
                        <thead>
                            <tr>
                                <th scope="col">
                                    Pages
                                </th>
                                <th scope="col">
                                    Create
                                </th>
                                <th scope="col">
                                    Read
                                </th>
                                <th scope="col">
                                    Update
                                </th>
                                <th scope="col">
                                    Delete
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="permission in permissions">
                                <td>
                                    {{ permission.name.replace(/_/g, ' ') }}
                                </td>
                                <td>
                                    <VCheckbox v-if="permission.value.create != null" v-model="selectedPermissions"
                                        :checked="selectedPermissions.includes(permission.value.id)"
                                        :value="permission.value.create" />
                                </td>
                                <td>
                                    <VCheckbox v-if="permission.value.read != null"
                                        :checked="selectedPermissions.includes(permission.value.id)"
                                        v-model="selectedPermissions" :value="permission.value.read" />
                                </td>
                                <td>
                                    <VCheckbox v-if="permission.value.update != null" v-model="selectedPermissions"
                                        :checked="selectedPermissions.includes(permission.value.id)"
                                        :value="permission.value.update" />
                                </td>
                                <td>
                                    <VCheckbox v-if="permission.value.delete != null" v-model="selectedPermissions"
                                        :checked="selectedPermissions.includes(permission.value.id)"
                                        :value="permission.value.delete" />
                                </td>
                            </tr>
                        </tbody>
                    </VTable>
                    <VDivider />
                    <VCardText>
                        <div class="d-flex flex-wrap gap-4 mt-4">
                            <VBtn type="submit">
                                Update Changes
                            </VBtn>
                        </div>
                    </VCardText>

                </VForm>
            </VCard>
        </VWindow>
    </div>
    <Loader :isLoading="loading"></Loader>

</template>
<script>
import { showPopup } from '../helper'
import Loader from "./../components/Loader.vue";
import axios from 'axios';

export default {
    data() {
        return {
            loading: false,
            permissions: [],
            selectedPermissions: [],
            role_name: null,
        }
    },

    mounted() {
        this.getRoleData()
        this.allPermission()
    },
    methods: {
        async getRoleData() {
            this.loading = true
            const token = localStorage.getItem('APP_TOKEN')
            const headers = {
                headers: {
                    Authorization: 'Bearer ' + token,
                    Accept: 'application/json',
                }
            }
            axios.get(this.$hostname + "/api/role-permission/edit/" + this.$route.params.id, headers)
                .then(async response => {
                    this.role_name = response.data.result.display_name;
                    this.selectedPermissions = response.data.result.get_permissions.map(permission => permission.permission_id);
                    this.loading = false
                })
                .catch(async error => {
                    this.loading = false
                    await showPopup('Error', error.response.data.message, error.response.data.status)
                    this.$router.go(-1)
                });
        },
        async allPermission() {
            this.loading = true
            const token = localStorage.getItem('APP_TOKEN')
            const headers = {
                headers: {
                    Authorization: 'Bearer ' + token,
                    Accept: 'application/json',
                }
            }
            axios.get(this.$hostname + "/api/role-permission/create", headers)
                .then(async response => {
                    this.permissions = response.data.result;
                    this.loading = false
                })
                .catch(async error => {
                    this.loading = false
                    await showPopup('Error', error.response.data.message, error.response.data.status)
                });
        },

        async UpdateRole() {
            if (this.selectedPermissions.length == 0) {
                await showPopup('warning', 'Select at least one permission', 'warning')
                return false;
            }
            this.loading = true
            const request_data = {
                'id': this.$route.params.id,
                'permissions': this.selectedPermissions,
            }
            const token = localStorage.getItem('APP_TOKEN')
            const headers = {
                headers: {
                    Authorization: 'Bearer ' + token,
                    Accept: 'application/json',
                }
            }
            axios.post(this.$hostname + "/api/role-permission/update", request_data, headers)
                .then(async response => {
                    this.loading = false
                    await showPopup(response.data.status, response.data.message, response.data.status)
                    this.selectedPermissions = this.selectedPermissions.map(() => false);
                    this.selectedPermissions = [];
                    this.$router.go(-1)
                })
                .catch(async error => {
                    this.loading = false
                    await showPopup('Error', error.response.data.message, error.response.data.status)
                    this.$router.go(-1)
                });
        },
    },
    components: {
        Loader
    }
}
</script>