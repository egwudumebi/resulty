document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    document.querySelectorAll('.tab-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach((b) => {
                b.classList.remove('active', 'border-stone-900', 'text-stone-900');
                b.classList.add('border-transparent', 'text-stone-500');
            });
            document.querySelectorAll('.tab-panel').forEach((p) => p.classList.add('hidden'));
            btn.classList.add('active', 'border-stone-900', 'text-stone-900');
            btn.classList.remove('border-transparent', 'text-stone-500');
            document.getElementById(`panel-${btn.dataset.tab}`)?.classList.remove('hidden');
        });
    });

    document.querySelectorAll('.dropzone').forEach((zone) => {
        const input = zone.querySelector('input[type="file"]');
        const label = zone.querySelector('.dropzone-filename');

        const showName = () => {
            if (label && input?.files?.[0]) {
                label.textContent = input.files[0].name;
            }
        };

        zone.addEventListener('dragover', (e) => {
            e.preventDefault();
            zone.classList.add('is-dragover');
        });

        zone.addEventListener('dragleave', () => {
            zone.classList.remove('is-dragover');
        });

        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.classList.remove('is-dragover');
            if (input && e.dataTransfer?.files?.[0]) {
                input.files = e.dataTransfer.files;
                showName();
            }
        });

        input?.addEventListener('change', showName);
    });

    const previewBtn = document.getElementById('semester-preview-btn');
    const semesterForm = document.getElementById('semester-form');
    const previewPanel = document.getElementById('semester-preview');

    previewBtn?.addEventListener('click', async () => {
        const input = semesterForm?.querySelector('input[name="semester_file"]');
        if (!input?.files?.[0]) {
            alert('Please select a semester Excel file first.');
            return;
        }

        const formData = new FormData();
        formData.append('semester_file', input.files[0]);
        formData.append('_token', csrf);

        previewPanel.innerHTML = '<div class="flex h-48 items-center justify-center"><div class="spinner h-6 w-6 rounded-full border-2 border-stone-200 border-t-stone-600"></div></div>';

        try {
            const res = await fetch('/semester/preview', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: formData,
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Preview failed');

            previewPanel.innerHTML = renderPreview(data);
        } catch (err) {
            previewPanel.innerHTML = `<div class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800">${err.message}</div>`;
        }
    });

    semesterForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const input = semesterForm.querySelector('input[name="semester_file"]');
        if (!input?.files?.[0]) return;

        const formData = new FormData(semesterForm);
        const btn = semesterForm.querySelector('button[type="submit"]');
        const original = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Processing…';

        try {
            const res = await fetch('/semester/process', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf },
                body: formData,
            });

            if (!res.ok) {
                const contentType = res.headers.get('content-type') || '';
                if (contentType.includes('application/json')) {
                    const err = await res.json();
                    throw new Error(err.message || err.errors?.semester_file?.[0] || 'Processing failed');
                }
                throw new Error('Processing failed');
            }

            const blob = await res.blob();
            downloadBlob(blob, `semester-result-${Date.now()}.xlsx`);
        } catch (err) {
            alert(err.message);
        } finally {
            btn.disabled = false;
            btn.textContent = original;
        }
    });

    const yearBlocks = document.getElementById('year-blocks');
    const addYearBtn = document.getElementById('add-year-btn');
    let yearCount = 0;

    const addYearBlock = (yearNum = null) => {
        yearCount++;
        const year = yearNum ?? yearCount;
        const block = document.createElement('div');
        block.className = 'year-block rounded-md border border-stone-300 bg-stone-50 p-4';
        block.innerHTML = `
            <div class="mb-3 flex items-center justify-between">
                <label class="text-sm font-medium text-stone-800">
                    Year
                    <select name="year[]" class="ml-2 rounded border border-stone-300 bg-white px-2 py-1 text-sm">${[1, 2, 3, 4].map((y) => `<option value="${y}" ${y === year ? 'selected' : ''}>${y}</option>`).join('')}</select>
                </label>
                <button type="button" class="remove-year text-xs text-stone-500 hover:text-red-700">Remove</button>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="dropzone file-field block">
                    <span class="file-field-label">1st semester</span>
                    <input type="file" name="first_semester[]" accept=".xlsx,.xls" class="file-input mt-2" required>
                    <p class="dropzone-filename file-field-name"></p>
                </label>
                <label class="dropzone file-field block">
                    <span class="file-field-label">2nd semester</span>
                    <input type="file" name="second_semester[]" accept=".xlsx,.xls" class="file-input mt-2" required>
                    <p class="dropzone-filename file-field-name"></p>
                </label>
            </div>
        `;
        yearBlocks?.appendChild(block);

        block.querySelector('.remove-year')?.addEventListener('click', () => block.remove());

        block.querySelectorAll('.dropzone').forEach((zone) => {
            const input = zone.querySelector('input[type="file"]');
            const label = zone.querySelector('.dropzone-filename');
            input?.addEventListener('change', () => {
                if (label && input.files?.[0]) label.textContent = input.files[0].name;
            });
        });
    };

    addYearBtn?.addEventListener('click', () => addYearBlock());
    addYearBlock(1);
    addYearBlock(2);
});

function renderPreview(data) {
    const meta = data.metadata || {};
    const rows = (data.students || []).map((s) => `
        <tr>
            <td>${s.serial ?? '—'}</td>
            <td>${s.reg_no || '—'}</td>
            <td>${s.name || '—'}</td>
            <td>${s.tcu}</td>
            <td>${s.tqp}</td>
            <td><span class="gpa-badge">${s.gpa ?? '—'}</span></td>
        </tr>
    `).join('');

    return `
        <div class="space-y-4">
            <div class="border-b border-stone-200 pb-4">
                <p class="font-medium text-stone-900">${meta.university || '—'}</p>
                <p class="text-sm text-stone-600">${meta.department || ''}${meta.title ? ' · ' + meta.title : ''}</p>
                <p class="mt-1 text-xs text-stone-500">${data.course_count} courses, ${data.student_count} students</p>
            </div>
            <div class="overflow-x-auto">
                <table class="preview-table">
                    <thead><tr><th>S/N</th><th>Reg No</th><th>Name</th><th>TCU</th><th>TQP</th><th>GPA</th></tr></thead>
                    <tbody>${rows || '<tr><td colspan="6" class="text-stone-500">No students found</td></tr>'}</tbody>
                </table>
            </div>
            ${data.student_count > 10 ? '<p class="text-xs text-stone-500">First 10 of ' + data.student_count + ' students shown.</p>' : ''}
        </div>
    `;
}

function downloadBlob(blob, filename) {
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    URL.revokeObjectURL(url);
}
