<?php include 'includes/header.php'; ?>

<style>
/* Additional Styles for Search Results */
.search-header {
    background-color: var(--primary-color);
    color: white;
    padding: 100px 20px 40px;
    text-align: center;
}

.search-header h1 {
    margin-bottom: 20px;
}

.results-container {
    max-width: 1200px;
    margin: 40px auto;
    display: flex;
    gap: 30px;
    padding: 0 20px;
}

.filters-sidebar {
    flex: 0 0 250px;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    height: fit-content;
}

.filter-group {
    margin-bottom: 20px;
}

.filter-group h3 {
    font-size: 16px;
    margin-bottom: 10px;
    color: var(--primary-color);
    border-bottom: 2px solid var(--accent-color);
    padding-bottom: 5px;
    display: inline-block;
}

.filter-option {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
    font-size: 14px;
}

.buses-list {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.bus-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    transition: transform 0.3s, box-shadow 0.3s;
    border-left: 5px solid var(--secondary-color);
}

.bus-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.bus-info h3 {
    color: var(--primary-color);
    margin-bottom: 5px;
    font-size: 1.2rem;
}

.bus-info p {
    color: #666;
    font-size: 14px;
    margin-bottom: 5px;
}

.bus-times {
    display: flex;
    align-items: center;
    gap: 15px;
    margin: 15px 0;
}

.time-block strong {
    font-size: 1.1rem;
    color: #333;
}

.time-duration {
    font-size: 12px;
    color: #999;
    padding: 5px 10px;
    background: #f4f6f9;
    border-radius: 20px;
}

.bus-price {
    text-align: right;
}

.bus-price h2 {
    color: var(--secondary-color);
    font-size: 1.8rem;
    margin-bottom: 5px;
}

.bus-price p {
    color: #666;
    font-size: 12px;
    margin-bottom: 15px;
}

.seats-badge {
    display: inline-block;
    background: #e8f5e9;
    color: #2e7d32;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}

.seats-badge.low {
    background: #ffebee;
    color: #c62828;
}

@media (max-width: 768px) {
    .results-container { flex-direction: column; }
    .filters-sidebar { flex: none; width: 100%; }
    .bus-card { flex-direction: column; align-items: flex-start; gap: 20px; }
    .bus-price { text-align: left; width: 100%; display: flex; justify-content: space-between; align-items: center; }
}
</style>

<div class="search-header">
    <h1>Search Results</h1>
    <p>From: <strong id="lblSource"><?= htmlspecialchars($_GET['source'] ?? '') ?></strong> &nbsp;&nbsp;|&nbsp;&nbsp; 
       To: <strong id="lblDest"><?= htmlspecialchars($_GET['destination'] ?? '') ?></strong> &nbsp;&nbsp;|&nbsp;&nbsp; 
       Date: <strong id="lblDate"><?= htmlspecialchars($_GET['date'] ?? '') ?></strong></p>
</div>

<div class="results-container">
    <aside class="filters-sidebar">
        <div class="filter-group">
            <h3>Bus Type</h3>
            <label class="filter-option"><input type="checkbox" value="AC" class="bus-filter"> AC</label>
            <label class="filter-option"><input type="checkbox" value="Non-AC" class="bus-filter"> Non-AC</label>
            <label class="filter-option"><input type="checkbox" value="Sleeper" class="bus-filter"> Sleeper</label>
        </div>
        <div class="filter-group">
            <h3>Departure Time</h3>
            <label class="filter-option"><input type="checkbox" value="Morning" class="time-filter"> Morning (6AM - 12PM)</label>
            <label class="filter-option"><input type="checkbox" value="Afternoon" class="time-filter"> Afternoon (12PM - 6PM)</label>
            <label class="filter-option"><input type="checkbox" value="Night" class="time-filter"> Night (6PM - 6AM)</label>
        </div>
    </aside>

    <main class="buses-list" id="busesList">
        <!-- Results will be loaded here via AJAX -->
        <div style="text-align:center; padding: 40px;">
            <i class="fa-solid fa-spinner fa-spin fa-2x" style="color:var(--primary-color);"></i>
            <p style="margin-top:10px;">Searching for buses...</p>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Read URL params
    const urlParams = new URLSearchParams(window.location.search);
    const source = urlParams.get('source');
    const destination = urlParams.get('destination');
    const travelDate = urlParams.get('date');
    
    // Filters logic ready for implementation
    const typeFilters = document.querySelectorAll('.bus-filter');
    const timeFilters = document.querySelectorAll('.time-filter');

    const fetchResults = async () => {
        const types = Array.from(typeFilters).filter(c => c.checked).map(c => c.value);
        const times = Array.from(timeFilters).filter(c => c.checked).map(c => c.value);

        const listDiv = document.getElementById('busesList');
        listDiv.innerHTML = '<div style="text-align:center; padding:40px;"><i class="fa-solid fa-spinner fa-spin fa-2x" style="color:var(--primary-color);"></i><p>Loading...</p></div>';

        try {
            const res = await fetch('api/search.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ source, destination, date: travelDate, types, times })
            });
            const data = await res.json();
            
            if (data.success) {
                renderBuses(data.data);
            } else {
                listDiv.innerHTML = `<div style="text-align:center; padding: 40px; color: #c62828;">${data.message}</div>`;
            }
        } catch (e) {
            listDiv.innerHTML = `<div style="text-align:center; padding: 40px; color: #c62828;">An error occurred while fetching schedules.</div>`;
        }
    };

    const renderBuses = (buses) => {
        const listDiv = document.getElementById('busesList');
        if (buses.length === 0) {
            listDiv.innerHTML = `<div style="text-align:center; padding: 40px;"><h3>No buses found for this route and date.</h3></div>`;
            return;
        }

        let html = '';
        buses.forEach(bus => {
            const seatsClass = bus.available_seats < 10 ? 'low' : '';
            html += `
            <div class="bus-card">
                <div class="bus-info">
                    <h3>${bus.bus_name} <span style="font-size:12px; color:#999;font-weight:normal;">(${bus.bus_type})</span></h3>
                    <p><i class="fa-solid fa-snowflake" style="color:#0288d1;"></i> ${bus.amenities || 'Standard Amenities'}</p>
                    
                    <div class="bus-times">
                        <div class="time-block">
                            <strong>${bus.dep_time}</strong><br>
                            <span style="font-size:12px;">${bus.source_city}</span>
                        </div>
                        <div class="time-duration">
                            <i class="fa-solid fa-arrow-right"></i>
                        </div>
                        <div class="time-block">
                            <strong>${bus.arr_time}</strong><br>
                            <span style="font-size:12px;">${bus.destination_city}</span>
                        </div>
                    </div>
                    <span class="seats-badge ${seatsClass}">${bus.available_seats} seats left</span>
                </div>
                
                <div class="bus-price">
                    <h2>₹${bus.price}</h2>
                    <p>per passenger</p>
                    <a href="seat_selection.php?schedule_id=${bus.schedule_id}" class="btn-search" style="text-decoration:none; display:inline-block; font-size:14px; padding:10px 20px;">Select Seats</a>
                </div>
            </div>`;
        });
        
        listDiv.innerHTML = html;
    };

    [...typeFilters, ...timeFilters].forEach(f => f.addEventListener('change', fetchResults));

    // Initial fetch
    fetchResults();
});
</script>

<?php include 'includes/footer.php'; ?>
