<template>
  <div class="lifelog-post-form">
    <div class="lifelog-post-form__editor">
      <q-editor
        v-model="textModel"
        min-height="5rem"
        style="border-radius: 0"
      />
    </div>

    <div class="lifelog-post-form-actions flex justify-end q-pa-md">
      <div class="lifelog-post-form-actions__right">
        <div class="lifelog-post-form__action">
          <q-btn
            label="Отправить"
            color="primary"
            :round="false"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue'

const textModel = ref<string>('')

</script>

<style lang="scss" scoped>
.lifelog-post-form {
  width: 100%;
  background-color: #ffffff;

  &-actions {
    background-color: #fbfbfb;
    border-right: 1px solid rgba(0, 0, 0, 0.12);
    border-left: 1px solid rgba(0, 0, 0, 0.12);
    border-bottom: 1px solid rgba(0, 0, 0, 0.12);
  }
}
</style>
