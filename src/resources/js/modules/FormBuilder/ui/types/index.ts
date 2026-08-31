export interface FormField {
    id: string;
    type: string;
    label: string;
    name: string;
    placeholder?: string;
    required: boolean;
    options?: string[];
    min_length?: number;
    max_length?: number;
    min_value?: number;
    max_value?: number;
    default_value?: string;
    css_class?: string;
    validate_email?: boolean;
    validate_phone?: boolean;
    conditions?: {
        field: string;
        operator: 'equals' | 'not_equals';
        value?: string;
    }[];
    conditions_logic?: 'and' | 'or';
}

export interface Form {
    id: number;
    title: string;
    alias: string;
    description: string | null;
    status: boolean;
    fields: FormField[];
    notification_emails?: string[] | null;
    settings?: Record<string, any>;
    is_dynamic?: boolean;
    submissions_count?: number;
    created_at?: string;
    updated_at?: string;
}

export interface FormFilters {
    search?: string;
    status?: boolean | string;
}

export interface Submission {
    id: number;
    form_id: number;
    user_id?: number | null;
    data: Record<string, any>;
    status: string;
    status_label?: string;
    status_color?: string;
    meta?: Record<string, any> | null;
    read_at: string | null;
    created_at: string;
    updated_at: string;
    form?: Form;
    display_name?: string;
    sender_name?: string;
    sender_email?: string;
    sender_phone?: string;
    content?: string;
    subject?: string;
}

export interface SubmissionFilters {
    search?: string;
    status?: string;
    form_id?: number;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}