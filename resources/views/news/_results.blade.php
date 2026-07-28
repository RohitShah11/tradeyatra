<div class="news-grid">
    @forelse($articles as $article)
        <article class="panel news-card {{ $loop->first && $articles->currentPage() === 1 ? 'featured' : '' }}">
            <div class="news-meta">
                <span class="news-source">{{ $article['source'] }}</span><span>&bull;</span>
                <time datetime="{{ $article['published_at'] }}">{{ \Illuminate\Support\Carbon::parse($article['published_at'])->diffForHumans() }}</time>
            </div>
            <h2>{{ $article['title'] }}</h2>
            @if(($article['sentiment'] ?? null) || !empty($article['tickers'] ?? []))
                <div class="news-signals" aria-label="Article trading signals">
                    @if($article['sentiment'] ?? null)
                        <span class="news-sentiment {{ $article['sentiment'] }}" title="{{ ($article['sentiment_method'] ?? '') === 'alpha_vantage' ? 'Alpha Vantage sentiment' : 'Estimated from the headline' }}{{ isset($article['sentiment_score']) ? ': '.number_format($article['sentiment_score'], 3) : '' }}">{{ $article['sentiment'] }}</span>
                    @endif
                    @foreach(($article['tickers'] ?? []) as $ticker)<span class="news-ticker">{{ $ticker }}</span>@endforeach
                </div>
            @endif
            @if($article['description'])<p>{{ $article['description'] }}</p>@endif
            <a class="news-read" href="{{ $article['url'] }}" target="_blank" rel="noopener noreferrer"><span>Read full story</span><span aria-hidden="true">&nearr;</span></a>
        </article>
    @empty
        <div class="panel news-empty">
            <svg class="icon"><use href="#icon-news"></use></svg>
            <h2>No headlines found</h2>
            <p class="muted">{{ $query ? 'Try a broader search or another category.' : 'Live headlines could not be loaded right now. Please try again.' }}</p>
        </div>
    @endforelse
</div>

@if($articles->hasPages())
    <nav class="news-pagination" aria-label="News pages">
        <a class="btn secondary {{ $articles->onFirstPage() ? 'disabled' : '' }}" href="{{ $articles->previousPageUrl() ?: '#' }}">Previous</a>
        <span class="news-page-state">Page {{ $articles->currentPage() }} of {{ $articles->lastPage() }}</span>
        <a class="btn secondary {{ $articles->hasMorePages() ? '' : 'disabled' }}" href="{{ $articles->nextPageUrl() ?: '#' }}">Next</a>
    </nav>
@endif
