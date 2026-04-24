<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useProductApi } from '@/composables/useProductApi';
import type { Product } from '@/types/catalog';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const props = defineProps<{
    productId: number;
}>();

const { fetchProduct } = useProductApi();

const product = ref<Product | null>(null);
const errorMessage = ref('');
const isLoading = ref(true);

onMounted(async () => {
    try {
        const response = await fetchProduct(props.productId);
        product.value = response.data;
    } catch (error) {
        errorMessage.value = error instanceof Error ? error.message : 'Не удалось загрузить товар.';
    } finally {
        isLoading.value = false;
    }
});
</script>

<template>
    <Head :title="product ? product.name : 'Карточка товара'" />

    <div class="min-h-screen bg-[linear-gradient(180deg,_#f7f2e7_0%,_#fff_40%,_#fffaf2_100%)]">
        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <Link href="/">
                    <Button variant="outline" class="border-stone-300 bg-white">Назад к каталогу</Button>
                </Link>
                <Link :href="route('admin.products.index')">
                    <Button variant="outline" class="border-stone-300 bg-white">Админ-панель</Button>
                </Link>
            </div>

            <div v-if="isLoading" class="h-96 animate-pulse rounded-3xl bg-white/80" />

            <div v-else-if="errorMessage" class="rounded-3xl border border-red-200 bg-red-50 px-6 py-5 text-red-700">
                {{ errorMessage }}
            </div>

            <Card v-else-if="product" class="rounded-[2rem] border-stone-200 bg-white shadow-xl shadow-amber-100/50">
                <CardHeader class="space-y-5 border-b border-stone-100 p-8">
                    <p class="text-xs uppercase tracking-[0.3em] text-amber-700">
                        {{ product.category?.name ?? 'Без категории' }}
                    </p>
                    <CardTitle class="max-w-3xl font-serif text-4xl text-stone-900">{{ product.name }}</CardTitle>
                    <div class="inline-flex w-fit rounded-full bg-stone-900 px-4 py-2 text-lg font-semibold text-white">
                        {{ Number(product.price).toLocaleString('ru-RU') }} ₽
                    </div>
                </CardHeader>
                <CardContent class="space-y-8 p-8">
                    <div>
                        <h2 class="mb-3 text-sm uppercase tracking-[0.24em] text-stone-500">Описание</h2>
                        <p class="max-w-3xl whitespace-pre-line text-base leading-8 text-stone-700">
                            {{ product.description || 'Описание отсутствует.' }}
                        </p>
                    </div>

                    <dl class="grid gap-4 rounded-3xl bg-stone-50 p-6 md:grid-cols-3">
                        <div>
                            <dt class="text-xs uppercase tracking-[0.2em] text-stone-500">ID товара</dt>
                            <dd class="mt-2 text-lg font-semibold text-stone-900">{{ product.id }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-[0.2em] text-stone-500">Создан</dt>
                            <dd class="mt-2 text-lg font-semibold text-stone-900">
                                {{ new Date(product.created_at).toLocaleDateString('ru-RU') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-[0.2em] text-stone-500">Обновлен</dt>
                            <dd class="mt-2 text-lg font-semibold text-stone-900">
                                {{ new Date(product.updated_at).toLocaleDateString('ru-RU') }}
                            </dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
