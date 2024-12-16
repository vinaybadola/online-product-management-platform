<script setup>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {router } from '@inertiajs/vue3';
import { ImageIcon } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

const products = ref({
    data: [],
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
    links: [],
});

const filters = ref({
    colors: [],
    brands: [],
    sizes: [],
    tags: [],
});

const selectedColors = ref([]);
const selectedBrand = ref('');
const selectedSize = ref('');
const selectedTags = ref([]);
const minPrice = ref(null);
const maxPrice = ref(null);

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
                alert('Failed to delete the product');
                console.error(error);
            });
    }
};

const fetchProducts = (filters = {}) => {
    axios.get(route('products.index'), { params: filters })
        .then((response) => {
            products.value = response.data;
        })
        .catch((error) => {
            console.error("Error loading products:", error);
        });
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


const fetchFilters = () => {
    axios.get(route('products.index')) 
        .then((response) => {
            const allProducts = response.data.data;

            filters.value.colors = [...new Set(allProducts.map(product => product.color).filter(Boolean))];
            filters.value.brands = [...new Set(allProducts.map(product => product.brand).filter(Boolean))];
            filters.value.sizes = [...new Set(allProducts.map(product => product.size).filter(Boolean))];
            filters.value.tags = [
                ...new Set(
                    allProducts.flatMap(product =>
                        product.tags ? JSON.parse(product.tags) : []
                    ).filter(Boolean)
                )
            ];
        })
        .catch((error) => {
            console.error("Error loading filters:", error);
        });
};

const applyFilters = () => {
    const appliedFilters = {
        color: selectedColors.value,
        brand: selectedBrand.value,
        size: selectedSize.value,
        tags: selectedTags.value,
        min_price: minPrice.value,
        max_price: maxPrice.value,
    };

    fetchProducts(appliedFilters);
};

const isMobileFiltersOpen = ref(false);
const activeFilters = ref(new Set());

// Add function to track active filters
const updateActiveFilters = () => {
    activeFilters.value.clear();
    if (selectedColors.value.length) activeFilters.value.add('colors');
    if (selectedBrand.value) activeFilters.value.add('brand');
    if (selectedSize.value) activeFilters.value.add('size');
    if (selectedTags.value.length) activeFilters.value.add('tags');
    if (minPrice.value || maxPrice.value) activeFilters.value.add('price');
};

// Add reset filters function
const resetFilters = () => {
    selectedColors.value = [];
    selectedBrand.value = '';
    selectedSize.value = '';
    selectedTags.value = [];
    minPrice.value = null;
    maxPrice.value = null;
    activeFilters.value.clear();
    applyFilters();
};

onMounted(() => {
    fetchProducts();
    fetchFilters();
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Products</h2>
                <button @click="navigateToCreateProduct"
                    class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg transition-colors duration-200">
                    Add Product
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col lg:flex-row gap-6">
                    <div class="lg:w-64 flex-shrink-0">
                        <!-- Mobile filter dialog -->
                        <div class="lg:hidden flex items-center justify-between mb-4">
                            <button @click="isMobileFiltersOpen = !isMobileFiltersOpen"
                                class="flex items-center text-gray-700 hover:text-gray-900">
                                <Filter class="h-5 w-5 mr-2" />
                                Filters
                                <span v-if="activeFilters.size" 
                                    class="ml-2 bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                    {{ activeFilters.size }}
                                </span>
                            </button>
                            <button v-if="activeFilters.size" 
                                @click="resetFilters"
                                class="text-sm text-gray-500 hover:text-gray-700">
                                Reset all
                            </button>
                        </div>

                        <div :class="[
                            'lg:block',
                            'bg-white rounded-lg shadow-sm border border-gray-200',
                            { 'hidden': !isMobileFiltersOpen }
                        ]">
                            <div class="p-4">
                                <div class="flex items-center justify-between mb-4">
                                    <h2 class="text-lg font-medium text-gray-900">Filters</h2>
                                    <button v-if="activeFilters.size" 
                                        @click="resetFilters"
                                        class="text-sm text-gray-500 hover:text-gray-700">
                                        Reset all
                                    </button>
                                </div>

                                <!-- Color Filter -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Colors</label>
                                    <div class="space-y-2">
                                        <label v-for="color in filters.colors" :key="color" 
                                            class="flex items-center">
                                            <input type="checkbox" 
                                                :value="color"
                                                v-model="selectedColors"
                                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-gray-600">{{ color }}</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Brand Filter -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Brand</label>
                                    <select v-model="selectedBrand" 
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                        <option value="">All Brands</option>
                                        <option v-for="brand in filters.brands" :key="brand" :value="brand">
                                            {{ brand }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Size Filter -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Size</label>
                                    <select v-model="selectedSize"
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                        <option value="">All Sizes</option>
                                        <option v-for="size in filters.sizes" :key="size" :value="size">
                                            {{ size }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Price Range Filter -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Price Range</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <input type="number" 
                                                v-model="minPrice"
                                                placeholder="Min"
                                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        </div>
                                        <div>
                                            <input type="number" 
                                                v-model="maxPrice"
                                                placeholder="Max"
                                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        </div>
                                    </div>
                                </div>

                                <!-- Tags Filter -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
                                    <div class="space-y-2">
                                        <label v-for="tag in filters.tags" :key="tag" 
                                            class="flex items-center">
                                            <input type="checkbox" 
                                                :value="tag"
                                                v-model="selectedTags"
                                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <span class="ml-2 text-gray-600">{{ tag }}</span>
                                        </label>
                                    </div>
                                </div>

                                <button @click="applyFilters" 
                                    class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 text-gray-900">
                                <div v-if="products.data.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <div v-for="product in products.data" :key="product.id"
                                        class="product-card border rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-200">
                                        <div class="relative w-full h-48">
                                            <img v-if="getProductImageUrl(product.images)"
                                                :src="getProductImageUrl(product.images)" :alt="product.name"
                                                class="w-full h-full object-cover" />
                                            <div v-else class="w-full h-full bg-gray-100 flex items-center justify-center">
                                                <ImageIcon class="w-12 h-12 text-gray-400" />
                                            </div>
                                        </div>

                                        <div class="p-4">
                                            <h2 class="text-lg font-semibold text-gray-800 mb-2">{{ product.name }}</h2>
                                            <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ product.description }}</p>
                                            <p class="text-blue-600 font-bold text-lg mb-4">₹{{ product.price }}</p>
                                            <Link :href="`/products/${product.id}`">View Details</Link>


                                            <div class="flex gap-2">
                                                <button @click="navigateToEditProduct(product.id)"
                                                    class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md transition-colors duration-200">
                                                    Edit
                                                </button>
                                                <button @click="deleteProduct(product.id)"
                                                    class="flex-1 bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-md transition-colors duration-200">
                                                    Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center p-4">
                                    <p>Oops ! No products found :(</p>
                                    <p>Please try again later or change the filters accordingly</p>
                                </div>

                                <div v-if="products.links && products.links.length" class="py-4">
                                    <div class="flex justify-center space-x-2">
                                        <!-- Previous Button -->
                                        <button v-if="products.links[0]?.url" @click="goToPage(products.links[0].url)"
                                            class="px-4 py-2 bg-gray-300 text-black rounded-md hover:bg-gray-400">
                                            &laquo; Previous
                                        </button>

                                        <!-- Page Number Buttons -->
                                        <button v-for="link in products.links" :key="link.label"
                                            v-if="link && link.url !== null" @click="goToPage(link.url)" :class="{
                                                'px-4 py-2 bg-blue-500 text-white rounded-md': link.active,
                                                'px-4 py-2 bg-gray-300 text-black rounded-md': !link.active
                                            }">
                                            {{ link.label }}
                                        </button>

                                        <!-- Next Button -->
                                        <button v-if="products.links[products.links.length - 1]?.url"
                                            @click="goToPage(products.links[products.links.length - 1].url)"
                                            class="px-4 py-2 bg-gray-300 text-black rounded-md hover:bg-gray-400">
                                            Next &raquo;
                                        </button>
                                    </div>
                                </div>

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
.flex-shrink-0 {
    flex-shrink: 0;
}
</style>
