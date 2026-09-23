<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">

    <h2>Reset Password Rajasa</h2>

    <p>Halo,</p>

    <p>
        Kami menerima permintaan untuk mereset password akun Rajasa Anda.
    </p>

    <p>
        Gunakan kode OTP berikut:
    </p>

    <div style="
        font-size: 32px;
        font-weight: bold;
        letter-spacing: 8px;
        margin: 24px 0;
    ">
        {{ $otp }}
    </div>

    <p>
        Kode OTP ini berlaku selama <strong>30 menit</strong>.
    </p>

    <p>
        Jika Anda tidak meminta reset password, abaikan email ini.
    </p>

    <p>
        Jangan berikan kode OTP ini kepada siapa pun.
    </p>

    <p>
        Terima kasih,<br>
        <strong>Rajasa</strong>
    </p>

</body>
</html>