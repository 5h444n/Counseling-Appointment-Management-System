<!DOCTYPE html>
<html>
<body>
    <h2>Hello {{ $student->name }},</h2>
    <p>Good news! A slot you were waiting for with {{ $slot->advisor->name }} is now OPEN.</p>
    <p><strong>Time:</strong> {{ $slot->start_time->format('M d, h:i A') }}</p>
    <p>Log in and book it immediately before someone else does!</p>
    <a href="{{ route('login') }}">Login Now</a>
</body>
</html>
