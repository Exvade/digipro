<div class="modal fade" id="modalCreate" tabindex="-1" role="dialog" aria-labelledby="modalCreateLabel" aria-hidden="true" data-keyboard="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-2xl rounded-3xl overflow-hidden bg-white dark:bg-slate-900 transition-colors duration-300">
            <form action="{{ route('moldshop.store') }}" method="POST">
                @csrf
                <!-- Modal Header -->
                <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 via-indigo-600 to-indigo-700 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-md flex items-center justify-center text-white text-lg shadow-sm border border-white/20">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <div>
                            <h5 class="text-base font-extrabold tracking-tight mb-0.5 text-white" id="modalCreateLabel">
                                Tambah Data Master Mold
                            </h5>
                            <p class="text-xs text-indigo-100/90 font-medium mb-0">
                                Masukkan spesifikasi dan status unit mold baru
                            </p>
                        </div>
                    </div>
                    <button type="button" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center text-sm transition-all focus:outline-none" data-dismiss="modal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4">
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Nomor Mold <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                                    <i class="fas fa-fingerprint text-xs"></i>
                                </span>
                                <input type="text" name="no_mold" class="form-control pl-10 h-11 text-xs rounded-xl font-mono font-bold bg-slate-50 dark:bg-slate-800/80 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:bg-white dark:focus:bg-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20" placeholder="Contoh: MLD-001" required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Nama Mold <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                                    <i class="fas fa-cube text-xs"></i>
                                </span>
                                <input type="text" name="nama_mold" class="form-control pl-10 h-11 text-xs rounded-xl font-semibold bg-slate-50 dark:bg-slate-800/80 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:bg-white dark:focus:bg-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20" placeholder="Masukkan nama cetakan/mold" required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Tipe / Spesifikasi <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                                    <i class="fas fa-tag text-xs"></i>
                                </span>
                                <input type="text" name="type" class="form-control pl-10 h-11 text-xs rounded-xl font-medium bg-slate-50 dark:bg-slate-800/80 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:bg-white dark:focus:bg-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20" placeholder="Contoh: 2 Cavity / 4 Cavity" required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Customer / Perusahaan <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                                    <i class="fas fa-building text-xs"></i>
                                </span>
                                <input type="text" name="customer" class="form-control pl-10 h-11 text-xs rounded-xl font-medium bg-slate-50 dark:bg-slate-800/80 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:bg-white dark:focus:bg-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20" placeholder="Masukkan nama customer" required>
                            </div>
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Status Operasional <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                                    <i class="fas fa-heartbeat text-xs"></i>
                                </span>
                                <select name="status" class="form-control pl-10 h-11 text-xs rounded-xl font-bold bg-slate-50 dark:bg-slate-800/80 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:bg-white dark:focus:bg-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20" required>
                                    <option value="OK" selected class="text-emerald-600 font-bold">🟢 OK (Siap Digunakan)</option>
                                    <option value="REPAIR" class="text-rose-600 font-bold">🔴 REPAIR (Dalam Perbaikan)</option>
                                    <option value="PREVENTIVE" class="text-amber-600 font-bold">🟡 PREVENTIVE (Jadwal Pemeliharaan)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
                    <button type="button" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold text-xs transition-all" data-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-600 text-white font-extrabold text-xs shadow-md shadow-indigo-500/25 hover:shadow-indigo-500/40 inline-flex items-center gap-2 transition-all">
                        <i class="fas fa-save"></i>
                        <span>Simpan Data Mold</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>