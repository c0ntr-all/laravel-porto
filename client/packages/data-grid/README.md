# @hp/data-grid

Лёгкий редактируемый DataGrid для Vue 3. Состояние — один JSON-документ, который можно хранить в поле бэкенда и заново рендерить.

## Подключение

Пакет живёт исходниками (Vite/Quasar обрабатывают `.vue` сами). В другом проекте:

```ts
import { DataGrid, parseDataGrid, createEmptyDataGrid } from '@hp/data-grid'
import '@hp/data-grid/styles'
```

Алиас на `packages/data-grid/src` или `"@hp/data-grid": "file:./packages/data-grid"`.

## JSON

```json
{
  "version": 1,
  "meta": { "id": "grid_...", "title": "", "templateId": null },
  "columns": [
    { "id": "col_...", "title": "Колонка 1", "type": "text", "width": null, "settings": {} }
  ],
  "rows": [
    { "id": "row_...", "cells": { "col_...": { "value": "текст" } } }
  ]
}
```

Ячейки разреженные: ключ — `column.id`. Тип колонки (`type`) и `settings` зарезервированы под будущие рендеры. Шаблоны таблиц — через `templateRegistry.register()`, без реализации заготовок в этом релизе.

## API

```vue
<DataGrid v-model="doc" :labels="{ addRow: 'Добавить строку' }" />
```

- `createEmptyDataGrid()` / `parseDataGrid(json)` / `serializeDataGrid(doc)`
- `cellRendererRegistry.register({ type, component })` — точка расширения рендеров
- `templateRegistry` — точка расширения шаблонов
