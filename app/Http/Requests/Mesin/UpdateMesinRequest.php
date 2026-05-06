<?php

namespace App\Http\Requests\Mesin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMesinRequest extends FormRequest
{
    public function authorize()
    {
        $user = auth()->user();
        if (!$user) return false;

        $isRMStaff = $user->role === 'staff' && $user->departemen === 'Repair & Maintenance';
        
        if ($user->role === 'staff' && !$isRMStaff) {
            return false;
        }
        return true;
    }

    protected function prepareForValidation()
    {
        $user = auth()->user();
        $isRMStaff = $user->role === 'staff' && $user->departemen === 'Repair & Maintenance';
        
        // Leaders can only update status/keterangan/reason, not machine details
        if ($user->role === 'leader' && !$isRMStaff) {
            $mesin = $this->route('mesin');
            if ($mesin) {
                $this->merge([
                    'nama_mesin' => $mesin->nama_mesin,
                    'brand_mesin' => $mesin->brand_mesin,
                    'tonase_mesin' => $mesin->tonase_mesin,
                ]);
            }
        }

        // Wipe stop reason if status is running
        if (!in_array($this->status, ['stop', 'maintenance', 'offline'])) {
            $this->merge(['stop_reason' => null]);
        }
    }

    public function rules()
    {
        return [
            'nama_mesin' => 'required|string|max:255',
            'brand_mesin' => 'required|string|max:255',
            'tonase_mesin' => 'required|numeric|min:0|max:999999',
            'status' => 'required|in:running,stop,maintenance,offline',
            'stop_reason' => 'nullable|required_if:status,stop,maintenance,offline',
            'keterangan' => 'nullable|string',
            'part_id' => 'nullable|exists:parts,id',
            'target_produksi' => 'nullable|numeric|min:1',
            'lot_produksi' => 'nullable|string',
        ];
    }
}
