// ADD BOOK PAGE FUNCTIONALITY

// Form validation rules
const validationRules = {
  title: {
    minLength: 3,
    maxLength: 100,
    message: "Book title must be between 3-100 characters"
  },
  author: {
    minLength: 3,
    maxLength: 50,
    message: "Author name must be between 3-50 characters"
  },
  isbn: {
    pattern: /^(?:ISBN(?:-1[03])?[ -]?)?(?=[0-9X]{10}$|(?=(?:[0-9]+[- ]){3})[- 0-9X]{13}$|97[89][0-9]{10}$|(?=(?:[0-9]+[- ]){4})[- 0-9]{17}$)(?:97[89][- ]?)?[0-9]{1,5}[- ]?[0-9]+[- ]?[0-9]+[- ]?[X0-9]$/,
    message: "Please enter a valid ISBN"
  },
  year: {
    min: 1000,
    max: 9999,
    message: "Publication year must be between 1000-9999"
  },
  pages: {
    min: 1,
    message: "Pages must be at least 1"
  },
  quantity: {
    min: 1,
    message: "Quantity must be at least 1"
  }
};

// Validate individual field
function validateField(fieldName, value) {
  const rules = validationRules[fieldName];
  if (!rules) return true;

  switch (fieldName) {
    case 'title':
    case 'author':
      if (value.length < rules.minLength || value.length > rules.maxLength) {
        return rules.message;
      }
      break;

    case 'isbn':
      // Simple ISBN validation - just check it's alphanumeric and right length
      if (!/^[0-9\-X]{10,17}$/.test(value.replace(/[-\s]/g, ''))) {
        return "ISBN must contain numbers and hyphens only (10-13 digits)";
      }
      break;

    case 'year':
      if (value < rules.min || value > rules.max) {
        return rules.message;
      }
      break;

    case 'pages':
    case 'quantity':
      if (value < rules.min) {
        return rules.message;
      }
      break;
  }

  return true;
}

// Show error message
function showError(fieldId, message) {
  const errorElement = document.getElementById(fieldId + 'Error');
  const inputElement = document.getElementById(fieldId);

  if (message !== true) {
    inputElement.classList.add('error');
    errorElement.textContent = message;
    errorElement.classList.add('show');
  } else {
    inputElement.classList.remove('error');
    errorElement.textContent = '';
    errorElement.classList.remove('show');
  }
}

// Real-time validation on blur
document.querySelectorAll('.book-form input, .book-form select').forEach(field => {
  field.addEventListener('blur', function() {
    const fieldId = this.id.replace('book', '').toLowerCase();
    const validation = validateField(fieldId, this.value);
    showError(this.id, validation);
  });
});

// Handle form submission
function handleAddBook(event) {
  event.preventDefault();

  // Get form values
  const formData = {
    title: document.getElementById('bookTitle').value.trim(),//trim removes the white spaces :) of a string 
    author: document.getElementById('bookAuthor').value.trim(),
    isbn: document.getElementById('bookISBN').value.trim(),
    genre: document.getElementById('bookGenre').value,
    year: parseInt(document.getElementById('bookYear').value),
    pages: parseInt(document.getElementById('bookPages').value),
    publisher: document.getElementById('bookPublisher').value.trim(),
    quantity: parseInt(document.getElementById('bookQuantity').value),
    description: document.getElementById('bookDescription').value.trim()
  };

  // Validate all fields
  let isValid = true;
  const fieldsToValidate = ['title', 'author', 'isbn', 'genre', 'year', 'pages', 'quantity'];

  fieldsToValidate.forEach(field => {
    let fieldId = 'book' + field.charAt(0).toUpperCase() + field.slice(1);
    if (field === 'genre') fieldId = 'bookGenre';

    const value = document.getElementById(fieldId).value;
    const validation = validateField(field, value);

    if (validation !== true) {
      showError(fieldId, validation);
      isValid = false;
    } else {
      showError(fieldId, true);
    }
  });

  if (!isValid) {
    showMessage('Please correct the errors above', 'error');
    return;
  }

  // Get existing books from localStorage
  let books = JSON.parse(localStorage.getItem('libraryBooks')) || [];

  // Check for duplicate ISBN
  if (books.some(book => book.isbn === formData.isbn)) {
    showMessage('A book with this ISBN already exists!', 'error');
    return;
  }

  // Add new book with ID and timestamp
  const newBook = {
    id: Date.now(),
    ...formData,
    addedDate: new Date().toLocaleString()
  };

  books.unshift(newBook); // Add to beginning of array
  localStorage.setItem('libraryBooks', JSON.stringify(books));

  // Show success message
  showMessage('Book added successfully!', 'success');

  // Reset form
  document.getElementById('bookForm').reset();

  // Update recently added books list
  displayRecentBooks();

  // Clear error messages
  document.querySelectorAll('.error-message').forEach(el => {
    el.classList.remove('show');
    el.textContent = '';
  });

  // Scroll to recent books
  setTimeout(() => {
    document.querySelector('.recently-added').scrollIntoView({ behavior: 'smooth' });
  }, 500);
}

// Display recently added books
function displayRecentBooks() {
  const books = JSON.parse(localStorage.getItem('libraryBooks')) || [];
  const recentBooksList = document.getElementById('recentBooksList');

  if (books.length === 0) {
    recentBooksList.innerHTML = '<p class="no-books">No books added yet</p>';
    return;
  }

  // Display only last 6 books
  const recentBooks = books.slice(0, 6);

  recentBooksList.innerHTML = recentBooks.map(book => `
    <div class="book-item">
      <div class="book-item-title">${escapeHtml(book.title)}</div>
      <div class="book-item-author">by ${escapeHtml(book.author)}</div>
      <div class="book-item-genre">${escapeHtml(book.genre)}</div>
      <div class="book-item-meta">
        <span>${book.year}</span>
        <span>${book.pages} pages</span>
        <span>Qty: ${book.quantity}</span>
      </div>
    </div>
  `).join('');
}

// Show message (success or error)
function showMessage(message, type) {
  const successEl = document.getElementById('successMessage');
  const errorEl = document.getElementById('errorMessage');

  if (type === 'success') {
    successEl.style.display = 'block';
    errorEl.style.display = 'none';
    successEl.textContent = message;

    // Auto hide after 3 seconds
    setTimeout(() => {
      successEl.style.display = 'none';
    }, 3000);
  } else {
    errorEl.style.display = 'block';
    successEl.style.display = 'none';
    errorEl.textContent = message;

    // Auto hide after 4 seconds
    setTimeout(() => {
      errorEl.style.display = 'none';
    }, 4000);
  }
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
  const map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return text.replace(/[&<>"']/g, m => map[m]);
}

// Toggle mobile menu
function toggleMenu() {
  const navMenu = document.getElementById('navMenu');
  navMenu.style.display = navMenu.style.display === 'flex' ? 'none' : 'flex';
}

// Load recent books on page load
document.addEventListener('DOMContentLoaded', function() {
  displayRecentBooks();
});
