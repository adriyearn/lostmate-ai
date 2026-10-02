/*
 * Smarter photo uploads (vanilla JS, no libraries).
 *
 * Add `data-lm-images` to any <input type="file"> to get:
 *   1. Instant previews of the chosen photos.
 *   2. Automatic shrinking: photos are resized to at most 1920px and saved
 *      as JPEG, so a 6 MB phone photo becomes ~300 KB and passes the 5 MB
 *      server limit. Uploads are also faster on mobile data.
 *   3. Privacy: re-drawing the photo drops its hidden EXIF metadata, which
 *      on phones often includes the GPS location where it was taken.
 *
 * Options (data attributes on the input):
 *   data-max-files="3"     - keep at most this many photos
 *   data-no-preview        - shrink only, don't show previews
 *
 * The server still validates everything (type, size, count), so the form
 * keeps working even if this script fails or JavaScript is turned off.
 */

const MAX_DIMENSION = 1920;
const JPEG_QUALITY = 0.82;
const SERVER_LIMIT = 5 * 1024 * 1024; // must match the 5 MB rule in the Form Requests

function formatSize(bytes) {
    return bytes >= 1024 * 1024
        ? (bytes / (1024 * 1024)).toFixed(1) + ' MB'
        : Math.max(1, Math.round(bytes / 1024)) + ' KB';
}

/**
 * Decode the photo. `imageOrientation: 'from-image'` makes sideways phone
 * photos come out the right way up.
 */
async function decode(file) {
    if (window.createImageBitmap) {
        return createImageBitmap(file, { imageOrientation: 'from-image' });
    }

    return new Promise((resolve, reject) => {
        const img = new Image();
        img.onload = () => resolve(img);
        img.onerror = reject;
        img.src = URL.createObjectURL(file);
    });
}

/**
 * Returns a smaller JPEG copy of the photo, or the original file if the
 * browser can't read it (the server will then decide whether to accept it).
 */
async function shrink(file) {
    try {
        const image = await decode(file);
        const scale = Math.min(1, MAX_DIMENSION / Math.max(image.width, image.height));
        const canvas = document.createElement('canvas');
        canvas.width = Math.round(image.width * scale);
        canvas.height = Math.round(image.height * scale);

        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#ffffff'; // transparent PNG areas become white, not black
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(image, 0, 0, canvas.width, canvas.height);

        const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', JPEG_QUALITY));
        if (!blob) {
            return file;
        }

        const name = file.name.replace(/\.[^.]+$/, '') + '.jpg';
        return new File([blob], name, { type: 'image/jpeg', lastModified: Date.now() });
    } catch (error) {
        return file;
    }
}

function renderPreviews(container, items) {
    container.innerHTML = '';

    items.forEach(({ original, processed }) => {
        const figure = document.createElement('figure');
        figure.className = 'lm-upload-thumb';

        const img = document.createElement('img');
        img.src = URL.createObjectURL(processed);
        img.alt = 'Preview of ' + original.name;

        const caption = document.createElement('figcaption');
        caption.className = 'lm-mono';
        caption.textContent = processed.size < original.size
            ? formatSize(original.size) + ' → ' + formatSize(processed.size)
            : formatSize(processed.size);

        if (processed.size > SERVER_LIMIT) {
            figure.classList.add('is-too-big');
            caption.textContent += ' · too large';
        }

        figure.append(img, caption);
        container.append(figure);
    });
}

function setUp(input) {
    const maxFiles = parseInt(input.dataset.maxFiles || (input.multiple ? '3' : '1'), 10);
    const form = input.form;

    let note = null;
    let previews = null;
    if (!('noPreview' in input.dataset)) {
        previews = document.createElement('div');
        previews.className = 'lm-upload-previews';
        note = document.createElement('div');
        note.className = 'form-text';
        input.after(previews, note);
    }

    input.addEventListener('change', async () => {
        let files = Array.from(input.files).filter((file) => file.type.startsWith('image/'));
        if (files.length === 0) {
            if (previews) previews.innerHTML = '';
            return;
        }

        let message = '';
        if (files.length > maxFiles) {
            files = files.slice(0, maxFiles);
            message = 'Only the first ' + maxFiles + ' photos were kept. ';
        }

        // Block submitting while photos are being processed.
        const buttons = form ? form.querySelectorAll('[type="submit"]') : [];
        buttons.forEach((button) => (button.disabled = true));
        if (note) note.textContent = 'Preparing photos…';

        const items = [];
        for (const original of files) {
            items.push({ original, processed: await shrink(original) });
        }

        // Swap the input's files for the smaller copies.
        if (window.DataTransfer) {
            const transfer = new DataTransfer();
            items.forEach(({ processed }) => transfer.items.add(processed));
            input.files = transfer.files;
        }

        buttons.forEach((button) => (button.disabled = false));

        if (previews) {
            renderPreviews(previews, items);
            const saved = items.reduce((sum, { original, processed }) => sum + (original.size - processed.size), 0);
            if (saved > 0) {
                message += 'Photos were resized to upload faster (saved ' + formatSize(saved) + '). Location data was removed.';
            }
            note.textContent = message;
        }
    });
}

document.querySelectorAll('input[type="file"][data-lm-images]').forEach(setUp);
