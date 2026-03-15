<?php

namespace App\Http\Requests\Planning;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bagian'      => 'required|in:inject,assy',
            'moldshop_id' => 'required_if:bagian,inject|nullable|exists:moldshops,id',
            'mesin_id'    => 'required_if:bagian,inject|nullable|exists:mesin,id',
            'meja_id'     => 'required_if:bagian,assy|nullable|exists:meja,id_meja',
            'part_id'     => 'required|exists:parts,id',
            'tanggal'     => 'required|date',
            'lot_produksi' => 'required|string|max:255',
            'qty_planning_produksi' => 'required|integer|min:0',

            'pagi_jam_07' => 'nullable|integer|min:0',
            'pagi_jam_08' => 'nullable|integer|min:0',
            'pagi_jam_09' => 'nullable|integer|min:0',
            'pagi_jam_10' => 'nullable|integer|min:0',
            'pagi_jam_11' => 'nullable|integer|min:0',
            'pagi_jam_12' => 'nullable|integer|min:0',
            'pagi_jam_13' => 'nullable|integer|min:0',
            'pagi_jam_14' => 'nullable|integer|min:0',

            'siang_jam_15' => 'nullable|integer|min:0',
            'siang_jam_16' => 'nullable|integer|min:0',
            'siang_jam_17' => 'nullable|integer|min:0',
            'siang_jam_18' => 'nullable|integer|min:0',
            'siang_jam_19' => 'nullable|integer|min:0',
            'siang_jam_20' => 'nullable|integer|min:0',
            'siang_jam_21' => 'nullable|integer|min:0',
            'siang_jam_22' => 'nullable|integer|min:0',

            'malam_jam_23' => 'nullable|integer|min:0',
            'malam_jam_00' => 'nullable|integer|min:0',
            'malam_jam_01' => 'nullable|integer|min:0',
            'malam_jam_02' => 'nullable|integer|min:0',
            'malam_jam_03' => 'nullable|integer|min:0',
            'malam_jam_04' => 'nullable|integer|min:0',
            'malam_jam_05' => 'nullable|integer|min:0',
            'malam_jam_06' => 'nullable|integer|min:0',

            'target_shift_pagi' => 'nullable|integer|min:0',
            'target_shift_siang' => 'nullable|integer|min:0',
            'target_shift_malam' => 'nullable|integer|min:0',

            'catatan' => 'nullable|string',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->all();

            // Validate Pagi
            $totalPagi = 0;
            for ($i = 7; $i <= 14; $i++) {
                $totalPagi += $data['pagi_jam_' . sprintf('%02d', $i)] ?? 0;
            }
            if ($totalPagi > ($data['target_shift_pagi'] ?? 0)) {
                $validator->errors()->add('target_shift_pagi', "Total jam shift pagi ($totalPagi) melebihi target shift pagi (" . ($data['target_shift_pagi'] ?? 0) . ").");
            }

            // Validate Siang
            $totalSiang = 0;
            for ($i = 15; $i <= 22; $i++) {
                $totalSiang += $data['siang_jam_' . sprintf('%02d', $i)] ?? 0;
            }
            if ($totalSiang > ($data['target_shift_siang'] ?? 0)) {
                $validator->errors()->add('target_shift_siang', "Total jam shift siang ($totalSiang) melebihi target shift siang (" . ($data['target_shift_siang'] ?? 0) . ").");
            }

            // Validate Malam
            $totalMalam = 0;
            foreach ([23, 0, 1, 2, 3, 4, 5, 6] as $i) {
                $totalMalam += $data['malam_jam_' . sprintf('%02d', $i)] ?? 0;
            }
            if ($totalMalam > ($data['target_shift_malam'] ?? 0)) {
                $validator->errors()->add('target_shift_malam', "Total jam shift malam ($totalMalam) melebihi target shift malam (" . ($data['target_shift_malam'] ?? 0) . ").");
            }
        });
    }
}
