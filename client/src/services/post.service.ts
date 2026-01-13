import { useGalleryStore } from 'src/stores/modules/galleryStore'
import { mapFileToUploadItem } from 'src/api/mappers/gallery.mapper'

interface IResult {
  images: File[],
  videos: File[],
  documents: File[],
  music: File[],
  other: File[]
}

export async function uploadPostAttachments(files: File[]): Promise<string[] | undefined> {
  const fileGroups = groupFileTypes(files)

  if (fileGroups.images.length) {
    const galleryStore = useGalleryStore()

    try {
      const mappedImages = fileGroups.images.map((file: File) => mapFileToUploadItem(file))
      const result = await galleryStore.uploadImages(mappedImages)

      return result ? result.map((item) => item.id) : []
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
