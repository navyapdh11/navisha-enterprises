// Main application orchestrator
document.addEventListener('DOMContentLoaded', () => {
    window.navishaAI = new NavishaAIAgent();
    window.showAdminPanel = () => window.navishaAI.showAdminPanel();
    // Attach close admin
    document.getElementById('closeAdminBtn')?.addEventListener('click', () => window.navishaAI.hideAdminPanel());
    document.getElementById('overlay')?.addEventListener('click', () => window.navishaAI.hideAdminPanel());
    // Admin tabs
    document.querySelectorAll('.admin-tab').forEach(tab => {
        tab.addEventListener('click', (e) => {
            document.querySelectorAll('.admin-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.getElementById(tab.dataset.tab + 'Tab')?.classList.add('active');
            if (tab.dataset.tab === 'search') window.navishaAI.renderSearchTab();
        });
    });
    // Load products grid
    const productsGrid = document.getElementById('productsGrid');
    if (productsGrid && window.navishaAI) {
        const products = window.navishaAI.culturalKnowledge.traditional_clothing;
        productsGrid.innerHTML = products.map(p => `
            <div class="product-card" onclick="window.navishaAI.askAboutProduct && window.navishaAI.askAboutProduct('${p.name}')">
                <div class="product-image"><i class="fas fa-tshirt"></i></div>
                <div class="product-content">
                    <h3 class="product-title"><i class="fas fa-tag"></i> ${p.name}</h3>
                    <p class="product-description">${p.description.substring(0,100)}</p>
                    <div class="product-price">${p.price}</div>
                    <button class="btn">Learn More</button>
                </div>
            </div>
        `).join('');
    }
    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'N') { e.preventDefault(); window.navishaAI.showAdminPanel(); }
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') { e.preventDefault(); window.navishaAI.toggleAgent(); }
        if (e.key === 'Escape') { window.navishaAI.hideAdminPanel(); window.navishaAI.hideAgent(); }
    });
    console.log('Navisha AI Super Agent • MCTS • ToT • GoT • CoT • OASIS‑IS • Active');
});
