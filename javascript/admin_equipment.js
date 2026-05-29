function showModal(id) { document.getElementById(id).classList.add('show'); }
function hideModal(id) { document.getElementById(id).classList.remove('show'); }

function openEditEq(id) {
    const btn = document.querySelector(`.btn-edit-eq[data-id="${id}"]`);
    if (!btn) return;
    const d = btn.dataset;
    document.getElementById('ee-id').value       = d.id;
    document.getElementById('ee-name').value     = d.name;
    document.getElementById('ee-category').value = d.category;
    document.getElementById('ee-quantity').value = d.quantity;
    document.getElementById('ee-desc').value     = d.description;
    document.getElementById('ee-status').value   = d.status;
    showModal('edit-modal');
}
