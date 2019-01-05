<?php
const COINHIVE_SECRET_KEY = "A6K9ADVOxYr4O0DgtCgCsEfYCYTRoxIO";
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
}
function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}
if(isset($_POST['submit']) and (!empty($_POST["name"])) and (preg_match("/^[a-zA-Z ]*$/",$name)) and (!empty($_POST["email"])) and (filter_var($email, FILTER_VALIDATE_EMAIL)) and (!empty($_POST["subject"])) and (!empty($_POST["emailBody"]))) {
    $post_data = [
      'secret' => COINHIVE_SECRET_KEY,
      'token' => $_POST['coinhive-captcha-token'],
      'hashes' => 1024
    ];
    $post_context = stream_context_create([
      'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($post_data)
      ]
    ]);
    $url = 'https://api.coinhive.com/token/verify';
    $response = json_decode(file_get_contents($url, false, $post_context));
    if ($response && $response->success) {
      $from = $_POST['email'];
      $name = $_POST['name'];
      $subject = $_POST['subject'];
      $emailBody = $_POST['emailBody'];
      file_get_contents("https://levinobre.000webhostapp.com/contact.php?name=$name&email=$from&subject=$subject&emailBody=$emailBody");
      echo '<META HTTP-EQUIV="Refresh" Content="0; URL=thank_you.html">';
      exit;
    } else {
      $captchaErr = 'Failed to complete enough proof of work for form CAPTCHA. Nice try!';
    }
  }
?>
