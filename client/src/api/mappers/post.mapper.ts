import { IPostModel } from 'src/types'
import { INewTag, ITag } from 'src/types/tag'
import { IPostCreateDto } from 'src/api/DTO/PostCreateDto'

export function mapPostFormToCreateDto(postModel: IPostModel): IPostCreateDto {
  const data: IPostCreateDto = {
    title: postModel.title,
    content: postModel.content,
    tags: postModel.tags.map((t: ITag) => t.id),
    datetime: postModel.datetime
  }
  if (postModel.newTags) {
    data.new_tags = postModel.newTags.map((t: INewTag) => t.name)
  }

  return data
}
