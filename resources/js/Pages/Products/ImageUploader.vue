<script setup>
import { ref } from 'vue';
import "./css/ImageUploader.css";
const props = defineProps({
  images: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['update:images']);
const previews = ref([]);

const handleFileChange = (event) => {
  const files = Array.from(event.target.files);
  const newImages = [...props.images];
  const newPreviews = [...previews.value];

  files.forEach(file => {
    if (file.type.startsWith('image/')) {
      newImages.push(file);
      newPreviews.push(URL.createObjectURL(file));
    }
  });

  emit('update:images', newImages);
  previews.value = newPreviews;
};

const removeImage = (index) => {
  const newImages = [...props.images];
  const newPreviews = [...previews.value];
  
  newImages.splice(index, 1);
  newPreviews.splice(index, 1);
  
  emit('update:images', newImages);
  previews.value = newPreviews;
};
</script>

<template>
  <div>
    <div class="upload-area">
      <input
        type="file"
        multiple
        accept="image/*"
        @change="handleFileChange"
        class="file-input"
      />
      <div class="upload-text">
        <p>Click to upload or drag and drop</p>
        <p class="file-types">SVG, PNG, JPG or GIF (MAX. 800x400px)</p>
      </div>
    </div>

    <!-- Image Previews -->
    <div v-if="previews.length" class="preview-container">
      <div v-for="(preview, index) in previews" :key="index" class="preview-item">
        <img :src="preview" alt="Preview" />
        <button @click="removeImage(index)" class="remove-btn">×</button>
      </div>
    </div>
  </div>
</template>