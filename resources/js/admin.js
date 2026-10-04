// MOAUM admin-only behaviour: the Trix rich-text editor and the media picker.
// Loaded before app.js so the Alpine components below are registered on
// `alpine:init`, which fires when app.js calls Alpine.start().
import 'trix';
import 'trix/dist/trix.css';

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

async function uploadImages(url, files) {
    const body = new FormData();
    [...files].forEach((file) => body.append('files[]', file));

    const response = await fetch(url, {
        method: 'POST',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
        body,
    });

    const payload = await response.json().catch(() => ({}));

    if (!response.ok) {
        const firstError = payload.errors ? Object.values(payload.errors)[0][0] : null;
        throw new Error(firstError ?? payload.message ?? 'Upload failed.');
    }

    return payload.data;
}

/* ------------------------------------------------------------------ Trix */

// Only images, and only through the media library (so they are optimised).
document.addEventListener('trix-file-accept', (event) => {
    const editor = event.target;
    if (!editor.dataset.uploadUrl || !event.file.type.startsWith('image/')) {
        event.preventDefault();
    }
});

document.addEventListener('trix-attachment-add', async (event) => {
    const { attachment } = event;
    const editor = event.target;
    if (!attachment.file || !editor.dataset.uploadUrl) {
        return;
    }

    try {
        attachment.setUploadProgress(10);
        const [media] = await uploadImages(editor.dataset.uploadUrl, [attachment.file]);
        attachment.setUploadProgress(100);
        attachment.setAttributes({ url: media.url, href: media.url });
    } catch (error) {
        attachment.remove();
        window.alert(error.message);
    }
});

/* ---------------------------------------------------------- Media picker */

document.addEventListener('alpine:init', () => {
    window.Alpine.data('mediaPicker', ({ value = '', libraryUrl, uploadUrl }) => ({
        value,
        open: false,
        items: [],
        next: null,
        query: '',
        loading: false,
        uploading: false,
        error: '',

        async show() {
            this.open = true;
            if (this.items.length === 0) {
                await this.search();
            }
        },

        async fetchPage(url) {
            this.loading = true;
            this.error = '';
            try {
                const response = await fetch(url, { headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('Could not load the media library.');
                return await response.json();
            } catch (error) {
                this.error = error.message;
                return { data: [], next: null };
            } finally {
                this.loading = false;
            }
        },

        async search() {
            const url = new URL(libraryUrl, window.location.origin);
            if (this.query) url.searchParams.set('q', this.query);
            const page = await this.fetchPage(url);
            this.items = page.data;
            this.next = page.next;
        },

        async more() {
            if (!this.next) return;
            const page = await this.fetchPage(this.next);
            this.items.push(...page.data);
            this.next = page.next;
        },

        async upload(event) {
            const files = event.target.files;
            if (!files.length) return;
            this.uploading = true;
            this.error = '';
            try {
                const created = await uploadImages(uploadUrl, files);
                this.items.unshift(...created);
                this.choose(created[0]);
            } catch (error) {
                this.error = error.message;
            } finally {
                this.uploading = false;
                event.target.value = '';
            }
        },

        choose(item) {
            this.value = item.url;
            this.open = false;
        },

        clear() {
            this.value = '';
        },
    }));
});
