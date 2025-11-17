<?php
// leaderboard.php (UTF-8)
header('Content-Type: text/html; charset=UTF-8');

// API local (tras Nginx → PHP-FPM)
$API = 'http://127.0.0.1/api/leaderboard?limit=50';

// Llamada a la API con cURL
$ch = curl_init($API);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 3,
    CURLOPT_TIMEOUT        => 5,
]);
$resp = curl_exec($ch);
$err  = curl_error($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Validación básica
if ($err || $code !== 200) {
    http_response_code(502);
    echo "<pre>ERROR API ($code): " . htmlspecialchars($err ?: $resp, ENT_QUOTES, 'UTF-8') . "</pre>";
    exit;
}

$data = json_decode($resp, true);
if (!is_array($data)) {
    http_response_code(500);
    echo "<pre>Respuesta no válida de la API:\n" . htmlspecialchars($resp, ENT_QUOTES, 'UTF-8') . "</pre>";
    exit;
}
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Últimas sesiones</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <!-- Fuente futurista opcional; si no carga, cae en system-ui -->
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    :root{
      --bg-0:#070b12;
      --bg-1:#0b1220;
      --panel:#0e1a2b;
      --edge:#1b2f4a;
      --txt:#e7f4ff;
      --muted:#9bb7d1;
      --accent:#31d2ff;  /* cian neón */
      --accent-2:#5b7cff;/* azul neón */
      --glow: 0 0 14px rgba(49,210,255,.45), 0 0 28px rgba(49,124,255,.25);
      --radius:18px;
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0;
      color:var(--txt);
      background:
        radial-gradient(1200px 600px at 80% -10%, rgba(49,124,255,.08) 0%, transparent 60%),
        radial-gradient(900px 500px at -10% 110%, rgba(49,210,255,.08) 0%, transparent 60%),
        linear-gradient(180deg, var(--bg-0) 0%, var(--bg-1) 100%);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      letter-spacing:.2px;
    }

    /* Contenedor principal */
    .wrap{
      max-width:1100px;
      margin:48px auto;
      padding:0 20px;
    }

    /* Banner estilo HUD */
    .hud-banner{
      position:relative;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
      padding:14px 18px;
      font-family: Orbitron, Inter, system-ui, sans-serif;
      text-transform:uppercase;
      letter-spacing:.08em;
      background:
        linear-gradient(180deg, rgba(10,25,46,.9) 0%, rgba(10,20,36,.75) 100%);
      border:1px solid var(--edge);
      border-radius:12px;
      box-shadow: var(--glow);
      clip-path: polygon(10px 0, calc(100% - 10px) 0, 100% 10px, 100% calc(100% - 10px), calc(100% - 10px) 100%, 10px 100%, 0 calc(100% - 10px), 0 10px);
      overflow:hidden;
    }
    .hud-banner::before{
      content:"";
      position:absolute; inset:0;
      pointer-events:none;
      background:
        linear-gradient(90deg, transparent 0 6%, rgba(49,210,255,.25) 10%, rgba(91,124,255,.22) 30%, transparent 40% 100%);
      mix-blend-mode:screen;
      opacity:.6;
    }
    .hud-title{
      display:flex; align-items:center; gap:10px;
      font-weight:700; font-size:20px;
    }
    .hud-pill{
      font-size:11px; padding:6px 10px; border-radius:999px;
      border:1px solid rgba(49,210,255,.45);
      background:linear-gradient(180deg, rgba(49,210,255,.12), rgba(91,124,255,.08));
      color:#cfeeff;
    }

    /* Tarjeta panel con esquinas anguladas */
    .panel{
      position:relative;
      margin-top:20px;
      background:linear-gradient(180deg, rgba(13,26,45,.9), rgba(9,18,34,.9));
      border:1px solid var(--edge);
      border-radius:var(--radius);
      box-shadow: var(--glow);
      clip-path: polygon(16px 0, calc(100% - 16px) 0, 100% 16px, 100% calc(100% - 16px), calc(100% - 16px) 100%, 16px 100%, 0 calc(100% - 16px), 0 16px);
      overflow:hidden;
    }
    .panel::before, .panel::after{
      content:"";
      position:absolute; inset:0; pointer-events:none;
      border-radius:var(--radius);
    }
    .panel::before{
      border:1px solid rgba(49,210,255,.15);
      clip-path: inherit;
    }
    .panel::after{
      background:
        linear-gradient( to right, rgba(49,210,255,.06), transparent 30% 70%, rgba(91,124,255,.06) ),
        radial-gradient(800px 240px at 20% -10%, rgba(49,210,255,.08), transparent 60%);
      opacity:.7;
      mix-blend-mode:screen;
    }

    /* Tabla estilo overlay */
    .tbl{
      width:100%;
      border-collapse:separate;
      border-spacing:0;
    }
    .tbl thead th{
      position:sticky; top:0; z-index:1;
      background:
        linear-gradient(180deg, rgba(20,40,70,.9), rgba(12,26,48,.9));
      font-family: Orbitron, Inter, system-ui, sans-serif;
      text-transform:uppercase;
      font-size:12px; letter-spacing:.12em;
      color:#cfe3ff;
      padding:14px 14px;
      border-bottom:1px solid rgba(99,140,200,.25);
    }
    .tbl th:first-child, .tbl td:first-child{ padding-left:18px; }
    .tbl th:last-child, .tbl td:last-child{ padding-right:18px; }

    .tbl tbody tr{
      transition: background .15s ease, transform .15s ease;
    }
    .tbl tbody tr:nth-child(odd){
      background: linear-gradient(180deg, rgba(10,22,40,.55), rgba(9,18,34,.35));
    }
    .tbl tbody tr:nth-child(even){
      background: linear-gradient(180deg, rgba(10,20,36,.45), rgba(8,16,30,.30));
    }
    .tbl tbody tr:hover{
      background: linear-gradient(180deg, rgba(18,46,74,.75), rgba(14,36,64,.55));
      box-shadow: inset 0 0 0 1px rgba(49,210,255,.15);
      transform: translateY(-1px);
    }
    .tbl td{
      padding:12px 14px;
      border-bottom:1px solid rgba(99,140,200,.15);
      color:var(--txt);
      white-space:nowrap;
    }
    .right{text-align:right}
    .rank{
      font-family: Orbitron, Inter, system-ui, sans-serif;
      color:#a7d9ff;
      opacity:.9;
    }
    .name{
      font-weight:600;
      color:#e9f6ff;
    }
    .chip{
      display:inline-block; padding:2px 8px; font-size:11px; border-radius:8px;
      border:1px solid rgba(49,210,255,.35);
      color:#bfe9ff;
      background:linear-gradient(180deg, rgba(49,210,255,.10), rgba(91,124,255,.08));
    }

    /* Responsive */
    @media (max-width:700px){
      .hud-title{ font-size:18px }
      .tbl thead{ display:none }
      .tbl, .tbl tbody, .tbl tr, .tbl td{ display:block; width:100% }
      .tbl tr{ margin:10px 12px; border:1px solid rgba(99,140,200,.18); border-radius:12px; overflow:hidden }
      .tbl td{ display:flex; justify-content:space-between; gap:16px; border:none; padding:10px 12px }
      .tbl td::before{
        content: attr(data-label);
        color:var(--muted);
        text-transform:uppercase;
        font-size:11px; letter-spacing:.12em;
        font-family: Orbitron, Inter, system-ui, sans-serif;
      }
      .rank{ min-width:36px }
    }
    /* Sutil animación de brillo en bordes */
    .panel, .hud-banner{ animation: glow 3.2s ease-in-out infinite alternate }
    @keyframes glow{
      from{ box-shadow: 0 0 0 rgba(0,0,0,0), var(--glow) }
      to  { box-shadow: 0 0 0 rgba(0,0,0,0), 0 0 24px rgba(49,210,255,.55), 0 0 40px rgba(91,124,255,.28) }
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="hud-banner">
      <div class="hud-title">Últimas sesiones</div>
      <div class="hud-pill">En vivo</div>
    </div>

    <div class="panel">
      <table class="tbl">
        <thead>
          <tr>
            <th>#</th>
            <th>Jugador</th>
            <th class="right">Kills</th>
            <th class="right">Rooms</th>
            <th>Última partida</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($data as $i => $r): ?>
          <tr>
            <td class="rank" data-label="#"><?= $i+1 ?></td>
            <td class="name" data-label="Jugador">
              <?= h($r['player_name'] ?? '-') ?>
            </td>
            <td class="right" data-label="Kills">
              <span class="chip"><?= (int)($r['best_kills'] ?? 0) ?></span>
            </td>
            <td class="right" data-label="Rooms">
              <span class="chip"><?= (int)($r['best_rooms'] ?? 0) ?></span>
            </td>
            <td data-label="Última partida"><?= h($r['last_played'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
