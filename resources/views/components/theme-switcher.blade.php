@props(['variant' => 'dropdown', 'align' => 'right'])

<div x-data="themePicker()" class="relative inline-block text-left">
    @if($variant === 'segmented')
        <!-- Segmented 3-button toggle (Monitor, Sun, Moon) -->
        <div class="inline-flex items-center p-1 rounded-xl bg-slate-100/90 dark:bg-slate-800/90 border border-slate-200/90 dark:border-slate-700/80 shadow-2xs gap-0.5">
            <button 
                type="button" 
                @click="setTheme('system')" 
                :class="theme === 'system' ? 'bg-white dark:bg-slate-700 text-emerald-700 dark:text-emerald-400 shadow-xs font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white'"
                class="px-2.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5 cursor-pointer"
                title="Default Sistem"
            >
                <i class="fa-solid fa-desktop text-xs"></i>
                <span class="text-[11px]">Sistem</span>
            </button>
            <button 
                type="button" 
                @click="setTheme('light')" 
                :class="theme === 'light' ? 'bg-white dark:bg-slate-700 text-amber-600 shadow-xs font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white'"
                class="px-2.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5 cursor-pointer"
                title="Light Mode"
            >
                <i class="fa-solid fa-sun text-xs text-amber-500"></i>
                <span class="text-[11px]">Light</span>
            </button>
            <button 
                type="button" 
                @click="setTheme('dark')" 
                :class="theme === 'dark' ? 'bg-white dark:bg-slate-700 text-indigo-400 shadow-xs font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white'"
                class="px-2.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5 cursor-pointer"
                title="Dark Mode"
            >
                <i class="fa-solid fa-moon text-xs text-indigo-400"></i>
                <span class="text-[11px]">Dark</span>
            </button>
        </div>
    @else
        <!-- Dropdown variant with current active icon and arrow -->
        <button 
            type="button" 
            @click="open = !open" 
            class="relative px-2.5 py-1.5 text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 rounded-xl transition-all cursor-pointer flex items-center gap-1.5 focus:outline-none"
            :class="open ? 'bg-slate-100 dark:bg-slate-800 text-emerald-600 dark:text-emerald-400' : ''"
            :title="'Tema: ' + label"
            aria-label="Pilih Tema Tampilan"
        >
            <!-- Ikon tema header merepresentasikan tema yang aktif -->
            <span class="inline-flex items-center text-base">
                <i x-show="theme === 'system'" class="fa-solid fa-desktop text-slate-600 dark:text-slate-300 transition-colors" @if(request()->cookie('theme_preference', 'system') !== 'system') style="display: none;" @endif></i>
                <i x-show="theme === 'light'" class="fa-solid fa-sun text-amber-500 transition-colors" @if(request()->cookie('theme_preference', 'system') !== 'light') style="display: none;" @endif></i>
                <i x-show="theme === 'dark'" class="fa-solid fa-moon text-indigo-400 transition-colors" @if(request()->cookie('theme_preference', 'system') !== 'dark') style="display: none;" @endif></i>
            </span>
            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180 text-emerald-600 dark:text-emerald-400' : ''"></i>
        </button>

        <!-- Dropdown Menu adaptive with active theme colors -->
        <div 
            x-show="open" 
            @click.outside="open = false" 
            @keydown.escape.window="open = false"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
            x-cloak
            class="absolute {{ $align === 'left' ? 'left-0' : 'right-0' }} mt-2.5 w-[calc(100vw-2rem)] max-w-xs sm:w-72 bg-white dark:bg-[#071521] text-slate-800 dark:text-white rounded-2xl shadow-xl shadow-slate-900/10 dark:shadow-2xl dark:shadow-black/50 border border-slate-200 dark:border-[#17344d] p-3.5 z-50 overflow-hidden"
        >
            <div class="px-1 mb-2.5 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                Tema Tampilan
            </div>

            <!-- Segmented Pill Track adapting to active theme -->
            <div class="w-full flex items-center p-1 rounded-xl bg-slate-100/90 dark:bg-[#040e18] border border-slate-200/90 dark:border-[#17344d] gap-1">
                <!-- Option 1: Sistem (ikon monitor) -->
                <button 
                    type="button" 
                    @click="setTheme('system')" 
                    :class="theme === 'system' ? 'bg-white dark:bg-[#0f283e] text-slate-900 dark:text-white shadow-xs dark:shadow-sm border border-slate-200/90 dark:border-slate-600/50 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-200/60 dark:hover:bg-slate-800/40'"
                    class="flex-1 py-1.5 px-2.5 rounded-lg text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer select-none"
                    title="Default Sistem"
                >
                    <i class="fa-solid fa-desktop text-xs"></i>
                    <span class="text-[11px]">Sistem</span>
                </button>

                <!-- Option 2: Light (ikon matahari) -->
                <button 
                    type="button" 
                    @click="setTheme('light')" 
                    :class="theme === 'light' ? 'bg-white dark:bg-[#0f283e] text-slate-900 dark:text-white shadow-xs dark:shadow-sm border border-slate-200/90 dark:border-slate-600/50 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-200/60 dark:hover:bg-slate-800/40'"
                    class="flex-1 py-1.5 px-2.5 rounded-lg text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer select-none"
                    title="Light Mode"
                >
                    <i class="fa-solid fa-sun text-xs text-amber-500"></i>
                    <span class="text-[11px]">Light</span>
                </button>

                <!-- Option 3: Dark (ikon bulan) -->
                <button 
                    type="button" 
                    @click="setTheme('dark')" 
                    :class="theme === 'dark' ? 'bg-white dark:bg-[#0f283e] text-slate-900 dark:text-white shadow-xs dark:shadow-sm border border-slate-200/90 dark:border-slate-600/50 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-200/60 dark:hover:bg-slate-800/40'"
                    class="flex-1 py-1.5 px-2.5 rounded-lg text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer select-none"
                    title="Dark Mode"
                >
                    <i class="fa-solid fa-moon text-xs text-indigo-400"></i>
                    <span class="text-[11px]">Dark</span>
                </button>
            </div>
        </div>
    @endif
</div>
