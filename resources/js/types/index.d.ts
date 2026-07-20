export interface Auth {
    user: User | null;
}

export interface AppNotification {
    id: string;
    type: string;
    message: string;
    url: string | null;
    created_at: string;
}

export interface Flash {
    success?: string | null;
    error?: string | null;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    auth: Auth;
    notifications: AppNotification[];
    flash: Flash;
};

export type UserRole = 'admin' | 'staff' | 'customer';

export interface User {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    role: UserRole;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface Review {
    id: number;
    rating: number;
    comment: string | null;
    created_at: string;
    user?: { name: string };
}

export interface Paginated<T> {
    data: T[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
}
