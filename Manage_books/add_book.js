// Modal Functions
let deleteBookId = null;

function openModal(bookId = null) {
  const modal = document.getElementById('bookModal');
  const modalTitle = document.getElementById('modalTitle');
  const form = document.getElementById('bookForm');
  
  if (bookId) {
    // Edit mode
    modalTitle.textContent = 'Edit Book';
    document.getElementById('action').value = 'edit';
    document.getElementById('book_id').value = bookId;
    
    // Fetch book data and populate form
    fetchBookData(bookId);
  } else {
    // Add mode
    modalTitle.textContent = 'Add New Book';
    document.getElementById('action').value = 'add';
    form.reset();
    document.getElementById('imagePreview').innerHTML = '';
  }
  
  modal.style.display = 'block';
}

function closeModal() {
  document.getElementById('bookModal').style.display = 'none';
  document.getElementById('bookForm').reset();
  document.getElementById('imagePreview').innerHTML = '';
}

function openDeleteModal(bookId) {
  deleteBookId = bookId;
  document.getElementById('deleteModal').style.display = 'block';
}

function closeDeleteModal() {
  deleteBookId = null;
  document.getElementById('deleteModal').style.display = 'none';
}

function confirmDelete() {
  if (deleteBookId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'add_book.php';
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'delete';
    
    const bookIdInput = document.createElement('input');
    bookIdInput.type = 'hidden';
    bookIdInput.name = 'book_id';
    bookIdInput.value = deleteBookId;
    
    form.appendChild(actionInput);
    form.appendChild(bookIdInput);
    document.body.appendChild(form);
    form.submit();
  }
}

// Fetch book data for editing
function fetchBookData(bookId) {
  const bookCard = document.querySelector(`[data-book-id="${bookId}"]`);
  if (!bookCard) return;
  
  // Get data from the card
  const title = bookCard.querySelector('.book-title').textContent;
  const author = bookCard.querySelector('.book-author').textContent.replace('by ', '');
  const genre = bookCard.querySelector('.genre-badge').textContent;
  const year = bookCard.querySelector('.year-badge').textContent;
  const pagesStat = bookCard.querySelector('.book-stats .stat:nth-child(1) span').textContent;
  const pages = pagesStat.split(' ')[0];
  const quantityStat = bookCard.querySelector('.book-stats .stat:nth-child(2) span').textContent;
  const quantities = quantityStat.split('/');
  const quantity = quantities[1].split(' ')[0];
  
  // Populate form
  document.getElementById('title').value = title;
  document.getElementById('author').value = author;
  document.getElementById('genre').value = genre;
  document.getElementById('publication_year').value = year;
  document.getElementById('pages').value = pages;
  document.getElementById('quantity').value = quantity;
  
  // Get image if exists
  const img = bookCard.querySelector('.book-image img');
  if (img) {
    document.getElementById('existing_cover').value = img.src;
    document.getElementById('imagePreview').innerHTML = `<img src="${img.src}" alt="Current cover">`;
  }
}

// Edit book
function editBook(bookId) {
  openModal(bookId);
}

// View book details
function viewBook(bookId) {
  const bookCard = document.querySelector(`[data-book-id="${bookId}"]`);
  if (!bookCard) return;
  
  const title = bookCard.querySelector('.book-title').textContent;
  const author = bookCard.querySelector('.book-author').textContent.replace('by ', '');
  const genre = bookCard.querySelector('.genre-badge').textContent;
  const year = bookCard.querySelector('.year-badge').textContent;
  const pagesStat = bookCard.querySelector('.book-stats .stat:nth-child(1) span').textContent;
  const pages = pagesStat.split(' ')[0];
  const quantityStat = bookCard.querySelector('.book-stats .stat:nth-child(2) span').textContent;
  
  const img = bookCard.querySelector('.book-image img');
  let imageHtml = '';
  if (img) {
    imageHtml = `<img src="${img.src}" alt="${title}">`;
  } else {
    imageHtml = '<div class="no-image"><i class="bx bx-book"></i></div>';
  }
  
  const viewContent = `
    <div class="book-view">
      ${imageHtml}
      <h3>${title}</h3>
      <p style="color: #666; font-style: italic; margin-bottom: 20px;">by ${author}</p>
      
      <div class="view-details">
        <div class="detail-row">
          <span class="detail-label">Genre:</span>
          <span class="detail-value">${genre}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Publication Year:</span>
          <span class="detail-value">${year}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Pages:</span>
          <span class="detail-value">${pages}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Availability:</span>
          <span class="detail-value">${quantityStat}</span>
        </div>
      </div>
    </div>
  `;
  
  document.getElementById('viewContent').innerHTML = viewContent;
  document.getElementById('viewModal').style.display = 'block';
}

function closeViewModal() {
  document.getElementById('viewModal').style.display = 'none';
}

// Image preview
function previewImage(event) {
  const file = event.target.files[0];
  const preview = document.getElementById('imagePreview');
  
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
    };
    reader.readAsDataURL(file);
  } else {
    preview.innerHTML = '';
  }
}

// Search functionality
function searchBooks() {
  const input = document.getElementById('searchInput');
  const filter = input.value.toLowerCase();
  const bookCards = document.querySelectorAll('.book-card');
  
  bookCards.forEach(card => {
    const title = card.querySelector('.book-title').textContent.toLowerCase();
    const author = card.querySelector('.book-author').textContent.toLowerCase();
    const isbn = card.getAttribute('data-isbn') || '';
    
    if (title.indexOf(filter) > -1 || author.indexOf(filter) > -1 || isbn.indexOf(filter) > -1) {
      card.style.display = '';
    } else {
      card.style.display = 'none';
    }
  });
}

// Close modals when clicking outside
window.onclick = function(event) {
  const bookModal = document.getElementById('bookModal');
  const viewModal = document.getElementById('viewModal');
  const deleteModal = document.getElementById('deleteModal');
  
  if (event.target == bookModal) {
    closeModal();
  }
  if (event.target == viewModal) {
    closeViewModal();
  }
  if (event.target == deleteModal) {
    closeDeleteModal();
  }
}
