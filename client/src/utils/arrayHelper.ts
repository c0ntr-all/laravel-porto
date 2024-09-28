interface TreeNode {
  id: string
  children?: TreeNode[]
  [key: string]: any
}

export function insertIntoTree(array: TreeNode[], id: string, tag: TreeNode, childrenNodeName: string = 'children'): void {
  for (let i = 0; i < array.length; i++) {
    if (id === array[i].id) {
      if (array[i][childrenNodeName]) {
        array[i][childrenNodeName].push(tag)
      } else {
        array[i][childrenNodeName] = [tag]
      }
      return
    } else if (array[i][childrenNodeName]) {
      insertIntoTree(array[i][childrenNodeName], id, tag)
    }
  }
}

export function deleteFromTree(array: TreeNode[], id: string, childrenNodeName: string = 'children'): void {
  for (let i = 0; i < array.length; i++) {
    if (array[i].id === id) {
      array.splice(i, 1)
      return
    }
    if (array[i].children) {
      deleteFromTree(array[i][childrenNodeName], id)
    }
  }
}
