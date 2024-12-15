<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ImageIcon } from 'lucide-vue-next';

const products = ref({
    data: [],
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
    links: [],
});

const getProductImageUrl = (images) => {
    if (images && images.length > 0) {
        return `/storage/${images[0].image_path}`;
    }
    return null;
};

const navigateToCreateProduct = () => {
    router.visit(route('products.create'));
};

const navigateToEditProduct = (productId) => {
    router.visit(route('products.edit', { idOrSlug: productId }));
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

const goToPage = (url) => {
    axios.get(url)
        .then((response) => {
            products.value = response.data;
        })
        .catch((error) => {
            console.error("Error loading products:", error);
        });
};

// Fetch products on component load
axios.get('/products')
    .then((response) => {
        products.value = response.data;
    })
    .catch((error) => {
        console.error("Error loading products:", error);
    });
</script>

<template>
    <Head title="Products" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Products</h2>
                <button
                    @click="navigateToCreateProduct"
                    class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg transition-colors duration-200"
                >
                    Add Product
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div v-if="products.data.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div
                                v-for="product in products.data"
                                :key="product.id"
                                class="product-card border rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-200"
                            >
                                <div class="relative w-full h-48">
                                    <img
                                        v-if="getProductImageUrl(product.images)"
                                        :src="getProductImageUrl(product.images)"
                                        :alt="product.name"
                                        class="w-full h-full object-cover"
                                    />
                                    <div 
                                        v-else 
                                        class="w-full h-full bg-gray-100 flex items-center justify-center"
                                    >
                                        <ImageIcon class="w-12 h-12 text-gray-400" />
                                    </div>
                                </div>
                                
                                <div class="p-4">
                                    <h2 class="text-lg font-semibold text-gray-800 mb-2">{{ product.name }}</h2>
                                    <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ product.description }}</p>
                                    <p class="text-blue-600 font-bold text-lg mb-4">₹{{ product.price }}</p>
                                    
                                    <div class="flex gap-2">
                                        <button
                                            @click="navigateToEditProduct(product.id)"
                                            class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md transition-colors duration-200"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="deleteProduct(product.id)"
                                            class="flex-1 bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-md transition-colors duration-200"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div v-if="products.links && products.links.length" class="py-4">
    <div class="flex justify-center space-x-2">
        <!-- Previous Button -->
        <button
            v-if="products.links[0]?.url"
            @click="goToPage(products.links[0].url)"
            class="px-4 py-2 bg-gray-300 text-black rounded-md hover:bg-gray-400"
        >
            &laquo; Previous
        </button>

        <!-- Page Number Buttons -->
        <button
            v-for="link in products.links"
            :key="link.label"
            v-if="link && link.url !== null"
            @click="goToPage(link.url)"
            :class="{
                'px-4 py-2 bg-blue-500 text-white rounded-md': link.active,
                'px-4 py-2 bg-gray-300 text-black rounded-md': !link.active
            }"
        >
            {{ link.label }}
        </button>

        <!-- Next Button -->
        <button
            v-if="products.links[products.links.length - 1]?.url"
            @click="goToPage(products.links[products.links.length - 1].url)"
            class="px-4 py-2 bg-gray-300 text-black rounded-md hover:bg-gray-400"
        >
            Next &raquo;
        </button>
    </div>
</div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>


<style scoped>
.product-card {
    display: flex;
    flex-direction: column;
    background: white;
}

.product-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
