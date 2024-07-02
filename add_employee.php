<?php
include 'debugging.php';
require_once 'Employee.php'; // Ensure this path is correct
require_once 'Users.php';    // Ensure this path is correct

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitted'])) {
    // Sanitize input data
    $firstName = filter_input(INPUT_POST, 'firstName', FILTER_SANITIZE_STRING);
    $lastName = filter_input(INPUT_POST, 'lastName', FILTER_SANITIZE_STRING);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phoneNumber = filter_input(INPUT_POST, 'phoneNumber', FILTER_SANITIZE_STRING);
    $department = filter_input(INPUT_POST, 'department', FILTER_SANITIZE_STRING);
    $position = filter_input(INPUT_POST, 'position', FILTER_SANITIZE_STRING);
    $joinDate = filter_input(INPUT_POST, 'joinDate', FILTER_SANITIZE_STRING);

    // Basic validation
    $errors = [];
    if (empty($firstName)) $errors[] = 'First name is required.';
    if (empty($lastName)) $errors[] = 'Last name is required.';
    if (empty($username)) $errors[] = 'Username is required.';
    if (empty($password)) $errors[] = 'Password is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';
    if (empty($phoneNumber)) $errors[] = 'Phone number is required.';
    if (empty($department)) $errors[] = 'Department is required.';
    if (empty($position)) $errors[] = 'Position is required.';
    if (empty($joinDate)) $errors[] = 'Join date is required.';

    if (empty($errors)) {
        // Create and register the user
        $user = new Users();
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setUsername($username);
        $user->setUserType('Employee'); // Assuming all employees have the userType 'Employee'
        $user->setPassword($password);
        $user->setEmail($email);
        $user->setPhoneNumber($phoneNumber);

        if ($user->registerUser()) {
            // Log user registration success
            error_log("User registered successfully: username = " . $username);
            
            // User registered successfully, now register the employee
            $employee = new Employee();
            $employee->setUserId($user->getUserId());
            $employee->setDepartment($department);
            $employee->setPosition($position);
            $employee->setJoinDate($joinDate);

            if ($employee->registerEmployee()) {
                // Log employee registration success
                error_log("Employee registered successfully: userId = " . $user->getUserId());
                echo 'Employee added successfully';
            } else {
                // Log a generic error message
                error_log("Error registering employee for userId: " . $user->getUserId());
                echo '<p class="error">Error adding employee. Please check the logs for details.</p>';
            }
        } else {
            // Log a generic error message
            error_log("Error registering user with username: " . $username);
            echo '<p class="error">Error adding user. Please check the logs for details.</p>';
        }
    } else {
        foreach ($errors as $error) {
            echo '<p class="error">' . htmlspecialchars($error) . '</p>';
        }
    }
}

include 'header.html';
?>

<h1>Add Employee</h1>
<div id="stylized" class="myform"> 
    <form action="add_employee.php" method="post">
        <fieldset>
            <label>First Name</label>
            <input type="text" name="firstName" size="20" value="" />
            <label>Last Name</label>
            <input type="text" name="lastName" size="20" value="" />
            <label>Username</label>
            <input type="text" name="username" size="20" value="" />
            <label>Password</label>
            <input type="password" name="password" size="20" value="" />
            <label>Email</label>
            <input type="text" name="email" size="20" value="" />
            <label>Phone Number</label>
            <input type="text" name="phoneNumber" size="20" value="" />
            <label>Department</label>
            <input type="text" name="department" size="20" value="" />
            <label>Position</label>
            <input type="text" name="position" size="20" value="" />
            <label>Join Date</label>
            <input type="date" name="joinDate" value="" />
            <div align="center">
                <input type="submit" value="Add Employee" />
            </div>  
            <input type="hidden" name="submitted" value="1" />
        </fieldset>
    </form>    
    <div class="spacer"></div>    
</div>    

<?php
include 'footer.html';
?>
