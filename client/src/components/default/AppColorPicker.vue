<template>
  <div class="q-pa-xs flex q-card--bordered" style="width: fit-content">
    <q-btn
      :style="'background-color:' + color"
      :size="size"
      square
    >
      <q-popup-proxy
        cover
        transition-show="scale"
        transition-hide="scale"
      >
        <q-color v-model="color" />
      </q-popup-proxy>
    </q-btn>
  </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
const { btnSize = 'sm' } = defineProps<{
  btnSize?: string
}>()
const color = defineModel<string>()
const size = ref(btnSize)
</script>

<style lang="sass" scoped>
.my-input
  max-width: 250px
</style>
