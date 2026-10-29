@props([
    'name',
    'id' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => null,
    'placeholderValue' => '',
    'required' => false,
    'disabled' => false,
    'autoSubmit' => false,
    'containerClass' => 'w-full',
    'buttonClass' => '',
    'alpineModel' => null,
    'ariaLabel' => null,
])

@php
    $componentId = $id ?? 'custom_select_' . str_replace(['[', ']', '.', '-'], '_', $name) . '_' . substr(md5(uniqid((string)mt_rand(), true)), 0, 6);
    $normalizedOptions = [];

    if ($placeholder !== null) {
        $normalizedOptions[] = [
            'value' => (string) $placeholderValue,
            'label' => (string) $placeholder,
            'is_placeholder' => true,
        ];
    }

    foreach ($options as $key => $val) {
        if (is_array($val)) {
            $normalizedOptions[] = [
                'value' => (string) ($val['value'] ?? $key),
                'label' => (string) ($val['label'] ?? $val['name'] ?? $key),
                'badge' => $val['badge'] ?? null,
                'icon' => $val['icon'] ?? null,
                'is_placeholder' => false,
            ];
        } elseif (is_numeric($key) && !array_key_exists((string)$val, $options)) {
            $normalizedOptions[] = [
                'value' => (string) $val,
                'label' => (string) $val,
                'is_placeholder' => false,
            ];
        } else {
            $normalizedOptions[] = [
                'value' => (string) $key,
                'label' => (string) $val,
                'is_placeholder' => ((string)$key === '' || (string)$key === (string)$placeholderValue),
            ];
        }
    }

    $dotName = str_replace(['[', ']'], ['.', ''], $name);
    $initialValue = (string) old($dotName, old($name, $selected ?? ($placeholder !== null ? $placeholderValue : ($normalizedOptions[0]['value'] ?? ''))));
@endphp

<div 
    id="{{ $componentId }}"
    class="relative {{ $containerClass }}"
    :class="{ 'z-[70]': open, 'z-10': !open }"
    :data-dropdown-open="open ? 'true' : 'false'"
    x-data="{
        open: false,
        dropUp: false,
        elevatedAncestorsList: [],
        value: @js($initialValue),
        focusedIndex: -1,
        options: {{ json_encode($normalizedOptions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }},
        autoSubmit: @js((bool)$autoSubmit),
        disabled: @js((bool)$disabled),

        init() {
            @if($alpineModel)
                this.$watch(() => {
                    try {
                        return {{ $alpineModel }};
                    } catch(e) {
                        return null;
                    }
                }, (newVal) => {
                    if (newVal !== undefined && newVal !== null) {
                        this.selectByValue(newVal, false);
                    }
                });
                try {
                    let initialFromModel = {{ $alpineModel }};
                    if (initialFromModel !== undefined && initialFromModel !== null) {
                        this.selectByValue(initialFromModel, false);
                    }
                } catch(e) {}
            @endif
        },

        get currentLabel() {
            const found = this.options.find(o => String(o.value) === String(this.value));
            return found ? found.label : (@js($placeholder) || 'Pilih opsi...');
        },

        get isPlaceholderSelected() {
            const found = this.options.find(o => String(o.value) === String(this.value));
            return !found || Boolean(found.is_placeholder);
        },

        toggle() {
            if (this.disabled) return;
            this.open ? this.close() : this.openDropdown();
        },

        openDropdown() {
            if (this.disabled) return;
            this.open = true;
            this.focusedIndex = this.options.findIndex(o => String(o.value) === String(this.value));
            if (this.focusedIndex === -1 && this.options.length > 0) this.focusedIndex = 0;
            
            this.elevateAncestors();

            this.$nextTick(() => {
                this.adjustDropdownPosition();
                this.scrollFocusedIntoView();
            });
        },

        close() {
            if (!this.open) return;
            this.open = false;
            this.focusedIndex = -1;
            this.restoreAncestors();
        },

        adjustDropdownPosition() {
            const trigger = this.$refs.triggerButton;
            const listbox = this.$refs.listbox;
            if (!trigger || !listbox) return;
            
            const rect = trigger.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;
            
            // If space below is constrained and space above is greater, open upward
            if (spaceBelow < 250 && spaceAbove > spaceBelow) {
                this.dropUp = true;
            } else {
                this.dropUp = false;
            }
        },

        elevateAncestors() {
            let el = this.$el.parentElement;
            this.elevatedAncestorsList = [];
            while (el && el !== document.body && el !== document.documentElement) {
                const tag = el.tagName.toUpperCase();
                const cls = (typeof el.className === 'string') ? el.className : '';
                if (
                    cls.includes('card-hover') || 
                    cls.includes('anim-fade-up') ||
                    cls.includes('rounded-2xl') ||
                    cls.includes('shadow') ||
                    tag === 'FORM' ||
                    tag === 'TABLE' ||
                    tag === 'TR' ||
                    tag === 'TD'
                ) {
                    this.elevatedAncestorsList.push({
                        element: el,
                        originalZIndex: el.style.zIndex,
                        originalPosition: el.style.position
                    });
                    el.style.zIndex = '50';
                    if (!el.style.position || el.style.position === 'static') {
                        el.style.position = 'relative';
                    }
                }
                el = el.parentElement;
            }
        },

        restoreAncestors() {
            if (Array.isArray(this.elevatedAncestorsList)) {
                this.elevatedAncestorsList.forEach(item => {
                    if (item.element) {
                        item.element.style.zIndex = item.originalZIndex || '';
                        item.element.style.position = item.originalPosition || '';
                    }
                });
                this.elevatedAncestorsList = [];
            }
        },

        select(option, triggerSubmit = true) {
            if (this.disabled) return;
            this.value = option.value;
            this.close();

            this.$nextTick(() => {
                const input = this.$refs.hiddenInput;
                if (input) {
                    input.value = option.value;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                }
                if (this.autoSubmit && triggerSubmit) {
                    const form = this.$el.closest('form');
                    if (form) form.submit();
                }
            });
        },

        selectByValue(val, triggerSubmit = false) {
            const found = this.options.find(o => String(o.value) === String(val));
            if (found) {
                this.select(found, triggerSubmit);
            } else {
                this.value = val;
            }
        },

        focusNext() {
            if (this.options.length === 0) return;
            this.focusedIndex = (this.focusedIndex + 1) % this.options.length;
            this.scrollFocusedIntoView();
        },

        focusPrev() {
            if (this.options.length === 0) return;
            this.focusedIndex = (this.focusedIndex - 1 + this.options.length) % this.options.length;
            this.scrollFocusedIntoView();
        },

        selectFocused() {
            if (this.focusedIndex >= 0 && this.focusedIndex < this.options.length) {
                this.select(this.options[this.focusedIndex]);
            }
        },

        scrollFocusedIntoView() {
            const container = this.$refs.listbox;
            if (!container) return;
            const item = container.children[this.focusedIndex];
            if (item && typeof item.scrollIntoView === 'function') {
                item.scrollIntoView({ block: 'nearest' });
            }
        }
    }"
    @click.outside="close()"
    @keydown.escape.prevent.stop="close()"
