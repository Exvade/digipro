<div class="modal fade" id="modalCreate" tabindex="-1" role="dialog" aria-labelledby="modalCreateLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('planning.store') }}" method="POST" id="formCreatePlanning">
                @csrf

                {{-- Header --}}
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalCreateLabel">
                        <i class="fas fa-plus-circle"></i> Tambah Planning Produksi
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                {{-- Body --}}
                <div class="modal-body">

                    {{-- Pilihan Bagian --}}
                    <div class="card mb-3 shadow-sm">
                        <div class="card-header bg-light">
                            <strong>Pilihan Bagian</strong>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="bagian">Bagian <span class="text-danger">*</span></label>
                                <select name="bagian" id="bagian" class="form-control" required>
                                    <option value="inject" selected>Inject</option>
                                </select>
                                <small class="form-text text-muted">Pilih bagian produksi yang akan dibuat planning</small>
                            </div>
                        </div>
                    </div>

                    {{-- Informasi Mold & Mesin (untuk Inject) --}}
                    <div class="card mb-3 shadow-sm" id="mold-mesin-card" style="display: none;">
                        <div class="card-header bg-light">
                            <strong>Informasi Mold & Mesin</strong>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="moldshop_id">No Mold <span class="text-danger">*</span></label>
                                    <select name="moldshop_id" id="moldshop_id" class="form-control">
                                        <option value="">-- Pilih Mold --</option>
                                        @foreach ($moldshops as $m)
                                        @php
                                        $statusUpper = strtoupper($m->status);
                                        $isDisabled = in_array($statusUpper, ['REPAIR', 'PREVENTIVE']);
                                        @endphp
                                        <option value="{{ $m->id }}" {{ $isDisabled ? 'disabled' : '' }}>
                                            {{ $m->no_mold }} - {{ $m->nama_mold }} [{{ $statusUpper }}]
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="mesin_id">Mesin <span class="text-danger">*</span></label>
                                    <select name="mesin_id" id="mesin_id" class="form-control">
                                        <option value="">-- Pilih Mesin --</option>
                                        @foreach ($mesins as $ms)
                                        @php
                                        $statusLower = strtolower($ms->status);
                                        $statusDisplay = '';
                                        if($statusLower == 'running') $statusDisplay = 'Running';
                                        elseif($statusLower == 'maintenance') $statusDisplay = 'BREAKDOWN';
                                        elseif($statusLower == 'offline') $statusDisplay = 'OFF';
                                        elseif($statusLower == 'stop') $statusDisplay = 'STAND BY';
                                        else $statusDisplay = ucfirst($statusLower);

                                        $isDisabled = in_array($statusLower, ['maintenance']);
                                        @endphp
                                        <option value="{{ $ms->id }}" {{ $isDisabled ? 'disabled' : '' }}>
                                            {{ $ms->nama_mesin }} - {{ $ms->brand_mesin }} [{{ $statusDisplay }}]
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Informasi Meja (untuk Assembly) --}}
                    <div class="card mb-3 shadow-sm" id="meja-card" style="display: none;">
                        <div class="card-header bg-light">
                            <strong>Informasi Meja</strong>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label for="meja_id">Meja <span class="text-danger">*</span></label>
                                    <select name="meja_id" id="meja_id" class="form-control">
                                        <option value="">-- Pilih Meja --</option>
                                        @foreach ($mejas as $mj)
                                        @php
                                        $statusMeja = strtolower($mj->status ?? 'aktif');
                                        $isDisabled = in_array($statusMeja, ['tidak_aktif', 'maintenance', 'rusak']);
                                        // Format status untuk ditampilkan (ganti underscore dengan spasi)
                                        $statusDisplay = str_replace('_', ' ', ucwords(str_replace('_', ' ', $mj->status ?? 'aktif')));
                                        @endphp
                                        <option value="{{ $mj->id_meja }}" {{ $isDisabled ? 'disabled' : '' }}>
                                            {{ $mj->nama_meja }}
                                            @if($mj->lokasi) - {{ $mj->lokasi }} @endif
                                            [{{ $statusDisplay }}]
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Informasi Produksi --}}
                    <div class="card mb-3 shadow-sm">
                        <div class="card-header bg-light">
                            <strong>Informasi Produksi</strong>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="tanggal">Tanggal</label>
                                    <input type="date" name="tanggal" id="tanggal" class="form-control" required
                                        min="{{ date('Y-m-d') }}">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="lot_produksi">Lot Produksi</label>
                                    <input type="text" name="lot_produksi" id="lot_produksi" class="form-control"
                                        required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="part_id">Part</label>
                                    <select name="part_id" id="part_id" class="form-control" required>
                                        <option value="">-- Pilih Part --</option>
                                        @foreach ($parts as $p)
                                        <option value="{{ $p->id }}">
                                            {{ $p->no_parts }} - {{ $p->nama_part }} ({{ $p->type }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="qty_planning_produksi">Qty Planning Total</label>
                                    <input type="number" name="qty_planning_produksi" id="qty_planning_produksi"
                                        class="form-control" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="target_shift_pagi">Target Shift Pagi</label>
                                    <input type="number" name="target_shift_pagi" id="target_shift_pagi"
                                        class="form-control" value="0">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="target_shift_siang">Target Shift Siang</label>
                                    <input type="number" name="target_shift_siang" id="target_shift_siang"
                                        class="form-control" value="0">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="target_shift_malam">Target Shift Malam</label>
                                    <input type="number" name="target_shift_malam" id="target_shift_malam"
                                        class="form-control" value="0">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="total_shift">Total Shift <small class="text-muted">(Otomatis)</small></label>
                                    <input type="number" name="total_shift" id="total_shift"
                                        class="form-control bg-light" value="0" readonly>
                                    <small class="form-text text-muted">Total otomatis dari penjumlahan target shift</small>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="catatan">Catatan</label>
                                <textarea name="catatan" id="catatan" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Qty Per Jam --}}
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <strong>Qty Per Jam</strong>
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-bordered text-center">
                                <thead class="thead-light">
                                    <P>Shift 1</P>
                                    <tr>
                                        <th>07:00 - 08:00</th>
                                        <th>08:00 - 09:00</th>
                                        <th>09:00 - 10:00</th>
                                        <th>10:00 - 11:00</th>
                                        <th>11:00 - 12:00</th>
                                        <th>12:00 - 13:00</th>
                                        <th>13:00 - 14:00</th>
                                        <th>14:00 - 15:00</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        @for ($i = 7; $i <= 14; $i++)
                                            <td>
                                            <input type="number" name="pagi_jam_{{ sprintf('%02d', $i) }}"
                                                class="form-control form-control-sm text-center" value="0">
                                            </td>
                                            @endfor
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="8" class="text-right font-weight-bold">Total: <span id="total_pagi">0</span></td>
                                    </tr>
                                </tfoot>
                            </table>

                            <table class="table table-bordered text-center mt-2">
                                <thead class="thead-light">
                                    <P>Shift 2</P>
                                    <tr>
                                        <th>15:00 - 16:00</th>
                                        <th>16:00 - 17:00</th>
                                        <th>17:00 - 18:00</th>
                                        <th>18:00 - 19:00</th>
                                        <th>19:00 - 20:00</th>
                                        <th>20:00 - 21:00</th>
                                        <th>21:00 - 22:00</th>
                                        <th>22:00 - 23:00</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        @for ($i = 15; $i <= 22; $i++)
                                            <td>
                                            <input type="number" name="siang_jam_{{ sprintf('%02d', $i) }}"
                                                class="form-control form-control-sm text-center" value="0">
                                            </td>
                                            @endfor
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="8" class="text-right font-weight-bold">Total: <span id="total_siang">0</span></td>
                                    </tr>
                                </tfoot>
                            </table>

                            <table class="table table-bordered text-center mt-2">
                                <thead class="thead-light">
                                    <P>Shift 3</P>
                                    <tr>
                                        <th>23:00 - 24:00</th>
                                        <th>24:00 - 01:00</th>
                                        <th>01:00 - 02:00</th>
                                        <th>02:00 - 03:00</th>
                                        <th>03:00 - 04:00</th>
                                        <th>04:00 - 05:00</th>
                                        <th>05:00 - 06:00</th>
                                        <th>06:00 - 07:00</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        @foreach ([23, 0, 1, 2, 3, 4, 5, 6] as $i)
                                        <td>
                                            <input type="number" name="malam_jam_{{ sprintf('%02d', $i) }}"
                                                class="form-control form-control-sm text-center" value="0">
                                        </td>
                                        @endforeach
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="8" class="text-right font-weight-bold">Total: <span id="total_malam">0</span></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/planning/create.js') }}"></script>
@endpush