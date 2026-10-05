{{-- Admin → write / edit a news article (PRD §16). --}}
@php($editing = $article->exists)

<x-layouts.admin :title="$editing ? 'Edit article' : 'New article'">
    <form method="POST" action="{{ $editing ? route('admin.news.update', $article) : route('admin.news.store') }}" class="space-y-6"
          x-data="{ status: @js(old('status', $article->status?->value)) }">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-admin.page-header :title="$editing ? $article->title : 'New article'" :back="route('admin.news.index')" back-label="All news"
            :description="$editing && $article->author ? 'Written by '.$article->author->name : null">
            @if ($editing && $article->isPublished())
                <a href="{{ route('news.show', $article) }}" target="_blank" rel="noopener" class="btn-ghost text-sm border border-slate-200">
                    <x-icon name="external-link" class="w-4 h-4" /> View on site
                </a>
            @endif
        </x-admin.page-header>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 space-y-6">
                <div class="card space-y-4">
                    <x-admin.input name="title" label="Headline" :value="$article->title" required maxlength="255" />
                    <x-admin.input name="slug" label="URL slug" :value="$article->slug" maxlength="255" hint="Leave blank to generate from the headline." />
                    <x-admin.textarea name="excerpt" label="Excerpt" :value="$article->excerpt" rows="2" maxlength="400"
                                      hint="Shown on news cards. Leave blank to use the opening of the article." />
                    <x-admin.rich-text name="content" label="Article" :value="$article->content" required />
                </div>

                <x-admin.seo-fields :model="$article" />
            </div>

            <div class="space-y-6">
                <div class="card space-y-4">
                    <h2 class="font-semibold text-moaum-charcoal">Publishing</h2>
                    <x-admin.select name="status" label="Status" :options="$statuses" :value="$article->status" required x-model="status" />
                    @unless ($canPublish)
                        <p class="text-xs text-slate-400 -mt-2">Send the article for Review; an editor with publish rights will put it live.</p>
                    @endunless
                    <div x-show="status === 'scheduled' || status === 'published'" x-cloak>
                        <x-admin.input name="published_at" type="datetime-local" label="Publish date"
                                       :value="$article->published_at?->format('Y-m-d\TH:i')"
                                       hint="Required when scheduling. For Published, blank means now." />
                    </div>
                    @if ($canPublish)
                        <x-admin.checkbox name="featured" label="Feature at the top of the news page" :checked="$article->featured" />
                    @endif
                    <x-admin.select name="news_category_id" label="Category" :options="$categories" :value="$article->news_category_id" placeholder="— None —" />
                    <x-admin.input name="tags" label="Tags" :value="implode(', ', $article->tags ?? [])" hint="Comma-separated." />
                </div>

                <div class="card">
                    <x-admin.media-picker name="featured_image" label="Featured image" :value="$article->featured_image" />
                </div>

                <div class="card">
                    <x-admin.form-actions :cancel="route('admin.news.index')" :submit="$editing ? 'Save article' : 'Create article'" />
                </div>
            </div>
        </div>
    </form>

    @if ($editing)
        @can('delete', $article)
            <div class="card mt-6 flex justify-end">
                <x-admin.delete-button :action="route('admin.news.destroy', $article)" label="Delete article" confirm="Delete this article permanently? Archiving keeps it." />
            </div>
        @endcan
    @endif
</x-layouts.admin>
