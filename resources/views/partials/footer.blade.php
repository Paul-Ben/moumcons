{{-- Site footer — faithful to prototype-docs/index.html (4-column, slate-900) --}}
<footer class="bg-slate-900 text-slate-300 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">

            {{-- Brand + social --}}
            <div>
                <img src="{{ asset('images/logo.jpg') }}" alt="{{ config('moaum.company.name') }}" class="h-16 w-auto mb-6 brightness-0 invert">
                <p class="text-slate-400 mb-6">The official business and investment arm of {{ config('moaum.company.parent') }}.</p>
                @if ($social = \App\Support\CompanyDetails::socialLinks())
                    <div class="flex flex-wrap gap-3">
                        @foreach ($social as $network => $url)
                            <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ \App\Support\SiteSettings::SOCIAL_NETWORKS[$network] }}"
                               class="w-10 h-10 bg-slate-800 rounded-lg flex items-center justify-center hover:bg-moaum-blue transition">
                                <x-icon :name="$network" class="w-5 h-5" />
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Quick links --}}
            <div>
                <h4 class="text-white font-semibold mb-4">Quick Links</h4>
                <ul class="space-y-3">
                    @foreach ([['About Us', 'about.profile'], ['Our Businesses', 'businesses.index'], ['Services', 'services.index'], ['Projects', 'projects.index'], ['Training', 'training.index'], ['News', 'news.index'], ['Gallery', 'gallery.index'], ['Downloads', 'downloads.index'], ['FAQs', 'faqs.index'], ['Careers', 'careers.index']] as [$label, $route])
                        <li><a href="{{ route($route) }}" class="hover:text-moaum-red transition">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>

            {{-- Business divisions --}}
            <div>
                <h4 class="text-white font-semibold mb-4">Business Divisions</h4>
                <ul class="space-y-3">
                    @foreach (\App\Models\BusinessDivision::query()->publiclyVisible()->ordered()->take(5)->get() as $division)
                        <li><a href="{{ route('businesses.show', $division) }}" class="hover:text-moaum-red transition">{{ $division->name }}</a></li>
                    @endforeach
                    <li><a href="{{ route('businesses.index') }}" class="text-moaum-blue transition">View All →</a></li>
                </ul>
            </div>

            {{-- Contact — values come from CompanyDetails so CLIENT_TO_PROVIDE placeholders are never printed (PRD §20). --}}
            <div>
                <h4 class="text-white font-semibold mb-4">Contact Us</h4>
                <ul class="space-y-3 text-slate-400">
                    @if (\App\Support\CompanyDetails::has('address'))
                        <li class="flex items-start">
                            <x-icon name="map-pin" class="w-5 h-5 mr-3 mt-0.5 text-moaum-red" />
                            <span>{{ \App\Support\CompanyDetails::get('address') }}</span>
                        </li>
                    @endif
                    @if (\App\Support\CompanyDetails::has('phone'))
                        <li class="flex items-center">
                            <x-icon name="phone" class="w-5 h-5 mr-3 text-moaum-red" />
                            <span>{{ \App\Support\CompanyDetails::get('phone') }}</span>
                        </li>
                    @endif
                    @if (\App\Support\CompanyDetails::has('email'))
                        <li class="flex items-center">
                            <x-icon name="mail" class="w-5 h-5 mr-3 text-moaum-red" />
                            <span>{{ \App\Support\CompanyDetails::get('email') }}</span>
                        </li>
                    @endif
                    @if (\App\Support\CompanyDetails::has('hours'))
                        <li class="flex items-center">
                            <x-icon name="clock" class="w-5 h-5 mr-3 text-moaum-red" />
                            <span>{{ \App\Support\CompanyDetails::get('hours') }}</span>
                        </li>
                    @endif
                    @if (! \App\Support\CompanyDetails::has('address') && ! \App\Support\CompanyDetails::has('phone'))
                        <li class="text-slate-500 text-sm">
                            Call or email us — full office details are coming shortly.
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row justify-between items-center">
            <p class="text-slate-500 text-sm">&copy; {{ date('Y') }} {{ config('moaum.company.name') }}. All rights reserved.</p>
            <div class="flex space-x-6 mt-4 md:mt-0">
                <a href="{{ route('legal.privacy') }}" class="text-slate-500 hover:text-white text-sm transition">Privacy Policy</a>
                <a href="{{ route('legal.terms') }}" class="text-slate-500 hover:text-white text-sm transition">Terms of Use</a>
            </div>
        </div>
    </div>
</footer>
