<template>
  <q-item class="checklist-item" :class="{'checklist-item--declined': checklistItem.is_declined}">
    <q-item-section class="checklist-item__section" avatar>
      <q-checkbox
        v-model="selectedItems"
        :val="item.id"
        :disable="item.is_declined"
      />
    </q-item-section>
    <q-item-section>
      {{ item.title }}
      <q-popup-edit
        ref="checklistItemTitlePopup"
        v-model="checklistItem.title"
        v-slot="scope"
        auto-save
      >
        <q-input
          v-model="scope.value"
          @keyup.enter="updateTitle(scope.value)"
          dense
          autofocus
          counter
        />
        <div class="q-pt-sm">
          <q-btn @click="updateTitle(scope.value)" label="Save" color="primary" flat/>
          <q-btn @click="checklistItemTitlePopup?.cancel()" label="Cancel" color="primary" flat/>
        </div>
      </q-popup-edit>
    </q-item-section>
    <q-item-section
      v-if="item.is_declined && item.decline_reason"
      side
    >
      <q-icon
        class="checklist-item__decline-icon"
        name="error_outline"
        color="orange"
        size="20px"
      >
        <q-tooltip
          anchor="top middle"
          self="bottom middle"
          :offset="[0, 8]"
          class="bg-grey-9 text-body2"
          max-width="280px"
        >
          {{ item.decline_reason }}
        </q-tooltip>
      </q-icon>
    </q-item-section>
    <q-item-section side>
      <AppActionsButton
        :actions="actions"
        icon="more_vert"
      />
    </q-item-section>
  </q-item>
</template>

<script lang="ts" setup>
import { computed, ref } from 'vue'
import { IChecklistItem } from 'src/types/TaskManager/task'
import AppActionsButton from 'src/components/default/AppActionsButton.vue'
import {
  convertChecklistItemToTaskKey,
  deleteChecklistItemKey,
  updateChecklistItemKey
} from 'src/symbols/task-manager.keys'
import { injectStrict } from 'src/utils/injection'

const props = defineProps<{
  item: IChecklistItem
}>()

// Only one emit for decline because it needs a modal window for reason text and this modal should be one for all.
const emit = defineEmits<{
  (e: 'decline', value: IChecklistItem): void
}>()

const updateChecklistItem = injectStrict(updateChecklistItemKey)
const deleteChecklistItem = injectStrict(deleteChecklistItemKey)
const convertChecklistItemToTask = injectStrict(convertChecklistItemToTaskKey)

const declineItem = () => {
  emit('decline', props.item)
}
const deleteItem = () => {
  deleteChecklistItem(props.item.id)
}
const activateItem = () => {
  updateChecklistItem(props.item.id, {
    is_declined: false,
    decline_reason: ''
  })
}
const transformToTask = () => {
  convertChecklistItemToTask(props.item.id)
}

const actions = computed(() => [{
  name: 'activate',
  label: 'Activate item',
  icon: 'do_disturb',
  color: 'green',
  is_active: props.item.is_declined,
  func: activateItem
}, {
  name: 'decline',
  label: 'Decline item',
  icon: 'do_disturb',
  color: 'red',
  is_active: !props.item.is_declined,
  func: declineItem
}, {
  name: 'transform',
  label: 'Transform to task',
  icon: 'subdirectory_arrow_right',
  is_active: !props.item.converted_task_id,
  func: transformToTask
}, {
  name: 'delete',
  label: 'Delete item',
  icon: 'delete',
  is_active: true,
  func: deleteItem
}])

const checklistItem = ref(props.item)
const checklistItemTitlePopup = ref<InstanceType<typeof import('quasar').QPopupEdit> | null>(null)
const selectedItems = defineModel<string[]>({ required: true })

const closeTitlePopup = () => {
  if (checklistItemTitlePopup.value) {
    checklistItemTitlePopup.value.cancel()
  }
}
const updateTitle = async (title: string) => {
  if (title !== props.item.title) {
    await updateChecklistItem(props.item.id, { title })

    closeTitlePopup()
  }

  closeTitlePopup()
}
</script>

<style lang="scss" scoped>
.checklist-item {
  padding: 2px 10px;

  &__section {
    display: flex;
    flex-direction: row;
    justify-items: center;
    align-items: center;
  }

  &__decline-icon {
    position: relative;
    z-index: 1;
    cursor: help;
  }

  &--declined {
    position: relative;
    &:after {
      content: " ";
      position: absolute;
      top: 50%;
      left: 0;
      border-bottom: 1px solid #111;
      width: 100%;
      pointer-events: none;
    }
  }
}
</style>
