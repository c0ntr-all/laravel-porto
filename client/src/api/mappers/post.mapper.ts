import { IPostModel } from 'src/types'
import { IPostCreateDto } from 'src/api/DTO/PostCreateDto'

export function mapPostFormToCreateDto(postModel: IPostModel): IPostCreateDto {
  return {
    title: postModel.title,
    content: postModel.content,
    tags: postModel.tags.map(t => t.id),
    datetime: postModel.datetime
  }
}
