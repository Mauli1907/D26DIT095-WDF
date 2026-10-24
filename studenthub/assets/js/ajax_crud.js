/**
 * StudentHub Portal - CHARUSAT FTE
 * Practical 13: PHP JSON API and AJAX/Fetch-Based CRUD without Page Reload
 * Intermediate: AJAX search with debounce
 * Advanced: Centralized error handling and HTTP status code processing
 */

document.addEventListener('DOMContentLoaded', () => {
  initAjaxEventsCRUD();
});

function initAjaxEventsCRUD() {
  const tableBody = document.getElementById('ajaxEventsTableBody');
  const searchInput = document.getElementById('ajaxSearchInput');
  const categoryFilter = document.getElementById('ajaxCategoryFilter');
  const addForm = document.getElementById('ajaxAddEventForm');
  const addModal = document.getElementById('ajaxAddModal');

  if (!tableBody) return;

  // Initial load
  fetchAndRenderEvents();

  // Search with debounce (Practical 13 Intermediate)
  if (searchInput) {
    searchInput.addEventListener('input', debounce(() => {
      fetchAndRenderEvents();
    }, 300));
  }

  if (categoryFilter) {
    categoryFilter.addEventListener('change', () => {
      fetchAndRenderEvents();
    });
  }

  // Handle Add Form submission via AJAX Fetch POST
  if (addForm) {
    addForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const submitBtn = addForm.querySelector('button[type="submit"]');
      const originalText = submitBtn.textContent;
      submitBtn.disabled = true;
      submitBtn.textContent = 'Saving...';

      const payload = {
        title: document.getElementById('ajax_title').value.trim(),
        category: document.getElementById('ajax_category').value,
        event_date: document.getElementById('ajax_date').value,
        event_time: document.getElementById('ajax_time').value || '09:00:00',
        venue: document.getElementById('ajax_venue').value.trim(),
        max_seats: parseInt(document.getElementById('ajax_seats').value, 10) || 100,
        description: document.getElementById('ajax_desc').value.trim(),
        poster_url: 'default_poster.svg'
      };

      try {
        const res = await fetch('../api/events.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (res.ok && data.success) {
          showToast(data.message || 'Event added successfully!', 'success');
          addForm.reset();
          if (addModal) {
            addModal.classList.remove('open');
            document.body.style.overflow = '';
          }
          fetchAndRenderEvents();
        } else {
          showToast(data.error || 'Failed to create event.', 'error');
        }
      } catch (err) {
        showToast('Network error: ' + err.message, 'error');
      } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
      }
    });
  }
}

// Fetch events from PHP REST API endpoint
async function fetchAndRenderEvents() {
  const tableBody = document.getElementById('ajaxEventsTableBody');
  const searchInput = document.getElementById('ajaxSearchInput');
  const categoryFilter = document.getElementById('ajaxCategoryFilter');
  if (!tableBody) return;

  const query = searchInput ? encodeURIComponent(searchInput.value.trim()) : '';
  const category = categoryFilter ? encodeURIComponent(categoryFilter.value) : 'all';

  tableBody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:30px; color:var(--text-muted);">Loading events from API...</td></tr>`;

  try {
    const res = await fetch(`../api/events.php?search=${query}&category=${category}`);
    const result = await res.json();

    if (!res.ok || !result.success) {
      throw new Error(result.error || 'Failed to fetch');
    }

    const events = result.data || [];
    if (events.length === 0) {
      tableBody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:30px; color:var(--text-muted);">No matching events found.</td></tr>`;
      return;
    }

    tableBody.innerHTML = events.map((ev) => `
      <tr id="event-row-${ev.id}">
        <td><strong>#${ev.id}</strong></td>
        <td>
          <div style="font-weight:700;">${escapeHtml(ev.title)}</div>
          <small style="color:var(--text-muted);">${escapeHtml(ev.venue)}</small>
        </td>
        <td><span class="badge badge-primary">${escapeHtml(ev.category)}</span></td>
        <td>${ev.event_date}</td>
        <td>${ev.registered_count} / ${ev.max_seats}</td>
        <td><span class="badge badge-success">${escapeHtml(ev.status)}</span></td>
        <td>
          <div style="display:flex; gap:6px;">
            <button class="btn btn-sm btn-outline" onclick="openEditEventModal(${ev.id})">Edit</button>
            <button class="btn btn-sm btn-danger" onclick="deleteEventAjax(${ev.id}, '${escapeHtml(ev.title)}')">Delete</button>
          </div>
        </td>
      </tr>
    `).join('');

  } catch (err) {
    tableBody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:30px; color:var(--danger);">Error: ${err.message}</td></tr>`;
  }
}

// Asynchronously delete event
async function deleteEventAjax(id, title) {
  if (!confirm(`Are you sure you want to delete event "${title}" (ID: ${id})?\nThis operation uses Fetch API without page reload.`)) {
    return;
  }

  try {
    const res = await fetch('../api/events.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id })
    });
    const result = await res.json();

    if (res.ok && result.success) {
      showToast(`Event #${id} deleted successfully!`, 'success');
      const row = document.getElementById(`event-row-${id}`);
      if (row) {
        row.style.transition = 'opacity 0.3s ease';
        row.style.opacity = '0';
        setTimeout(() => row.remove(), 300);
      } else {
        fetchAndRenderEvents();
      }
    } else {
      showToast(result.error || 'Failed to delete event.', 'error');
    }
  } catch (err) {
    showToast('Network error during delete: ' + err.message, 'error');
  }
}

// Helpers
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

function escapeHtml(text) {
  if (!text) return '';
  return text.replace(/[&<>"']/g, (m) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]));
}
