<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Low Balance Notification</title>
</head>
<body>
    <p>Hi {{ $user->name }},</p>

    <p>Your balance has dropped below $5, so auto trading has been automatically disabled to prevent further purchases.</p>

    <p>Please <a href="{{ url('/wallet') }}">reload your balance</a> to continue using auto trading.</p>

    <p>Thank you,<br>Your CryptoBot Team</p>
</body>
</html>
