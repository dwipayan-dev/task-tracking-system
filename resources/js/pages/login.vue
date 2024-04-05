<template>
  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <VRow class="align-center">
      <VCol cols="12" md="6">
        <div class="login-image-div">
          <img class="" style="width: 100%;height: auto;" :src="logoBanner" />
        </div>
      </VCol>
      <VCol cols="12" md="6">
        <VCard class="auth-card pa-4 pt-7" max-width="448" style="margin: 0 auto;">

          <VCardItem class="justify-center">
            <template #prepend>
              <div class="d-flex">
                <img class="d-flex text-primary" style="height:100px;" :src="logo" />
              </div>
            </template>
          </VCardItem>

          <VCardText class="pt-2">
            <h5 class="text-h5 mb-1">
              Welcome to Task Tracking System!
            </h5>
            <p class="mb-0">
              Please sign-in to your account
            </p>
          </VCardText>

          <VCardText>
            <VForm @submit.prevent="LoginData" ref="form">
              <VRow>
                <!-- email -->
                <VCol cols="12">
                  <VTextField v-model="email" placeholder="johndoe@email.com" label="Email" type="email"
                    :rules="validEmail" />
                </VCol>

                <!-- password -->
                <VCol cols="12">
                  <VTextField label="Password" v-model="password" placeholder="············"
                    :type="isPasswordVisible ? 'text' : 'password'" :rules="validPassword"
                    :append-inner-icon="isPasswordVisible ? 'bx-hide' : 'bx-show'"
                    @click:append-inner="isPasswordVisible = !isPasswordVisible" />

                  <!-- remember me checkbox -->
                  <div class="d-flex align-center justify-space-between flex-wrap mt-1 mb-4">
                    <!-- <VCheckbox v-model="form.remember" label="Remember me" /> -->

                    <!-- <RouterLink
                  class="text-primary ms-2 mb-1"
                  to="javascript:void(0)"
                >
                  Forgot Password?
                </RouterLink> -->
                  </div>

                  <!-- login button -->
                  <VCol md="12" cols="12">
                    <VBtn block type="submit" color="success" class="mr-2">
                      Login
                    </VBtn>
                    <!-- <RouterLink class="v-btn v-btn--block v-btn--elevated v-theme--light bg-primary v-btn--density-default v-btn--size-default v-btn--variant-elevated mr-2 text-primary ms-2" to="/customer/dashboard">
                  <VBtn block>
                    User Login
                  </VBtn>
                </RouterLink> -->
                  </VCol>
                </VCol>

                <!-- create account -->
                <!-- <VCol cols="12" class="text-center text-base"> -->
                <!-- <span>New on our platform?</span> -->
                <!-- <RouterLink class="text-primary ms-2" to="/admin/register">
                Create an account
              </RouterLink> -->
                <!-- </VCol> -->

                <!-- <VCol
              cols="12"
              class="d-flex align-center"
            >
              <VDivider />
              <span class="mx-4">or</span>
              <VDivider />
            </VCol> -->

                <!-- auth providers -->
                <!-- <VCol
              cols="12"
              class="text-center"
            >
              <AuthProvider />
            </VCol> -->
              </VRow>
            </VForm>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
  <Loader :isLoading="loading"></Loader>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth.scss";

.login-image-div {
  width: 500px;
  max-height: 500px;
  overflow: hidden;
  margin: 0 auto;
}

@media screen and (max-width:576px) {
  .login-image-div {
    width: 100%;
  }
}
</style>
<script>
import axios from 'axios';
import logo from '@images/logo.png';
import logoBanner from '@images/login-banner.gif';
import { showPopup } from '../helper';
import Loader from "./../components/Loader.vue";

export default {

  data() {
    return {
      logo: logo,
      logoBanner: logoBanner,
      email: '',
      password: '',
      loading: false,
      url: this.$hostname + '/api/login',
      isPasswordVisible: false,
      validEmail: [
        value => {
          if (/^[a-z.-]+@[a-z.-]+\.[a-z]+$/i.test(value)) return true
          return 'Must be a valid e-mail.'
        },
      ],
      validPassword: [
        value => {
          if (value?.length > 6) return true
          return 'Must be a valid password.'
        },
      ],
    }
  },
  methods: {
    async LoginData() {
      const { valid } = await this.$refs.form.validate()
      if (valid) {
        this.loading = true
        let request = {
          'email': this.email,
          'password': this.password
        }
        axios.post(this.url, request)
          .then(response => {
            localStorage.setItem('APP_TOKEN', response.data.token)
            localStorage.setItem('USER_NAME', response.data.user_name)
            localStorage.setItem('ROLE_NAME', response.data.role_name)
            this.loading = false
            this.$router.push('admin/dashboard')
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