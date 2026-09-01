<template>
  <q-form class="password-form" @submit="onSubmit">
    <q-input
      v-model="form.current_password"
      label="Текущий пароль"
      :type="showCurrent ? 'text' : 'password'"
      :rules="[requiredRule]"
      outlined
      dense
    >
      <template #append>
        <q-icon
          :name="showCurrent ? 'visibility_off' : 'visibility'"
          class="cursor-pointer"
          @click="showCurrent = !showCurrent"
        />
      </template>
    </q-input>

    <q-input
      v-model="form.password"
      label="Новый пароль"
      :type="showNext ? 'text' : 'password'"
      :rules="[requiredRule, minRule]"
      outlined
      dense
    >
      <template #append>
        <q-icon
          :name="showNext ? 'visibility_off' : 'visibility'"
          class="cursor-pointer"
          @click="showNext = !showNext"
        />
      </template>
    </q-input>

    <q-input
      v-model="form.password_confirmation"
      label="Повторите новый пароль"
      :type="showNext ? 'text' : 'password'"
      :rules="[requiredRule, confirmRule]"
      outlined
      dense
    />

    <div class="password-form__actions">
      <q-btn
        label="Сменить пароль"
        type="submit"
        color="primary"
        :loading="isLoading"
        unelevated
        no-caps
      />
    </div>
  </q-form>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { useUserStore } from 'src/stores/modules/userStore'
import { IPasswordChangeForm } from 'src/types/user'

const userStore = useUserStore()
const { isLoading } = storeToRefs(userStore)

const showCurrent = ref(false)
const showNext = ref(false)
const form = reactive<IPasswordChangeForm>({
  current_password: '',
  password: '',
  password_confirmation: ''
})

const requiredRule = (value: string) => Boolean(value) || 'Поле не заполнено'
const minRule = (value: string) => (value && value.length >= 6) || 'Минимум 6 символов'
const confirmRule = (value: string) => value === form.password || 'Пароли не совпадают'

const resetForm = () => {
  form.current_password = ''
  form.password = ''
  form.password_confirmation = ''
}

const onSubmit = async () => {
  try {
    await userStore.changePassword({ ...form })
    resetForm()
  } catch {
    // Notification is handled in the store
  }
}
</script>

<style lang="scss" scoped>
.password-form {
  display: flex;
  flex-direction: column;
  gap: 12px;

  &__actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 4px;
  }
}
</style>
