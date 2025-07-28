<?php
require 'db.php';

function getUserRole() {
    return ['Principal Secretary', 'HOD', 'STAFF'];
}

function CreateUser($username, $password, $email, $personal_number, $role, $designation) {
    global $conn;
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, personal_number, role, designation) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiss", $username, $email, $hashedPassword, $personal_number, $role, $designation);
    return $stmt->execute();
}

function userLogin($username, $password) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            return $user;
        }
    }
    return false;
}

function getUserByUsername($username) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

class Application {
    public static function CreateApplication(
        $username, $name, $personal_number, $designation, $leave_type, $leave_days, $leave_start_date, $leave_end_date, $last_leave_start_date, $last_leave_end_date, $leave_balance,
        $contact_address, $telephone, $salary_payment, $payment_address, $outside_kenya_permission, $acting_officer, $applicant_date, $signature, $authority_comments, $date, $authority_signature
    ) {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO leave_applications (username, name, personal_number, designation, leave_type, leave_days, leave_start_date, leave_end_date, last_leave_start_date, last_leave_end_date, leave_balance, contact_address, telephone, salary_payment, payment_address, outside_kenya_permission, acting_officer, applicant_date, signature, authority_comments, date, authority_signature) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "ssssssssssssssssssssss",
            $username, $name, $personal_number, $designation, $leave_type, $leave_days, $leave_start_date, $leave_end_date, $last_leave_start_date, $last_leave_end_date, $leave_balance,
            $contact_address, $telephone, $salary_payment, $payment_address, $outside_kenya_permission, $acting_officer, $applicant_date, $signature, $authority_comments, $date, $authority_signature
        );
        return $stmt->execute();
    }

    public static function CheckPendingApplication($username) {
        global $conn;
        $stmt = $conn->prepare("SELECT * FROM leave_applications WHERE username = ? AND status IN ('Pending', 'Approved') ORDER BY created_at DESC LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return false;
    }

    public static function HasPendingApplication($username) {
        global $conn;
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM leave_applications WHERE username = ? AND status = 'Pending'");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'] > 0;
    }

    public static function GetApprovedApplication($username) {
        global $conn;
        $stmt = $conn->prepare("SELECT * FROM leave_applications WHERE username = ? AND status = 'Approved' AND (viewed IS NULL OR viewed = 0 OR printed IS NULL OR printed = 0) ORDER BY approved_at DESC LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return false;
    }

    public static function MarkAsViewed($application_id) {
        global $conn;
        $stmt = $conn->prepare("UPDATE leave_applications SET viewed = 1, viewed_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $application_id);
        return $stmt->execute();
    }

    public static function MarkAsPrinted($application_id) {
        global $conn;
        $stmt = $conn->prepare("UPDATE leave_applications SET printed = 1, printed_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $application_id);
        return $stmt->execute();
    }

    public static function GetUserApplications($username) {
        global $conn;
        $stmt = $conn->prepare("SELECT * FROM leave_applications WHERE username = ? ORDER BY created_at DESC");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        return $stmt->get_result();
    }

    public static function CanApplyForLeave($username) {
        global $conn;
        $stmt = $conn->prepare("SELECT * FROM leave_applications WHERE username = ? AND (status = 'Pending' OR (status = 'Approved' AND (printed IS NULL OR printed = 0) AND DATEDIFF(NOW(), approved_at) < 30)) ORDER BY created_at DESC LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows == 0;
    }
}
?>