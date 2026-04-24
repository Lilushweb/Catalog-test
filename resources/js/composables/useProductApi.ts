import type { Category, PaginatedResponse, Product, ProductPayload } from '@/types/catalog';

import { useApi } from './useApi';

export function useProductApi() {
    const { request } = useApi();

    function fetchProducts(params: { categoryId?: number | null; page?: number; perPage?: number } = {}) {
        const query = new URLSearchParams();

        if (params.page) {
            query.set('page', String(params.page));
        }

        if (params.perPage) {
            query.set('per_page', String(params.perPage));
        }

        if (params.categoryId) {
            query.set('category_id', String(params.categoryId));
        }

        const url = query.size > 0 ? `/api/products?${query.toString()}` : '/api/products';

        return request<PaginatedResponse<Product>>(url);
    }

    function fetchProduct(id: number) {
        return request<{ data: Product }>(`/api/products/${id}`);
    }

    function fetchCategories() {
        return request<{ data: Category[] }>('/api/categories');
    }

    function createProduct(payload: ProductPayload) {
        return request<{ data: Product; message: string }>(
            '/api/products',
            {
                method: 'POST',
                body: JSON.stringify(payload),
            },
            true,
        );
    }

    function updateProduct(id: number, payload: ProductPayload) {
        return request<{ data: Product; message: string }>(
            `/api/products/${id}`,
            {
                method: 'PUT',
                body: JSON.stringify(payload),
            },
            true,
        );
    }

    function deleteProduct(id: number) {
        return request(`/api/products/${id}`, { method: 'DELETE' }, true);
    }

    return {
        fetchProducts,
        fetchProduct,
        fetchCategories,
        createProduct,
        updateProduct,
        deleteProduct,
    };
}
