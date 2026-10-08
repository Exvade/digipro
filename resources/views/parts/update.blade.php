<!-- Edit Part Modal -->
<div class="modal fade" id="editPartModal" tabindex="-1" aria-labelledby="editPartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-2xl border-0 shadow-2xl bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 overflow-hidden">
            <div class="modal-header px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg flex-shrink-0">
                        <i class="fas fa-pen"></i>
                    </div>
                    <div>
                        <h5 class="text-base font-bold text-slate-900 dark:text-white mb-0" id="editPartModalLabel">
                            Edit Data Part
                        </h5>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0">
                            Perbarui informasi spesifikasi katalog part
                        </p>
                    </div>
                </div>
                <button type="button" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center justify-center transition-all cursor-pointer" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>

            <form id="editPartForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_part_id" name="part_id">
                
                <div class="modal-body p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="edit_no_parts" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                No Part <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                class="form-control w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none @error('no_parts') is-invalid @enderror"
                                id="edit_no_parts" name="no_parts"
                                placeholder="Nomor part" required>
                            @error('no_parts')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="edit_no_barang" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                No Barang
                            </label>
                            <input type="text" 
                                class="form-control w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none @error('no_barang') is-invalid @enderror"
                                id="edit_no_barang" name="no_barang"
                                placeholder="Nomor barang">
                            @error('no_barang')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="edit_type" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                Type Part <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                class="form-control w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none @error('type') is-invalid @enderror"
                                id="edit_type" name="type"
                                placeholder="Type part" required>
                            @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="edit_nama_part" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                            Nama Part Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                            class="form-control w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none @error('nama_part') is-invalid @enderror"
                            id="edit_nama_part" name="nama_part"
                            placeholder="Nama part lengkap" required>
                        @error('nama_part')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="edit_quantity" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                Quantity (Stok / Unit) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" 
                                class="form-control w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm font-mono focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none @error('quantity') is-invalid @enderror"
                                id="edit_quantity" name="quantity" min="0"
                                placeholder="0" required>
                            @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="edit_bruto" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                                Bruto (kg) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" 
                                class="form-control w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm font-mono focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none @error('bruto') is-invalid @enderror"
                                id="edit_bruto" name="bruto" min="0"
                                step="0.01" placeholder="0.00" required>
                            @error('bruto')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="modal-footer px-6 py-4 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-end gap-2.5 bg-slate-50/50 dark:bg-slate-900/50">
                    <button type="button" 
                        class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all cursor-pointer" 
                        data-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" 
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-xs bg-amber-500 hover:bg-amber-400 active:bg-amber-600 text-white shadow-md shadow-amber-500/25 transition-all cursor-pointer" 
                        id="editSubmitBtn">
                        <i class="fas fa-save text-xs"></i>
                        <span>Update Part</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>