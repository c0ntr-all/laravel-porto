<template>
  <q-input v-model="datetimeModel" name="datetime" dense filled>
    <template v-slot:prepend>
      <q-icon name="event" class="cursor-pointer">
        <q-popup-proxy transition-show="scale" transition-hide="scale" cover>
          <q-date v-model="datetimeModel" mask="YYYY-MM-DD" name="datetime">
            <div class="row items-center justify-end">
              <q-btn label="Close" color="primary" flat v-close-popup />
            </div>
          </q-date>
        </q-popup-proxy>
      </q-icon>
    </template>
  </q-input>
</template>

<script lang="ts" setup>
const datetimeModel = defineModel<string>({ required: true })
</script>

<style lang="scss" scoped>

</style>
