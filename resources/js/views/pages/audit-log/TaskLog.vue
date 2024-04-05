<template>
    <VRow>
        <VCol cols="2" style="line-height: 2.5;text-align: center;">
            Filter:
        </VCol>
        <VCol cols="4">
            <v-text-field type="date" density="compact" v-model="dateSearch" variant="solo" label="Search...."
                single-line hide-details @change="DateFilter"></v-text-field>
        </VCol>
        <VCol cols="4" style="padding-bottom: 20px;">
            <v-text-field :loading="searchLoading" v-model="searchTask" density="compact" variant="solo"
                label="Search...." append-inner-icon="mdi-magnify" single-line hide-details
                @click:append-inner="Search"></v-text-field>
        </VCol>
        <VCol cols="1" style="line-height: 2.5;text-align: center;">
            <VTooltip activator="parent" location="top">Reset</VTooltip>

            <VIcon size="30" color="error" class="mr-2" :icon="'bx-reset'" @click="Reset" />
        </VCol>
        <VCol cols="1" style="line-height: 2.5;text-align: center;">
            <VTooltip activator="parent" location="top">Refresh</VTooltip>

            <VIcon size="30" color="success" class="mr-2" :icon="'bx-refresh'" @click="Reset" />
        </VCol>
    </VRow>
    <VTable density="compact" style="font-size: 12px;">
        <thead>
            <tr>
                <th>
                    Employee ID
                </th>
                <th>
                    Task Details
                </th>
                <th>
                    Priority
                </th>
                <th>
                    Estimated
                </th>
                <th>
                    Time
                </th>
                <th>
                    Status
                </th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(item, index) in logs" :key="index" v-if="logs.length>0">
                <td>
                    <!-- {{ item.get_employee?.name }} -->

                    <VDialog width="300">
                        <template v-slot:activator="{ props }">
                            <div class="text-decoration-underline" style="cursor: pointer;color: #3B71CA"
                                v-bind="props">{{
                item.get_employee?.employee_id }}</div>

                        </template>

                        <template v-slot:default="{ isActive }">
                            <VCard :title="item.get_employee?.name">
                                <VCardText>
                                    <div class="text-overline mb-1">{{
                item.get_employee?.employee_id }} </div>
                                    <div class="text-overline mb-1">{{ item.get_employee?.email }}
                                    </div>
                                    <div class="text-overline mb-1"><b>Position: </b> {{
                item.get_employee?.position }} </div>
                                    <div class="text-overline mb-1"><b> Team: </b> {{
                item.get_employee?.get_team?.name }} </div>
                                </VCardText>
                            </VCard>
                        </template>
                    </VDialog>



                </td>
                <td>
                    {{ item.get_task?.task_name }}<VBadge :color="item.get_task?.type === '1' ? 'error' : 'success'"
                        :content="item.get_task?.type === '1' ? 'Carry Forward' : 'Newly Created'" inline>
                    </VBadge>
                    <p class="mb-1" style="font-size: 9px;color: grey;">Project: {{ item.get_task?.project_name }} </p>
                </td>
                <td>
                    <VIcon class="mr-2" :icon="item.priority == '0' ? 'bx-signal-3' : item.priority == '1' ? 'bx-signal-4' : 'bx-signal-5'"
                        :color="item.priority == '0' ? 'success' : item.priority == '1' ? 'warning' : 'error'" />
                    <v-label>{{ item.priority == '0' ? 'Low' : item.priority == '1' ? 'Medium' : 'High' }}</v-label>
                </td>
                <td>
                    {{ item.get_task?.estimation_hrs }} Hours
                    <p class="mb-1" style="font-size: 9px;color: grey;" v-if="item.status === 2">Consume: {{
                estimateTime(item.get_task?.estimation_hrs, item.status, item.task_progress_percentage) }} Hours
                    </p>
                    <v-progress-linear class="mb-1" rounded :color="item.delay_percentage_color" v-if="item.status != 1"
                        :model-value="item.task_progress_percentage" height="10" striped>
                        <strong style="font-size: 10px;">{{ Math.ceil(item.task_progress_percentage) }}%</strong>
                    </v-progress-linear>
                    <p class="mb-1" style="font-size: 9px;color: grey;" v-if="item.status === 3">Completed: {{
                item.get_task?.completion_hrs }} Hours</p>
                    <div v-if="item.status === 3">
                        <p class="mb-1" style="font-size: 9px;color: red;"
                            v-if="isNegative(calculateValue(item.get_task?.estimation_hrs, item.get_task?.completion_hrs))">
                            {{ (Math.round(Math.abs(item.get_task?.estimation_hrs - item.get_task?.completion_hrs) *
                100) /
                100).toFixed(2) }} Delay
                        </p>
                        <p class="mb-1" style="font-size: 9px;color: green;" v-else>
                            No Delay
                        </p>
                    </div>
                </td>
                <td>
                    {{ item.time }}
                </td>
                <td>
                    <VBadge color='warning' v-if="item.status === 0" content='Initiation' inline>
                    </VBadge>
                    <VBadge color='info' v-if="item.status === 1" content='In Progress' inline>
                    </VBadge>
                    <VBadge color='error' v-if="item.status === 2" content='Pause' inline>
                    </VBadge>
                    <VBadge color='success' v-if="item.status === 3" content='Completed' inline>
                    </VBadge>
                </td>
            </tr>
            <tr v-else>
                <td colspan="5" class="text-center" style="color:rgb(255,62,29);">
                    <b>No Records Found</b>
                </td>
            </tr>
        </tbody>
    </VTable>
    <v-pagination v-model="page" :length="totalPage" @click="onPageChange" v-if="logs.length>0"></v-pagination>
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
            knowledge: 33,
            searchTask: null,
            dateSearch: null,
            loaded: false,
            searchLoading: false,
            page: 1,
            totalPage: 0,
            loading: false,
            logs: [],
        }
    },
    mounted() {
        this.AllLog()
    },
    methods: {
        async AllLog() {
            this.loading = true
            const token = localStorage.getItem('APP_TOKEN')
            const headers = {
                headers: {
                    Authorization: 'Bearer ' + token,
                    Accept: 'application/json',
                }
            }
            axios.get(this.$hostname + "/api/audit/task-logs?page=" + this.page + "&search=" + this.searchTask + "&date=" + this.dateSearch, headers)
                .then(async response => {
                    this.logs = response.data.result.data;
                    this.page = response.data.result.current_page;
                    this.totalPage = response.data.result.last_page;
                    this.loading = false
                })
                .catch(async error => {
                    this.loading = false
                    await showPopup('Error', error.response.data.message, error.response.data.status)
                });
        },

        onPageChange() {
            this.AllLog()
        },
        Search() {
            this.searchLoading = true
            setTimeout(() => {
                this.searchLoading = false
                this.loaded = true
                this.page = 1
                this.AllLog()
            }, 1000)
        },
        DateFilter() {
            this.AllLog()
        },
        Reset() {
            this.searchTask = null;
            this.dateSearch = null;
            this.AllLog()
        },
        calculateValue(estimation_hrs, completion_hrs) {
            var value = estimation_hrs - completion_hrs
            return value
        },
        isNegative(value) {
            return value < 0;
        },
        estimateTime(estimation, status, progress) {
            if (status == 2) {
                const min = estimation * 60;
                const convert_hrs = min / 100;

                const progress_min = convert_hrs * progress;
                const progress_hrs = progress_min / 60;
                return progress_hrs.toFixed(2);
            } else {
                return estimation;
            }

        }
    },
    components: {
        Loader
    }
}
</script>

<style>
.v-table th{
    text-align: left !important;
}
</style>
