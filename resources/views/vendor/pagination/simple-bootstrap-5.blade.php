@if ($paginator->hasPages())
    <nav aria-label="Pagination">
        <ul class="pagination pagination-sm mb-0">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link px-2">&#8249; Prev</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link px-2" href="{{ $paginator->previousPageUrl() }}" rel="prev">&#8249; Prev</a>
                </li>
            @endif

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link px-2" href="{{ $paginator->nextPageUrl() }}" rel="next">Next &#8250;</a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link px-2">Next &#8250;</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
