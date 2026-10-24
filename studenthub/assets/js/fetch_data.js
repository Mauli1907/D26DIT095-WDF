/**
 * StudentHub Portal - CHARUSAT FTE
 * Practical 6: Rendering External JSON Data using Fetch API, Search and Filter
 * Intermediate: Dependent dropdowns (Department -> Course)
 * Advanced: Cache the latest JSON response in localStorage for offline-like display
 */

let allEventsData = [];
let filteredEventsData = [];
let currentEventPage = 1;
const eventsPerPage = 6;

document.addEventListener('DOMContentLoaded', () => {
  const eventsContainer = document.getElementById('externalEventsContainer');
  if (eventsContainer) {
    loadEventsData();
    initEventFilters();
  }

  // Practical 6 Intermediate Extension: Dependent dropdowns
  initDependentDropdowns();
});

/* -------------------------------------------------------------------------
   1. Fetch JSON Data with LocalStorage Caching (Practical 6 Advanced)
------------------------------------------------------------------------- */
async function loadEventsData() {
  const container = document.getElementById('externalEventsContainer');
  const cacheKey = 'studenthub_cached_events';
  const cacheExpiryKey = 'studenthub_cached_events_time';
  const CACHE_TTL_MS = 10 * 60 * 1000; // 10 minutes

  if (!container) return;

  // Show loading spinner
  container.innerHTML = `
    <div style="grid-column: 1 / -1; text-align: center; padding: 50px 0;">
      <div style="display:inline-block; width:40px; height:40px; border:4px solid var(--border); border-top-color:var(--primary); border-radius:50%; animation: spin 0.8s linear infinite;"></div>
      <p style="margin-top:14px; color:var(--text-muted); font-weight:600;">Fetching campus events from external JSON...</p>
    </div>
    <style>@keyframes spin { 100% { transform: rotate(360deg); } }</style>
  `;

  // Check LocalStorage cache first
  const cachedData = localStorage.getItem(cacheKey);
  const cachedTime = localStorage.getItem(cacheExpiryKey);

  if (cachedData && cachedTime && (Date.now() - parseInt(cachedTime, 10) < CACHE_TTL_MS)) {
    try {
      allEventsData = JSON.parse(cachedData);
      filteredEventsData = [...allEventsData];
      renderEventsPage(1);
      console.log('Loaded events from localStorage cache (Practical 6 Advanced).');
      return;
    } catch (e) {
      console.warn('Cache parsing failed, fetching network copy.');
    }
  }

  try {
    const response = await fetch('../data/events.json');
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    const data = await response.json();
    allEventsData = data;
    filteredEventsData = [...allEventsData];

    // Store in localStorage cache
    try {
      localStorage.setItem(cacheKey, JSON.stringify(data));
      localStorage.setItem(cacheExpiryKey, Date.now().toString());
    } catch (e) {
      console.warn('Failed to cache in localStorage');
    }

    renderEventsPage(1);
  } catch (err) {
    console.error('Fetch error:', err);
    container.innerHTML = `
      <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: #fee2e2; border-radius: 12px; color: #991b1b;">
        <h3 style="margin-bottom:8px;">Failed to load events data</h3>
        <p>${err.message}</p>
        <button class="btn btn-primary" onclick="loadEventsData()" style="margin-top:14px;">Retry Fetch</button>
      </div>
    `;
  }
}

/* -------------------------------------------------------------------------
   2. Search, Filter, Sort & Pagination
------------------------------------------------------------------------- */
function initEventFilters() {
  const searchInput = document.getElementById('eventSearchInput');
  const categoryFilter = document.getElementById('eventCategoryFilter');
  const sortSelect = document.getElementById('eventSortSelect');

  function applyFilters() {
    const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
    const category = categoryFilter ? categoryFilter.value : 'all';
    const sortBy = sortSelect ? sortSelect.value : 'date_asc';

    filteredEventsData = allEventsData.filter((item) => {
      const matchQuery =
        item.title.toLowerCase().includes(query) ||
        item.description.toLowerCase().includes(query) ||
        item.venue.toLowerCase().includes(query);
      const matchCategory = category === 'all' || item.category.toLowerCase() === category.toLowerCase();
      return matchQuery && matchCategory;
    });

    // Sort
    if (sortBy === 'date_asc') {
      filteredEventsData.sort((a, b) => new Date(a.date) - new Date(b.date));
    } else if (sortBy === 'date_desc') {
      filteredEventsData.sort((a, b) => new Date(b.date) - new Date(a.date));
    } else if (sortBy === 'title_asc') {
      filteredEventsData.sort((a, b) => a.title.localeCompare(b.title));
    } else if (sortBy === 'title_desc') {
      filteredEventsData.sort((a, b) => b.title.localeCompare(a.title));
    }

    renderEventsPage(1);
  }

  if (searchInput) searchInput.addEventListener('input', debounce(applyFilters, 250));
  if (categoryFilter) categoryFilter.addEventListener('change', applyFilters);
  if (sortSelect) sortSelect.addEventListener('change', applyFilters);
}

