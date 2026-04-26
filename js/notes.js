
function filterTable(searchInput, visibilityFilter, semesterFilter, tableBody) {
    const searchTerm = searchInput.value.toLowerCase();
    const visibility = visibilityFilter.value.toLowerCase();
    const semester = semesterFilter.value;
    
    const rows = tableBody.getElementsByTagName('tr');
    
    Array.from(rows).forEach(row => {
        const title = row.cells[0].textContent.toLowerCase();
        const subject = row.cells[1].textContent.toLowerCase();
        const rowSemester = row.cells[2].textContent;
        const rowVisibility = row.cells[3].textContent.toLowerCase();
        
        const matchesSearch = title.includes(searchTerm) || subject.includes(searchTerm);
        const matchesVisibility = !visibility || rowVisibility.includes(visibility);
        const matchesSemester = !semester || rowSemester.includes(semester);
        
        if (matchesSearch && matchesVisibility && matchesSemester) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

/**
 * Filter Note Cards (for grid view)
 */
function filterNoteCards(searchInput, subjectFilter, semesterFilter, sortBy, notesGrid) {
    const searchTerm = searchInput.value.toLowerCase();
    const subject = subjectFilter.value.toLowerCase();
    const semester = semesterFilter.value;
    const sortValue = sortBy.value;
    
    const cards = Array.from(notesGrid.getElementsByClassName('note-card'));
    let visibleCount = 0;
    
    // Filter cards
    cards.forEach(card => {
        const title = card.dataset.title || '';
        const cardSubject = card.dataset.subject || '';
        const cardSemester = card.dataset.semester || '';
        const uploader = card.dataset.uploader || '';
        
        const matchesSearch = !searchTerm || 
            title.includes(searchTerm) || 
            cardSubject.includes(searchTerm) || 
            uploader.includes(searchTerm);
        
        const matchesSubject = !subject || cardSubject === subject;
        const matchesSemester = !semester || cardSemester === semester;
        
        if (matchesSearch && matchesSubject && matchesSemester) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Sort visible cards
    const visibleCards = cards.filter(card => card.style.display !== 'none');
    
    visibleCards.sort((a, b) => {
        if (sortValue === 'popular') {
            return parseInt(b.dataset.downloads) - parseInt(a.dataset.downloads);
        } else if (sortValue === 'title') {
            return a.dataset.title.localeCompare(b.dataset.title);
        } else { // recent
            return parseInt(b.dataset.date) - parseInt(a.dataset.date);
        }
    });
    
    // Re-append sorted cards
    visibleCards.forEach(card => notesGrid.appendChild(card));
    
    // Show/hide "no results" message
    const noResults = document.getElementById('noResultsMessage');
    if (noResults) {
        if (visibleCount === 0) {
            notesGrid.style.display = 'none';
            noResults.style.display = 'block';
        } else {
            notesGrid.style.display = 'grid';
            noResults.style.display = 'none';
        }
    }
}

// =====================================================
// REAL-TIME SEARCH
// =====================================================

/**
 * Setup Search with Debounce
 */
function setupSearch(inputId, filterFunction, delay = 300) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    let timeout = null;
    
    input.addEventListener('input', function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            filterFunction();
        }, delay);
    });
}

// =====================================================
// SUBJECT DROPDOWN FILTER (for upload page)
// =====================================================

/**
 * Update Subjects Based on Course and Semester
 */
function updateSubjectDropdown(allSubjects, courseSelect, semesterSelect, subjectSelect) {
    const courseId = courseSelect.value;
    const semester = semesterSelect.value;
    
    // Clear current options
    subjectSelect.innerHTML = '<option value="">Select Subject</option>';
    
    if (courseId && semester) {
        // Filter subjects
        const filtered = allSubjects.filter(subject => 
            subject.course_id == courseId && subject.semester == semester
        );
        
        if (filtered.length > 0) {
            filtered.forEach(subject => {
                const option = document.createElement('option');
                option.value = subject.subject_id;
                option.textContent = subject.subject_name;
                subjectSelect.appendChild(option);
            });
            subjectSelect.disabled = false;
        } else {
            subjectSelect.innerHTML = '<option value="">No subjects available</option>';
            subjectSelect.disabled = true;
        }
    } else {
        subjectSelect.innerHTML = '<option value="">Select Course and Semester first</option>';
        subjectSelect.disabled = true;
    }
}

// =====================================================
// UTILITIES
// =====================================================

/**
 * Highlight Search Terms
 */
function highlightSearchTerm(text, searchTerm) {
    if (!searchTerm) return text;
    
    const regex = new RegExp('(' + searchTerm + ')', 'gi');
    return text.replace(regex, '<mark>$1</mark>');
}

/**
 * Format File Size
 */
function formatFileSize(bytes) {
    if (bytes >= 1073741824) {
        return (bytes / 1073741824).toFixed(2) + ' GB';
    } else if (bytes >= 1048576) {
        return (bytes / 1048576).toFixed(2) + ' MB';
    } else if (bytes >= 1024) {
        return (bytes / 1024).toFixed(2) + ' KB';
    } else {
        return bytes + ' bytes';
    }
}

/**
 * Time Ago Format
 */
function timeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    
    if (seconds < 60) return 'just now';
    if (seconds < 3600) return Math.floor(seconds / 60) + ' minutes ago';
    if (seconds < 86400) return Math.floor(seconds / 3600) + ' hours ago';
    if (seconds < 604800) return Math.floor(seconds / 86400) + ' days ago';
    
    return date.toLocaleDateString();
}

console.log('✅ Notes.js loaded');
