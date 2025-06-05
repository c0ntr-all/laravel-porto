<template>
  <div class="task-item__actions-button">
    <q-btn
      icon="more_horiz"
      label=""
      size="sm"
      dense
      flat
    >
      <q-menu cover auto-close anchor="bottom left" self="top start">
        <q-list dense>
          <q-item
            v-if="!isFinished"
            @click="switchTaskFinishing(true)"
            clickable
          >
            <q-item-section>
              <div class="flex items-center">
                <q-icon
                  size="xs"
                  name="done"
                  flat
                  round
                  dense
                />
                <div class="q-ml-xs">Finish</div>
              </div>
            </q-item-section>
          </q-item>
          <q-item
            v-else
            @click="switchTaskFinishing(false)"
            clickable
          >
            <q-item-section>
              <div class="flex items-center">
                <q-icon
                  size="xs"
                  name="remove_done"
                  flat
                  round
                  dense
                />
                <div class="q-ml-xs">Unfinish</div>
              </div>
            </q-item-section>
          </q-item>
          <q-item
            v-if="!isDeclined"
            @click="switchTaskDeclanation(true)"
            clickable
          >
            <q-item-section>
              <div class="flex items-center">
                <q-icon
                  size="xs"
                  name="block"
                  flat
                  round
                  dense
                />
                <div class="q-ml-xs">Decline</div>
              </div>
            </q-item-section>
          </q-item>
          <q-item
            v-else
            @click="switchTaskDeclanation(false)"
            clickable
          >
            <q-item-section>
              <div class="flex items-center">
                <q-icon
                  size="xs"
                  name="hide_source"
                  flat
                  round
                  dense
                />
                <div class="q-ml-xs">Undecline</div>
              </div>
            </q-item-section>
          </q-item>
          <q-item
            @click="deleteTask"
            clickable
          >
            <q-item-section>
              <div class="flex items-center">
                <q-icon
                  size="xs"
                  name="delete"
                  flat
                  round
                  dense
                />
                <div class="q-ml-xs">Delete</div>
              </div>
            </q-item-section>
          </q-item>
        </q-list>
      </q-menu>
    </q-btn>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'

interface ActionsButtonData {
  isFinished: boolean,
  isDeclined: boolean,
}
const props = defineProps<{
  data: ActionsButtonData
}>()

const emit = defineEmits<{
  (e: 'finishSwitched', status: boolean): void,
  (e: 'declanationSwitched', status: boolean): void,
  (e: 'deleted'): void,
}>()

const isFinished = computed(() => props.data.isFinished)
const isDeclined = computed(() => props.data.isDeclined)

const switchTaskFinishing = (status: boolean) => {
  emit('finishSwitched', status)
}

const switchTaskDeclanation = (status: boolean) => {
  emit('declanationSwitched', status)
}

const deleteTask = () => {
  emit('deleted')
}
</script>

<style lang="scss" scoped>
.task-item {
  &__actions-button {
    visibility: hidden;
    position: absolute;
    top: 50%;
    margin-top: -11px;
    right: 2px;
    z-index: 10;

    button {
      border-radius: 50%;
    }
  }

  &:hover {
    .task-item__actions-button {
      visibility: visible;
    }
  }
}
</style>
