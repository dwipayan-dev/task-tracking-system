<template>
    <VRow>
        <VCol cols="12">
            <VCard title="Employee Details">
                <!-- <VCardText class="d-flex">
                    <VAvatar rounded="lg" size="100" class="me-6" :image="accountDataLocal.avatarImg" />

                    <form class="d-flex flex-column justify-center gap-5">
                        <div class="d-flex flex-wrap gap-2">
                            <VBtn color="primary" @click="refInputEl?.click()">
                                <VIcon icon="bx-cloud-upload" class="d-sm-none" />
                                <span class="d-none d-sm-block">Upload new photo</span>
                            </VBtn>

                            <input ref="refInputEl" type="file" name="file" accept=".jpeg,.png,.jpg,GIF" hidden
                                @input="changeAvatar">

                            <VBtn type="reset" color="error" variant="tonal" @click="resetAvatar">
                                <span class="d-none d-sm-block">Reset</span>
                                <VIcon icon="bx-refresh" class="d-sm-none" />
                            </VBtn>
                        </div>

                        <p class="text-body-1 mb-0">
                            Allowed JPG, GIF or PNG. Max size of 800K
                        </p>
                    </form>
                </VCardText> -->

                <!-- <VDivider /> -->

                <VCardText>
                    <VForm class="mt-6" @submit.prevent="UserData" ref="form">
                        <VRow>
                            <VCol md="6" cols="12">
                                <VTextField placeholder="John Doe" v-model="full_name" label="Full Name"
                                    :rules="validFullName" required />
                            </VCol>
                            <VCol md="6" cols="12">
                                <VTextField placeholder="Q00157" v-model="employee_id" label="Employee ID"
                                    :rules="validEmployeeID" required />
                            </VCol>
                            <VCol cols="12" md="6">
                                <VTextField label="E-mail" autocomplete="off" v-model="email"
                                    placeholder="johndoe@quocent.com" type="email" :rules="validEmail" />
                            </VCol>
                            <VCol cols="12" md="6">
                                <VSelect label="Team" :items="team" v-model="team_uuid" persistent-hint return-object
                                    item-title="name" item-value="uuid" single-line placeholder="Select team"
                                    :rules="validTeam" required />
                            </VCol>
                            <VCol cols="12" md="6">
                                <VTextField label="Position" v-model="position" placeholder="Developer"
                                    :rules="validPosition" />
                            </VCol>
                            <VCol cols="12" md="6">
                                <VTextField label="Password" v-model="password" placeholder="············"
                                    :type="'password'" :rules="validPassword" />
                            </VCol>
                            <VCol cols="12" md="6">
                                <VTextField label="Confirm Password" v-model="confPassword" placeholder="············"
                                    :type="'password'" :rules="validConfPassword" />
                            </VCol>
                            <VCol cols="12" md="6">
                                <VSelect label="Role" persistent-hint return-object :rules="validRole" v-model="role"
                                    :items="roles" placeholder="Select role" item-title="display_name" item-value="id"
                                    single-line />
                            </VCol>

                            <VCol cols="12" class="d-flex flex-wrap gap-4">
                                <VBtn type="submit">Save changes</VBtn>
                                <VBtn color="error" variant="tonal" type="reset"
                                    @click.prevent="this.$refs.form.reset()">
                                    Reset
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VForm>
                </VCardText>
            </VCard>
        </VCol>
    </VRow>
    <Loader :isLoading="loading"></Loader>
</template>

<script>
import axios from 'axios';
import { request } from '../../../helper'
import { showPopup } from '../../../helper'
import Loader from "./../../../components/Loader.vue";

export default {
    data() {
        return {
            loading: false,
            team: [],
            team_uuid: null,
            full_name: null,
            email: null,
            password: null,
            position: null,
            confPassword: null,
            role: null,
            employee_id: null,
            roles: [],
            validFullName: [
                v => !!v || 'Please enter a valid full name.',
            ],
            validEmail: [
                v => !!v || 'Must be a valid e-mail.',
                v => (v && /^[a-z.-]+@[a-z.-]+\.[a-z]+$/i.test(v)) || 'Must be a valid e-mail.',
            ],
            validTeam: [
                v => !!v || 'Please select a team.',
            ],
            validPosition: [
                v => !!v || 'Please enter positon.',
            ],
            validPassword: [
                v => !!v || 'Must be a valid password.',
                v => (v && v?.length > 6) || 'Must be a valid password.',
            ],
            validConfPassword: [
                v => (v == this.password) || 'Must be a same with password.',
            ],
            validRole: [
                v => !!v || 'Please select a role.',
            ],
            validEmployeeID: [
                v => !!v || 'Please enter a valid employee id.',
            ],
        }
    },
    async mounted() {
        this.loading = true
        const response = await request('get', this.$hostname + "/api/all-team")
        this.team = response.data.result;
        this.loading = false
        this.allRole();
    },
    methods: {
        async UserData() {
            const { valid } = await this.$refs.form.validate()
            if (valid) {
                this.loading = true
                const request_data = {
                    'team_uuid': this.team_uuid.uuid,
                    'full_name': this.full_name,
                    'employee_id': this.employee_id,
                    'email': this.email,
                    'password': this.password,
                    'position': this.position,
                    'role': this.role.id,
                }

                const token = localStorage.getItem('APP_TOKEN')
                const headers = {
                    headers: {
                        Authorization: 'Bearer ' + token,
                        Accept: 'application/json',
                    }
                }
                axios.post(this.$hostname + "/api/employee-create", request_data, headers)
                    .then(async response => {
                        this.loading = false
                        await showPopup(response.data.status, response.data.message, response.data.status)
                        this.$refs.form.reset()
                        // window.location.reload();
                    })
                    .catch(async error => {
                        this.loading = false
                        await showPopup('Error', error.response.data.message, error.response.data.status)
                    });
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
    },
    components: {
        Loader
    }
}
</script>