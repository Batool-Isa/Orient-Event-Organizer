<?php

$page_title = 'Edit Employee';
include 'header.html';

echo '<h1>Edit Employee</h1>';

include "debugging.php";
require_once 'Employee.php';
require_once 'Users.php';

$id = 0;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
} elseif (isset($_POST['id'])) {
    $id = $_POST['id'];
} else {
    echo '<p class="error">Error has occurred</p>';
    include 'footer.html';
    exit();
}

$employee = new Employee();
$user = new Users();

if (!$employee->initWithEmployeeId($id)) {
    echo '<p class="error">Employee not found.</p>';
    include 'footer.html';
    exit();
}

if (!$user->initWithUserId($employee->getUserId())) {
    echo '<p class="error">User not found.</p>';
    include 'footer.html';
    exit();
}

if (isset($_POST['submitted'])) {
    $user->setFirstName($_POST['firstName']);
    $user->setLastName($_POST['lastName']);
    $user->setUsername($_POST['username']);
    $user->setEmail($_POST['email']);
    $user->setPhoneNumber($_POST['phoneNumber']);
    $employee->setDepartment($_POST['department']);
    $employee->setPosition($_POST['position']);
    $employee->setJoinDate($_POST['joinDate']);

    $userUpdateSuccess = $user->updateUserDB();
    $employeeUpdateSuccess = $employee->updateEmployeeDB();

    if ($userUpdateSuccess && $employeeUpdateSuccess) {
        echo '<p>Update Successful</p>';
    } else {
        echo '<p class="error">Update Not Successful</p>';
        error_log("User update success: " . ($userUpdateSuccess ? "true" : "false"));
        error_log("Employee update success: " . ($employeeUpdateSuccess ? "true" : "false"));
    }
}

// Always show the form
echo '<form action="edit_employee.php" method="post">
    <br />
    <h3>Edit Employee: ' . $employee->getEmployeeId() . ' ' . $user->getFirstName() . ' ' . $user->getLastName() . '</h3>
    <p><br />
       <p>First Name: <input type="text" name="firstName" value="' . $user->getFirstName() . '" /></p>
       <p>Last Name: <input type="text" name="lastName" value="' . $user->getLastName() . '"/></p>
       <p>Username: <input type="text" name="username" value="' . $user->getUsername() . '"/></p>
       <p>Email: <input type="email" name="email" value="' . $user->getEmail() . '"/></p>
       <p>Phone Number: <input type="text" name="phoneNumber" value="' . $user->getPhoneNumber() . '"/></p>
       <p>Department: <input type="text" name="department" value="' . $employee->getDepartment() . '"/></p>
       <p>Position: <input type="text" name="position" value="' . $employee->getPosition() . '"/></p>
       <p>Join Date: <input type="date" name="joinDate" value="' . $employee->getJoinDate() . '"/></p>
    </p>
    <p><input type="submit" class="DB4Button" name="submit" value="update" /></p>
    <input type="hidden" name="submitted" value="TRUE">
    <input type="hidden" name="id" value="' . $id . '"/>
    </form>';

include 'footer.html';

?>
