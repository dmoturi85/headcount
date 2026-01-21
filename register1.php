<?php
$conn = new mysqli("localhost", "root", "", "employee_census");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = $_POST['full_name'];
    $id_number = $_POST['id_number'];
    $personal_number = $_POST['personal_number'];
    $gender = $_POST['gender'];
    $disability_status = $_POST['disability_status'];
    $disability_type = $_POST['disability_type'] ?? '';
    $date_of_appointment = $_POST['date_of_appointment'];
    $job_group = $_POST['job_group'];
    $highest_academic_qualification = $_POST['highest_academic_qualification'];
    $professional_qualification = $_POST['professional_qualification'];
    $department = $_POST['department'];
    $section = $_POST['section'];
    $home_county = $_POST['home_county'];
    $sub_county = $_POST['sub_county'];
    $ward = $_POST['ward'];
    $work_station = $_POST['work_station'];
    $phone_number = $_POST['phone_number'];
    $email = $_POST['email'];
    $photo = $_POST['photo_data'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    $stmt = $conn->prepare("INSERT INTO employees (
        full_name, id_number, personal_number, gender, disability_status, disability_type,
        date_of_appointment, job_group, highest_academic_qualification, professional_qualification,
        department, section, home_county, sub_county, ward, work_station, phone_number,
        email, photo, latitude, longitude
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssssssssssssss",
        $full_name, $id_number, $personal_number, $gender, $disability_status, $disability_type,
        $date_of_appointment, $job_group, $highest_academic_qualification, $professional_qualification,
        $department, $section, $home_county, $sub_county, $ward, $work_station, $phone_number,
        $email, $photo, $latitude, $longitude
    );

    if ($stmt->execute()) {
        echo "<script>alert('Employee Registered Successfully!'); //window.location='view_employees.php';</script>";
    } else {
        echo "<script>alert('Registration Failed. Try Again.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Employee Registration - Nyamira County</title>
<style>
body {
  font-family: Arial, sans-serif;
  background-color: #f5f7fa;
  margin: 0;
}
form {
  background: white;
  max-width: 900px;
  margin: 20px auto;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
h2 {
  text-align: center;
  color: #163eb9;
}
label { display: block; margin-top: 10px; font-weight: bold; }
input, select {
  width: 100%;
  padding: 8px;
  margin-top: 4px;
  border: 1px solid #ccc;
  border-radius: 4px;
}
button {
  background-color: #2563eb;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  margin-top: 15px;
}
button:hover { background-color: #1d4ed8; }
#photo-preview {
  width: 120px; height: 120px;
  border: 2px dashed #2563eb;
  border-radius: 8px;
  display: flex; justify-content: center; align-items: center;
  margin-top: 10px;
}
#photo-preview img {
  width: 100%; height: 100%;
  object-fit: cover;
  border-radius: 8px;
}
</style>
</head>
<body>
<form method="POST" onsubmit="return validateForm()">
  <h2>Employee Registration</h2>

  <label>Full Name</label>
  <input type="text" name="full_name" required>

  <label>ID Number</label>
  <input type="text" name="id_number" id="id_number" required pattern="\d{7,8}" title="Enter a valid ID (7-8 digits)">

  <label>Personal Number</label>
  <input type="text" name="personal_number" required>

  <label>Gender</label>
  <select name="gender" required>
    <option value="">--Select Gender--</option>
    <option>Male</option>
    <option>Female</option>
  </select>

  <label>Disability Status</label>
  <select name="disability_status" id="disability_status" onchange="toggleDisabilityType()" required>
    <option value="">--Select--</option>
    <option value="Not Disabled">Not Disabled</option>
    <option value="Disabled">Disabled</option>
  </select>

  <div id="disability_type_container" style="display:none;">
    <label>Type of Disability</label>
    <select name="disability_type">
      <option value="">--Select Type--</option>
      <option value="Physical">Physical</option>
      <option value="Visual">Visual</option>
      <option value="Hearing">Hearing</option>
      <option value="Mental">Mental</option>
      <option value="Others">Others</option>
    </select>
  </div>

  <label>Date of Appointment</label>
  <input type="date" name="date_of_appointment" required>

  <label>Job Group</label>
  <input type="text" name="job_group" required>

  <label>Highest Academic Qualification</label>
  <input type="text" name="highest_academic_qualification" required>

  <label>Professional Qualification</label>
  <input type="text" name="professional_qualification" required>

  <label>Department</label>
  <input type="text" name="department" required>

  <label>Section</label>
  <input type="text" name="section" required>

  <label>Home County</label>
  <input type="text" name="home_county" required>

  <label>Sub-County</label>
  <input type="text" name="sub_county" required>

  <label>Ward</label>
  <input type="text" name="ward" required>

  <label>Work Station</label>
  <input type="text" name="work_station" required>

  <label>Phone Number</label>
  <input type="tel" name="phone_number" id="phone_number" required pattern="^07\d{8}$" title="Enter valid Kenyan phone e.g., 07XXXXXXXX">

  <label>Email</label>
  <input type="email" name="email" required>

  <hr>

  <h3>Auto Location Capture</h3>
  <label>Latitude</label>
  <input type="text" id="latitude" name="latitude" readonly required>

  <label>Longitude</label>
  <input type="text" id="longitude" name="longitude" readonly required>

  <button type="button" onclick="captureLocation()">📍 Get Current Location</button>

  <hr>

  <h3>Photo Capture</h3>
  <div id="photo-preview">No photo</div>
  <button type="button" onclick="capturePhoto()">📸 Capture Photo</button>
  <input type="hidden" name="photo_data" id="photo_data">

  <br>
  <button type="submit">Register Employee</button>
</form>

<script>
function captureLocation() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition((pos) => {
      document.getElementById('latitude').value = pos.coords.latitude.toFixed(6);
      document.getElementById('longitude').value = pos.coords.longitude.toFixed(6);
      alert("Location captured successfully!");
    }, () => alert("Unable to get location. Allow GPS access."));
  } else {
    alert("Geolocation not supported by this browser.");
  }
}

