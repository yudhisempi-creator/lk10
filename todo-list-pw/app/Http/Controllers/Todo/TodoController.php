<?php

namespace App\Http\Controllers\Todo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Models\Todo;

class TodoController extends Controller
{
    /**
     * INDEX - Tampilkan semua todo milik user yang login dengan fitur search
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        // Query todos hanya milik user yang login (scoped query)
        $query = auth()->user()->todos();

        if ($search) {
            $query->where('task', 'LIKE', '%' . $search . '%');
        }

        $todos = $query->orderBy('created_at', 'desc')->get();

        return view('todo.app', compact('todos'));
    }

    /**
     * STORE - Simpan todo baru menggunakan Form Request validation
     */
    public function store(StoreTodoRequest $request)
    {
        // Data sudah tervalidasi oleh StoreTodoRequest
        auth()->user()->todos()->create([
            'task' => $request->input('task'),
            'is_done' => false
        ]);

        return redirect()->route('todo')->with('success', 'Data Berhasil Disimpan');
    }

    /**
     * UPDATE - Update todo dengan route model binding dan ownership check
     */
    public function update(UpdateTodoRequest $request, Todo $todo)
    {
        // UpdateTodoRequest::authorize() sudah mengecek kepemilikan
        $todo->update([
            'task' => $request->input('task'),
            'is_done' => $request->input('is_done')
        ]);

        return redirect()->route('todo')->with('success', 'Status tugas berhasil diperbarui!');
    }

    /**
     * DESTROY - Hapus todo dengan ownership check
     */
    public function destroy(Todo $todo)
    {
        // Cek kepemilikan todo
        if ($todo->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $todo->delete();

        return redirect()->route('todo')->with('success', 'Tugas berhasil dihapus!');
    }

    /**
     * API INDEX - Endpoint JSON untuk mengambil todos milik user (dengan auth)
     */
    public function apiIndex(Request $request)
    {
        $search = $request->input('search');
        $query = auth()->user()->todos();

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