function toggleReviewForm() {
    const form = document.getElementById('review-form');
    if (form) {
        form.style.display = form.style.display === 'block' ? 'none' : 'block';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.star').forEach(star => {
        star.addEventListener('click', function() {
            const value = this.dataset.value;
            const ratingInput = document.getElementById('rating-value');
            if (ratingInput) {
                ratingInput.value = value;
            }
            document.querySelectorAll('.star').forEach((s, i) => {
                s.textContent = i < value ? '★' : '☆';
            });
        });
    });
});
