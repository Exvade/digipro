<!-- General modal form for other roles -->
<div class="modal fade" id="createReportModal" tabindex="-1" role="dialog" aria-labelledby="createReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createReportModalLabel">
                    <i class="fas fa-file-alt mr-2"></i>Buat Laporan
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="createReportForm">
                <div class="modal-body">
                    <!-- Hidden input for department type -->
                    <input type="hidden" name="department_type" value="general">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="report_type">Jenis Laporan <span class="text-danger">*</span></label>
                                <select class="form-control" id="report_type" name="report_type" required>
                                    <option value="">Pilih Jenis Laporan</option>
                                    <option value="general">Semua Laporan</option>
                                    <option value="assy">Laporan Assy</option>
                                    <option value="inject">Laporan Inject</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="approval_status">Status Approval</label>
                                <select class="form-control" id="approval_status" name="approval_status">
                                    <option value="">Semua Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date">Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_date">Tanggal Akhir <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="end_date" name="end_date" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="mesin_id">Mesin</label>
                                <select class="form-control" id="mesin_id" name="mesin_id">
                                    <option value="">Semua Mesin</option>
                                    @foreach($mesins ?? [] as $mesin)
                                    <option value="{{ $mesin->id }}">{{ $mesin->nama_mesin }} - {{ $mesin->brand_mesin }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="meja_id">Meja</label>
                                <select class="form-control" id="meja_id" name="meja_id">
                                    <option value="">Semua Meja</option>
                                    @foreach($mejas ?? [] as $meja)
                                    <option value="{{ $meja->id_meja }}">{{ $meja->nama_meja }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="part_id">Part</label>
                                <select class="form-control" id="part_id" name="part_id">
                                    <option value="">Semua Part</option>
                                    @foreach($parts ?? [] as $part)
                                    <option value="{{ $part->id }}">{{ $part->nama_part }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="shift">Shift</label>
                                <select class="form-control" id="shift" name="shift">
                                    <option value="">Semua Shift</option>
                                    <option value="pagi">Pagi</option>
                                    <option value="siang">Siang</option>
                                    <option value="malam">Malam</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="user_id">User</label>
                                <select class="form-control" id="user_id" name="user_id">
                                    <option value="">Semua User</option>
                                    @foreach($users ?? [] as $user)
                                    <option value="{{ $user->id }}" data-grup-id="{{ $user->grup_id }}">
                                        {{ $user->nama }} ({{ $user->username }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="grup_id">Grup</label>
                                <select class="form-control" id="grup_id" name="grup_id">
                                    <option value="">Semua Grup</option>
                                    @foreach($grups ?? [] as $grup)
                                    <option value="{{ $grup->id }}">{{ $grup->nama_grup }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Filter berdasarkan grup kerja
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search mr-1"></i>Generate Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> 