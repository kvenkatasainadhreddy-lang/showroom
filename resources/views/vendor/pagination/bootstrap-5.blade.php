@if ($paginator->hasPages())
    <nav class="d-flex align-items-center justify-content-between flex-wrap gap-2" aria-label="Pagination">

        {{-- Result count --}}
        <div class="small text-muted">
            Showing <strong>{{ $paginator->firstItem() }}</strong>–<strong>{{ $paginator->lastItem() }}</strong>
            of <strong>{{ $paginator->total() }}</strong> results
        </div>

        {{-- Page links --}}
        <ul class="pagination pagination-sm mb-0">

            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link px-2">&#8249;</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link px-2" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous">&#8249;</a>
                </li>
            @endif

            {{-- Page numbers --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled"><span class="page-link px-2">{{ $element }}</span></li>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link px-2">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link px-2" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link px-2" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next">&#8250;</a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link px-2">&#8250;</span>
                </li>
            @endif

        </ul>
    </nav>
@endif
