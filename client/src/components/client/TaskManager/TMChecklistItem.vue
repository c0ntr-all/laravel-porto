<template>
  <q-item>
    <q-item-section avatar>
      <q-checkbox
        v-model="selectedItems"
        :val="item.id"
        @click="updateChecklistItemStatus()"
      />
    </q-item-section>
    <q-item-section>
      {{ item.title }}
      <q-popup-edit
        ref="titlePopup"
        v-model="item.title"
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
          <q-btn @click="titlePopup?.cancel()" label="Cancel" color="primary" flat/>
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
const titlePopup = ref<InstanceType<typeof import('quasar').QPopupEdit> | null>(null)
const selectedItems = defineModel<string[]>({ required: true })
const updateChecklistItemTitle = inject<{(checklistItem: IChecklistItem, title: string): Promise<IUpdateChecklistItemResponse>}>('updateChecklistItemTitle')!
const closePopup = () => {
  if (titlePopup.value) {
    titlePopup.value.cancel()
  }
}

const updateTitle = (newTitle: string) => {
  if (newTitle !== props.item.title) {
    updateChecklistItemTitle(props.item, newTitle)
      .then((response: IUpdateChecklistItemResponse) => {
        const responseData = response.data
        checklistItem.value.title = responseData.attributes.title
      }).finally(() => {
        closePopup()
      })
  }
}

const updateChecklistItemStatus = () => {
  // console.log(model)
}

</script>

<style lang="scss" scoped>

</style>
