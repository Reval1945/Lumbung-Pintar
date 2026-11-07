import Swal from 'sweetalert2';

/**
 * Escape HTML agar pesan dari server tidak menyebabkan XSS.
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = String(text);
    return div.innerHTML;
}

/** Notifikasi sukses (hilang otomatis). */
export function showSuccess(message, title = 'Berhasil') {
    return Swal.fire({
        icon: 'success',
        title,
        text: message,
        timer: 1800,
        showConfirmButton: false,
    });
}

/** Notifikasi error. */
export function showError(message = 'Terjadi kesalahan.', title = 'Gagal') {
    return Swal.fire({
        icon: 'error',
        title,
        text: message,
        confirmButtonText: 'Tutup',
    });
}

/**
 * Dialog konfirmasi. Mengembalikan true jika user menekan tombol konfirmasi.
 * Contoh: if (await showConfirm('Apakah kamu yakin?', 'Data tidak bisa dikembalikan.')) { ... }
 */
export async function showConfirm(title, text, confirmText = 'Ya, Hapus') {
    const result = await Swal.fire({
        icon: 'warning',
        title,
        text,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        reverseButtons: true,
    });

    return result.isConfirmed;
}

/** Loading modal. Tutup dengan closeLoading(). */
export function showLoading(title = 'Memproses...') {
    Swal.fire({
        title,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });
}

export function closeLoading() {
    Swal.close();
}

/**
 * Tampilkan error validasi 422 dari Laravel.
 * errors = { title: ['Judul wajib diisi.'], ... }
 */
export function showValidationError(errors = {}, title = 'Validasi gagal') {
    const items = Object.values(errors)
        .flat()
        .map((msg) => `<li>${escapeHtml(msg)}</li>`)
        .join('');

    return Swal.fire({
        icon: 'error',
        title,
        html: `<ul class="text-start mb-0">${items}</ul>`,
        confirmButtonText: 'Tutup',
    });
}