// public/js/app.js — SkillSwap global JS

'use strict';

/* ── Base path (reads from <meta id="appBase">) ───────────── */
function appBase() {
  return document.getElementById('appBase')?.content ?? '';
}

/* ── CSRF helper ──────────────────────────────────────────── */
function csrfToken() {
  return document.getElementById('csrfMeta')?.content ?? '';
}

/* ── Fetch wrapper with CSRF + JSON ──────────────────────── */
async function api(url, data = {}) {
  const form = new FormData();
  form.append('_csrf_token', csrfToken());
  for (const [k, v] of Object.entries(data)) form.append(k, v);

  const res = await fetch(appBase() + url, { method: 'POST', body: form });
  const json = await res.json();
  return json;
}

/* ── Toast ────────────────────────────────────────────────── */
function showToast(msg, type = 'success') {
  const t = document.getElementById('globalToast');
  if (!t) return;
  t.innerHTML = msg;
  t.className = 'toast show' + (type === 'error' ? ' toast-error' : '');
  clearTimeout(t._timer);
  t._timer = setTimeout(() => t.classList.remove('show'), 3500);
}

/* ── Modal helpers ────────────────────────────────────────── */
function openModal(id)  { document.getElementById('modal-' + id)?.classList.add('open'); }
function closeModal(id) { document.getElementById('modal-' + id)?.classList.remove('open'); }

// Close modal on overlay click
document.addEventListener('click', e => {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('open');
  }
});
// Close modal on Escape
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
    closeUserMenu();
  }
});

/* ── User menu dropdown ───────────────────────────────────── */
function toggleUserMenu() {
  document.getElementById('userMenu')?.classList.toggle('open');
}
function closeUserMenu() {
  document.getElementById('userMenu')?.classList.remove('open');
}
document.addEventListener('click', e => {
  if (!e.target.closest('.nav-right')) closeUserMenu();
});

/* ── Request Service modal ────────────────────────────────── */
function openRequestModal(serviceId, title, credits) {
  const el = document.getElementById('modal-request');
  if (!el) return;
  el.querySelector('#reqServiceId').value  = serviceId;
  el.querySelector('#reqModalTitle').textContent  = `Request "${title}"`;
  el.querySelector('#reqModalCredits').textContent = credits + ' credits will be held in escrow';
  openModal('request');
}

async function submitRequest() {
  const serviceId = document.getElementById('reqServiceId').value;
  const message   = document.getElementById('reqMessage').value.trim();
  const btn       = document.getElementById('reqSubmitBtn');

  if (!message) { showToast('Please write a message to the provider.', 'error'); return; }

  btn.disabled = true;
  btn.textContent = 'Sending…';

  const res = await api('/swaps/request', { service_id: serviceId, message });

  btn.disabled = false;
  btn.textContent = 'Send Request';

  if (res.success) {
    closeModal('request');
    showToast('Swap request sent! Credits held in escrow. 🎉');
  } else {
    showToast(res.error || 'Something went wrong.', 'error');
  }
}

/* ── Swap actions (accept / decline / complete) ───────────── */
async function swapAction(swapId, action, btn) {
  if (!confirm(`Are you sure you want to ${action} this swap?`)) return;
  btn.disabled = true;

  const res = await api(`/swaps/${swapId}/${action}`);

  if (res.success) {
    showToast(res.message);
    setTimeout(() => location.reload(), 1200);
  } else {
    showToast(res.error || 'Action failed.', 'error');
    btn.disabled = false;
  }
}

/* ── Review modal ─────────────────────────────────────────── */
function openReviewModal(swapId) {
  document.getElementById('reviewSwapId').value = swapId;
  openModal('review');
}

async function submitReview() {
  const swapId  = document.getElementById('reviewSwapId').value;
  const rating  = document.getElementById('reviewRating').value;
  const comment = document.getElementById('reviewComment').value.trim();
  const btn     = document.getElementById('reviewSubmitBtn');

  if (!comment) { showToast('Please write a review comment.', 'error'); return; }

  btn.disabled = true;
  const res = await api(`/swaps/${swapId}/review`, { rating, comment });
  btn.disabled = false;

  if (res.success) {
    closeModal('review');
    showToast('Review submitted. Thank you! ⭐');
    setTimeout(() => location.reload(), 1200);
  } else {
    showToast(res.error || 'Could not submit review.', 'error');
  }
}

