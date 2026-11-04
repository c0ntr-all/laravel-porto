<template>
  <div
    v-if="hasAny"
    class="note-links"
  >
    <section v-if="note.wiki_links.length">
      <h3>Ссылки</h3>
      <router-link
        v-for="link in note.wiki_links"
        :key="`${link.id}:${link.label}`"
        class="note-links__item"
        :to="`/notes/${link.id}`"
      >
        {{ link.name || link.label }}
      </router-link>
    </section>

    <section v-if="note.backlinks.length">
      <h3>Обратные ссылки</h3>
      <router-link
        v-for="link in note.backlinks"
        :key="`${link.id}:${link.label}`"
        class="note-links__item"
        :to="`/notes/${link.id}`"
      >
        {{ link.name || link.label || 'Без названия' }}
      </router-link>
    </section>

    <section v-if="note.unresolved_links.length">
      <h3>Не найдены</h3>
      <q-btn
        v-for="label in note.unresolved_links"
        :key="label"
        class="note-links__missing"
        flat
        dense
        no-caps
        :label="label"
        @click="emit('create-unresolved', label)"
      >
        <q-tooltip>Создать заметку. Связь появится после сохранения источника</q-tooltip>
      </q-btn>
    </section>

    <section v-if="note.ambiguous_links.length">
      <h3>Неоднозначные</h3>
      <span
        v-for="label in note.ambiguous_links"
        :key="label"
        class="note-links__ambiguous"
      >
        {{ label }}
      </span>
    </section>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { INote } from 'src/types'

const props = defineProps<{
  note: INote
}>()

const emit = defineEmits<{
  'create-unresolved': [label: string]
}>()

const hasAny = computed(() => props.note.wiki_links.length > 0 ||
  props.note.backlinks.length > 0 ||
  props.note.unresolved_links.length > 0 ||
  props.note.ambiguous_links.length > 0)
</script>

<style lang="scss" scoped>
.note-links {
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin-top: 20px;
  padding-top: 16px;
  border-top: 1px solid #e6e8f0;

  h3 {
    margin: 0 0 8px;
    font-size: 13px;
    font-weight: 600;
    color: #5c6278;
  }

  section {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }

  h3 {
    flex-basis: 100%;
  }

  &__item,
  &__missing,
  &__ambiguous {
    padding: 4px 10px;
    border-radius: 999px;
    background: #f4f5fa;
    font-size: 13px;
  }

  &__item {
    color: $primary;
    font-weight: 600;
    text-decoration: none;
  }

  &__missing {
    border: 0;
    color: #9aa0b5;
    cursor: pointer;
  }

  &__ambiguous {
    color: #8a6232;
    background: #fff4e5;
  }
}
</style>
