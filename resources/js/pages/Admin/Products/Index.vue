<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useAuth } from '@/composables/useAuth';
import { ApiError } from '@/composables/useApi';
import { useProductApi } from '@/composables/useProductApi';
import type { Category, PaginatedResponse, Product } from '@/types/catalog';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

const { isAuthenticated, logout, user } = useAuth();
const { fetchCategories, fetchProducts, deleteProduct } = useProductApi();

const categories = ref<Category[]>([]);
const products = ref<PaginatedResponse<Product> | null>(null);
const selectedCategory = ref<number | null>(null);
const isLoading = ref(false);
const errorMessage = ref('');

const currentPage = computed(() => products.value?.meta.current_page ?? 1);
const lastPage = computed(() => products.value?.meta.last_page ?? 1);

async function ensureAccess() {
    if (!isAuthenticated.value) {
        router.visit(route('admin.login'));
        return false;
    }

    return true;
}

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
        errorMessage.value = error instanceof Error ? error.message : 'Не удалось загрузить список товаров.';
    } finally {
        isLoading.value = false;
    }
}

watch(selectedCategory, async () => {
    await loadProducts(1);
});

onMounted(async () => {
    if (!(await ensureAccess())) {
        return;
    }

    await loadCategories();
    await loadProducts();
});

async function handleDelete(product: Product) {
    if (!window.confirm(`Удалить товар "${product.name}"?`)) {
        return;
    }

    try {
        await deleteProduct(product.id);
        await loadProducts(currentPage.value);
    } catch (error) {
        if (error instanceof ApiError && error.status === 401) {
            router.visit(route('admin.login'));
            return;
        }

        errorMessage.value = error instanceof Error ? error.message : 'Не удалось удалить товар.';
    }
}

async function handleLogout() {
    await logout();
    router.visit(route('admin.login'));
}
</script>

<template>
    <Head title="Управление товарами" />

    <div class="min-h-screen bg-[linear-gradient(180deg,_#1c1917_0%,_#292524_22%,_#f5efe6_22%,_#f5efe6_100%)]">
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
            <header class="mb-8 rounded-[2rem] bg-stone-900 px-6 py-6 text-white shadow-xl shadow-stone-950/20">
                <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.32em] text-amber-300">Admin panel</p>
                        <h1 class="mt-2 font-serif text-4xl">Управление товарами</h1>
                        <p class="mt-3 text-sm text-stone-300">
                            {{ user?.email ?? 'Администратор' }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <Link :href="route('admin.products.create')">
                            <Button class="bg-amber-500 text-stone-950 hover:bg-amber-400">Добавить товар</Button>
                        </Link>
                        <Link href="/">
                            <Button variant="outline" class="border-stone-700 bg-stone-800 text-white hover:bg-stone-700">Публичный каталог</Button>
                        </Link>
                        <Button variant="outline" class="border-stone-700 bg-stone-800 text-white hover:bg-stone-700" @click="handleLogout">
                            Выйти
                        </Button>
                    </div>
                </div>
            </header>

            <section class="mb-6 rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
                <div class="grid gap-4 md:grid-cols-[1fr_280px] md:items-end">
                    <div>
                        <p class="text-sm font-medium text-stone-700">Товаров в каталоге</p>
                        <p class="text-sm text-stone-500">{{ products?.meta.total ?? 0 }}</p>
                    </div>

                    <label class="grid gap-2 text-sm text-stone-600">
                        Фильтр по категории
                        <select
                            v-model="selectedCategory"
                            class="h-10 rounded-md border border-stone-300 bg-white px-3 text-sm text-stone-900 outline-none transition focus:border-amber-500"
                        >
                            <option :value="null">Все категории</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </label>
                </div>
            </section>

            <div v-if="errorMessage" class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ errorMessage }}
            </div>

            <div class="grid gap-4">
                <Card v-for="product in products?.data" :key="product.id" class="rounded-3xl border-stone-200 bg-white shadow-sm">
                    <CardHeader class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.22em] text-amber-700">
                                {{ product.category?.name ?? 'Без категории' }}
                            </p>
                            <CardTitle class="mt-2 text-2xl text-stone-900">{{ product.name }}</CardTitle>
                            <p class="mt-3 max-w-3xl text-sm leading-6 text-stone-600">
                                {{ product.description || 'Описание отсутствует.' }}
                            </p>
                        </div>
                        <div class="flex flex-col items-start gap-3 md:items-end">
                            <div class="rounded-full bg-stone-900 px-4 py-2 text-sm font-semibold text-white">
                                {{ Number(product.price).toLocaleString('ru-RU') }} ₽
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <Link :href="`/admin/products/${product.id}/edit`">
                                    <Button variant="outline" class="border-stone-300 bg-white text-stone-600">Редактировать</Button>
                                </Link>
                                <Button
                                    variant="outline"
                                    class="border-red-200 bg-red-50 text-red-700 hover:bg-red-100"
                                    :disabled="isLoading"
                                    @click="handleDelete(product)"
                                >
                                    Удалить
                                </Button>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="pt-0">
                        <Link :href="`/product/${product.id}`" class="text-sm font-medium text-stone-700 underline underline-offset-4">
                            Открыть публичную карточку
                        </Link>
                    </CardContent>
                </Card>
            </div>

            <div
                v-if="!isLoading && (products?.data.length ?? 0) === 0"
                class="rounded-3xl border border-dashed border-stone-300 bg-white px-6 py-10 text-center text-stone-500"
            >
                Товары не найдены.
            </div>

            <div v-if="products && lastPage > 1" class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <Button variant="outline" class="border-stone-300 bg-white" :disabled="currentPage <= 1" @click="loadProducts(currentPage - 1)">
                    Назад
                </Button>
                <span class="rounded-md bg-white px-4 py-2 text-sm text-stone-700 shadow-sm"> Страница {{ currentPage }} из {{ lastPage }} </span>
                <Button
                    variant="outline"
                    class="border-stone-300 bg-white"
                    :disabled="currentPage >= lastPage"
                    @click="loadProducts(currentPage + 1)"
                >
                    Вперед
                </Button>
            </div>
        </div>
    </div>
</template>
