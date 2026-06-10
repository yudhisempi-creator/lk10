<?php

namespace App\Http\Controllers\Todo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// 1. PASTIKAN panggil Model Todo di atas class Controller
use App\Models\Todo; 

class TodoController extends Controller
{
    // METHOD INDEX (Sudah ditambahkan parameter Request $request untuk sistem filtering/search)
    public function index(Request $request)
    {
        // 1. Ambil kata kunci pencarian dari parameter URL (?search=...)
        $search = $request->input('search');

        // 2. Inisialisasi Query Builder dari Model Todo
        $query = Todo::query();

        // 3. JIKA ada kata kunci yang dicari, saring berdasarkan kolom 'task'
        if ($search) {
            $query->where('task', 'LIKE', '%' . $search . '%');
        }

        // 4. Ambil data yang sudah ter-filter (atau seluruh data jika keyword kosong)
        $todos = $query->orderBy('created_at', 'desc')->get();

        // 5. Kirim data yang bersih ke dalam View
        return view('todo.app', compact('todos'));
    }

    // Fungsi store() untuk memvalidasi & menyimpan data baru
    public function store(Request $request)
    {
        $request->validate([
            'task' => 'required|min:5|max:25'
        ], [
            'task.required' => 'Task Wajib Diisi',
            'task.min' => 'Task Minimal 5 Karakter',
            'task.max' => 'Task Maksimum 25 Karakter',
        ]);

        Todo::create([
            'task' => $request->input('task'),
            'is_done' => false
        ]);

        return redirect()->route('todo')->with('success', 'Data Berhasil Disimpan');
    }

    // Fungsi update() untuk mengubah teks task dan status radio button (is_done)
    public function update(Request $request, string $id)
    {
        // Validasi input untuk keamanan
        $request->validate([
            'task' => 'required|min:5|max:25',
            'is_done' => 'required|boolean'
        ], [
            'task.required' => 'Task Wajib Diisi',
            'task.min' => 'Task Minimal 5 Karakter',
            'task.max' => 'Task Maksimum 25 Karakter',
            'is_done.required' => 'Status Selesai Wajib Diisi',
            'is_done.boolean' => 'Format Status Tidak Valid',
        ]);

        $todo = Todo::findOrFail($id);
        
        // Memperbarui task teks beserta status penyelesaian berdasarkan input form collapse
        $todo->update([
            'task' => $request->input('task'),
            'is_done' => $request->input('is_done')
        ]);

        return redirect()->route('todo')->with('success', 'Status tugas berhasil diperbarui!');
    }

    // Fungsi destroy() untuk menghapus record data dari database
    public function destroy(string $id)
    {
        $todo = Todo::findOrFail($id);
        $todo->delete();

        return redirect()->route('todo')->with('success', 'Tugas berhasil dihapus!');
    }

    // Endpoint API JSON untuk mengambil semua task
    public function apiIndex(Request $request)
    {
        $search = $request->input('search');
        $query = Todo::query();

        if ($search) {
            $query->where('task', 'LIKE', '%' . $search . '%');
        }

        $todos = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil diambil',
            'data' => $todos
        ]);
    }
}