<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get application ID from URL
$application_id = isset($_GET['id']) ? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) : null;

if (!$application_id) {
    header("Location: staff.php?error=No+application+ID+provided");
    exit();
}

// Fetch application data (accessible by applicant or approver)
$stmt = $conn->prepare("SELECT * FROM leave_applications WHERE id = ? AND (username = ? OR approved_by = ?)");
$stmt->bind_param("iss", $application_id, $_SESSION['username'], $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: staff.php?error=Application+not+found");
    exit();
}

$application = $result->fetch_assoc();

// Check if application is approved
if ($application['status'] !== 'Approved') {
    header("Location: staff.php?error=Application+not+approved");
    exit();
}

// Mark as viewed and printed
Application::MarkAsViewed($application_id);
Application::MarkAsPrinted($application_id);

$stmt->close();
$conn->close();

// Format dates for display
function format_date($date) {
    return $date ? date('d/m/Y', strtotime($date)) : 'N/A';
}

function format_datetime($datetime) {
    return $datetime ? date('d/m/Y H:i', strtotime($datetime)) : 'N/A';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            line-height: 1.6; 
            background-color: #fff;
        }
        .container { 
            max-width: 800px; 
            margin: auto; 
            padding: 20px;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px;
            border-bottom: 2px solid #28a745;
            padding-bottom: 10px;
        }
        .approval-badge {
            background: #28a745;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .application-details {
            padding: 10px;
        }
        .detail-row {
            display: flex;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
        }
        .detail-label {
            font-weight: bold;
            width: 200px;
            flex-shrink: 0;
        }
        .detail-value {
            flex: 1;
        }
        @media print {
            body {
                margin: 0;
                font-size: 12pt;
            }
            .container {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <div class="header">
            <h2>REPUBLIC OF KENYA</h2>
            <h3>MINISTRY OF INFORMATION, COMMUNICATIONS AND THE DIGITAL ECONOMY</h3>
            <h3>STATE DEPARTMENT FOR ICT AND DIGITAL ECONOMY</h3>
            <h4>APPROVED LEAVE APPLICATION</h4>
        </div>

        <div class="application-details">
            <h3>Part I: Applicant Details</h3>
            <div class="detail-row">
                <span class="detail-label">Application ID:</span>
                <span class="detail-value">#<?php echo htmlspecialchars($application['id']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Name:</span>
                <span class="detail-value"><?php echo htmlspecialchars($application['name']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Personal Number:</span>
                <span class="detail-value"><?php echo htmlspecialchars($application['personal_number']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Designation:</span>
                <span class="detail-value"><?php echo htmlspecialchars($application['designation']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Leave Type:</span>
                <span class="detail-value"><?php echo htmlspecialchars($application['leave_type']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Leave Days:</span>
                <span class="detail-value"><?php echo htmlspecialchars($application['leave_days']); ?> days</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Leave Period:</span>
                <span class="detail-value"><?php echo format_date($application['leave_start_date']) . ' to ' . format_date($application['leave_end_date']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Last Leave:</span>
                <span class="detail-value"><?php echo format_date($application['last_leave_start_date']) . ' to ' . format_date($application['last_leave_end_date']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Leave Balance:</span>
                <span class="detail-value"><?php echo $application['leave_balance'] ? htmlspecialchars($application['leave_balance']) . ' days' : 'N/A'; ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Contact Address:</span>
                <span class="detail-value"><?php echo $application['contact_address'] ? nl2br(htmlspecialchars($application['contact_address'])) : 'N/A'; ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Telephone:</span>
                <span class="detail-value"><?php echo htmlspecialchars($application['telephone']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Salary Payment:</span>
                <span class="detail-value">
                    <?php 
                    echo $application['salary_payment'] === 'bank_account' 
                        ? 'Bank Account' 
                        : 'Other Address: ' . ($application['payment_address'] ? htmlspecialchars($application['payment_address']) : 'N/A');
                    ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Outside Kenya Permission:</span>
                <span class="detail-value"><?php echo htmlspecialchars($application['outside_kenya_permission']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Acting Officer:</span>
                <span class="detail-value"><?php echo htmlspecialchars($application['acting_officer']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Application Date:</span>
                <span class="detail-value"><?php echo format_date($application['applicant_date']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Applicant Signature:</span>
                <span class="detail-value"><?php echo htmlspecialchars($application['signature']); ?></span>
            </div>

            <h3>Part II: Approval Details</h3>
            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span class="detail-value"><span class="approval-badge">Approved</span></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Approved By:</span>
                <span class="detail-value"><?php echo htmlspecialchars($application['approved_by']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Approval Comments:</span>
                <span class="detail-value"><?php echo $application['authority_comments'] ? nl2br(htmlspecialchars($application['authority_comments'])) : 'N/A'; ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Approval Date:</span>
                <span class="detail-value"><?php echo format_date($application['date']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Authority Signature:</span>
                <span class="detail-value"><?php echo htmlspecialchars($application['authority_signature']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Approved At:</span>
                <span class="detail-value"><?php echo format_datetime($application['approved_at']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Submitted At:</span>
                <span class="detail-value"><?php echo format_datetime($application['created_at']); ?></span>
            </div>
        </div>

        <div class="no-print" style="text-align: center; margin-top: 20px;">
            <a href="staff.php" style="padding: 8px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>