document.getElementById('generate-btn').addEventListener('click', async function() {
    const query = document.getElementById('newsletter-query').value.trim();
    const loading = document.getElementById('loading');
    const result = document.getElementById('result');
    const error = document.getElementById('error');
    const content = document.getElementById('newsletter-content');

    // Validate
    if (!query) {
        error.textContent = 'Please enter a topic';
        error.style.display = 'block';
        return;
    }

    // Show loading
    loading.style.display = 'block';
    result.style.display = 'none';
    error.style.display = 'none';

    try {
        // Call your PHP backend
        const response = await fetch('scripts/php/newsletters.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ query: query })
        });

        const data = await response.json();
        loading.style.display = 'none';

        if (data.success !== false) {
            // Display the newsletter (adjust based on what your n8n returns)
            content.innerHTML = data.content || data.html || data.body || JSON.stringify(data);
            result.style.display = 'block';
        } else {
            error.textContent = data.error || 'Something went wrong';
            error.style.display = 'block';
        }
    } catch (err) {
        loading.style.display = 'none';
        error.textContent = 'Error: ' + err.message;
        error.style.display = 'block';
    }
});