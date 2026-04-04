@extends('layouts.auth')

@section('content')

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

body, html {
    margin: 0;
    padding: 0;
    font-family: 'Poppins', sans-serif;
    background: rgb(2,6,23);
    color: #fff;
    height: 100%;
    overflow: hidden;
}

/* Particle background */
#particles-js {
    position: fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    z-index: 0;
    pointer-events: none;
}

.login-wrapper {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    gap: 2rem;
    padding: 2rem;
    position: relative;
    z-index: 2; /* panel particle ustida turadi */
}

/* Panels */
.info-panel, .form-panel {
    flex: 1;
    max-width: 450px;
    background: rgba(248, 246, 246, 0.05);
    backdrop-filter: blur(18px);
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 8px 32px rgba(36, 35, 35, 0.4);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    z-index: 2;
}

.info-panel:hover, .form-panel:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 36px rgba(0,0,0,0.5);
}

/* Info panel */
.info-title {
    font-size: 2rem;
    font-weight: 600;
    margin: 1rem 0;
}

.info-desc {
    opacity: 0.85;
    font-size: 0.95rem;
    line-height: 1.5;
}

.badge-tag {
    background: rgba(255, 255, 255, 0.178);
    padding: 6px 14px;
    border-radius: 14px;
    font-weight: 500;
    font-size: 12px;
    display: inline-block;
}

.role-cards {
    display: flex;
    gap: 12px;
    margin-top: 2rem;
    flex-wrap: wrap;
}

.role-card {
    background: rgba(255,255,255,0.1);
    padding: 14px 18px;
    border-radius: 16px;
    cursor: pointer;
    transition: transform 0.2s ease, background 0.3s ease;
    min-width: 100px;
    text-align: center;
    font-weight: 500;
}

.role-card:hover {
    background: rgba(255,255,255,0.2);
    transform: scale(1.05);
}

.role-label { display:block; font-size:14px; margin-bottom:4px; }
.role-user { font-size:12px; opacity:0.8; }

/* Form panel */
.form-title { font-size:24px; font-weight:600; margin-bottom:0.5rem; }
.form-subtitle { font-size:14px; color: rgba(255,255,255,0.6); margin-bottom:2rem; }

.field-group { margin-bottom:1.5rem; position:relative; }
.field-group input {
    width:100%;
    padding:12px;
    border-radius:12px;
    border:none;
    background: rgba(255,255,255,0.1);
    color:#fff;
    font-size:14px;
    backdrop-filter: blur(6px);
    transition: 0.3s ease;
}
.field-group input:focus {
    background: rgba(255,255,255,0.2);
    outline:none;
    box-shadow: 0 0 0 2px rgba(99,102,241,0.5);
}

.toggle-pw {
    position:absolute;
    right:12px;
    top:50%;
    transform:translateY(-50%);
    border:none;
    background:none;
    cursor:pointer;
    color:#fff;
    font-size:16px;
}

.submit-btn {
    width:100%;
    padding:12px;
    border-radius:16px;
    background: linear-gradient(90deg,#6366f1,#4f46e5);
    border:none;
    font-weight:600;
    cursor:pointer;
    color:#fff;
    font-size:16px;
    transition: all 0.3s ease;
}
.submit-btn:hover {
    background: linear-gradient(90deg,#4f46e5,#3730a3);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
}

@media(max-width:768px){
    .login-wrapper { flex-direction:column; }
}

</style>

<!-- Particle container -->
<div id="particles-js"></div>

<div class="login-wrapper">

    <section class="info-panel">
        <span class="badge-tag">Restaurant POS MVP</span>
        <h1 class="info-title">Login, branch flow va checkout bilan ishlaydigan birinchi versiya</h1>
        <p class="info-desc">
            Tizimda admin, manager va cashier rollari tayyor. Branchlar, stollar,
            kategoriya, mahsulot, order, payment, receipt va basic report oqimi
            bitta loyihada jamlangan.
        </p>

        <div class="role-cards">
            @foreach ([
                ['role'=>'Admin','user'=>'admin','pass'=>'admin456'],
                ['role'=>'Manager','user'=>'manager','pass'=>'manager456'],
                ['role'=>'Cashier','user'=>'cashier','pass'=>'cashier456'],
            ] as $cred)
            <button type="button" class="role-card"
                    data-login="{{ $cred['user'] }}"
                    data-password="{{ $cred['pass'] }}">
                <span class="role-label">{{ $cred['role'] }}</span>
                <span class="role-user">{{ $cred['user'] }}</span>
            </button>
            @endforeach
        </div>
    </section>

    <section class="form-panel">
        <div class="form-inner">
            <h2 class="form-title">Accountga kiring</h2>
            <p class="form-subtitle">Role asosida kerakli bo'limga yo'naltirilasiz</p>

            <form id="loginForm" action="{{ route('login.store') }}" method="POST">
                @csrf

                <div class="field-group">
                    <label for="login">Login</label>
                    <input id="login" type="text" name="login" placeholder="admin" required>
                </div>

                <div class="field-group">
                    <label for="password">Parol</label>
                    <input id="password" type="password" name="password" placeholder="••••••••" required>
                    <button type="button" class="toggle-pw">👁️</button>
                </div>

                <button type="submit" class="submit-btn">Kirish</button>
            </form>
        </div>
    </section>

</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js" defer></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Toggle password
    const toggle=document.querySelector(".toggle-pw");
    const password=document.getElementById("password");
    toggle.addEventListener("click",function(){
        password.type = password.type==="password"?"text":"password";
    });

    // Autofill roles
    document.querySelectorAll(".role-card").forEach(card=>{
        card.addEventListener("click", function(){
            document.getElementById("login").value = this.dataset.login;
            document.getElementById("password").value = this.dataset.password;
            document.getElementById("loginForm").submit();
        });
    });

    // Particle.js
    particlesJS('particles-js', {
        "particles": {
            "number": { "value": 80, "density": { "enable": true, "value_area": 800 } },
            "color": { "value": "#ffffff" },
            "shape": { "type": "circle" },
            "opacity": { "value": 0.2, "random": true },
            "size": { "value": 3, "random": true },
            "line_linked": { "enable": true, "distance": 120, "color": "#ffffff", "opacity": 0.5, "width": 1 },
            "move": { "enable": true, "speed": 1, "direction": "none", "random": true, "straight": false, "out_mode": "out" }
        },
        "interactivity": {
            "detect_on": "canvas",
            "events": { "onhover": { "enable": true, "mode": "repulse" }, "onclick": { "enable": true, "mode": "push" } },
            "modes": { "repulse": { "distance": 100 }, "push": { "particles_nb": 4 } }
        },
        "retina_detect": true
    });
});
</script>

@endsection