<?php

namespace App\Http\Requests\Mesin;

use Illuminate\Foundation\Http\FormRequest;

class StoreMesinRequest extends FormRequest
{
    public function authorize()
    {
        $user = auth()->user();
        if (!$user) return false;
        
        $isRMStaff = $user->role === 'staff' && $user->departemen === 'Repair & Maintenance';
        
        // Only RM Staff or higher (Super Admin/Manager) can store machines
        if (in_array($user->role, ['leader', 'staff']) && !$isRMStaff) {
            return false;
        }

        return true;
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
        ];
    }

    protected function prepareForValidation()
    {
        // Enforce stop reason logic internally instead of hard validation trickery if needed
        if (!in_array($this->status, ['stop', 'maintenance', 'offline'])) {
            $this->merge(['stop_reason' => null]);
        }
    }
}
