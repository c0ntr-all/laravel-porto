<template>
  <div class="lifelog-period-form q-pa-md">
    <div class="text-h6 q-mb-md">Создать период</div>
    <div class="q-mb-md">
      <q-input
        name="period"
        ref="titleRef"
        v-model="model.title"
        class="q-pa-none"
        label="Title of period"
        :rules="[val => !!val || 'Field is required']"
        dense
        outlined
      />
    </div>
    <div class="flex justify-between items-center">
      <div class="lifelog-periods-table">
        <div class="lifelog-periods-table__row">
          <div class="lifelog-periods-table__col">Start Post</div>
          <div v-if="startPeriodPost" class="lifelog-periods-table__col">
            {{ startPeriodPost.id }}. {{ startPeriodPost.title }} ({{ startPeriodPost.date }} {{ startPeriodPost.time }})
          </div>
          <div v-else class="lifelog-periods-table__col lifelog-periods-table__col--empty">
            Не выбрано
          </div>
        </div>
        <div class="lifelog-periods-table__row">
          <div class="lifelog-periods-table__col">End Post</div>
          <div v-if="endPeriodPost" class="lifelog-periods-table__col">
            {{ endPeriodPost.id }}. {{ endPeriodPost.title }} ({{ endPeriodPost.date }} {{ endPeriodPost.time }})
          </div>
          <div v-else class="lifelog-periods-table__col lifelog-periods-table__col--empty">
            Не выбрано
          </div>
        </div>
      </div>
      <div class="lifelog-periods-actions">
        <q-btn
          class="q-mt-none q-ml-md"
          color="grey"
          label="Reset"
          @click="resetPeriod"
          :disable="!isSaveAvailable"
          outline
        />
        <q-btn
          label="Создать"
          color="primary"
          @click="processCreatePeriod"
          :disable="!isSaveAvailable"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>+ clearModel = () => {
  model.value = baseModel.value
}

const processCreatePeriod = () => {
  const payload = {
    title: model.value.title,
    start_post_id: startPeriodPost.value.id,
    end_post_id: endPeriodPost.value.id
  }
  createPeriod(payload).then(() => {
    clearModel()
  })
}

</script>

<style lang="scss" scoped>
.lifelog-period-form {
  width: 100%;
  background-color: #ffffff;
}
.lifelog-periods-table {
  &__row {
    display: flex;
    column-gap: 1rem;
  }
  &__col {
    &:first-child {
      width: 62px;
      text-align: right;
      font-weight: bold;
    }

    &--empty {
      color: #aaa;
    }
  }
}
.lifelog-periods-actions {
  display: flex;
  column-gap: 1rem;
}
</style>
