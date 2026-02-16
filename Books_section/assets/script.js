// Sample books database
const books = [
    { id: 1, title: "The Great Gatsby", author: "F. Scott Fitzgerald", genre: "Fiction", available: true },
    { id: 2, title: "To Kill a Mockingbird", author: "Harper Lee", genre: "Fiction", available: true },
    { id: 3, title: "1984", author: "George Orwell", genre: "Fiction", available: false },
    { id: 4, title: "Pride and Prejudice", author: "Jane Austen", genre: "Fiction", available: true },
    { id: 5, title: "The Catcher in the Rye", author: "J.D. Salinger", genre: "Fiction", available: true },
    { id: 6, title: "Sapiens", author: "Yuval Noah Harari", genre: "Non-Fiction", available: true },
    { id: 7, title: "Educated", author: "Tara Westover", genre: "Biography", available: true },
    { id: 8, title: "A Brief History of Time", author: "Stephen Hawking", genre: "Science", available: false },
    { id: 9, title: "The Selfish Gene", author: "Richard Dawkins", genre: "Science", available: true },
    { id: 10, title: "The Silk Roads", author: "Peter Frankopan", genre: "History", available: true },
    { id: 11, title: "Cosmos", author: "Carl Sagan", genre: "Science", available: true },
    { id: 12, title: "Steve Jobs", author: "Walter Isaacson", genre: "Biography", available: true }
];

// Local storage for users
let users = JSON.parse(localStorage.getItem('users')) || [];
let currentUser = JSON.parse(localStorage.getItem('currentUser')) || null;
let contacts = JSON.parse(localStorage.getItem('contacts')) || [];

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    renderBooks();
    setupNavigation();
    updateAuthUI();
});

// Render books in catalog
function renderBooks() {
    const booksList = document.getElementById('booksList');
    if (!booksList) return;
    
    booksList.innerHTML = books.map(book => `
        <div class="book-card" data-genre="${book.genre}">
            <div class="book-cover">📖</div>
            <h3 class="book-title">${book.title}</h3>
            <p class="book-author">by ${book.author}</p>
            <p class="book-genre">${book.genre}</p>
            <p class="book-availability ${book.available ? 'available' : 'unavailable'}">
                ${book.available ? '✓ Available' : '✗ Unavailable'}
            </p>
            <button class="borrow-btn" ${!book.available ? 'disabled' : ''} 
                onclick="borrowBook(${book.id})">
                ${book.available ? 'Borrow' : 'Unavailable'}
            </button>
        </div>
    `).join('');
}

// Filter books
function filterBooks() {
    const searchInput = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const genreFilter = document.getElementById('genreFilter')?.value || '';
    const cards = document.querySelectorAll('.book-card');
    
    cards.forEach(card => {
        const title = card.querySelector('.book-title').textContent.toLowerCase();
        const author = card.querySelector('.book-author').textContent.toLowerCase();
        const genre = card.getAttribute('data-genre');
        
        const matchesSearch = title.includes(searchInput) || author.includes(searchInput);
        const matchesGenre = !genreFilter || genre === genreFilter;
        
        card.style.display = (matchesSearch && matchesGenre) ? 'block' : 'none';
    });
}

// Search books
function searchBooks() {
    filterBooks();
    document.getElementById('catalog').scrollIntoView({ behavior: 'smooth' });
}

// Borrow book
function borrowBook(bookId) {
    if (!currentUser) {
        alert('Please login first to borrow books');
        document.getElementById('login').scrollIntoView({ behavior: 'smooth' });
        return;
    }
    alert(`Book borrowed successfully! Return within 14 days.`);
}

// Setup navigation links
function setupNavigation() {
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

// Update auth UI based on login status
function updateAuthUI() {
    const navbar = document.querySelector('.navbar');
    if (currentUser) {
        const userBtn = document.createElement('button');
        userBtn.className = 'user-btn';
        userBtn.innerHTML = `${currentUser.firstName} <button onclick="handleLogout()">Logout</button>`;
        navbar.querySelector('.nav-menu').appendChild(userBtn);
    }
}

// Handle registration
function handleRegister(event) {
    event.preventDefault();
    
    const firstName = document.getElementById('firstName').value;
    const lastName = document.getElementById('lastName').value;
    const email = document.getElementById('registerEmail').value;
    const phone = document.getElementById('phone').value;
    const password = document.getElementById('registerPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    // Validation
    if (!firstName || !lastName) {
        showMessage('registerMessage', 'Please enter your full name', 'error');
        return;
    }
    
    if (!validateEmail(email)) {
        showMessage('registerMessage', 'Please enter a valid email', 'error');
        return;
    }
    
    if (users.find(u => u.email === email)) {
        showMessage('registerMessage', 'Email already registered', 'error');
        return;
    }
    
    if (password.length < 6) {
        showMessage('registerMessage', 'Password must be at least 6 characters', 'error');
        return;
    }
    
    if (password !== confirmPassword) {
        showMessage('registerMessage', 'Passwords do not match', 'error');
        return;
    }
    
    if (!validatePhone(phone)) {
        showMessage('registerMessage', 'Please enter a valid phone number', 'error');
        return;
    }
    
    // Save user
    const newUser = {
        id: Date.now(),
        firstName,
        lastName,
        email,
        phone,
        password: btoa(password) // Simple encoding (NOT secure for production)
    };
    
    users.push(newUser);
    localStorage.setItem('users', JSON.stringify(users));
    
    showMessage('registerMessage', 'Registration successful! Redirecting to login...', 'success');
    
    setTimeout(() => {
        document.getElementById('registerForm').reset();
        document.getElementById('login').scrollIntoView({ behavior: 'smooth' });
    }, 1500);
}

// Handle login
function handleLogin(event) {
    event.preventDefault();
    
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    
    const user = users.find(u => u.email === email && u.password === btoa(password));
    
    if (user) {
        currentUser = user;
        localStorage.setItem('currentUser', JSON.stringify(user));
        showMessage('loginMessage', 'Login successful!', 'success');
        
        setTimeout(() => {
            document.getElementById('loginForm').reset();
            updateAuthUI();
            alert(`Welcome back, ${user.firstName}!`);
        }, 1000);
    } else {
        showMessage('loginMessage', 'Invalid email or password', 'error');
    }
}

// Handle logout
function handleLogout() {
    currentUser = null;
    localStorage.removeItem('currentUser');
    location.reload();
}

// Handle contact form
function handleContactSubmit(event) {
    event.preventDefault();
    
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const message = document.getElementById('message').value;
    
    if (!validateEmail(email)) {
        showMessage('contactMessage', 'Please enter a valid email', 'error');
        return;
    }
    
    const contact = {
        id: Date.now(),
        name,
        email,
        message,
        date: new Date().toLocaleString()
    };
    
    contacts.push(contact);
    localStorage.setItem('contacts', JSON.stringify(contacts));
    
    showMessage('contactMessage', 'Message sent successfully! We will contact you soon.', 'success');
    document.getElementById('contactForm').reset();
}

// Validate email
function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Validate phone
function validatePhone(phone) {
    const regex = /^[\d\-\+\s\(\)]{10,}$/;
    return regex.test(phone.trim());
}

// Show message
function showMessage(elementId, message, type) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = message;
        element.className = `message ${type}`;
        element.style.display = 'block';
    }
}
