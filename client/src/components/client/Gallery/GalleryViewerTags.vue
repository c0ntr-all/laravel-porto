<template>
  <div class="viewer-tags">
    <div class="viewer-tags__chips">
      <q-chip
        v-for="tag in assigned"
        :key="tag.id"
        dense
        outline
        removable
        color="primary"
        text-color="primary"
        :disable="saving"
        @remove="removeTag(tag)"
      >
        {{ tag.name }}
      </q-chip>
    </div>

    <q-select
      v-model="picked"
      class="viewer-tags__input"
      :options="options"
      option-label="name"
      option-value="id"
      emit-value
      map-options
      use-input
      fill-input
      hide-selected
      dense
      outlined
      placeholder="Добавить тег"
      input-debounce="0"
      maxlength="50"
      :loading="saving"
      :disable="!mediaId || saving"
      new-value-mode="add-unique"
      @filter="onFilter"
      @update:model-value="onPick"
      @new-value="onCreate"
    >
      <template #no-option>
        <q-item>
          <q-item-section class="text-grey-6">
            {{ query.trim() ? `Создать «${query.trim()}»` : 'Начните вводить название' }}
          </q-item-section>
        </q-item>
      </template>
    </q-select>
  </div>
</template>

<script lang="ts" setup>
import { computed, ref, watch } from 'vue'
import { useGalleryStore } from 'src/stores/modules/galleryStore'
import { useTagStore } from 'src/stores/modules/tagStore'
import { IImageSource } from 'src/types/carousel'
import { IGalleryMediaItem } from 'src/types/gallery'
import { ITag } from 'src/types/tag'
import { TagsModeEnum } from 'src/enums/LifeLog/TagsModeEnum'

const props = defineProps<{
  item?: IImageSource | IGalleryMediaItem | null
}>()

const galleryStore = useGalleryStore()
const tagStore = useTagStore()

const assigned = ref<ITag[]>([])
const picked = ref<string | null>(null)
const query = ref('')
const options = ref<ITag[]>([])
const saving = ref(false)

const mediaId = computed(() => (
  props.item ? String(props.item.id) : ''
))

const assignedIds = computed(() => new Set(assigned.value.map(tag => String(tag.id))))

const catalog = computed(() => tagStore.tags.filter(tag => !assignedIds.value.has(String(tag.id))))

watch(
  () => String(props.item?.id ?? ''),
  () => {
    assigned.value = props.item && 'tags' in props.item && Array.isArray(props.item.tags)
      ? [...props.item.tags]
      : []
    picked.value = null
    query.value = ''
  },
  { immediate: true }
)

watch(mediaId, () => {
  if (mediaId.value && !tagStore.tags.length && !tagStore.isLoading) {
    void tagStore.getTags(TagsModeEnum.LAST)
  }
}, { immediate: true })

function onFilter(value: string, update: (fn: () => void) => void): void {
  query.value = value
  const needle = value.trim().toLowerCase()

  update(() => {
    options.value = needle
      ? catalog.value.filter(tag => tag.name.toLowerCase().includes(needle))
      : catalog.value.slice(0, 12)
  })
}

async function persist(nextTags: ITag[], newTagNames: string[] = []): Promise<void> {
  if (!props.item || saving.value) {
    return
  }

  saving.value = true
  const previous = assigned.value

  try {
    assigned.value = nextTags
    assigned.value = await galleryStore.syncMediaTags(props.item, nextTags, newTagNames)
    rememberTags(assigned.value)
  } catch {
    assigned.value = previous
  } finally {
    saving.value = false
    picked.value = null
    query.value = ''
  }
}

function rememberTags(tags: ITag[]): void {
  const known = new Set(tagStore.tags.map(tag => String(tag.id)))
  const extra = tags.filter(tag => !known.has(String(tag.id)))

  if (extra.length) {
    tagStore.tags = [...extra, ...tagStore.tags]
  }
}

async function onPick(id: string | null): Promise<void> {
  if (!id) {
    return
  }

  const tag = tagStore.tags.find(item => String(item.id) === String(id))

  if (!tag || assignedIds.value.has(String(tag.id))) {
    picked.value = null
    return
  }

  await persist([...assigned.value, tag])
}

async function onCreate(
  name: string,
  done: (item?: unknown, mode?: 'add' | 'add-unique' | 'toggle') => void
): Promise<void> {
  const value = name.trim()
  done()

  if (!value) {
    return
  }

  const existing = [...assigned.value, ...tagStore.tags]
    .find(tag => tag.name.toLowerCase() === value.toLowerCase())

  if (existing) {
    if (!assignedIds.value.has(String(existing.id))) {
      await persist([...assigned.value, existing])
    }

    return
  }

  await persist(assigned.value, [value])
}

async function removeTag(tag: ITag): Promise<void> {
  await persist(assigned.value.filter(item => String(item.id) !== String(tag.id)))
}
</script>

<style lang="scss" scoped>
.viewer-tags {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding-bottom: 12px;
  margin-bottom: 12px;
  border-bottom: 1px solid #ececf4;

  &__chips {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    min-height: 8px;
  }

  &__input {
    width: 100%;
  }
}
</style>
