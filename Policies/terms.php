<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Terms & Policy | OpenLib</title>
  <link rel="stylesheet" href="terms.css">
</head>
<body>

  <div class="terms-container">
    <h2>Terms & Conditions & Privacy Policy</h2>
    <form>
        <fieldset>
            <p>
            By registering for and using this library system, you agree to provide accurate and complete information
            and to keep your login credentials confidential. You are fully responsible for all activities performed
            under your account.
            </p>

            <p>
            All books and digital resources are provided strictly for educational and personal use. Borrowed books
            must be returned on or before the due date. Failure to do so may result in penalties or temporary
            suspension of your account.
            </p>

            <p>
            Illegal sharing of copyrighted content, misuse of the system, hacking, data manipulation, and any
            abusive or harmful behavior are strictly prohibited and may lead to immediate account termination.
            </p>
            <p>
            The library reserves the right to suspend or terminate any account that violates these terms and policies.
            </p>
        </fieldset>
    </form>

    

    <!-- Checkbox -->
    <div class="checkbox-group">
      <input type="checkbox" id="agreeCheck" onclick="toggleButtons()">
      <label for="agreeCheck">
        I have read and agree to the Terms & Conditions and Privacy Policy.
      </label>
    </div>

    <!-- Buttons -->
    <div class="btn-group">
      <button class="agree-btn" id="acceptBtn" disabled onclick="acceptTerms()">Accept</button>
      <button class="decline-btn" onclick="declineTerms()">Decline</button>
    </div>
  </div>

  <script>
    function toggleButtons() {
      const checkbox = document.getElementById("agreeCheck");
      const acceptBtn = document.getElementById("acceptBtn");

      acceptBtn.disabled = !checkbox.checked;
    }

    function acceptTerms() {
      window.location.href = "../Login/user_login.php"; // change to your dashboard page
    }

    function declineTerms() {
      alert("You must accept the terms to continue using the system.");
      window.location.href = "../Register/register.php"; // change to your register page
    }
  </script>

</body>
</html>
