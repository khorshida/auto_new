<x-layouts::showroom title="Sign in">
    <section class="mx-auto flex w-full max-w-md flex-col gap-8 px-5 py-16 sm:px-8 sm:py-24">
        <div class="flex flex-col gap-4">
            <p class="text-[11px] tracking-[0.28em] text-gold uppercase">Studio desk</p>
            <h1 class="font-display text-5xl text-cream">Sign in</h1>
            <p class="text-sm leading-relaxed text-cream-dim">For this demo, sign in with test@example.com and the password password.</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4 border border-line bg-panel p-5">
            @csrf
            <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
                Email
                <input name="email" type="email" value="{{ old('email') }}" autocomplete="username" required class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
            </label>
            <label class="flex flex-col gap-2 text-[11px] tracking-[0.16em] text-cream-dim uppercase">
                Password
                <input name="password" type="password" autocomplete="current-password" required class="border border-line bg-ink px-3 py-3 text-sm tracking-normal text-cream normal-case">
            </label>
            @error('email')
                <p class="text-sm text-gold">{{ $message }}</p>
            @enderror
            <label class="flex items-center gap-2 text-sm tracking-normal text-cream-dim normal-case">
                <input name="remember" type="checkbox" class="accent-gold">
                Remember this desk
            </label>
            <button type="submit" class="inline-flex self-start bg-gold px-6 py-3 text-[12px] tracking-[0.18em] text-ink uppercase">Sign in</button>
        </form>
    </section>
</x-layouts::showroom>
