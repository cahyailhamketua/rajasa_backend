# Forgot Password — OTP Email

Fitur forgot password digunakan untuk membantu user mendapatkan kembali akses ke akun ketika lupa password.

Implementasi saat ini menggunakan **OTP 6 digit yang dikirim melalui email**. Proses pengiriman email menggunakan Gmail SMTP.

> **Catatan:** Dokumentasi ini mengikuti implementasi backend yang sedang berjalan. Seiring pengembangan fitur, konfigurasi, endpoint, flow, maupun mekanisme keamanan dapat berubah dan bagian dokumentasi terkait akan diperbarui kembali.

---

## Alur Saat Ini

```text
User
 │
 │ Request forgot password
 ▼
POST /api/forgot-password
 │
 ├── Cari user berdasarkan email
 ├── Generate OTP 6 digit
 ├── Hash OTP
 ├── Simpan OTP ke database
 └── Kirim OTP melalui email
 │
 ▼
User menerima OTP
 │
 │ Verify OTP
 ▼
POST /api/verify-otp
 │
 ├── Cari OTP aktif
 ├── Cek masa berlaku
 ├── Cek jumlah percobaan
 └── Validasi OTP
 │
 ▼
OTP valid
 │
 │ Reset password
 ▼
POST /api/reset-password
 │
 ├── Validasi OTP kembali
 ├── Update password
 ├── Tandai OTP sebagai digunakan
 └── Hapus token Sanctum lama
 │
 ▼
Password berhasil diubah
```

---

## Konfigurasi Email

Forgot password menggunakan Gmail SMTP sebagai mail server.

Konfigurasi disimpan di `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=cahyailham811@gmail.com
MAIL_PASSWORD=<GMAIL_APP_PASSWORD>
MAIL_FROM_ADDRESS=cahyailham811@gmail.com
MAIL_FROM_NAME="Rajasa"
```

`MAIL_PASSWORD` menggunakan **Gmail App Password**, bukan password utama akun Gmail.

Credential SMTP tidak boleh dimasukkan ke repository.

Setelah melakukan perubahan konfigurasi:

```bash
php artisan config:clear
```

Konfigurasi dapat diperiksa melalui Tinker:

```bash
php artisan tinker
```

Kemudian:

```php
config('mail.default');
config('mail.mailers.smtp.host');
config('mail.mailers.smtp.port');
config('mail.from');
```

Contoh hasil yang diharapkan:

```text
"smtp"
"smtp.gmail.com"
587
[
    "address" => "cahyailham811@gmail.com",
    "name" => "Rajasa",
]
```

### Test SMTP

SMTP dapat diuji secara langsung menggunakan Tinker:

```php
\Illuminate\Support\Facades\Mail::raw(
    'Test email dari Rajasa Laravel',
    function ($message) {
        $message->to('EMAIL_TUJUAN');
        $message->subject('Test Email Rajasa');
    }
);
```

Jika email diterima, konfigurasi SMTP dapat digunakan untuk fitur OTP.

---

# OTP

## Karakteristik OTP Saat Ini

| Konfigurasi                 | Nilai      |
| --------------------------- | ---------- |
| Panjang OTP                 | 6 digit    |
| Masa berlaku                | 30 menit   |
| Maksimum percobaan OTP      | 5 kali     |
| OTP dapat digunakan kembali | Tidak      |
| Penyimpanan                 | Hash       |
| Pengiriman                  | Gmail SMTP |

OTP dibuat menggunakan:

```php
$otp = (string) random_int(100000, 999999);
```

OTP kemudian di-hash sebelum disimpan:

```php
'otp_hash' => Hash::make($otp),
```

Dengan demikian, OTP asli tidak disimpan secara langsung di database.

Verifikasi dilakukan menggunakan:

```php
Hash::check($request->otp, $otpRecord->otp_hash)
```

---

# Database

OTP disimpan pada tabel:

```text
password_reset_otps
```

Struktur saat ini:

