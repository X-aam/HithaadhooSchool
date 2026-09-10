function readCookie(name: string): string {
    const match = document.cookie
        .split('; ')
        .find((row) => row.startsWith(`${name}=`));

    return match ? decodeURIComponent(match.split('=').slice(1).join('=')) : '';
}

/**
 * Upload an image to the admin uploads endpoint and return its public URL.
 * Uses the Laravel XSRF cookie for CSRF protection.
 */
export async function uploadImage(file: File): Promise<string> {
    const body = new FormData();
    body.append('image', file);

    const response = await fetch('/admin/uploads', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'X-XSRF-TOKEN': readCookie('XSRF-TOKEN'),
        },
        body,
    });

    if (!response.ok) {
        throw new Error(`Upload failed (${response.status})`);
    }

    const data = (await response.json()) as { url: string };

    return data.url;
}

export interface UploadedFileInfo {
    url: string;
    name: string;
    extension: string;
    size: string;
}

/**
 * Upload a document (PDF, Word, Excel, …) and get back its link plus the
 * details a content editor needs to fill in — type and human-readable size.
 */
export async function uploadFile(file: File): Promise<UploadedFileInfo> {
    const body = new FormData();
    body.append('file', file);

    const response = await fetch('/admin/uploads/file', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'X-XSRF-TOKEN': readCookie('XSRF-TOKEN'),
        },
        body,
    });

    if (!response.ok) {
        throw new Error(`Upload failed (${response.status})`);
    }

    return (await response.json()) as UploadedFileInfo;
}
