<script setup>
import { useForm } from '@inertiajs/vue3';
import ImageUploader from './ImageUploader.vue';
import './css/ProductForm.css';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const form = useForm({
    id: null,
    name: '',
    description: '',
    price: null,
    color: '',
    brand: '',
    stock: 0,
    size: '',
    tags: [],
    images: []
});

const handleSubmit = () => {
    if (form.id) {
        form.put(route('products.update', form.id));
    } else {
        form.post(route('products.store'));
    }
};
</script>

<template>
    <AuthenticatedLayout>
    <div class="product-form-container">
        <div class="form-card">
            <div class="form-header">
                <h1>{{ form.id ? 'Edit Product' : 'Add Product' }}</h1>
            </div>

            <form @submit.prevent="handleSubmit" class="form-content">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input 
                            id="name"
                            v-model="form.name" 
                            type="text"
                            placeholder="Enter product name"
                            required
                        />
                        <span v-if="form.errors.name" class="error">{{ form.errors.name }}</span>
                    </div>

                    <div class="form-group full-width">
                        <label for="description">Description</label>
                        <textarea 
                            id="description"
                            v-model="form.description" 
                            placeholder="Enter product description"
                            rows="4"
                            required
                        ></textarea>
                        <span v-if="form.errors.description" class="error">{{ form.errors.description }}</span>
                    </div>

                    <div class="form-group">
                        <label for="price">Price</label>
                        <input 
                            id="price"
                            v-model="form.price" 
                            type="number" 
                            step="0.01"
                            placeholder="0.00"
                            required
                        />
                        <span v-if="form.errors.price" class="error">{{ form.errors.price }}</span>
                    </div>

                    <div class="form-group">
                        <label for="stock">Stock</label>
                        <input 
                            id="stock"
                            v-model="form.stock" 
                            type="number"
                            placeholder="0"
                            required
                        />
                        <span v-if="form.errors.stock" class="error">{{ form.errors.stock }}</span>
                    </div>

                    <div class="form-group">
                        <label for="color">Color</label>
                        <input 
                            id="color"
                            v-model="form.color" 
                            type="text"
                            placeholder="Product color"
                        />
                    </div>

                    <div class="form-group">
                        <label for="size">Size</label>
                        <input 
                            id="size"
                            v-model="form.size" 
                            type="text"
                            placeholder="Product size"
                        />
                    </div>

                    <div class="form-group">
                        <label for="brand">Brand</label>
                        <input 
                            id="brand"
                            v-model="form.brand" 
                            type="text"
                            placeholder="Product brand"
                        />
                    </div>

                    <div class="form-group full-width">
                        <label for="tags">Tags</label>
                        <input 
                            id="tags"
                            v-model="form.tags" 
                            type="text"
                            placeholder="Enter tags (comma-separated)"
                        />
                    </div>

                    <div class="form-group full-width">
                        <label>Product Images</label>
                        <ImageUploader v-model:images="form.images" />
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" @click="$inertia.visit(route('products.index'))">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary" :disabled="form.processing">
                        {{ form.id ? 'Update Product' : 'Create Product' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</AuthenticatedLayout>
</template>