// Tab Switching
function showTab(tabName) {
  // Hide all tab contents
  const tabContents = document.querySelectorAll('.tab-content');
  tabContents.forEach(content => {
    content.classList.remove('active');
  });
  
  // Remove active class from all tab buttons
  const tabBtns = document.querySelectorAll('.tab-btn');
  tabBtns.forEach(btn => {
    btn.classList.remove('active');
  });
  
  // Show selected tab content
  document.getElementById(tabName).classList.add('active');
  
  // Add active class to clicked button
  event.target.closest('.tab-btn').classList.add('active');
}

// Borrow Book Functions
function borrowBook(bookId, bookTitle) {
  document.getElementById('borrow_book_id').value = bookId;
  document.getElementById('borrowMessage').textContent = 
    `Are you sure you want to borrow "${bookTitle}"? You will have 14 days to return it.`;
  document.getElementById('borrowModal').style.display = 'block';
}

function closeBorrowModal() {
  document.getElementById('borrowModal').style.display = 'none';
}

// Return Book Functions
function returnBook(recordId, bookTitle) {
  document.getElementById('return_record_id').value = recordId;
  document.getElementById('returnMessage').textContent = 
    `Are you sure you want to return "${bookTitle}"?`;
  document.getElementById('returnModal').style.display = 'block';
}

function closeReturnModal() {
  document.getElementById('returnModal').style.display = 'none';
}

// View Book Details
function viewDetails(bookId) {
  // In a real implementation, this would fetch book details via AJAX
  // For now, we'll redirect to a details page or show a modal
  alert('Book details functionality coming soon!');
}

// Close modals when clicking outside
window.onclick = function(event) {
  const borrowModal = document.getElementById('borrowModal');
  const returnModal = document.getElementById('returnModal');
  
  if (event.target == borrowModal) {
    closeBorrowModal();
  }
  if (event.target == returnModal) {
    closeReturnModal();
  }
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    setTimeout(() => {
      alert.style.opacity = '0';
      setTimeout(() => {
        alert.style.display = 'none';
      }, 300);
    }, 5000);
  });
});

// Add smooth scroll behavior
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      target.scrollIntoView({
        behavior: 'smooth'
      });
    }
  });
});
