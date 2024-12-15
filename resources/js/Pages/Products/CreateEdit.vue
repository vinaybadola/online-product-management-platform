<script setup>
import { ref, reactive, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ImageUploader from './ImageUploader.vue'; // Update the path if necessary

// Initialize the form using useForm for Inertia
const form = reactive({
    id: null, // Optional, for editing
    name: '',
    description: '',
    price: '',
    color: '',
    brand: '',
    stock: '',
    size: '',
    tags: '',
    images: [], // Placeholder for image files
});

// Ref for selected images from the ImageUploader
const selectedImages = ref([]);

// Handle form submission
const handleSubmit = () => {
    const formData = new FormData();

    // Append form fields to FormData
    for (const key in form) {
        if (key === 'images') {
            // Append each image file individually
            selectedImages.value.forEach((file) => {
                formData.append('images[]', file);
            });
        } else {
            formData.append(key, form[key]);
        }
    }

    // Make the POST or PUT request
    const url = form.id
        ? route('products.update', { id: form.id })
        : route('products.store');

    axios.post(url, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
    })
    .then(() => {
        alert('Product successfully saved!');
        window.location = route('products.index'); // Redirect to product listing
    })
    .catch((error) => {
        console.error('Error saving product:', error.response.data.errors);
    });
};
</script>

<template>
    <div>
        <h1 class="text-xl font-bold mb-4">
            {{ form.id ? 'Edit Product' : 'Add Product' }}
        </h1>
        <form @submit.prevent="handleSubmit" enctype="multipart/form-data">
            <div v-if="form.errors" class="text-red-500 mb-4">
                <ul>
                    <li v-for="(error, field) in form.errors" :key="field">
                        {{ error }}
                    </li>
                </ul>
            </div>

            <input v-model="form.name" name="name" placeholder="Name" class="block mb-4" />
            <textarea v-model="form.description" name="description" placeholder="Description" class="block mb-4"></textarea>
            <input v-model="form.price" name="price" type="number" placeholder="Price" class="block mb-4" />
            <input v-model="form.color" name="color" placeholder="Color" class="block mb-4" />
            <input v-model="form.brand" name="brand" placeholder="Brand" class="block mb-4" />
            <input v-model="form.stock" name="stock" type="number" placeholder="Stock" class="block mb-4" />
            <input v-model="form.size" name="size" placeholder="Size" class="block mb-4" />
            <textarea v-model="form.tags" name="tags" placeholder="Tags (comma-separated)" class="block mb-4"></textarea>

            <!-- Image uploader component -->
            <ImageUploader v-model:images="selectedImages" />

            <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded">
                {{ form.id ? 'Update Product' : 'Create Product' }}
            </button>
        </form>
    </div>
</template>
