<script setup>
import { useForm } from '@inertiajs/vue3';

defineProps({
    product: {
        type: Object,
        default: () => ({
            name: '',
            description: '',
            price: null,
            slug: '',
            color: '',
            brand: '',
            stock: 0,
            size: '',
            tags: [],
        }),
    },
});

const form = useForm({ ...product });

const submit = () => {
    if (form.id) {
        form.put(route('products.update', form.id)); // Editing the  existing product
    } else {
        form.post(route('products.store')); // Create new product
    }
};
</script>

<template>
    <div>
        <h1 class="text-xl font-bold mb-4">
            {{ form.id ? 'Edit Product' : 'Add Product' }}
        </h1>
        <form @submit.prevent="submit">
            <input v-model="form.name" placeholder="Name" class="block mb-4" />
            <textarea v-model="form.description" placeholder="Description" class="block mb-4"></textarea>
            <input v-model="form.price" type="number" placeholder="Price" class="block mb-4" />
            <input v-model="form.color" placeholder="Color" class="block mb-4" />
            <input v-model="form.brand" placeholder="Brand" class="block mb-4" />
            <input v-model="form.stock" type="number" placeholder="Stock" class="block mb-4" />
            <input v-model="form.size" placeholder="Size" class="block mb-4" />
            <textarea v-model="form.tags" placeholder="Tags (comma-separated)" class="block mb-4"></textarea>
            <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded">
                {{ form.id ? 'Update Product' : 'Create Product' }}
            </button>
        </form>
    </div>
</template>
