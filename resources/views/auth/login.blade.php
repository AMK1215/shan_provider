<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GSC PLUS | Dashboard</title>

    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}">
    <style>
       

        /* Mobile view adjustments */
        @media (max-width: 768px) {
            .login-page {
                background-size: cover;
                background-image: url(img/mobile.png);
                padding: 20px;
                /* Add padding for smaller screens */
            }

        }

        body, html {
    height: 100%;
    margin: 0;
    padding: 0;
    overflow: hidden;
}

#rainbow-bg {
    position: fixed;
    top: 0; left: 0; width: 100vw; height: 100vh;
    z-index: 0;
    pointer-events: none;
}

.login-box {
    position: relative;
    z-index: 2;
}

    </style>
</head>

<body class="hold-transition login-page">
<canvas id="rainbow-bg"></canvas>
    <div class="login-box">
        <div class="login-logo">
            <h2 class="text-white">Login</h2>
        </div>
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">Sign in to start your session</p>
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="input-group mb-3">
                        <input id="" type="text"
                            class="form-control @error('user_name') is-invalid @enderror" name="user_name"
                            value="{{ old('user_name') }}" required placeholder="Enter User Name" autofocus>
                        @error('user_name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input id="password" type="password"
                            class="form-control @error('password') is-invalid @enderror" name="password" required
                            placeholder="Enter Password">

                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-eye" onclick="PwdView()" id="eye"
                                    style="cursor: pointer;"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-8">
                            <div class="icheck-primary">
                                <input type="checkbox" id="remember">
                                <label for="remember">
                                    Remember Me
                                </label>
                            </div>
                        </div>

                        <div class="col-4">
                            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                        </div>

                    </div>
                </form>
            </div>

        </div>
    </div>

    <!-- chat box -->
        <!-- Telegram Chat Box -->
<div id="chatPopup" style="position: fixed; bottom: 20px; right: 20px; width: 320px; display: none; z-index: 1000;">
    <div style="background: #007bff; color: white; padding: 10px; border-radius: 10px 10px 0 0; cursor: pointer;" onclick="toggleChat()">💬 Need Help?</div>
    <div style="background: white; border: 1px solid #ccc; border-top: none; padding: 10px; max-height: 400px; overflow-y: auto;" id="chatBox">
        <div><small>Bot: Hello! Welcome to PoneWine. Type your message below.</small></div>
    </div>
    <form id="chatForm" onsubmit="sendChat(event)" style="display: flex; border-top: 1px solid #ccc;">
        <input type="text" id="chatInput" placeholder="Type..." required style="flex: 1; padding: 5px; border: none;">
        <button type="submit" style="padding: 5px 10px; border: none; background: #007bff; color: white;">Send</button>
    </form>
</div>

<!-- Chat Toggle Button -->
<button onclick="toggleChat()" style="position: fixed; bottom: 20px; right: 20px; z-index: 999; background: #007bff; color: white; border: none; border-radius: 50%; width: 50px; height: 50px; font-size: 18px;">
    💬
</button>

    <!-- chat box end -->


    <script>
        function PwdView() {
            var x = document.getElementById("password");
            var y = document.getElementById("eye");

            if (x.type === "password") {
                x.type = "text";
                y.classList.remove('fa-eye');
                y.classList.add('fa-eye-slash');
            } else {
                x.type = "password";
                y.classList.remove('fa-eye-slash');
                y.classList.add('fa-eye');
            }
        }
    </script>

    <!-- chat box -->
    <script>
    function toggleChat() {
        const chat = document.getElementById('chatPopup');
        chat.style.display = chat.style.display === 'none' ? 'block' : 'none';
    }

    async function sendChat(event) {
        event.preventDefault();
        const input = document.getElementById('chatInput');
        const text = input.value.trim();
        if (!text) return;

        const chatBox = document.getElementById('chatBox');
        chatBox.innerHTML += `<div><strong>You:</strong> ${text}</div>`;
        input.value = '';

        const res = await fetch('{{ route('web.telegram.send') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ message: text })
        });
        const data = await res.json();
        chatBox.innerHTML += `<div><strong>Bot:</strong> ${data.reply}</div>`;
        chatBox.scrollTop = chatBox.scrollHeight;
    }