```php
Schema::create('password_reset_otps', function (Blueprint $table) {
    $table->id();

    $table->foreignId('user_id')
        ->constrained('users')
        ->cascadeOnDelete();

    $table->string('otp_hash');

    $table->timestamp('expires_at');

    $table->unsignedTinyInteger('attempts')->default(0);

    $table->timestamp('used_at')->nullable();

    $table->timestamps();

    $table->index(['user_id', 'expires_at']);
});
```

### Kolom

| Kolom        | Fungsi                           |
| ------------ | -------------------------------- |
| `id`         | Primary key                      |
| `user_id`    | User yang meminta reset password |
| `otp_hash`   | OTP dalam bentuk hash            |
| `expires_at` | Waktu OTP kedaluwarsa            |
| `attempts`   | Jumlah percobaan OTP             |
| `used_at`    | Waktu OTP digunakan              |
| `created_at` | Waktu record dibuat              |
| `updated_at` | Waktu record diperbarui          |

---

# Model

Model OTP:

```text
app/Models/PasswordResetOtp.php
```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordResetOtp extends Model
{
    protected $fillable = [
        'user_id',
        'otp_hash',
        'expires_at',
        'attempts',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

Pada model `User` terdapat relasi:

```php
public function passwordResetOtps(): HasMany
{
    return $this->hasMany(PasswordResetOtp::class);
}
```

---

# Email OTP

Mailable:

```text
app/Mail/PasswordResetOtpMail.php
```

```php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode OTP Reset Password - Rajasa',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset-otp',
        );
    }
}
```

Template email:

```text
resources/views/emails/password-reset-otp.blade.php
```

Template saat ini berisi:

* Informasi bahwa terdapat permintaan reset password.
* OTP 6 digit.
* Informasi masa berlaku OTP selama 30 menit.
* Peringatan untuk tidak membagikan OTP.
* Informasi bahwa email dapat diabaikan jika user tidak melakukan reset password.

---

# API

Saat ini terdapat tiga endpoint yang digunakan dalam proses forgot password.

| Method | Endpoint               | Authentication |
| ------ | ---------------------- | -------------- |
| `POST` | `/api/forgot-password` | Public         |
| `POST` | `/api/verify-otp`      | Public         |
| `POST` | `/api/reset-password`  | Public         |

Endpoint dibuat public karena user yang lupa password belum memiliki akses authentication.

---

## 1. Request OTP

### Endpoint

```http
POST /api/forgot-password
```

### Body

```json
{
    "email": "user@gmail.com"
}
```

### Proses

Backend melakukan:

1. Validasi email.
2. Mencari user berdasarkan email.
3. Menghapus OTP aktif sebelumnya.
4. Generate OTP baru.
5. Hash OTP.
6. Menyimpan OTP.
7. Menentukan masa berlaku OTP selama 30 menit.
8. Mengirim OTP melalui email.

Generate OTP:

```php
$otp = (string) random_int(100000, 999999);
```

Menyimpan OTP:

```php
PasswordResetOtp::create([
    'user_id' => $user->id,
    'otp_hash' => Hash::make($otp),
    'expires_at' => now()->addMinutes(30),
    'attempts' => 0,
]);
```

Mengirim email:

```php
Mail::to($user->email)->send(
    new PasswordResetOtpMail($otp)
);
```

### Response

```json
{
    "success": true,
    "message": "Jika email terdaftar, kode OTP telah dikirim."
}
```

Response dibuat generic agar tidak secara langsung mengungkap apakah sebuah email terdaftar pada sistem.

---

# 2. Verify OTP

### Endpoint

```http
POST /api/verify-otp
```

### Body

```json
{
    "email": "user@gmail.com",
    "otp": "482731"
}
```

### Proses

Backend:

1. Mencari user berdasarkan email.
2. Mengambil OTP yang belum digunakan.
3. Mengambil OTP terbaru.
4. Mengecek masa berlaku.
5. Mengecek jumlah percobaan.
6. Membandingkan OTP menggunakan `Hash::check()`.

Jika OTP salah:

```php
$otpRecord->increment('attempts');
```

Jika OTP sudah melebihi batas percobaan:

```text
HTTP 429
```

Response berhasil:

```json
{
    "success": true,
    "message": "OTP berhasil diverifikasi."
}
```

---

# 3. Reset Password

### Endpoint

```http
POST /api/reset-password
```

### Body

```json
{
    "email": "user@gmail.com",
    "otp": "482731",
    "password": "passwordbaru123",
    "password_confirmation": "passwordbaru123"
}
```

### Proses

OTP tetap diverifikasi kembali pada endpoint reset password.

Jika valid:

```php
$user->update([
    'password' => $request->password,
]);
```

Password akan di-hash melalui cast pada model `User`:

```php
'password' => 'hashed',
```

OTP kemudian ditandai sebagai sudah digunakan:

```php
$otpRecord->update([
    'used_at' => now(),
]);
```

Token Sanctum user juga dihapus:

```php
$user->tokens()->delete();
```

Response:

```json
{
    "success": true,
    "message": "Password berhasil diubah. Silakan login kembali."
}
```

User kemudian harus login kembali menggunakan password baru.

---

# Rate Limiting

Request OTP menggunakan Laravel Rate Limiter.

Konfigurasi saat ini:

```text
Maksimum request : 3 kali
Window           : 60 detik
```

Implementasi:

```php
$rateLimitKey = 'password-reset-otp:' . $email;

if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
    return response()->json([
        'success' => false,
        'message' => 'Terlalu banyak permintaan OTP. Silakan coba lagi nanti.',
    ], 429);
}

RateLimiter::hit($rateLimitKey, 60);
```

Tujuannya untuk membatasi request OTP secara berulang.

---

# OTP Attempt Limit

Setiap OTP memiliki batas percobaan sebanyak **5 kali**.

```php
if ($otpRecord->attempts >= 5) {
    return response()->json([
        'success' => false,
        'message' => 'Terlalu banyak percobaan OTP.',
    ], 429);
}
```

Setiap OTP yang salah akan meningkatkan nilai `attempts`:

```php
$otpRecord->increment('attempts');
```

---

# OTP Expiration

OTP berlaku selama 30 menit.

```php
'expires_at' => now()->addMinutes(30),
```

Validasi:

```php
if ($otpRecord->expires_at->isPast()) {
    return response()->json([
        'success' => false,
        'message' => 'OTP sudah kedaluwarsa.',
    ], 422);
}
```

Contoh:

```text
OTP dibuat
21:00

OTP expired
21:30
```

---

# Single Use OTP

OTP hanya dapat digunakan satu kali.

Setelah password berhasil diubah:

```php
$otpRecord->update([
    'used_at' => now(),
]);
```

Pencarian OTP menggunakan:

```php
->whereNull('used_at')
```

Dengan demikian OTP yang sudah digunakan tidak dapat digunakan kembali.

---

# Invalidation Token

Setelah password berhasil di-reset:

```php
$user->tokens()->delete();
```

Semua token Sanctum user dihapus.

User harus melakukan login kembali setelah password berhasil diubah.

---

# Form Request

Validasi request dipisahkan menggunakan Form Request.

```text
app/Http/Requests/
├── ForgotPasswordRequest.php
├── VerifyOtpRequest.php
└── ResetPasswordRequest.php
```

### ForgotPasswordRequest

```php
public function rules(): array
{
    return [
        'email' => ['required', 'email'],
    ];
}
```

### VerifyOtpRequest

```php
public function rules(): array
{
    return [
        'email' => ['required', 'email'],
        'otp' => ['required', 'digits:6'],
    ];
}
```

### ResetPasswordRequest

```php
public function rules(): array
{
    return [
        'email' => ['required', 'email'],
        'otp' => ['required', 'digits:6'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ];
}
```

---

# Testing

Testing dapat dilakukan menggunakan Bruno.

## Happy Path

### Step 1 — Request OTP

```http
POST http://localhost:8000/api/forgot-password
```

```json
{
    "email": "EMAIL_ASLI_USER"
}
```

Kemudian cek inbox email dan ambil OTP.

---

### Step 2 — Verify OTP

```http
POST http://localhost:8000/api/verify-otp
```

```json
{
    "email": "EMAIL_ASLI_USER",
    "otp": "OTP_DARI_EMAIL"
}
```

Expected:

```json
{
    "success": true,
    "message": "OTP berhasil diverifikasi."
}
```

---

### Step 3 — Reset Password

```http
POST http://localhost:8000/api/reset-password
```

```json
{
    "email": "EMAIL_ASLI_USER",
    "otp": "OTP_DARI_EMAIL",
    "password": "passwordbaru123",
    "password_confirmation": "passwordbaru123"
}
```

Expected:

```json
{
    "success": true,
    "message": "Password berhasil diubah. Silakan login kembali."
}
```

---

### Step 4 — Login

Setelah reset berhasil, lakukan login menggunakan password baru:

```http
POST http://localhost:8000/api/login
```

```json
{
    "username": "USERNAME_USER",
    "password": "passwordbaru123"
}
```

Jika login berhasil, flow forgot password berjalan dengan baik.

---

# Negative Testing

Selain happy path, beberapa kondisi perlu diuji.

### OTP Salah

```json
{
    "email": "user@gmail.com",
    "otp": "123456"
}
```

Expected:

```text
HTTP 422
```

```json
{
    "success": false,
    "message": "OTP tidak valid."
}
```

---

### OTP Kedaluwarsa

Gunakan OTP setelah melewati masa berlaku 30 menit.

Expected:

```text
HTTP 422
```

```json
{
    "success": false,
    "message": "OTP sudah kedaluwarsa."
}
```

---

### OTP Sudah Digunakan

Gunakan kembali OTP yang sebelumnya sudah berhasil digunakan.

Expected:

```text
HTTP 422
```

```json
{
    "success": false,
    "message": "OTP tidak ditemukan atau sudah digunakan."
}
```

---

### Percobaan OTP Melebihi Batas

Masukkan OTP yang salah sebanyak 5 kali.

Expected:

```text
HTTP 429
```

```json
{
    "success": false,
    "message": "Terlalu banyak percobaan OTP."
}
```

---

### Request OTP Terlalu Sering

Kirim request forgot password lebih dari batas rate limiter.

Expected:

```text
HTTP 429
```

```json
{
    "success": false,
    "message": "Terlalu banyak permintaan OTP. Silakan coba lagi nanti."
}
```

---

# Struktur File

Komponen forgot password saat ini berada pada struktur:

```text
rajasa_backend/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       └── PasswordResetController.php
│   │   │
│   │   └── Requests/
│   │       ├── ForgotPasswordRequest.php
│   │       ├── VerifyOtpRequest.php
│   │       └── ResetPasswordRequest.php
│   │
│   ├── Mail/
│   │   └── PasswordResetOtpMail.php
│   │
│   └── Models/
│       └── PasswordResetOtp.php
│
├── database/
│   └── migrations/
│       └── create_password_reset_otps_table.php
│
├── resources/
│   └── views/
│       └── emails/
│           └── password-reset-otp.blade.php
│
└── routes/
    └── api.php
```

---

# Keamanan Saat Ini

Implementasi saat ini menerapkan beberapa mekanisme keamanan:

* OTP dibuat menggunakan `random_int()`.
* OTP disimpan dalam bentuk hash.
* OTP memiliki masa berlaku 30 menit.
* OTP hanya dapat digunakan satu kali.
* OTP memiliki batas 5 percobaan.
* Request OTP memiliki rate limiting.
* Response forgot password dibuat generic.
* Password menggunakan Laravel hashing.
* Token Sanctum lama dihapus setelah password di-reset.
* Credential Gmail disimpan melalui `.env`.
* Gmail App Password tidak disimpan di source code.

---

# Catatan Pengembangan

Implementasi forgot password ini merupakan bagian dari pengembangan backend Rajasa dan dapat mengalami perubahan seiring bertambahnya kebutuhan aplikasi.

Jika terdapat perubahan pada:

* durasi OTP,
* mekanisme verifikasi,
* endpoint,
* response API,
* rate limiting,
* email template,
* authentication,
* struktur database,
* atau mekanisme keamanan,

maka dokumentasi pada bagian terkait perlu diperbarui agar tetap sesuai dengan implementasi terbaru.

Dokumentasi ini **bukan penanda bahwa pengembangan backend telah selesai**, melainkan catatan dari fitur yang sudah tersedia pada tahap pengembangan saat ini.
