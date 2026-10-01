export function pluralizeRu(value: number, forms: [string, string, string]): string {
  const abs = Math.abs(value) % 100
  const last = abs % 10

  if (abs > 10 && abs < 20) {
    return forms[2]
  }

  if (last === 1) {
    return forms[0]
  }

  if (last >= 2 && last <= 4) {
    return forms[1]
  }

  return forms[2]
}

export function formatMoviesCount(count: number): string {
  return `${count} ${pluralizeRu(count, ['фильм', 'фильма', 'фильмов'])}`
}
