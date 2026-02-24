import { useGalleryStore } from 'src/stores/modules/galleryStore'
import { mapFileToUploadItem } from 'src/api/mappers/gallery.mapper'

interface IResult {
  images: File[],
  videos: File[],
  documents: File[],
  music: File[],
  other: File[]
}

export async function uploadPostAttachments(files: File[]): Promise<{id: string, type: string}[] | undefined> {
  const fileGroups = groupFileTypes(files)
  const galleryStore = useGalleryStore()

  if (fileGroups.images.length) {
    try {
      const mappedImages = fileGroups.images.map((file: File) => mapFileToUploadItem(file))
      const url = 'v1/gallery/albums/1/images/upload'
      const result = await galleryStore.uploadFiles(url, mappedImages)

      return result ? result.map((item): {id: string, type: string} => {
        return {
          id: item.id,
          type: item.type
        }
      }) : []
    } catch (err: any) {
      console.error(err)
    }
  }
  if (fileGroups.videos.length) {
    try {
      const url = 'v1/gallery/albums/1/videos/upload'
      const mappedImages = fileGroups.videos.map((file: File) => mapFileToUploadItem(file))
      const result = await galleryStore.uploadFiles(url, mappedImages)

      return result ? result.map((item) => {
        return {
          id: item.id,
          type: item.type
        }
      }) : []
    } catch (err: any) {
      console.error(err)
    }
  }
}

export function groupFileTypes(files: File[]): IResult {
  const imagesMimePatterns = ['image/']
  const videoMimePatterns = ['video/']
  const audioMimePatterns = ['audio/']
  const documentMimePatterns = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument'
  ]

  const result: IResult = {
    images: [],
    videos: [],
    documents: [],
    music: [],
    other: []
  }

  files.forEach((file: File) => {
    const mime = file.type

    if (imagesMimePatterns.some(pattern => mime.startsWith(pattern))) {
      result.images.push(file)
    } else if (videoMimePatterns.some(pattern => mime.startsWith(pattern))) {
      result.videos.push(file)
    } else if (audioMimePatterns.some(pattern => mime.startsWith(pattern))) {
      result.music.push(file)
    } else if (documentMimePatterns.some(pattern => mime.includes(pattern))) {
      result.documents.push(file)
    } else {
      result.other.push(file)
    }
  })

  return result
}
