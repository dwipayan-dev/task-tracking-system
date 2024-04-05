<template>
    <!-- <VRow>
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
    </VRow> -->
    <VRow>
        <VCol cols="6 d-flex align-center mb-2">
            <div>
                <v-text-field size="100" :loading="searchLoading" v-model="searchTask" density="compact" variant="solo"
                    label="Search...." append-inner-icon="mdi-magnify" single-line hide-details
                    @click:append-inner="Search"></v-text-field>
            </div>
            <div class="ml-5">
                <VTooltip activator="parent" location="top">Reset</VTooltip>
                <VIcon size="27" color="error" :icon="'bx-reset'" @click="Reset" />
            </div>
        </VCol>
        <VCol cols="6 d-flex justify-end mb-2">

            <div class="mr-5">
                <VTooltip activator="parent" location="top">Refresh</VTooltip>
                <VIcon size="30" color="success" :icon="'bx-refresh'" @click="allTask" />
            </div>
            <div class="mr-5">
                <VTooltip activator="parent" location="top">Export</VTooltip>
                <VIcon size="27" color="primary" :icon="'bxs-file-export'" @click="allTaskExport" />
            </div>
            <div class="mr-2">
                <VTooltip activator="parent" location="top">Filter</VTooltip>
                <VIcon size="27" color="warning" :icon="'bx-filter-alt'" @click.stop="drawer = !drawer" />
            </div>
        </VCol>
    </VRow>
    <v-navigation-drawer v-model="drawer" location="right" temporary style="padding:10px;" width="300">
        <VRow class="text-center">
            <VCol cols="12">
                <h2>Filter</h2>
                <VDivider class="my-2" />
            </VCol>
            <VCol cols="12">
                <v-text-field type="date" density="compact" v-model="dateSearch" variant="outlined" clearable
                    label="Search...." single-line hide-details></v-text-field>
            </VCol>
            <VCol cols="12" class="text-left">
                <v-label>Priority:</v-label>
                <v-chip-group mandatory v-model="priorityFilter">
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
            <VCol cols="12" class="text-left">
                <v-label>Status:</v-label>
                <v-chip-group mandatory v-model="statusFilter">
                    <v-chip color="warning" size="small">
                        Initiation
                    </v-chip>
                    <v-chip color="info" size="small">
                        In Progress
                    </v-chip>
                    <v-chip color="error" size="small">
                        Pause
                    </v-chip>
                    <v-chip color="success" size="small">
                        Completed
                    </v-chip>
                </v-chip-group>
            </VCol>
            <VCol cols="12" class="text-left">
                <v-label>Task Type:</v-label>
                <v-chip-group mandatory v-model="taskTypeFilter">
                    <v-chip color="success" size="small">
                        Newly Created
                    </v-chip>
                    <v-chip color="error" size="small">
                        Carry Forward
                    </v-chip>
                </v-chip-group>
            </VCol>
            <VCol cols="12" class="text-center">
                <VDivider class="my-2" />
                <div class="d-flex justify-space-around">

                    <VBtn color="error" type="reset" @click.prevent="ResetFilter">
                        Reset
                    </VBtn>
                    <VBtn color="primary" type="reset" @click.prevent="DateFilter">
                        Apply
                    </VBtn>
                </div>
            </VCol>
        </VRow>
    </v-navigation-drawer>
    <VTable fixed-header density="compact">
        <thead>
            <tr>
                <th class="text-uppercase">
                    #
                </th>
                <th width="10%">
                    Employee
                </th>
                <th>
                    Task Name
                </th>
                <th>
                    Priority
                </th>
                <th>
                    Estimated
                </th>
                <th>
                    Completed
                </th>
                <th>
                    Status
                </th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(item, index) in task" :key="index" v-if="task.length > 0">
                <td>
                    {{ item.count }}
                </td>
                <td>
                    <v-badge
                        :color="item.get_employee?.availability === 0 && item.get_employee.get_attendance != null ? 'success' : 'error'"
                        dot floating location="left">
                        &nbsp;&nbsp;{{ item.get_employee?.name }}
                    </v-badge>

                    <VDialog width="300">
                        <template v-slot:activator="{ props }">
                            <p class="text-decoration-underline" style="cursor: pointer;color: #3B71CA;font-size: 9px;"
                                v-bind="props">{{
                    item.get_employee?.employee_id }} </p>
                        </template>

                        <template v-slot:default="{ isActive }">
                            <VCard :title="item.get_employee?.name">
                                <VCardText>
                                    <div class="text-overline mb-1">{{
                    item.get_employee?.employee_id }} </div>
                                    <div class="mb-1">{{ item.get_employee?.email }}
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
                    {{ item.task_name }} <VBadge :color="item.type === '1' ? 'error' : 'success'"
                        :content="item.type === '1' ? 'Carry Forward' : 'Newly Created'" inline>
                    </VBadge>
                    <p class="mb-1" style="font-size: 9px;color: grey;">{{ item.created_time }} </p>
                    <p class="mb-1" style="font-size: 9px;color: grey;">Project: {{ item.project_name }} </p>
                </td>
                <td>
                    <VIcon class="mr-2"
                        :icon="item.priority == '0' ? 'bx-signal-3' : item.priority == '1' ? 'bx-signal-4' : 'bx-signal-5'"
                        :color="item.priority == '0' ? 'success' : item.priority == '1' ? 'warning' : 'error'" />
                    <v-label>{{ item.priority == '0' ? 'Low' : item.priority == '1' ? 'Medium' : 'High' }}</v-label>
                </td>
                <td>
                    {{ item.estimation_hrs }} Hours
                    <br>
                    <v-progress-linear class="mb-1" rounded :color="item.delay_percentage_color"
                        :model-value="item.delay_percentage" height="10" striped>
                        <strong style="font-size: 10px;">{{ Math.ceil(item.delay_percentage) }}%</strong>
                    </v-progress-linear>
                </td>
                <td>
                    {{ item.completion_hrs }} Hours
                    <p class="mb-1" style="font-size: 9px;color: red;"
                        v-if="isNegative(calculateValue(item.estimation_hrs, item.completion_hrs))">
                        {{ (Math.round(Math.abs(item.estimation_hrs - item.completion_hrs) * 100) / 100).toFixed(2) }}
                        Delay
                    </p>
                    <p class="mb-1" style="font-size: 9px;color: green;" v-else>
                        No Delay
                    </p>
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
                <td colspan="7" class="text-center" style="color:rgb(255,62,29);">
                    <b>No Records Found</b>
                </td>
            </tr>

        </tbody>
    </VTable>
    <v-pagination v-model="page" :length="totalPage" @click="onPageChange" v-if="task.length >0"></v-pagination>
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
            statusFilter: null,
            priorityFilter: null,
            taskTypeFilter: null,
            knowledge: 33,
            searchTask: null,
            dateSearch: null,
            loaded: false,
            searchLoading: false,
            page: 1,
            totalPage: 0,
            loading: false,
            task: [],
            drawer: false,
        }
    },

    mounted() {
        this.allTask()
    },
    methods: {
        async allTask() {
            this.loading = true
            const token = localStorage.getItem('APP_TOKEN')
            const headers = {
                headers: {
                    Authorization: 'Bearer ' + token,
                    Accept: 'application/json',
                }
            }
            axios.get(this.$hostname + "/api/task?page=" + this.page + "&search=" + this.searchTask + "&date=" + this.dateSearch + "&status=" + this.statusFilter + "&priority=" + this.priorityFilter + "&type=" + this.taskTypeFilter, headers)
                .then(async response => {
                    this.task = response.data.result.data;
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
            this.allTask()
        },
        Search() {
            this.searchLoading = true
            setTimeout(() => {
                this.searchLoading = false
                this.loaded = true
                this.page = 1
                this.allTask()
            }, 1000)
        },
        DateFilter() {
            this.drawer = false;
            this.allTask()
        },
        Reset() {
            this.searchTask = null;
            this.dateSearch = null;
            this.statusFilter = null;
            this.priorityFilter = null;
            this.taskTypeFilter = null;
            this.allTask()
        },
        ResetFilter() {
            this.dateSearch = null;
            this.statusFilter = null;
            this.priorityFilter = null;
            this.taskTypeFilter = null;
        },
        calculateValue(estimation_hrs, completion_hrs) {
            var value = estimation_hrs - completion_hrs
            return value
        },
        isNegative(value) {
            return value < 0;
        },
        allTaskExport() {
            this.loading = true
            const token = localStorage.getItem('APP_TOKEN')
            const headers = {
                responseType: 'blob',
                headers: {
                    Authorization: 'Bearer ' + token,
                    Accept: 'application/json',
                }
            }
            axios.get(this.$hostname + "/api/task/all-task-excel-export?search=" + this.searchTask + "&date=" + this.dateSearch + "&status=" + this.statusFilter + "&priority=" + this.priorityFilter + "&type=" + this.taskTypeFilter, headers)
                .then(async response => {
                    let fileUrl = window.URL.createObjectURL(response.data);
                    let fileLink = document.createElement('a');
                    fileLink.href = fileUrl;
                    fileLink.setAttribute('download', 'all-task.xls');
                    document.body.appendChild(fileLink)
                    fileLink.click();
                    this.loading = false
                })
                .catch(async error => {
                    this.loading = false
                    await showPopup('Error', error.response.data.message, error.response.data.status)
                });
        }
    },
    components: {
        Loader
    }
}
</script>
<style>
.v-table th {
    text-align: left !important;
}
</style>
