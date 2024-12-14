<script setup>
defineProps({
    product: Object,
    submitLabel: String,
});
</script>

<template>
    <form @submit.prevent="$emit('submit', product)">
        <input v-model="product.name" placeholder="Name" />
        <textarea v-model="product.description" placeholder="Description"></textarea>
        <select v-model="product.category">
            <option value="electronics">Electronics</option>
            <option value="fashion">Fashion</option>
        </select>
        <input v-model="product.price" type="number" placeholder="Price" />
        <button type="submit">{{ submitLabel }}</button>
    </form>
</template>