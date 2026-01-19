<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MILFIT MONITORING</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #000;
            margin-bottom: 30px;
            font-size: 2.5em;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            gap: 10px;
        }

        .cubicle-card {
            border-radius: 15px;
            padding: 3em 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            background: #e5e7eb;
        }

        .cubicle-card.vacant {
            background: #038e60;
            color: white;
        }

        .cubicle-card.occupied {
            background: #c61111;
            color: white;
        }

        .cubicle-header {
            text-align: center;
            margin-bottom: 15px;
        }

        .cubicle-number {
            font-size: 0.9rem;
            font-weight: bold;
        }

        .cubicle-sub {
            font-size: 2.5em;
            font-weight: bold;
        }

        .last-update {
            text-align: center;
            margin-top: 10px;
            font-size: 0.8em;
        }

        .stats {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-around;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 3em;
            font-weight: bold;
        }

        .stat-label{
            font-size: 1.2em;
            font-weight: bold;
        }

        .occupied-stat { color: #c61111; }
        .vacant-stat { color: #038e60; }
    </style>
</head>

<body>

<h1>MILFIT CUBICLE OCCUPANCY DASHBOARD</h1>

<div class="stats">
    <div class="stat-item">
        <div class="stat-number occupied-stat" id="occupied-count">0</div>
        <div class="stat-label">Occupied</div>
    </div>
    <div class="stat-item">
        <div class="stat-number vacant-stat" id="vacant-count">20</div>
        <div class="stat-label">Vacant</div>
    </div>
    <div class="stat-item">
        <div class="stat-number" id="occupancy-rate">0%</div>
        <div class="stat-label">Occupancy Rate</div>
    </div>
</div>

<div class="grid" id="cubicle-grid">
@for ($i = 1; $i <= 20; $i++)
    @php
        if ($i <= 10) {
            $title = 'Push Up ' . $i;
            $sub = 'P-' . $i;
        } else {
            $title = 'Sit Ups ' . ($i - 10);
            $sub = 'S-' . ($i - 10);
        }
    @endphp

    <div class="cubicle-card vacant" id="card-{{ $i }}">
        <div class="cubicle-header">
            <div class="cubicle-number">{{ $title }}</div>
            <div class="cubicle-sub">{{ $sub }}</div>
        </div>
        <div class="last-update" id="time-{{ $i }}"></div>
    </div>
@endfor
</div>

<script>
// Update all cubicles from /api/all-status (periodic sync)
function updateAllStatus() {
    fetch('/api/all-status')
        .then(res => res.json())
        .then(data => {
            let occupied = 0;
            let vacant = 0;

            for (let i = 1; i <= 20; i++) {
                const status = data.cubicles[i] || 'vacant';
                const card = document.getElementById(`card-${i}`);
                const timeEl = document.getElementById(`time-${i}`);

                // Only update if status changed to reduce flicker
                if(!card.classList.contains(status)){
                    card.classList.remove('occupied','vacant');
                    card.classList.add(status);
                    timeEl.textContent = `Updated: ${new Date().toLocaleTimeString()}`;
                }

                if(status === 'occupied') occupied++;
                else vacant++;
            }

            document.getElementById('occupied-count').textContent = occupied;
            document.getElementById('vacant-count').textContent = vacant;
            document.getElementById('occupancy-rate').textContent =
                Math.round((occupied / 20) * 100) + '%';
        })
        .catch(console.error);
}

// Update a single cubicle after POST
function updateCubicle(cubicleId, status) {
    const card = document.getElementById(`card-${cubicleId}`);
    const timeEl = document.getElementById(`time-${cubicleId}`);

    card.classList.remove('occupied','vacant');
    card.classList.add(status === 'occupied' ? 'occupied' : 'vacant');
    timeEl.textContent = `Updated: ${new Date().toLocaleTimeString()}`;
}

// Send status to server
function sendStatus(cubicleId, status) {
    fetch('/api/update-status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cubicle_id: cubicleId, status })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            // Update only the cubicle that was changed
            updateCubicle(data.cubicle_id, data.status);
        }
    })
    .catch(console.error);
}

// Initial load
updateAllStatus();

// Poll every 2 seconds for eventual consistency
setInterval(updateAllStatus, 2000);
</script>

</body>
</html>