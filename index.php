<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Employee Census - Welcome</title>
<style>
body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: #f0f4f8;
  margin: 0;
  padding: 0;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}
.container {
  background: white;
  padding: 30px 40px;
  border-radius: 10px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  max-width: 480px;
  text-align: center;
}
h2 {
  color: #163eb9;
  margin-bottom: 10px;
}
p {
  color: #333;
  font-size: 15px;
  line-height: 1.5;
}
label {
  display: block;
  margin: 20px 0;
  font-size: 15px;
  color: #222;
}
button {
  background-color: #2563eb;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  font-size: 16px;
  cursor: pointer;
  transition: 0.3s ease;
  margin: 5px;
}
button:hover {
  background-color: #1e40af;
}
#proceedSection {
  display: none;
  margin-top: 15px;
}
footer {
  font-size: 13px;
  color: #777;
  margin-top: 20px;
}
</style>
</head>
<body>
<div class="container">
  <h2>Nyamira County Employee head count</h2>
  <center><img src = "img/log.png" width="80"></center>
  <p>
    <h2><center><font color = "red"><marquee> Fill all the fields correctly</marquee></font></center> </h2> .<br>
   Your personal data is collected for purposes  of staff head count, <br>
  administration and management of staff in accordance with the <br>
  Data Protection Act Cap 411C<br>
  </p>
<b><font color = "red"> Shall not be used for any other purpose whatsoever </font></b><br></center></h2>
  <p><center><font color = "blue" size = "4">
   1. Enable your device camera <br>
   2. Enable your device location
 </font></center> </p>
  <label>
    <input type="checkbox" id="agreeCheckbox" onclick="showProceedOptions()">
    I confirm that the information I provide is true.
  </label>

  <div id="proceedSection">
    <p><strong>Proceed as:</strong></p>
    <button onclick="goTo('register.php')">👷 Employee Registration</button>
    <button onclick="goTo('admin_login.php')">👨‍💼 System Login</button>
  </div>

  <footer>
    © <?= date('Y') ?> Nyamira County Government — PSM Department <br>
    © <?= date('Y') ?> denosoft tech solutions
  </footer>
</div>

<script>
function showProceedOptions() {
  const checkbox = document.getElementById('agreeCheckbox');
  const section = document.getElementById('proceedSection');
  section.style.display = checkbox.checked ? 'block' : 'none';
}
function goTo(page) {
  window.location.href = page;
}
</script>
</body>
</html>
