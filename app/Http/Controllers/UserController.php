<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Grup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();
        return view('user.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $grups = Grup::all();
        return view('user.create', compact('grups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nik' => 'required|numeric|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'departemen' => 'required|string|max:255',
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:super_admin,leader,operator,foreman,kabag,staff,manager_plant',
            'bagian' => 'nullable|in:inject,assy',
            'grup_id' => 'nullable|exists:grups,id',
        ]);
        
        User::create([
            'nik' => $request->nik,
            'username' => $request->username,
            'nama' => $request->nama,
            'departemen' => $request->departemen,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'bagian' => $request->bagian,
            'grup_id' => $request->grup_id,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data user berhasil ditambahkan.'
            ]);
        }

        return redirect()->route('user.index')
            ->with('success', 'Data user berhasil ditambahkan.');
    }

    /**
     * Display the specified resource (in modal).
     */
    public function show(User $user)
    {
        return view('user.show-modal', compact('user'));
    }

    /**
     * Show the specified resource in modal (alias for backward compatibility).
     */
    public function showModal(User $user)
    {
        return $this->show($user);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $grups = Grup::all();
        return view('user.edit', compact('user', 'grups'));
    }

    /**
     * Show the form for editing the specified resource in modal.
     */
    public function editModal(User $user)
    {
        $grups = Grup::all();
        return view('user.update', compact('user', 'grups'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'nik' => 'required|numeric|unique:users,nik,' . $user->id,
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'departemen' => 'required|string|max:255',
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:super_admin,leader,operator,foreman,kabag,staff,manager_plant',
            'bagian' => 'nullable|in:inject,assy',
            'grup_id' => 'nullable|exists:grups,id',
        ]);
        
        $data = [
            'nik' => $request->nik,
            'username' => $request->username,
            'departemen' => $request->departemen,
            'nama' => $request->nama,
            'email' => $request->email,
            'role' => $request->role,
            'bagian' => $request->bagian,
            'grup_id' => $request->grup_id,
        ];        

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data user berhasil diperbarui.'
            ]);
        }

        return redirect()->route('user.index')
            ->with('success', 'Data user berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return redirect()->route('user.index')
                ->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('user.index')
            ->with('success', 'Data user berhasil dihapus.');
    }

    /**
     * Get users by grup
     */
    public function getByGrup(Request $request)
    {
        $grup_id = $request->grup_id;
        $users = User::where('grup_id', $grup_id)->get();
        return response()->json($users);
    }

    /**
     * Get available grups by bagian
     */
    public function getGrupsByBagian(Request $request)
    {
        $bagian = $request->bagian;
        $grups = Grup::where('bagian', $bagian)->get();
        return response()->json($grups);
    }
}