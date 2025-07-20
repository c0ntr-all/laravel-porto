<template>
  <q-layout view="lHh Lpr lFf" class="flex flex-center">
    <div class="main-container flex-column">
      <q-tabs
        v-model="tab"
        inline-label
      >
        <q-tab name="login" label="Login" />
        <q-tab name="register" label="Register" />
      </q-tabs>
      <q-card class="flex q-pa-lg">
        <q-tab-panels
          v-model="tab"
          class="tab-panels"
          animated
        >
          <q-tab-panel name="login">
            <div class="col text-h6 ellipsis q-mb-md">Sign In</div>
            <LoginForm />
          </q-tab-panel>

          <q-tab-panel name="register">
            <div class="col text-h6 ellipsis q-mb-md">Sign Up</div>
            <RegisterForm />
          </q-tab-panel>
        </q-tab-panels>
      </q-card>
    </div>
  </q-layout>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import LoginForm from 'src/components/auth/LoginForm.vue'
import RegisterForm from 'src/components/auth/RegisterForm.vue'

const tab = ref('login')
</script>

<style lang="scss" scoped>
.tab-panels {
  width: 400px;
}
</style>
