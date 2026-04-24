import { computed, ref, watch } from 'vue';

import type { ApiUser } from '@/types/catalog';

const TOKEN_KEY = 'catalog_admin_token';
const USER_KEY = 'catalog_admin_user';

const token = ref<string | null>(null);
const user = ref<ApiUser | null>(null);
const isReady = ref(false);

function canUseStorage() {
    return typeof window !== 'undefined' && typeof window.localStorage !== 'undefined';
}

function hydrate() {
    if (isReady.value || !canUseStorage()) {
        isReady.value = true;
        return;
    }

    token.value = window.localStorage.getItem(TOKEN_KEY);

    const storedUser = window.localStorage.getItem(USER_KEY);
    user.value = storedUser ? (JSON.parse(storedUser) as ApiUser) : null;
    isReady.value = true;
}

function clearAuth() {
    token.value = null;
    user.value = null;
}

watch(token, (value) => {
    if (!canUseStorage()) {
        return;
    }

    if (value) {
        window.localStorage.setItem(TOKEN_KEY, value);
        return;
    }

    window.localStorage.removeItem(TOKEN_KEY);
});

watch(user, (value) => {
    if (!canUseStorage()) {
        return;
    }

    if (value) {
        window.localStorage.setItem(USER_KEY, JSON.stringify(value));
        return;
    }

    window.localStorage.removeItem(USER_KEY);
});

export function useAuth() {
    hydrate();

    async function login(email: string, password: string) {
        const response = await fetch('/api/login', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email, password }),
        });

        const payload = (await response.json()) as {
            errors?: Record<string, string[]>;
            message?: string;
            token?: string;
            user?: ApiUser;
        };

        if (!response.ok || !payload.token || !payload.user) {
            throw new Error(payload.message ?? 'Не удалось выполнить вход.');
        }

        token.value = payload.token;
        user.value = payload.user;

        return payload;
    }

    async function logout() {
        try {
            if (token.value) {
                await fetch('/api/logout', {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        Authorization: `Bearer ${token.value}`,
                    },
                });
            }
        } finally {
            clearAuth();
        }
    }

    return {
        token,
        user,
        isReady,
        isAuthenticated: computed(() => Boolean(token.value)),
        login,
        logout,
        clearAuth,
    };
}
