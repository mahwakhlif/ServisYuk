// ==========================================================
// ServisYuk - Live Tracking Interactivity
// ==========================================================

document.addEventListener('DOMContentLoaded', function () {
    const trackForm = document.getElementById('tracking_form');
    const trackInput = document.getElementById('tracking_code_input');
    const resultContainer = document.getElementById('tracking_result_container');
    const notFoundContainer = document.getElementById('tracking_not_found');
    const loadingEl = document.getElementById('tracking_loading');

    function performTracking(code) {
        if (!code) return;

        if (resultContainer) resultContainer.style.display = 'none';
        if (notFoundContainer) notFoundContainer.style.display = 'none';
        if (loadingEl) loadingEl.style.display = 'block';

        fetch(`index.php?page=api_quick_track&code=${encodeURIComponent(code)}`)
            .then(res => res.json())
            .then(res => {
                if (loadingEl) loadingEl.style.display = 'none';

                if (res.success && res.data) {
                    renderTrackingResult(res.data);
                } else {
                    if (notFoundContainer) {
                        notFoundContainer.textContent = res.message || 'Data perbaikan tidak ditemukan. Silakan periksa kembali nomor tiket Anda.';
                        notFoundContainer.style.display = 'block';
                    }
                }
            })
            .catch(err => {
                if (loadingEl) loadingEl.style.display = 'none';
                if (notFoundContainer) {
                    notFoundContainer.textContent = 'Gagal menghubungi server. Pastikan koneksi internet Anda aktif.';
                    notFoundContainer.style.display = 'block';
                }
            });
    }

    function renderTrackingResult(data) {
        if (!resultContainer) return;

        document.getElementById('track_code_display').textContent = data.booking_code;
        document.getElementById('track_category_display').textContent = data.category_name + (data.appliance_brand ? ` - ${data.appliance_brand}` : '');
        document.getElementById('track_schedule_display').textContent = `${data.scheduled_date_formatted} (${data.scheduled_time})`;
        document.getElementById('track_issue_display').textContent = data.issue_description;

        // Status Badge
        const statusBadge = document.getElementById('track_status_badge');
        if (statusBadge) {
            statusBadge.className = `badge ${data.status_info.class}`;
            statusBadge.innerHTML = `<i class="${data.status_info.icon}"></i> ${data.status_info.label}`;
        }

        // Status Description
        document.getElementById('track_status_desc').textContent = data.status_info.description;

        // Stepper Progress
        const currentStep = data.status_info.step || 1;
        for (let i = 1; i <= 5; i++) {
            const stepEl = document.getElementById(`track_step_${i}`);
            if (stepEl) {
                stepEl.classList.remove('active', 'completed');
                if (i < currentStep) {
                    stepEl.classList.add('completed');
                } else if (i === currentStep) {
                    stepEl.classList.add('active');
                }
            }
        }

        // Technician details
        const techBox = document.getElementById('track_technician_box');
        if (techBox) {
            if (data.technician_name) {
                document.getElementById('track_tech_name').textContent = data.technician_name;
                document.getElementById('track_tech_phone').textContent = data.technician_phone || '-';
                document.getElementById('track_tech_wa_btn').href = `https://wa.me/${(data.technician_phone || '').replace(/[^0-9]/g, '')}`;
                techBox.style.display = 'flex';
            } else {
                techBox.style.display = 'none';
            }
        }

        resultContainer.style.display = 'block';
    }

    if (trackForm) {
        trackForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const code = trackInput ? trackInput.value.trim() : '';
            performTracking(code);
        });
    }

    // Auto-search if URL contains ?code=
    const urlParams = new URLSearchParams(window.location.search);
    const codeParam = urlParams.get('code');
    if (codeParam) {
        if (trackInput) trackInput.value = codeParam;
        performTracking(codeParam);
    }
});
