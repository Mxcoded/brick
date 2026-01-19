<!DOCTYPE html>
<html>
<body>
    <h3>New Contact Message</h3>
    <p>You have received a new message via the website contact form.</p>
    
    <ul>
        <li><strong>Name:</strong> {{ $data['name'] }}</li>
        <li><strong>Email:</strong> {{ $data['email'] }}</li>
        <li><strong>Date:</strong> {{ now()->format('M d, Y h:i A') }}</li>
    </ul>

    <div style="background: #f5f5f5; padding: 15px; border-left: 4px solid #d4a017;">
        <strong>Message:</strong><br>
        {!! nl2br(e($data['message'])) !!}
    </div>
</body>
</html>