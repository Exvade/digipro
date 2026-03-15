<div class="modal fade" id="modalShow" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content border-0 rounded-lg shadow">

            {{-- Header --}}
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-eye"></i> Detail Planning Produksi
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            {{-- Body --}}
            <div class="modal-body p-4" id="modalShowContent">
                <div class="row">

                    {{-- Kolom Kiri: Info Utama --}}
                    <div class="col-md-5">
                        <h6 class="mb-3"><i class="fas fa-info-circle"></i> Informasi Umum</h6>
                        <table class="table table-sm table-bordered">
                            <tbody>
                                <tr>
                                    <th style="width:130px;">Bagian</th>
                                    <td>
                                        <span class="badge badge-primary" id="show-bagian-badge">-</span>
                                    </td>
                                </tr>
                                <tr id="row-no_mold" style="display:none;">
                                    <th>No Mold</th>
                                    <td id="show-no_mold">-</td>
                                </tr>
                                <tr id="row-mesin" style="display:none;">
                                    <th>Mesin</th>
                                    <td id="show-mesin">-</td>
                                </tr>
                                <tr id="row-meja" style="display:none;">
                                    <th>Meja</th>
                                    <td id="show-meja">-</td>
                                </tr>
                                <tr>
                                    <th>Tanggal</th>
                                    <td id="show-tanggal">-</td>
                                </tr>
                                <tr>
                                    <th>Lot Produksi</th>
                                    <td><span class="badge badge-primary" id="show-lot">-</span></td>
                                </tr>
                                <tr>
                                    <th>No Part</th>
                                    <td id="show-no_part">-</td>
                                </tr>
                                <tr>
                                    <th>No Barang</th>
                                    <td id="show-no_barang">-</td>
                                </tr>
                                <tr>
                                    <th>Nama Part</th>
                                    <td id="show-nama_part">-</td>
                                </tr>
                                <tr>
                                    <th>Type</th>
                                    <td id="show-type">-</td>
                                </tr>
                                <tr>
                                    <th>Qty Planning</th>
                                    <td><strong class="text-primary" id="show-qty">-</strong></td>
                                </tr>
                                <tr>
                                    <th>Status Approval</th>
                                    <td id="show-status-approval">-</td>
                                </tr>
                                <tr>
                                    <th>Catatan</th>
                                    <td id="show-catatan">-</td>
                                </tr>
                            </tbody>
                        </table>

                        {{-- Operator List --}}
                        <h6 class="mb-3 mt-4"><i class="fas fa-users"></i> Operator Bertugas</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Operator</th>
                                        <th>Grup</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="show-operators-body">
                                    {{-- Populated by JS --}}
                                </tbody>
                            </table>
                        </div>

                        {{-- Target Shift --}}
                        <h6 class="mb-3 mt-4"><i class="fas fa-target"></i> Target Per Shift</h6>
                        <table class="table table-sm table-bordered">
                            <tbody>
                                <tr>
                                    <th style="width:130px;">Target Shift Pagi</th>
                                    <td><strong class="text-warning" id="show-target_shift_pagi">0</strong></td>
                                </tr>
                                <tr>
                                    <th>Target Shift Siang</th>
                                    <td><strong class="text-info" id="show-target_shift_siang">0</strong></td>
                                </tr>
                                <tr>
                                    <th>Target Shift Malam</th>
                                    <td><strong class="text-secondary" id="show-target_shift_malam">0</strong></td>
                                </tr>
                                <tr>
                                    <th>Total Shift</th>
                                    <td><strong class="text-success" id="show-total_shift">0</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Kolom Kanan: Detail Per Shift --}}
                    <div class="col-md-7">
                        <h6 class="mb-3"><i class="fas fa-clock"></i> Planning Produksi Per Jam</h6>

                        {{-- Shift 1 (Pagi) --}}
                        <div class="card mb-2 shadow-sm">
                            <div class="card-header bg-warning text-dark py-2">
                                <strong><i class="fas fa-sun"></i> SHIFT 1 (07:00 - 15:00)</strong>
                            </div>
                            <div class="card-body p-2">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered text-center mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                @for ($i = 7; $i <= 14; $i++)
                                                    <th style="font-size:11px;">{{ sprintf('%02d:00', $i) }}</th>
                                                    @endfor
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr id="shift-pagi-row">
                                                @for ($i = 7; $i <= 14; $i++)
                                                    <td id="pagi_jam_{{ sprintf('%02d', $i) }}">0</td>
                                                    @endfor
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-light">
                                                <td colspan="8" class="text-right">
                                                    <strong>Total: <span id="total-pagi">0</span></strong>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Shift 2 (Siang) --}}
                        <div class="card mb-2 shadow-sm">
                            <div class="card-header bg-info text-white py-2">
                                <strong><i class="fas fa-cloud-sun"></i> SHIFT 2 (15:00 - 23:00)</strong>
                            </div>
                            <div class="card-body p-2">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered text-center mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                @for ($i = 15; $i <= 22; $i++)
                                                    <th style="font-size:11px;">{{ sprintf('%02d:00', $i) }}</th>
                                                    @endfor
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr id="shift-siang-row">
                                                @for ($i = 15; $i <= 22; $i++)
                                                    <td id="siang_jam_{{ sprintf('%02d', $i) }}">0</td>
                                                    @endfor
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-light">
                                                <td colspan="8" class="text-right">
                                                    <strong>Total: <span id="total-siang">0</span></strong>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Shift 3 (Malam) --}}
                        <div class="card mb-2 shadow-sm">
                            <div class="card-header bg-dark text-white py-2">
                                <strong><i class="fas fa-moon"></i> SHIFT 3 (23:00 - 07:00)</strong>
                            </div>
                            <div class="card-body p-2">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered text-center mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                @foreach ([23, 0, 1, 2, 3, 4, 5, 6] as $i)
                                                <th style="font-size:11px;">{{ sprintf('%02d:00', $i) }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr id="shift-malam-row">
                                                @foreach ([23, 0, 1, 2, 3, 4, 5, 6] as $i)
                                                <td id="malam_jam_{{ sprintf('%02d', $i) }}">0</td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-light">
                                                <td colspan="8" class="text-right">
                                                    <strong>Total: <span id="total-malam">0</span></strong>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Grand Total --}}
                        <div class="alert alert-primary mb-0">
                            <div class="row">
                                <div class="col-6">
                                    <strong><i class="fas fa-calculator"></i> GRAND TOTAL:</strong>
                                </div>
                                <div class="col-6 text-right">
                                    <h5 class="mb-0">
                                        <strong id="show-grandtotal">0</strong>
                                    </h5>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Tutup
                </button>
            </div>

        </div>
    </div>
</div>