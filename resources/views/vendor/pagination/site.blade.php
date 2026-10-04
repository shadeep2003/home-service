
@if($paginator->total() > 0)
<nav class="site-pagination" aria-label="Pagination"><p>Showing <strong>{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</strong> of <strong>{{ $paginator->total() }}</strong></p>

@if($paginator->hasPages())<div class="pagination-pages">

@if($paginator->onFirstPage())<span class="pagination-disabled">← Previous</span>
@else
<a href="{{ $paginator->previousPageUrl() }}" rel="prev">← Previous</a>
@endif


@foreach($elements as $element)

@if(is_string($element))<span class="pagination-gap">{{ $element }}</span>
@endif


@if(is_array($element))
@foreach($element as $page => $url)
@if($page == $paginator->currentPage())<span class="pagination-current" aria-current="page">{{ $page }}</span>
@else
<a href="{{ $url }}" aria-label="Go to page {{ $page }}">{{ $page }}</a>
@endif

@endforeach

@endif


@endforeach


@if($paginator->hasMorePages())<a href="{{ $paginator->nextPageUrl() }}" rel="next">Next →</a>
@else
<span class="pagination-disabled">Next →</span>
@endif

</div>
@endif
</nav>

@endif

