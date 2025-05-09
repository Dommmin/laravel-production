import { LucideIcon } from 'lucide-react';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

interface Article {
    id: number;
    title: string;
    slug: string;
    content: string;
    user: string | { name: string };
    tags: string[];
    created_at: string;
    updated_at: string;
    location?: {
        lat: number;
        lon: number;
    };
    location_name?: string;
    city_name?: string;
}

interface FiltersProps {
    q: string;
    setQuery: (query: string) => void;
    tag: string;
    setTag: (tag: string) => void;
    city: string;
    setCity: (city: string) => void;
    radius: number;
    setRadius: (radius: number) => void;
    cities: string[];
    tags: string[];
    lat: string;
    lon: string;
    page: number;
}

interface Message {
    id: number;
    user_id: number;
    recipient_id: number;
    message: string;
    created_at: string;
    updated_at: string;
}
