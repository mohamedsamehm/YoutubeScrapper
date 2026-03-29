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
        <link rel="stylesheet" href="{{ asset('css/main.css') }}">
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
                                class="tab-count"><span class="tab-count">{{ $counts->sum() }}</span></span></a>

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
        <script src="{{ asset('js/main.js') }}"></script>
</body>

</html>