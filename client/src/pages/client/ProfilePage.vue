<template>
  <div class="profile-page">
    <q-card class="profile-hero" flat>
      <q-card-section class="profile-hero__content">
        <ProfileAvatar />
        <div class="profile-hero__meta">
          <div class="profile-hero__name">{{ displayName }}</div>
          <div class="profile-hero__login">{{ displayLogin }}</div>
          <div class="profile-hero__chips">
            <q-chip color="primary" text-color="white" size="sm" square>
              {{ displayRole }}
            </q-chip>
            <q-chip v-if="createdAt" outline color="grey-7" size="sm" square>
              С {{ createdAt }}
            </q-chip>
          </div>
        </div>
      </q-card-section>
    </q-card>

    <div class="profile-page__grid">
      <q-card class="profile-card" flat>
        <q-card-section>
          <div class="profile-card__title">Основная информация</div>
          <div class="profile-card__caption">
            ФИО и логин. Логин используется для входа.
          </div>
        </q-card-section>
        <q-separator />
        <q-card-section>
          <ProfileInfoForm />
        </q-card-section>
      </q-card>

      <q-card class="profile-card" flat>
        <q-card-section>
          <div class="profile-card__title">Смена пароля</div>
          <div class="profile-card__caption">
            Укажите текущий пароль и задайте новый.
          </div>
        </q-card-section>
        <q-separator />
        <q-card-section>
          <ProfilePasswordForm />
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { date } from 'quasar'
import { storeToRefs } from 'pinia'
import { useUserStore } from 'src/stores/modules/userStore'
import ProfileAvatar from 'src/components/client/Profile/ProfileAvatar.vue'
import ProfileInfoForm from 'src/components/client/Profile/ProfileInfoForm.vue'
import ProfilePasswordForm from 'src/components/client/Profile/ProfilePasswordForm.vue'

const userStore = useUserStore()
const { user, displayName, displayRole, displayLogin } = storeToRefs(userStore)
const createdAt = computed(() => {
  if (!user.value.created_at) {
    return ''
  }

  return date.formatDate(user.value.created_at, 'D MMMM YYYY')
})
</script>

<style lang="scss" scoped>
.profile-page {
  max-width: 960px;

  &__grid {
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 16px;
    align-items: start;
  }

  @media (max-width: 900px) {
    &__grid {
      grid-template-columns: 1fr;
    }
  }
}

.profile-hero {
  margin-bottom: 16px;
  border-radius: 16px;

  &__content {
    display: flex;
    align-items: center;
    gap: 20px;
  }

  &__name {
    font-size: 22px;
    font-weight: 600;
    color: #282f53;
    line-height: 1.3;
  }

  &__login {
    margin: 4px 0 10px;
    color: #777a8f;
  }

  &__chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }
}

.profile-card {
  border-radius: 16px;

  &__title {
    font-size: 16px;
    font-weight: 600;
    color: #282f53;
  }

  &__caption {
    margin-top: 4px;
    font-size: 13px;
    color: #777a8f;
  }
}
</style>
