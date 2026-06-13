@if ($paginator->hasPages())
    <div class="pagination-container">
        <nav class="pagination-nav" role="navigation">
            <ul class="pagination">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link page-nav-button">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="15 18l-6-6 6-6"></polyline>
                            </svg>
                        </span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link page-nav-button" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="15 18l-6-6 6-6"></polyline>
                            </svg>
                        </a>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <li class="page-item dots-item" aria-disabled="true">
                            <span class="page-link">{{ $element }}</span>
                        </li>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active" aria-current="page">
                                    <span class="page-link page-number">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link page-number" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link page-nav-button" href="{{ $paginator->nextPageUrl() }}" rel="next">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18l6-6-6-6"></polyline>
                            </svg>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link page-nav-button">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18l6-6-6-6"></polyline>
                            </svg>
                        </span>
                    </li>
                @endif
            </ul>
        </nav>
        <div class="pagination-info">
            <span class="pagination-text">
                Mostrando <strong>{{ ($paginator->currentPage() - 1) * $paginator->perPage() + 1 }}</strong> 
                a <strong>{{ min($paginator->currentPage() * $paginator->perPage(), $paginator->total()) }}</strong> 
                de <strong>{{ $paginator->total() }}</strong> resultados
            </span>
        </div>
    </div>
@endif
