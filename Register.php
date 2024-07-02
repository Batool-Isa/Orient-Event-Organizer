<?php
include 'Users.php';
$error = '';
if (isset($_POST['submitted'])) {
    // Create user object and save user details
        $user = new Users();
        $user->setUserName($_POST['username']);
        $user->setUserType("Client");
        $user->setFirstName($_POST['fName']);
        $user->setLastName($_POST['lName']);
        $user->setEmail($_POST['email']);
        $user->setPhoneNumber($_POST['phone']);
        $user->setPassword($_POST['password']);
        
        if ($user->initWithUsername()) {
            if ($user->registerUser()) {
                $error = 'Registerd Successfully';
                header('Location: LoginForm.php');
            } else {
                $error = 'Not Successfull ';
            }
        } else {
            $error = 'Username Already Exists';
        }
}// End of If-Submit statment
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-group {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        .form-group label {
            width: 150px;
            margin-bottom: 0;
            margin-right: 1rem;
        }
        .form-group input,
        .form-group .text-danger {
            flex: 1;
            width: 100%;
            margin-right: 1rem;
        }
        .form-control {
            margin-bottom: 10px;
        }
        .card {
            width: 100%;
            padding: 10px;
        }
        .custom-link {
            color: blue;
        }
        .custom-link:hover {
            color: darkblue;
        }
    </style>
    <script>
    // Function to validate the entered data in fields
    function validate(obj) {
        var errField = obj.id + 'Err';
        var valid = false;
        var value = obj.value.trim();

        if (value === '') {
            document.getElementById(errField).innerHTML = obj.id + ' field may not be blank';
        } else {
            if (obj.id === 'email') {
                var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(value)) {
                    document.getElementById(errField).innerHTML = 'Invalid email format';
                } else {
                    document.getElementById(errField).innerHTML = '';
                    valid = true;
                }
            } else if (obj.id === 'phone') {
                if (!/^\d+$/.test(value)) {
                    document.getElementById(errField).innerHTML = 'Phone number must contain only numbers';
                } else if (value.length !== 8) {
                    document.getElementById(errField).innerHTML = 'Phone number must be 8 digits long';
                } else {
                    document.getElementById(errField).innerHTML = '';
                    valid = true;
                }
            } else if (obj.id === 'password') {
                if (value.length < 8) {
                    document.getElementById(errField).innerHTML = 'Password must be at least 8 characters long';
                } else if (!/[A-Z]/.test(value)) {
                    document.getElementById(errField).innerHTML = 'Password must include at least one uppercase letter';
                } else if (!/[a-z]/.test(value)) {
                    document.getElementById(errField).innerHTML = 'Password must include at least one lowercase letter';
                } else if (!/[0-9]/.test(value)) {
                    document.getElementById(errField).innerHTML = 'Password must include at least one number';
                } else if (!/[!@#$%^&*]/.test(value)) {
                    document.getElementById(errField).innerHTML = 'Password must include at least one special character';
                } else {
                    document.getElementById(errField).innerHTML = '';
                    valid = true;
                }
            } else {
                document.getElementById(errField).innerHTML = '';
                valid = true;
            }
        }
        return valid;
    }
</script>

</head>
<body>
    <?php include 'header.html'; ?>
    <div class="container">
        <div class="card">
            <div class="card-body">
                <h1 class="card-title text-center">User Registration</h1>
                <form name="cForm" id="cForm" action="Register.php" method="post">
                    <div class="form-group">
                        <label for="username"><b>Username</b></label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter Username" onblur="validate(this);" value="<?php echo $_POST['username']; ?>">
                        <span id="usernameErr" class="text-danger"></span>
                    </div>
                    <div class="form-group">
                        <label for="fName"><b>First Name</b></label>
                        <input type="text" class="form-control" id="fName" name="fName" placeholder="Enter First Name" onblur="validate(this);" value="<?php echo $_POST['fName']; ?>">
                        <span id="fNameErr" class="text-danger"></span>
                    </div>
                    <div class="form-group">
                        <label for="lName"><b>Last Name</b></label>
                        <input type="text" class="form-control" id="lName" name="lName" placeholder="Enter Last Name" onblur="validate(this);" value="<?php echo $_POST['lName']; ?>">
                        <span id="lNameErr" class="text-danger"></span>
                    </div>
                    <div class="form-group">
                        <label for="phone"><b>Phone Number</b></label>
                        <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter Phone Number" onblur="validate(this);" value="<?php echo $_POST['phone']; ?>">
                        <span id="phoneErr" class="text-danger"></span>
                    </div>
                    <div class="form-group">
                        <label for="email"><b>Email</b></label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email" onblur="validate(this);" value="<?php echo $_POST['email']; ?>">
                        <span id="emailErr" class="text-danger"></span>
                    </div>
                    <div class="form-group">
                        <label for="password"><b>Password</b></label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter Password" onblur="validate(this);" value="<?php echo $_POST['password']; ?>">
                        <span id="passwordErr" class="text-danger"></span>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Register</button>
                        <p class="mt-3">Already have an account? <a href="LoginForm.php" class="custom-link">Login here</a>.</p>
                    </div>
                    <div class="error" style="color: red; text-align: center;"><?php echo $error; ?></div>
                    <input type="hidden" name="submitted" value="1">
                </form>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>


<?php
include 'footer.html';
?>
