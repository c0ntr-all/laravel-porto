import TextCell from './components/TextCell.vue'
import { cellRendererRegistry } from './registries/cellRenderers'

cellRendererRegistry.register({
  type: 'text',
  label: 'Text',
  defaultValue: () => '',
  component: TextCell
})
