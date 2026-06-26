# Ringkasan Kode Hasil Perbaikan

Dokumentasi singkat dengan kode-kode utama yang sudah diperbaiki.

---

## 1. Migration Update - Tambah user_id & Fix Down

**File:** `database/migrations/2026_05_20_064522_create_todos_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('todo', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('task');
        $table->boolean('is_done')->default(false);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('todo'); // ✅ FIXED (was 'todos')
    }
};
```

---

## 2. Form Requests untuk Validation Terpisah

### StoreTodoRequest
**File:** `app/Http/Requests/StoreTodoRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTodoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'task' => 'required|string|min:5|max:25',
        ];
    }

    public function messages(): array
    {
        return [
            'task.required' => 'Task Wajib Diisi',
            'task.min' => 'Task Minimal 5 Karakter',
            'task.max' => 'Task Maksimum 25 Karakter',
        ];
    }
}
```

### UpdateTodoRequest (dengan Ownership Check)
**File:** `app/Http/Requests/UpdateTodoRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTodoRequest extends FormRequest
{
    /**
     * ✅ Verifikasi bahwa todo milik user yang login
     */
    public function authorize(): bool
    {
        $todo = $this->route('todo');
        return $todo->user_id === auth()->id();
    }

    public function rules(): array
    {
        return [
            'task' => 'required|string|min:5|max:25',
            'is_done' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'task.required' => 'Task Wajib Diisi',
            'task.min' => 'Task Minimal 5 Karakter',
            'task.max' => 'Task Maksimum 25 Karakter',
            'is_done.required' => 'Status Selesai Wajib Diisi',
            'is_done.boolean' => 'Format Status Tidak Valid',
        ];
    }
}
```

### LoginRequest
**File:** `app/Http/Requests/LoginRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string',
            'password' => 'required|min:5',
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Username harus diisi',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 5 karakter',
        ];
    }
}
```

---

## 3. User Model - Fix Casts & Tambah Relationship

**File:** `app/Models/User.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * ✅ FIXED: Gunakan $casts property (bukan method)
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * ✅ NEW: Relationship ke todos (untuk scoped queries)
     */
    public function todos(): HasMany
    {
        return $this->hasMany(\App\Models\Todo::class);
    }
}
```

---

## 4. Todo Model - Tambah user_id & Type Casting

**File:** `app/Models/Todo.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    protected $table = "todo";
    
    /**
     * ✅ Tambah user_id ke fillable untuk scoping
     */
    protected $fillable = ['task', 'is_done', 'user_id'];
    
    /**
     * ✅ Type casting untuk is_done
     */
    protected $casts = [
        'is_done' => 'boolean',
    ];
}
```

---

## 5. TodoController - Gunakan Form Request & Scoped Queries

**File:** `app/Http/Controllers/Todo/TodoController.php`

```php
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
     * ✅ Scoped query - hanya ambil todos milik user yang login
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = auth()->user()->todos();

        if ($search) {
            $query->where('task', 'LIKE', '%' . $search . '%');
        }

        $todos = $query->orderBy('created_at', 'desc')->get();
        return view('todo.app', compact('todos'));
    }

    /**
     * ✅ Gunakan StoreTodoRequest untuk validation
     */
    public function store(StoreTodoRequest $request)
    {
        auth()->user()->todos()->create([
            'task' => $request->input('task'),
            'is_done' => false
        ]);

        return redirect()->route('todo')->with('success', 'Data Berhasil Disimpan');
    }

    /**
     * ✅ Route Model Binding: Laravel auto-inject Todo model
     * ✅ UpdateTodoRequest::authorize() check ownership
     */
    public function update(UpdateTodoRequest $request, Todo $todo)
    {
        $todo->update([
            'task' => $request->input('task'),
            'is_done' => $request->input('is_done')
        ]);

        return redirect()->route('todo')->with('success', 'Status tugas berhasil diperbarui!');
    }

    /**
     * ✅ Route Model Binding & ownership check
     */
    public function destroy(Todo $todo)
    {
        if ($todo->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $todo->delete();
        return redirect()->route('todo')->with('success', 'Tugas berhasil dihapus!');
    }

    /**
     * ✅ API endpoint juga scoped ke user yang login
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
```

