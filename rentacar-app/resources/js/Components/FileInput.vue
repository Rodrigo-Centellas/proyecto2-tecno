<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  id: String,
  accept: {
    type: String,
    default: '*/*'
  },
  multiple: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  maxSize: {
    type: Number,
    default: 5120 // 5MB por defecto en KB
  },
  placeholder: {
    type: String,
    default: 'Seleccionar archivo...'
  },
  description: String,
  error: String
})

const emit = defineEmits(['change', 'error'])

const fileInput = ref(null)
const selectedFiles = ref([])
const isDragOver = ref(false)

const acceptedTypes = computed(() => {
  if (!props.accept || props.accept === '*/*') return []
  return props.accept.split(',').map(type => type.trim())
})

const fileTypeDescription = computed(() => {
  if (props.description) return props.description
  
  const types = acceptedTypes.value
  if (types.length === 0) return 'Cualquier tipo de archivo'
  
  const extensions = types.map(type => {
    if (type.startsWith('.')) return type.toUpperCase()
    if (type === 'image/*') return 'Imágenes'
    if (type === 'application/pdf') return 'PDF'
    if (type.includes('image/')) return type.replace('image/', '').toUpperCase()
    return type.toUpperCase()
  })
  
  return `Formatos: ${extensions.join(', ')}`
})

const maxSizeDescription = computed(() => {
  if (props.maxSize >= 1024) {
    return `Máximo ${(props.maxSize / 1024).toFixed(1)}MB`
  }
  return `Máximo ${props.maxSize}KB`
})

const handleFileSelect = (files) => {
  const fileList = Array.from(files)
  
  // Validar archivos
  const validFiles = []
  const errors = []
  
  for (const file of fileList) {
    // Validar tamaño
    if (file.size > props.maxSize * 1024) {
      errors.push(`${file.name}: Archivo muy grande (máximo ${maxSizeDescription.value})`)
      continue
    }
    
    // Validar tipo
    if (acceptedTypes.value.length > 0) {
      const isValidType = acceptedTypes.value.some(type => {
        if (type.startsWith('.')) {
          return file.name.toLowerCase().endsWith(type.toLowerCase())
        }
        if (type.includes('/*')) {
          const category = type.split('/')[0]
          return file.type.startsWith(category + '/')
        }
        return file.type === type
      })
      
      if (!isValidType) {
        errors.push(`${file.name}: Tipo de archivo no permitido`)
        continue
      }
    }
    
    validFiles.push(file)
  }
  
  if (errors.length > 0) {
    emit('error', errors)
    return
  }
  
  if (!props.multiple && validFiles.length > 1) {
    emit('error', ['Solo se permite un archivo'])
    return
  }
  
  selectedFiles.value = validFiles
  emit('change', props.multiple ? validFiles : validFiles[0])
}

const handleInputChange = (event) => {
  const files = event.target.files
  if (files && files.length > 0) {
    handleFileSelect(files)
  }
}

const handleDrop = (event) => {
  event.preventDefault()
  isDragOver.value = false
  
  const files = event.dataTransfer.files
  if (files && files.length > 0) {
    handleFileSelect(files)
  }
}

const handleDragOver = (event) => {
  event.preventDefault()
  isDragOver.value = true
}

const handleDragLeave = (event) => {
  event.preventDefault()
  isDragOver.value = false
}

const openFileDialog = () => {
  if (!props.disabled) {
    fileInput.value?.click()
  }
}

const removeFile = (index) => {
  if (props.multiple) {
    selectedFiles.value.splice(index, 1)
    emit('change', selectedFiles.value)
  } else {
    selectedFiles.value = []
    emit('change', null)
  }
  
  // Limpiar el input
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}
</script>

<template>
  <div class="w-full">
    <!-- Input oculto -->
    <input
      ref="fileInput"
      :id="id"
      type="file"
      :accept="accept"
      :multiple="multiple"
      :required="required && selectedFiles.length === 0"
      :disabled="disabled"
      @change="handleInputChange"
      class="hidden"
    />
    
    <!-- Zona de drop/click -->
    <div
      @click="openFileDialog"
      @drop="handleDrop"
      @dragover="handleDragOver"
      @dragleave="handleDragLeave"
      :class="[
        'relative border-2 border-dashed rounded-lg p-6 transition-all duration-200 cursor-pointer',
        isDragOver 
          ? 'border-blue-400 bg-blue-50' 
          : 'border-gray-300 hover:border-gray-400',
        disabled 
          ? 'opacity-50 cursor-not-allowed bg-gray-50' 
          : 'hover:bg-gray-50',
        error 
          ? 'border-red-300 bg-red-50' 
          : ''
      ]"
    >
      <div class="text-center">
        <!-- Icono -->
        <div class="mx-auto mb-4">
          <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
          </svg>
        </div>
        
        <!-- Texto principal -->
        <div class="mb-2">
          <p class="text-lg font-medium text-gray-900">
            {{ selectedFiles.length > 0 ? 'Cambiar archivo' : placeholder }}
          </p>
          <p class="text-sm text-gray-600">
            Arrastra y suelta aquí o haz clic para seleccionar
          </p>
        </div>
        
        <!-- Información de archivos permitidos -->
        <div class="text-xs text-gray-500 space-y-1">
          <p>{{ fileTypeDescription }}</p>
          <p>{{ maxSizeDescription }}</p>
        </div>
      </div>
    </div>
    
    <!-- Lista de archivos seleccionados -->
    <div v-if="selectedFiles.length > 0" class="mt-4 space-y-2">
      <div
        v-for="(file, index) in selectedFiles"
        :key="index"
        class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border"
      >
        <div class="flex items-center space-x-3">
          <!-- Icono del archivo -->
          <div class="flex-shrink-0">
            <svg v-if="file.type.startsWith('image/')" class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <svg v-else-if="file.type === 'application/pdf'" class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <svg v-else class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
          </div>
          
          <!-- Información del archivo -->
          <div>
            <p class="text-sm font-medium text-gray-900">{{ file.name }}</p>
            <p class="text-xs text-gray-500">{{ formatFileSize(file.size) }}</p>
          </div>
        </div>
        
        <!-- Botón para eliminar -->
        <button
          @click.stop="removeFile(index)"
          type="button"
          class="flex-shrink-0 p-1 text-red-400 hover:text-red-600 transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    </div>
    
    <!-- Mensaje de error -->
    <div v-if="error" class="mt-2 text-sm text-red-600">
      {{ error }}
    </div>
  </div>
</template>