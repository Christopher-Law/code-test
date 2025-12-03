<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';

interface Optimization {
    cart_item_id: number;
    current_variant: {
        id: number;
        product_name: string;
        supplier_name: string;
        price: number;
        shipping_cost: number;
        total: number;
        estimated_delivery_date: string | null;
        availability_status: string;
    };
    recommended_variant: {
        id: number;
        product_name: string;
        supplier_name: string;
        price: number;
        shipping_cost: number;
        total: number;
        estimated_delivery_date: string | null;
        availability_status: string;
    };
    type: string;
    relationship_type: string | null;
    optimization_score: number;
    price_savings: number;
    total_savings: number;
    quantity: number;
}

interface Props {
    optimizationData: {
        optimizations: Optimization[];
        total_savings: number;
        total_savings_with_shipping: number;
    } | null;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    close: [];
    applied: [];
}>();

const activeTab = ref<'same_brand' | 'similar_items' | 'show_all'>('show_all');
const selectedOptimizations = ref<Set<number>>(new Set());

const filteredOptimizationsList = ref<Optimization[]>([]);

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};

const getStatusBadge = (optimization: Optimization): string => {
    if (optimization.type === 'same_product') {
        return 'IN YOUR CART';
    }
    if (optimization.relationship_type === 'same_brand') {
        return 'SAME BRAND';
    }
    return 'SIMILAR ITEM';
};

const getStatusBadgeClass = (optimization: Optimization): string => {
    if (optimization.type === 'same_product') {
        return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
    }
    if (optimization.relationship_type === 'same_brand') {
        return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
    }
    return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200';
};

watch([() => props.optimizationData, activeTab], () => {
    if (!props.optimizationData) {
        filteredOptimizationsList.value = [];
        return;
    }

    const optimizations = props.optimizationData.optimizations;
    
    if (!optimizations || optimizations.length === 0) {
        filteredOptimizationsList.value = [];
        return;
    }

    let filtered: typeof optimizations;
    switch (activeTab.value) {
        case 'same_brand':
            filtered = optimizations.filter(
                (opt) => opt.relationship_type === 'same_brand' || opt.type === 'same_product'
            );
            break;
        case 'similar_items':
            filtered = optimizations.filter((opt) => opt.relationship_type === 'similar_item');
            break;
        case 'show_all':
            filtered = optimizations;
            break;
        default:
            filtered = optimizations;
    }

    filteredOptimizationsList.value = filtered;
}, { immediate: true });

const filteredOptimizations = computed(() => filteredOptimizationsList.value);

const toggleOptimization = (cartItemId: number) => {
    const newSet = new Set(selectedOptimizations.value);
    if (newSet.has(cartItemId)) {
        newSet.delete(cartItemId);
    } else {
        newSet.add(cartItemId);
    }
    selectedOptimizations.value = newSet;
};

const selectAll = () => {
    const newSet = new Set(selectedOptimizations.value);
    filteredOptimizations.value.forEach((opt) => {
        newSet.add(opt.cart_item_id);
    });
    selectedOptimizations.value = newSet;
};

const deselectAll = () => {
    const newSet = new Set(selectedOptimizations.value);
    filteredOptimizations.value.forEach((opt) => {
        newSet.delete(opt.cart_item_id);
    });
    selectedOptimizations.value = newSet;
};

