@if($wasteItems->hasPages())
    <div class="pagination">
        @if($wasteItems->onFirstPage())
            <span class="page-link disabled">&laquo; Previous</span>
        @else
            <a href="{{ $wasteItems->previousPageUrl() }}" class="page-link">&laquo; Previous</a>
        @endif
        @foreach($wasteItems->getUrlRange(1, $wasteItems->lastPage()) as $page => $url)
            @if($page == $wasteItems->currentPage())
                <span class="page-link active">{{ $page }}</span>
            @else
                <a href="{{ $url }}" class="page-link">{{ $page }}</a>
            @endif
        @endforeach
        @if($wasteItems->hasMorePages())
            <a href="{{ $wasteItems->nextPageUrl() }}" class="page-link">Next &raquo;</a>
        @else
            <span class="page-link disabled">Next &raquo;</span>
        @endif
    </div>
@endif
