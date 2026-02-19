// Toggle mobile menu
function toggleMenu() {
  const navMenu = document.getElementById('navMenu');
  navMenu.classList.toggle('active');
}

// Search functionality - ACTIVE
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('searchInput');
  
  if (searchInput) {
    // Search on Enter key press
    searchInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        performSearch();
      }
    });

    // Add search button functionality (if you add a search button later)
    const searchButton = document.getElementById('searchButton');
    if (searchButton) {
      searchButton.addEventListener('click', performSearch);
    }

    // Live search suggestions (optional - shows results as you type)
    let searchTimeout;
    searchInput.addEventListener('input', function() {
      clearTimeout(searchTimeout);
      const searchTerm = this.value.trim();
      
      // Only search if user has typed at least 2 characters
      if (searchTerm.length >= 2) {
        searchTimeout = setTimeout(() => {
          // You can add live search preview here if needed
          console.log('Searching for:', searchTerm);
        }, 500); // Wait 500ms after user stops typing
      }
    });

    // Focus effect
    searchInput.addEventListener('focus', function() {
      this.parentElement.style.transform = 'scale(1.02)';
      this.parentElement.style.transition = 'transform 0.3s';
    });

    searchInput.addEventListener('blur', function() {
      this.parentElement.style.transform = 'scale(1)';
    });
  }

  // Perform search function
  function performSearch() {
    const searchTerm = searchInput.value.trim();
    if (searchTerm) {
      // Show loading state
      searchInput.style.opacity = '0.6';
      searchInput.disabled = true;
      
      // Redirect to borrow books page with search query
      window.location.href = `../Borrow_books/borrow_books.php?search=${encodeURIComponent(searchTerm)}`;
    } else {
      // If empty, just go to borrow books page
      window.location.href = '../Borrow_books/borrow_books.php';
    }
  }

  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }
    });
  });

  // Add animation on scroll
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
  };

  const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
      }
    });
  }, observerOptions);

  // Observe feature cards and book cards
  document.querySelectorAll('.feature-card, .book-card').forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'opacity 0.5s, transform 0.5s';
    observer.observe(card);
  });

  // Search input placeholder animation
  const placeholders = [
    'Search for books, authors, or ISBN...',
    'Try searching "Harry Potter"...',
    'Search by author name...',
    'Find books by genre...'
  ];
  
  let placeholderIndex = 0;
  setInterval(() => {
    if (searchInput && document.activeElement !== searchInput) {
      placeholderIndex = (placeholderIndex + 1) % placeholders.length;
      searchInput.placeholder = placeholders[placeholderIndex];
    }
  }, 3000); // Change placeholder every 3 seconds
});

// Close mobile menu when clicking outside
document.addEventListener('click', function(event) {
  const navMenu = document.getElementById('navMenu');
  const menuToggle = document.querySelector('.menu-toggle');
  
  if (navMenu && menuToggle) {
    if (!navMenu.contains(event.target) && !menuToggle.contains(event.target)) {
      navMenu.classList.remove('active');
    }
  }
});

// Add active class to nav links based on current page
window.addEventListener('load', function() {
  const currentLocation = window.location.pathname;
  const navLinks = document.querySelectorAll('.navbar a');
  
  navLinks.forEach(link => {
    if (link.getAttribute('href') && currentLocation.includes(link.getAttribute('href'))) {
      link.classList.add('active');
    }
  });
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
  // Press '/' to focus search
  if (e.key === '/' && document.activeElement.tagName !== 'INPUT') {
    e.preventDefault();
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
      searchInput.focus();
    }
  }
  
  // Press 'Escape' to clear search
  if (e.key === 'Escape') {
    const searchInput = document.getElementById('searchInput');
    if (searchInput && document.activeElement === searchInput) {
      searchInput.value = '';
      searchInput.blur();
    }
  }
});

// Add visual feedback when hovering over search
const searchContainer = document.querySelector('.search-container');
if (searchContainer) {
  searchContainer.addEventListener('mouseenter', function() {
    this.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.15)';
    this.style.transition = 'box-shadow 0.3s';
  });
  
  searchContainer.addEventListener('mouseleave', function() {
    this.style.boxShadow = 'none';
  });
}