function renderEventsPage(pageNumber) {
  currentEventPage = pageNumber;
  const container = document.getElementById('externalEventsContainer');
  const paginationContainer = document.getElementById('eventsPagination');
  if (!container) return;

  if (filteredEventsData.length === 0) {
    container.innerHTML = `
      <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: var(--bg-card); border-radius: 12px; border: 1px dashed var(--border);">
        <p style="font-size:2rem; margin-bottom:8px;">🔍</p>
        <h3 style="margin-bottom:6px;">No events found</h3>
        <p style="color:var(--text-muted);">Try adjusting your search criteria or category filter.</p>
      </div>
    `;
    if (paginationContainer) paginationContainer.innerHTML = '';
    return;
  }

  const totalPages = Math.ceil(filteredEventsData.length / eventsPerPage);
  const startIndex = (pageNumber - 1) * eventsPerPage;
  const endIndex = Math.min(startIndex + eventsPerPage, filteredEventsData.length);
  const pageItems = filteredEventsData.slice(startIndex, endIndex);

  // Render cards matching wireframe
  container.innerHTML = pageItems
    .map(
      (ev) => `
    <article class="event-card">
      <div class="event-card-poster">
        <img src="../assets/images/${ev.poster || 'default_poster.svg'}" alt="${escapeHtml(ev.title)}" loading="lazy">
        <span class="event-badge">${escapeHtml(ev.category)}</span>
      </div>
      <div class="event-card-body">
        <div class="event-meta">
          <span>📅 ${ev.date}</span>
          <span>⏰ ${ev.time}</span>
          <span>📍 ${escapeHtml(ev.venue)}</span>
        </div>
        <h3 class="event-title">${escapeHtml(ev.title)}</h3>
        <p class="event-desc">${escapeHtml(ev.description)}</p>
        <div class="event-card-footer">
          <span class="seats-info">${ev.seats - ev.registered} seats left</span>
          <a href="event_detail.php?id=${ev.id}" class="btn btn-sm btn-outline">View Details →</a>
        </div>
      </div>
    </article>
  `
    )
    .join('');

  // Render pagination buttons
  if (paginationContainer) {
    if (totalPages <= 1) {
      paginationContainer.innerHTML = '';
      return;
    }
    let pagHtml = '';
    pagHtml += `<button class="pagination-btn" ${currentEventPage === 1 ? 'disabled' : ''} onclick="renderEventsPage(${currentEventPage - 1})">« Prev</button>`;
    for (let i = 1; i <= totalPages; i++) {
      pagHtml += `<button class="pagination-btn ${i === currentEventPage ? 'active' : ''}" onclick="renderEventsPage(${i})">${i}</button>`;
    }
    pagHtml += `<button class="pagination-btn" ${currentEventPage === totalPages ? 'disabled' : ''} onclick="renderEventsPage(${currentEventPage + 1})">Next »</button>`;
    paginationContainer.innerHTML = pagHtml;
  }
}

/* -------------------------------------------------------------------------
   3. Dependent Dropdowns (Practical 6 Intermediate Extension)
------------------------------------------------------------------------- */
function initDependentDropdowns() {
  const departmentSelect = document.getElementById('filterDepartment');
  const courseSelect = document.getElementById('filterCourse');
  if (!departmentSelect || !courseSelect) return;

  const coursesByDept = {
    engineering: [
      { id: 'it', name: 'B.Tech Information Technology' },
      { id: 'ce', name: 'B.Tech Computer Engineering' },
      { id: 'cse', name: 'B.Tech Computer Science & Eng.' },
      { id: 'ec', name: 'B.Tech Electronics & Comm.' }
    ],
    pharmacy: [
      { id: 'bpharm', name: 'Bachelor of Pharmacy' },
      { id: 'mpharm', name: 'Master of Pharmacy' }
    ],
    management: [
      { id: 'bba', name: 'BBA - Business Administration' },
      { id: 'mba', name: 'MBA - Technology Management' }
    ],
    computer_apps: [
      { id: 'bca', name: 'BCA - Bachelor of Computer Apps' },
      { id: 'mca', name: 'MCA - Master of Computer Apps' }
    ]
  };

  departmentSelect.addEventListener('change', () => {
    const selectedDept = departmentSelect.value;
    courseSelect.innerHTML = '<option value="">-- Select Course --</option>';

    if (coursesByDept[selectedDept]) {
      coursesByDept[selectedDept].forEach((c) => {
        const opt = document.createElement('option');
        opt.value = c.id;
        opt.textContent = c.name;
        courseSelect.appendChild(opt);
      });
      courseSelect.disabled = false;
    } else {
      courseSelect.disabled = true;
    }
  });
}

// Helpers
function debounce(fn, delay) {
  let timer;
  return function (...args) {
    clearTimeout(timer);
    timer = setTimeout(() => fn.apply(this, args), delay);
  };
}

function escapeHtml(str) {
  if (!str) return '';
  return str.replace(/[&<>"']/g, (m) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]));
}
