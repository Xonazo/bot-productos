<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Publicar producto</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 40px auto; padding: 0 20px; }
        label { display: block; margin-top: 15px; font-weight: bold; }
        input, textarea { width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box; }
        #map { height: 350px; margin-top: 10px; border-radius: 8px; }
        button { margin-top: 20px; padding: 12px 20px; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; }
        .success { background: #dcfce7; color: #166534; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
        .hint { font-size: 13px; color: #666; margin-top: 5px; }
    </style>
</head>
<body>

    <h1>Publicar producto</h1>

    @if (session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="success" style="background:#fee2e2; color:#991b1b;">
            <ul style="margin:0; padding-left:20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('products.store') }}">
        @csrf

        <label for="name">Nombre del producto</label>
        <input type="text" name="name" id="name" required value="{{ old('name') }}">

        <label for="description">Descripción</label>
        <textarea name="description" id="description" rows="3">{{ old('description') }}</textarea>

        <label for="price">Precio (opcional)</label>
        <input type="number" name="price" id="price" step="0.01" value="{{ old('price') }}">

        <label>Ubicación (haz click en el mapa)</label>
        <div class="hint">Click en el punto donde está tu producto</div>
        <div id="map"></div>

        <input type="hidden" name="lat" id="lat" required>
        <input type="hidden" name="lng" id="lng" required>

        <button type="submit">Publicar producto</button>
    </form>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const map = L.map('map').setView([-33.4489, -70.6693], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        let marker = null;

        map.on('click', function (e) {
            const { lat, lng } = e.latlng;

            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = lng;

            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng).addTo(map);
            }
        });

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                map.setView([position.coords.latitude, position.coords.longitude], 14);
            });
        }
    </script>

</body>
</html> 