function showModal(id) { document.getElementById(id).classList.add('show'); }
function hideModal(id) { document.getElementById(id).classList.remove('show'); }

function openEditModal(id) {
    const btn = document.querySelector(`.btn-edit-class[data-id="${id}"]`);
    if (!btn) return;
    const d = btn.dataset;
    document.getElementById('e-id').value       = d.id;
    document.getElementById('e-title').value    = d.title;
    document.getElementById('e-type').value     = d.type;
    document.getElementById('e-day').value      = d.day;
    document.getElementById('e-start').value    = d.start;
    document.getElementById('e-end').value      = d.end;
    document.getElementById('e-location').value = d.location;
    document.getElementById('e-trainer').value  = d.trainer;
    document.getElementById('e-spots').value    = d.spots;
    showModal('edit-modal');
}
