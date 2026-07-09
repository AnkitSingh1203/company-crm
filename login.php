<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Company CRM Login</title>

    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body>

    <div class="login-container">

        <!-- Left Panel -->

        <div class="left-panel">

            <div>

                <h1>Company CRM</h1>

                <p class="tagline">

                    Employee Management System

                </p>

                <ul>

                    <li>✔ Attendance Tracking</li>

                    <li>✔ Leave Management</li>

                    <li>✔ Salary Slips</li>

                    <li>✔ Task Management</li>

                    <li>✔ Performance Reports</li>

                </ul>

            </div>

        </div>


        <!-- Right Panel -->

        <div class="right-panel">

            <form
                class="login-form"
                method="POST"
                action="">

                <h2>Welcome Back</h2>

                <p>Please login to continue</p>

                <div class="input-group">

                    <label>Email</label>

                    <input
                        type="email"
                        name="email"
                        placeholder="Enter Email"
                        required>

                </div>

                <div class="input-group">
                    <label>Password</label>

                    <div class="password-box">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter Password"
                            required>

                        <i class="fa-solid fa-eye" id="togglePassword"></i>
                    </div>
                </div>
                <div class="options">

                    <label>

                        <input type="checkbox">

                        Remember Me

                    </label>

                    <a href="#">Forgot Password?</a>

                </div>

                <button
                    id="loginBtn"
                    type="submit">

                    Login

                </button>

            </form>

        </div>

    </div>

    <script src="assets/js/login.js"></script>

</body>

</html>