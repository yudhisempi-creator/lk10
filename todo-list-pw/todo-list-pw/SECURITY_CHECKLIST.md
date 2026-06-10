# Checklist Keamanan Dasar Aplikasi Todo List

Dokumen ini berisi daftar checklist keamanan yang telah diterapkan pada aplikasi Todo List, beserta penjelasan singkat mengenai mitigasi risiko kerentanan keamanan seperti XSS dan SQL Injection.

## 1. Validasi Input
- [x] **Client-side Validation:** Menggunakan atribut HTML5 (seperti `required`, `minlength`, `maxlength`) pada form input untuk memberikan feedback langsung kepada pengguna sebelum data dikirim.
- [x] **Server-side Validation:** Menggunakan fitur validasi Laravel (`$request->validate(...)`) pada controller (`TodoController@store`, `TodoController@update`, dan `AuthController@login`). Hal ini memastikan bahwa data yang masuk ke server selalu sesuai dengan format yang diharapkan (misalnya: batas karakter, format email yang valid, boolean untuk status is_done).

## 2. Sanitasi Data & XSS Prevention (Cross Site Scripting)
**Potensi Risiko XSS:** XSS terjadi ketika aplikasi menerima data dari pengguna tanpa sanitasi dan menampilkannya kembali ke halaman web, sehingga memungkinkan penyerang mengeksekusi script berbahaya (JavaScript) di browser pengguna lain.
**Mitigasi:**
- [x] Aplikasi menggunakan Blade templating engine bawaan Laravel.
- [x] Semua output variabel (misalnya `{{ $d->task }}`) secara otomatis di-escape menggunakan fungsi `htmlspecialchars` PHP oleh Blade. Hal ini mengkonversi karakter khusus seperti `<` dan `>` menjadi entitas HTML yang aman.

## 3. SQL Injection Prevention
**Potensi Risiko SQL Injection:** SQL Injection terjadi ketika input pengguna yang tidak disanitasi disisipkan secara langsung ke dalam query database, yang memungkinkan penyerang untuk memanipulasi struktur query.
**Mitigasi:**
- [x] Aplikasi menggunakan **Laravel Eloquent ORM** dan **Query Builder**.
- [x] Metode pencarian menggunakan prepared statements secara otomatis oleh Laravel: `$query->where('task', 'LIKE', '%' . $search . '%');` Parameter di-bind secara aman dan tidak digabungkan secara langsung ke dalam string SQL mentah.

## 4. Password Hashing & Authentication
- [x] Password pengguna di-hash menggunakan algoritma Bcrypt bawaan Laravel (via model `User` dan metode `Hash::make` saat registrasi/seeding).
- [x] Proses autentikasi dikelola dengan aman menggunakan class `Auth::attempt(...)` dari Laravel.

## 5. Session Management & CSRF Protection
- [x] Laravel secara otomatis mengelola cookie session secara aman.
- [x] **CSRF Protection:** Setiap form yang menggunakan method POST, PUT, atau DELETE (`<form method="POST">`) telah menyertakan directive `@csrf`. Token ini memverifikasi bahwa permintaan tersebut benar-benar berasal dari pengguna aplikasi yang sah dan bukan dari situs eksternal.

## 6. Access Control & Route Protection
- [x] Fitur ToDo (melihat, menambah, mengubah, dan menghapus) dibatasi hanya untuk pengguna yang telah masuk (logged in).
- [x] Hal ini dikontrol menggunakan Route Middleware `->middleware('auth')` di dalam `routes/web.php`.

## 7. Error Handling yang Aman
- [x] Mode Debug pada environment production (`APP_DEBUG=false`) secara default akan menyembunyikan detail stack trace dari pengguna akhir, sehingga tidak ada informasi sensitif (seperti path folder, kueri SQL) yang bocor ke publik.
- [x] Pesan kesalahan validasi dibuat deskriptif dan ramah pengguna, tanpa mengekspos informasi teknis server.

## 8. Secure API Response
- [x] Response untuk Endpoint API (`/api/todos`) menggunakan format JSON terstruktur yang aman dan konsisten.
- [x] *Catatan: Jika nantinya API digunakan untuk aplikasi terpisah (SPA, Mobile), perlu diimplementasikan mekanisme autentikasi tambahan seperti Laravel Sanctum atau Passport.*
