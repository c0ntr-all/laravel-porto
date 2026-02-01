<template>
  <q-item class="checklist-item" :class="{'checklist-item--declined': checklistItem.is_declined}">
    <q-item-section class="checklist-item__section" avatar>
      <q-checkbox
        v-model="selectedItems"
        :val="item.id"
        :disable="item.is_declined === 1"
        @click="updateStatus(item.id)"
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
import { IAction } from 'src/components/types'

const props = defineProps<{
  item: IChecklistItem
}>()
const emit = defineEmits<{
  (e: 'activate', value: IChecklistItem): void
  (e: 'decline', value: IChecklistItem): void
  (e: 'update', value: IChecklistItem): void
  (e: 'delete', value: IChecklistItem): void
}>()

const activateItem = () => {
  emit('activate', props.item)
}
const declineItem = () => {
  emit('decline', props.item)
}
const deleteItem = () => {
  emit('delete', props.item)
}

const actions: IAction[] = computed(() => [{
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
  name: 'delete',
  label: 'Delete item',
  icon: 'delete',
  is_active: true,
  func: deleteItem
}])

const checklistItem = ref(props.item)
const checklistItemTitlePopup = ref<InstanceType<typeof import('quasar').QPopupEdit> | null>(null)
const selectedItems = defineModel<string[]>({ required: true })

const closePopup = () => {
  if (checklistItemTitlePopup.value) {
    checklistItemTitlePopup.value.cancel()
  }
}

const updateTitle = async (newTitle: string) => {
  if (newTitle !== props.item.title) {
    emit('update', { title: newTitle })
  }
}

const updateStatus = (id: string) => {
  if (props.item.is_declined) {
    return
  }

  const status = !selectedItems.value.includes(id)

  emit('update', { status })
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

  &--declined {
    position: relative;
    &:after {
      content: " ";
      position: absolute;
      top: 50%;
      left: 0;
      border-bottom: 1px solid #111;
      width: 100%;
    }
  }
}
</style>
