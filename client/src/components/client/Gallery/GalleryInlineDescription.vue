<template>
  <div class="inline-description">
    <q-input
      v-if="editing"
      ref="inputRef"
      v-model="draft"
      type="textarea"
      autogrow
      outlined
      dense
      maxlength="5000"
      :disable="saving"
      @blur="commit"
      @keydown.esc.prevent="cancel"
    />
    <button
      v-else
      type="button"
      class="inline-description__text"
      :class="{ 'inline-description__text--empty': !displayText }"
      @click="startEdit"
    >
      {{ displayText || emptyLabel }}
    </button>
  </div>
</template>

<script lang="ts" setup>
import { computed, nextTick, ref } from 'vue'
import { QInput } from 'quasar'

const props = withDefaults(defineProps<{
  text?: string | null
  emptyLabel?: string
  saving?: boolean
}>(), {
  text: null,
  emptyLabel: 'Нет описания',
  saving: false
})

const emit = defineEmits<{
  save: [value: string | null]
}>()

const editing = ref(false)
const draft = ref('')
const inputRef = ref<QInput | null>(null)

const displayText = computed(() => props.text?.trim() || '')

function startEdit(): void {
  if (props.saving) {
    return
  }

  draft.value = displayText.value
  editing.value = true
  void nextTick(() => {
    inputRef.value?.focus()
  })
}

function cancel(): void {
  editing.value = false
  draft.value = displayText.value
}

function commit(): void {
  if (!editing.value) {
    return
  }

  const next = draft.value.trim() || null
  editing.value = false

  if (next === (displayText.value || null)) {
    return
  }

  emit('save', next)
}
</script>

<style lang="scss" scoped>
.inline-description {
  width: 100%;

  &__text {
    display: block;
    width: 100%;
    padding: 0;
    border: 0;
    background: transparent;
    color: #282f53;
    font-size: 14px;
    line-height: 1.45;
    text-align: left;
    white-space: pre-wrap;
    word-break: break-word;
    cursor: text;

    &--empty {
      color: #9aa0b8;
    }
  }
}
</style>
