<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { XCircle } from 'lucide-vue-next';
import "./css/edit.css";
const props = defineProps({
    product: Object
});

const form = useForm({
    id: props.product?.id,
    name: props.product?.name,
    description: props.product?.description,
    price: props.product?.price,
    color: props.product?.color || '',
    brand: props.product?.brand || '',
    stock: props.product?.stock || 0,
    size: props.product?.size || '',
    tags: Array.isArray(props.product?.tags) 
        ? props.product?.tags.join(', ') 
        : (props.product?.tags ? JSON.parse(props.product?.tags).join(', ') : ''),
    images: []
});

const previewImages = ref([]);

const handleImageChange = (event) => {
    const files = event.target.files;
    form.images = Array.from(files);

    previewImages.value = [];
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImages.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    }
};

const removeImage = (index) => {
    previewImages.value.splice(index, 1);
    const newFiles = Array.from(form.images);
    newFiles.splice(index, 1);
    form.images = newFiles;
};

const handleSubmit = () => {
    if (Array.isArray(form.tags)) {
        form.tags = form.tags.join(', ') || '';
    } else {
        form.tags = form.tags || '';
    }

    form.put(route('products.update', form.id), {
        onStart: () => {
            form.processing = true;
            previewImages.value = []; 
        },
        onFinish: () => {
            form.processing = false;
        },
        onSuccess: () => {
            previewImages.value = [];
            alert('Product updated successfully!');
        },
        onError: () => {
            console.error(form.errors);
            alert('An error occurred while updating the product. Please try again.');
        },
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 py-8">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <!-- Header -->
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-700">
                        <h1 class="text-2xl font-bold text-white">
                            {{ form.id ? 'Edit Product' : 'Add Product' }}
                        </h1>
                    </div>

                    <!-- Form Content -->
                    <form @submit.prevent="handleSubmit" class="p-6">
                        <!-- Error Messages -->
                        <div v-if="Object.keys(form.errors).length > 0" 
                             class="mb-6 bg-red-50 border-l-4 border-red-500 p-4">
                            <div v-for="(error, key) in form.errors" 
                                 :key="key"
                                 class="text-red-700 text-sm">
                                {{ error }}
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Left Column -->
                            <div class="space-y-6">
                                <div class="form-group">
                                    <label class="form-label">Product Name</label>
                                    <input 
                                        v-model="form.name"
                                        type="text"
                                        class="form-input"
                                        placeholder="Enter product name"
                                        required
                                    />
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Description</label>
                                    <textarea 
                                        v-model="form.description"
                                        rows="4"
                                        class="form-input"
                                        placeholder="Enter product description"
                                        required
                                    ></textarea>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="form-group">
                                        <label class="form-label">Price</label>
                                        <input 
                                            v-model="form.price"
                                            type="number"
                                            step="0.01"
                                            class="form-input"
                                            placeholder="0.00"
                                            required
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Stock</label>
                                        <input 
                                            v-model="form.stock"
                                            type="number"
                                            class="form-input"
                                            placeholder="0"
                                            required
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="space-y-6">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="form-group">
                                        <label class="form-label">Color</label>
                                        <input 
                                            v-model="form.color"
                                            type="text"
                                            class="form-input"
                                            placeholder="Product color"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Size</label>
                                        <input 
                                            v-model="form.size"
                                            type="text"
                                            class="form-input"
                                            placeholder="Product size"
                                        />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Brand</label>
                                    <input 
                                        v-model="form.brand"
                                        type="text"
                                        class="form-input"
                                        placeholder="Product brand"
                                    />
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Tags</label>
                                    <input 
                                        v-model="form.tags"
                                        type="text"
                                        class="form-input"
                                        placeholder="Enter tags (comma-separated)"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Image Upload Section -->
                        <div class="mt-6">
                            <label class="form-label">Product Images</label>
                            <div class="mt-2">
                                <div class="flex items-center justify-center w-full">
                                    <label class="flex flex-col w-full h-32 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                        <div class="flex flex-col items-center justify-center pt-7">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <p class="pt-1 text-sm text-gray-600">Drag & drop images or click to select</p>
                                        </div>
                                        <input 
                                            type="file" 
                                            class="opacity-0" 
                                            multiple 
                                            @change="handleImageChange"
                                            accept="image/*"
                                        />
                                    </label>
                                </div>
                            </div>

                            <!-- Image Previews -->
                            <div v-if="previewImages.length" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                                <div v-for="(image, index) in previewImages" 
                                     :key="index" 
                                     class="relative group">
                                    <img 
                                        :src="image" 
                                        class="w-full h-32 object-cover rounded-lg"
                                        alt="Preview" 
                                    />
                                    <button 
                                        @click.prevent="removeImage(index)"
                                        class="absolute top-2 right-2 p-1 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
                                    >
                                        <XCircle class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-8 flex justify-end space-x-4 border-t pt-6">
                            <button 
                                type="button" 
                                @click="$inertia.visit(route('products.index'))"
                                class="btn-secondary"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit" 
                                class="btn-primary"
                                :disabled="form.processing"
                            >
                                {{ form.id ? 'Update Product' : 'Create Product' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>