<template>
  <q-card flat bordered class="preset-date-block">
    <q-card-section class="q-pb-sm">
      <div class="row items-center q-gutter-xs q-mb-sm">
        <q-icon :name="icon" size="sm" color="primary" />
        <span class="text-subtitle2">{{ label }}</span>
      </div>

      <AppDatetimeField
        v-model="modelValue"
        class="preset-date-block__field"
      />

      <div v-if="post" class="q-mt-sm">
        <q-chip
          dense
          removable
          outline
          color="primary"
          icon="article"
          @remove="emit('clear-post')"
        >
          Из поста: {{ post.id }}. {{ post.title }}
        </q-chip>
      </div>
      <div v-else class="text-caption text-grey-6 q-mt-xs">
        Или выберите пост через меню на карточке
      </div>
    </q-card-section>
  </q-card>
</template>

<script lang="ts" setup>
import AppDatetimeField from 'src/components/default/AppDatetimeField.vue'
import type { IPost } from 'src/types/LifeLog/post'

defineProps<{
  label: string
  icon: string
  post?: IPost
}>()

const emit = defineEmits<{
  'clear-post': []
}>()

const modelValue = defineModel<string>({ required: true })
</script>

<style lang="scss" scoped>
.preset-date-block {
  background: #fafafa;

  &__field {
    width: 100%;
  }
}
</style>
