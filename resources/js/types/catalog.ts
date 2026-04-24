export interface Category {
    id: number;
    name: string;
    description: string | null;
    created_at: string;
    updated_at: string;
}

export interface Product {
    id: number;
    name: string;
    description: string | null;
    price: string;
    category_id: number;
    category?: Category | null;
    created_at: string;
    updated_at: string;
}

export interface PaginationLink {
    active: boolean;
    label: string;
    url: string | null;
}

export interface PaginationMeta {
    current_page: number;
    from: number | null;
    last_page: number;
    links: PaginationLink[];
    path: string;
    per_page: number;
    to: number | null;
    total: number;
}

export interface PaginatedResponse<T> {
    data: T[];
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
    meta: PaginationMeta;
}

export interface ProductPayload {
    name: string;
    description: string;
    price: number | string;
    category_id: number | null;
}

export interface ApiUser {
    id: number;
    name: string;
    email: string;
}
