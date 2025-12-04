<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import OptimizationModal from '@/components/OptimizationModal.vue';

interface CartItem {
    id: number;
    product_name: string;
    product_sku: string;
    supplier_name: string;
    variant_id: number;
    price: number;
    shipping_cost: number;
    quantity: number;
    is_selected: boolean;
    availability_status: string;
    estimated_delivery_date: string | null;
    estimated_delivery_days: number | null;
    image_url: string | null;
    subtotal: number;
    shipping_total: number;
    total: number;
}

interface SupplierGroup {
    supplier: string;
    items: CartItem[];
    estimated_total: number;
    estimated_shipping: number;
}

interface Props {
    cartItems: SupplierGroup[];
    summary: {
        subtotal: number;
        shipping: number;
        tax: number;
        total: number;
        item_count: number;
    };
}

const props = defineProps<Props>();

const showOptimizationModal = ref(false);
const optimizationData = ref<any>(null);
const loading = ref(false);

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};

const getAvailabilityBadgeClass = (status: string): string => {
    switch (status) {
        case 'in_stock':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
        case 'backordered':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200';
    }
};

const getAvailabilityLabel = (status: string): string => {
    switch (status) {
        case 'in_stock':
            return 'In Stock';
        case 'backordered':
            return 'Backordered';
        default:
            return 'Out of Stock';
    }
};

const updateQuantity = (item: CartItem, newQuantity: number) => {
    if (newQuantity < 1) {
        return;
    }

    router.patch(`/cart/items/${item.id}/quantity`, {
        quantity: newQuantity,
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['cartItems', 'summary'],
    });
};

const toggleSelection = (item: CartItem) => {
    router.patch(`/cart/items/${item.id}/toggle-selection`, {}, {
        preserveState: true,
        preserveScroll: true,
        only: ['cartItems', 'summary'],
    });
};

const removeItem = (item: CartItem) => {
    if (!confirm('Are you sure you want to remove this item from your cart?')) {
        return;
    }

    router.delete(`/cart/items/${item.id}`, {
        preserveState: true,
        preserveScroll: true,
    });
};

const optimizeCart = async () => {
    loading.value = true;
    try {
        const response = await fetch('/api/optimize/cart');
        if (!response.ok) {
            throw new Error('Failed to fetch optimizations');
        }
        const data = await response.json();
        console.log('Optimization data received:', data);
        optimizationData.value = data;
        if (data.optimizations && data.optimizations.length > 0) {
            showOptimizationModal.value = true;
        } else {
            alert('No optimization opportunities found at this time.');
        }
    } catch (error) {
        console.error('Optimization error:', error);
        alert('Failed to optimize cart. Please try again.');
    } finally {
        loading.value = false;
    }
};

const onOptimizationApplied = () => {
    showOptimizationModal.value = false;
    router.reload();
};

const selectedItemsCount = computed(() => {
    return props.cartItems.reduce((count, group) => {
        return count + group.items.filter(item => item.is_selected).length;
    }, 0);
});

const deselectAll = () => {
    const selectedItemIds = props.cartItems
        .flatMap(group => group.items)
        .filter(item => item.is_selected)
        .map(item => item.id);

    if (selectedItemIds.length === 0) {
        return;
    }

    router.post('/cart/deselect-all', {
        item_ids: selectedItemIds,
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['cartItems', 'summary'],
    });
};

const removeSelected = () => {
    const selectedItemIds = props.cartItems
        .flatMap(group => group.items)
        .filter(item => item.is_selected)
        .map(item => item.id);

    if (selectedItemIds.length === 0) {
        return;
    }

    if (!confirm(`Are you sure you want to remove ${selectedItemIds.length} item(s) from your cart?`)) {
        return;
    }

    router.post('/cart/remove-selected', {
        item_ids: selectedItemIds,
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['cartItems', 'summary'],
    });
};

const saveForLater = () => {
    const selectedItemIds = props.cartItems
        .flatMap(group => group.items)
        .filter(item => item.is_selected)
        .map(item => item.id);

    if (selectedItemIds.length === 0) {
        return;
    }

    router.post('/cart/save-for-later', {
        item_ids: selectedItemIds,
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['cartItems', 'summary'],
    });
};
</script>

