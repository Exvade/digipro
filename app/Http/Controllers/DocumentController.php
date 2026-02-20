<?php

namespace App\Http\Controllers;

use App\Models\Meja;
use App\Models\MejaDocument;
use App\Models\Mesin;
use App\Models\MesinDocument;
use App\Models\Part;
use App\Models\PlanningProduksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index()
    {
        // Get all mesins with plannings, then sort by natural order
        $mesins = Mesin::with(['plannings' => function ($query) {
            $query->where('tanggal', '>=', now()->format('Y-m-d'))
                ->with('part')
                ->orderBy('tanggal', 'asc')
                ->orderBy('id', 'asc');
        }])->get()->sort(function ($a, $b) {
            // Extract number from nama_mesin (e.g., "Mesin 1" -> 1)
            preg_match('/\d+/', $a->nama_mesin, $numA);
            preg_match('/\d+/', $b->nama_mesin, $numB);

            $numberA = isset($numA[0]) ? (int)$numA[0] : 0;
            $numberB = isset($numB[0]) ? (int)$numB[0] : 0;

            // Natural numeric sort
            return $numberA <=> $numberB;
        });

        // Sort meja naturally as well
        $mejas = Meja::all()->sort(function ($a, $b) {
            preg_match('/\d+/', $a->nama_meja, $numA);
            preg_match('/\d+/', $b->nama_meja, $numB);

            $numberA = isset($numA[0]) ? (int)$numA[0] : 0;
            $numberB = isset($numB[0]) ? (int)$numB[0] : 0;

            return $numberA <=> $numberB;
        });

        $mesinTypes = ['apd', 'qpoint', 'instruksi_kerja', 'limit_sampel', 'amptm'];
        return view('documents.index', compact('mesins', 'mejas', 'mesinTypes'));
    }

    public function editMesin(Mesin $mesin)
    {
        $mesin->load('documents');
        $types = ['apd', 'qpoint', 'instruksi_kerja', 'limit_sampel', 'amptm'];

        $parts = Part::orderBy('nama_part', 'asc')->get();
        $currentPlanning = PlanningProduksi::where('mesin_id', $mesin->id)
            ->whereDate('tanggal', now()->format('Y-m-d'))
            ->first();

        // Group documents by type (ambil yang pertama untuk compatibility dengan view)
        $docs = [];
        foreach ($types as $type) {
            $docs[$type] = $mesin->documents->where('type', $type)->first();
        }

        return view('documents.edit-mesin', compact('mesin', 'types', 'docs', 'parts', 'currentPlanning'));
    }

    public function updateMesin(Request $request, Mesin $mesin)
    {
        $types = ['apd', 'qpoint', 'instruksi_kerja', 'limit_sampel', 'amptm'];
        $data = $request->validate([
            'docs' => 'array',
            'docs.*.url' => 'nullable|string',
            'docs.*.enabled' => 'nullable|boolean',
            'docs.*.file' => 'nullable',
            'docs.*.file.*' => 'file|mimes:pdf,doc,docx|max:10240',
        ]);

        $uploadedIds = [];

        foreach ($types as $type) {
            $payload = $data['docs'][$type] ?? null;
            if (!$payload) {
                continue;
            }

            // Handle multiple uploaded files
            if ($request->hasFile("docs.$type.file")) {
                foreach ($request->file("docs.$type.file") as $file) {
                    if (!$file || !$file->isValid()) {
                        continue;
                    }

                    $originalName = $file->getClientOriginalName();
                    $filename = time() . '_' . uniqid() . '_' . preg_replace('/\s+/', '_', $originalName);

                    $path = $file->storeAs(
                        'documents/mesin/' . $mesin->id,
                        $filename,
                        'public'
                    );

                    // 🔥 DELETE old document dengan type yang sama (jika ada)
                    $oldDoc = MesinDocument::where('mesin_id', $mesin->id)
                        ->where('type', $type)
                        ->first();

                    if ($oldDoc) {
                        // Delete old physical file
                        $oldPath = str_replace('storage/', '', $oldDoc->url);
                        if (Storage::disk('public')->exists($oldPath)) {
                            Storage::disk('public')->delete($oldPath);
                            Log::info('🗑️ Old file deleted', ['path' => $oldPath]);
                        }
                        $oldDoc->delete();
                    }

                    // CREATE NEW document dengan path relatif
                    $document = MesinDocument::create([
                        'mesin_id' => $mesin->id,
                        'type' => $type,
                        'url' => 'storage/' . $path,
                        'filename' => $originalName,
                        'enabled' => (bool)($payload['enabled'] ?? true)
                    ]);

                    $uploadedIds[] = $document->id;

                    Log::info('✅ Document created via updateMesin', [
                        'doc_id' => $document->id,
                        'mesin_id' => $mesin->id,
                        'type' => $type,
                        'url' => $document->url,
                        'filename' => $originalName
                    ]);
                }
            }

            // Handle URL input (optional)
            elseif (!empty($payload['url'])) {
                MesinDocument::updateOrCreate(
                    ['mesin_id' => $mesin->id, 'type' => $type],
                    ['url' => $payload['url'], 'enabled' => (bool)($payload['enabled'] ?? false)]
                );
            }
        }

        return redirect()
            ->route('documents.mesin.edit', $mesin->id)
            ->with('success', 'Dokumen mesin diperbarui')
            ->with('uploaded_ids', $uploadedIds);
    }

    public function uploadMesin(Request $request, Mesin $mesin)
    {
        $validated = $request->validate([
            'type' => 'required|in:apd,qpoint,instruksi_kerja,limit_sampel,amptm',
            'dokumen' => 'required',
            'dokumen.*' => 'file|mimes:pdf,doc,docx|max:10240',
        ]);

        $uploadedIds = [];
        $files = is_array($request->file('dokumen')) ? $request->file('dokumen') : [$request->file('dokumen')];

        foreach ($files as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $originalName = $file->getClientOriginalName();
            $filename = time() . '_' . uniqid() . '_' . preg_replace('/\s+/', '_', $originalName);

            $path = $file->storeAs(
                'documents/mesin/' . $mesin->id,
                $filename,
                'public'
            );

            // 🔥 DELETE old document dengan type yang sama (jika ada)
            $oldDoc = MesinDocument::where('mesin_id', $mesin->id)
                ->where('type', $validated['type'])
                ->first();

            if ($oldDoc) {
                // Delete old physical file
                $oldPath = str_replace('storage/', '', $oldDoc->url);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                    Log::info('🗑️ Old file deleted', ['path' => $oldPath]);
                }
                $oldDoc->delete();
            }

            // CREATE NEW document dengan path relatif
            $document = MesinDocument::create([
                'mesin_id' => $mesin->id,
                'type' => $validated['type'],
                'url' => 'storage/' . $path,
                'filename' => $originalName,
                'enabled' => true
            ]);

            $uploadedIds[] = $document->id;

            Log::info('✅ Document uploaded', [
                'doc_id' => $document->id,
                'mesin_id' => $mesin->id,
                'type' => $validated['type'],
                'url' => $document->url,
                'filename' => $originalName,
                'full_path' => storage_path('app/public/' . $path)
            ]);
        }

        // Langsung redirect ke halaman edit yang sama (refresh)
        return back()->with('success', count($uploadedIds) . ' dokumen berhasil diupload dan menggantikan dokumen lama!');
    }

    public function editMeja(Meja $meja)
    {
        $meja->load('documents');
        return view('documents.edit-meja', compact('meja'));
    }

    public function updateMeja(Request $request, Meja $meja)
    {
        $validated = $request->validate([
            'documents' => 'array',
            'documents.*.id' => 'nullable|integer',
            'documents.*.type' => 'required|string',
            'documents.*.url' => 'required|string',
            'documents.*.enabled' => 'nullable|boolean',
        ]);

        $incoming = collect($validated['documents'] ?? []);

        foreach ($incoming as $row) {
            $doc = null;
            if (!empty($row['id'])) {
                $doc = MejaDocument::where('meja_id', $meja->id_meja)->where('id', $row['id'])->first();
            }
            if (!$doc) {
                $doc = new MejaDocument();
                $doc->meja_id = $meja->id_meja;
            }
            $doc->type = $row['type'];
            $doc->url = $row['url'];
            $doc->enabled = (bool)($row['enabled'] ?? false);
            $doc->save();
        }

        return redirect()->route('documents.index')->with('success', 'Dokumen meja diperbarui');
    }

    public function uploadMeja(Request $request, Meja $meja)
    {
        $validated = $request->validate([
            'type' => 'required|in:apd,qpoint,instruksi_kerja,limit_sampel,amptm',
            'dokumen' => 'required',
            'dokumen.*' => 'file|mimes:pdf,doc,docx|max:10240',
        ]);

        $uploadedIds = [];
        $files = is_array($request->file('dokumen')) ? $request->file('dokumen') : [$request->file('dokumen')];

        foreach ($files as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $originalName = $file->getClientOriginalName();
            $filename = time() . '_' . uniqid() . '_' . preg_replace('/\s+/', '_', $originalName);

            $path = $file->storeAs(
                'documents/meja/' . $meja->id_meja,
                $filename,
                'public'
            );

            // 🔥 DELETE old document dengan type yang sama (jika ada)
            $oldDoc = MejaDocument::where('meja_id', $meja->id_meja)
                ->where('type', $validated['type'])
                ->first();

            if ($oldDoc) {
                // Delete old physical file
                $oldPath = str_replace('storage/', '', $oldDoc->url);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                    Log::info('🗑️ Old meja file deleted', ['path' => $oldPath]);
                }
                $oldDoc->delete();
            }

            $document = MejaDocument::create([
                'meja_id' => $meja->id_meja,
                'type' => $validated['type'],
                'url' => 'storage/' . $path,
                'filename' => $originalName,
                'enabled' => true
            ]);

            $uploadedIds[] = $document->id;
        }

        return redirect()
            ->route('documents.index')
            ->with('success', count($uploadedIds) . ' dokumen meja berhasil diupload.');
    }

    public function destroyMesinDoc(MesinDocument $doc)
    {
        // Delete physical file
        if ($doc->url) {
            // Handle both relative and absolute URLs
            $path = str_replace('storage/', '', $doc->url);
            $path = parse_url($path, PHP_URL_PATH) ?? $path;
            $path = ltrim($path, '/');

            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                Log::info('File deleted', ['path' => $path]);
            }
        }

        $mesinId = $doc->mesin_id;
        $doc->delete();

        return redirect()
            ->route('documents.mesin.edit', $mesinId)
            ->with('success', 'Dokumen mesin dihapus');
    }

    public function destroyMejaDoc(MejaDocument $doc)
    {
        if ($doc->url) {
            $path = str_replace('storage/', '', $doc->url);
            $path = parse_url($path, PHP_URL_PATH) ?? $path;
            $path = ltrim($path, '/');

            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $doc->delete();
        return back()->with('success', 'Dokumen meja dihapus');
    }
}
