// ==========================================================
// ServisYuk - Core JavaScript Interactivity
// ==========================================================

document.addEventListener('DOMContentLoaded', function () {
    // Auto-dismiss alert messages after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    // Modal Triggers
    const modalOpeners = document.querySelectorAll('[data-modal-target]');
    modalOpeners.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-modal-target');
            const modal = document.getElementById(targetId);
            if (modal) {
                modal.classList.add('show');
            }
        });
    });

    const modalClosers = document.querySelectorAll('[data-modal-close]');
    modalClosers.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const modal = this.closest('.modal-backdrop');
            if (modal) {
                modal.classList.remove('show');
            }
        });
    });

    // Close modal on backdrop click
    document.querySelectorAll('.modal-backdrop').forEach(function (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === this) {
                this.classList.remove('show');
            }
        });
    });

    // Category Change -> Filter Services AJAX
    const categorySelect = document.getElementById('category_select');
    const serviceSelect = document.getElementById('service_select');

    if (categorySelect && serviceSelect) {
        categorySelect.addEventListener('change', function () {
            const categoryId = this.value;
            if (!categoryId) {
                serviceSelect.innerHTML = '<option value="">-- Pilih Layanan (Opsional) --</option>';
                return;
            }

            serviceSelect.innerHTML = '<option value="">Memuat layanan...</option>';
            fetch(`index.php?page=api_services&category_id=${categoryId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.data.length > 0) {
                        let html = '<option value="">-- Pilih Layanan Spesifik --</option>';
                        data.data.forEach(s => {
                            const minFormatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(s.estimated_price_min);
                            const maxFormatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(s.estimated_price_max);
                            html += `<option value="${s.id}">${s.name} (${minFormatted} - ${maxFormatted})</option>`;
                        });
                        serviceSelect.innerHTML = html;
                    } else {
                        serviceSelect.innerHTML = '<option value="">-- Layanan Standar / Inspeksi Umum --</option>';
                    }
                })
                .catch(() => {
                    serviceSelect.innerHTML = '<option value="">-- Pilih Layanan Spesifik --</option>';
                });
        });
    }

    // Print Invoice Button
    const printBtns = document.querySelectorAll('.btn-print-invoice');
    printBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            window.print();
        });
    });
});