<template>
    <Head title="Shopping Cart">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Header -->
        <header class="bg-white dark:bg-gray-800 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <Link href="/" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                        ← Continue shopping
                    </Link>
                    <div class="text-right">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Company</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Ship To: 567 Texas Avenue, 77204</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Payment Info: VISA 5556</p>
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Main Cart Content -->
                <div class="flex-1">
                    <!-- Cart Header -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                                Shopping Cart {{ summary.item_count }} items
                            </h2>
                            <input
                                type="text"
                                placeholder="Search in the cart"
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white"
                            />
                        </div>
                        <div class="flex gap-4">
                            <button
                                @click="deselectAll"
                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400"
                            >
                                Deselect All
                            </button>
                            <button
                                @click="saveForLater"
                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400"
                            >
                                Save For Later ({{ selectedItemsCount }})
                            </button>
                            <button
                                @click="removeSelected"
                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400"
                            >
                                Remove ({{ selectedItemsCount }})
                            </button>
                        </div>
                    </div>

                    <!-- Supplier Groups -->
                    <div class="space-y-6">
                        <div
                            v-for="group in cartItems"
                            :key="group.supplier"
                            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden"
                        >
                            <!-- Supplier Header -->
                            <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ group.supplier }}</h3>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            Est. Total {{ formatCurrency(group.estimated_total) }}
                                        </p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            Est. Shipping: {{ group.estimated_shipping === 0 ? 'Free' : formatCurrency(group.estimated_shipping) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Items -->
                            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                                <div
                                    v-for="item in group.items"
                                    :key="item.id"
                                    data-cart-item
                                    class="p-6"
                                    style="contain: layout style paint;"
                                >
                                    <div class="flex gap-4">
                                        <!-- Checkbox -->
                                        <input
                                            type="checkbox"
                                            :checked="item.is_selected"
                                            @change="toggleSelection(item)"
                                            class="mt-1 h-4 w-4 text-blue-600 rounded"
                                        />

                                        <!-- Product Image -->
                                        <div class="w-24 h-24 bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center flex-shrink-0">
                                            <img
                                                v-if="item.image_url"
                                                :src="item.image_url"
                                                :alt="item.product_name"
                                                class="w-full h-full object-cover rounded"
                                            />
                                            <span v-else class="text-gray-400 text-xs">No Image</span>
                                        </div>

                                        <!-- Product Details -->
                                        <div class="flex-1">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <h4 class="font-medium text-gray-900 dark:text-white mb-1">
                                                        {{ item.product_name }}
                                                    </h4>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                                        Product ID: {{ item.product_sku }}
                                                    </p>
                                                    <div class="flex items-center gap-4 text-sm">
                                                        <span
                                                            :class="getAvailabilityBadgeClass(item.availability_status)"
                                                            class="px-2 py-1 rounded text-xs font-medium"
                                                        >
                                                            {{ getAvailabilityLabel(item.availability_status) }}
                                                        </span>
                                                        <span class="text-gray-600 dark:text-gray-400">
                                                            Unit Price: {{ formatCurrency(item.price) }}
                                                        </span>
                                                    </div>
                                                    <p
                                                        v-if="item.estimated_delivery_date"
                                                        class="text-sm text-gray-600 dark:text-gray-400 mt-2"
                                                    >
                                                        Delivery: {{ item.estimated_delivery_date }}
                                                    </p>
                                                </div>

                                                <div class="text-right ml-4">
                                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                                        {{ formatCurrency(item.total) }}
                                                    </p>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                                        {{ formatCurrency(item.price) }} × {{ item.quantity }}
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- Actions -->
                                            <div class="flex items-center justify-between mt-4">
                                                <div class="flex items-center gap-2">
                                                    <button
                                                        @click="updateQuantity(item, item.quantity - 1)"
                                                        class="p-1 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200"
                                                    >
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                        </svg>
                                                    </button>
                                                    <span class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 dark:text-white">
                                                        {{ item.quantity }}
                                                    </span>
                                                    <button
                                                        @click="updateQuantity(item, item.quantity + 1)"
                                                        class="p-1 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200"
                                                    >
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                        </svg>
                                                    </button>
                                                </div>

                                                <div class="flex gap-4 text-sm">
                                                    <button
                                                        @click="removeItem(item)"
                                                        class="text-red-600 hover:text-red-800 dark:text-red-400"
                                                    >
                                                        Remove
                                                    </button>
                                                    <button class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                                        Save for Later
                                                    </button>
                                                    <button class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                                        Add a Note
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary Sidebar -->
                <div class="w-full lg:w-80">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 sticky top-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Order Summary</h3>
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                                <span class="text-gray-900 dark:text-white">{{ formatCurrency(summary.subtotal) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Handling/Fuel:</span>
                                <span class="text-gray-900 dark:text-white">$0.00</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Estimated Shipping:</span>
                                <span class="text-gray-900 dark:text-white">{{ formatCurrency(summary.shipping) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Estimated Sales Tax:</span>
                                <span class="text-gray-900 dark:text-white">{{ formatCurrency(summary.tax) }}</span>
                            </div>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mb-4">
                            <div class="flex justify-between">
                                <span class="text-lg font-bold text-gray-900 dark:text-white">ORDER TOTAL</span>
                                <span class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(summary.total) }}</span>
                            </div>
                        </div>
                        <button
                            v-if="optimizationData && optimizationData.total_savings_with_shipping > 0"
                            class="w-full mb-4 px-4 py-2 border-2 border-orange-500 text-orange-600 rounded-md hover:bg-orange-50 dark:hover:bg-orange-900/20 font-medium"
                        >
                            Save with us {{ formatCurrency(optimizationData.total_savings_with_shipping) }}
                        </button>
                        <button
                            @click="optimizeCart"
                            :disabled="loading"
                            class="w-full mb-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium"
                        >
                            {{ loading ? 'Optimizing...' : 'Optimize Cart' }}
                        </button>
                        <button class="w-full px-4 py-3 bg-yellow-400 text-gray-900 rounded-md hover:bg-yellow-500 font-semibold">
                            Proceed To Checkout
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Optimization Modal -->
        <OptimizationModal
            v-if="showOptimizationModal"
            :optimization-data="optimizationData"
            @close="showOptimizationModal = false"
            @applied="onOptimizationApplied"
        />
    </div>
</template>

