import {
  MOVIE_ACTOR_PROFESSION,
  MOVIE_DIRECTOR_PROFESSION,
  MOVIE_PRODUCER_PROFESSION,
  MOVIE_PROFESSION_GROUP_ORDER,
  MOVIE_WRITER_PROFESSION
} from 'src/enums/Movie/MovieProfession'
import {
  IMovieCredit,
  IMovieCreditGroup,
  IMovieProfession,
  IPersonCreditGroup,
  IPersonMovieCredit
} from 'src/types/Movie'

const PROFESSION_ORDER_INDEX = new Map(
  MOVIE_PROFESSION_GROUP_ORDER.map((enName, index) => [enName, index])
)

export function isActorProfession(profession: Pick<IMovieProfession, 'en_name'>): boolean {
  return profession.en_name === MOVIE_ACTOR_PROFESSION
}

export function professionLabel(profession: IMovieProfession): string {
  return profession.name || profession.en_name
}

export function movieActorCredits(credits: IMovieCredit[]): IMovieCredit[] {
  return credits.filter(credit => isActorProfession(credit.profession))
}

export function movieCrewCredits(credits: IMovieCredit[]): IMovieCredit[] {
  return credits.filter(credit => !isActorProfession(credit.profession))
}

function professionGroupRank(profession: IMovieProfession): number {
  const byEnName = PROFESSION_ORDER_INDEX.get(profession.en_name)

  if (byEnName !== undefined) {
    return byEnName
  }

  const normalizedName = (profession.name || '').trim().toLowerCase()
  let fallbackEnName: string | null = null

  if (normalizedName.includes('актёр') || normalizedName.includes('актер')) {
    fallbackEnName = MOVIE_ACTOR_PROFESSION
  } else if (normalizedName.includes('режисс')) {
    fallbackEnName = MOVIE_DIRECTOR_PROFESSION
  } else if (normalizedName.includes('продюс')) {
    fallbackEnName = MOVIE_PRODUCER_PROFESSION
  } else if (normalizedName.includes('сценарист')) {
    fallbackEnName = MOVIE_WRITER_PROFESSION
  }

  if (fallbackEnName) {
    return PROFESSION_ORDER_INDEX.get(fallbackEnName) ?? MOVIE_PROFESSION_GROUP_ORDER.length
  }

  return MOVIE_PROFESSION_GROUP_ORDER.length
}

function sortProfessionGroups<T extends { profession: IMovieProfession }>(
  groups: Array<{ profession: IMovieProfession, credits: T[] }>
): Array<{ profession: IMovieProfession, credits: T[] }> {
  return [...groups].sort((left, right) => {
    const rankDiff = professionGroupRank(left.profession) - professionGroupRank(right.profession)

    if (rankDiff !== 0) {
      return rankDiff
    }

    return professionLabel(left.profession).localeCompare(
      professionLabel(right.profession),
      'ru'
    )
  })
}

function groupCreditsByProfession<T extends { profession: IMovieProfession }>(
  credits: T[]
): Array<{ profession: IMovieProfession, credits: T[] }> {
  const groups: Array<{ profession: IMovieProfession, credits: T[] }> = []
  const indexByKey = new Map<string, number>()

  for (const credit of credits) {
    const key = credit.profession.en_name || credit.profession.id
    let index = indexByKey.get(key)

    if (index === undefined) {
      index = groups.length
      indexByKey.set(key, index)
      groups.push({
        profession: credit.profession,
        credits: []
      })
    }

    groups[index]?.credits.push(credit)
  }

  return sortProfessionGroups(groups)
}

export function groupMovieCredits(credits: IMovieCredit[]): IMovieCreditGroup[] {
  return groupCreditsByProfession(credits)
}

export function groupPersonCredits(credits: IPersonMovieCredit[]): IPersonCreditGroup[] {
  return groupCreditsByProfession(credits)
}
