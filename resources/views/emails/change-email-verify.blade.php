<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email Change</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4a90e2;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 20px;
            margin-top: 20px;
        }
        .verification-code {
            background-color: #e9f2ff;
            border: 2px dashed #4a90e2;
            color: #4a90e2;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 5px;
            margin: 20px 0;
            padding: 15px;
            text-align: center;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name') }}</h1>
    </div>
    <div class="content">
        <p>Hello {{ $user->name }},</p>
        <p>You are receiving this email because you requested to change your email address for your account at {{ config('app.name') }}.</p>
        <p>To verify this change, please use the following verification code:</p>
        <div class="verification-code">
            {{ $user->email_change_verification_code }}
        </div>
        <p>This code will expire in 60 minutes for security reasons.</p>
        <p>If you did not request an email change, please ignore this email or contact our support team immediately if you believe this is an unauthorized action.</p>
        <div class="warning">
            <strong>Important:</strong> For security reasons, we will never ask you to provide this code via email or phone. Always enter the code on our official website or app.
        </div>
        <p>Thank you for helping us keep your account secure.</p>
        <p>Best regards,<br>The {{ config('app.name') }} Team</p>
    </div>
</body>
</html>
