<template>
    <VCard title="">
        <VForm @submit.prevent="SubmitRole" ref="form">
            <VCardText>
                <VRow>
                    <!-- 👉 Role Name -->
                    <VCol md="6" cols="12">

                        <VTextField label="Role Name" v-model="role_name" placeholder="Admin" type="text"
                            :rules="validRole" />
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
                                :value="permission.value.create" />
                        </td>
                        <td>
                            <VCheckbox v-if="permission.value.read != null" v-model="selectedPermissions"
                                :value="permission.value.read" />
                        </td>
                        <td>
                            <VCheckbox v-if="permission.value.update != null" v-model="selectedPermissions"
                                :value="permission.value.update" />
                        </td>
                        <td>
                            <VCheckbox v-if="permission.value.delete != null" v-model="selectedPermissions"
                                :value="permission.value.delete" />
                        </td>
                    </tr>
                </tbody>
            </VTable>
            <VDivider />
            <VCardText>
                <div class="d-flex flex-wrap gap-4 mt-4">
                    <VBtn type="submit">
                        Save Changes
                    </VBtn>
                    <VBtn color="secondary" variant="tonal" type="reset" @click.prevent="Reset">
                        Reset
                    </VBtn>
                </div>
            </VCardText>

        </VForm>
    </VCard>
    <Loader :isLoading="loading"></Loader>

</template>

<style lang="scss" scoped>
.v-table {
    th {
        text-align: start !important;
    }
}
</style>

<script>
import { showPopup } from '../../../helper'
import Loader from "./../../../components/Loader.vue";
import axios from 'axios';

export default {
    data() {
        return {
            loading: false,
            permissions: [],
            selectedPermissions: [],
            role_name: null,
            validRole: [
                v => !!v || 'Please enter a valid role name.',
            ],
        }
    },

    mounted() {
        this.allPermission()
    },
    methods: {

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

        async SubmitRole() {
            const { valid } = await this.$refs.form.validate()
            if (valid) {
                if (this.selectedPermissions.length == 0) {
                    await showPopup('warning', 'Select at least one permission', 'warning')
                    return false;
                }
                this.loading = true
                const request_data = {
                    'role_name': this.role_name,
                    'permissions': this.selectedPermissions,
                }
                const token = localStorage.getItem('APP_TOKEN')
                const headers = {
                    headers: {
                        Authorization: 'Bearer ' + token,
                        Accept: 'application/json',
                    }
                }
                axios.post(this.$hostname + "/api/role-permission/store", request_data, headers)
                    .then(async response => {
                        this.loading = false
                        await showPopup(response.data.status, response.data.message, response.data.status)
                        this.$refs.form.reset()
                        this.selectedPermissions = this.selectedPermissions.map(() => false);
                        this.selectedPermissions = [];
                    })
                    .catch(async error => {
                        this.loading = false
                        await showPopup('Error', error.response.data.message, error.response.data.status)
                    });
            }
        },
        Reset() {
            this.$refs.form.reset()
            this.selectedPermissions = this.selectedPermissions.map(() => false);
            this.selectedPermissions = [];
        }
    },
    components: {
        Loader

    }
}
</script>
