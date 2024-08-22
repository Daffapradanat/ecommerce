<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Password Reset Code</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            padding: 20px;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
            color: #007bff;
        }
        .code {
            font-size: 32px;
            font-weight: bold;
            color: #007bff;
            text-align: center;
            margin: 20px 0;
        }
        .footer {
            font-size: 14px;
            text-align: center;
            color: #888;
            margin-top: 30px;
        }
        .footer a {
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            Password Reset Request
        </div>
        <p>Hello,</p>
        <p>We received a request to reset your password. Please use the following code to proceed with resetting your password:</p>
        <div class="code">
            {{ $code }}
        </div>
        <p>This code will expire in <strong>15 minutes</strong>.</p>
        <p>If you did not request a password reset, please ignore this email or contact support if you have concerns.</p>
        <div class="footer">
            <p>Thank you for using our service!</p>
            <p><a href="{{ url('/') }}">Visit our website</a></p>
        </div>
    </div>
</body>
</html>
