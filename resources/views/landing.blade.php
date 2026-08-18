@extends('layouts.marketing')

@section('title', 'Resulty — Composite result processing')

@section('content')
    <section class="landing-hero relative overflow-hidden">
        <div class="landing-grid" aria-hidden="true"></div>
        <div class="landing-orb landing-orb-1" aria-hidden="true"></div>
        <div class="landing-orb landing-orb-2" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-6xl px-4 pb-20 pt-8 sm:px-6 sm:pb-28 sm:pt-12 lg:pb-32">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                <div class="reveal">
                    <span class="landing-badge">University result processing</span>
                    <h1 class="landing-headline mt-6">
                        Composite results,<br>
                        calculated <span class="landing-gradient-text">accurately</span>.
                    </h1>
                    <p class="landing-lead mt-6 max-w-lg">
                        Upload semester Excel sheets — get TCU, TQP, and GPA filled in automatically. Merge sessions, export degree summaries, and keep every signature block intact.
                    </p>
                    <div class="mt-9 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <a href="{{ route('results') }}" class="landing-btn-glow landing-btn-lg group">
                            Start processing results
                            <svg class="ml-2 h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                        <a href="#how-it-works" class="landing-btn-ghost landing-btn-lg">See how it works</a>
                    </div>

                    <dl class="landing-stats mt-12 grid grid-cols-3 gap-4 sm:gap-8">
                        <div>
                            <dt class="landing-stat-value">3</dt>
                            <dd class="landing-stat-label">Processing stages</dd>
                        </div>
                        <div>
                            <dt class="landing-stat-value">A–E</dt>
                            <dd class="landing-stat-label">Grading scale</dd>
                        </div>
                        <div>
                            <dt class="landing-stat-value">100%</dt>
                            <dd class="landing-stat-label">Template preserved</dd>
                        </div>
                    </dl>
                </div>

                <div class="reveal reveal-delay-2 relative">
                    <div class="landing-preview float-slow">
                        <div class="landing-preview-glow" aria-hidden="true"></div>
                        <div class="landing-preview-card">
                            <div class="landing-preview-header">
                                <div>
                                    <p class="landing-preview-label">Live preview</p>
                                    <p class="landing-preview-title">Semester composite sheet</p>
                                </div>
                                <span class="landing-live-dot"><span></span>Ready</span>
                            </div>
                            <div class="space-y-3 p-5">
                                <div class="landing-preview-row">
                                    <span class="landing-preview-muted">Student</span>
                                    <span class="landing-preview-value">Chinedu Eze</span>
                                </div>
                                <div class="landing-preview-row">
                                    <span class="landing-preview-muted">TCU</span>
                                    <span class="landing-preview-value font-mono">19</span>
                                </div>
                                <div class="landing-preview-row">
                                    <span class="landing-preview-muted">TQP</span>
                                    <span class="landing-preview-accent font-mono">53</span>
                                </div>
                                <div class="landing-preview-row border-none pb-0">
                                    <span class="landing-preview-muted">GPA</span>
                                    <span class="landing-gpa-pill">2.79</span>
                                </div>
                            </div>
                            <div class="landing-preview-footer">
                                <div class="landing-progress-track">
                                    <div class="landing-progress-bar h-full rounded-full"></div>
                                </div>
                                <p class="mt-2 text-xs text-stone-500">TQP & GPA calculated from course grades</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="how-it-works" class="landing-section-alt py-20 sm:py-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="reveal max-w-2xl">
                <p class="landing-eyebrow">Workflow</p>
                <h2 class="landing-section-title mt-3">Three steps from upload to report</h2>
                <p class="landing-lead mt-3">Built around the official composite result workflow used in Nigerian universities.</p>
            </div>

            <div class="mt-14 grid gap-6 md:grid-cols-3">
                @foreach ([
                    ['n' => '01', 'title' => 'Semester results', 'desc' => 'Upload composite Excel. TQP and GPA are computed per student and written back into the same template.', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                    ['n' => '02', 'title' => 'Session summary', 'desc' => 'Combine 1st and 2nd semester files. TC and TQP are summed; session GPA = TQP ÷ TC.', 'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4'],
                    ['n' => '03', 'title' => 'Degree summary', 'desc' => 'Stack academic years and export a Word document with FCGPA, class of degree, and signatures.', 'icon' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z'],
                ] as $i => $step)
                    <article class="landing-feature-card reveal" style="--delay: {{ $i * 100 }}ms">
                        <div class="landing-feature-icon">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $step['icon'] }}"/></svg>
                        </div>
                        <span class="landing-feature-num">{{ $step['n'] }}</span>
                        <h3 class="landing-feature-title mt-4">{{ $step['title'] }}</h3>
                        <p class="landing-feature-desc mt-2">{{ $step['desc'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-20 sm:py-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                <div class="reveal">
                    <h2 class="landing-section-title">What stays intact</h2>
                    <ul class="mt-8 space-y-4">
                        @foreach (['University, faculty, and department headers', 'Course names, codes, and credit unit rows', 'Score and grade columns for every course', 'Head of Department / Dean / Registrar signature blocks'] as $item)
                            <li class="landing-check-item">{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>

                <div class="reveal reveal-delay-2">
                    <div class="landing-glass-panel">
                        <h3 class="landing-eyebrow">Grading scale</h3>
                        <dl class="mt-6 grid grid-cols-5 gap-3">
                            @foreach (['A' => ['pts' => 5, 'color' => 'emerald'], 'B' => ['pts' => 4, 'color' => 'teal'], 'C' => ['pts' => 3, 'color' => 'sky'], 'D' => ['pts' => 2, 'color' => 'amber'], 'E' => ['pts' => 1, 'color' => 'rose']] as $grade => $meta)
                                <div class="landing-grade-tile landing-grade-{{ $meta['color'] }}">
                                    <dt class="text-xl font-bold text-stone-900">{{ $grade }}</dt>
                                    <dd class="text-xs text-stone-500">{{ $meta['pts'] }} pts</dd>
                                </div>
                            @endforeach
                        </dl>
                        <div class="landing-formula-box mt-6">
                            GPA = TQP ÷ TCU · FCGPA = CTQP ÷ CTC
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-cta-section relative overflow-hidden py-20 sm:py-24">
        <div class="landing-cta-glow" aria-hidden="true"></div>
        <div class="relative mx-auto max-w-3xl px-4 text-center sm:px-6 reveal">
            <h2 class="landing-section-title text-3xl sm:text-4xl">Ready to process your results?</h2>
            <p class="landing-lead mx-auto mt-4 max-w-lg">
                Upload your composite Excel files and download processed documents in minutes.
            </p>
            <a href="{{ route('results') }}" class="landing-btn-glow landing-btn-lg mt-10 inline-flex">
                Go to results workspace
                <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </section>
@endsection