</script>

    <!-- chat box -->

<script>
        // rainbow-waves.js
        const canvas = document.getElementById('rainbow-bg');
const ctx = canvas.getContext('2d');

function resizeCanvas() {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
}
resizeCanvas();
window.addEventListener('resize', resizeCanvas);

// More vivid rainbow color stops
const colors = [
    "#FF0000", // red
    "#FF7F00", // orange
    "#FFFF00", // yellow
    "#00FF00", // green
    "#00FFFF", // cyan
    "#0000FF", // blue
    "#8B00FF", // violet
    "#FF00FF", // magenta
    "#FF0000"  // repeat red for smooth loop
];

let t = 0;

function drawWaves() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    let amplitude = 75;
    let waveCount = 6;
    let heightUnit = canvas.height / (waveCount + 1);

    for (let i = 0; i < waveCount; i++) {
        ctx.beginPath();
        for (let x = 0; x <= canvas.width; x += 2) {
            let angle = (x / (180 + i * 10)) + t * (0.5 + 0.15 * i);
            let y = Math.sin(angle + i * 2) * amplitude + (i + 1) * heightUnit;
            if (x === 0) ctx.moveTo(x, y);
            else ctx.lineTo(x, y);
        }
        ctx.lineTo(canvas.width, canvas.height);
        ctx.lineTo(0, canvas.height);
        ctx.closePath();

        // Create a more vibrant, multi-color gradient
        let grad = ctx.createLinearGradient(0, 0, canvas.width, 0);
        let colorOffset = i;
        let colorStops = 5;
        for (let j = 0; j < colorStops; j++) {
            let idx = (colorOffset + j) % colors.length;
            grad.addColorStop(j / (colorStops - 1), colors[idx]);
        }
        ctx.fillStyle = grad;
        ctx.globalAlpha = 0.35 + 0.11 * Math.sin(t + i * 2); // slightly more visible
        ctx.fill();
    }

    ctx.globalAlpha = 1;
    t += 0.010;
    requestAnimationFrame(drawWaves);
}

drawWaves();

// const canvas = document.getElementById('rainbow-bg');
// const ctx = canvas.getContext('2d');

// function resizeCanvas() {
//     canvas.width = window.innerWidth;
//     canvas.height = window.innerHeight;
// }
// resizeCanvas();
// window.addEventListener('resize', resizeCanvas);

// const colors = [
//     '#FF3CAC', // pink
//     '#784BA0', // purple
//     '#2B86C5', // blue
//     '#2FFFAF', // teal
//     '#FFF720', // yellow
//     '#FF3CAC'  // repeat for loop
// ];

// let t = 0;

// function drawWaves() {
//     ctx.clearRect(0, 0, canvas.width, canvas.height);

//     let amplitude = 60;
//     let waveCount = 5;
//     let heightUnit = canvas.height / (waveCount + 1);

//     for (let i = 0; i < waveCount; i++) {
//         ctx.beginPath();
//         for (let x = 0; x <= canvas.width; x += 2) {
//             let angle = (x / 220) + t * (0.7 + 0.2 * i);
//             let y = Math.sin(angle + i) * amplitude + (i + 1) * heightUnit;
//             if (x === 0) ctx.moveTo(x, y);
//             else ctx.lineTo(x, y);
//         }
//         ctx.lineTo(canvas.width, canvas.height);
//         ctx.lineTo(0, canvas.height);
//         ctx.closePath();

//         // Create gradient for each wave
//         let grad = ctx.createLinearGradient(0, 0, canvas.width, 0);
//         grad.addColorStop(0, colors[i]);
//         grad.addColorStop(1, colors[i + 1]);
//         ctx.fillStyle = grad;
//         ctx.globalAlpha = 0.28 + 0.15 * Math.sin(t + i);
//         ctx.fill();
//     }

//     ctx.globalAlpha = 1;
//     t += 0.012;
//     requestAnimationFrame(drawWaves);
// }

// drawWaves();

    </script>

</body>

</html>
