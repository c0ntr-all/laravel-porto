export enum PostContentTypeEnum {
  DEFAULT = 'default',
  MUSIC = 'music',
  MOVIE = 'movie',
  TV_SERIES = 'tv_series',
  GAME = 'game',
  TASK = 'task',
  ALBUM = 'album',
  NOTE = 'note'
}

export const POST_CONTENT_TYPES = Object.values(PostContentTypeEnum)

export const POST_CONTENT_TYPE_LABELS: Record<PostContentTypeEnum, string> = {
  [PostContentTypeEnum.DEFAULT]: 'Обычный',
  [PostContentTypeEnum.MUSIC]: 'Музыка',
  [PostContentTypeEnum.MOVIE]: 'Фильм',
  [PostContentTypeEnum.TV_SERIES]: 'Сериал',
  [PostContentTypeEnum.GAME]: 'Игра',
  [PostContentTypeEnum.TASK]: 'Задача',
  [PostContentTypeEnum.ALBUM]: 'Альбом',
  [PostContentTypeEnum.NOTE]: 'Заметка'
}
