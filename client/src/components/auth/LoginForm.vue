<template>
  <q-form @submit="login" class="q-gutter-sm">
    <q-input
      v-model="email"
      label="Email"
      lazy-rules
      :rules="[val => val && val.length > 0 || 'Поле не заполнено']"
      filled
    />

    <q-input
      type="password"
      v-model="password"
      label="Пароль"
      lazy-rules
      :rules="[val => val !== null && val !== '' || 'Введите пароль']"
      filled
    />
    <div>
      <q-btn label="Войти" type="submit" color="primary"/>
      <q-btn label="Сбросить пароль" type="reset" color="primary" flat class="q-ml-sm"/>
    </div>
  </q-form>
</template>

<script lang="ts" setup>

import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import { useUserStore } from 'src/stores/modules/user'

const $q = useQuasar()
const $router = useRouter()
const user = useUserStore()

const email = ref('')
const password = ref('')

interface ErrorResponse {
  response: {
    data: {
      message: string;
    }
  }
}

const login = (): void => {
  user.login({
    email: email.value,
    password: password.value
  }).then((): void => {
    $q.notify({
      type: 'positive',
      message: 'Вы успешно вошли в систему!'
    })
    $router.push('/')
  }).catch((error: ErrorResponse): void => {
    $q.notify({
      type: 'negative',
      message: error.response.data.message
    })
  })
}
</script>

<style lang="scss" scoped>
</style>
