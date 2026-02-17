# Manage Members Page - OpenLib

A complete CRUD (Create, Read, Update, Delete) system for managing library members, matching the OpenLib registration page theme.

## Features

✅ **Complete CRUD Operations**
- Add new members
- Edit existing members
- Delete members
- View all members in a table

✅ **User-Friendly Interface**
- Beautiful glassmorphism design matching your register page
- Responsive table layout
- Modal dialogs for add/edit operations
- Confirmation dialog for deletions
- Real-time search functionality

✅ **Security Features**
- Password hashing using PHP's password_hash()
- SQL injection prevention using PDO prepared statements
- Email validation
- Form validation (client and server-side)

✅ **Additional Features**
- Role management (User/Admin)
- Status management (Active/Inactive/Suspended)
- Search members by name or email
- Status and role badges with color coding
- Responsive design for mobile devices

## Files Included

1. **manage_members.php** - Main PHP file with all CRUD logic
2. **manage_members.css** - Stylesheet matching your theme
3. **manage_members.js** - JavaScript for modals and search

## Installation & Setup

### Step 1: Database Configuration

The table structure is already provided. Make sure your `users` table exists in the `openlib` database.

### Step 2: Update Database Credentials

Open `manage_members.php` and update the database connection details (lines 7-9):

```php
$host = 'localhost';
$dbname = 'openlib';
$username = 'root';     // Change to your MySQL username
$password = '';         // Change to your MySQL password
```

### Step 3: Upload Files

Upload all three files to your web server in the same directory:
- manage_members.php
- manage_members.css
- manage_members.js

### Step 4: Background Image (Optional)

The CSS references a background image `bg.jpg`. Either:
- Add a `bg.jpg` file in the same directory, OR
- Update line 12 in `manage_members.css` to use your register page background

### Step 5: Authentication (Important!)

The current code has authentication checks commented out. To enable proper security:

1. Uncomment lines 22-26 in `manage_members.php`:
```php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Login/user_login.php");
    exit();
}
```

2. Make sure your login system sets these session variables:
   - `$_SESSION['user_id']`
   - `$_SESSION['role']`

### Step 6: Test the System

1. Navigate to `http://yourdomain.com/path/to/manage_members.php`
2. Try adding a new member
3. Edit an existing member
4. Search for members
5. Delete a member

## Usage Guide

### Adding a Member
1. Click the "Add New Member" button
2. Fill in all required fields
3. Select role (User/Admin) and status (Active/Inactive/Suspended)
4. Click "Save Member"

### Editing a Member
1. Click the edit icon (pencil) next to any member
2. Modify the fields you want to change
3. Leave password blank to keep the current password
4. Click "Save Member"

### Deleting a Member
1. Click the delete icon (trash) next to any member
2. Confirm the deletion in the popup
3. Member will be permanently deleted

### Searching Members
- Type in the search box to filter members by name or email
- Results update in real-time as you type

## Customization

### Colors
Edit `manage_members.css` to change colors:
- Status badges: Lines 178-201
- Role badges: Lines 204-221
- Button colors: Lines 42-50, 224-247

### Table Columns
To add/remove columns, edit:
1. Table headers in `manage_members.php` (lines 91-101)
2. Table data in the foreach loop (lines 105-131)

### Validation Rules
Edit validation in `manage_members.php`:
- `handleAdd()` function (lines 48-90)
- `handleEdit()` function (lines 92-145)

## Security Notes

⚠️ **Important Security Considerations:**

1. **Always use HTTPS** in production
2. **Enable authentication** before deploying
3. **Set proper file permissions** (644 for PHP files)
4. **Use strong database passwords**
5. **Keep PHP and MySQL updated**
6. **Implement rate limiting** for form submissions
7. **Add CSRF protection** for production use

## Browser Compatibility

✅ Chrome/Edge (latest)
✅ Firefox (latest)
✅ Safari (latest)
✅ Mobile browsers (iOS Safari, Chrome Mobile)

## Troubleshooting

### Problem: White page or errors
**Solution:** Check PHP error logs and ensure database credentials are correct

### Problem: Modals not opening
**Solution:** Ensure `manage_members.js` is loaded correctly

### Problem: Styles not applying
**Solution:** Check that `manage_members.css` path is correct and file exists

### Problem: Can't delete members
**Solution:** Check database user has DELETE permissions

### Problem: Search not working
**Solution:** Ensure JavaScript is enabled in browser

## Credits

Design inspired by your OpenLib registration page theme with glassmorphism effects and modern UI elements.

## License

Free to use for your OpenLib project.
