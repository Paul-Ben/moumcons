{{-- Site footer — faithful to prototype-docs/index.html (4-column, slate-900) --}}
<footer class="bg-slate-900 text-slate-300 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">

            {{-- Brand + social --}}
            <div>
                <img src="{{ asset('images/logo.jpg') }}" alt="{{ config('moaum.company.name') }}" class="h-16 w-auto mb-6 brightness-0 invert">
                <p class="text-slate-400 mb-6">The official business and investment arm of {{ config('moaum.company.parent') }}.</p>
                <div class="flex space-x-4">
                    @foreach (['facebook', 'twitter', 'linkedin'] as $network)
                        <a href="{{ config('moaum.company.social.'.$network) }}" aria-label="{{ ucfirst($network) }}"
                           class="w-10 h-10 bg-slate-800 rounded-lg flex items-center justify-center hover:bg-moaum-{{ $network === 'facebook' ? 'red' : 'blue' }} transition">
                            <x-icon :name="$network" class="w-5 h-5" />
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Quick links --}}
            <div>
                <h4 class="text-white font-semibold mb-4">Quick Links</h4>
                <ul class="space-y-3">
                    @foreach (['About Us', 'Our Businesses', 'Services', 'Projects', 'Training', 'Careers'] as $link)
                        <li><a href="#" class="hover:text-moaum-red transition">{{ $link }}</a></li>
                    @endforeach
                </ul>
            </div>

            {{-- Business divisions --}}
            <div>
                <h4 class="text-white font-semibold mb-4">Business Divisions</h4>
                <ul class="space-y-3">
                    @foreach (['Printing & Publishing', 'Cleaning & Fumigation', 'AI & Digital Training', 'Construction Services', 'Agriculture & Farms'] as $division)
                        <li><a href="#" class="hover:text-moaum-red transition">{{ $division }}</a></li>
                    @endforeach
                    <li><a href="#" class="text-moaum-blue transition">View All →</a></li>
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <h4 class="text-white font-semibold mb-4">Contact Us</h4>
                <ul class="space-y-3 text-slate-400">
                    <li class="flex items-start">
                        <x-icon name="map-pin" class="w-5 h-5 mr-3 mt-0.5 text-moaum-red" />
                        <span>{{ config('moaum.company.address') }}</span>
                    </li>
                    <li class="flex items-center">
                        <x-icon name="phone" class="w-5 h-5 mr-3 text-moaum-red" />
                        <span>{{ config('moaum.company.phone') }}</span>
                    </li>
                    <li class="flex items-center">
                        <x-icon name="mail" class="w-5 h-5 mr-3 text-moaum-red" />
                        <span>{{ config('moaum.company.email') }}</span>
                    </li>
                    <li class="flex items-center">
                        <x-icon name="clock" class="w-5 h-5 mr-3 text-moaum-red" />
                        <span>{{ config('moaum.company.hours') }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row justify-between items-center">
            <p class="text-slate-500 text-sm">&copy; {{ date('Y') }} {{ config('moaum.company.name') }}. All rights reserved.</p>
            <div class="flex space-x-6 mt-4 md:mt-0">
                <a href="#" class="text-slate-500 hover:text-white text-sm transition">Privacy Policy</a>
                <a href="#" class="text-slate-500 hover:text-white text-sm transition">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>
