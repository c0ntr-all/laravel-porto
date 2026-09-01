<template>
  <q-form class="profile-form" @submit="onSubmit">
    <div class="profile-form__grid">
      <q-input
        v-model="form.last_name"
        label="Фамилия"
        :rules="[requiredRule]"
        outlined
        dense
      />
      <q-input
        v-model="form.first_name"
        label="Имя"
        :rules="[requiredRule]"
        outlined
        dense
      />
      <q-input
        v-model="form.patronymic"
        label="Отчество"
        outlined
        dense
      />
      <q-input
        v-model="form.login"
        class="profile-form__login"
        label="Логин"
        hint="Используется для входа в систему"
        :rules="[requiredRule, emailRule]"
        outlined
        dense
      />
    </div>

    <div class="profile-form__actions">
      <q-btn
        label="Сохранить"
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
import { reactive, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useUserStore } from 'src/stores/modules/userStore'
import { toProfileForm } from 'src/api/mappers/user.mapper'
import { IProfileForm } from 'src/types/user'

const userStore = useUserStore()
const { user, isLoading } = storeToRefs(userStore)

const form = reactive<IProfileForm>(toProfileForm(user.value))

const requiredRule = (value: string) => Boolean(value?.trim()) || 'Поле не заполнено'
const emailRule = (value: string) =>
  /^\S+@\S+\.\S+$/.test(value.trim()) || 'Введите корректный логин (email)'

watch(user, (next) => {
  Object.assign(form, toProfileForm(next))
}, { deep: true })

const onSubmit = async () => {
  try {
    await userStore.updateProfile({ ...form })
  } catch {
    // Notification is handled in the store
  }
}
</script>

<style lang="scss" scoped>
.profile-form {
  &__grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px 16px;
  }

  &__login {
    grid-column: 1 / -1;
  }

  &__actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 16px;
  }

  @media (max-width: 700px) {
    &__grid {
      grid-template-columns: 1fr;
    }
  }
}
</style>
