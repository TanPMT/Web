const API_URL = '/api';
let currentUser = null;
let notes = [];

// Initialize app
document.addEventListener('DOMContentLoaded', () => {
    checkAuth();
    setupEventListeners();
});

function setupEventListeners() {
    document.getElementById('loginForm').addEventListener('submit', handleLogin);
    document.getElementById('signupForm').addEventListener('submit', handleSignup);
    document.getElementById('noteForm').addEventListener('submit', handleNoteSave);
    
    // Search on Enter key
    document.getElementById('searchInput')?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            searchNotes();
        }
    });
}

// Authentication
async function checkAuth() {
    const token = localStorage.getItem('token');
    
    if (!token) {
        showLogin();
        return;
    }

    try {
        const response = await fetch(`${API_URL}/auth/verify`, {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        const data = await response.json();

        if (data.success) {
            currentUser = data.data.user;
            showApp();
            loadNotes();
        } else {
            localStorage.removeItem('token');
            showLogin();
        }
    } catch (error) {
        console.error('Auth check failed:', error);
        localStorage.removeItem('token');
        showLogin();
    }
}

async function handleLogin(e) {
    e.preventDefault();
    
    const username = document.getElementById('loginUsername').value;
    const password = document.getElementById('loginPassword').value;

    try {
        const response = await fetch(`${API_URL}/auth/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ username, password })
        });

        const data = await response.json();

        if (data.success) {
            localStorage.setItem('token', data.data.token);
            currentUser = data.data.user;
            showApp();
            loadNotes();
            showMessage('Login successful!', 'success');
        } else {
            showMessage(data.message, 'error');
        }
    } catch (error) {
        console.error('Login failed:', error);
        showMessage('Login failed. Please try again.', 'error');
    }
}

async function handleSignup(e) {
    e.preventDefault();
    
    const username = document.getElementById('signupUsername').value;
    const email = document.getElementById('signupEmail').value;
    const password = document.getElementById('signupPassword').value;

    try {
        const response = await fetch(`${API_URL}/auth/signup`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ username, email, password })
        });

        const data = await response.json();

        if (data.success) {
            localStorage.setItem('token', data.data.token);
            currentUser = data.data.user;
            showApp();
            loadNotes();
            showMessage('Account created successfully!', 'success');
        } else {
            showMessage(data.message, 'error');
        }
    } catch (error) {
        console.error('Signup failed:', error);
        showMessage('Signup failed. Please try again.', 'error');
    }
}

function logout() {
    localStorage.removeItem('token');
    currentUser = null;
    notes = [];
    showLogin();
    showMessage('Logged out successfully', 'success');
}

// Notes CRUD
async function loadNotes(search = '') {
    const token = localStorage.getItem('token');
    
    try {
        let url = `${API_URL}/notes`;
        if (search) {
            url += `?search=${encodeURIComponent(search)}`;
        }

        const response = await fetch(url, {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        const data = await response.json();

        if (data.success) {
            notes = data.data.notes;
            renderNotes();
        } else {
            showMessage(data.message, 'error');
        }
    } catch (error) {
        console.error('Failed to load notes:', error);
        showMessage('Failed to load notes', 'error');
    }
}

async function handleNoteSave(e) {
    e.preventDefault();
    
    const token = localStorage.getItem('token');
    const noteId = document.getElementById('noteId').value;
    const title = document.getElementById('noteTitle').value;
    const content = document.getElementById('noteContent').value;
    const tags = document.getElementById('noteTags').value;

    const noteData = { title, content, tags };

    try {
        let url = `${API_URL}/notes`;
        let method = 'POST';

        if (noteId) {
            url += `/${noteId}`;
            method = 'PUT';
        }

        const response = await fetch(url, {
            method: method,
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(noteData)
        });

        const data = await response.json();

        if (data.success) {
            closeNoteModal();
            loadNotes();
            showMessage(noteId ? 'Note updated!' : 'Note created!', 'success');
        } else {
            showMessage(data.message, 'error');
        }
    } catch (error) {
        console.error('Failed to save note:', error);
        showMessage('Failed to save note', 'error');
    }
}

async function deleteNote(noteId) {
    if (!confirm('Are you sure you want to delete this note?')) {
        return;
    }

    const token = localStorage.getItem('token');

    try {
        const response = await fetch(`${API_URL}/notes/${noteId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        const data = await response.json();

        if (data.success) {
            loadNotes();
            showMessage('Note deleted!', 'success');
        } else {
            showMessage(data.message, 'error');
        }
    } catch (error) {
        console.error('Failed to delete note:', error);
        showMessage('Failed to delete note', 'error');
    }
}

// UI Functions
function showLogin() {
    document.getElementById('loginPage').classList.remove('hidden');
    document.getElementById('signupPage').classList.add('hidden');
    document.getElementById('mainApp').classList.add('hidden');
    updateNavBar(false);
}

function showSignup() {
    document.getElementById('loginPage').classList.add('hidden');
    document.getElementById('signupPage').classList.remove('hidden');
    document.getElementById('mainApp').classList.add('hidden');
    updateNavBar(false);
}

function showApp() {
    document.getElementById('loginPage').classList.add('hidden');
    document.getElementById('signupPage').classList.add('hidden');
    document.getElementById('mainApp').classList.remove('hidden');
    updateNavBar(true);
}

function updateNavBar(loggedIn) {
    const navButtons = document.getElementById('navButtons');
    
    if (loggedIn) {
        navButtons.innerHTML = `
            <span class="text-gray-700 mr-4">Welcome, <strong>${currentUser.username}</strong></span>
            <button onclick="logout()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md">
                <i class="fas fa-sign-out-alt mr-2"></i>Logout
            </button>
        `;
    } else {
        navButtons.innerHTML = '';
    }
}

function showNewNoteModal() {
    document.getElementById('modalTitle').textContent = 'New Note';
    document.getElementById('noteForm').reset();
    document.getElementById('noteId').value = '';
    document.getElementById('noteModal').classList.remove('hidden');
}

function editNote(noteId) {
    const note = notes.find(n => n.id == noteId);
    
    if (!note) return;

    document.getElementById('modalTitle').textContent = 'Edit Note';
    document.getElementById('noteId').value = note.id;
    document.getElementById('noteTitle').value = note.title;
    document.getElementById('noteContent').value = note.content;
    document.getElementById('noteTags').value = note.tags;
    document.getElementById('noteModal').classList.remove('hidden');
}

function closeNoteModal() {
    document.getElementById('noteModal').classList.add('hidden');
    document.getElementById('noteForm').reset();
}

function searchNotes() {
    const searchTerm = document.getElementById('searchInput').value;
    loadNotes(searchTerm);
}

function renderNotes() {
    const container = document.getElementById('notesContainer');
    const emptyState = document.getElementById('emptyState');

    if (notes.length === 0) {
        container.innerHTML = '';
        emptyState.classList.remove('hidden');
        return;
    }

    emptyState.classList.add('hidden');
    
    container.innerHTML = notes.map(note => {
        const tags = note.tags ? note.tags.split(',').map(t => t.trim()).filter(t => t) : [];
        const lastModified = new Date(note.last_modified).toLocaleString();
        const contentPreview = note.content ? note.content.substring(0, 150) + (note.content.length > 150 ? '...' : '') : '';

        return `
            <div class="note-card bg-white rounded-lg shadow-md p-6 border-l-4 border-indigo-500">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-xl font-bold text-gray-800 flex-1">${escapeHtml(note.title)}</h3>
                    <div class="flex gap-2">
                        <button onclick="editNote(${note.id})" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteNote(${note.id})" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                
                ${tags.length > 0 ? `
                    <div class="mb-3">
                        ${tags.map(tag => `<span class="tag">${escapeHtml(tag)}</span>`).join('')}
                    </div>
                ` : ''}
                
                <p class="text-gray-600 mb-4 whitespace-pre-wrap">${escapeHtml(contentPreview)}</p>
                
                <div class="text-sm text-gray-500">
                    <i class="fas fa-clock mr-1"></i> Last modified: ${lastModified}
                </div>
            </div>
        `;
    }).join('');
}

function showMessage(message, type = 'info') {
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500'
    };

    const messageEl = document.createElement('div');
    messageEl.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50`;
    messageEl.textContent = message;

    document.body.appendChild(messageEl);

    setTimeout(() => {
        messageEl.remove();
    }, 3000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
