<!DOCTYPE html>
<html>
<head>
    <title>Telegram Bot Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Telegram Bot Test Panel</h2>
        
        <div class="card mt-4">
            <div class="card-header">
                Webhook Management
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <a href="{{ url('telegram/webhook/info') }}" class="btn btn-info" target="_blank">Check Webhook Info</a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ url('telegram/webhook/set') }}" class="btn btn-success" target="_blank">Set Webhook</a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ url('telegram/webhook/delete') }}" class="btn btn-danger" target="_blank">Delete Webhook</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                Send Messages
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="{{ url('sendMessage') }}" class="btn btn-primary w-100" target="_blank">Send Text</a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ url('sendPhoto') }}" class="btn btn-primary w-100" target="_blank">Send Photo</a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ url('sendAudio') }}" class="btn btn-primary w-100" target="_blank">Send Audio</a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ url('sendVideo') }}" class="btn btn-primary w-100" target="_blank">Send Video</a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ url('sendVoice') }}" class="btn btn-primary w-100" target="_blank">Send Voice</a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ url('sendDocument') }}" class="btn btn-primary w-100" target="_blank">Send Document</a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ url('sendLocation') }}" class="btn btn-primary w-100" target="_blank">Send Location</a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ url('sendVenue') }}" class="btn btn-primary w-100" target="_blank">Send Venue</a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ url('sendContact') }}" class="btn btn-primary w-100" target="_blank">Send Contact</a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ url('sendPoll') }}" class="btn btn-primary w-100" target="_blank">Send Poll</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 