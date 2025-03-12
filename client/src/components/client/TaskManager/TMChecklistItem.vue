<template>
  <q-item>
    <q-item-section avatar>
      <q-checkbox
        v-model="selectedItems"
        :val="checklistItem.id"
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
  </q-item>
</template>

<script lang="ts" setup>
import { inject, ref } from 'vue'
import { IChecklistItem } from 'src/components/client/TaskManager/types'

interface IUpdateChecklistItemResponse {
  data: {
    type: string
    id: string
    attributes: {
      title: string
      finished_at: string
      created_at: string
    }
  },
  meta: {
    message?: string
  }
}

const props = defineProps<{
  item: IChecklistItem
}>()

const checklistItem = ref(props.item)
const checklistItemTitlePopup = ref<InstanceType<typeof import('quasar').QPopupEdit> | null>(null)
const selectedItems = defineModel<string[]>({ required: true })

const updateChecklistItem = inject<{
  (checklistItem: IChecklistItem, data: {
    title?: string,
    is_finished?: boolean
  }): Promise<IUpdateChecklistItemResponse>
    }>('updateChecklistItem')!
const closePopup = () => {
  if (checklistItemTitlePopup.value) {
    checklistItemTitlePopup.value.cancel()
  }
}

const updateTitle = async (newTitle: string) => {
  if (newTitle !== props.item.title) {
    await updateChecklistItem(props.item, { title: newTitle })
      .then((response: IUpdateChecklistItemResponse) => {
        const responseData = response.data
        checklistItem.value.title = responseData.attributes.title
      }).finally(() => {
        closePopup()
      })
  }
}

const updateStatus = (id: string) => {
  const status = !selectedItems.value.includes(id)
  updateChecklistItem(props.item, { is_finished: status })
    .catch(() => {
      const idx = selectedItems.value.filter(item => item === id).indexOf(id)
      if (idx !== -1) {
        selectedItems.value.splice(idx, 1)
      }
    }).finally(() => {
      closePopup()
    })
}

</script>

<style lang="scss" scoped>

</style>
