<template>
  <q-layout view="hHh lpR fFf" class="auth-layout">
    <q-page-container>
      <q-page class="auth-page">
        <aside class="auth-brand">
          <div class="auth-brand__glow" />
          <div class="auth-brand__content">
            <div class="auth-brand__logo">
              <q-icon name="home" size="28px" />
            </div>
            <div class="auth-brand__title">Home Portal</div>
            <p class="auth-brand__lead">
              Единая точка входа к музыке, фильмам, задачам и галерее.
            </p>
            <ul class="auth-brand__features">
              <li>
                <q-icon name="library_music" size="20px" />
                <span>Музыка и плейлисты</span>
              </li>
              <li>
                <q-icon name="movie" size="20px" />
                <span>Фильмы и галерея</span>
              </li>
              <li>
                <q-icon name="task_alt" size="20px" />
                <span>Задачи и напоминания</span>
              </li>
            </ul>
          </div>
        </aside>

        <section class="auth-panel">
          <q-card class="auth-card" flat>
            <div class="auth-card__mobile-brand">
              <div class="auth-card__mobile-logo">
                <q-icon name="home" size="22px" color="primary" />
              </div>
              <div>
                <div class="auth-card__product">Home Portal</div>
                <div class="auth-card__tagline">Личный кабинет</div>
              </div>
            </div>

            <div class="auth-tabs">
              <q-tabs
                v-model="tab"
                class="auth-tabs__control"
                active-color="primary"
                indicator-color="transparent"
                no-caps
                stretch
                dense
              >
                <q-tab name="login" label="Вход" />
                <q-tab name="register" label="Регистрация" />
              </q-tabs>
            </div>

            <q-tab-panels v-model="tab" class="auth-panels">
              <q-tab-panel name="login" class="auth-panels__panel">
                <div class="auth-card__heading">
                  <h1 class="auth-card__title">С возвращением</h1>
                  <p class="auth-card__caption">Войдите, чтобы продолжить работу</p>
                </div>
                <LoginForm @switch-tab="tab = 'register'" />
              </q-tab-panel>

              <q-tab-panel name="register" class="auth-panels__panel">
                <div class="auth-card__heading">
                  <h1 class="auth-card__title">Создайте аккаунт</h1>
                  <p class="auth-card__caption">Укажите данные и подтвердите пароль</p>
                </div>
                <RegisterForm @switch-tab="tab = 'login'" />
              </q-tab-panel>
            </q-tab-panels>
          </q-card>
        </section>
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import LoginForm from 'src/components/auth/LoginForm.vue'
import RegisterForm from 'src/components/auth/RegisterForm.vue'

defineOptions({
  name: 'LoginLayout'
})

const tab = ref('login')
</script>

<style lang="scss" scoped>
.auth-layout {
  background: #f0f0f5;
}

.auth-page {
  display: grid;
  grid-template-columns: minmax(320px, 0.92fr) minmax(400px, 1.08fr);
  min-height: 100vh;
}

.auth-brand {
  position: relative;
  overflow: hidden;
  display: flex;
  align-items: center;
  padding: 48px 56px;
  color: #fff;
  background:
    radial-gradient(circle at 18% 12%, rgba(255, 255, 255, 0.22), transparent 28%),
    linear-gradient(160deg, #5b4ef0 0%, $primary 46%, #4f46c8 100%);

  &__glow {
    position: absolute;
    inset: auto -80px -120px auto;
    width: 320px;
    height: 320px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.12);
    pointer-events: none;
  }

  &__content {
    position: relative;
    z-index: 1;
    max-width: 420px;
  }

  &__logo {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 56px;
    height: 56px;
    margin-bottom: 24px;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.16);
    backdrop-filter: blur(8px);
  }

  &__title {
    font-size: 32px;
    font-weight: 600;
    line-height: 1.2;
    letter-spacing: -0.02em;
  }

  &__lead {
    margin: 12px 0 32px;
    max-width: 360px;
    font-size: 16px;
    line-height: 1.5;
    color: rgba(255, 255, 255, 0.82);
  }

  &__features {
    display: flex;
    flex-direction: column;
    gap: 14px;
    margin: 0;
    padding: 0;
    list-style: none;

    li {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 15px;
      color: rgba(255, 255, 255, 0.92);
    }

    .q-icon {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      border-radius: 10px;
      background: rgba(255, 255, 255, 0.14);
    }
  }
}

.auth-panel {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 0;
  padding: 32px 24px;
}

.auth-card {
  width: 100%;
  max-width: 420px;
  min-width: 0;
  padding: 28px 28px 24px;
  overflow: hidden;
  border-radius: 16px;
  background: #fff;
  box-shadow: 0 18px 48px rgba(40, 47, 83, 0.08);

  &__mobile-brand {
    display: none;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
  }

  &__mobile-logo {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: rgba(108, 95, 252, 0.12);
  }

  &__product {
    font-size: 16px;
    font-weight: 600;
    color: #282f53;
  }

  &__tagline {
    margin-top: 2px;
    font-size: 13px;
    color: #777a8f;
  }

  &__heading {
    margin-bottom: 20px;
  }

  &__title {
    margin: 0;
    font-size: 22px;
    font-weight: 600;
    line-height: 1.3;
    color: #282f53;
  }

  &__caption {
    margin: 6px 0 0;
    font-size: 14px;
    color: #777a8f;
  }
}

.auth-tabs {
  margin-bottom: 8px;
  padding: 4px;
  border-radius: 12px;
  background: #f0f0f5;

  &__control {
    min-height: 40px;
  }

  :deep(.q-tab) {
    min-height: 40px;
    padding: 0 12px;
    border-radius: 10px;
    font-weight: 500;
    color: #777a8f;
  }

  :deep(.q-tab--active) {
    background: #fff;
    color: #282f53;
    box-shadow: 0 1px 4px rgba(40, 47, 83, 0.08);
  }

  :deep(.q-tab__label) {
    font-size: 14px;
  }
}

.auth-panels {
  width: 100%;
  overflow: hidden;
  background: transparent;

  &__panel {
    padding: 16px 0 0;
  }
}

@media (max-width: 900px) {
  .auth-page {
    grid-template-columns: 1fr;
  }

  .auth-brand {
    display: none;
  }

  .auth-card {
    box-shadow: none;

    &__mobile-brand {
      display: flex;
    }
  }

  .auth-panel {
    align-items: flex-start;
    padding-top: 24px;
  }
}
</style>
