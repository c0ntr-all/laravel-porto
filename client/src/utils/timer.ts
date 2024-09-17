export default class Timer {
  private targetSeconds: number | null
  private startedAt: number | null
  private pausedAt: number | null
  private pausedSeconds: number
  private timerId: number | null

  constructor() {
    this.targetSeconds = null
    this.startedAt = null
    this.pausedAt = null
    this.pausedSeconds = 0
    this.timerId = null

    this.clearTrigger()
  }

  start(seconds: number): void {
    this.clearTrigger()
    this.startedAt = this.now()
    this.update(seconds)
  }

  pause(): void {
    this.pausedAt = this.now()
    this.clearTrigger()
  }

  resume(): void {
    if (this.startedAt !== null && this.pausedAt !== null) {
      this.pausedSeconds += this.now() - this.pausedAt
      this.pausedAt = null

      if (!this.timerId && this.targetSeconds !== null) {
        this.setTrigger(this.targetSeconds - this.getElapsed()!)
      }
    }
  }

  update(seconds: number): void {
    if (this.startedAt !== null) {
      this.targetSeconds = seconds

      if (this.targetSeconds !== null) {
        if (this.pausedAt === null) {
          this.setTrigger(this.targetSeconds - this.getElapsed()!)
        }
      } else {
        this.clearTrigger()
      }
    }
  }

  getRemainingSeconds(): number | null {
    return this.targetSeconds !== null ? this.targetSeconds - this.getElapsed()! : null
  }

  private setTrigger(seconds: number): void {
    this.clearTrigger()
    this.timerId = window.setTimeout(() => {
      this.clearTrigger()
    }, seconds * 1000)
  }

  private clearTrigger(): void {
    if (this.timerId !== null) {
      clearTimeout(this.timerId)
    }
    this.timerId = null
  }

  getElapsed(): number | null {
    if (this.startedAt === null) {
      return null
    }

    let elapsed = this.now() - this.startedAt - this.pausedSeconds

    if (this.pausedAt !== null) {
      elapsed -= this.now() - this.pausedAt
    }

    return elapsed
  }

  isExpired(): boolean {
    const remainingSeconds: number | null = this.getRemainingSeconds()
    return remainingSeconds !== null && remainingSeconds < 0
  }

  // TODO: Move to helper
  now(): number {
    return Math.round(new Date().valueOf() / 1000)
  }
}
