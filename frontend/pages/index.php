<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MMO IT Service Request Page</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
   
<div class="container">

        <img src="../assets/images/solano1.png" alt="MMO Logo" class="logo" />
        <h1>IT Service Request System</h1>

    <div>

        <button class="btn" type="button" id="open-technician-modal">
            Technician Login
        </button>

        <button class="btn" type="button" id="open-request-modal">
            Send Request
        </button>

    </div>
<div>

   <!-- Technician Login Modal -->
    <div class="modal" id="technician-login-modal">   
        <div class="modal-content" id="technician-modal-content">
            <span class="close" id="close-technician-modal">&times;</span>
            <h2>Technician Login</h2>
            <form action="./../../backend/config/login.php" method="POST">
                <div id="technician-login">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required />

                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required />
                </div>
                <button type="submit" onClick="submit">Login</button>
            </form>
        </div>
    </div>

    <!-- Service Request Modal -->

    <div class="modal" id="request-modal">
        <div class="modal-content" id="request-modal-content">
            <span class="close" id="close-request-modal">&times;</span>
            <h2>Send Service Request</h2>
            <form action="" method="POST">
                <div id="service-request-form">
                    <label for="client_name">Client Name:</label>
                    <input type="text" id="client_name" name="client_name" required />

                    <label for="office">Office:</label>
                    <input type="text" id="office" name="office" required />

                    <label for="semester">Semester:</label>
                    <select id="semester" name="semester" required>
                        <option value="" disabled selected>Select Semester</option>
                        <option value="1st Semester">1st Semester</option>
                        <option value="2nd Semester">2nd Semester</option>
                    </select>

                    <label for="unit">Unit:</label>
                    <select id="unit" name="unit" required>
                        <option value="desktop">Desktop</option>
                        <option value="laptop">Laptop</option>
                        <option value="printer">Printer</option>
                        <option value="network">Network</option>
                        <option value="others">Others</option>
                    </select>

                    <label for="issue">Issue Description:</label>
                    <input type="text" id="issue" name="issue" required />

                    <label for="remarks">Remarks</label>
                    <input type="text" id="remarks" name="remarks" />

                    <label for="recommendation">Recommendation</label>
                    <input type="text" id="recommendation" name="recommendation" />

                    <label for="technician">Assign Technician:</label>
                    <select id="technician" name="technician" required>
                        
                    </select>

                </div>
                <button type="submit" onClick="sendRequest()">Submit Request</button>
            </form>
        </div>
    </div>

    <script src="../assets/js/technician_login.js"></script>
    <script src="../assets/js/send_request.js"></script>
</body>
</html>