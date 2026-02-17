// Modal Functions
let deleteUserId = null;

function openModal(userId = null) {
  const modal = document.getElementById('memberModal');
  const modalTitle = document.getElementById('modalTitle');
  const form = document.getElementById('memberForm');
  const passwordBox = document.getElementById('passwordBox');
  
  if (userId) {
    // Edit mode
    modalTitle.textContent = 'Edit Member';
    document.getElementById('action').value = 'edit';
    document.getElementById('user_id').value = userId;
    
    // Make password optional for editing
    document.getElementById('password').removeAttribute('required');
    document.getElementById('password').placeholder = 'Leave blank to keep current password';
    
    // Fetch user data and populate form
    fetchUserData(userId);
  } else {
    // Add mode
    modalTitle.textContent = 'Add New Member';
    document.getElementById('action').value = 'add';
    form.reset();
    document.getElementById('password').setAttribute('required', 'required');
    document.getElementById('password').placeholder = 'Password';
  }
  
  modal.style.display = 'block';
}

function closeModal() {
  document.getElementById('memberModal').style.display = 'none';
  document.getElementById('memberForm').reset();
}

function openDeleteModal(userId) {
  deleteUserId = userId;
  document.getElementById('deleteModal').style.display = 'block';
}

function closeDeleteModal() {
  deleteUserId = null;
  document.getElementById('deleteModal').style.display = 'none';
}

function confirmDelete() {
  if (deleteUserId) {
    // Create a form and submit it
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'manage_members.php';
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'delete';
    
    const userIdInput = document.createElement('input');
    userIdInput.type = 'hidden';
    userIdInput.name = 'user_id';
    userIdInput.value = deleteUserId;
    
    form.appendChild(actionInput);
    form.appendChild(userIdInput);
    document.body.appendChild(form);
    form.submit();
  }
}

// Fetch user data for editing
function fetchUserData(userId) {
  // This would typically be an AJAX call
  // For now, we'll use the data from the table row
  const row = document.querySelector(`tr[data-user-id="${userId}"]`);
  if (row) {
    const cells = row.cells;
    document.getElementById('f_name').value = cells[1].textContent.split(' ')[0];
    document.getElementById('l_name').value = cells[1].textContent.split(' ').slice(1).join(' ');
    document.getElementById('email').value = cells[2].textContent;
    document.getElementById('role').value = cells[3].textContent.toLowerCase();
    document.getElementById('status').value = cells[4].querySelector('.status-badge').textContent.toLowerCase();
  }
}

// Search functionality
function searchMembers() {
  const input = document.getElementById('searchInput');
  const filter = input.value.toLowerCase();
  const table = document.getElementById('membersTableBody');
  const rows = table.getElementsByTagName('tr');
  
  for (let i = 0; i < rows.length; i++) {
    const nameCell = rows[i].getElementsByTagName('td')[1];
    const emailCell = rows[i].getElementsByTagName('td')[2];
    
    if (nameCell || emailCell) {
      const nameText = nameCell.textContent || nameCell.innerText;
      const emailText = emailCell.textContent || emailCell.innerText;
      
      if (nameText.toLowerCase().indexOf(filter) > -1 || 
          emailText.toLowerCase().indexOf(filter) > -1) {
        rows[i].style.display = '';
      } else {
        rows[i].style.display = 'none';
      }
    }
  }
}

// Close modal when clicking outside
window.onclick = function(event) {
  const memberModal = document.getElementById('memberModal');
  const deleteModal = document.getElementById('deleteModal');
  
  if (event.target == memberModal) {
    closeModal();
  }
  if (event.target == deleteModal) {
    closeDeleteModal();
  }
}
