{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ config('moaum.company.name') }} — News</title>
        <link>{{ route('news.index') }}</link>
        <description>News, announcements and updates from {{ config('moaum.company.name') }}.</description>
        <language>en</language>
        <atom:link href="{{ route('news.feed') }}" rel="self" type="application/rss+xml" />
@foreach ($articles as $article)
        <item>
            <title>{{ $article->title }}</title>
            <link>{{ route('news.show', $article) }}</link>
            <guid isPermaLink="true">{{ route('news.show', $article) }}</guid>
            <pubDate>{{ $article->published_at->toRssString() }}</pubDate>
@if ($article->category)
            <category>{{ $article->category->name }}</category>
@endif
            <description>{{ $article->summary(300) }}</description>
        </item>
@endforeach
    </channel>
</rss>
