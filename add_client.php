<?php
include 'debugging.php';
require_once 'Client.php'; // Ensure this path is correct
require_once 'Users.php';  // Ensure this path is correct

$error = ''; // Initialize a variable to store error messages

if (isset($_POST['submitted'])) {
    // Server-side validation
    $user = new Users();
    $user->setUserName($_POST['username']);
    $user->setUserType("Client");
    $user->setFirstName($_POST['firstName']);
    $user->setLastName($_POST['lastName']);
    $user->setEmail($_POST['email']);
    $user->setPhoneNumber($_POST['phoneNumber']);
    $user->setPassword($_POST['password']);

    echo "Checking if username exists...<br>"; // Debugging step

    if ($user->initWithUsername()) {
        echo "Username does not exist, proceeding to register user...<br>"; // Debugging step
        if ($user->registerUser()) {
            // User registered successfully, now register the client
            $client = new Client();
            $client->setUserId($user->getUserId());
            $client->setCompanyName($_POST['companyName']);
            $client->setRoyaltyPoints(0); // Default value for new clients

            echo "Registering client...<br>"; // Debugging step

            if ($client->registerClient($user)) {
                echo 'Client added successfully';
            } else {
                $error = 'Error adding client';
                echo "Error adding client<br>"; // Debugging step
            }
        } else {
            $error = 'Error adding user';
            echo "Error adding user<br>"; // Debugging step
        }
    } else {
        $error = 'Username Already Exists';
        echo "Username Already Exists<br>"; // Debugging step
    }
}

include 'header.html';
?>

<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
    }
    .myform {
        width: 50%;
        margin: 50px auto;
        padding: 20px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        font-size: 18px;
    }
    .myform fieldset {
        border: none;
    }
    .myform label {
        display: block;
        margin-bottom: 10px;
        font-size: 20px;
    }
    .myform input[type="text"],
    .myform input[type="password"] {
        width: calc(100% - 22px);
        padding: 10px;
        margin-bottom: 20px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 18px;
    }
    .myform input[type="submit"] {
        background-color: #ff6666; /* Light red color */
        color: white;
        padding: 15px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 20px;
        transition: background-color 0.3s;
    }
    .myform input[type="submit"]:hover {
        background-color: #ff4d4d; /* Slightly darker red on hover */
    }
    .error {
        color: red;
        font-size: 18px;
        text-align: center;
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
            } else if (obj.id === 'phoneNumber') {
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

    function validateForm() {
        var fields = ['firstName', 'lastName', 'username', 'password', 'email', 'phoneNumber', 'companyName'];
        var isValid = true;

        fields.forEach(function(field) {
            var element = document.getElementById(field);
            if (!validate(element)) {
                isValid = false;
            }
        });

        return isValid;
    }
</script>

<h1 style="text-align: center;">Add New Client</h1>
<div id="stylized" class="myform">
    <form name="clientForm" id="clientForm" action="add_client.php" method="post" onsubmit="return validateForm()">
        <fieldset>
            <label for="firstName">First Name</label>
            <input type="text" id="firstName" name="firstName" size="20" value="" placeholder="Enter first name" onblur="validate(this);" />
            <span id="firstNameErr" class="text-danger"></span>
            
            <label for="lastName">Last Name</label>
            <input type="text" id="lastName" name="lastName" size="20" value="" placeholder="Enter last name" onblur="validate(this);" />
            <span id="lastNameErr" class="text-danger"></span>
            
            <label for="username">Username</label>
            <input type="text" id="username" name="username" size="20" value="" placeholder="Enter username" onblur="validate(this);" />
            <span id="usernameErr" class="text-danger"></span>
            
            <label for="password">Password</label>
            <input type="password" id="password" name="password" size="20" value="" placeholder="Enter password" onblur="validate(this);" />
            <span id="passwordErr" class="text-danger"></span>
            
            <label for="email">Email</label>
            <input type="text" id="email" name="email" size="20" value="" placeholder="Enter email" onblur="validate(this);" />
            <span id="emailErr" class="text-danger"></span>
            
            <label for="phoneNumber">Phone Number</label>
            <input type="text" id="phoneNumber" name="phoneNumber" size="20" value="" placeholder="Enter phone number" onblur="validate(this);" />
            <span id="phoneNumberErr" class="text-danger"></span>
            
            <label for="companyName">Company Name</label>
            <input type="text" id="companyName" name="companyName" size="20" value="" placeholder="Enter company name" onblur="validate(this);" />
            <span id="companyNameErr" class="text-danger"></span>
            
            <div align="center">
                <input type="submit" value="Add Client" />
            </div>
            <input type="hidden" name="submitted" value="1" />
        </fieldset>
    </form>
    <div class="error" style="color: red; text-align: center;"><?php echo $error; ?></div>
    <div class="spacer"></div>
</div>

<?php
include 'footer.html';
?>

