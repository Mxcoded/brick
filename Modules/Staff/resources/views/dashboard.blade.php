<!DOCTYPE html>
<html>
<head>
    <title>Staff Dashboard</title>
</head>
<body>
    <h1>Staff Dashboard</h1>
    <p>Welcome, {{ $user->name }}!</p>
    <p>Email: {{ $user->email }}</p>
    <a href="{{ route('logout') }}">Logout</a>
</body>
</html>