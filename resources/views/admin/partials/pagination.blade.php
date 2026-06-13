@if ($paginator->hasPages())
    <div class="pagination-container">
        <div class="pagination-nav">
            <ul class="pagination">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled">
                        <button class="page-link page-nav-button" disabled aria-label="Anterior">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                    </li>
                @else
                    <li class="page-item">
                        <a href="{{ $paginator->previousPageUrl() }}" class="page-link page-nav-button" aria-label="Anterior">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                        </a>
                    </li>
                @endif

                {{-- Page Status --}}
                <li class="pagination-info">
                    <span class="pagination-text">
                        Página <strong>{{ $paginator->currentPage() }}</strong> de <strong>{{ $paginator->lastPage() }}</strong>
                    </span>
                </li>

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a href="{{ $paginator->nextPageUrl() }}" class="page-link page-nav-button" aria-label="Siguiente">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <button class="page-link page-nav-button" disabled aria-label="Siguiente">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                    </li>
                @endif
            </ul>
        </div>
    </div>
@else
    <div class="pagination-container">
        <div class="pagination-nav">
            <ul class="pagination">
                <li class="page-item disabled">
                    <button class="page-link page-nav-button" disabled aria-label="Anterior">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                    </button>
                </li>
                <li class="pagination-info">
                    <span class="pagination-text">
                        Página <strong>1</strong> de <strong>1</strong>
                    </span>
                </li>
                <li class="page-item disabled">
                    <button class="page-link page-nav-button" disabled aria-label="Siguiente">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                </li>
            </ul>
        </div>
    </div>
@endif
