<!-- Edit Modal -->
<div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 rounded-2xl shadow-2xl overflow-hidden bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800">
            <form id="updateForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="update_id" name="id">

                <!-- Modal Header -->
                <div class="px-6 py-4 bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent dark:from-amber-500/20 border-b border-amber-200/50 dark:border-amber-900/40 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-sm">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div>
                            <h5 class="text-base font-bold text-slate-800 dark:text-slate-100 mb-0" id="updateModalLabel">
                                Edit Jadwal Kerja
                            </h5>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-0">Perbarui penugasan shift atau jam kerja karyawan</p>
                        </div>
                    </div>
                    <button type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-1" data-dismiss="modal" aria-label="Close">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <!-- Karyawan -->
                        <div>
                            <label for="update_user_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Karyawan <span class="text-rose-500">*</span>
                            </label>
                            <select name="user_id" id="update_user_id" class="w-full px-3.5 py-2.5 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all" required>
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach ($users ?? [] as $user)
                                    <option value="{{ $user->id }}">{{ $user->nama }} ({{ ucfirst($user->bagian ?? 'Produksi') }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tanggal -->
                        <div>
                            <label for="update_tanggal" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Tanggal <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="tanggal" id="update_tanggal" class="w-full px-3.5 py-2.5 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all cursor-pointer" required>
                        </div>

                        <!-- Shift -->
                        <div>
                            <label for="update_shift" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Shift <span class="text-rose-500">*</span>
                            </label>
                            <select name="shift" id="update_shift" class="w-full px-3.5 py-2.5 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all" required>
                                <option value="">-- Pilih Shift --</option>
                                <option value="pagi">Pagi (07:00 - 15:00)</option>
                                <option value="siang">Siang (15:00 - 23:00)</option>
                                <option value="malam">Malam (23:00 - 07:00)</option>
                                <option value="off">Off (Libur / 🚫)</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="update_status" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Status <span class="text-rose-500">*</span>
                            </label>
                            <select name="status" id="update_status" class="w-full px-3.5 py-2.5 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all" required>
                                <option value="aktif">✅ Aktif</option>
                                <option value="tidak_aktif">❌ Tidak Aktif</option>
                            </select>
                        </div>

                        <!-- Jam Masuk -->
                        <div>
                            <label for="update_jam_masuk" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Jam Masuk
                            </label>
                            <input type="time" name="jam_masuk" id="update_jam_masuk" class="w-full px-3.5 py-2.5 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all">
                        </div>

                        <!-- Jam Keluar -->
                        <div>
                            <label for="update_jam_keluar" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Jam Keluar
                            </label>
                            <input type="time" name="jam_keluar" id="update_jam_keluar" class="w-full px-3.5 py-2.5 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all">
                        </div>

                        <!-- Hidden / Bagian helper -->
                        <input type="hidden" id="update_bagian" name="bagian">

                        <!-- Keterangan -->
                        <div class="col-span-1 md:col-span-2">
                            <label for="update_keterangan" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Keterangan
                            </label>
                            <textarea name="keterangan" id="update_keterangan" rows="2" maxlength="500" class="w-full px-3.5 py-2.5 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all" placeholder="Catatan perubahan..."></textarea>
                            <div class="flex justify-between items-center text-[11px] text-slate-400 dark:text-slate-500 mt-1">
                                <span>Maksimal 500 karakter</span>
                                <span id="updateCharCount">0/500</span>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-3.5 bg-slate-50/80 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2.5">
                    <button type="button" class="btn-modal-cancel px-4 py-2 rounded-xl border font-bold text-xs inline-flex items-center gap-1.5 shadow-sm transition-all" data-dismiss="modal">
                        <i class="fas fa-times text-[10px]"></i>
                        <span>Batal</span>
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs inline-flex items-center gap-1.5 shadow-sm shadow-amber-500/20 transition-all cursor-pointer">
                        <i class="fas fa-check text-[10px]"></i>
                        <span>Perbarui Jadwal</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
