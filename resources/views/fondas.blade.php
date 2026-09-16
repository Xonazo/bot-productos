<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fondas cerca de ti</title>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root{
    --paper:#F6EEDD;
    --paper-2:#EFE4CC;
    --red:#A32638;
    --red-dark:#7E1D2B;
    --blue:#163A5F;
    --gold:#D6A24A;
    --ink:#2B241C;
    --muted:#6b6255;
  }
  *{ box-sizing:border-box; }
  body{
    margin:0;
    background:var(--paper);
    color:var(--ink);
    font-family:'Inter',sans-serif;
    -webkit-font-smoothing:antialiased;
  }
  a{ color:var(--red); }
  a:focus-visible, button:focus-visible, input:focus-visible{
    outline:3px solid var(--blue);
    outline-offset:2px;
  }

  .bunting{ width:100%; height:30px; display:block; }

  /* --- Header --- */
  header{
    background:var(--blue);
    color:var(--paper);
    padding:0 0 44px;
    position:relative;
    overflow:hidden;
  }
  .header-inner{
    max-width:820px;
    margin:0 auto;
    padding:32px 24px 0;
  }
  .eyebrow-date{
    font-size:14px;
    letter-spacing:.02em;
    color:var(--gold);
    margin:0 0 10px;
  }
  h1{
    font-family:'Fraunces',serif;
    font-optical-sizing:auto;
    font-weight:600;
    font-size:clamp(1.9rem, 6vw, 3.2rem);
    line-height:1.05;
    margin:0 0 14px;
    max-width:13ch;
  }
  header p.lead{
    font-size:16px;
    line-height:1.55;
    max-width:48ch;
    color:#DCE4EC;
    margin:0 0 24px;
  }

  .actions-row{
    display:flex;
    flex-wrap:wrap;
    gap:10px;
  }
  .btn{
    border:none;
    padding:13px 20px;
    border-radius:6px;
    font-size:15px;
    font-weight:600;
    font-family:'Inter',sans-serif;
    cursor:pointer;
    display:inline-flex;
    align-items:center;
    gap:8px;
    transition:transform .15s ease, background .15s ease;
    white-space:nowrap;
  }
  .btn-primary{ background:var(--red); color:var(--paper); }
  .btn-primary:hover{ background:var(--red-dark); }
  .btn-outline{ background:transparent; border:2px solid var(--gold); color:var(--paper); padding:11px 20px; }
  .btn-outline:hover{ background:rgba(214,162,74,.15); }
  .btn-telegram{ background:#229ED9; color:#fff; text-decoration:none; }
  .btn-telegram:hover{ background:#1B87BD; }
  .btn:active{ transform:scale(.98); }
  .btn:disabled{ opacity:.6; cursor:default; }

  /* --- Control de radio --- */
  .radius-control{
    margin-top:18px;
    background:rgba(255,255,255,.08);
    border-radius:8px;
    padding:14px 16px;
    max-width:420px;
  }
  .radius-control-label{
    display:flex;
    justify-content:space-between;
    font-size:14px;
    margin-bottom:8px;
    color:#DCE4EC;
  }
  .radius-control-label strong{ color:var(--gold); }
  input[type="range"]{
    width:100%;
    accent-color:var(--gold);
    cursor:pointer;
  }

  /* --- Cuerpo --- */
  main{ max-width:820px; margin:0 auto; padding:0 24px 80px; }
  #map{
    height:380px;
    border-radius:10px;
    margin-top:-24px;
    border:6px solid var(--paper);
    box-shadow:0 8px 24px rgba(43,36,28,.18);
    position:relative;
    z-index:2;
  }

  .results-heading{
    display:flex;
    align-items:baseline;
    justify-content:space-between;
    margin:32px 0 16px;
    flex-wrap:wrap;
    gap:6px;
  }
  .results-heading h2{
    font-family:'Fraunces',serif;
    font-weight:600;
    font-size:21px;
    margin:0;
  }
  .results-count{ font-size:14px; color:var(--muted); }

  .status-msg{
    padding:26px 20px;
    text-align:center;
    color:var(--muted);
    font-size:15px;
    border:1px dashed #C9BB9E;
    border-radius:8px;
  }
  .status-msg .btn{ margin-top:14px; }

  /* --- Tarjeta tipo "boleto" --- */
  .ticket{
    background:#fff;
    border-radius:8px;
    display:flex;
    margin-bottom:14px;
    box-shadow:0 2px 8px rgba(43,36,28,.08);
    position:relative;
    overflow:hidden;
    transition:box-shadow .15s ease;
  }
  .ticket:hover{ box-shadow:0 4px 14px rgba(43,36,28,.14); }
  .ticket-stub{
    width:10px;
    background:repeating-linear-gradient(to bottom, var(--gold) 0 8px, transparent 8px 16px);
    flex-shrink:0;
  }
  .ticket-stub::before, .ticket-stub::after{
    content:''; position:absolute; width:14px; height:14px;
    background:var(--paper); border-radius:50%; left:-2px;
  }
  .ticket-stub::before{ top:-7px; }
  .ticket-stub::after{ bottom:-7px; }
  .ticket-body{ padding:16px 18px; flex:1; min-width:0; }
  .ticket-body h3{ font-family:'Fraunces',serif; font-weight:600; font-size:17px; margin:0 0 6px; }
  .ticket-body p{ margin:0 0 8px; font-size:14px; color:#514A3F; line-height:1.5; }
  .ticket-meta{ display:flex; align-items:center; gap:10px; font-size:13px; flex-wrap:wrap; margin-top:10px; }
  .distance-badge{ background:var(--paper-2); color:var(--ink); padding:3px 9px; border-radius:100px; font-weight:600; }
  .ticket-meta a{ text-decoration:none; font-weight:600; }
  .ticket-meta a:hover{ text-decoration:underline; }

  /* --- Popup del mapa --- */
  .fonda-popup h4{ font-family:'Fraunces',serif; margin:0 0 6px; font-size:15px; }
  .fonda-popup p{ margin:0 0 6px; font-size:13px; line-height:1.4; }
  .fonda-popup a.popup-maps-link{
    display:inline-block; margin-top:4px; font-size:13px; font-weight:600;
    color:var(--red); text-decoration:none;
  }
  .fonda-popup a.popup-maps-link:hover{ text-decoration:underline; }
  .fonda-pin{ filter:drop-shadow(0 2px 3px rgba(0,0,0,.35)); }

  @media (prefers-reduced-motion: reduce){ .btn{ transition:none; } }

  /* --- Responsive --- */
  @media (max-width:600px){
    header{ padding-bottom:36px; }
    .header-inner{ padding:24px 18px 0; }
    main{ padding:0 18px 60px; }
    .actions-row{ flex-direction:column; }
    .btn{ width:100%; justify-content:center; }
    #map{ height:300px; }
    .radius-control{ max-width:100%; }
  }
</style>
</head>
<body>

<header>
  <svg class="bunting" viewBox="0 0 800 30" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <path d="M0 0 L40 0 L20 30 Z" fill="#A32638"/><path d="M40 0 L80 0 L60 30 Z" fill="#F6EEDD"/>
    <path d="M80 0 L120 0 L100 30 Z" fill="#D6A24A"/><path d="M120 0 L160 0 L140 30 Z" fill="#A32638"/>
    <path d="M160 0 L200 0 L180 30 Z" fill="#F6EEDD"/><path d="M200 0 L240 0 L220 30 Z" fill="#D6A24A"/>
    <path d="M240 0 L280 0 L260 30 Z" fill="#A32638"/><path d="M280 0 L320 0 L300 30 Z" fill="#F6EEDD"/>
    <path d="M320 0 L360 0 L340 30 Z" fill="#D6A24A"/><path d="M360 0 L400 0 L380 30 Z" fill="#A32638"/>
    <path d="M400 0 L440 0 L420 30 Z" fill="#F6EEDD"/><path d="M440 0 L480 0 L460 30 Z" fill="#D6A24A"/>
    <path d="M480 0 L520 0 L500 30 Z" fill="#A32638"/><path d="M520 0 L560 0 L540 30 Z" fill="#F6EEDD"/>
    <path d="M560 0 L600 0 L580 30 Z" fill="#D6A24A"/><path d="M600 0 L640 0 L620 30 Z" fill="#A32638"/>
    <path d="M640 0 L680 0 L660 30 Z" fill="#F6EEDD"/><path d="M680 0 L720 0 L700 30 Z" fill="#D6A24A"/>
    <path d="M720 0 L760 0 L740 30 Z" fill="#A32638"/><path d="M760 0 L800 0 L780 30 Z" fill="#F6EEDD"/>
  </svg>

  <div class="header-inner">
    <p class="eyebrow-date">18 de septiembre</p>
    <h1>Encuentra tu fonda</h1>
    <p class="lead">Terrenos, empanadas y cueca cerca tuyo. Comparte tu ubicación y te mostramos las fondas más cercanas.</p>

    <div class="actions-row">
      <button class="btn btn-primary" id="locate-btn">📍 Buscar fondas cerca de mí</button>
      <button class="btn btn-outline" id="show-all-btn">📋 Ver todas las fondas</button>
      <a href="https://t.me/CercaDeMiBot" target="_blank" rel="noopener" class="btn btn-telegram">💬 Ábrelo en Telegram</a>
    </div>

    <div class="radius-control" id="radius-control" style="display:none;">
      <div class="radius-control-label">
        <span>Radio de búsqueda</span>
        <strong id="radius-value">50 km</strong>
      </div>
      <input type="range" id="radius-slider" min="5" max="300" step="5" value="50">
    </div>
  </div>
</header>

<main>
  <div id="map"></div>

  <div class="results-heading">
    <h2>Fondas</h2>
    <span class="results-count" id="results-count"></span>
  </div>

  <div id="results">
    <div class="status-msg">Toca un botón de arriba para ver fondas.</div>
  </div>
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
  const map = L.map('map', { scrollWheelZoom: true }).setView([-33.4489, -70.6693], 6);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  let markers = [];
  let userMarker = null;
  let currentLat = null;
  let currentLng = null;

  const btn = document.getElementById('locate-btn');
  const allBtn = document.getElementById('show-all-btn');
  const resultsEl = document.getElementById('results');
  const countEl = document.getElementById('results-count');
  const radiusControl = document.getElementById('radius-control');
  const radiusSlider = document.getElementById('radius-slider');
  const radiusValue = document.getElementById('radius-value');

  function fondaIcon(){
    return L.divIcon({
      className: 'fonda-pin',
      html: `
        <svg width="30" height="38" viewBox="0 0 30 38" xmlns="http://www.w3.org/2000/svg">
          <path d="M15 0C6.7 0 0 6.7 0 15c0 10.5 15 23 15 23s15-12.5 15-23C30 6.7 23.3 0 15 0z" fill="#A32638"/>
          <path d="M15 3.5C9 3.5 4 8.5 4 14.5c0 8 11 18 11 18s11-10 11-18c0-6-5-11-11-11z" fill="#F6EEDD"/>
          <circle cx="15" cy="14.5" r="6.5" fill="#163A5F"/>
          <path d="M15 9.5l1.1 3.4h3.6l-2.9 2.1 1.1 3.4-2.9-2.1-2.9 2.1 1.1-3.4-2.9-2.1h3.6z" fill="#D6A24A"/>
        </svg>
      `,
      iconSize: [30, 38],
      iconAnchor: [15, 38],
      popupAnchor: [0, -34],
    });
  }

  function popupContent(f){
    const mapsLink = (f.lat && f.lng) ? `https://www.google.com/maps?q=${f.lat},${f.lng}` : null;
    return `
      <div class="fonda-popup">
        <h4>${f.name}</h4>
        ${f.address ? `<p>📍 ${f.address}</p>` : ''}
        ${f.dates ? `<p>🗓️ ${f.dates}</p>` : ''}
        ${f.artists ? `<p>🎤 ${f.artists}</p>` : ''}
        ${f.prices ? `<p>💰 ${f.prices}</p>` : ''}
        ${mapsLink ? `<a class="popup-maps-link" href="${mapsLink}" target="_blank" rel="noopener">Abrir en Google Maps →</a>` : ''}
      </div>
    `;
  }

  function clearMarkers(){
    markers.forEach(m => map.removeLayer(m));
    markers = [];
  }

  function ticketCard(f, showDistance){
    const mapsLink = (f.lat && f.lng) ? `https://www.google.com/maps?q=${f.lat},${f.lng}` : null;
    const km = showDistance ? (f.distance_m / 1000).toFixed(1) : null;

    return `
      <div class="ticket">
        <div class="ticket-stub"></div>
        <div class="ticket-body">
          <h3>${f.name}</h3>
          ${f.address ? `<p>📍 ${f.address}</p>` : ''}
          ${f.dates ? `<p>🗓️ ${f.dates}</p>` : ''}
          ${f.artists ? `<p>🎤 ${f.artists}</p>` : ''}
          ${f.prices ? `<p>💰 ${f.prices}</p>` : ''}
          <div class="ticket-meta">
            ${km !== null ? `<span class="distance-badge">${km} km</span>` : ''}
            ${mapsLink ? `<a href="${mapsLink}" target="_blank" rel="noopener">Ver en el mapa →</a>` : ''}
          </div>
        </div>
      </div>
    `;
  }

  function renderList(fondas, { showDistance, emptyMsg }){
    clearMarkers();

    if (fondas.length === 0){
      resultsEl.innerHTML = `
        <div class="status-msg">
          ${emptyMsg}<br>
          <button class="btn btn-primary" onclick="document.getElementById('show-all-btn').click()">📋 Ver todas las fondas</button>
        </div>`;
      countEl.textContent = '';
      return;
    }

    countEl.textContent = fondas.length + (fondas.length === 1 ? ' fonda' : ' fondas');

    let html = '';
    fondas.forEach(f => {
      if (f.lat && f.lng){
        const marker = L.marker([f.lat, f.lng], { icon: fondaIcon() }).addTo(map);
        marker.bindPopup(popupContent(f));
        markers.push(marker);
      }
      html += ticketCard(f, showDistance);
    });
    resultsEl.innerHTML = html;

    if (markers.length > 0){
      const group = L.featureGroup(markers);
      map.fitBounds(group.getBounds().pad(0.25));
    }
  }

  function searchNearby(){
    if (currentLat === null) return;
    const radiusKm = radiusSlider.value;
    resultsEl.innerHTML = '<div class="status-msg">Buscando fondas cerca tuyo…</div>';

    fetch(`/api/fondas/nearby?lat=${currentLat}&lng=${currentLng}&radius=${radiusKm}`)
      .then(res => res.json())
      .then(fondas => renderList(fondas, {
        showDistance: true,
        emptyMsg: `No encontramos fondas dentro de ${radiusKm} km.`
      }))
      .catch(() => {
        resultsEl.innerHTML = '<div class="status-msg">Algo falló buscando fondas. Intenta de nuevo.</div>';
      });
  }

  btn.addEventListener('click', () => {
    if (!navigator.geolocation){
      resultsEl.innerHTML = '<div class="status-msg">Tu navegador no permite compartir ubicación.</div>';
      return;
    }
    btn.disabled = true;
    btn.textContent = 'Obteniendo tu ubicación…';

    navigator.geolocation.getCurrentPosition(
      pos => {
        currentLat = pos.coords.latitude;
        currentLng = pos.coords.longitude;

        map.setView([currentLat, currentLng], 12);
        if (userMarker) map.removeLayer(userMarker);
        userMarker = L.circleMarker([currentLat, currentLng], {
          radius: 7, color: '#163A5F', fillColor: '#163A5F', fillOpacity: 1, weight: 2
        }).addTo(map);

        radiusControl.style.display = 'block';
        searchNearby();

        btn.disabled = false;
        btn.textContent = '📍 Buscar fondas cerca de mí';
      },
      () => {
        resultsEl.innerHTML = '<div class="status-msg">No pudimos acceder a tu ubicación. Actívala en tu navegador e intenta de nuevo.</div>';
        btn.disabled = false;
        btn.textContent = '📍 Buscar fondas cerca de mí';
      }
    );
  });

  allBtn.addEventListener('click', () => {
    radiusControl.style.display = 'none';
    resultsEl.innerHTML = '<div class="status-msg">Cargando todas las fondas…</div>';

    fetch('/api/fondas/all')
      .then(res => res.json())
      .then(fondas => renderList(fondas, {
        showDistance: false,
        emptyMsg: 'Aún no hay fondas cargadas.'
      }))
      .catch(() => {
        resultsEl.innerHTML = '<div class="status-msg">No pudimos cargar las fondas. Intenta de nuevo.</div>';
      });
  });

  radiusSlider.addEventListener('input', () => {
    radiusValue.textContent = radiusSlider.value + ' km';
  });
  radiusSlider.addEventListener('change', () => {
    searchNearby();
  });
</script>

</body>
</html>