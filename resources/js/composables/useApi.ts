import { computed } from 'vue';

import { useAuth } from './useAuth';

export class ApiError extends Error {
    status: number;
    errors: Record<string, string[]> | undefined;

    constructor(message: string, status: number, errors?: Record<string, string[]>) {
        super(message);
        this.status = status;
        this.errors = errors;
    }
}

export function useApi() {
    const { token, clearAuth } = useAuth();

    const authHeaders = computed(() =>
        token.value
            ? {
                  Authorization: `Bearer ${token.value}`,
              }
            : {},
    );

    async function request<T>(url: string, options: RequestInit = {}, requiresAuth = false): Promise<T> {
        const headers = new Headers(options.headers ?? {});

        headers.set('Accept', 'application/json');

        if (!headers.has('Content-Type') && options.body && !(options.body instanceof FormData)) {
            headers.set('Content-Type', 'application/json');
        }

        if (requiresAuth && token.value) {
            Object.entries(authHeaders.value).forEach(([key, value]) => headers.set(key, value));
        }

        const response = await fetch(url, {
            ...options,
            headers,
        });

        const isJson = response.headers.get('content-type')?.includes('application/json');
        const payload = isJson ? await response.json() : null;

        if (!response.ok) {
            if (response.status === 401) {
                clearAuth();
            }

            throw new ApiError(
                payload?.message ?? 'Request failed.',
                response.status,
                payload?.errors,
            );
        }

        return payload as T;
    }

    return { request };
}
