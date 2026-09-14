/* ============================================================
   main.js — Lógica del frontend público
   ============================================================ */

const API_BASE = 'http://localhost/messapi/api';

// --- Referencias DOM ---
const restaurantsEl = document.getElementById('restaurants');
const loadingEl     = document.getElementById('loading');
const errorEl       = document.getElementById('error');
const refreshBtn    = document.getElementById('refreshBtn');
const lastUpdateEl  = document.getElementById('lastUpdate');

const modal       = document.getElementById('modal');
const modalTitle  = document.getElementById('modalTitle');
const modalBody   = document.getElementById('modalBody');
const modalClose  = document.getElementById('modalClose');

// --- Utilidades ---
const showLoading = (v) => loadingEl.classList.toggle('hidden', !v);
const showError   = (m) => { errorEl.textContent = m; errorEl.classList.remove('hidden'); };
const hideError   = ()  => errorEl.classList.add('hidden');

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function availabilityClass(available, total) {
    if (total === 0) return 'low';
    const ratio = available / total;
    if (ratio >= 0.6) return 'high';
    if (ratio >= 0.3) return 'medium';
    return 'low';
}

// --- Cargar restaurantes ---
async function loadRestaurants() {
    showLoading(true);
    hideError();
    restaurantsEl.innerHTML = '';

    try {
        const res = await fetch(`${API_BASE}/public/restaurants`);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);

        // ✅ Tu API devuelve el array pelado directamente
        const data = await res.json();

        if (!Array.isArray(data) || data.length === 0) {
            restaurantsEl.innerHTML = '<p class="loading">No hay restaurantes registrados.</p>';
            return;
        }

        renderRestaurants(data);
        lastUpdateEl.textContent =
            'Última actualización: ' + new Date().toLocaleTimeString();
    } catch (err) {
        showError('No se pudieron cargar los restaurantes: ' + err.message);
    } finally {
        showLoading(false);
    }
}

// --- Render de tarjetas ---
function renderRestaurants(restaurants) {
    restaurantsEl.innerHTML = restaurants.map(r => {
        const total = r.total_tables || 0;
        // 👇 Calcular available_tables contando las mesas disponibles (por si el backend no lo manda)
        const available = (typeof r.available_tables === 'number')
            ? r.available_tables
            : (r.tables || []).filter(t => t.status === 'disponible').length;

        const cls = availabilityClass(available, total);

        return `
            <article class="card">
                <h2>${escapeHtml(r.name)}</h2>
                <p class="address">📍 ${escapeHtml(r.address)}</p>
                ${r.phone ? `<p class="phone">📞 ${escapeHtml(r.phone)}</p>` : ''}
                ${r.description ? `<p class="description">${escapeHtml(r.description)}</p>` : ''}
                <div class="availability">
                    <span class="badge ${cls}">
                        ${available} / ${total} disponibles
                    </span>
                    <button class="btn-view" data-id="${r.id}" data-name="${escapeHtml(r.name)}">
                        Ver mesas
                    </button>
                </div>
            </article>
        `;
    }).join('');

    document.querySelectorAll('.btn-view').forEach(btn => {
        btn.addEventListener('click', () =>
            openModal(btn.dataset.id, btn.dataset.name)
        );
    });
}

// --- Modal de mesas ---
async function openModal(restaurantId, restaurantName) {
    modalTitle.textContent = `Mesas — ${restaurantName}`;
    modalBody.innerHTML = '<p class="loading">Cargando mesas...</p>';
    modal.classList.remove('hidden');

    try {
        const res = await fetch(
            `${API_BASE}/public/restaurants/${restaurantId}/tables`
        );
        if (!res.ok) throw new Error(`HTTP ${res.status}`);

        // ✅ Tu API devuelve el array pelado directamente
        const tables = await res.json();

        if (!Array.isArray(tables) || tables.length === 0) {
            modalBody.innerHTML =
                '<p class="loading">Este restaurante no tiene mesas cargadas.</p>';
            return;
        }

        modalBody.innerHTML = `
            <ul class="tables-list">
                ${tables.map(t => {
                    const key = (t.status || t.status_name || '').toLowerCase();
                    return `
                        <li class="${key}">
                            <span class="table-info">
                                <strong>${escapeHtml(t.details || t.detail || 'Mesa ' + (t.table_number || t.id))}</strong>
                                — ${t.chairs} sillas
                            </span>
                            <span class="table-status ${key}">
                                ${escapeHtml(t.status_name || t.status)}
                            </span>
                        </li>
                    `;
                }).join('')}
            </ul>
        `;
    } catch (err) {
        modalBody.innerHTML =
            `<p class="error">Error al cargar mesas: ${err.message}</p>`;
    }
}

function closeModal() {
    modal.classList.add('hidden');
}

// --- Eventos ---
refreshBtn.addEventListener('click', loadRestaurants);
modalClose.addEventListener('click', closeModal);
modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
});
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
});

// --- Init ---
loadRestaurants();
setInterval(loadRestaurants, 30000); // auto-refresh cada 30s