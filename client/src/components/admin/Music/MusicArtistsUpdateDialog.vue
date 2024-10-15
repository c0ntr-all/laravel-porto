<template>
  <q-dialog v-model="show">
    <q-card style="width: 700px; max-width: 80vw;">
      <q-card-section>
        <div class="text-h6">Artist Edit</div>
      </q-card-section>

      <q-card-section class="q-pt-none">
        <q-form class="q-gutter-y-md column">
          <q-input
            v-model="model.name"
            label="Name"
            :rules="[(val: any) => val && val.length > 0 || 'Поле name должно быть заполнено!']"
            outlined
          />
          <q-input v-model="model.description" label="Description" type="textarea" outlined/>
          <q-file v-model="newImage" label="Cover" name="poster" filled>
            <template v-if="model.image" v-slot:append>
              <q-icon name="cancel" @click.stop.prevent="model.image = null" class="cursor-pointer"/>
            </template>
          </q-file>
          <div v-if="newImage" class="artist-edit__image">
            <img :src="newImagePreview" alt="">
          </div>
          <div v-else-if="!newImage && typeof model.image === 'string'" class="artist-edit__image">
            <img :src="model.image" alt="">
          </div>
          <q-select
            @filter="baseTagsFilter"
            v-model="model.tags.base"
            :options="baseOptions"
            :loading="tagsLoading"
            label="Base tags"
            input-debounce="0"
            option-value="id"
            option-label="name"
            use-input
            use-chips
            multiple
            outlined
          />
          <q-select
            @filter="secondaryTagsFilter"
            v-model="model.tags.secondary"
            :options="secondaryOptions"
            :loading="tagsLoading"
            label="Secondary tags"
            input-debounce="0"
            use-input
            use-chips
            multiple
            outlined
          />
        </q-form>
      </q-card-section>

      <q-card-actions align="right" class="bg-white">
        <q-btn label="Save" color="primary" @click="updateArtist" :loading="updateButtonLoading"/>
        <!--Todo: Need to write cancel update handler for returning previous values to model-->
        <q-btn label="Cancel" v-close-popup/>
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup>
import { computed, inject, onMounted, ref, watch, watchEffect } from 'vue'
import { api } from 'src/boot/axios'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import {
  IArtist,
  IResponseArtist,
  ITagShort
} from 'src/components/admin/Music/types'

interface IResponseTag {
  id: string
  attributes: {
    name: string
    description: string | null
    is_base: boolean
    parent_id: string
  }
}
interface IArtistModel {
  name: string
  image: string | null | File
  description: string | null
  tags: {
    base: ITagShort[]
    secondary: ITagShort[]
  }
}
interface IUpdateArtistResponse {
  data: IResponseArtist
  included: any
  meta: {
    message: string
  }
}

const props = defineProps<{
  modelValue: any,
  artist: IArtist
}>()
const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'updated', value: IArtist): void
}>()

const model = ref<IArtistModel>({
  name: props.artist.name,
  image: props.artist.image,
  description: props.artist.description,
  tags: {
    base: props.artist.relationships.tags.data.filter((tag: ITagShort) => tag.is_base),
    secondary: props.artist.relationships.tags.data.filter((tag: ITagShort) => !tag.is_base)
  }
})

const tagsLoading = ref(true)
const baseTags = ref<ITagShort[]>([])
const secondaryTags = ref<ITagShort[]>([])
const baseOptions = ref<ITagShort[]>([])
const secondaryOptions = ref<ITagShort[]>([])
const newImage = ref()
const show = ref(props.modelValue)
const updateButtonLoading = ref(false)

const transformArtistFromResponse = inject<((responseArtist: IResponseArtist, responseData: any) => IArtist)>('transformArtistFromResponse')!

const updateArtist = async () => {
  updateButtonLoading.value = true

  const formData = prepareRequestData()

  await api.post<IUpdateArtistResponse>(`v1/music/artists/${props.artist.id}`, formData)
    .then(response => {
      const artist = transformArtistFromResponse(response.data.data, response.data)
      emit('updated', artist)
      handleApiSuccess(response)
    }).catch(error => {
      handleApiError(error)
    }).finally(() => {
      updateButtonLoading.value = false
    })
}

const getTagsSelect = async () => {
  await api.get('v1/music/tags')
    .then(response => {
      const allTags = response.data.data.map((responseTag: IResponseTag) => {
        return {
          id: responseTag.id,
          name: responseTag.attributes.name,
          is_base: responseTag.attributes.is_base
        }
      })
      baseTags.value = baseOptions.value = allTags.filter((tag: ITagShort) => tag.is_base)
      secondaryTags.value = secondaryOptions.value = allTags.filter((tag: ITagShort) => !tag.is_base)
      tagsLoading.value = false
    }).catch(error => {
      handleApiError(error)
    })
}

const prepareRequestData = () => {
  const formData = new FormData()

  formData.append('_method', 'PATCH')
  formData.append('name', model.value.name)
  if (model.value.description) {
    formData.append('description', model.value.description)
  }

  if (typeof model.value.image === 'object' && model.value.image !== null) {
    formData.append('image_file', model.value.image)
  } else if (model.value.image === null) {
    formData.append('image_file', '')
  }

  const baseTagsValues = model.value.tags.base.map(item => item.id)
  const secondaryTagsValues = model.value.tags.secondary.map(item => item.id)

  baseTagsValues.concat(secondaryTagsValues).forEach(val => {
    formData.append('tags[]', val)
  })

  return formData
}

const newImagePreview = computed(() => {
  return URL.createObjectURL(newImage.value)
})

const baseTagsFilter = (val: string, update: any) => {
  update(() => {
    const needle = val.toLowerCase()
    baseOptions.value = baseTags.value.filter(tag => tag.name.toLowerCase().indexOf(needle) > -1)
  })
}
const secondaryTagsFilter = (val: string, update: any) => {
  update(() => {
    const needle = val.toLowerCase()
    secondaryOptions.value = secondaryTags.value.filter(tag => tag.name.toLowerCase().indexOf(needle) > -1)
  })
}

watchEffect(() => {
  show.value = props.modelValue
})
watch(show, (newVal) => {
  if (newVal !== props.modelValue) {
    emit('update:modelValue', newVal)
  }
})
watch(newImage, (newValue) => {
  model.value.image = newValue
})
onMounted(() => {
  getTagsSelect()
})
</script>

<style lang="scss" scoped>
.artist {
  &-edit {
    &__image {
      width: 250px;
      height: 250px;

      img {
        width: 100%;
        height: 100%;
      }
    }
  }
  &-row {
    &__image {
      width: 50px;
      height: 50px;
      overflow: hidden;

      img {
        width: 100%;
        object-fit: cover;
      }
    }
    &__tag {
      & span:not(:last-child) {
        &::after {
          content: ', '
        }
      }
    }
  }
  &-search {
    max-width: 400px;
  }
}
</style>
