<script setup>
import { ref } from 'vue';

const selectedImages = ref([]);

const handleImageUpload = (event) => {
    const files = Array.from(event.target.files);
    selectedImages.value = files;
    emit('update:images', files);
};
</script>

<template>
    <div>
        <input type="file" @change="handleImageUpload" multiple accept="image/*" />
        <p v-if="selectedImages.length">
            Selected Images: {{ selectedImages.map(file => file.name).join(', ') }}
        </p>
    </div>
</template>
