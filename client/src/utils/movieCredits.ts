import { MOVIE_ACTOR_PROFESSION } from 'src/enums/Movie/MovieProfession'
import { IMovieCredit, IMovieCreditGroup, IMovieProfession } from 'src/types/Movie'

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

export function groupMovieCredits(credits: IMovieCredit[]): IMovieCreditGroup[] {
  const groups: IMovieCreditGroup[] = []
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

  return groups
}
