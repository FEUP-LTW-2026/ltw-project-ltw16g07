function toggleEditProfile() {
    const display = document.getElementById('profile-display');
    const form    = document.getElementById('profile-edit-form');
    if (form && display) {
        const hidden  = form.style.display === 'none' || form.style.display === '';
        display.style.display = hidden ? 'none' : 'block';
        form.style.display    = hidden ? 'block' : 'none';
    }
}
