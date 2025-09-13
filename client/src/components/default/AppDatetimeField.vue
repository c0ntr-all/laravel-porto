<template>
  <q-input v-model="datetimeModel" dense filled>
    <template v-slot:prepend>
      <q-icon name="event" class="cursor-pointer">
        <q-popup-proxy transition-show="scale" transition-hide="scale" cover>
          <q-date v-model="datetimeModel" mask="YYYY-MM-DD HH:mm">
            <div class="row items-center justify-end">
              <q-btn label="Close" color="primary" flat v-close-popup />
            </div>
          </q-date>
        </q-popup-proxy>
      </q-icon>
    </template>

    <template v-slot:append>
      <q-icon name="access_time" class="cursor-pointer">
        <q-popup-proxy transition-show="scale" transition-hide="scale" cover>
          <q-time v-model="datetimeModel" mask="YYYY-MM-DD HH:mm" format24h>
            <div class="row items-center justify-end">
              <q-btn label="Close" color="primary" flat v-close-popup />
            </div>
          </q-time>
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
