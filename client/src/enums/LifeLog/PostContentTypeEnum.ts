export enum PostContentTypeEnum {
  DEFAULT = 'default',
  MUSIC = 'music',
  MOVIE = 'movie',
  GAME = 'game'
}

export const POST_CONTENT_TYPES = Object.values(PostContentTypeEnum)

export const POST_CONTENT_TYPE_LABELS: Record<PostContentTypeEnum, string> = {
  [PostContentTypeEnum.DEFAULT]: 'Обычный',
  [PostContentTypeEnum.MUSIC]: 'Музыка',
  [PostContentTypeEnum.MOVIE]: 'Фильмы и сериалы',
  [PostContentTypeEnum.GAME]: 'Игра'
}
