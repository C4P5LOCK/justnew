<?php #  - register.php
 // This is the registration page for the site.

 session_start();

 
 if (isset($_POST['submitted'])) { // Handle the form.

 require_once ('includes/mysqli_connect.php');

 // Trim all the incoming data:
 $trimmed = array_map('trim', $_POST);

 // Assume invalid values:
 $fn = $ln = $e = $p = $g = $r =FALSE;

 // Check for a first name:
 if (preg_match ('/^[A-Z \'.-]{2,20}$/i',$trimmed['firstname'])) {
 $fn = mysqli_real_escape_string ($dbc,$trimmed['firstname']);
 } else {
 echo '<p class="error">Please enter your first name!</p>';
 }

 // Check for a last name:
 if (preg_match ('/^[A-Z \'.-]{2,40}$/i',$trimmed['lastname'])) {
$ln = mysqli_real_escape_string ($dbc,$trimmed['lastname']);
 } else {
 echo '<p class="error">Please enter your last name!</p>';
 }

 // Check for an email address:
 if (preg_match ('/^[\w.-]+@[\w.-]+\.[AZa-z]{2,6}$/',$trimmed['email'])) {
$e = mysqli_real_escape_string ($dbc,$trimmed['email']);
 } else {
 echo '<p class="error">Please enter a valid email address!</p>';
 }

 /* Validation to check if gender is selected */

	if(isset($_POST["gender"])) {
		$g= mysqli_real_escape_string($dbc, $trimmed['gender']);
	}else{
		echo '<p class="error">Please select gender</p>';
	}
	
 // Check for a password and match against the confirmed password:
 if (preg_match ('/^\w{4,20}$/',$trimmed['password1']) ) {
 if ($trimmed['password1'] == $trimmed['password2']) {
 $p = mysqli_real_escape_string($dbc, $trimmed['password1']);
 } else {
 echo '<p class="error">Your password did not match the confirmed password!</p>';
 }
 } else {
 echo '<p class="error">Please enter a valid password!</p>';
 }

 //FILES HANDLING ****************************************************************************
 if(!isset($_FILES['resume']['name']))
	{
		$errors[] = "Please select a file";
	}
	
 if($_FILES['resume']['error']>0)
	{
		$errors[] = "error uploading file";
	}
	
	if(file_exists("uploads/".$_FILES['resume']['name']))
	{
		$errors[] = "this file is already uploaded.";
	}
	

	if(!(strtoupper(substr($_FILES['resume']['name'],-7))=='.docx'))
		
	if(!(strtoupper(substr($_FILES['resume']['name'], -7)) == ".MSWORD" || strtoupper(substr($_FILES['resume']['name'], -4)) == ".PDF" 
		|| strtoupper(substr($_FILES['resume']['name'], -5)) == ".DOCX" || strtoupper(substr($_FILES['resume']['name'], -4)) == ".DOC"))
	{
		$errors[] = "wrong file type";
	}
	move_uploaded_file($_FILES['resume']['tmp_name'],"uploads/".$_FILES['resume']['name']);
 
   $path = "uploads/".$_FILES['resume']['name'];
	
 
  // If everything's OK... 
 if ($fn && $ln && $e && $g && $p && $path ) { 

  // Make sure the email address is available So checkup first:
 $q = "SELECT employee_id FROM employees WHERE email='$e'";
 $r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));

 if (mysqli_num_rows($r) == 0) { // if Available i.e No user with such mail.

 // Create the activation code that will be sent to tthe user:
 $a = md5(uniqid(rand(), true));

 // Add the user to the database:
 $q = "INSERT INTO employees (email,password, firstname, lastname, resume, active,gender,registeration_date) VALUES ('$e',SHA1('$p'), '$fn', '$ln','$path','$a','" . $_POST["gender"] . "',NOW() )";
 $r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));

 if (mysqli_affected_rows($dbc) == 1)
{ // If it ran OK.

 // Send the email to the user:
 $body = "Thank you for registering. To activate your account, please click on this link:\n\n";
 $body .= BASE_URL . 'activate.php?x=' . urlencode($e) . "&y=$a";
 mail($trimmed['email'],'Registration Confirmation', $body,'From: admin@jobhunter.com');

 // Finish the page: 
 echo '<h3>Thank you for registering! A confirmation email has been sent to your address. Please click on the link in that email in order to activate your
account.</h3>';

//include ('includes/footer.html');   $body .= BASE_URL .'activate.php?x=' .urlencode($e) . "&y=$a";
// Include the HTML footer.
 exit(); // Stop the page.

 } else { // If it did not run OK.
 echo '<p class="error">You could not be registered due to a system error. We apologize for any inconvenience.</p>';
 }

 } else { // The email address is not available.
echo '<p class="error">That email address has already been registered. If you have forgotten your password, use the password reset link.</p>';
 }

 } else { // If one of the data tests failed.
 echo '<p class="error">Please re-enter your passwords and try again.</p>';
 }

 mysqli_close($dbc);

 } // End of the main Submit conditional.
 ?>

 <h1>Register</h1>
 <form enctype="multipart/form-data" action="register.php" method="post">
 <fieldset>

 <p><b>First Name:</b> <input type="text" name="firstname" size="20" maxlength="20" value="<?php if (isset($trimmed['first_name'])) echo $trimmed
['first_name']; ?>" required/></p>

 <p><b>Last Name:</b> <input type="text" name="lastname" size="20" maxlength="40" value="<?php if (isset($trimmed['last_name'])) echo $trimmed
['last_name']; ?>" /></p>

 <p><b>Email Address:</b> <input type="text" name="email" size="30" maxlength="80" value="<?php if(isset($trimmed['email'])) echo
$trimmed['email']; ?>" /> </p>

<P>Gender</p><input type="radio" name="gender" value="Male" <?php if(isset($_POST['gender']) && $_POST['gender']=="Male") { ?>checked<?php  } ?>> Male
<input type="radio" name="gender" value="Female" <?php if(isset($_POST['gender']) && $_POST['gender']=="Female") { ?>checked<?php  } ?>> Female

 <p><b>Password:</b> <input type="password" name="password1" size="20" maxlength="20" /> <small>Use only letters, numbers, and the underscore. Must be between 4 and 20 characters long.</small></p>

 <p><b>Confirm Password:</b> <input type="password" name="password2" size="20" maxlength="20" /></p>
 <P>RESUME :<input type="file" method="file" name="resume" style="width:200px;">

 
 



</fieldset>

 <div align="center"><input type="submit" name="submit" value="Register" /></div>
 <input type="hidden" name="submitted" value="TRUE" />

 </form>

 