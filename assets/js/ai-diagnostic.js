// ==========================================================
// ServisYuk - AI Diagnostic Assistant Interactive Engine
// ==========================================================

document.addEventListener('DOMContentLoaded', function () {
    const categorySelect = document.getElementById('ai_category');
    const symptomInput = document.getElementById('ai_symptoms');
    const chipsContainer = document.getElementById('ai_symptom_chips');
    const diagForm = document.getElementById('ai_diagnostic_form');
    const loadingState = document.getElementById('ai_loading_state');
    const resultCard = document.getElementById('ai_result_card');
    const formContainer = document.getElementById('ai_form_container');

    // Preset Symptom Suggestions based on category
    const symptomPresets = {
        'AC & Sistem Pendingin': [
            'AC Tidak Dingin (Hanya Angin)',
            'Unit Indoor Bocor / Netes Air',
            'Suara Berisik / Mendengung Keras',
            'Hembusan Angin Berbau Apek',
            'Outdoor Mati Total / Kipas Tidak Putar',
            'AC Mati Hidup Sendiri'
        ],
        'Kulkas & Freezer': [
            'Kulkas Tidak Dingin Lampu Nyala',
            'Penumpukan Bunga Es Berlebih',
            'Freezer Dingin Bawah Hangat',
            'Kompresor Belakang Panas & Dengung',
            'Bocor Air di Bagian Bawah Kulkas',
            'Karet Pintu Longgar / Rusak'
        ],
        'Mesin Cuci & Pengering': [
            'Tabung Cuci/Pengering Tidak Berputar',
            'Air Tidak Mau Keluar (Drain Error)',
            'Air Masuk Terus Menerus Tidak Berhenti',
            'Getaran Keras & Bunyi Menghantam',
            'Modul Error Digital E1 / IE / OE',
            'Pintu Terkunci Tidak Mau Terbuka'
        ],
        'Microwave & Oven Listrik': [
            'Piringan Putar Normal Tapi Makanan Tak Panas',
            'Timbul Percikan Api di Bagian Dalam',
            'Mati Total / Tombol Sentuh Tidak Merespons',
            'Bau Hangus / Asap Saat Dinyalakan'
        ],
        'TV & Audio Visual': [
            'Suara Ada Gambar Layar Gelap (Black Screen)',
            'Layar Bergaris-garis Vertikal / Horisontal',
            'TV Mati Total Lampu Indikator Kedip',
            'Gambar Berbayang atau Warna Tidak Normal'
        ],
        'Dispenser & Pompa Air': [
            'Air Panas / Dingin Tidak Berfungsi',
            'Pompa Hisap Galon Bawah Macet',
            'Kran Air Menetes Terus Menerus',
            'Mesin Pompa Air Mendengung Tidak Menyedot'
        ],
        'Kompor Gas & Induksi': [
            'Pemantik Api Tidak Mau Memercik',
            'Api Berwarna Merah & Menghitamkan Wajan',
            'Bau Gas Menusuk Saat Kompor Dinyalakan',
            'Induksi Muncul Kode Error E0 / E1'
        ],
        'Rice Cooker & Air Fryer': [
            'Nasi Cepat Basi & Berair',
            'Tombol Cooking Langsung Pindah ke Warm',
            'Elemen Pemanas Mati Total',
            'Air Fryer Kipas Berputar Tapi Tidak Panas'
        ]
    };

    // Render chips when category changes
    function updateSymptomChips(categoryName) {
        if (!chipsContainer) return;
        chipsContainer.innerHTML = '';

        const presets = symptomPresets[categoryName] || [
            'Peralatan mati total',
            'Terdengar suara aneh / mendengung',
            'Kinerja berkurang drastis',
            'Keluar bau terbakar / panas berlebih',
            'Lampu indikator berkedip error'
        ];

        presets.forEach(function (text) {
            const chip = document.createElement('div');
            chip.className = 'symptom-chip';
            chip.textContent = text;
            chip.addEventListener('click', function () {
                if (symptomInput.value.trim() === '') {
                    symptomInput.value = text;
                } else if (!symptomInput.value.includes(text)) {
                    symptomInput.value += ', ' + text;
                }
                chip.classList.toggle('selected');
                symptomInput.focus();
            });
            chipsContainer.appendChild(chip);
        });
    }

    if (categorySelect) {
        categorySelect.addEventListener('change', function () {
            const selectedText = categorySelect.options[categorySelect.selectedIndex].text;
            updateSymptomChips(selectedText);
        });

        // Initialize on load
        if (categorySelect.value) {
            const initialText = categorySelect.options[categorySelect.selectedIndex].text;
            updateSymptomChips(initialText);
        }
    }

    // Handle Diagnosis Form Submission
    let currentDiagnosisData = null;

    if (diagForm) {
        diagForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const categoryName = categorySelect ? categorySelect.options[categorySelect.selectedIndex].text : '';
            const brand = document.getElementById('ai_brand') ? document.getElementById('ai_brand').value : '';
            const model = document.getElementById('ai_model') ? document.getElementById('ai_model').value : '';
            const symptoms = symptomInput ? symptomInput.value.trim() : '';
            const soundBehavior = document.getElementById('ai_sound') ? document.getElementById('ai_sound').value : '';
            const ageYears = document.getElementById('ai_age') ? document.getElementById('ai_age').value : '';

            if (!symptoms) {
                alert('Silakan deskripsikan gejala atau keluhan kerusakan peralatan Anda.');
                return;
            }

            // Show loading scanner
            if (formContainer) formContainer.style.display = 'none';
            if (resultCard) resultCard.style.display = 'none';
            if (loadingState) loadingState.style.display = 'block';

            fetch('index.php?page=api_diagnose', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    category_name: categoryName,
                    brand: brand,
                    model: model,
                    symptoms: symptoms,
                    sound_behavior: soundBehavior,
                    age_years: ageYears
                })
            })
            .then(res => res.json())
            .then(res => {
                if (loadingState) loadingState.style.display = 'none';

                if (res.success && res.data) {
                    currentDiagnosisData = {
                        ...res.data,
                        category_name: categoryName,
                        brand: brand,
                        model: model,
                        symptoms: symptoms
                    };
                    renderDiagnosisResult(currentDiagnosisData);
                } else {
                    alert(res.message || 'Terjadi kesalahan saat memproses diagnosa AI. Silakan coba lagi.');
                    if (formContainer) formContainer.style.display = 'block';
                }
            })
            .catch(err => {
                if (loadingState) loadingState.style.display = 'none';
                if (formContainer) formContainer.style.display = 'block';
                alert('Koneksi terganggu. Silakan periksa jaringan Anda.');
                console.error(err);
            });
        });
    }

    // Render Diagnosis Result to UI
    function renderDiagnosisResult(data) {
        if (!resultCard) return;

        // Title & Source
        document.getElementById('res_title').textContent = data.issue_title || 'Hasil Analisis Diagnosa';
        document.getElementById('res_source').innerHTML = `<i class="fa-solid fa-microchip"></i> Dianalisis oleh: <strong>${data.source || 'ServisYuk AI'}</strong>`;

        // Urgency badge
        const urgencyEl = document.getElementById('res_urgency');
        if (urgencyEl) {
            const urgency = data.urgency_level || 'Sedang';
            urgencyEl.textContent = 'Tingkat Urgensi: ' + urgency;
            urgencyEl.className = 'badge ' + (urgency.toLowerCase() === 'tinggi' || urgency.toLowerCase() === 'darurat' ? 'badge-danger' : 'badge-warning');
        }

        // Cost estimate
        document.getElementById('res_cost').textContent = data.estimated_cost_formatted || 'Rp 75.000 - Rp 250.000';
        document.getElementById('res_time').textContent = 'Estimasi Durasi Pengerjaan: ' + (data.estimated_repair_time || '1 - 2 Jam');

        // Probable Causes
        const causesList = document.getElementById('res_causes');
        if (causesList) {
            causesList.innerHTML = '';
            (data.probable_causes || []).forEach(c => {
                const li = document.createElement('li');
                li.textContent = c;
                causesList.appendChild(li);
            });
        }

        // Safety Warnings
        const safetyBox = document.getElementById('res_safety');
        if (safetyBox) {
            safetyBox.textContent = data.safety_warning || 'Pastikan mencabut aliran listrik jika timbul bau hangus atau percikan api.';
        }

        // DIY Checks
        const diyList = document.getElementById('res_diy');
        if (diyList) {
            diyList.innerHTML = '';
            (data.diy_recommendations || []).forEach(d => {
                const li = document.createElement('li');
                li.textContent = d;
                diyList.appendChild(li);
            });
        }

        // AI Summary & Recommended Action
        document.getElementById('res_summary').textContent = data.ai_summary || data.recommended_action || '';

        // Show result card
        resultCard.style.display = 'block';
    }

    // Button: Rediagnose / Reset
    const btnResetDiag = document.getElementById('btn_re_diagnose');
    if (btnResetDiag) {
        btnResetDiag.addEventListener('click', function () {
            if (resultCard) resultCard.style.display = 'none';
            if (formContainer) formContainer.style.display = 'block';
        });
    }

    // Button: 1-Click "Pesan Teknisi Sekarang dengan Diagnosa Ini"
    const btnBookNow = document.getElementById('btn_book_with_diagnosis');
    if (btnBookNow) {
        btnBookNow.addEventListener('click', function () {
            if (!currentDiagnosisData) return;

            btnBookNow.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyiapkan Pesanan...';
            btnBookNow.disabled = true;

            fetch('index.php?page=api_store_prefill', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(currentDiagnosisData)
            })
            .then(res => res.json())
            .then(res => {
                if (res.success && res.redirect_url) {
                    window.location.href = res.redirect_url;
                } else {
                    window.location.href = 'index.php?page=customer_booking_create';
                }
            })
            .catch(() => {
                window.location.href = 'index.php?page=customer_booking_create';
            });
        });
    }
});
