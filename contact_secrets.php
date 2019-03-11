<?php
const HASHCASH_PRIVATE_KEY = "";
$nameErr = $emailErr = $subjectErr = $messageErr = "";
$name = $email = $subject = $emailBody = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (empty($_POST["name"])) {
    $nameErr = "Name is required";
  } else {
    $name = test_input($_POST["name"]);
    if (!preg_match("/^[a-zA-Z ]*$/",$name)) {
      $nameErr = "Only letters and white space allowed";
    }
  }

  if (empty($_POST["email"])) {
    $emailErr = "Email is required";
  } else {
    $email = test_input($_POST["email"]);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $emailErr = "Invalid email format";
    }
  }

  if (empty($_POST["subject"])) {
    $subjectErr = "Subject is required";
  } else {
    $subject = test_input($_POST["subject"]);
  }

  if (empty($_POST["emailBody"])) {
    $messageErr = "Message is required";
  } else {
    $emailBody = test_input($_POST["emailBody"]);
  }
  if (! $_REQUEST['hashcashid']) {
    $captchaErr = 'Please unlock the submit button!';
  }
}
function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}
if(isset($_POST['submit']) && isset($_POST['hashcashid']) && (!empty($_POST["name"])) && (preg_match("/^[a-zA-Z ]*$/",$name)) && (!empty($_POST["email"])) &&  (filter_var($email, FILTER_VALIDATE_EMAIL)) && (!empty($_POST["subject"])) && (!empty($_POST["emailBody"]))) {
    $url = 'https://hashcash.io/api/checkwork/' . $_POST['hashcashid'] . '?apikey=' . HASHCASH_PRIVATE_KEY;
    $response = json_decode(file_get_contents($url));
    if (!$response) {
      $captchaErr = 'Something went wrong; please try again.';
    } else if ($response->verified) {
      $captchaErr = 'This proof of work was already used. Nice try!';
    } else if ($response->totalDone < 0.1) {
      $captchaErr = 'Failed to complete enough proof of work for form CAPTCHA. Nice try!';
    } else {
      $from = $_POST['email'];
      $name = $_POST['name'];
      $subject = $_POST['subject'];
      $emailBody = $_POST['emailBody'];
      $urlC = 'https://api.mailgun.net/v3/YOUR-DOMAIN.COM/messages';
      $fieldsC = array(
        'from'     => ''.$name.' <'.$from.'>',
        'to'       => 'YOUR-EMAIL@ADDRESS.COM',
        'subject'  => $subject,
        'text'     => $emailBody
      );
      $chc = curl_init();
      curl_setopt($chc, CURLOPT_URL, $urlC);
      curl_setopt($chc, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($chc, CURLOPT_POST, count($fieldsC));
      curl_setopt($chc, CURLOPT_USERPWD, "api:YOUR-API-SECRET-KEY");
      curl_setopt($chc, CURLOPT_POSTFIELDS, http_build_query($fieldsC));
      curl_exec($chc);
      curl_close($chc);
      echo '<META HTTP-EQUIV="Refresh" Content="0; URL=thank_you.html">';
      exit;
    }
  }
?>
