<template>
  <div class="preset-tags-select">
    <div class="text-subtitle2 q-mb-sm">Теги</div>

    <q-select
      v-model="modelValue"
      :options="availableTags"
      option-value="name"
      option-label="name"
      emit-value
      map-options
      multiple
      use-chips
      dense
      outlined
      label="Выберите теги"
      :loading="isLoading"
      :disable="isLoading"
    >
      <template #no-option>
        <q-item>
          <q-item-section class="text-grey">
            Теги не найдены
          </q-item-section>
        </q-item>
      </template>
    </q-select>
  </div>
</template>

<script lang="ts" setup>
import { onMounted, ref } from 'vue'
import { useTagStore } from 'src/stores/modules/tagStore'
import { TagsModeEnum } from 'src/enums/LifeLog/TagsModeEnum'
import type { ITag } from 'src/types/tag'

const modelValue = defineModel<string[]>({ default: () => [] })

const tagStore = useTagStore()
const availableTags = ref<ITag[]>([])
const isLoading = ref(false)

onMounted(async () => {
  isLoading.value = true

  try {
    availableTags.value = await tagStore.getTags(TagsModeEnum.LAST)
  } finally {
    isLoading.value = false
  }
})
</script>

<style lang="scss" scoped>
.preset-tags-select {
  width: 100%;
}
</style>
