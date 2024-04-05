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
                    single-line hide-details></v-text-field>
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
                <th v-if="edit_permission">
                    Action
                </th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(item, index) in task" :key="index" v-if="task.length > 0">
                <td>
                    {{ item.count }}
                </td>
                <td>
                    {{ item.task_name }}<VBadge :color="item.type === '1' ? 'error' : 'success'"
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
                    <VBadge color='error' v-if="item.status === 2" content='Stuck' inline>
                    </VBadge>
                    <VBadge color='success' v-if="item.status === 3" content='Done' inline>
                    </VBadge>
                </td>
                <td v-if="edit_permission && checkDate(item.created_at)">
                    <VBtn color="info" size="small" v-if="item.status === 0" append-icon="mdi-arrow-down">
                        Action
                        <VMenu activator="parent">
                            <v-list>
                                <v-list-item value="1" @click="UpdateStatus(item.uuid, 1)">
                                    <v-list-item-title>Start</v-list-item-title>
                                </v-list-item>
                            </v-list>
                        </VMenu>
                        <template v-slot:append>
                            <v-icon size="15"></v-icon>
                        </template>
                    </VBtn>

                    <VBtn color="info" size="small" v-if="item.status === 1" append-icon="mdi-arrow-down">
                        Action
                        <VMenu activator="parent">
                            <v-list>
                                <v-list-item value="2" @click="UpdateStatus(item.uuid, 2)">
                                    <v-list-item-title>Pause</v-list-item-title>
                                </v-list-item>
                                <v-list-item value="3" @click="UpdateStatus(item.uuid, 3)">
                                    <v-list-item-title>Stop</v-list-item-title>
                                </v-list-item>
                            </v-list>
                        </VMenu>
                        <template v-slot:append>
                            <v-icon size="15"></v-icon>
                        </template>
                    </VBtn>

                    <VBtn color="info" size="small" v-if="item.status === 2" append-icon="mdi-arrow-down">
                        Action
                        <VMenu value="tonal" activator="parent">
                            <v-list>
                                <v-list-item value="1" @click="UpdateStatus(item.uuid, 1)">
                                    <v-list-item-title>Resume</v-list-item-title>
                                </v-list-item>
                            </v-list>
                        </VMenu>
                        <template v-slot:append>
                            <v-icon size="15"></v-icon>
                        </template>
                    </VBtn>

                    <!-- <VBtn color="success" v-if="item.status === 3">
                        Stop
                    </VBtn> -->
                </td>
                <td v-else>
                </td>
            </tr>
            <tr v-else>
                <td colspan="7" class="text-center" style="color:rgb(255,62,29);">
                    <b>No Records Found</b>
                </td>
            </tr>
        </tbody>
    </VTable>
    <v-pagination v-model="page" :length="totalPage" @click="onPageChange" v-if="task.length > 0"></v-pagination>
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
            drawer: false,
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
            edit_permission: false,
            task: [],
        }
    },
    mounted() {
        this.allTask()
        this.checkPermission()

    },
    methods: {
        checkPermission() {
            if (JSON.parse(localStorage.getItem('PERMISSIONS')).some(item => item['name'] === 'manage_task-update')) {
                this.edit_permission = true
            } else {
                this.edit_permission = false
            }
        },
        async allTask() {
            this.loading = true
            const token = localStorage.getItem('APP_TOKEN')
            const headers = {
                headers: {
                    Authorization: 'Bearer ' + token,
                    Accept: 'application/json',
                }
            }
            axios.get(this.$hostname + "/api/task/all-task?page=" + this.page + "&&search=" + this.searchTask + "&date=" + this.dateSearch + "&status=" + this.statusFilter + "&priority=" + this.priorityFilter + "&type=" + this.taskTypeFilter, headers)
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
        UpdateStatus(task_uuid, status) {
            this.loading = true
            const token = localStorage.getItem('APP_TOKEN')
            const request_data = {
                'task_uuid': task_uuid,
                'status': status,
            }
            const headers = {
                headers: {
                    Authorization: 'Bearer ' + token,
                    Accept: 'application/json',
                }
            }


            axios.post(this.$hostname + "/api/task/update-task-status", request_data, headers)
                .then(async response => {
                    this.loading = false
                    // showPopup(response.data.status, response.data.message, response.data.status)
                    this.allTask()
                })
                .catch(async error => {
                    this.loading = false
                    await showPopup('Error', error.response.data.message, error.response.data.status)
                });
        },
        checkDate(createdAt) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const createdDate = new Date(createdAt);
            createdDate.setHours(0, 0, 0, 0);

            if (createdDate.getTime() === today.getTime()) {
                return true;
            } else {
                return false;
            }
        },
        calculateValue(estimation_hrs, completion_hrs) {
            var value = estimation_hrs - completion_hrs
            return value
        },
        isNegative(value) {
            return value < 0;
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
