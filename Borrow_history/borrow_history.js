// ── MODAL ────────────────────────────────────────────────────────────────────

function openModal(id, member, book, fine, notes) {
  document.getElementById('m_id').value     = id;
  document.getElementById('m_member').value = member;
  document.getElementById('m_book').value   = book;
  document.getElementById('m_fine').value   = fine;
  document.getElementById('m_notes').value  = notes;

  document.getElementById('modal').classList.add('show');
  document.getElementById('overlay').classList.add('show');
}

function closeModal() {
  document.getElementById('modal').classList.remove('show');
  document.getElementById('overlay').classList.remove('show');
}

// Close on Escape key
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') closeModal();
});

// ── TOAST AUTO-HIDE ───────────────────────────────────────────────────────────

const toast = document.getElementById('toast');
if (toast) {
  setTimeout(function () {
    toast.style.transition = 'opacity 0.4s';
    toast.style.opacity    = '0';
    setTimeout(function () { toast.remove(); }, 400);
  }, 3000);
}