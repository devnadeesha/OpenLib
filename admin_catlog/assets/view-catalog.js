// Sample books data (will be merged with localStorage books)
const sampleBooks = [
  {
    id: 1,
    title: 'The Great Gatsby',
    author: 'F. Scott Fitzgerald',
    genre: 'Fiction',
    isbn: '978-0743273565',
    year: 1925,
    pages: 180,
    publisher: 'Scribner',
    quantity: 5,
    available_quantity: 5,
    description: 'A classic novel of the American Dream and Jazz Age'
  },
  {
    id: 2,
    title: 'To Kill a Mockingbird',
    author: 'Harper Lee',
    genre: 'Fiction',
    isbn: '978-0061120084',
    year: 1960,
    pages: 320,
    publisher: 'Lippincott',
    quantity: 5,
    available_quantity: 3,
    description: 'A gripping tale of racial injustice in the South'
  },
  {
    id: 3,
    title: '1984',
    author: 'George Orwell',
    genre: 'Science',
    isbn: '978-0451524935',
    year: 1949,
    pages: 328,
    publisher: 'Houghton Mifflin',
    quantity: 5,
    available_quantity: 4,
    description: 'A haunting tale of totalitarianism'
  },
  {
    id: 4,
    title: 'Pride and Prejudice',
    author: 'Jane Austen',
    genre: 'Romance',
    isbn: '978-0141439518',
    year: 1813,
    pages: 432,
    publisher: 'Penguin',
    quantity: 5,
    available_quantity: 5,
    description: 'A timeless love story of Elizabeth Bennet and Mr. Darcy'
  },
  {
    id: 5,
    title: 'The Catcher in the Rye',
    author: 'J.D. Salinger',
    genre: 'Fiction',
    isbn: '978-0316769174',
    year: 1951,
    pages: 277,
    publisher: 'Little, Brown',
    quantity: 5,
    available_quantity: 2,
    description: 'The adventures of teenage Holden Caulfield'
  },
  {
    id: 6,
    title: 'Brave New World',
    author: 'Aldous Huxley',
    genre: 'Science',
    isbn: '978-0060085237',
    year: 1932,
    pages: 288,
    publisher: 'Harper',
    quantity: 5,
    available_quantity: 3,
    description: 'A dystopian vision of the future'
  },
  {
    id: 7,
    title: 'The Hobbit',
    author: 'J.R.R. Tolkien',
    genre: 'Fantasy',
    isbn: '978-0547928227',
    year: 1937,
    pages: 310,
    publisher: 'Allen & Unwin',
    quantity: 5,
    available_quantity: 4,
    description: 'An epic fantasy adventure'
  },
  {
    id: 8,
    title: 'Harry Potter and the Philosophers Stone',
    author: 'J.K. Rowling',
    genre: 'Fantasy',
    isbn: '978-0439708180',
    year: 1998,
    pages: 309,
    publisher: 'Bloomsbury',
    quantity: 5,
    available_quantity: 5,
    description: 'The beginning of a magical journey'
  },
  {
    id: 9,
    title: 'The Lord of the Rings',
    author: 'J.R.R. Tolkien',
    genre: 'Fantasy',
    isbn: '978-0544003415',
    year: 1954,
    pages: 1178,
    publisher: 'Allen & Unwin',
    quantity: 5,
    available_quantity: 2,
    description: 'The ultimate fantasy epic'
  },
  {
    id: 10,
    title: 'Atomic Habits',
    author: 'James Clear',
    genre: 'Self-Help',
    isbn: '978-0735211292',
    year: 2018,
    pages: 320,
    publisher: 'Avery',
    quantity: 5,
    available_quantity: 5,
    description: 'Build better habits through small changes'
  }
];

let allBooks = [];
let currentView = 'grid';

// Initialize catalog on page load
document.addEventListener('DOMContentLoaded', function() {
  loadCatalog();
});