const applyOptimizations = async () => {
    if (selectedOptimizations.value.size === 0) {
        alert('Please select at least one optimization to apply.');
        return;
    }

    for (const cartItemId of selectedOptimizations.value) {
        const optimization = props.optimizationData?.optimizations.find(
            (opt) => opt.cart_item_id === cartItemId
        );

        if (!optimization) {
            continue;
        }

        await router.post(`/api/optimize/cart-items/${cartItemId}/apply`, {
            variant_id: optimization.recommended_variant.id,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    }

    emit('applied');
};

const totalSelectedSavings = computed(() => {
    if (!props.optimizationData) {
        return 0;
    }

    return props.optimizationData.optimizations
        .filter((opt) => selectedOptimizations.value.has(opt.cart_item_id))
        .reduce((sum, opt) => sum + opt.total_savings, 0);
});
</script>

<template>
    <div class="fixed inset-0 z-50 overflow-y-auto" @click.self="emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="emit('close')"></div>

            <!-- Modal panel -->
            <div
                class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full relative z-10"
                @click.stop
            >
                <!-- Header -->
                <div class="bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                            We found {{ optimizationData?.optimizations.length || 0 }} products to save you money!
                        </h3>
                        <button
                            @click="emit('close')"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between px-6 py-2">
                        <nav class="flex -mb-px">
                        <button
                            @click="activeTab = 'same_brand'"
                            :class="[
                                activeTab === 'same_brand'
                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                                'whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm',
                            ]"
                        >
                            Same Brand
                        </button>
                        <button
                            @click="activeTab = 'similar_items'"
                            :class="[
                                activeTab === 'similar_items'
                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                                'whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm',
                            ]"
                        >
                            Similar Items
                        </button>
                        <button
                            @click="activeTab = 'show_all'"
                            :class="[
                                activeTab === 'show_all'
                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                                'whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm',
                            ]"
                        >
                            Show All
                        </button>
                    </nav>
                    <div class="flex gap-4">
                        <button
                            @click="selectAll"
                            class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                            Select All
                        </button>
                        <button
                            @click="deselectAll"
                            class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                            Deselect All
                        </button>
                    </div>
                </div>
                </div>

                <!-- Content -->
                <div class="px-6 py-4 max-h-96 overflow-y-auto bg-white dark:bg-gray-800 min-h-[200px]">
                    <div v-if="!optimizationData" class="text-center py-12">
                        <p class="text-gray-500 dark:text-gray-400">Loading optimizations...</p>
                    </div>
                    <div v-else-if="!optimizationData.optimizations || optimizationData.optimizations.length === 0" class="text-center py-12">
                        <p class="text-gray-500 dark:text-gray-400">No optimization opportunities found.</p>
                    </div>
                    <div v-else-if="filteredOptimizations.length === 0" class="text-center py-12">
                        <p class="text-gray-500 dark:text-gray-400">No options in this category.</p>
                        <p class="text-sm text-gray-400 mt-2">Try switching to another tab.</p>
                    </div>
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div
                            v-for="optimization in filteredOptimizations"
                            :key="`opt-${optimization.cart_item_id}-${optimization.recommended_variant.id}`"
                            :class="[
                                'border-2 rounded-lg p-4 cursor-pointer transition-colors relative',
                                selectedOptimizations.has(optimization.cart_item_id)
                                    ? 'border-green-500 bg-green-50 dark:bg-green-900/20'
                                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600',
                            ]"
                            style="min-height: 280px;"
                            @click="toggleOptimization(optimization.cart_item_id)"
                        >
                            <!-- Status Badge -->
                            <div class="mb-2">
                                <span
                                    :class="getStatusBadgeClass(optimization)"
                                    class="px-2 py-1 rounded text-xs font-medium"
                                >
                                    {{ getStatusBadge(optimization) }}
                                    <span v-if="optimization.type === 'same_product'">
                                        QTY ({{ optimization.quantity }})
                                    </span>
                                </span>
                            </div>

                            <!-- Product Image -->
                            <div class="w-full h-32 bg-gray-200 dark:bg-gray-700 rounded mb-3 flex items-center justify-center">
                                <span class="text-gray-400 text-sm">Product Image</span>
                            </div>

                            <!-- Product Name -->
                            <h4 class="font-medium text-gray-900 dark:text-white text-sm mb-2 line-clamp-2">
                                {{ optimization.recommended_variant.product_name }}
                            </h4>

                            <!-- Price -->
                            <div class="mb-2">
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ formatCurrency(optimization.recommended_variant.price) }}
                                </span>
                                <span
                                    v-if="optimization.price_savings > 0"
                                    class="ml-2 text-sm text-green-600 dark:text-green-400"
                                >
                                    -{{ formatCurrency(Math.abs(optimization.price_savings)) }}
                                </span>
                            </div>

                            <!-- Shipping -->
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {{ optimization.recommended_variant.shipping_cost === 0 ? 'Free' : formatCurrency(optimization.recommended_variant.shipping_cost) }}
                                <span v-if="optimization.recommended_variant.estimated_delivery_date">
                                    (Get it {{ new Date(optimization.recommended_variant.estimated_delivery_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) }})
                                </span>
                            </p>

                            <!-- Supplier -->
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ optimization.recommended_variant.supplier_name }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 border-t border-gray-200 dark:border-gray-600">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Saved W/Shipping:</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                                {{ formatCurrency(totalSelectedSavings || optimizationData?.total_savings_with_shipping || 0) }}
                            </p>
                        </div>
                        <button
                            @click="applyOptimizations"
                            :disabled="selectedOptimizations.size === 0"
                            class="px-6 py-3 bg-green-600 text-white rounded-full hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium flex items-center gap-2"
                        >
                            Apply Optimizations
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