>
    <!-- Hidden input synced with form -->
    <input 
        type="hidden" 
        name="{{ $name }}" 
        :value="value" 
        @if($required) required @endif 
        x-ref="hiddenInput"
    >

    <!-- Trigger Button -->
    <button 
        type="button" 
        x-ref="triggerButton"
        @click="toggle()" 
        @keydown.down.prevent="if(!open){ openDropdown(); } else { focusNext(); }"
        @keydown.up.prevent="if(!open){ openDropdown(); } else { focusPrev(); }"
        @keydown.enter.prevent="if(open){ selectFocused(); } else { openDropdown(); }"
        @keydown.space.prevent="if(open){ selectFocused(); } else { openDropdown(); }"
        @keydown.tab="close()"
        :aria-expanded="open"
        aria-haspopup="listbox"
        @if($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
        :disabled="disabled"
        class="w-full flex items-center justify-between text-left text-xs bg-slate-50 dark:bg-slate-800/90 hover:bg-white dark:hover:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:border-emerald-500/70 dark:hover:border-emerald-500/70 rounded-xl px-3.5 py-2.5 text-slate-800 dark:text-slate-100 font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all duration-150 shadow-2xs cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed {{ $buttonClass }}"
        :class="{
            'ring-2 ring-emerald-500/20 border-emerald-500 bg-white dark:bg-slate-800': open
        }"
    >
        <span 
            class="truncate block mr-2"
            :class="{ 'text-slate-400 dark:text-slate-500': isPlaceholderSelected, 'font-semibold text-slate-800 dark:text-slate-100': !isPlaceholderSelected }"
            x-text="currentLabel"
        ></span>

        <!-- Chevron Icon -->
        <span class="flex-shrink-0 flex items-center text-slate-400 dark:text-slate-500 transition-transform duration-200" :class="{ 'rotate-180 text-emerald-600 dark:text-emerald-400': open }">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 20 20" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 8l4 4 4-4"/>
            </svg>
        </span>
    </button>

    <!-- Custom Floating Dropdown Menu (NOT native OS select) -->
    <div 
        x-show="open" 
        x-cloak
        x-ref="listbox"
        role="listbox"
        tabindex="-1"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1 scale-98"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-98"
        class="absolute left-0 right-0 z-[60] w-full min-w-[160px] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/80 rounded-2xl shadow-xl shadow-slate-900/10 dark:shadow-black/60 p-1.5 max-h-60 overflow-y-auto custom-scrollbar focus:outline-none"
        :class="{ 'bottom-full mb-1.5': dropUp, 'top-full mt-1.5': !dropUp }"
    >
        <template x-for="(option, index) in options" :key="option.value + '_' + index">
            <div 
                role="option"
                :aria-selected="String(option.value) === String(value)"
                @click="select(option)"
                @mouseenter="focusedIndex = index"
                class="px-3 py-2 text-xs rounded-xl flex items-center justify-between cursor-pointer transition-colors select-none"
                :class="{
                    'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-300 font-semibold': focusedIndex === index && String(option.value) !== String(value),
                    'bg-emerald-500/15 text-emerald-800 dark:text-emerald-300 font-bold': String(option.value) === String(value),
                    'text-slate-400 dark:text-slate-500 italic': option.is_placeholder,
                    'text-slate-700 dark:text-slate-200': !option.is_placeholder && String(option.value) !== String(value) && focusedIndex !== index
                }"
            >
                <div class="flex items-center gap-2 truncate">
                    <span x-text="option.label" class="truncate"></span>
                    <template x-if="option.badge">
                        <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-medium" x-text="option.badge"></span>
                    </template>
                </div>

                <!-- Active Checkmark Indicator -->
                <span x-show="String(option.value) === String(value)" class="text-emerald-600 dark:text-emerald-400 flex-shrink-0 ml-2">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </span>
            </div>
        </template>
    </div>
</div>
