<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';


defineProps({
    products: {
        type: Array,
        default: () => [],
    },
});

const navigateToCreateProduct = () => {
    router.visit(route('products.create'));};

const navigateToEditProduct = (productId) => {
    router.push({ name: 'products.edit', params: { idOrSlug: productId } });
};
</script>

<template>
    <Head title="Products" />

    <AuthenticatedLayout>
        <!-- Header -->
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Products</h2>
            <div class="add-product">
                <button
                    @click="navigateToCreateProduct"
                    class="mt-4 bg-green-500 text-white py-2 px-4 rounded"
                >
                    Add Product
                </button>
            </div>
        </template>

        <!-- Main content -->
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Product grid -->
                        <div v-if="products.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="product in products" :key="product.id" class="product-card border rounded-lg p-4 shadow-md">
                                <img :src="product.image_url" alt="Product Image" class="w-full h-48 object-cover rounded-md mb-4" />
                                <h2 class="text-lg font-semibold">{{ product.name }}</h2>
                                <p class="text-gray-600 mb-2">{{ product.description }}</p>
                                <p class="text-blue-600 font-bold">₹{{ product.price }}</p>
                                <button
                                    @click="navigateToEditProduct(product.id)"
                                    class="mt-2 bg-blue-500 text-white py-1 px-2 rounded"
                                >
                                    Edit
                                </button>
                            </div>
                        </div>
                        <div v-else>
                            <p>No products available.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.product-card img {
    max-height: 200px;
}
.add-product {
    display: flex;
    justify-content: flex-end;
}
</style>
