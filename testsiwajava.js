const generateBtn = document.getElementById('generateBtn');
const promptInput = document.getElementById('promptInput');
const cardsContainer = document.getElementById('cardsContainer');

generateBtn.addEventListener('click', () => {
    const prompt = promptInput.value.trim();
    if (!prompt) return alert('Please enter a prompt!');

    // Clear existing cards
    cardsContainer.innerHTML = '';

    // Generate 3 newsletter cards
    for (let i = 1; i <= 3; i++) {
        const card = document.createElement('div');
        card.classList.add('card');
        card.innerHTML = `
            <h3>Newsletter ${i}</h3>
            <p>${prompt} - This is a dynamically generated newsletter content based on your prompt.</p>
        `;
        cardsContainer.appendChild(card);

        // Fade-in effect
        setTimeout(() => {
            card.classList.add('show');
        }, i * 100);
    }

    // Clear input
    promptInput.value = '';
});
