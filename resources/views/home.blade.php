{{--
    Home page — static structural implementation faithful to prototype-docs/index.html.
    Module 4 will replace the hardcoded content with database-driven CMS content.
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
                        51% Owned by Rev. Fr. Moses Orshio Adasu University
                    </div>
                    <h1 class="font-display text-4xl lg:text-6xl font-bold text-moaum-charcoal leading-tight mb-6">
                        Building Enterprise Value Through <span class="text-moaum-red">Diverse</span> Business Solutions
                    </h1>
                    <p class="text-lg lg:text-xl text-slate-600 mb-8 leading-relaxed">
                        MOAUM Consultancy Services Limited is a diversified commercial enterprise spanning 16 business divisions, delivering professional excellence across technology, agriculture, construction, education, and beyond.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <x-button href="#" size="lg">Request a Service <x-icon name="arrow-right" class="h-5 w-5" /></x-button>
                        <x-button href="#" variant="outline" size="lg">Explore Our Businesses</x-button>
                    </div>
                </div>

                <div class="relative hidden lg:block">
                    <div class="relative rounded-2xl overflow-hidden shadow-elevated">
                        <img src="https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=800&h=600&fit=crop" alt="MOAUM Team at work" class="w-full h-auto" loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-tr from-moaum-charcoal/40 to-transparent"></div>
                        <div class="absolute bottom-6 left-6 right-6 text-white">
                            <p class="font-display text-2xl font-bold">16+ Business Divisions</p>
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
                <x-stat value="51%" label="University Shareholding" color="red" class="text-white" />
                <x-stat value="16" label="Business Divisions" color="blue" class="text-white" />
                <x-stat value="∞" label="Opportunities for Growth" color="green" class="text-white" />
            </div>
        </x-container>
    </section>

    {{-- Business portfolio (first 3 shown; full directory in Module 5) --}}
    <section class="py-20 bg-white">
        <x-container>
            <x-section-heading
                eyebrow="Our Business Portfolio"
                title="Diversified Capabilities. One Enterprise Platform."
                description="Explore our comprehensive range of professional services across multiple sectors" />

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <x-business-card name="Printing & Publishing Services" status="Active"
                    description="Commercial printing, publishing and institutional documentation services." />
                <x-business-card name="Industrial Cleaning & Fumigation" status="Active"
                    description="Professional cleaning and fumigation services for commercial spaces." />
                <x-business-card name="AI & Digital Technology Training" status="Active"
                    description="Industry-aligned digital skills programmes for the next generation." />
            </div>

            <div class="text-center mt-12">
                <x-button href="{{ route('businesses.index') }}" variant="secondary">View All Businesses</x-button>
            </div>
        </x-container>
    </section>

    {{-- Why MOAUM (Why-us block from prototype) --}}
    <section class="py-20 bg-slate-50">
        <x-container>
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <x-section-heading align="left" eyebrow="Why Choose MOAUM"
                        title="One Enterprise. Many Strengths." class="mb-8" />
                    <div class="space-y-8">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 bg-moaum-blue/10 rounded-lg flex items-center justify-center mr-4">
                                <x-icon name="zap" class="w-6 h-6 text-moaum-blue" />
                            </div>
                            <div>
                                <h3 class="font-display text-lg font-bold text-moaum-charcoal mb-2">Diversified Expertise</h3>
                                <p class="text-slate-600">16 business divisions spanning technology, agriculture, construction, education, and professional services.</p>
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
                <div class="grid grid-cols-2 gap-4">
                    <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=400&h=500&fit=crop" alt="Team collaboration" class="rounded-xl shadow-lg" loading="lazy">
                    <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?w=400&h=500&fit=crop" alt="Business meeting" class="rounded-xl shadow-lg mt-8" loading="lazy">
                </div>
            </div>
        </x-container>
    </section>

    {{-- CTA --}}
    <section class="py-20 bg-moaum-charcoal relative overflow-hidden">
        <div class="absolute inset-0 opacity-10"><div class="absolute inset-0 dot-grid-blue"></div></div>
        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="font-display text-3xl lg:text-5xl font-bold text-white mb-6">Ready to Work With Us?</h2>
            <p class="text-xl text-slate-300 mb-10 max-w-3xl mx-auto">Let's discuss how our diverse business capabilities can meet your needs</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <x-button href="#" size="lg">Request a Service <x-icon name="arrow-right" class="h-5 w-5" /></x-button>
                <x-button href="#" variant="white" size="lg">Request a Quote</x-button>
            </div>
        </div>
    </section>

</x-layouts.public>
