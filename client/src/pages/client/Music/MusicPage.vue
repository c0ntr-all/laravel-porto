<template>
  <q-card class="q-mb-md" flat>
    <q-tabs
      align="left"
      no-caps
      outside-arrows
      mobile-arrows
    >
      <q-route-tab
        exact
        :to="{ name: 'music-tracks' }"
        label="Tracks"
      />
      <q-route-tab
        exact
        :to="{ name: 'music-artists' }"
        label="Artists"
      />
      <q-route-tab
        exact
        :to="{ name: 'music-playlists' }"
        label="Playlists"
      />
      <q-route-tab
        exact
        :to="{ name: 'music-tags' }"
        label="Tags"
      />
      <q-route-tab
        exact
        :to="{ name: 'music-history' }"
        label="History"
      />
    </q-tabs>
  </q-card>
  <router-view />
</template>

<script lang="ts" setup>
</script>
