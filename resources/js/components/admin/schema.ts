export type FieldType =
    | 'text'
    | 'textarea'
    | 'number'
    | 'date'
    | 'select'
    | 'bilingual'
    | 'bilingualText'
    | 'image';

export interface FieldDef {
    key: string;
    label: string;
    type: FieldType;
    options?: { value: string | number; label: string }[];
    placeholder?: string;
    help?: string;
    step?: number | 'any';
}

/** Deep clone a plain JSON value (used to copy sample-data defaults safely). */
export function clone<T>(value: T): T {
    return JSON.parse(JSON.stringify(value)) as T;
}