/* ── Service modal (create/edit) ──────────────────────────── */
async function submitService(e) {
  e.preventDefault();
  const form = e.target;
  const btn  = form.querySelector('[type="submit"]');
  const data = Object.fromEntries(new FormData(form));
  const editId = form.dataset.editId;
  const url  = editId ? `/services/${editId}/edit` : '/services';

  btn.disabled = true; btn.textContent = 'Saving…';
  const res = await api(url, data);
  btn.disabled = false; btn.textContent = 'Publish';

  if (res.success) {
    closeModal('addService');
    showToast(editId ? 'Service updated!' : 'Service listed! 🎉');
    setTimeout(() => location.reload(), 1200);
  } else {
    showToast(res.error || 'Could not save service.', 'error');
  }
}

/* ── Delete service ───────────────────────────────────────── */
async function deleteService(serviceId) {
  if (!confirm('Delete this service listing? This cannot be undone.')) return;
  const res = await api(`/services/${serviceId}/delete`);
  if (res.success) { showToast('Service deleted.'); setTimeout(() => location.reload(), 1000); }
  else showToast(res.error || 'Could not delete.', 'error');
}

/* ── Messaging ────────────────────────────────────────────── */
async function sendMessage() {
  const input  = document.getElementById('chatInput');
  const swapId = document.getElementById('chatSwapId')?.value;
  const text   = input?.value.trim();
  if (!text || !swapId) return;

  const res = await api('/messages/send', { swap_id: swapId, body: text });
  if (res.success) {
    appendMessage(text, new Date());
    input.value = '';
  } else {
    showToast(res.error || 'Could not send message.', 'error');
  }
}

function appendMessage(text, date) {
  const box = document.getElementById('chatMessages');
  if (!box) return;
  const time = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  const div  = document.createElement('div');
  div.className = 'msg me';
  div.innerHTML = `<div class="msg-bubble">${escHtml(text)}</div><div class="msg-time">${time}</div>`;
  box.appendChild(div);
  box.scrollTop = box.scrollHeight;
}

// Enter to send in chat
document.addEventListener('keydown', e => {
  if (e.key === 'Enter' && e.target.id === 'chatInput' && !e.shiftKey) {
    e.preventDefault(); sendMessage();
  }
});

/* ── Profile update ───────────────────────────────────────── */
async function updateProfile(e) {
  e.preventDefault();
  const form = e.target;
  const btn  = form.querySelector('[type="submit"]');
  btn.disabled = true; btn.textContent = 'Saving…';

  // Traditional form submit for profile (uses flash messages)
  form.submit();
}

/* ── Browse filters ───────────────────────────────────────── */
function setCategory(cat, el) {
  document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
  el.classList.add('active');
  updateBrowseURL();
}

function updateBrowseURL() {
  const cat = document.querySelector('.chip.active')?.dataset.cat ?? '';
  const q   = document.getElementById('searchInput')?.value ?? '';
  const params = new URLSearchParams();
  if (q)   params.set('q', q);
  if (cat) params.set('category', cat);
  const url = '/services' + (params.toString() ? '?' + params : '');
  window.location.href = url;
}

let searchTimer;
function onSearch() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(updateBrowseURL, 450);
}

/* ── Star rating UI ───────────────────────────────────────── */
function initStarRating(containerId, inputId) {
  const container = document.getElementById(containerId);
  const input     = document.getElementById(inputId);
  if (!container || !input) return;

  container.querySelectorAll('.star').forEach(star => {
    star.addEventListener('click', () => {
      const val = star.dataset.val;
      input.value = val;
      container.querySelectorAll('.star').forEach(s => {
        s.classList.toggle('active', parseInt(s.dataset.val) <= parseInt(val));
      });
    });
  });
}

/* ── Util ─────────────────────────────────────────────────── */
function escHtml(str) {
  return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

/* ── Init on load ─────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  // Scroll chat to bottom
  const chatBox = document.getElementById('chatMessages');
  if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;

  // Init star ratings
  initStarRating('starContainer', 'reviewRating');

  // Auto-dismiss flash after 5s
  setTimeout(() => {
    document.querySelectorAll('.flash').forEach(f => f.remove());
  }, 5000);
});
