<script setup>
import { ref } from 'vue';

const selectedCategory = ref('');
const searchQuery = ref('');

function applyFilters() {
    // Emit selected filters to the parent component
    emit('filter', { category: selectedCategory.value, query: searchQuery.value });
}
</script>

<template>
    <div class="filters">
        <select v-model="selectedCategory" @change="applyFilters">
            <option value="">All Categories</option>
            <option value="electronics">Electronics</option>
            <option value="fashion">Fashion</option>
            <!-- Add more categories here -->
        </select>
        <input
            type="text"
            v-model="searchQuery"
            placeholder="Search products..."
            @input="applyFilters"
        />
    </div>
</template>
