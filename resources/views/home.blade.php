{{--
    Home page — database-driven (Module 4). Structure mirrors
    prototype-docs/index.html; copy comes from the settings table,
    portfolio cards from business_divisions.
--}}
<x-layouts.public>

    {{-- Hero --}}
    <section class="relative bg-gradient-to-br from-slate-50 via-white to-slate-100 overflow-hidden">
        <div class="absolute inset-0 opacity-5"><div class="absolute inset-0 dot-grid-red"></div></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="fade-up">
                    <div class="inline-flex items-center px-4 py-2 bg-moaum-red/10 text-moaum-red rounded-full text-sm font-semibold mb-6">
                        <span class="flex h-2 w-2 bg-moaum-red rounded-full mr-2"></span>
                        {{ $heroBadge }}
                    </div>
                    <h1 class="font-display text-4xl lg:text-6xl font-bold text-moaum-charcoal leading-tight mb-6">
                        Building Enterprise Value Through <span class="text-moaum-red">Diverse</span> Business Solutions
                    </h1>
                    <p class="text-lg lg:text-xl text-slate-600 mb-8 leading-relaxed">{{ $heroDescription }}</p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <x-button href="{{ route('requests.service.create') }}" size="lg">Request a Service <x-icon name="arrow-right" class="h-5 w-5" /></x-button>
                        <x-button href="{{ route('businesses.index') }}" variant="outline" size="lg">Explore Our Businesses</x-button>
                    </div>
                </div>

                <div class="relative hidden lg:block">
                    <div class="relative rounded-2xl overflow-hidden shadow-elevated">
                        <img src="/images/hero-home.jpg" alt="MOAUM Team at work" class="w-full h-auto" loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-tr from-moaum-charcoal/40 to-transparent"></div>
                        <div class="absolute bottom-6 left-6 right-6 text-white">
                            <p class="font-display text-2xl font-bold">{{ $activeCount }}+ Business Divisions</p>
                            <p class="text-white/90">Driving innovation and excellence</p>
                        </div>
                    </div>
                    <div class="absolute -bottom-6 -left-6 bg-white rounded-xl shadow-elevated p-6 border border-slate-200">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-moaum-green/10 rounded-lg flex items-center justify-center">
                                <x-icon name="check-circle" class="w-6 h-6 text-moaum-green" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-moaum-charcoal">100%</p>
                                <p class="text-sm text-slate-600">Commitment to Excellence</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Trust banner --}}
    <section class="bg-moaum-charcoal py-12">
        <x-container>
            <div class="grid md:grid-cols-3 gap-8 items-center">
                @foreach ($stats as $stat)
                    <x-stat :value="$stat['value']" :label="$stat['label']" :color="$stat['color'] ?? 'red'" />
                @endforeach
            </div>
        </x-container>
    </section>

    {{-- Business portfolio — featured divisions from the database --}}
    <section class="py-20 bg-white">
        <x-container>
            <x-section-heading :eyebrow="$portfolio['eyebrow']" :title="$portfolio['title']" :description="$portfolio['description']" />

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($featuredDivisions as $division)
                    <x-business-card :division="$division" />
                @endforeach

                {{-- "View all" card (prototype index.html) --}}
                <a href="{{ route('businesses.index') }}" class="group bg-moaum-charcoal rounded-xl overflow-hidden hover:shadow-elevated transition-all duration-300 hover:-translate-y-1 flex flex-col items-center justify-center text-center p-8">
                    <div class="w-16 h-16 bg-white/10 rounded-full flex items-center justify-center mb-4">
                        <x-icon name="layout-grid" class="w-8 h-8 text-white" />
                    </div>
                    <h3 class="font-display text-xl font-bold text-white mb-2">View All {{ $activeCount }} Businesses</h3>
                    <p class="text-slate-300 text-sm mb-4">Explore our complete portfolio</p>
                    <span class="text-moaum-blue font-semibold group-hover:translate-x-1 transition-transform inline-flex items-center">
                        Browse all <x-icon name="arrow-right" class="ml-1 w-4 h-4" />
                    </span>
                </a>
            </div>
        </x-container>
    </section>

    {{-- Why MOAUM (prototype "Why Choose Us") --}}
    <section class="py-20 bg-slate-50">
        <x-container>
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div>
                    <p class="text-moaum-red font-semibold text-sm uppercase tracking-wide mb-2">Why Choose Us</p>
                    <h2 class="font-display text-3xl lg:text-4xl font-bold text-moaum-charcoal mb-6">Institutional Excellence Meets Commercial Innovation</h2>
                    <p class="text-slate-600 text-lg mb-8">We unite university-backed governance with private-sector speed, so every engagement is accountable, professional and commercially focused.</p>

                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 bg-moaum-red/10 rounded-lg flex items-center justify-center mr-4">
                                <x-icon name="shield-check" class="w-6 h-6 text-moaum-red" />
                            </div>
                            <div>
                                <h3 class="font-display text-lg font-bold text-moaum-charcoal mb-2">Institutional Credibility</h3>
                                <p class="text-slate-600">Backed by Rev. Fr. Moses Orshio Adasu University with 51% shareholding, ensuring trust and accountability.</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 bg-moaum-blue/10 rounded-lg flex items-center justify-center mr-4">
                                <x-icon name="zap" class="w-6 h-6 text-moaum-blue" />
                            </div>
                            <div>
                                <h3 class="font-display text-lg font-bold text-moaum-charcoal mb-2">Diversified Expertise</h3>
                                <p class="text-slate-600">{{ $activeCount }} business divisions spanning technology, agriculture, construction, education, and professional services.</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 bg-moaum-green/10 rounded-lg flex items-center justify-center mr-4">
                                <x-icon name="users" class="w-6 h-6 text-moaum-green" />
                            </div>
                            <div>
                                <h3 class="font-display text-lg font-bold text-moaum-charcoal mb-2">Professional Team</h3>
                                <p class="text-slate-600">Led by experienced professionals committed to delivering excellence across all service areas.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <div class="grid grid-cols-2 gap-4">
                        <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=400&h=500&fit=crop" alt="Team collaboration" class="rounded-xl shadow-lg" loading="lazy">
                        <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?w=400&h=500&fit=crop" alt="Business meeting" class="rounded-xl shadow-lg mt-8" loading="lazy">
                    </div>
                </div>
            </div>
        </x-container>
    </section>

    {{-- CTA --}}
    <section class="py-20 bg-moaum-charcoal relative overflow-hidden">
        <div class="absolute inset-0 opacity-10"><div class="absolute inset-0 dot-grid-blue"></div></div>
        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="font-display text-3xl lg:text-5xl font-bold text-white mb-6">{{ $cta['title'] }}</h2>
            <p class="text-xl text-slate-300 mb-10 max-w-3xl mx-auto">{{ $cta['description'] }}</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <x-button href="{{ route('requests.service.create') }}" size="lg">Request a Service <x-icon name="arrow-right" class="h-5 w-5" /></x-button>
                <x-button href="{{ route('requests.quote.create') }}" variant="white" size="lg">Request a Quote</x-button>
            </div>
        </div>
    </section>

</x-layouts.public>
