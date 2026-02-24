<template>
  <q-btn
    class="actions-button"
    color="grey-7"
    :icon=icon
    round
    flat>
    <q-menu cover auto-close>
      <q-list>
        <template
          v-for="action in actions"
          :key="action.name"
        >
          <q-item
            v-if="action.is_active"
            @click="handleClick(action)"
            :style="'color: ' + action.color ?? 'inherit'"
            clickable
            dense
          >
            <q-item-section>
              <div class="flex items-center">
                <q-icon
                  size="xs"
                  :name="action.icon"
                  flat
                  round
                />
                <div class="q-ml-xs">{{ action.label }}</div>
              </div>
            </q-item-section>
          </q-item>
        </template>
      </q-list>
    </q-menu>
  </q-btn>
</template>

<script lang="ts" setup>
import { IAction } from 'src/components/types'

const { icon = 'more_horiz' } = defineProps<{
  actions: IAction[],
  icon: string
}>()

const emit = defineEmits<{
  (e: 'clicked', value: boolean): void
}>()

const handleClick = (action: IAction) => {
  action.func()
  emit('clicked', true)
}
</script>

<style lang="scss" scoped>
.actions-button {
  display: flex;
  width: 42px;
}
</style>
