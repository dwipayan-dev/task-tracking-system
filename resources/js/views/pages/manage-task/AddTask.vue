<template>
    <VRow>
        <VCol cols="12">
            <VCard title="Task Details">
                <VCardText>
                    <VForm class="mt-6" @submit.prevent="SubmitTask" ref="form">
                        <VRow>
                            <VCol cols="12" md="6">
                                <VTextField label="Project Name" v-model="project_name" placeholder="CMS Management"
                                    type="text" :rules="validProject" />
                            </VCol>
                            <VCol md="6" cols="12">
                                <VTextField placeholder="Perform CRUD Opertaion" v-model="task_name" label="Task Name"
                                    :rules="validTask" />
                            </VCol>


                            <VCol cols="12" md="6">
                                <VTextField label="Estimation Time" v-model="estimation_hr" placeholder="7.5"
                                    type="number" :rules="validEstimation" />
                            </VCol>
                            <VCol cols="12" md="6">
                                <v-label>Priority:</v-label>
                                <v-chip-group filter mandatory v-model="priority">
                                    <v-chip color="success" size="small">
                                        Low
                                    </v-chip>
                                    <v-chip color="warning" size="small">
                                        Medium
                                    </v-chip>
                                    <v-chip color="error" size="small">
                                        High
                                    </v-chip>
                                </v-chip-group>
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
<style>
/* input[type=time]::-webkit-datetime-edit-ampm-field {
    display: none;
}

input[type="time"]::-webkit-calendar-picker-indicator {
    display: none;
}

input[type="time"]::-webkit-inner-spin-button,
input[type="time"]::-webkit-clear-button {
    display: none;
} */
/* Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Firefox */
input[type=number] {
    -moz-appearance: textfield;
}
</style>
<script>
import axios from 'axios';
import { showPopup } from '../../../helper'
import Loader from "./../../../components/Loader.vue";
import Dashboard from "./Dashboard.vue";

export default {
    data() {
        return {
            priority: 0,
            loading: false,
            task_name: null,
            project_name: null,
            estimation_hr: null,
            validTask: [
                v => !!v || 'Please enter a valid task name.',
            ],
            validProject: [
                v => !!v || 'Please enter a valid project name.',
            ],
            validEstimation: [
                v => !!v || 'Please enter a valid time.',
            ],
        }
    },
    methods: {
        async SubmitTask() {
            const { valid } = await this.$refs.form.validate()
            if (valid) {
                this.loading = true
                const request_data = {
                    'task_name': this.task_name,
                    'project_name': this.project_name,
                    'estimation_hr': this.estimation_hr,
                    'priority': this.priority
                }
                const token = localStorage.getItem('APP_TOKEN')
                const headers = {
                    headers: {
                        Authorization: 'Bearer ' + token,
                        Accept: 'application/json',
                    }
                }
                axios.post(this.$hostname + "/api/task/create", request_data, headers)
                    .then(async response => {
                        this.loading = false
                        await showPopup(response.data.status, response.data.message, response.data.status)
                        this.$refs.form.reset()
                        // window.location.reload()
                    })
                    .catch(async error => {
                        this.loading = false
                        await showPopup('Error', error.response.data.message, error.response.data.status)
                    });
            }
        }
    },
    components: {
        Loader
    }
}
</script>