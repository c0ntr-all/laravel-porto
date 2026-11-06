<template>
  <q-btn
    class="ll-null-time"
    :class="modelValue ? 'll-null-time--off' : 'll-null-time--on'"
    flat
    round
    dense
    icon="schedule"
    :color="modelValue ? 'negative' : 'positive'"
    aria-label="Не учитывать время"
    @click="modelValue = !modelValue"
  >
    <q-tooltip>Не учитывать время</q-tooltip>
  </q-btn>
</template>

<script setup lang="ts">
const modelValue = defineModel<boolean>({ required: true })
</script>

<style scoped lang="scss">
.ll-null-time {
  &--on {
    background: #ecfdf3;
  }

  &--off {
    background: #fef2f2;
  }
}
</style>
