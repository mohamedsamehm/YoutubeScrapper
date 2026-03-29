<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Youtube Scrapper') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f7;
            font-family: "Open Sans", sans-serif;
        }

        /* ══════════════════════════════════════════════════
           Spinner
        ══════════════════════════════════════════════════ */
        .spinner {
            display: inline-block;
            width: 17px;
            height: 17px;
            border: 2.5px solid rgba(255, 255, 255, .35);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .65s linear infinite;
            flex-shrink: 0;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ══════════════════════════════════════════════════
           Toast Notification
        ══════════════════════════════════════════════════ */
        .toast-bar {
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%) translateY(20px);
            background: var(--navy);
            color: #fff;
            padding: .7rem 1.6rem;
            border-radius: var(--radius);
            font-size: .88rem;
            font-weight: 600;
            box-shadow: 0 8px 30px rgba(0, 0, 0, .3);
            z-index: 9999;
            opacity: 0;
            pointer-events: none;
            white-space: nowrap;
            transition: opacity .3s, transform .3s;
        }

        .toast-bar.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        .toast-bar.success {
            background: #14532d;
        }

        .toast-bar.error {
            background: #7f1d1d;
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 5rem 1rem;
            color: var(--muted);
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: .3;
            display: block;
        }

        .empty-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--slate);
            margin-bottom: .4rem;
        }

        .empty-sub {
            font-size: .88rem;
        }


        .btn-fetch {
            width: 100%;
            background: #d3493f !important;
            color: #fff !important;
            border: none;
            border-radius: 9px;
            font-size: 1rem;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            transition: background .2s, box-shadow .2s
        }

        .btn-fetch:hover {
            background: #b83a32;
            box-shadow: 0 4px 18px rgba(255, 0, 0, .35);
            color: #fff;
        }

        .btn-fetch:active:not(:disabled) {
            transform: translateY(0);
        }

        .btn-fetch:disabled {
            opacity: .65;
            cursor: not-allowed;
        }

        .btn-stop {
            width: 100%;
            background: #fff;
            border: 1.5px solid #e2e8f0;
            border-radius: 9px;
            font-size: .85rem;
            color: #64748b;
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            margin-top: .5rem;
            transition: border-color .2s, color .2s
        }

        .btn-stop:hover {
            border-color: #d3493f;
            color: #d3493f
        }

        .text-\[\#d3493f\] {
            color: #d3493f !important;
        }

        .text-\[\#707378\] {
            color: #707378 !important
        }

        .bg-\[\#18203a\] {
            background: #18203a
        }

        .card-thumb-inner {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #dde3ed, #eaeff6);
            display: flex;
            align-items: center;
            justify-content: center
        }

        .hero {
            color: #fff;
            padding: 4.5rem 1.5rem 4.5rem;
        }

        .hero h1 {
            font-size: clamp(1.8rem, 4vw, 2.6rem);
            font-weight: 700;
        }

        .hero p {
            font-size: .94rem;
            color: #b1b6c2;
        }

        .input-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 8px 32px rgba(15, 23, 42, .14);
            padding: 1.5rem 1.8rem;
            margin-top: -4.5rem;
            position: relative;
            z-index: 20
        }

        .input-label {
            font-size: .8rem;
            font-weight: 700;
            color: #475569;
            margin-bottom: .4rem;
            display: block
        }

        .cat-textarea {
            width: 100%;
            min-height: 104px;
            border: 1.5px solid #e2e8f0;
            border-radius: 9px;
            font-size: .9rem;
            padding: .7rem 1rem;
            color: #0f172a;
        }

        .cat-textarea:focus {
            outline: none;
            border-color: #d3493f;
        }

        .section-head {
            display: flex;
            flex-direction: column;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700
        }

        .section-meta {
            font-size: .75rem;
            color: #64748b
        }

        .cat-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .cat-tab {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .48rem .85rem;
            border-radius: 99px;
            font-size: .8rem;
            font-weight: 700;
            color: #64748b;
            background: #fff;
            border: 1.5px solid #e2e8f0;
            cursor: pointer;
            transition: all .2s;
            white-space: nowrap
        }

        .cat-tab:hover {
            border-color: #d3493f;
            color: #d3493f
        }

        .cat-tab.active {
            background: #d3493f;
            color: #fff;
            border-color: #d3493f
        }

        .tab-count {
            font-size: .68rem;
            background: rgba(255, 255, 255, .22);
            padding: 1px 5px;
            border-radius: 99px
        }

        .cat-tab:not(.active) .tab-count {
            background: rgba(0, 0, 0, .08);
            color: #94a3b8
        }

        .pagination {
            --bs-pagination-active-bg: #d3493f;
            --bs-pagination-color: #475569;
            --bs-pagination-border-width: 1.5px;
            --bs-pagination-border-color: #e2e8f0;
            --bs-pagination-active-border-color: #d3493f;
        }

        .page-item.disabled {
            opacity: .35;
            cursor: default
        }

        .page-link {
            border-radius: 0.375rem;
            width: 42px;
            height: 42px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .page-item:not(.active) .page-link:hover {
            color: #d3493f;
            border-color: #d3493f
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem
        }

        @media(max-width:1200px) {
            .courses-grid {
                grid-template-columns: repeat(3, 1fr)
            }
        }

        @media(max-width:900px) {
            .courses-grid {
                grid-template-columns: repeat(2, 1fr)
            }
        }

        @media(max-width:520px) {
            .courses-grid {
                grid-template-columns: 1fr
            }
        }


        .course-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 13px;
            overflow: hidden;
            transition: transform .2s, box-shadow .2s;
            display: flex;
            flex-direction: column;
            cursor: pointer
        }

        .course-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(15, 23, 42, .14)
        }

        .card-thumb {
            position: relative;
            aspect-ratio: 16/9;
            background: #e2e8f0;
            overflow: hidden;
            flex-shrink: 0
        }
        .card-thumb img {
            object-fit: cover;
            height: 100%;
        }

        .card-thumb-inner {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #dde3ed, #eaeff6);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.4rem;
            color: #b0b8c9;
        }

        .badge-lessons {
            position: absolute;
            top: 8px;
            left: 8px;
            background: #d3493f;
            color: #fff;
            font-size: .65rem;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 3px
        }

        .badge-duration {
            position: absolute;
            bottom: 8px;
            right: 8px;
            background: #2a2a2b;
            color: #fff;
            font-size: .63rem;
            font-weight: 600;
            padding: 3px 7px;
            border-radius: 4px
        }

        .card-body {
            padding: .8rem;
            flex: 1;
            display: flex;
            flex-direction: column
        }

        .card-title {
            font-size: .85rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.4;
            margin-bottom: .75rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-channel {
            font-size: .74rem;
            color: #828488;
            display: flex;
            align-items: center;
            gap: .3rem;
            margin-bottom: .75rem;
            max-width: 225px;
            line-height: 20px;
            display: flex;
            align-items: baseline;
        }

        .card-footer-row {
            margin-top: auto;
            padding-top: .75rem;
            border-top: 1px solid #ebecef;
            display: flex;
            align-items: center;
            justify-content: space-between
        }

        .card-views {
            font-size: .7rem;
            color: #a5aaae;
            display: flex;
            align-items: center;
            gap: 3px
        }

        .card-cat-badge {
            font-size: .65rem;
            font-weight: 700;
            color: #d3493f;
            background: #fcecee;
            padding: 4px 8px;
            border-radius: 16px;
        }

        .youtube-icon {
            font-size: 32px;
            height: 32px;
            margin-top: -15px;
        }
    </style>
</head>

<body>
    <div class="toast-bar" id="toast"></div>
    <nav class="navbar py-3">
        <div class="container">
            <div class="d-flex align-items-center w-100">
                <a class="navbar-brand d-flex align-items-center text-[#d3493f] font-weight" href="#">
                    <span class="d-inline-block align-text-top me-2 youtube-icon">
                        <i class="fa-brands fa-youtube"></i>
                    </span>
                    Youtube Course Scrapper
                </a>
                <span class="text-[#707378] me-3">|</span>
                <span class="text-[#707378]">Course collection tool</span>
            </div>
        </div>
    </nav>
    <div class="hero bg-[#18203a] position-relative overflow-hidden">
        <div class="container">
            <h1 class="mb-3">Collecting educational courses from YouTube</h1>
            <p>Enter the categories and press Start — the system will automatically compile the
                courses using artificial
                intelligence.</p>
        </div>
    </div>
    <div class="container py-4">
        <div class="input-card mb-5">
            <div class="row g-3 align-items-stretch">
                <div class="col-md-8">
                    <label class="input-label">Enter the categories (each category on a new line).</label>
                    <textarea class="cat-textarea" id="catInput"></textarea>
                </div>

                <div class="col-md-4 d-flex flex-column justify-content-end gap-2">
                    <button class="btn btn-fetch" id="fetchBtn" onclick="startFetch()">
                        <i class="fa-solid fa-play"></i> <span>Start Fetching</span>
                    </button>
                    <button class="btn btn-stop" id="stopBtn" disabled onclick="stopFetch()">
                        <i class="fa-solid fa-stop"></i> Stop
                    </button>
                </div>

            </div>
        </div>
        @if($courses->total() > 0 || $activeCategory !== 'all')
            <div id="coursesSection">
                <div class="d-flex justify-content-between align-items-lg-end flex-column flex-lg-row mb-4">
                    <div class="section-head mb-4 mb-lg-0">
                        <h3 class="section-title">Collected Courses</h3>
                        <p class="section-meta m-0">{{ number_format($courses->total()) }} courses were found in
                            {{ count($categories) }} {{ count($categories) === 1 ? 'category' : 'categories' }}.</p>
                    </div>

                    <div class="cat-tabs">
                        <a href="{{ route('home', array_merge(request()->except(['category', 'page']), ['category' => 'all'])) }}"
                            class="text-decoration-none cat-tab {{ $activeCategory === 'all' ? 'active' : '' }}">All<span
                                class="tab-count"><span class="tab-count">{{ $courses->total() }}</span></span></a>

                        @foreach($categories as $cat)
                            <a href="{{ route('home', array_merge(request()->except(['category', 'page']), ['category' => $cat])) }}"
                                class="text-decoration-none cat-tab {{ $activeCategory === $cat ? 'active' : '' }}">{{$cat}}
                                <span class="tab-count">
                                    @if(isset($counts[$cat]))
                                        <span class="tab-count">{{ $counts[$cat] }}</span>
                                    @endif
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>

                @if($courses->count() > 0)
                    <div class="courses-grid" id="coursesGrid">
                        @foreach ($courses as $course)
                            <a href="{{ $course->youtube_url }}" target="_blank" rel="noopener noreferrer"
                                class="card course-card text-decoration-none">
                                <div class="card-thumb">
                                    @if ($course->thumbnail_url)
                                        <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="card-img-top" alt="">
                                    @else
                                        <div class="card-thumb-inner">
                                            <i class="fa-regular fa-circle-play"></i>
                                        </div>
                                    @endif
                                    <span class="badge-lessons">{{$course->video_count}} lessons</span>
                                    <span class="badge-duration"><i class="fa-regular fa-clock"></i> {{$course->playlist_duration}}</span>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">{{$course->title}}</h5>
                                    <div class="card-channel"><i class="fa-regular fa-user"></i>{{$course->channel_name}}</div>
                                    <div class="card-footer-row">
                                        <span class="card-views"><i class="fa-regular fa-eye"></i>{{$course->formatted_view_count}} Views</span>
                                        <span class="card-cat-badge">{{$course->category}}</span>
                                    </div>

                                </div>
                            </a>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    @if($courses->hasPages())
                        <nav aria-label="Page navigation" class="py-4 mt-4">
                            <ul class="pagination gap-3 align-items-center justify-content-center">
                                @if($courses->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link" aria-label="Previous">
                                            <span aria-hidden="true"><i class="fa-solid fa-arrow-left"></i></span>
                                        </span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $courses->previousPageUrl() }}" aria-label="Previous">
                                            <span aria-hidden="true"><i class="fa-solid fa-arrow-left"></i></span>
                                        </a>
                                    </li>
                                @endif

                                @php
                                    $current = $courses->currentPage();
                                    $last = $courses->lastPage();
                                @endphp

                                {{-- First pages --}}
                                @for ($p = 1; $p <= min(3, $last); $p++)
                                    <li class="page-item {{ $p == $current ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $courses->url($p) }}">{{ $p }}</a>
                                    </li>
                                @endfor

                                @if ($last > 4)
                                    @if ($current > 4)
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    @endif
                                @endif

                                {{-- Middle pages --}}
                                @for ($p = max(4, $current - 1); $p <= min($last - 1, $current + 1); $p++)
                                    @if ($p > 3 && $p < $last)
                                        <li class="page-item {{ $p == $current ? 'active' : '' }}">
                                            <a class="page-link" href="{{ $courses->url($p) }}">{{ $p }}</a>
                                        </li>
                                    @endif
                                @endfor

                                {{-- Last page --}}
                                @if ($last > 3)
                                    @if ($current < $last - 2)
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    @endif

                                    <li class="page-item {{ $current == $last ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $courses->url($last) }}">{{ $last }}</a>
                                    </li>
                                @endif

                                @if($courses->hasMorePages())
                                    <li class="page-item">
                                        <a href="{{ $courses->nextPageUrl() }}" class="page-link" aria-label="Next">
                                            <span aria-hidden="true"><i class="fa-solid fa-arrow-right"></i></span>
                                        </a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                        <span class="page-link" aria-label="Next">
                                            <span aria-hidden="true"><i class="fa-solid fa-arrow-right"></i></span>
                                        </span>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    @endif
                @else
                    <div class="empty-state">
                        <i class="bi bi-search empty-icon"></i>
                        <div class="empty-title">No results were found for your search.</div>
                        <p class="empty-sub">Try a different category or another search term.</p>
                    </div>
                @endif
            </div>
        @else
            {{-- Initial empty state --}}
            <div class="empty-state">
                <i class="fa-solid empty-icon fa-triangle-exclamation" style="color:var(--red);opacity:.6;"></i>
                <div class="empty-title">No courses have been collected yet.</div>
                <p class="empty-sub">
                    Enter the categories at the top and press <strong>Start Fetch</strong> o start automatically extracting
                    educational courses.
                </p>
            </div>
        @endif
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <script>
        /* ── CSRF token ─────────────────────────────────────────────────────── */
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;
        let controller = null;

        /* ── Toast helper ───────────────────────────────────────────────────── */
        function toast(msg, type = '') {
            const el = document.getElementById('toast');
            el.textContent = msg;
            el.className = 'toast-bar show ' + type;
            clearTimeout(el._t);
            el._t = setTimeout(() => el.className = 'toast-bar', 3800);
        }

        /* ── Start Fetch ────────────────────────────────────────────────────── */
        async function startFetch() {
            const categories = document.getElementById('catInput').value.trim();

            if (!categories) {
                toast('⚠️ Please enter categories', 'error');
                return;
            }

            const startBtn = document.getElementById('fetchBtn');
            const stopBtn = document.getElementById('stopBtn');

            controller = new AbortController();

            startBtn.disabled = true;
            stopBtn.disabled = false;

            startBtn.innerHTML = '<span class="spinner"></span> Fetching...';

            try {
                const res = await fetch('/fetch', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ categories }),
                    signal: controller.signal
                });

                const data = await res.json();

                toast('✓ Done', 'success');

            } catch (err) {
                if (err.name === 'AbortError') {
                    toast('⛔ Stopped', 'error');
                } else {
                    toast(err.message, 'error');
                }
            } finally {
                startBtn.disabled = false;
                stopBtn.disabled = true;

                startBtn.innerHTML = '<i class="fa-solid fa-play"></i> Start Fetching';
            }
        }

        function stopFetch() {
            if (controller) {
                controller.abort();
            }
        }
    </script>

</body>

</html>