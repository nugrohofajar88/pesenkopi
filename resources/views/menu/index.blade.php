@extends('layouts.app')

@section('title', 'Menu')

@section('content')
    <div
        x-data="{
            categories: @js($categories),
            activeCategoryId: null,
            search: '',
            pickerProduct: null,
            pickerSelections: {},
            pickerQty: 1,
            pickerNote: '',

            init() {
                if (this.categories.length > 0) this.activeCategoryId = this.categories[0].id;
            },

            get activeCategory() {
                return this.categories.find(c => c.id === this.activeCategoryId) ?? null;
            },

            get visibleProducts() {
                const products = this.activeCategory?.products ?? [];
                const term = this.search.trim().toLowerCase();
                if (term === '') return products;
                return products.filter(p => p.name.toLowerCase().includes(term));
            },

            money(v) {
                return 'Rp' + Number(v).toLocaleString('id-ID');
            },

            openPicker(product) {
                this.pickerProduct = product;
                this.pickerQty = 1;
                this.pickerNote = '';
                const selections = {};
                for (const group of product.modifierGroups) {
                    selections[group.id] = group.options.filter(o => o.isDefault).map(o => o.id);
                }
                this.pickerSelections = selections;
            },

            closePicker() {
                this.pickerProduct = null;
            },

            toggleOption(group, optionId) {
                const current = this.pickerSelections[group.id] ?? [];
                if (group.selectionType === 'single') {
                    this.pickerSelections[group.id] = [optionId];
                } else if (current.includes(optionId)) {
                    this.pickerSelections[group.id] = current.filter(id => id !== optionId);
                } else {
                    this.pickerSelections[group.id] = [...current, optionId];
                }
            },

            isSelected(group, optionId) {
                return (this.pickerSelections[group.id] ?? []).includes(optionId);
            },

            get pickerModifiersTotal() {
                if (!this.pickerProduct) return 0;
                let total = 0;
                for (const group of this.pickerProduct.modifierGroups) {
                    const selected = this.pickerSelections[group.id] ?? [];
                    for (const option of group.options) {
                        if (selected.includes(option.id)) total += option.priceDelta;
                    }
                }
                return total;
            },

            get pickerUnitTotal() {
                return (this.pickerProduct?.basePrice ?? 0) + this.pickerModifiersTotal;
            },

            get pickerSelectedOptionIds() {
                if (!this.pickerProduct) return [];
                let ids = [];
                for (const group of this.pickerProduct.modifierGroups) {
                    ids = ids.concat(this.pickerSelections[group.id] ?? []);
                }
                return ids;
            },

            submitAdd(event) {
                for (const group of this.pickerProduct.modifierGroups) {
                    if (group.isRequired && (this.pickerSelections[group.id] ?? []).length === 0) {
                        event.preventDefault();
                        alert('Pilih dulu \'' + group.name + '\'.');
                        return;
                    }
                }
            },
        }"
    >
        @if ($menuError)
            <div class="rounded-xl bg-error-container/20 border border-error/40 text-on-surface px-4 py-3 text-sm mb-4">{{ $menuError }}</div>
        @endif

        <div class="mb-5">
            <h1 class="text-2xl font-bold text-on-surface mb-1">Daftar Menu Kopi</h1>
            <p class="text-sm text-on-surface-variant">Pesan langsung tanpa antre, ambil sendiri atau minta diantar.</p>
        </div>

        <div class="relative mb-4">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
            <input type="text" x-model="search" placeholder="Cari es kopi favoritmu..."
                   class="w-full pl-10 pr-4 py-2.5 rounded-full bg-surface-container-low border border-outline-variant text-on-surface placeholder:text-outline focus:outline-none focus:border-primary-container">
        </div>

        <div class="flex items-center gap-2 overflow-x-auto pb-2 mb-5" style="scrollbar-width: none;">
            <template x-for="category in categories" :key="category.id">
                <button
                    type="button"
                    @click="activeCategoryId = category.id"
                    :class="activeCategoryId === category.id ? 'bg-primary-container text-on-primary-container font-bold' : 'bg-surface-container-low border border-outline-variant text-on-surface-variant'"
                    class="shrink-0 px-4 py-2 rounded-full text-sm transition-colors"
                    x-text="category.name"
                ></button>
            </template>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="product in visibleProducts" :key="product.id">
                <div class="bg-surface-container-low border border-outline-variant rounded-2xl p-4 flex flex-col shadow-sm">
                    <div class="w-full h-40 rounded-xl bg-surface-container-lowest border border-outline-variant/40 flex items-center justify-center overflow-hidden mb-3">
                        <img x-show="product.imageUrl" :src="product.imageUrl" class="h-full w-full object-cover">
                        <span x-show="!product.imageUrl" class="material-symbols-outlined text-outline text-[40px]">local_cafe</span>
                    </div>
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <h3 class="font-semibold text-on-surface" x-text="product.name"></h3>
                        <span class="text-primary font-bold whitespace-nowrap" x-text="money(product.basePrice)"></span>
                    </div>
                    <button
                        type="button"
                        @click="openPicker(product)"
                        class="mt-auto pt-3 inline-flex items-center justify-center gap-1.5 rounded-full bg-primary-container text-on-primary-container font-semibold text-sm px-4 py-2 hover:opacity-90 transition-opacity"
                    >
                        <span class="material-symbols-outlined text-[18px]">add</span> Tambah
                    </button>
                </div>
            </template>

            <p x-show="visibleProducts.length === 0" class="col-span-full text-center text-outline italic py-10">
                Tidak ada menu yang cocok.
            </p>
        </div>

        {{-- Modal pilih modifier + qty --}}
        <div
            x-show="pickerProduct"
            x-cloak
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 p-0 sm:p-4"
            @click.self="closePicker()"
        >
            <div class="bg-surface-container-low w-full sm:max-w-md sm:rounded-2xl rounded-t-3xl max-h-[90vh] overflow-y-auto p-5 border border-outline-variant" x-cloak>
                <template x-if="pickerProduct">
                    <form method="POST" action="{{ route('cart.store') }}" @submit="submitAdd" class="flex flex-col gap-4">
                        @csrf
                        <input type="hidden" name="product_id" :value="pickerProduct.id">
                        <input type="hidden" name="qty" :value="pickerQty">
                        <input type="hidden" name="note" :value="pickerNote">
                        <template x-for="optionId in pickerSelectedOptionIds" :key="optionId">
                            <input type="hidden" name="modifier_option_ids[]" :value="optionId">
                        </template>

                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-semibold text-lg text-on-surface" x-text="pickerProduct.name"></div>
                                <div class="text-sm text-on-surface-variant" x-text="money(pickerProduct.basePrice)"></div>
                            </div>
                            <button type="button" @click="closePicker()" class="material-symbols-outlined text-on-surface-variant text-[22px]">close</button>
                        </div>

                        <template x-for="group in pickerProduct.modifierGroups" :key="group.id">
                            <div>
                                <div class="font-medium text-on-surface mb-1.5 flex items-center gap-1">
                                    <span x-text="group.name"></span>
                                    <span x-show="group.isRequired" class="text-error">*</span>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="option in group.options" :key="option.id">
                                        <button
                                            type="button"
                                            @click="toggleOption(group, option.id)"
                                            :class="isSelected(group, option.id) ? 'bg-primary-container text-on-primary-container border-primary-container' : 'bg-surface-container-lowest text-on-surface-variant border-outline-variant'"
                                            class="px-3 py-1.5 rounded-full border text-sm"
                                        >
                                            <span x-text="option.name"></span>
                                            <span x-show="option.priceDelta > 0" x-text="' (+' + option.priceDelta.toLocaleString('id-ID') + ')'"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <div>
                            <label class="block text-sm text-on-surface-variant mb-1">Catatan (opsional)</label>
                            <input type="text" x-model="pickerNote" placeholder="mis. less sweet" class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-on-surface">
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="font-medium text-on-surface">Jumlah</span>
                            <div class="flex items-center gap-3">
                                <button type="button" @click="pickerQty = Math.max(1, pickerQty - 1)" class="w-8 h-8 rounded-full border border-outline-variant flex items-center justify-center text-on-surface">-</button>
                                <span class="font-semibold w-6 text-center text-on-surface" x-text="pickerQty"></span>
                                <button type="button" @click="pickerQty++" class="w-8 h-8 rounded-full border border-outline-variant flex items-center justify-center text-on-surface">+</button>
                            </div>
                        </div>

                        <button type="submit" class="w-full rounded-full bg-primary-container text-on-primary-container font-bold py-3">
                            Tambah ke Keranjang — <span x-text="money(pickerUnitTotal * pickerQty)"></span>
                        </button>
                    </form>
                </template>
            </div>
        </div>
    </div>
@endsection
