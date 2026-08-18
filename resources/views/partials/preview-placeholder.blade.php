<div class="space-y-5">
    <div>
        <h3 class="text-sm font-semibold text-stone-900">Preview</h3>
        <p class="mt-1 text-sm text-stone-500">Upload a semester file and click Preview calculations to see student results here.</p>
    </div>

    <div class="overflow-hidden rounded-md border border-stone-200">
        <table class="preview-table">
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>Reg No</th>
                    <th>Name</th>
                    <th>TCU</th>
                    <th>TQP</th>
                    <th>GPA</th>
                </tr>
            </thead>
            <tbody>
                @foreach ([
                    ['1', '—', '—', '19', '—', '—'],
                    ['2', '—', '—', '19', '—', '—'],
                    ['3', '—', '—', '19', '—', '—'],
                ] as $row)
                    <tr class="text-stone-400">
                        @foreach ($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="text-xs text-stone-400">Sample layout — values appear after preview.</p>

    <div class="grid gap-3 sm:grid-cols-2">
        <div class="rounded-md border border-stone-200 bg-stone-50 p-3">
            <p class="text-xs font-medium uppercase tracking-wide text-stone-500">Per course</p>
            <p class="mt-1 font-mono text-sm text-stone-800">points = credit × grade point</p>
        </div>
        <div class="rounded-md border border-stone-200 bg-stone-50 p-3">
            <p class="text-xs font-medium uppercase tracking-wide text-stone-500">Per student</p>
            <p class="mt-1 font-mono text-sm text-stone-800">GPA = TQP ÷ TCU</p>
        </div>
    </div>
</div>
