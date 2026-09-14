<template>
  <q-form class="auth-form" greedy @submit="login">
    <q-input
      v-model="email"
      type="email"
      label="Email"
      autocomplete="email"
      :rules="[requiredRule, emailRule]"
      outlined
      dense
      lazy-rules
    >
      <template #prepend>
        <q-icon name="mail_outline" />
      </template>
    </q-input>

    <q-input
      v-model="password"
      :type="showPassword ? 'text' : 'password'"
      label="Пароль"
      autocomplete="current-password"
      :rules="[requiredRule]"
      outlined
      dense
      lazy-rules
    >
      <template #prepend>
        <q-icon name="lock_outline" />
      </template>
      <template #append>
        <q-icon
          :name="showPassword ? 'visibility_off' : 'visibility'"
          class="cursor-pointer"
          @click="showPassword = !showPassword"
        />
      </template>
    </q-input>

    <q-btn
      class="auth-form__submit"
      label="Войти"
      type="submit"
      color="primary"
      :loading="isSubmitting"
      unelevated
      no-caps
    />

    <div class="auth-form__switch">
      Нет аккаунта?
      <q-btn
        label="Зарегистрироваться"
        color="primary"
        flat
        dense
        no-caps
        @click="emit('switch-tab')"
      />
    </div>
  </q-form>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import { useUserStore } from 'src/stores/modules/userStore'

const emit = defineEmits<{
  'switch-tab': []
}>()

const $q = useQuasar()
const $router = useRouter()
const user = useUserStore()

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const isSubmitting = ref(false)

const requiredRule = (value: string) => Boolean(value?.trim()) || 'Поле не заполнено'
const emailRule = (value: string) =>
  /^\S+@\S+\.\S+$/.test(value.trim()) || 'Введите корректный email'

interface ErrorResponse {
  response?: {
    data?: {
      message?: string
    }
  }
  message?: string
}

const login = async (): Promise<void> => {
  isSubmitting.value = true

  try {
    await user.login({
      email: email.value.trim(),
      password: password.value
    })
    $q.notify({
      type: 'positive',
      message: 'Вы успешно вошли в систему!'
    })
    await $router.push('/')
  } catch (error) {
    const err = error as ErrorResponse
    $q.notify({
      type: 'negative',
      message: err.response?.data?.message || err.message || 'Не удалось войти'
    })
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style lang="scss" scoped>
.auth-form {
  display: flex;
  flex-direction: column;
  gap: 12px;

  &__submit {
    width: 100%;
    margin-top: 8px;
    min-height: 44px;
    font-size: 15px;
    font-weight: 500;
  }

  &__switch {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    margin-top: 4px;
    font-size: 13px;
    color: #777a8f;
  }

  :deep(.q-field--outlined .q-field__control) {
    border-radius: 10px;
  }
}
</style>
