<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email</title>
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
        .button {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 15px 32px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name') }}</h1>
    </div>
    <div class="content">
        <p>Hello {{ $user->name }},</p>
        <p>Thank you for registering with {{ config('app.name') }}. We're excited to have you on board!</p>
        <p>To complete your registration and verify your email address, please use the following verification code:</p>
        <div class="verification-code">
            {{ $user->verification_code }}
        </div>
        <p>This code will expire in 60 minutes for security reasons.</p>
        <p>If you did not create an account, please disregard this email.</p>
        <p>
            <a href="{{ url('/email/verify') }}" class="button">Verify Email Address</a>
        </p>
        <p>If you're having trouble clicking the "Verify Email Address" button, copy and paste the URL below into your web browser:</p>
        <p>{{ url('/email/verify') }}</p>
        <p>Thank you for choosing {{ config('app.name') }}!</p>
        <p>Best regards,<br>The {{ config('app.name') }} Team</p>
    </div>
</body>
</html>
