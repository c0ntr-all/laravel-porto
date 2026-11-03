<template>
  <div class="tracks-rate-filter" :class="{ 'tracks-rate-filter--inline': inline }">
    <div v-if="showLabel" class="tracks-rate-filter__label">Rating</div>
    <div class="tracks-rate-filter__row">
      <q-btn
        v-for="option in rateOptions"
        :key="option.value"
        class="tracks-rate-filter__chip"
        unelevated
        dense
        no-caps
        :color="isSelected(option.value) ? 'primary' : 'grey-2'"
        :text-color="isSelected(option.value) ? 'white' : 'grey-8'"
        :icon="option.icon || undefined"
        :label="option.label || undefined"
        @click="toggle(option.value)"
      >
        <q-tooltip>{{ option.hint }}</q-tooltip>
      </q-btn>
    </div>
  </div>
</template>

<script lang="ts" setup>
const props = withDefaults(defineProps<{
  modelValue: number[]
  showLabel?: boolean
  inline?: boolean
}>(), {
  showLabel: true,
  inline: false
})

const emit = defineEmits<{
  'update:modelValue': [value: number[]]
}>()

const rateOptions = [
  { value: 1, icon: 'star', hint: '1 star', label: '1' },
  { value: 2, icon: 'star', hint: '2 stars', label: '2' },
  { value: 3, icon: 'star', hint: '3 stars', label: '3' },
  { value: 4, icon: 'star', hint: '4 stars', label: '4' },
  { value: 5, icon: 'star', hint: '5 stars', label: '5' },
  { value: 0, icon: '', hint: 'Not rated yet', label: 'Unrated' }
] as const

const isSelected = (value: number): boolean => props.modelValue.includes(value)

const toggle = (value: number): void => {
  const current = props.modelValue
  const next = current.includes(value)
    ? current.filter(item => item !== value)
    : [...current, value]

  emit('update:modelValue', next)
}
</script>

<style lang="scss" scoped>
.tracks-rate-filter {
  &__label {
    margin-bottom: 8px;
    color: rgba(0, 0, 0, 0.7);
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
  }

  &__row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
  }

  &__chip {
    min-width: 40px;
    padding: 0 8px;
  }

  &--inline {
    min-width: 0;
  }
}
</style>
