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
    <VRow>
        <VCol cols="6">
            <VTable fixed-header density="compact" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th>
                            Employee ID
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
                    <tr v-for="(item, index) in logs" :key="index" v-if="logs.length > 0">
                        <td>
                            {{ item.name }}
                            <VDialog width="300">
                                <template v-slot:activator="{ props }">
                                    <div class="text-decoration-underline" style="cursor: pointer;color: #3B71CA"
                                        v-bind="props">{{
                item?.employee_id }}</div>

                                </template>

                                <template v-slot:default="{ isActive }">
                                    <VCard :title="item?.name">
                                        <VCardText>
                                            <div class="text-overline mb-1">{{
                item?.employee_id }} </div>
                                            <div class="text-overline mb-1">{{ item.email }}
                                            </div>
                                            <div class="text-overline mb-1"><b>Position: </b> {{
                item?.position }} </div>
                                            <div class="text-overline mb-1"><b> Team: </b> {{
                item?.team_name }} </div>
                                        </VCardText>
                                    </VCard>
                                </template>
                            </VDialog>
                        </td>
                        <td>
                            {{ item.time }}
                        </td>
                        <td>
                            <VBadge color='error' v-if="item.status === '0'" content='Absent' inline>
                            </VBadge>
                            <VBadge color='success' v-if="item.status === '1'" content='Present' inline>
                            </VBadge>
                            <VBadge color='warning' v-if="item.status === '2'" content='Workfrom Home' inline>
                            </VBadge>
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
        <VCol cols="6">
            <img class="" style="width: 100%;height: auto;" :src="attendance_logo" />
        </VCol>
    </VRow>
    <!-- <v-pagination v-model="page" :length="totalPage" @click="onPageChange"></v-pagination> -->
    <Loader :isLoading="loading"></Loader>
</template>

<script>
import { showPopup } from '../../../helper'
import Swal from 'sweetalert2';
import Loader from "./../../../components/Loader.vue";
import axios from 'axios';
import attendance_logo from '@images/attendance_logo.png';

export default {
    data() {
        return {
            attendance_logo:attendance_logo,
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
        this.AttendanceLog()
    },
    methods: {
        async AttendanceLog() {
            this.loading = true
            const token = localStorage.getItem('APP_TOKEN')
            const headers = {
                headers: {
                    Authorization: 'Bearer ' + token,
                    Accept: 'application/json',
                }
            }
            axios.get(this.$hostname + "/api/audit/attendance-logs?&search=" + this.searchTask + "&date=" + this.dateSearch, headers)
                .then(async response => {
                    this.logs = response.data.result;
                    // this.page = response.data.result.current_page;
                    // this.totalPage = response.data.result.last_page;
                    this.loading = false
                })
                .catch(async error => {
                    this.loading = false
                    await showPopup('Error', error.response.data.message, error.response.data.status)
                });
        },

        // onPageChange() {
        //     this.AttendanceLog()
        // },
        Search() {
            this.searchLoading = true
            setTimeout(() => {
                this.searchLoading = false
                this.loaded = true
                this.page = 1
                this.AttendanceLog()
            }, 1000)
        },
        DateFilter() {
            this.AttendanceLog()
        },
        Reset() {
            this.searchTask = null;
            this.dateSearch = null;
            this.AttendanceLog()
        },
    },
    components: {
        Loader
    }
}
</script>
