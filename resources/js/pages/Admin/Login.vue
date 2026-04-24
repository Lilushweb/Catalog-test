<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useAuth } from '@/composables/useAuth';
import { ApiError } from '@/composables/useApi';
import { Head, router } from '@inertiajs/vue3';
import { onMounted, reactive, ref } from 'vue';

const { isAuthenticated, login } = useAuth();

const form = reactive({
    email: 'admin@example.com',
    password: 'password',
});

const isSubmitting = ref(false);
const errorMessage = ref('');
const fieldErrors = ref<Record<string, string[]>>({});

onMounted(() => {
    if (isAuthenticated.value) {
        router.visit(route('admin.products.index'));
    }
});

async function submit() {
    isSubmitting.value = true;
    errorMessage.value = '';
    fieldErrors.value = {};

    try {
        await login(form.email, form.password);
        router.visit(route('admin.products.index'));
    } catch (error) {
        if (error instanceof ApiError) {
            errorMessage.value = error.message;
            fieldErrors.value = error.errors ?? {};
        } else {
            errorMessage.value = 'Не удалось выполнить вход.';
        }
    } finally {
        isSubmitting.value = false;
    }
}
</script>

<template>
    <Head title="Вход администратора" />

    <div class="flex min-h-screen items-center justify-center bg-[radial-gradient(circle_at_top,_rgba(28,25,23,0.12),_transparent_35%),linear-gradient(180deg,_#faf6ef_0%,_#efe3d0_100%)] px-4 py-10">
        <Card class="w-full max-w-md rounded-[2rem] border-stone-200 bg-white/95 shadow-2xl shadow-stone-200/70">
            <CardHeader class="space-y-3">
                <p class="text-xs uppercase tracking-[0.32em] text-amber-700">Admin</p>
                <CardTitle class="font-serif text-3xl text-stone-900">Вход в панель управления</CardTitle>
                <CardDescription class="text-sm leading-6 text-stone-600">
                    Используйте API-логин. Токен будет сохранен в локальном хранилище браузера.
                </CardDescription>
            </CardHeader>

            <CardContent>
                <form class="space-y-5" @submit.prevent="submit">
                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input id="email" v-model="form.email" type="email" required />
                        <InputError :message="fieldErrors.email?.[0]" />
                    </div>

                    <div class="space-y-2">
                        <Label for="password">Пароль</Label>
                        <Input id="password" v-model="form.password" type="password" required />
                        <InputError :message="fieldErrors.password?.[0]" />
                    </div>

                    <div v-if="errorMessage" class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ errorMessage }}
                    </div>

                    <Button type="submit" class="w-full bg-stone-900 text-white hover:bg-stone-800" :disabled="isSubmitting">
                        {{ isSubmitting ? 'Входим...' : 'Войти' }}
                    </Button>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