function toggleDisabilityType() {
  const status = document.getElementById('disability_status').value;
  document.getElementById('disability_type_container').style.display =
    (status === 'Disabled') ? 'block' : 'none';
}

function capturePhoto() {
  const video = document.createElement('video');
  const canvas = document.createElement('canvas');
  const preview = document.getElementById('photo-preview');
  const context = canvas.getContext('2d');

  navigator.mediaDevices.getUserMedia({ video: true })
    .then((stream) => {
      video.srcObject = stream;
      video.play();
      preview.innerHTML = '';
      preview.appendChild(video);

      setTimeout(() => {
        canvas.width = 120; canvas.height = 120;
        context.drawImage(video, 0, 0, 120, 120);
        const photoData = canvas.toDataURL('image/png');
        document.getElementById('photo_data').value = photoData;
        preview.innerHTML = `<img src="${photoData}" alt="Photo Preview">`;
        stream.getTracks().forEach(track => track.stop());
      }, 3000); // 3-second delay before capture
    })
    .catch(err => alert("Camera access denied or unavailable."));
}

function validateForm() {
  const phone = document.getElementById('phone_number').value;
  const id = document.getElementById('id_number').value;
  const photo = document.getElementById('photo_data').value;
  const lat = document.getElementById('latitude').value;
  const lon = document.getElementById('longitude').value;

  if (!photo) {
    alert("Please capture a photo before submitting.");
    return false;
  }
  if (!lat || !lon) {
    alert("Please capture location before submitting.");
    return false;
  }
  if (!/^07\d{8}$/.test(phone)) {
    alert("Invalid phone format. Must be 07XXXXXXXX.");
    return false;
  }
  if (!/^\d{7,8}$/.test(id)) {
    alert("Invalid ID format.");
    return false;
  }
  return true;
}
</script>
</body>
</html>
