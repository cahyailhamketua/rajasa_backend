<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Mail\PasswordResetOtpMail;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class PasswordResetController extends Controller
{
    /**
     * Mengirim OTP ke email user.
     */
    public function sendOtp(ForgotPasswordRequest $request): JsonResponse
    {
        $email = $request->email;

        $user = User::where('email', $email)->first();

        /*
         * Jangan membocorkan apakah email terdaftar atau tidak.
         */
        if (!$user) {
            return response()->json([
                'success' => true,
                'message' => 'Jika email terdaftar, kode OTP telah dikirim.',
            ]);
        }

        /*
         * Batasi request OTP agar tidak disalahgunakan.
         */
        $rateLimitKey = 'password-reset-otp:' . $email;

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak permintaan OTP. Silakan coba lagi nanti.',
            ], 429);
        }

        RateLimiter::hit($rateLimitKey, 60);

        /*
         * Hapus OTP lama yang masih aktif.
         */
        PasswordResetOtp::where('user_id', $user->id)
            ->whereNull('used_at')
            ->delete();

        /*
         * Generate OTP 6 digit.
         */
        $otp = (string) random_int(100000, 999999);

        /*
         * Simpan OTP dalam bentuk hash.
         */
        PasswordResetOtp::create([
            'user_id' => $user->id,
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(30),
            'attempts' => 0,
        ]);

        /*
         * Kirim OTP ke email.
         */
        Mail::to($user->email)->send(
            new PasswordResetOtpMail($otp)
        );

        return response()->json([
            'success' => true,
            'message' => 'Jika email terdaftar, kode OTP telah dikirim.',
        ]);
    }

    /**
     * Verifikasi OTP.
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'OTP tidak valid.',
            ], 422);
        }

        $otpRecord = PasswordResetOtp::where('user_id', $user->id)
            ->whereNull('used_at')
            ->latest()
            ->first();

        if (!$otpRecord) {
            return response()->json([
                'success' => false,
                'message' => 'OTP tidak ditemukan atau sudah digunakan.',
            ], 422);
        }

        if ($otpRecord->expires_at->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'OTP sudah kedaluwarsa.',
            ], 422);
        }

        if ($otpRecord->attempts >= 5) {
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak percobaan OTP.',
            ], 429);
        }

        if (!Hash::check($request->otp, $otpRecord->otp_hash)) {
            $otpRecord->increment('attempts');

            return response()->json([
                'success' => false,
                'message' => 'OTP tidak valid.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP berhasil diverifikasi.',
        ]);
    }

    /**
     * Reset password menggunakan OTP.
     */
    public function resetPassword(
        ResetPasswordRequest $request
    ): JsonResponse {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'OTP tidak valid.',
            ], 422);
        }

        $otpRecord = PasswordResetOtp::where('user_id', $user->id)
            ->whereNull('used_at')
            ->latest()
            ->first();

        if (!$otpRecord) {
            return response()->json([
                'success' => false,
                'message' => 'OTP tidak ditemukan atau sudah digunakan.',
            ], 422);
        }

        if ($otpRecord->expires_at->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'OTP sudah kedaluwarsa.',
            ], 422);
        }

        if ($otpRecord->attempts >= 5) {
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak percobaan OTP.',
            ], 429);
        }

        if (!Hash::check($request->otp, $otpRecord->otp_hash)) {
            $otpRecord->increment('attempts');

            return response()->json([
                'success' => false,
                'message' => 'OTP tidak valid.',
            ], 422);
        }

        /*
         * Update password.
         */
        $user->update([
            'password' => $request->password,
        ]);

        /*
         * Tandai OTP sudah digunakan.
         */
        $otpRecord->update([
            'used_at' => now(),
        ]);

        /*
         * Logout semua token lama.
         * Ini penting karena password berhasil diganti
         * melalui forgot password.
         */
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah. Silakan login kembali.',
        ]);
    }
}