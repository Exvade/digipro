<div class="modal fade" id="modalUpdate" tabindex="-1" role="dialog" aria-labelledby="modalUpdateLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="updateForm" method="POST" action="" class="formUpdatePlanning">
                @csrf
                @method('PUT')

                {{-- Header --}}
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="modalUpdateLabel">
                        <i class="fas fa-edit"></i> Edit Planning Produksi
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
                                <label for="update_bagian">Bagian <span class="text-danger">*</span></label>
                                <select name="bagian" id="update_bagian" class="form-control" required>
                                    <option value="">-- Pilih Bagian --</option>
                                    <option value="inject">Inject</option>
                                    <option value="assy">Assembly</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Informasi Mold & Mesin (untuk Inject) --}}
                    <div class="card mb-3 shadow-sm" id="update-mold-mesin-card" style="display: none;">
                        <div class="card-header bg-light">
                            <strong>Informasi Mold & Mesin</strong>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="update_moldshop_id">No Mold <span class="text-danger">*</span></label>
                                    <select name="moldshop_id" id="update_moldshop_id" class="form-control">
                                        <option value="">-- Pilih Mold --</option>
                                        @foreach ($moldshops as $m)
                                        <option value="{{ $m->id }}">{{ $m->no_mold }} -
                                            {{ $m->nama_mold }} [{{ strtoupper($m->status) }}]
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="update_mesin_id">Mesin <span class="text-danger">*</span></label>
                                    <select name="mesin_id" id="update_mesin_id" class="form-control">
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
                                            {{ $ms->nama_mesin }} [{{ $statusDisplay }}]
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Informasi Meja (untuk Assembly) --}}
                    <div class="card mb-3 shadow-sm" id="update-meja-card" style="display: none;">
                        <div class="card-header bg-light">
                            <strong>Informasi Meja</strong>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label for="update_meja_id">Meja <span class="text-danger">*</span></label>
                                    <select name="meja_id" id="update_meja_id" class="form-control">
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
                                    <label for="update_tanggal">Tanggal</label>
                                    <input type="date" name="tanggal" id="update_tanggal" class="form-control"
                                        required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="update_lot_produksi">Lot Produksi</label>
                                    <input type="text" name="lot_produksi" id="update_lot_produksi"
                                        class="form-control" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="update_part_id">Part</label>
                                    <select name="part_id" id="update_part_id" class="form-control" required>
                                        <option value="">-- Pilih Part --</option>
                                        @foreach ($parts as $p)
                                        <option value="{{ $p->id }}">{{ $p->no_parts }} -
                                            {{ $p->nama_part }} ({{ $p->type }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label for="update_qty_planning_produksi">Qty Planning Total</label>
                                    <input type="number" name="qty_planning_produksi" id="update_qty_planning_produksi"
                                        class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Qty Per Jam --}}
                    <div class="card shadow-sm">
                        <div class="card-header bg-light"><strong>Qty Per Jam</strong></div>
                        <div class="card-body table-responsive">
                            {{-- Shift 1 --}}
                            <p class="font-weight-bold">Shift 1</p>
                            <table class="table table-bordered text-center">
                                <thead class="thead-light">
                                    <tr>
                                        @for ($i = 7; $i <= 14; $i++)
                                            <th>{{ sprintf('%02d', $i) }}:00 - {{ sprintf('%02d', $i + 1) }}:00</th>
                                            @endfor
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        @for ($i = 7; $i <= 14; $i++)
                                            @php $key='pagi_jam_' . sprintf('%02d', $i); @endphp
                                            <td><input type="number" name="{{ $key }}"
                                                id="update_{{ $key }}"
                                                class="form-control form-control-sm text-center"></td>
                                            @endfor
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="8" class="text-right font-weight-bold">Total: <span id="update_total_pagi">0</span></td>
                                    </tr>
                                </tfoot>
                            </table>

                            {{-- Shift 2 --}}
                            <p class="font-weight-bold">Shift 2</p>
                            <table class="table table-bordered text-center">
                                <thead class="thead-light">
                                    <tr>
                                        @for ($i = 15; $i <= 22; $i++)
                                            <th>{{ sprintf('%02d', $i) }}:00 - {{ sprintf('%02d', $i + 1) }}:00</th>
                                            @endfor
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        @for ($i = 15; $i <= 22; $i++)
                                            @php $key='siang_jam_' . sprintf('%02d', $i); @endphp
                                            <td><input type="number" name="{{ $key }}"
                                                id="update_{{ $key }}"
                                                class="form-control form-control-sm text-center"></td>
                                            @endfor
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="8" class="text-right font-weight-bold">Total: <span id="update_total_siang">0</span></td>
                                    </tr>
                                </tfoot>
                            </table>

                            {{-- Shift 3 --}}
                            <p class="font-weight-bold">Shift 3</p>
                            <table class="table table-bordered text-center">
                                <thead class="thead-light">
                                    <tr>
                                        @foreach ([23, 0, 1, 2, 3, 4, 5, 6] as $i)
                                        <th>{{ sprintf('%02d', $i) }}:00 - {{ sprintf('%02d', ($i + 1) % 24) }}:00</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        @foreach ([23, 0, 1, 2, 3, 4, 5, 6] as $i)
                                        @php $key = 'malam_jam_' . sprintf('%02d', $i); @endphp
                                        <td><input type="number" name="{{ $key }}"
                                                id="update_{{ $key }}"
                                                class="form-control form-control-sm text-center"></td>
                                        @endforeach
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="8" class="text-right font-weight-bold">Total: <span id="update_total_malam">0</span></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {{-- Target & Catatan --}}
                    <div class="card mt-3 shadow-sm">
                        <div class="card-header bg-light"><strong>Target Produksi & Catatan</strong></div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label for="update_target_shift_pagi">Target Shift Pagi</label>
                                    <input type="number" name="target_shift_pagi" id="update_target_shift_pagi"
                                        class="form-control">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="update_target_shift_siang">Target Shift Siang</label>
                                    <input type="number" name="target_shift_siang" id="update_target_shift_siang"
                                        class="form-control">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="update_target_shift_malam">Target Shift Malam</label>
                                    <input type="number" name="target_shift_malam" id="update_target_shift_malam"
                                        class="form-control">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="update_total_shift">Total Shift <small class="text-muted">(Otomatis)</small></label>
                                    <input type="number" name="total_shift" id="update_total_shift"
                                        class="form-control bg-light" readonly>
                                    <small class="form-text text-muted">Total otomatis dari penjumlahan target shift</small>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="update_catatan">Catatan</label>
                                <textarea name="catatan" id="update_catatan" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-white">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/planning/update.js') }}"></script>
@endpush