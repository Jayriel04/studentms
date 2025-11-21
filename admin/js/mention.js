function initializeMention(textarea, suggestionUrl) {
    let suggestionsBox = null;
    let currentMentionQuery = '';
    let mentionStartIndex = -1;

    function createSuggestionsBox() {
        if (!suggestionsBox) {
            suggestionsBox = document.createElement('div');
            suggestionsBox.className = 'list-group';
            suggestionsBox.style.position = 'absolute';
            suggestionsBox.style.zIndex = '1050';
            suggestionsBox.style.maxHeight = '200px';
            suggestions.style.width = '250px';
            suggestionsBox.style.overflowY = 'auto';
            textarea.parentNode.insertBefore(suggestionsBox, textarea.nextSibling);
        }
    }

    function hideSuggestions() {
        if (suggestionsBox) {
            suggestionsBox.innerHTML = '';
            suggestionsBox.style.display = 'none';
        }
    }

    function showSuggestions(suggestions) {
        if (!suggestionsBox) createSuggestionsBox();
        suggestionsBox.innerHTML = '';
        suggestionsBox.style.display = 'block';

        if (suggestions.length === 0) {
            hideSuggestions();
            return;
        }

        suggestions.forEach(user => {
            const item = document.createElement('a');
            item.href = '#';
            item.className = 'list-group-item list-group-item-action';
            item.textContent = `${user.FirstName} ${user.FamilyName}`;
            item.addEventListener('click', (e) => {
                e.preventDefault();
                insertMention(user);
            });
            suggestionsBox.appendChild(item);
        });

        // Position the box - this is a simple positioning, might need adjustment
        const rect = textarea.getBoundingClientRect();
        const parentRect = textarea.parentNode.getBoundingClientRect();
        suggestionsBox.style.top = `${rect.bottom - parentRect.top}px`;
        suggestionsBox.style.left = `${rect.left - parentRect.left}px`;
    }

    function insertMention(user) {
        const mentionText = `@${user.FirstName} ${user.FamilyName} `;
        const currentText = textarea.value;
        const textBefore = currentText.substring(0, mentionStartIndex);
        const textAfter = currentText.substring(textarea.selectionStart);

        textarea.value = textBefore + mentionText + textAfter;
        hideSuggestions();
        textarea.focus();
    }

    textarea.addEventListener('input', (e) => {
        const text = textarea.value;
        const cursorPos = textarea.selectionStart;
        const atPos = text.lastIndexOf('@', cursorPos - 1);

        if (atPos !== -1 && (atPos === 0 || /\s/.test(text[atPos - 1]))) {
            const query = text.substring(atPos + 1, cursorPos);
            // Avoid fetching for empty query or if it contains spaces
            if (!/\s/.test(query)) {
                mentionStartIndex = atPos;
                currentMentionQuery = query;

                if (currentMentionQuery.length > 1) {
                    fetch(`${suggestionUrl}&term=${encodeURIComponent(currentMentionQuery)}`)
                        .then(res => res.json())
                        .then(suggestions => showSuggestions(suggestions))
                        .catch(() => hideSuggestions());
                } else {
                    hideSuggestions();
                }
                return;
            }
        }
        hideSuggestions();
    });

    textarea.addEventListener('keydown', (e) => {
        if (suggestionsBox && suggestionsBox.style.display === 'block') {
            if (e.key === 'Escape') {
                hideSuggestions();
            }
        }
    });

    document.addEventListener('click', (e) => {
        if (e.target !== textarea && (!suggestionsBox || !suggestionsBox.contains(e.target))) {
            hideSuggestions();
        }
    });
}