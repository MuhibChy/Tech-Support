@extends('layouts.public')

@section('title', 'Contact Our Engineering Team — TechSupport Solutions')
@section('description', 'Request a consultation with our IT engineers and solutions team. Submit the form and we will respond according to your service plan.')

@section('content')

{{-- Hero --}}
<section class="relative w-full py-28 lg:py-36 bg-space-radial border-b border-white/10 overflow-hidden z-10">
    {{-- Global 3D hero scene (same implementation as Home) --}}
    <x-hero-scene />
    <div class="absolute inset-0 bg-cyber-grid opacity-15 pointer-events-none"></div>
    <div class="w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16 relative z-10">
        <div class="max-w-4xl">
            <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-xs font-semibold uppercase tracking-widest text-cyan-300 mb-6">
                <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                Request a Consultation
            </div>
            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-white tracking-tight leading-[1.05] mb-8">
                Connect with <span class="gradient-text-cyber">Our Engineering Team.</span>
            </h1>
            <p class="text-lg sm:text-xl text-slate-300 leading-relaxed font-normal max-w-3xl">
                Whether you need a security assessment, a cloud migration plan, or ongoing IT support, send us the details and we will respond according to your service plan.
            </p>
        </div>
    </div>
</section>

{{-- Contact Grid (Wide 12-Column Layout) --}}
<section class="relative w-full py-24 lg:py-32 bg-[#030712] border-b border-white/10 z-10">
    <div class="w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16">
        <div class="grid lg:grid-cols-12 gap-12 lg:gap-16">

            {{-- Form Column --}}
            <div class="lg:col-span-8">
                <div class="cosmic-glass p-8 sm:p-12 rounded-3xl border border-white/10">
                    <h2 class="text-2xl sm:text-3xl font-black text-white mb-2">Request a Consultation</h2>
                    <p class="text-sm text-slate-400 mb-8">Fill out the details below and our team will get back to you.</p>

                    <form action="{{ route('contact.submit') }}" method="POST" class="space-y-6">
                        @csrf
                        <div class="grid sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-mono uppercase tracking-widest text-slate-300 mb-2">Full Name *</label>
                                <input type="text" name="name" value="{{ old('name') }}" required
                                       class="w-full px-4 py-3.5 rounded-xl border border-white/10 bg-black/40 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-500/20 text-sm">
                                @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-mono uppercase tracking-widest text-slate-300 mb-2">Corporate Email *</label>
                                <input type="email" name="email" value="{{ old('email') }}" required
                                       class="w-full px-4 py-3.5 rounded-xl border border-white/10 bg-black/40 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-500/20 text-sm">
                                @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-mono uppercase tracking-widest text-slate-300 mb-2">Company / Organization</label>
                                <input type="text" name="company" value="{{ old('company') }}" placeholder="Acme Enterprise"
                                       class="w-full px-4 py-3.5 rounded-xl border border-white/10 bg-black/40 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-500/20 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-mono uppercase tracking-widest text-slate-300 mb-2">Direct Phone</label>
                                <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="Your contact number"
                                       class="w-full px-4 py-3.5 rounded-xl border border-white/10 bg-black/40 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-500/20 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-mono uppercase tracking-widest text-slate-300 mb-2">Primary Consultation Focus *</label>
                            <select name="subject" class="w-full px-4 py-3.5 rounded-xl border border-white/10 bg-[#040816] text-white focus:outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-500/20 text-sm">
                                <option value="cybersecurity">Cybersecurity Assessment & Hardening</option>
                                <option value="managed-it">Managed IT Infrastructure & Support</option>
                                <option value="cloud">Cloud Migration & DevOps</option>
                                <option value="general">Custom Work Order / Quote</option>
                                <option value="support">Existing Contract Support Request</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-mono uppercase tracking-widest text-slate-300 mb-2">Project Scope & Technical Details *</label>
                            <textarea name="message" rows="5" required
                                      placeholder="Provide an overview of your current infrastructure, timeline, user count, or specific threat vectors..."
                                      class="w-full px-4 py-3.5 rounded-xl border border-white/10 bg-black/40 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-500/20 text-sm resize-none">{{ old('message') }}</textarea>
                            @error('message') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit"
                                class="btn btn-lg text-white font-bold text-sm px-8 py-4 rounded-xl transition-all shadow-lg shadow-cyan-500/30 hover:scale-[1.02] btn-brand-gradient">
                            Submit Request to Engineering
                            <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Sidebar Column --}}
            <div class="lg:col-span-4 space-y-6">
                <div class="cosmic-card p-8">
                    <div class="text-xs font-mono uppercase tracking-widest text-cyan-400 mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        Priority Support
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Existing Customers</h3>
                    <p class="text-xs text-slate-400 leading-relaxed mb-6">
                        If you have an active service plan, sign in to the customer portal to open or escalate a ticket — requests are handled under your agreed SLA.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('login') }}" class="text-xs font-bold px-4 py-2.5 rounded-xl bg-cyan-500 text-black hover:bg-cyan-400 transition-colors">Customer Login</a>
                        <a href="{{ route('get-quote') }}" class="text-xs font-bold px-4 py-2.5 rounded-xl border border-cyan-400/40 text-cyan-300 hover:bg-cyan-500 hover:text-black transition-all">Get a Quote</a>
                    </div>
                </div>

                <div class="cosmic-card p-8 space-y-6">
                    @if(config('app.support_email'))
                    <div>
                        <div class="text-xs font-mono text-slate-400 uppercase tracking-wider mb-1">Email</div>
                        <div class="text-base font-bold text-white"><a href="mailto:{{ config('app.support_email') }}" class="hover:text-cyan-300 transition-colors">{{ config('app.support_email') }}</a></div>
                    </div>
                    @endif
                    @if(config('app.support_phone'))
                    <div class="border-t border-white/10 pt-4">
                        <div class="text-xs font-mono text-slate-400 uppercase tracking-wider mb-1">Phone</div>
                        <div class="text-base font-bold text-white">{{ config('app.support_phone') }}</div>
                    </div>
                    @endif
                    <div class="border-t border-white/10 pt-4">
                        <div class="text-xs font-mono text-slate-400 uppercase tracking-wider mb-1">Response</div>
                        <div class="text-sm font-bold text-emerald-400">{{ config('app.support_hours') }}</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

@endsection
