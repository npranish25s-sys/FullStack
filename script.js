const API_URL = 'http://localhost:3000/movies';

const movieListDiv = document.getElementById('movie-list');
const searchInput = document.getElementById('search-input');
const form = document.getElementById('add-movie-form');
const refreshBtn = document.getElementById('refresh-btn');
const clearFormBtn = document.getElementById('clear-form');

const modal = document.getElementById('modal');
const editForm = document.getElementById('edit-movie-form');
const cancelEditBtn = document.getElementById('cancel-edit');

let allMovies = [];
let searchTimeout = null;

function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function renderMovies(moviesToDisplay) {
  movieListDiv.innerHTML = '';
  if (!moviesToDisplay || moviesToDisplay.length === 0) {
    movieListDiv.innerHTML = '<p>No movies found matching your criteria.</p>';
    return;
  }

  moviesToDisplay.forEach(movie => {
    const el = document.createElement('div');
    el.className = 'movie-item';
    el.innerHTML = `
      <p><strong>${escapeHtml(movie.title)}</strong> (${movie.year}) - ${escapeHtml(movie.genre)}</p>
      <div class="movie-actions">
        <button class="edit" data-id="${movie.id}">Edit</button>
        <button class="delete" data-id="${movie.id}">Delete</button>
      </div>
    `;
    el.querySelector('.edit').addEventListener('click', () => openEditModal(movie));
    el.querySelector('.delete').addEventListener('click', () => {
      if (confirm(`Delete "${movie.title}"?`)) deleteMovie(movie.id);
    });
    movieListDiv.appendChild(el);
  });
}

async function fetchMovies() {
  try {
    const res = await fetch(API_URL);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const movies = await res.json();
    allMovies = movies;
    renderMovies(allMovies);
  } catch (err) {
    console.error('Error fetching movies:', err);
    movieListDiv.innerHTML = '<p class="error">Unable to load movies. Is JSON Server running at <code>http://localhost:3000</code>?</p>';
  }
}

form.addEventListener('submit', async function (e) {
  e.preventDefault();
  const newMovie = {
    title: document.getElementById('title').value.trim(),
    genre: document.getElementById('genre').value.trim(),
    year: parseInt(document.getElementById('year').value, 10)
  };

  try {
    const res = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(newMovie)
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    this.reset();
    await fetchMovies();
  } catch (err) {
    console.error('Error adding movie:', err);
    alert('Failed to add movie. Check console for details.');
  }
});

clearFormBtn.addEventListener('click', () => form.reset());

function openEditModal(movie) {
  document.getElementById('edit-id').value = movie.id;
  document.getElementById('edit-title').value = movie.title;
  document.getElementById('edit-genre').value = movie.genre;
  document.getElementById('edit-year').value = movie.year;
  modal.classList.remove('hidden');
  modal.setAttribute('aria-hidden', 'false');
}

cancelEditBtn.addEventListener('click', () => {
  modal.classList.add('hidden');
  modal.setAttribute('aria-hidden', 'true');
});

editForm.addEventListener('submit', async function (e) {
  e.preventDefault();
  const id = document.getElementById('edit-id').value;
  const updated = {
    id: parseInt(id, 10),
    title: document.getElementById('edit-title').value.trim(),
    genre: document.getElementById('edit-genre').value.trim(),
    year: parseInt(document.getElementById('edit-year').value, 10)
  };

  try {
    const res = await fetch(`${API_URL}/${id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(updated)
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
    await fetchMovies();
  } catch (err) {
    console.error('Error updating movie:', err);
    alert('Failed to update movie. Check console for details.');
  }
});

async function deleteMovie(id) {
  try {
    const res = await fetch(`${API_URL}/${id}`, { method: 'DELETE' });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    await fetchMovies();
  } catch (err) {
    console.error('Error deleting movie:', err);
    alert('Failed to delete movie. Check console for details.');
  }
}

searchInput.addEventListener('input', () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    const term = searchInput.value.trim().toLowerCase();
    if (!term) return renderMovies(allMovies);
    const filtered = allMovies.filter(m =>
      (m.title || '').toLowerCase().includes(term) ||
      (m.genre || '').toLowerCase().includes(term)
    );
    renderMovies(filtered);
  }, 200);
});

refreshBtn.addEventListener('click', fetchMovies);

// initial load
fetchMovies();
