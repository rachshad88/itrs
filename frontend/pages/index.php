    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technician Login - MMO IT Service Request Page</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    
   <!-- Technician Login -->
    <div class="container">
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


        <script src="../assets/js/technician_login.js"></script>