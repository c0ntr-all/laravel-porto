export enum MovieImportStatusEnum {
  PENDING = 'pending',
  COMPLETED = 'completed',
  FAILED = 'failed'
}

export const MOVIE_IMPORT_STATUSES = Object.values(MovieImportStatusEnum)

export const MOVIE_IMPORT_STATUS_LABELS: Record<MovieImportStatusEnum, string> = {
  [MovieImportStatusEnum.PENDING]: 'Pending',
  [MovieImportStatusEnum.COMPLETED]: 'Completed',
  [MovieImportStatusEnum.FAILED]: 'Failed'
}
