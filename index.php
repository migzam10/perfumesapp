<?php
session_start();
if (isset($_SESSION['user_id'])) {
  header('Location: app.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>Perfumes — Acceso</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

<link rel="apple-touch-icon" href="assets/icon.jpg">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<link rel="icon" href="assets/icon.jpg" type="image/x-icon">
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{background:#0d0b0e;color:#f0ebe8;font-family:'DM Sans',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;}
.wrap{width:100%;max-width:370px;}
.logo{text-align:center;margin-bottom:36px;}
.logo h1{font-family:'Playfair Display',serif;font-size:2.5rem;color:#c9a96e;}
.logo p{font-size:.72rem;color:#a09898;margin-top:5px;letter-spacing:2px;text-transform:uppercase;}
.card{background:#1a1520;border:1px solid rgba(201,169,110,.18);border-radius:18px;padding:28px 24px;}
.card h2{font-size:1.2rem;color:#e8c99a;margin-bottom:20px;font-weight:500;}
label{font-size:.8rem;color:#a09898;display:block;margin-bottom:4px;}
input{width:100%;background:#241d2c;border:1px solid rgba(201,169,110,.18);border-radius:10px;padding:11px 13px;color:#f0ebe8;font-family:'DM Sans',sans-serif;font-size:.88rem;outline:none;margin-bottom:14px;transition:border-color .2s;}
input:focus{border-color:#c9a96e;}
.btn{width:100%;padding:13px;border:none;border-radius:12px;background:linear-gradient(135deg,#c9a96e,#e8c99a);color:#1a1520;font-family:'DM Sans',sans-serif;font-size:.9rem;font-weight:700;cursor:pointer;}
.btn:active{opacity:.85;}
.error{background:rgba(224,112,112,.12);border:1px solid rgba(224,112,112,.28);border-radius:9px;padding:9px 13px;font-size:.78rem;color:#e07070;margin-bottom:14px;}
.hint{font-size:.68rem;color:#a09898;text-align:center;margin-top:14px;}
</style>
</head>
<body>
<div class="wrap">
  <div class="logo">
    <h1>Asaria Perfumería</h1>
    <p></p>
  </div>
  <div class="card">
    <h2>Iniciar sesión</h2>
    <div class="error" id="err" style="display:none"></div>
    <label>Usuario</label>
    <input type="text" id="u" placeholder="usuario" autocomplete="username">
    <label>Contraseña</label>
    <input type="password" id="p" placeholder="••••••••" autocomplete="current-password" onkeydown="if(event.key==='Enter')login()">
    <button class="btn" onclick="login()">Entrar</button>
  </div>
</div>

<script>
async function login() {
  const u = document.getElementById('u').value.trim();
  const p = document.getElementById('p').value;
  const err = document.getElementById('err');
  err.style.display = 'none';
  try {
    const r = await fetch('api/index.php?action=login', {
      method: 'POST',
      headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
      body: JSON.stringify({usuario: u, password: p})
    });
    const j = await r.json();
    if (!r.ok) { err.textContent = j.error; err.style.display = 'block'; return; }
    window.location.href = 'app.php';
  } catch(e) { err.textContent = 'Error de conexión'; err.style.display = 'block'; }
}
</script>
</body>
</html>
