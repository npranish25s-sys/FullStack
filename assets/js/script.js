document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    
    if (searchInput) {
        const autocompleteResults = document.getElementById('autocomplete-results');
        let debounceTimer;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const term = this.value.trim();
            
            if (term.length < 2) {
                autocompleteResults.classList.remove('show');
                autocompleteResults.innerHTML = '';
                return;
            }
            
            debounceTimer = setTimeout(() => {
                fetch(`autocomplete.php?term=${encodeURIComponent(term)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length === 0) {
                            autocompleteResults.classList.remove('show');
                            return;
                        }
                        
                        let html = '';
                        data.forEach(item => {
                            html += `<div class="autocomplete-item" data-value="${escapeHtml(item.value)}">${escapeHtml(item.label)}</div>`;
                        });
                        
                        autocompleteResults.innerHTML = html;
                        autocompleteResults.classList.add('show');
                        
                        document.querySelectorAll('.autocomplete-item').forEach(item => {
                            item.addEventListener('click', function() {
                                searchInput.value = this.dataset.value;
                                autocompleteResults.classList.remove('show');
                                searchInput.form.submit();
                            });
                        });
                    })
                    .catch(error => console.error('Error:', error));
            }, 300);
        });
        
        searchInput.addEventListener('blur', function() {
            setTimeout(() => {
                autocompleteResults.classList.remove('show');
            }, 200);
        });
    }
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
