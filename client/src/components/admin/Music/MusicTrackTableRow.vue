<template>
  <q-tr
    class="table-track"
    :class="{'table-track--active': isCurrent}"
    :props="props.rowProps"
  >
    <q-td
      v-for="col in props.rowProps.cols"
      :key="col.name"
      :props="props.rowProps"
    >
      <template v-if="col.name === 'number'">
        <q-btn
          @click="play"
          class="table-track__play-icon"
          :icon="icon"
          flat
          round
          dense
        />
        <div class="table-track__number">{{ col.value }}</div>
      </template>
      <template v-else-if="col.name === 'image'">
        <q-img
          v-if="col.value"
          :src="col.value"
          class="table-track-cover__image"
          :alt="col.value"
          height="40px"
          width="40px"
        />
      </template>
      <template v-else-if="col.name === 'tags'">
        <div class="table-track__tags">
          <template v-if="col.value?.length">
            <p class="q-ma-none">{{ tagsToString(col.value?.tags) }}</p>
            <p class="q-ma-none">{{ tagsToString(col.value?.tags, false) }}</p>
          </template>
        </div>
      </template>
      <template v-else>
        {{ col.value }}
      </template>
    </q-td>
  </q-tr>
</template>

<script lang="ts" setup>
import { computed, ref } from 'vue'
import { useMusicPlayer } from 'src/stores/modules/musicPlayer'
import { ITagShort, ITrack } from 'src/components/admin/Music/types'

interface Col {
  name: string
  value: any
}

interface RowProps {
  row: ITrack
  cols: Col[]
}

const props = defineProps<{
  rowProps: RowProps
}>()
const emit = defineEmits<{
  (e: 'play', value: ITrack): void
}>()
const row = ref(props.rowProps.row)
const musicPlayer = useMusicPlayer()
const isCurrent = computed(() => musicPlayer.isCurrentTrack(row.value.id))
const isPlaying = computed(() => isCurrent.value && musicPlayer.isPlaying)
const icon = computed(() => isPlaying.value ? 'pause' : 'play_arrow')
const tagsToString = (tagsArray: ITagShort[], isBase: boolean = true) => {
  tagsArray.filter(tag => tag.is_base === isBase).join(', ')
}

const play = () => {
  emit('play', props.rowProps.row)
}
</script>

<style lang="scss" scoped>
.table-track {
  &-cover {
    display: flex;
    position: relative;
    justify-content: center;
    align-items: center;
    flex-shrink: 0;
    width: 30px;
    height: 30px;
    margin: 4px;
    border-radius: 8px;
    background: #ccc;

    &__image {
      border-radius: 8px;
    }
  }
  &:hover {
    .table-track__play-icon {
      display: flex;
    }

    .table-track__number {
      display: none;
    }
  }

  &--active {
    background-color: rgba(0, 0, 0, 0.03);

    .table-track__play-icon {
      display: flex;
    }

    .table-track__number {
      display: none;
    }
  }

  &__play-icon {
    display: none;
  }

  &__number {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 2.4em;
    min-width: 2.4em;
  }
}
</style>
