<template>
    <VRow>
        <VCol cols="6" style="padding-bottom: 20px;">
            <v-text-field :loading="searchLoading" v-model="searchEmployee" density="compact" variant="solo"
                label="Search...." append-inner-icon="mdi-magnify" single-line hide-details
                @click:append-inner="Search"></v-text-field>
        </VCol>
        <VCol cols="2">
        </VCol>
        <VCol cols="2">
        </VCol>
        <VCol cols="1">
        </VCol>
        <VCol cols="1" style="line-height: 2.5;text-align: end;">
            <VIcon size="30" color="success" class="mr-2" :icon="'bx-refresh'" @click="Reload" />
            <VTooltip activator="parent" location="top">Refresh</VTooltip>
        </VCol>
    </VRow>
    <VTable fixed-header density="compact" style="font-size: 12px;">
        <thead>
            <tr>
                <th class="text-uppercase">
                    #
                </th>
                <th>
                    Name
                </th>
                <th>
                    Role
                </th>
                <th>
                    Team
                </th>
                <th>
                    Position
                </th>
                <th>
                    Status
                </th>
                <th v-if="edit_permission">
                    Action
                </th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(item, index) in employee" :key="index" v-if="employee.length>0"> 
                <td>
                    {{ item.count }}
                </td>
                <td>
                    <v-badge :color="item.availability === 0 && item.get_attendance != null ? 'success' : 'error'" dot
                        floating location="left">
                        &nbsp;&nbsp;{{ item.name }}
                    </v-badge>
                    <p class="mb-1" v-if="item.employee_id != null" style="font-size: 9px;color: grey;">{{
                item.employee_id }}
                    </p>
                    <p class="mb-1" style="font-size: 9px;color: grey;">{{ item.email }} </p>
                </td>
                <td class="text-center">
                    {{ item?.get_role?.get_role_details?.display_name }}
                </td>
                <td class="text-center">
                    {{ item?.get_team?.name }}
                </td>
                <td class="text-center">
                    {{ item?.position }}
                </td>
                <td class="text-center">
                    <VBadge :color="item.status == 0 ? 'error' : 'success'"
                        :content="item.status == 0 ? 'In Active' : 'Active'" inline>
                    </VBadge>
                </td>
                <td v-if="edit_permission" class="text-center">
                    <VDialog width="500">
                        <template v-slot:activator="{ props }">
                            <VIcon v-bind="props" size="15" class="mr-2" :icon="'bx-edit'"
                                @click="editEmployee(item.status, item?.get_role?.role_id, item.position)" />
                            <!-- <VBtn v-bind="props" color="primary" variant="tonal" size="small">
                                Make Payment
                            </VBtn> -->
                        </template>

                        <template v-slot:default="{ isActive }">
                            <VCard title="Edit Employee">
                                <VForm class="mt-6" @submit.prevent="EmployeeDataUpdate(item.id, item.uuid)"
                                    :ref="`updateForm_${item.id}`">
                                    <VCardText>

                                        <VRow>
                                            <VCol cols="12" md="6">
                                                <VTextField label="Password" v-model="editPassword"
                                                    placeholder="············" :type="'password'"
                                                    :rules="validPassword" />
                                            </VCol>
                                            <VCol cols="12" md="6">
                                                <VTextField label="Confirm Password" v-model="editConfPassword"
                                                    placeholder="············" :type="'password'"
                                                    :rules="validConfPassword" />
                                            </VCol>
                                            <VCol cols="12" md="6">
                                                <VSelect label="Status" :items="status" v-model="editStatus"
                                                    persistent-hint return-object item-title="key" item-value="value"
                                                    single-line />
                                            </VCol>
                                            <VCol cols="12" md="6">
                                                <VSelect label="Role" :items="roles" v-model="editRole" persistent-hint
                                                    return-object item-title="display_name" item-value="id"
                                                    single-line />
                                            </VCol>
                                            <VCol cols="12" md="6">
                                                <VTextField label="Position" v-model="editPosition"
                                                    placeholder="Developer" :rules="validPosition" />
                                            </VCol>
                                        </VRow>
                                    </VCardText>
                                    <VCardActions>
                                        <VBtn type="submit" color="success">Update changes</VBtn>
                                        <VSpacer></VSpacer>
                                        <VBtn text="Close Dialog" @click="isActive.value = false"></VBtn>
                                    </VCardActions>
                                </VForm>

                            </VCard>
                        </template>
                    </VDialog>
                </td>
            </tr>
            <tr v-else>
                <td colspan="7" class="text-center" style="color:rgb(255,62,29);">
                    <b>No Records Found</b>
                </td>
            </tr>
        </tbody>
    </VTable>
    <v-pagination v-model="page" :length="totalPage" @click="onPageChange" v-if="employee.length>0"></v-pagination>

    <Loader :isLoading="loading"></Loader>
