import ky, { KyInstance, Options } from "ky";

export function http(prefix: string, options?: Options): KyInstance {
    return ky.extend({
        prefixUrl: `http://localhost:8000${prefix}`,
        credentials: "include",
        retry: 0,
        hooks: {
            beforeRequest: [
                async request => {
                    // Get fresh CSRF token if needed
                    if (!getCookieValue('XSRF-TOKEN')) {
                        await fetch('http://localhost:8000/sanctum/csrf-cookie', {
                            credentials: 'include'
                        });
                    }

                    const token = getCookieValue("XSRF-TOKEN");
                    if (!token) {
                        throw new Error("CSRF token not found after refresh attempt");
                    }

                    request.headers.set('X-XSRF-TOKEN', decodeURIComponent(token));
                    request.headers.set('Accept', 'application/json');
                    
                    // Log request details for debugging
                    console.log('Request details:', {
                        url: request.url,
                        method: request.method,
                        headers: Object.fromEntries(request.headers.entries())
                    });
                }
            ],
            beforeError: [
                error => {
                    console.error('Request error:', {
                        status: error.response?.status,
                        statusText: error.response?.statusText,
                        url: error.request.url
                    });
                    return error;
                }
            ]
        },
        ...options
    });
}


export function getCookieValue(name: string): string | undefined {
    const regex = new RegExp(`(^| )${name}=([^;]+)`)
    const match = decodeURIComponent(document.cookie).match(regex)
    if (match) {
        return match[2]
    }
}