// Toggle password visibility
function togglePassword(inputId) {
  const input = document.getElementById(inputId);
  const icon = document.getElementById(inputId + 'Icon');
  
  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.remove('bx-show');
    icon.classList.add('bx-hide');
  } else {
    input.type = 'password';
    icon.classList.remove('bx-hide');
    icon.classList.add('bx-show');
  }
}

// Preview profile picture before upload
function previewImage(event) {
  const file = event.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const preview = document.getElementById('profilePreview');
      
      // If preview is currently a default avatar div, replace it with an img
      if (preview.classList.contains('default-avatar')) {
        const img = document.createElement('img');
        img.id = 'profilePreview';
        img.src = e.target.result;
        img.alt = 'Profile Picture';
        preview.parentNode.replaceChild(img, preview);
      } else {
        // Just update the src of existing img
        preview.src = e.target.result;
      }
      
      // Auto-submit the form after preview
      setTimeout(() => {
        document.getElementById('photoForm').submit();
      }, 500);
    };
    reader.readAsDataURL(file);
  }
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
  const form = document.querySelector('form[method="POST"]');
  
  if (form && form.action.includes('edit_profile.php')) {
    form.addEventListener('submit', function(e) {
      const newPassword = document.getElementById('newPassword').value;
      const confirmPassword = document.getElementById('confirmPassword').value;
      const currentPassword = document.getElementById('currentPassword').value;
      
      // If user is trying to change password
      if (newPassword || confirmPassword) {
        if (!currentPassword) {
          e.preventDefault();
          alert('Please enter your current password to change your password.');
          document.getElementById('currentPassword').focus();
          return false;
        }
        
        if (newPassword.length < 6) {
          e.preventDefault();
          alert('New password must be at least 6 characters long.');
          document.getElementById('newPassword').focus();
          return false;
        }
        
        if (newPassword !== confirmPassword) {
          e.preventDefault();
          alert('New password and confirm password do not match.');
          document.getElementById('confirmPassword').focus();
          return false;
        }
      }
    });
  }
  
  // Auto-hide alerts after 5 seconds
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    setTimeout(() => {
      alert.style.opacity = '0';
      alert.style.transition = 'opacity 0.3s';
      setTimeout(() => {
        alert.style.display = 'none';
      }, 300);
    }, 5000);
  });
});

// Email validation
function validateEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

// Phone number formatting (optional enhancement)
function formatPhoneNumber(input) {
  // Remove all non-digit characters
  let phone = input.value.replace(/\D/g, '');
  
  // Format as needed (example: xxx-xxx-xxxx)
  if (phone.length >= 6) {
    phone = phone.slice(0, 3) + '-' + phone.slice(3, 6) + '-' + phone.slice(6, 10);
  } else if (phone.length >= 3) {
    phone = phone.slice(0, 3) + '-' + phone.slice(3);
  }
  
  input.value = phone;
}

// Add phone formatting to phone input
const phoneInput = document.querySelector('input[name="phone"]');
if (phoneInput) {
  phoneInput.addEventListener('input', function() {
    formatPhoneNumber(this);
  });
}