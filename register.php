<?php
// ======================
// Employee Registration
// ======================
$conn = new mysqli("localhost", "root", "", "employee_census");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Collect form data safely
    $full_name = $_POST['full_name'] ?? '';
    $id_number = $_POST['id_number'] ?? '';
    $personal_number = $_POST['personal_number'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $disability_status = $_POST['disability_status'] ?? '';
    $disability_type = $_POST['disability_type'] ?? '';
    $date_of_appointment = $_POST['date_of_appointment'] ?? '';
    $job_group = $_POST['job_group'] ?? '';
    $highest_academic_qualification = $_POST['highest_academic_qualification'] ?? '';
    $professional_qualification = $_POST['professional_qualification'] ?? '';
    $department = $_POST['department'] ?? '';
    $section = $_POST['section'] ?? '';
    $home_county = $_POST['home_county'] ?? '';
    $subcounty = $_POST['subcounty'] ?? '';
    $ward = $_POST['ward'] ?? '';
    $workstation = $_POST['workstation'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $email = $_POST['email'] ?? '';
    $latitude = $_POST['latitude'] ?? '';
    $longitude = $_POST['longitude'] ?? '';
    $photo_data = $_POST['photo_data'] ?? '';

    if ($subcounty && $phone_number) {
        $stmt = $conn->prepare("INSERT INTO employees 
            (full_name, id_number, personal_number, gender, disability_status, disability_type, date_of_appointment,
            job_group, highest_academic_qualification, professional_qualification, department, section,
            home_county, subcounty, ward, work_station, phone_number, email, latitude, longitude, photo)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "sssssssssssssssssssss",
            $full_name, $id_number, $personal_number, $gender, $disability_status, $disability_type,
            $date_of_appointment, $job_group, $highest_academic_qualification, $professional_qualification,
            $department, $section, $home_county, $subcounty, $ward, $workstation,
            $phone_number, $email, $latitude, $longitude, $photo_data
        );

        if ($stmt->execute()) {
            $message = "Employee registered successfully!";
        } else {
            $message = "Database error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Please fill all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Employee Registration</title>
<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background: #f4f7fa;
    margin: 0;
    padding: 20px;
}
.container {
    background: #fff;
    max-width: 800px;
    margin: auto;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
}
h2 {
    text-align: center;
    color: #1e40af;
}
label {
    font-weight: bold;
}
input, select {
    width: 100%;
    padding: 8px;
    margin: 5px 0 15px;
    border: 1px solid #ccc;
    border-radius: 6px;
}
button {
    background-color: #1e40af;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}
button:hover {
    background-color: #2563eb;
}
.preview {
    width: 100px;
    height: 100px;
    border: 2px dashed #ccc;
    border-radius: 8px;
    margin-bottom: 10px;
    object-fit: cover;
}
.message {
    text-align: center;
    font-weight: bold;
    color: green;
}
</style>
</head>
<body>
<div class="container">
    <h2>Employee Registration</h2>
    <?php if ($message): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <form method="POST" onsubmit="return validateForm();">

        <label>Full Name</label>
        <input type="text" name="full_name" required>

        <label>ID Number</label>
        <input type="text" name="id_number" required>

        <label>Personal Number</label>
        <input type="text" name="personal_number" required>

        <label>Gender</label>
        <select name="gender" required>
            <option value="">--Select Gender--</option>
            <option>Male</option>
            <option>Female</option>
            <option>Other</option>
        </select>

        <label>Disability Status</label>
        <select name="disability_status" id="disability_status" required onchange="toggleDisabilityType()">
            <option value="">--Select--</option>
            <option value="Not Disabled">Not Disabled</option>
            <option value="Disabled">Disabled</option>
        </select>

        <div id="disability_type_div" style="display:none;">
            <label>Type of Disability</label>
            <select name="disability_type">
                <option value="">--Select Type--</option>
                <option>Physical</option>
                <option>Visual</option>
                <option>Hearing</option>
                <option>Mental</option>
                <option>Other</option>
            </select>
        </div>

        <label>Date of Appointment</label>
        <input type="date" name="date_of_appointment" required>

        <label>Job Group</label>
        <select name="job_group" required>
    <option value="">--Select Job Group--</option>
   <?php
$groups = range('A', 'T');
foreach ($groups as $g) {
    if ($g === 'I' || $g === 'O') continue; // skip I and O
    echo "<option value='$g'>$g</option>";
}
?>
</select>

        <label>Highest Academic Qualification</label>
        <select name="highest_academic_qualification" required>
            <option value="">--Select Qualification--</option>
            <option>KCPE</option>
            <option>KCSE</option>
            <option>Certificate</option>
            <option>Diploma</option>
            <option>Masters</option>
            <option>PhD</option>
        </select>

        <label>Professional Qualification</label>
        <input type="text" name="professional_qualification">

        <label>Department</label>
        <select name="department" required>
            <option value="">--Select Department--</option>
            <option>Agriculture, Livestock and Fisheries</option>
            <option>Finance and Economic Planning</option>
            <option>Gender, Youth, Sports, Culture and Social Services</option>
            <option>Roads, Transport, Public Works and Disaster Management</option>
            <option>Trade, Tourism and Co-operative Development</option>
            <option>Lands, Housing and Urban Development</option>
            <option>Education and Vocation Training and  ICT </option>
            <option>Public Service Management</option>
            <option>Health Services</option>
            <option>Environment, Water, Energy, Mining and Natural Resources</option>
            <option>Public Service Board</option>
            <option>Governors</option>
        </select>

        <label>Section</label>
        <input type="text" name="section">

        <label>Home County</label>
        <input type="text" name="home_county" value="Nyamira" readonly>

        <label>Subcounty</label>
        <select name="subcounty" id="subcounty" required onchange="updateWards();">
            <option value="">--Select Subcounty--</option>
            <option value="Nyamira South">Nyamira South</option>
            <option value="Nyamira North">Nyamira North</option>
            <option value="Manga">Manga</option>
            <option value="Borabu">Borabu</option>
            <option value="Masaba North">Masaba North</option>
        </select>

        <label>Ward</label>
        <select name="ward" id="ward" disabled required>
            <option value="">--Select Ward--</option>
        </select>

        <label>Workstation</label>
        <input type="text" name="workstation" required>

        <label>Phone Number</label>
        <input type="text" name="phone_number" required pattern="^[0-9]{10}$" title="Enter 10-digit phone number">

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Auto Location Capture</label><br>
        <input type="text" name="latitude" id="latitude" placeholder="Latitude" readonly>
        <input type="text" name="longitude" id="longitude" placeholder="Longitude" readonly><br>
        <button type="button" onclick="getLocation()">📍 Get Current Location</button>

        <label>Photo Capture</label><br>
        <video id="camera" width="200" height="150" autoplay style="border:1px solid #ccc;"></video><br>
        <button type="button" onclick="capturePhoto()">📸 Capture Photo</button><br>
        <img id="photoPreview" class="preview" src="" alt="No photo">
        <input type="hidden" name="photo_data" id="photo_data">

        <br><br>
        <button type="submit">Register Employee</button>
    </form>
</div>

<script>
// Toggle disability type
function toggleDisabilityType() {
    const status = document.getElementById("disability_status").value;
    document.getElementById("disability_type_div").style.display = (status === "Disabled") ? "block" : "none";
}

// Subcounty → Ward mapping
const wardsBySubcounty = {
    "Manga": ["Kemera", "Manga", "Magombo"],
    "Masaba North": ["Rigoma", "Gachuba", "Gesima"],
    "Borabu": ["Esise", "Nyansiongo", "Mekenene", "Kiabonyoru"],
    "Nyamira South": ["Township", "Bogichora", "Bonyamatuta", "Nyamaiya", "Bosamaro"],
    "Nyamira North": ["Magwagwa", "Bokeira", "Itibo", "Ekerenyo", "Bomwagama"]
};

// Update ward dropdown
function updateWards() {
    const subcounty = document.getElementById("subcounty").value;
    const wardSelect = document.getElementById("ward");
    wardSelect.innerHTML = '<option value="">--Select Ward--</option>';
    wardSelect.disabled = true;
    if (subcounty && wardsBySubcounty[subcounty]) {
        wardsBySubcounty[subcounty].forEach(ward => {
            const option = document.createElement("option");
            option.value = ward;
            option.textContent = ward;
            wardSelect.appendChild(option);
        });
        wardSelect.disabled = false;
    }
}

// Location
function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            document.getElementById("latitude").value = pos.coords.latitude;
            document.getElementById("longitude").value = pos.coords.longitude;
        }, () => alert("Location access denied."));
    } else {
        alert("Geolocation not supported.");
    }
}

// Camera
const video = document.getElementById("camera");
if (navigator.mediaDevices.getUserMedia) {
    navigator.mediaDevices.getUserMedia({ video: true }).then(stream => {
        video.srcObject = stream;
    }).catch(() => alert("Cannot access camera."));
}

function capturePhoto() {
    const canvas = document.createElement("canvas");
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext("2d");
    ctx.drawImage(video, 0, 0);
    const dataUrl = canvas.toDataURL("image/png");
    document.getElementById("photo_data").value = dataUrl;
    document.getElementById("photoPreview").src = dataUrl;
}

// Client-side validation
function validateForm() {
    const phone = document.querySelector("[name='phone_number']").value;
    if (!/^[0-9]{10}$/.test(phone)) {
        alert("Please enter a valid 10-digit phone number.");
        return false;
    }
    return true;
}
</script>
</body>
</html>
