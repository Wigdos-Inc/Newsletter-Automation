let currentNewsletter = null;

document.getElementById('generate-btn').addEventListener('click', async function() {
    const query = document.getElementById('newsletter-query').value.trim();
    const loading = document.getElementById('loading');
    const result = document.getElementById('result');
    const error = document.getElementById('error');
    const content = document.getElementById('newsletter-content');

    if (!query) {
        error.textContent = 'Please enter a topic';
        error.style.display = 'block';
        return;
    }

    loading.style.display = 'block';
    result.style.display = 'none';
    error.style.display = 'none';

    try {
        const response = await fetch('scripts/php/newsletters.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ query: query, action: 'generate' })
        });

        const data = await response.json();
        loading.style.display = 'none';

        if (data.success !== false) {
            currentNewsletter = data;
            const newsletterHTML = data.content || data.html || data.body || JSON.stringify(data);
            content.innerHTML = newsletterHTML;
            result.style.display = 'block';
            
            // Show save button
            document.getElementById('save-newsletter-btn').style.display = 'inline-block';
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

// Save newsletter to database
document.getElementById('save-newsletter-btn').addEventListener('click', async function() {
    const title = prompt('Enter a title for this newsletter:');
    if (!title) return;
    
    const content = document.getElementById('newsletter-content').innerHTML;
    const error = document.getElementById('error');
    const saveBtn = this;
    
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';
    
    try {
        const response = await fetch('scripts/php/newsletters.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                action: 'save',
                title: title,
                content: content
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Newsletter saved successfully! ID: ' + data.newsletter_ID);
            saveBtn.textContent = 'Saved ✓';
        } else {
            error.textContent = data.error || 'Failed to save newsletter';
            error.style.display = 'block';
            saveBtn.textContent = 'Save to Database';
            saveBtn.disabled = false;
        }
    } catch (err) {
        error.textContent = 'Error: ' + err.message;
        error.style.display = 'block';
        saveBtn.textContent = 'Save to Database';
        saveBtn.disabled = false;
    }
});