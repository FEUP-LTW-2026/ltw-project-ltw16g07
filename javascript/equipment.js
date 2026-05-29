document.addEventListener('DOMContentLoaded', () => {
    const btns  = document.querySelectorAll('.filter-btn-eq');
    const cards = document.querySelectorAll('.equipment-card');
 
    btns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Remove o estado ativo dos outros botões e aplica ao clicado
            btns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            const filter = btn.dataset.filter;
            
            // Filtra os quadradinhos no ecrã de forma instantânea
            cards.forEach(card => {
                if (filter === 'all' || card.dataset.category === filter) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
});
