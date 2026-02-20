<!-- Edit Modal -->
<div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-edit mr-2"></i> Edit Jadwal Kerja
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="updateForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="update_id" name="id">

                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="update_user_id">Karyawan <span class="text-danger">*</span></label>
                            <select name="user_id" id="update_user_id" class="form-control" required>
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach ($users ?? [] as $user)
                                    <option value="{{ $user->id }}">{{ $user->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="update_tanggal">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" id="update_tanggal" class="form-control" required>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="update_shift">Shift <span class="text-danger">*</span></label>
                            <select name="shift" id="update_shift" class="form-control" required>
                                <option value="">Pilih Shift</option>
                                <option value="pagi">Pagi (07:00-15:00)</option>
                                <option value="siang">Siang (15:00-23:00)</option>
                                <option value="malam">Malam (23:00-07:00)</option>
                                <option value="off">Off</option>
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="update_jam_masuk">Jam Masuk</label>
                            <input type="time" name="jam_masuk" id="update_jam_masuk" class="form-control">
                        </div>

                        <div class="form-group col-md-6">
                            <label for="update_jam_keluar">Jam Keluar</label>
                            <input type="time" name="jam_keluar" id="update_jam_keluar" class="form-control">
                        </div>

                        <div class="form-group col-md-6">
                            <label for="update_status">Status <span class="text-danger">*</span></label>
                            <select name="status" id="update_status" class="form-control" required>
                                <option value="aktif">Aktif</option>
                                <option value="tidak_aktif">Tidak Aktif</option>
                            </select>
                        </div>

                        <div class="form-group col-12">
                            <label for="update_keterangan">Keterangan</label>
                            <textarea name="keterangan" id="update_keterangan" class="form-control" rows="3" maxlength="500"
                                placeholder="Keterangan tambahan (opsional)"></textarea>
                            <small id="updateCharCount" class="text-muted">0/500</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                         Batal
                    </button>
                    <button type="submit" class="btn btn-warning">
                         Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
