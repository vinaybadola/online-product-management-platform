<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

// Props
defineProps({
    products: {
        type: Array,
        default: () => [],
    },
});

const getProductImageUrl = (images) => {
    if (images && images.length > 0) {
        return `/storage/${images[0].image_path}`;
    }
    return '/placeholder-image.jpg';
};

const navigateToCreateProduct = () => {
    router.visit(route('products.create'));
};

const navigateToEditProduct = (productId) => {
    router.visit(route('products.edit', { id: productId }));
};

const deleteProduct = (productId) => {
    if (confirm('Are you sure you want to delete this product?')) {
        axios.delete(route('products.destroy', productId))
            .then(() => {
                alert('Product deleted successfully');
                window.location.reload();
            })
            .catch((error) => {
                alert('Failed to delete the product', error);
                console.error(error);
            });
    }
};
</script>

<template>
    <Head title="Products" />

    <AuthenticatedLayout>
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

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div v-if="products.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div
                                v-for="product in products"
                                :key="product.id"
                                class="product-card border rounded-lg p-4 shadow-md"
                            >
                                <img
                                    :src="getProductImageUrl(product.images)"
                                    alt="Product Image"
                                    class="w-full h-48 object-cover rounded-md mb-4"
                                />
                                <h2 class="text-lg font-semibold">{{ product.name }}</h2>
                                <p class="text-gray-600 mb-2">{{ product.description }}</p>
                                <p class="text-blue-600 font-bold">₹{{ product.price }}</p>
                                <button
                                    @click="navigateToEditProduct(product.id)"
                                    class="mt-2 bg-blue-500 text-white py-1 px-2 rounded"
                                >
                                    Edit
                                </button>
                                <button
                                    @click="deleteProduct(product.id)"
                                    class="mt-5 mx-4 bg-blue-500 text-white py-1 px-2 rounded"
                                >
                                    Delete
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
