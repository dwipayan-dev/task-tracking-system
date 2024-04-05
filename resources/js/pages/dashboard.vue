<template>
  <VRow v-if="role == 'admin'">
    <!-- 👉 Congratulations -->
    <!-- <VCol
      cols="12"
      md="8"
    >
      <AnalyticsCongratulations />
    </VCol> -->

    <VCol cols="12" sm="12">
      <VRow>
        <VCol cols="12" md="3">
          <CardStatisticsVertical v-bind="{
    title: 'Total Employee',
    image: chart,
    stats: total_employee.toString(),
    change: 100,
  }" />
        </VCol>
        <VCol cols="12" sm="3">
          <CardStatisticsVertical v-bind="{
    title: 'Total Task',
    image: chart,
    stats: total_task.toString(),
    change: 28.14,
  }" />
        </VCol>
        <VCol cols="12" md="3">
          <CardStatisticsVertical v-bind="{
    title: 'Employee Present',
    image: chart,
    stats: total_employee_present.toString(),
    change: 28.42,
  }" />
        </VCol>

        <VCol cols="12" sm="3">
          <CardStatisticsVertical v-bind="{
    title: 'Employee Abesent',
    image: chart,
    stats: total_employee_absent.toString(),
    change: -14.82,
  }" />
        </VCol>
      </VRow>
    </VCol>

    <!-- 👉 Total Revenue -->
    <!-- <VCol cols="12" md="8" order="2" order-md="1">
      <AnalyticsTotalRevenue />
    </VCol> -->
    <!-- 👉 Transactions -->
    <!-- <VCol cols="12" md="4" sm="6" order="3">
      <AnalyticsTransactions />
    </VCol> -->
  </VRow>
  <VCard v-if="role != 'admin'" class="text-center text-sm-start">
    <VRow no-gutters>
      <VCol cols="12" sm="8" order="2" order-sm="1">
        <VCardItem>
          <VCardTitle class="text-md-h5 text-primary">
            Hello {{ name }}! Welcome Back 👋
          </VCardTitle>
        </VCardItem>

        <VCardText>
          You're logged as {{ email }}
        </VCardText>
      </VCol>

      <!-- <VCol cols="12" sm="4" order="1" order-sm="2" class="text-center">
                <img :src="illustrationJohn" :height="$vuetify.display.xs ? '150' : '175'"
                    :class="$vuetify.display.xs ? 'mt-6 mb-n2' : 'position-absolute'" class="john-illustration flip-in-rtl">
            </VCol> -->
    </VRow>
  </VCard>
  <Loader :isLoading="loading"></Loader>

</template>
<script>
// 👉 Images
import chart from '@images/cards/chart-success.png'
import card from '@images/cards/credit-card-primary.png'
import paypal from '@images/cards/paypal-error.png'
import wallet from '@images/cards/wallet-info.png'

import { showPopup } from '../helper'
import Loader from "./../components/Loader.vue";
import axios from 'axios';

export default {
  data() {
    return {
      chart: chart,
      card: card,
      paypal: paypal,
      wallet: wallet,
      email: '',
      name: '',
      role: '',
      loading: false,
      total_employee: 0,
      total_task: 0,
      total_employee_present: 0,
      total_employee_absent: 0,
    }
  },
  mounted() {
    this.userDetails()
    this.dashboard()
  },
  methods: {
    userDetails() {
      this.loading = true
      const token = localStorage.getItem('APP_TOKEN')
      const headers = {
        headers: {
          Authorization: 'Bearer ' + token,
          Accept: 'application/json',
        }
      }
      axios.get(this.$hostname + "/api/user-details", headers)
        .then(async response => {
          this.email = response.data.result.email;
          this.name = response.data.result.name;
          this.role = response.data.result.roles[0]['name'];
          this.loading = false
        })
        .catch(async error => {
          this.loading = false
          await showPopup('Error', error.response.data.message, error.response.data.status)
        });
    },
    dashboard() {
      this.loading = true
      const token = localStorage.getItem('APP_TOKEN')
      const headers = {
        headers: {
          Authorization: 'Bearer ' + token,
          Accept: 'application/json',
        }
      }
      axios.get(this.$hostname + "/api/dashboard", headers)
        .then(async response => {
          this.total_employee = response.data.result.total_employee
          this.total_task = response.data.result.total_task
          this.total_employee_present = response.data.result.total_employee_present
          this.total_employee_absent = response.data.result.total_employee_absent
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
