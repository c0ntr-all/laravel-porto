<template>
  <q-dialog
    v-model="show"
    :persistent="persistent"
    :maximized="maximized"
    @hide="onHide"
  >
    <q-card
      class="app-modal__card"
      :style="cardStyle"
    >
      <q-card-section
        v-if="hasHeader"
        class="app-modal__header row items-center no-wrap"
      >
        <p class="text-h6 q-ma-none col">
          <slot name="header" />
        </p>
        <q-btn
          v-if="showClose"
          flat
          round
          dense
          icon="close"
          aria-label="Close"
          @click="close"
        />
      </q-card-section>

      <q-separator v-if="hasHeader" />

      <q-card-section
        class="app-modal__body"
        :class="{ 'app-modal__body--scrollable': scrollable }"
      >
        <slot name="body" />
      </q-card-section>

      <template v-if="hasFooter">
        <q-separator />
        <q-card-actions
          :align="actionsAlign"
          class="bg-white app-modal__footer"
        >
          <slot name="footer" />
        </q-card-actions>
      </template>
    </q-card>
  </q-dialog>
</template>

<script setup lang="ts">
import { computed, useSlots } from 'vue'

interface Props {
  width?: string
  maxWidth?: string
  actionsAlign?: 'right' | 'left' | 'center' | 'between' | 'around' | 'evenly' | 'stretch'
  scrollable?: boolean
  persistent?: boolean
  maximized?: boolean
  showClose?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  width: '768px',
  maxWidth: '80vw',
  actionsAlign: 'right',
  scrollable: false,
  persistent: false,
  maximized: false,
  showClose: true
})

const emit = defineEmits<{
  hide: []
}>()

const show = defineModel<boolean>({ default: false })

const slots = useSlots()

const hasHeader = computed(() => Boolean(slots.header))
const hasFooter = computed(() => Boolean(slots.footer))

const cardStyle = computed(() => ({
  width: props.width,
  maxWidth: props.maxWidth
}))

const close = () => {
  show.value = false
}

const onHide = () => {
  emit('hide')
}
</script>

<style lang="scss" scoped>
.app-modal__body--scrollable {
  max-height: 70vh;
  overflow-y: auto;
}
</style>
