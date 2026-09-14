<template>
  <q-form class="auth-form" greedy @submit="register">
    <q-input
      v-model="model.name"
      label="Имя"
      autocomplete="name"
      :rules="[requiredRule, nameRule]"
      outlined
      dense
      lazy-rules
      no-error-icon
    >
      <template #prepend>
        <q-icon name="person_outline" />
      </template>
    </q-input>

    <q-input
      v-model="model.email"
      type="email"
      label="Email"
      autocomplete="email"
      :rules="[requiredRule, emailRule]"
      outlined
      dense
      lazy-rules
      no-error-icon
    >
      <template #prepend>
        <q-icon name="mail_outline" />
      </template>
    </q-input>

    <q-input
      v-model="model.password"
      :type="showPassword ? 'text' : 'password'"
      label="Пароль"
      autocomplete="new-password"
      hint="Минимум 6 символов"
      :rules="[requiredRule, minRule]"
      outlined
      dense
      lazy-rules
      no-error-icon
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

    <q-input
      ref="confirmInput"
      v-model="model.password_confirm"
      :type="showPassword ? 'text' : 'password'"
      label="Подтверждение пароля"
      autocomplete="new-password"
      :rules="[requiredRule, confirmRule]"
      outlined
      dense
      lazy-rules
      no-error-icon
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
      label="Зарегистрироваться"
      type="submit"
      color="primary"
      :loading="isSubmitting"
      unelevated
      no-caps
    />

    <div class="auth-form__switch">
      Уже есть аккаунт?
      <q-btn
        label="Войти"
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
import { reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { QInput, useQuasar } from 'quasar'
import { useUserStore } from 'src/stores/modules/userStore'

const emit = defineEmits<{
  'switch-tab': []
}>()

const $q = useQuasar()
const $router = useRouter()
const user = useUserStore()

const showPassword = ref(false)
const isSubmitting = ref(false)
const confirmInput = ref<QInput | null>(null)
const model = reactive({
  name: '',
  email: '',
  password: '',
  password_confirm: ''
})

watch(() => model.password, () => {
  if (model.password_confirm) {
    void confirmInput.value?.validate()
  }
})

const requiredRule = (value: string) => Boolean(value?.trim()) || 'Поле не заполнено'
const nameRule = (value: string) =>
  (value && value.trim().length <= 30) || 'Не больше 30 символов'
const emailRule = (value: string) =>
  /^\S+@\S+\.\S+$/.test(value.trim()) || 'Введите корректный email'
const minRule = (value: string) => (value && value.length >= 6) || 'Минимум 6 символов'
const confirmRule = (value: string) => value === model.password || 'Пароли не совпадают'

interface ErrorResponse {
  response?: {
    data?: {
      message?: string
    }
  }
  message?: string
}

const register = async (): Promise<void> => {
  isSubmitting.value = true

  try {
    const signedIn = await user.register({
      name: model.name.trim(),
      email: model.email.trim(),
      password: model.password,
      password_confirm: model.password_confirm
    })

    $q.notify({
      type: 'positive',
      message: signedIn ? 'Аккаунт создан, вы вошли в систему' : 'Аккаунт успешно создан'
    })

    if (signedIn) {
      await $router.push('/')
      return
    }

    emit('switch-tab')
  } catch (error) {
    const err = error as ErrorResponse
    $q.notify({
      type: 'negative',
      message: err.response?.data?.message || err.message || 'Не удалось зарегистрироваться'
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
