// Interactions spécifiques pour la page événement
document.addEventListener('DOMContentLoaded', function() {
    // Confirmation avant inscription
    const teamButtons = document.querySelectorAll('.btn-team');
    teamButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!this.disabled) {
                const teamName = this.textContent.trim().split('\n')[0];
                const confirmed = confirm(`Voulez-vous vraiment rejoindre le ${teamName} ?`);
                if (!confirmed) {
                    e.preventDefault();
                }
            }
        });
    });
    
    // Afficher le nombre de places restantes en temps réel
    updateTeamAvailability();
});

function updateTeamAvailability() {
    const teamStats = document.querySelectorAll('.team-stat');
    teamStats.forEach(stat => {
        const countText = stat.querySelector('.team-count');
        if (countText) {
            const [current, max] = countText.textContent.split('/').map(n => parseInt(n.trim()));
            const remaining = max - current;
            
            if (remaining === 0) {
                stat.style.opacity = '0.6';
            } else if (remaining <= 3) {
                const statusEl = stat.querySelector('.team-status');
                if (statusEl) {
                    statusEl.style.animation = 'pulse 2s infinite';
                }
            }
        }
    });
}

// Animation pulse pour les places limitées
const style = document.createElement('style');
style.textContent = `
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }
`;
document.head.appendChild(style);
