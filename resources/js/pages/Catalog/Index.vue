<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useProductApi } from '@/composables/useProductApi';
import type { Category, PaginatedResponse, Product } from '@/types/catalog';
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

const { fetchCategories, fetchProducts } = useProductApi();

const categories = ref<Category[]>([]);
const products = ref<PaginatedResponse<Product> | null>(null);
const selectedCategory = ref<number | null>(null);
const isLoading = ref(false);
const errorMessage = ref('');

const currentPage = computed(() => products.value?.meta.current_page ?? 1);
const lastPage = computed(() => products.value?.meta.last_page ?? 1);
const hasProducts = computed(() => (products.value?.data.length ?? 0) > 0);

async function loadCategories() {
    const response = await fetchCategories();
    categories.value = response.data;
}

async function loadProducts(page = 1) {
    isLoading.value = true;
    errorMessage.value = '';

    try {
        products.value = await fetchProducts({
            page,
            perPage: 12,
            categoryId: selectedCategory.value,
        });
    } catch (error) {
        errorMessage.value = error instanceof Error ? error.message : 'Не удалось загрузить товары.';
    } finally {
        isLoading.value = false;
    }
}

watch(selectedCategory, async () => {
    await loadProducts(1);
});

onMounted(async () => {
    await loadCategories();
    await loadProducts();
});
</script>

<template>
    <Head title="Каталог товаров" />

    <div
        class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(217,119,6,0.14),_transparent_32%),linear-gradient(180deg,_#fffdf7_0%,_#f5efe3_100%)]"
    >
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
            <header
                class="mb-8 flex flex-col gap-4 rounded-3xl border border-amber-200/70 bg-white/80 p-6 shadow-sm backdrop-blur sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <p class="text-sm uppercase tracking-[0.28em] text-amber-700">Catalog</p>
                    <h1 class="mt-2 font-serif text-4xl text-stone-900">Товары и категории</h1>
                    <p class="mt-3 max-w-2xl text-sm text-stone-600">Публичный каталог товаров с пагинацией и фильтрацией по категории.</p>
                </div>

                <div class="flex items-center gap-3">
                    <Link :href="route('admin.login')">
                        <Button variant="outline" class="border-stone-300 bg-white text-stone-900">Вход для администратора</Button>
                    </Link>
                </div>
            </header>

            <section class="mb-6 grid gap-4 rounded-3xl border border-stone-200 bg-white p-5 shadow-sm md:grid-cols-[1fr_280px]">
                <div>
                    <p class="text-sm font-medium text-stone-700">Товары</p>
                    <p class="text-sm text-stone-500">Найдено: {{ products?.meta.total ?? 0 }}.</p>
                </div>

                <label class="grid gap-2 text-sm text-stone-600">
                    Категория
                    <select
                        v-model="selectedCategory"
                        class="h-10 rounded-md border border-stone-300 bg-white px-3 text-sm text-stone-900 outline-none ring-0 transition focus:border-amber-500"
                    >
                        <option :value="null">Все категории</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>
                </label>
            </section>

            <div v-if="errorMessage" class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ errorMessage }}
            </div>

            <div v-if="isLoading" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div v-for="item in 6" :key="item" class="h-64 animate-pulse rounded-3xl bg-white/70" />
            </div>

            <div v-else-if="hasProducts" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <Card
                    v-for="product in products?.data"
                    :key="product.id"
                    class="overflow-hidden rounded-3xl border-stone-200 bg-white/90 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
                >
                    <CardHeader class="space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs uppercase tracking-[0.22em] text-amber-700">
                                    {{ product.category?.name ?? 'Без категории' }}
                                </p>
                                <CardTitle class="mt-2 text-xl text-stone-900">{{ product.name }}</CardTitle>
                            </div>
                            <div class="rounded-full bg-stone-900 px-3 py-1 text-sm font-semibold text-white">
                                {{ Number(product.price).toLocaleString('ru-RU') }} ₽
                            </div>
                        </div>
                        <CardDescription class="line-clamp-3 min-h-16 text-sm leading-6 text-stone-600">
                            {{ product.description || 'Описание отсутствует.' }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Link :href="`/product/${product.id}`">
                            <Button class="w-full bg-stone-900 text-white hover:bg-stone-800">Открыть карточку</Button>
                        </Link>
                    </CardContent>
                </Card>
            </div>

            <div v-else class="rounded-3xl border border-dashed border-stone-300 bg-white/80 px-6 py-10 text-center text-stone-500">
                По выбранному фильтру товары не найдены.
            </div>

            <div v-if="products && lastPage > 1" class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <Button
                    variant="outline"
                    class="border-stone-300 bg-white text-stone-500"
                    :disabled="currentPage <= 1 || isLoading"
                    @click="loadProducts(currentPage - 1)"
                >
                    Назад
                </Button>
                <Input :model-value="String(currentPage)" readonly class="w-20 border-stone-300 bg-white text-center text-stone-500" />
                <span class="text-sm text-stone-500">из {{ lastPage }}</span>
                <Button
                    variant="outline"
                    class="border-stone-300 bg-white text-stone-500"
                    :disabled="currentPage >= lastPage || isLoading"
                    @click="loadProducts(currentPage + 1)"
                >
                    Вперед
                </Button>
            </div>
        </div>
    </div>
</template>
