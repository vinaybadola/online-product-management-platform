<template>
  <AuthenticatedLayout>
    <div class="min-h-screen bg-gray-50 py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center min-h-[400px]">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-red-50 p-4 rounded-md">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">{{ error }}</h3>
            </div>
          </div>
        </div>

        <!-- Product Details -->
        <div v-else class="bg-white shadow-xl rounded-lg overflow-hidden">
          <div class="md:flex">
            <!-- Product Image Section -->
            <div class="md:flex-shrink-0 md:w-1/2">
              <div class="relative h-96 w-full">
                <img 
                  v-if="productImage"
                  :src="productImage" 
                  :alt="product?.name"
                  @error="handleImageError"
                  class="w-full h-full object-cover"
                />
                <img 
                  v-else 
                  src="/placeholder.jpg" 
                  alt="No Image Available"
                  class="w-full h-full object-cover bg-gray-100"
                />
              </div>
            </div>

            <!-- Product Info Section -->
            <div class="p-8 md:w-1/2">
              <div class="uppercase tracking-wide text-sm text-blue-500 font-semibold">
                {{ product?.brand ?? "Brand Not Available" }}
              </div>
              <h1 class="mt-2 text-3xl font-bold text-gray-900">
                {{ product?.name ?? "Product Name Not Available" }}
              </h1>
              
              <div class="mt-4 flex items-center">
                <span class="text-3xl font-bold text-gray-900">₹{{ product?.price ?? "N/A" }}</span>
                <span class="ml-2 text-sm text-gray-500">
                  {{ product?.stock > 0 ? `${product.stock} in stock` : 'Out of stock' }}
                </span>
              </div>

              <div class="mt-6 space-y-6">
                <div class="text-base text-gray-700">
                  {{ product?.description ?? "No description available." }}
                </div>

                <div class="border-t border-gray-200 pt-6">
                  <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                      <dt class="text-sm font-medium text-gray-500">Color</dt>
                      <dd class="mt-1 text-sm text-gray-900">{{ product?.color ?? "Not available" }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                      <dt class="text-sm font-medium text-gray-500">Size</dt>
                      <dd class="mt-1 text-sm text-gray-900">{{ product?.size ?? "Not available" }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                      <dt class="text-sm font-medium text-gray-500">Tags</dt>
                      <dd class="mt-1 text-sm text-gray-900">
                        <div class="flex flex-wrap gap-2">
                          <span 
                            v-for="tag in (product?.tags?.split(',') || [])" 
                            :key="tag"
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                          >
                            {{ tag.trim() }}
                          </span>
                          <span v-if="!product?.tags" class="text-gray-500">No tags available</span>
                        </div>
                      </dd>
                    </div>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';

const product = ref(null);
const productImage = ref(null);
const loading = ref(true);
const error = ref(null);

const fetchProduct = async () => {
  try {
    const { idOrSlug } = usePage().props.params;
    const response = await axios.get(`/products/${idOrSlug}`);
    const productData = response.data;
    product.value = productData;
    productImage.value = getProductImageUrl(productData.images);
  } catch (err) {
    console.error("Error fetching product:", err);
    error.value = err.response?.data?.message || "An error occurred while fetching the product.";
  } finally {
    loading.value = false;
  }
};

const getProductImageUrl = (images) => {
  if (images && images.length > 0) {
    return `/storage/${images[0].image_path}`;
  }
  return null;
};

const handleImageError = (event) => {
  event.target.src = "/placeholder.jpg";
};

onMounted(fetchProduct);
</script>
