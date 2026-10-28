export enum GameImportStatusEnum {
  PENDING = 'pending',
  COMPLETED = 'completed',
  FAILED = 'failed'
}

export const GAME_IMPORT_STATUS_LABELS: Record<GameImportStatusEnum, string> = {
  [GameImportStatusEnum.PENDING]: 'Pending',
  [GameImportStatusEnum.COMPLETED]: 'Completed',
  [GameImportStatusEnum.FAILED]: 'Failed'
}
