@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-6 grid gap-3 sm:grid-cols-3">
        <div class="workflow-card">
            <span class="workflow-step">1</span>
            <div>
                <p class="font-medium text-stone-900">Semester results</p>
                <p class="mt-0.5 text-sm text-stone-600">Upload composite Excel → get TCU, TQP, GPA per student</p>
            </div>
        </div>
        <div class="workflow-card">
            <span class="workflow-step">2</span>
            <div>
                <p class="font-medium text-stone-900">Session summary</p>
                <p class="mt-0.5 text-sm text-stone-600">Merge 1st & 2nd semester into one session record</p>
            </div>
        </div>
        <div class="workflow-card">
            <span class="workflow-step">3</span>
            <div>
                <p class="font-medium text-stone-900">Degree summary</p>
                <p class="mt-0.5 text-sm text-stone-600">Combine all years → CTC, CTQP, FCGPA, class of degree</p>
            </div>
        </div>
    </div>

    <nav class="mb-6 flex border-b border-stone-200 bg-white px-1" aria-label="Workflows">
        <button type="button" data-tab="semester" class="tab-btn active -mb-px border-b-2 border-stone-900 px-4 py-3 text-sm font-medium text-stone-900">
            Semester results
        </button>
        <button type="button" data-tab="session" class="tab-btn -mb-px border-b-2 border-transparent px-4 py-3 text-sm font-medium text-stone-500 hover:text-stone-700">
            Session summary
        </button>
        <button type="button" data-tab="degree" class="tab-btn -mb-px border-b-2 border-transparent px-4 py-3 text-sm font-medium text-stone-500 hover:text-stone-700">
            Degree summary
        </button>
    </nav>

    {{-- Semester --}}
    <section id="panel-semester" class="tab-panel space-y-6">
        <div class="grid gap-6 lg:grid-cols-5">
            <div class="lg:col-span-2 space-y-4">
                <div class="panel">
                    <h2 class="panel-title">Semester composite sheet</h2>
                    <p class="panel-desc">
                        Upload your official semester Excel template. Resulty fills in the TQP and GPA columns and returns the same document layout — course headers, student rows, and signature blocks unchanged.
                    </p>

                    <form id="semester-form" class="mt-5 space-y-4" enctype="multipart/form-data">
                        @csrf
                        <label class="dropzone block cursor-pointer rounded-md border-2 border-dashed border-stone-300 bg-stone-50 px-4 py-8 text-center transition hover:border-stone-400 hover:bg-stone-100">
                            <input type="file" name="semester_file" accept=".xlsx,.xls" class="hidden" required>
                            <p class="text-sm font-medium text-stone-800">Choose or drop Excel file</p>
                            <p class="mt-1 text-xs text-stone-500">Composite result sheet (.xlsx / .xls)</p>
                            <p class="dropzone-filename mt-2 text-sm font-medium text-stone-700"></p>
                        </label>

                        <div class="flex flex-col gap-2 sm:flex-row">
                            <button type="button" id="semester-preview-btn" class="btn-secondary flex-1">Preview calculations</button>
                            <button type="submit" class="btn-primary flex-1">Download processed file</button>
                        </div>
                    </form>
                </div>

                <div class="panel">
                    <h3 class="text-sm font-semibold text-stone-900">What gets calculated</h3>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between border-b border-stone-100 py-2">
                            <dt class="text-stone-600">TCU</dt>
                            <dd class="font-medium text-stone-900">Sum of credit units for the semester</dd>
                        </div>
                        <div class="flex justify-between border-b border-stone-100 py-2">
                            <dt class="text-stone-600">TQP</dt>
                            <dd class="font-medium text-stone-900">Σ (credit × grade point)</dd>
                        </div>
                        <div class="flex justify-between py-2">
                            <dt class="text-stone-600">GPA</dt>
                            <dd class="font-medium text-stone-900">TQP ÷ TCU</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="lg:col-span-3">
                <div id="semester-preview" class="panel min-h-[520px]">
                    @include('partials.preview-placeholder')
                </div>
            </div>
        </div>

        @include('partials.reference-tables')
    </section>

    {{-- Session --}}
    <section id="panel-session" class="tab-panel hidden space-y-6">
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 panel">
                <h2 class="panel-title">Session summary</h2>
                <p class="panel-desc">
                    A session is two semesters combined. Upload both semester files and Resulty merges them by student (matric number or serial number).
                </p>

                <form id="session-form" class="mt-5 space-y-4" action="{{ route('session.process') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="dropzone file-field">
                            <span class="file-field-label">1st semester Excel</span>
                            <input type="file" name="first_semester" accept=".xlsx,.xls" class="file-input" required>
                            <p class="dropzone-filename file-field-name"></p>
                        </label>
                        <label class="dropzone file-field">
                            <span class="file-field-label">2nd semester Excel</span>
                            <input type="file" name="second_semester" accept=".xlsx,.xls" class="file-input" required>
                            <p class="dropzone-filename file-field-name"></p>
                        </label>
                    </div>

                    <label class="file-field block">
                        <span class="file-field-label">Biodata CSV (optional)</span>
                        <input type="file" name="biodata_file" accept=".csv,.txt" class="file-input mt-2">
                        <span class="mt-1 block text-xs text-stone-500">Columns: reg_no, name, state, dob, sex</span>
                    </label>

                    <button type="submit" class="btn-primary">Generate session summary (Excel)</button>
                </form>
            </div>

            <div class="panel">
                <h3 class="text-sm font-semibold text-stone-900">Session calculation</h3>
                <div class="mt-4 space-y-4 text-sm text-stone-600">
                    <p>For each student across both semesters:</p>
                    <ul class="list-inside list-disc space-y-1.5 pl-1">
                        <li><strong class="text-stone-800">TC</strong> = Sem 1 TCU + Sem 2 TCU</li>
                        <li><strong class="text-stone-800">TQP</strong> = Sem 1 TQP + Sem 2 TQP</li>
                        <li><strong class="text-stone-800">GPA</strong> = TQP ÷ TC</li>
                    </ul>
                    <div class="rounded-md border border-stone-200 bg-stone-50 p-3 font-mono text-xs text-stone-700">
                        Example: 19 + 19 = 38 TC<br>
                        53 + 49 = 102 TQP<br>
                        102 ÷ 38 = 2.68 GPA
                    </div>
                </div>
            </div>
        </div>

        @include('partials.reference-tables')
    </section>

    {{-- Degree --}}
    <section id="panel-degree" class="tab-panel hidden space-y-6">
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 panel">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="panel-title">Degree summary</h2>
                        <p class="panel-desc">
                            Add each academic year with its two semester files. Resulty produces a Word document with year-by-year TC/TQP/GPA, cumulative totals, and signature lines.
                        </p>
                    </div>
                    <button type="button" id="add-year-btn" class="btn-secondary shrink-0">+ Add year</button>
                </div>

                <form id="degree-form" class="mt-5 space-y-4" action="{{ route('degree.process') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div id="year-blocks" class="space-y-3"></div>

                    <label class="file-field block">
                        <span class="file-field-label">Biodata CSV (optional)</span>
                        <input type="file" name="biodata_file" accept=".csv,.txt" class="file-input mt-2">
                        <span class="mt-1 block text-xs text-stone-500">Used to populate name, state, date of birth, and sex on the summary sheet</span>
                    </label>

                    <button type="submit" class="btn-primary">Generate degree summary (.docx)</button>
                </form>
            </div>

            <div class="space-y-4">
                <div class="panel">
                    <h3 class="text-sm font-semibold text-stone-900">Cumulative columns</h3>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between border-b border-stone-100 py-2">
                            <dt class="font-medium text-stone-800">CTC</dt>
                            <dd class="text-stone-600">Total credits (all years)</dd>
                        </div>
                        <div class="flex justify-between border-b border-stone-100 py-2">
                            <dt class="font-medium text-stone-800">CTQP</dt>
                            <dd class="text-stone-600">Total quality points</dd>
                        </div>
                        <div class="flex justify-between border-b border-stone-100 py-2">
                            <dt class="font-medium text-stone-800">FCGPA</dt>
                            <dd class="text-stone-600">CTQP ÷ CTC</dd>
                        </div>
                        <div class="flex justify-between py-2">
                            <dt class="font-medium text-stone-800">Class</dt>
                            <dd class="text-stone-600">Degree classification code</dd>
                        </div>
                    </dl>
                </div>

                <div class="panel">
                    <h3 class="text-sm font-semibold text-stone-900">Class of degree</h3>
                    <table class="ref-table mt-3">
                        <thead>
                            <tr><th>FCGPA</th><th>Class</th></tr>
                        </thead>
                        <tbody>
                            @foreach (config('resulty.class_of_degree') as $band)
                                <tr>
                                    <td>≥ {{ number_format($band['min'], 2) }}</td>
                                    <td>{{ app(\App\Services\Grading\ClassOfDegreeFormatter::class)->plainText($band['code']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
