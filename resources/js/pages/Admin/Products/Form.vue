<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useAuth } from '@/composables/useAuth';
import { ApiError } from '@/composables/useApi';
import { useProductApi } from '@/composables/useProductApi';
import type { Category, ProductPayload } from '@/types/catalog';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted, reactive, ref } from 'vue';

const props = defineProps<{
    productId?: number;
}>();

const { isAuthenticated, logout } = useAuth();
const { fetchCategories, fetchProduct, createProduct, updateProduct } = useProductApi();

const categories = ref<Category[]>([]);
const isLoading = ref(true);
const isSubmitting = ref(false);
const errorMessage = ref('');
const fieldErrors = ref<Record<string, string[]>>({});

const form = reactive<ProductPayload>({
    name: '',
    description: '',
    price: '',
    category_id: null,
});

const isEditMode = computed(() => Boolean(props.productId));

function validateClient() {
    const errors: Record<string, string[]> = {};

    if (!form.name.trim()) {
        errors.name = ['Название обязательно.'];
    }

    if (!form.category_id) {
        errors.category_id = ['Категория обязательна.'];
    }

    if (Number(form.price) <= 0) {
        errors.price = ['Цена должна быть больше 0.'];
    }

    fieldErrors.value = errors;

    return Object.keys(errors).length === 0;
}

async function ensureAccess() {
    if (!isAuthenticated.value) {
        router.visit(route('admin.login'));
        return false;
    }

    return true;
}

async function loadPageData() {
    if (!(await ensureAccess())) {
        return;
    }

    try {
        const categoriesResponse = await fetchCategories();
        categories.value = categoriesResponse.data;

        if (props.productId) {
            const productResponse = await fetchProduct(props.productId);
            const product = productResponse.data;

            form.name = product.name;
            form.description = product.description ?? '';
            form.price = product.price;
            form.category_id = product.category_id;
        }
    } catch (error) {
        if (error instanceof ApiError && error.status === 401) {
            await logout();
            router.visit(route('admin.login'));
            return;
        }

        errorMessage.value = error instanceof Error ? error.message : 'Не удалось загрузить форму.';
    } finally {
        isLoading.value = false;
    }
}

async function submit() {
    errorMessage.value = '';

    if (!validateClient()) {
        return;
    }

    isSubmitting.value = true;

    try {
        if (props.productId) {
            await updateProduct(props.productId, form);
        } else {
            await createProduct(form);
        }

        router.visit(route('admin.products.index'));
    } catch (error) {
        if (error instanceof ApiError) {
            fieldErrors.value = error.errors ?? {};
            errorMessage.value = error.message;

            if (error.status === 401) {
                await logout();
                router.visit(route('admin.login'));
            }
        } else {
            errorMessage.value = 'Не удалось сохранить товар.';
        }
    } finally {
        isSubmitting.value = false;
    }
}

onMounted(loadPageData);
</script>

<template>
    <Head :title="isEditMode ? 'Редактирование товара' : 'Создание товара'" />

    <div class="min-h-screen bg-[linear-gradient(180deg,_#1c1917_0%,_#2d2a26_20%,_#f8f3ea_20%,_#f8f3ea_100%)]">
        <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-wrap gap-3">
                <Link :href="route('admin.products.index')">
                    <Button variant="outline" class="border-stone-300 bg-white">К списку товаров</Button>
                </Link>
                <Link href="/">
                    <Button variant="outline" class="border-stone-300 bg-white">Публичный каталог</Button>
                </Link>
            </div>

            <Card class="rounded-[2rem] border-stone-200 bg-white shadow-xl">
                <CardHeader>
                    <p class="text-xs uppercase tracking-[0.3em] text-amber-700">
                        {{ isEditMode ? 'Edit product' : 'Create product' }}
                    </p>
                    <CardTitle class="mt-2 font-serif text-4xl text-stone-900">
                        {{ isEditMode ? 'Редактирование товара' : 'Добавление товара' }}
                    </CardTitle>
                    <CardDescription class="text-sm leading-6 text-stone-600">
                        Поля категории загружаются из публичного API, сохранение идет через защищенные Sanctum-эндпоинты.
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <div v-if="isLoading" class="h-80 animate-pulse rounded-3xl bg-stone-100" />

                    <form v-else class="space-y-6" @submit.prevent="submit">
                        <div class="grid gap-2">
                            <Label for="name">Название</Label>
                            <Input id="name" v-model="form.name" required />
                            <InputError :message="fieldErrors.name?.[0]" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="category_id">Категория</Label>
                            <select
                                id="category_id"
                                v-model="form.category_id"
                                required
                                class="h-10 rounded-md border border-stone-300 bg-white px-3 text-sm text-stone-900 outline-none transition focus:border-amber-500"
                            >
                                <option :value="null" disabled>Выберите категорию</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                            <InputError :message="fieldErrors.category_id?.[0]" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="price">Цена</Label>
                            <Input id="price" v-model="form.price" type="number" min="0.01" step="0.01" required />
                            <InputError :message="fieldErrors.price?.[0]" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="description">Описание</Label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="6"
                                class="min-h-36 rounded-md border border-stone-300 bg-white px-3 py-2 text-sm text-stone-900 outline-none transition focus:border-amber-500"
                            />
                            <InputError :message="fieldErrors.description?.[0]" />
                        </div>

                        <div v-if="errorMessage" class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {{ errorMessage }}
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <Button type="submit" class="bg-stone-900 text-white hover:bg-stone-800" :disabled="isSubmitting">
                                {{ isSubmitting ? 'Сохранение...' : 'Сохранить товар' }}
                            </Button>
                            <Link :href="route('admin.products.index')">
                                <Button type="button" variant="outline" class="border-stone-300 bg-white">Отмена</Button>
                            </Link>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
