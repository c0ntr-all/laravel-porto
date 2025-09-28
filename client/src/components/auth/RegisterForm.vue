<template>
  <q-form @submit="register" class="q-gutter-sm">
    <q-input
      v-model="model.name"
      label="Name"
      lazy-rules
      :rules="[val => val && val.length > 0 || 'Поле не заполнено']"
      filled
    />

    <q-input
      v-model="model.email"
      label="Email"
      lazy-rules
      :rules="[val => val && val.length > 0 || 'Поле не заполнено']"
      filled
    />

    <q-input
      type="password"
      v-model="model.password"
      label="Пароль"
      lazy-rules
      :rules="[val => val !== null && val !== '' || 'Введите пароль']"
      filled
    />
    <div>
      <q-btn label="Register" type="submit" color="primary"/>
    </div>
  </q-form>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { useQuasar } from 'quasar'
import { useUserStore } from 'src/stores/modules/user'

const $q = useQuasar()
const user = useUserStore()

const model = ref({
  name: '',
  email: '',
  password: ''
})

interface ErrorResponse {
  response: {
    data: {
      message: string;
    }
  }
}

const register = (): void => {
  user.register(model.value).then((): void => {
    $q.notify({
      type: 'positive',
      message: 'Success Register'
    })
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
