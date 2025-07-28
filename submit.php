<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get username from session
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : null;

    // Collect and sanitize POST data
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $personal_number = filter_input(INPUT_POST, 'personal_number', FILTER_SANITIZE_STRING);
    $designation = filter_input(INPUT_POST, 'designation', FILTER_SANITIZE_STRING);
    $leave_type = filter_input(INPUT_POST, 'leave_type', FILTER_SANITIZE_STRING);
    $leave_days = filter_input(INPUT_POST, 'leave_days', FILTER_VALIDATE_INT);
    $leave_start_date = $_POST['leave_start_date'];
    $leave_end_date = $_POST['leave_end_date'];
    $last_leave_start_date = !empty($_POST['last_leave_start_date']) ? $_POST['last_leave_start_date'] : null;
    $last_leave_end_date = !empty($_POST['last_leave_end_date']) ? $_POST['last_leave_end_date'] : null;
    $leave_balance = !empty($_POST['leave_balance']) ? filter_input(INPUT_POST, 'leave_balance', FILTER_VALIDATE_INT) : null;
    $contact_address = !empty($_POST['contact_address']) ? filter_input(INPUT_POST, 'contact_address', FILTER_SANITIZE_STRING) : null;
    $telephone = !empty($_POST['telephone']) ? filter_input(INPUT_POST, 'telephone', FILTER_SANITIZE_STRING) : null;
    $salary_payment = filter_input(INPUT_POST, 'salary_payment', FILTER_SANITIZE_STRING);
    $payment_address = !empty($_POST['salary_payment_address']) ? filter_input(INPUT_POST, 'salary_payment_address', FILTER_SANITIZE_STRING) : null;
    $outside_kenya_permission = !empty($_POST['outside_kenya_permission']) ? filter_input(INPUT_POST, 'outside_kenya_permission', FILTER_SANITIZE_STRING) : null;
    $acting_officer = !empty($_POST['acting_officer']) ? filter_input(INPUT_POST, 'acting_officer', FILTER_SANITIZE_STRING) : null;
    $applicant_date = $_POST['applicant_date'];
    $signature = filter_input(INPUT_POST, 'signature', FILTER_SANITIZE_STRING);

    // These fields are for PART II (HOD/PS) and are not filled by applicant
    $authority_comments = null;
    $date = null;
    $authority_signature = null;

    // Validate required fields
    if (
        !$username || !$name || !$personal_number || !$designation || !$leave_type ||
        !$leave_days || !$leave_start_date || !$leave_end_date || !$applicant_date || !$signature || !$salary_payment
    ) {
        header("Location: leave.php?error=All+required+fields+must+be+filled");
        exit();
    }

    // Validate salary_payment
    if (!in_array($salary_payment, ['bank_account', 'other_address'])) {
        header("Location: leave.php?error=Invalid+salary+payment+option");
        exit();
    }

    // Validate dates
    if (strtotime($leave_end_date) < strtotime($leave_start_date)) {
        header("Location: leave.php?error=End+date+must+be+after+start+date");
        exit();
    }

    // Call the Application class method
    $result = Application::CreateApplication(
        $username,
        $name,
        $personal_number,
        $designation,
        $leave_type,
        $leave_days,
        $leave_start_date,
        $leave_end_date,
        $last_leave_start_date,
        $last_leave_end_date,
        $leave_balance,
        $contact_address,
        $telephone,
        $salary_payment,
        $payment_address,
        $outside_kenya_permission,
        $acting_officer,
        $applicant_date,
        $signature,
        $authority_comments,
        $date,
        $authority_signature
    );

    if ($result) {
    
        $application_id = $conn->insert_id;
        header("Location: leave.php?application_id=" . urlencode($application_id) . "&success=1");
        exit();
    } else {
        header("Location: leave.php?error=Could+not+submit+application.+Please+try+again");
        exit();
    }
} else {
    header("Location: leave.php?error=Invalid+request+method");
    exit();
}
?>