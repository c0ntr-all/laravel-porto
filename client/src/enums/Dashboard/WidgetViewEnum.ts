export enum WidgetViewEnum {
  List = 'list',
  Count = 'count',
  Media = 'media',
  Html = 'html',
  Text = 'text',
  Welcome = 'welcome',
  Custom = 'custom'
}

export type WidgetView = `${WidgetViewEnum}`