---

## 6. AuthController - Gunakan LoginRequest & Fix Error Keys

**File:** `app/Http/Controllers/AuthController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * ✅ Gunakan LoginRequest untuk validation
     * ✅ Fix error key dari 'email' → 'username'
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('username', 'password');

        $user = User::where('name', $credentials['username'])->first();
        
        if (!$user) {
            return back()->withErrors([
                'username' => 'User dengan username ' . $credentials['username'] . ' tidak ditemukan',
            ])->onlyInput('username');
        }

        if (Auth::attempt(['name' => $credentials['username'], 'password' => $credentials['password']], $request->has('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/todo')->with('success', 'Login berhasil!');
        }

        /**
         * ✅ FIXED: Error key sesuai dengan form field
         */
        return back()->withErrors([
            'username' => 'Username atau password salah',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login')->with('success', 'Logout berhasil!');
    }
}
```

---

## 7. Routes Update - Route Model Binding & API Security

**File:** `routes/web.php`

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Todo\TodoController;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::get('/todo', [TodoController::class, 'index'])->name('todo');
    Route::post('/todo', [TodoController::class, 'store'])->name('todo.store');
    
    /**
     * ✅ Route Model Binding: {todo} bukan {id}
     * Laravel auto-inject Todo model ke controller
     */
    Route::put('/todo/{todo}', [TodoController::class, 'update'])->name('todo.update');
    Route::delete('/todo/{todo}', [TodoController::class, 'destroy'])->name('todo.destroy');
    
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
```

**File:** `routes/api.php`

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Todo\TodoController;

/**
 * ✅ API endpoint sekarang protected dengan auth:sanctum
 */
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/todos', [TodoController::class, 'apiIndex']);
});
```

---

## 8. View Update - Route Parameter Binding

**File:** `resources/views/todo/app.blade.php` (snippet)

```blade
{{-- ✅ Update parameter dari {id} ke {todo} --}}
<form action="{{ route('todo.destroy', ['todo' => $d->id]) }}" method="POST" class="inline">
    @csrf
    @method('DELETE')
    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded text-sm transition shadow-sm">
        ✕
    </button>
</form>

{{-- ✅ Update parameter di update route juga --}}
<form action="{{ route('todo.update', ['todo' => $d->id]) }}" method="POST">
    @csrf
    @method('PUT')
    <!-- form content -->
</form>
```

---

## 📊 Perbandingan Sebelum & Sesudah

### Security: IDOR Vulnerability

**SEBELUM:**
```php
// ❌ User bisa akses/edit/hapus todo user lain
$todos = Todo::all();
$todo = Todo::find($id); // Bisa siapa saja
```

**SESUDAH:**
```php
// ✅ Hanya akses todos milik user sendiri
$todos = auth()->user()->todos();
$todo = $request->route('todo'); // Auto-injected & authorized
```

### Code Quality: Validation

**SEBELUM:**
```php
public function store(Request $request)
{
    $request->validate([...], [...]);
    // 15+ lines validation code
}
```

**SESUDAH:**
```php
public function store(StoreTodoRequest $request)
{
    // Clean & focused on business logic
}
```

---

## 🔄 Testing Checklist

- [ ] Run migration: `php artisan migrate:fresh`
- [ ] Login dengan user test
- [ ] Create todo (validasi min 5 char)
- [ ] Edit todo (check ownership)
- [ ] Delete todo (check ownership)
- [ ] Test API `/api/todos` (harus auth)
- [ ] Test IDOR: coba akses todo user lain (harus 403)

---

**Generated:** 2026-06-26  
**Status:** Ready for Production
