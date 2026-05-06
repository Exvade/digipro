<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class JadwalKerjaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Hanya yang bukan leader, foreman, dan operator yang boleh
        return !in_array(Auth::user()->role, ['leader', 'foreman', 'operator']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id'    => 'required|exists:users,id',
            'tanggal'    => 'required|date',
            'shift'      => 'required|in:pagi,siang,malam,off',
            'status'     => 'required|in:aktif,tidak_aktif',
            'jam_masuk'  => ['nullable', 'string', 'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'jam_keluar' => ['nullable', 'string', 'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'keterangan' => 'nullable|string|max:500',
        ];
    }

    /**
     * Custom error messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'jam_masuk.regex' => 'Format jam masuk harus H:i (contoh: 7:00 atau 07:00)',
            'jam_keluar.regex' => 'Format jam keluar harus H:i (contoh: 7:00 atau 07:00)',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        if ($this->has('jam_masuk') || $this->has('jam_keluar')) {
            $this->merge([
                'jam_masuk' => $this->normalizeTime($this->jam_masuk),
                'jam_keluar' => $this->normalizeTime($this->jam_keluar),
            ]);
        }
    }

    /**
     * Normalize the time to remove leading zero if hours < 10.
     *
     * @param string|null $time
     * @return string|null
     */
    private function normalizeTime($time)
    {
        if (empty($time)) {
            return null;
        }

        if (preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
            $parts = explode(':', $time);
            return (int)$parts[0] . ':' . $parts[1];
        }

        return $time;
    }
}