// Load books from localStorage and merge with sample books
function loadCatalog() {
  const storedBooks = JSON.parse(localStorage.getItem('libraryBooks')) || [];
  
  // Merge stored books with sample books, avoiding duplicates
  const seenISBNs = new Set();
  allBooks = [];

  // Add stored books first
  storedBooks.forEach(book => {
    if (!seenISBNs.has(book.isbn)) {
      allBooks.push(book);
      seenISBNs.add(book.isbn);
    }
  });

  // Add sample books
  sampleBooks.forEach(book => {
    if (!seenISBNs.has(book.isbn)) {
      allBooks.push(book);
      seenISBNs.add(book.isbn);
    }
  });

  displayBooks(allBooks);
}

// Display books in grid/list view
function displayBooks(books) {
  const booksGrid = document.getElementById('booksGrid');
  const emptyState = document.getElementById('emptyState');
  const resultsCount = document.getElementById('resultsCount');

  if (books.length === 0) {
    booksGrid.style.display = 'none';
    emptyState.style.display = 'block';
    resultsCount.textContent = 'No books found';
    return;
  }

  booksGrid.style.display = 'grid';
  emptyState.style.display = 'none';
  resultsCount.textContent = `Showing ${books.length} book${books.length !== 1 ? 's' : ''}`;

  booksGrid.innerHTML = books.map(book => {
    const isAvailable = book.available_quantity > 0;
    const statusClass = isAvailable ? 'available' : 'out-of-stock';
    const statusText = isAvailable ? 'In Stock' : 'Out of Stock';

    return `
      <div class="book-card ${currentView === 'list' ? 'list-view' : ''}">
        <div class="book-cover">
          ${book.imageData ? `<img src="${book.imageData}" alt="${escapeHtml(book.title)}">` : 
            `<div class="book-cover-placeholder">📚 ${escapeHtml(book.genre)}</div>`}
        </div>
        <div class="book-info">
          <div>
            <h3 class="book-title">${escapeHtml(book.title)}</h3>
            <p class="book-author">by ${escapeHtml(book.author)}</p>
            <span class="book-genre">${escapeHtml(book.genre)}</span>
            
            <div class="book-details">
              <p><strong>ISBN:</strong> ${book.isbn}</p>
              <p><strong>Year:</strong> ${book.year}</p>
              <p><strong>Pages:</strong> ${book.pages}</p>
              <p><strong>Publisher:</strong> ${book.publisher || 'Unknown'}</p>
              ${book.description ? `<p><strong>Description:</strong> ${book.description}</p>` : ''}
            </div>
          </div>
          
          <div class="book-availability">
            <div class="availability-status ${statusClass}">
              <span class="status-dot ${statusClass}"></span>
              ${statusText} (${book.available_quantity}/${book.quantity})
            </div>
          </div>
        </div>
      </div>
    `;
  }).join('');
}

// Filter and search functionality
function filterCatalog() {
  const searchTerm = document.getElementById('searchInput').value.toLowerCase();
  const genreFilter = document.getElementById('genreFilter').value;
  const quantityFilter = document.getElementById('quantityFilter').value;

  let filtered = allBooks.filter(book => {
    // Search filter
    const matchesSearch = 
      book.title.toLowerCase().includes(searchTerm) ||
      book.author.toLowerCase().includes(searchTerm) ||
      book.isbn.toLowerCase().includes(searchTerm);

    if (!matchesSearch) return false;

    // Genre filter
    if (genreFilter && book.genre !== genreFilter) return false;

    // Quantity filter
    if (quantityFilter === 'available' && book.available_quantity === 0) return false;

    return true;
  });

  displayBooks(filtered);
}

// Toggle between grid and list view
function toggleView(view) {
  currentView = view;
  
  // Update toggle buttons
  document.querySelectorAll('.toggle-btn').forEach(btn => {
    btn.classList.remove('active');
  });
  document.querySelector(`[data-view="${view}"]`).classList.add('active');

  // Update grid
  const booksGrid = document.getElementById('booksGrid');
  booksGrid.classList.toggle('list-view', view === 'list');

  // Re-display with current filters
  filterCatalog();
}

// Reset all filters
function resetFilters() {
  document.getElementById('searchInput').value = '';
  document.getElementById('genreFilter').value = '';
  document.getElementById('quantityFilter').value = '';
  displayBooks(allBooks);
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