</template>

<script>
import { request } from '../../../helper'
import { showPopup } from '../../../helper'
import Swal from 'sweetalert2';
import Loader from "./../../../components/Loader.vue";
import axios from 'axios';

export default {
    data() {
        return {
            searchEmployee: null,
            loaded: false,
            searchLoading: false,
            page: 1,
            totalPage: 0,
            loading: false,
            employee: [],
            editPosition: null,
            edit_permission: false,
            status: [
                { 'key': 'In Active', 'value': 0 },
                { 'key': 'Active', 'value': 1 }
            ],
            roles: [],
            editStatus: null,
            editRole: null,
            editPassword: null,
            editConfPassword: null,
            validPassword: [
                v => !!v || 'Must be a valid password.',
                v => (v && v?.length > 6) || 'Must be a valid password.',
            ],
            validConfPassword: [
                v => (this.editPassword != null && v == this.editPassword) || 'Must be a same with password.',

                // value => {
                //     if (this.editPassword != null) {
                //         if (value == this.editPassword) return true
                //         return 'Must be a same with password.'
                //     } else {
                //         return true
                //     }
                // },
            ],
            validPosition: [
                v => !!v || 'Please enter positon.',
            ],

        }
    },
    mounted() {
        this.allEmployee()
        this.allRole()
        this.checkPermission()
    },
    methods: {
        checkPermission() {
            if (JSON.parse(localStorage.getItem('PERMISSIONS')).some(item => item['name'] === 'employee-update')) {
                this.edit_permission = true
            } else {
                this.edit_permission = false
            }
        },
        async allEmployee() {
            this.loading = true
            const token = localStorage.getItem('APP_TOKEN')
            const headers = {
                headers: {
                    Authorization: 'Bearer ' + token,
                    Accept: 'application/json',
                }
            }
            axios.get(this.$hostname + "/api/all-employee?page=" + this.page + "&search=" + this.searchEmployee, headers)
                .then(async response => {
                    this.employee = response.data.result.data;
                    this.page = response.data.result.current_page;
                    this.totalPage = response.data.result.last_page;
                    this.loading = false
                })
                .catch(async error => {
                    this.loading = false
                    await showPopup('Error', error.response.data.message, error.response.data.status)
                });
        },
        async EmployeeDataUpdate(id, uuid) {
            this.$nextTick(async () => {
                const { valid } = await this.$refs['updateForm_' + id][0].validate()
                if (valid) {
                    const request_data = {
                        'uuid': uuid,
                        'status': this.editStatus.value,
                        'password': this.editPassword,
                        'role': this.editRole.id,
                        'position': this.editPosition,
                    }
                    const token = localStorage.getItem('APP_TOKEN')
                    const headers = {
                        headers: {
                            Authorization: 'Bearer ' + token,
                            Accept: 'application/json',
                        }
                    }
                    axios.post(this.$hostname + "/api/employee-update", request_data, headers)
                        .then(async response => {
                            this.loading = false
                            await showPopup(response.data.status, response.data.message, response.data.status)
                            this.allEmployee()
                        })
                        .catch(async error => {
                            this.loading = false
                            await showPopup('Error', error.response.data.message, error.response.data.status)
                        });
                }
            });
        },
        editEmployee(status, role_id, position) {
            this.editStatus = this.status.find(item => item.value === status);
            this.editRole = this.roles.find(item => item.id === role_id);
            this.editPosition = position;
        },
        onPageChange() {
            this.allEmployee()
        },
        Search() {
            this.searchLoading = true
            setTimeout(() => {
                this.searchLoading = false
                this.loaded = true
                this.page = 1
                this.allEmployee()
            }, 1000)
        },
        Reload() {
            this.allEmployee()
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
    },
    components: {
        Loader
    }
}
</script>
