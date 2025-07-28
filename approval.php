<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$current_user = getUserByUsername($_SESSION['username']);
$user_role = $current_user['role'];

// Check if user has approval rights (Principal Secretary or HOD)
if (!in_array($user_role, ['Principal Secretary', 'HOD'])) { 
    header("Location: staff.php?error=Access+denied");
    exit();
}

// Handle approval submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['approve_application'])) {
    $application_id = filter_input(INPUT_POST, 'application_id', FILTER_VALIDATE_INT);
    $authority_comments = filter_input(INPUT_POST, 'authority_comments', FILTER_SANITIZE_STRING);
    $approval_date = $_POST['approval_date'];
    $authority_signature = filter_input(INPUT_POST, 'authority_signature', FILTER_SANITIZE_STRING);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    
    // Validate required fields
    if (!$application_id || !$authority_comments || !$approval_date || !$authority_signature || !$status) {
        header("Location: approval.php?error=All+fields+are+required");
        exit();
    }
    
    // Update the application with approval details
    $stmt = $conn->prepare("UPDATE leave_applications SET 
        authority_comments = ?, 
        date = ?, 
        authority_signature = ?, 
        status = ?, 
        approved_by = ?,
        approved_at = NOW()
        WHERE id = ?");
    
    $stmt->bind_param("sssssi", 
        $authority_comments, 
        $approval_date, 
        $authority_signature, 
        $status,
        $_SESSION['username'],
        $application_id
    );
    
    if ($stmt->execute()) {
        header("Location: approval.php?status={$status}&application_id={$application_id}");
        exit();
    } else {
        header("Location: approval.php?error=Failed+to+update+application");
        exit();
    }
} 

// Get specific application if ID is provided
$application = null;
if (isset($_GET['id'])) {
    $application_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($application_id) {
        $stmt = $conn->prepare("SELECT * FROM leave_applications WHERE id = ?");
        $stmt->bind_param("i", $application_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $application = $result->fetch_assoc();
    }
}

// Get all pending applications if no specific ID
$pending_applications = [];
if (!$application) {
    $stmt = $conn->prepare("SELECT * FROM leave_applications WHERE status = 'Pending' ORDER BY created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $pending_applications[] = $row;
    }
}

// Format dates for display
function format_date($date) {
    return $date ? date('d/m/Y', strtotime($date)) : 'N/A';
}

function format_datetime($datetime) {
    return $datetime ? date('d/m/Y H:i', strtotime($datetime)) : 'N/A';
}

// Helper function to check if current user can approve a specific application
function canApproveApplication($user_role, $application_username) {
    if ($user_role === 'Principal Secretary') {
        return true; // PS can approve anyone
    }
    
    if ($user_role === 'HOD') {
        $applicant = getUserByUsername($application_username);
        $applicant_role = $applicant['role'];
        // HOD can approve anyone except other HODs
        return $applicant_role !== 'HOD';
    }
    
    return false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Application Approval - <?php echo htmlspecialchars($user_role); ?></title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            line-height: 1.6; 
            background-color: #f5f5f5;
        }
        .container { 
            max-width: 1200px; 
            margin: auto; 
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header { 
          color: #007bff;
            text-align: center; 
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .applications-list {
            margin-bottom: 30px;
        }
        .application-card {
            border: 1px solid #ddd;
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 5px;
            background: #f9f9f9;
        }
        .application-card:hover {
            background: #f0f0f0;
        }
        .application-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .applicant-info {
            flex: 1;
        }
        .leave-info {
            flex: 1;
            text-align: center;
        }
        .action-buttons {
            flex: 1;
            text-align: right;
        }
        .btn {
            padding: 8px 15px;
            margin: 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn:hover { opacity: 0.8; }
        
        .approval-form {
            border: 2px solid #007bff;
            padding: 20px;
            margin-top: 20px;
            border-radius: 8px;
            background: #f8f9fa;
        }
        .form-section {
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        .application-details {
            background: white;
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .detail-row {
            display: flex;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
        }
        .detail-label {
            font-weight: bold;
            width: 200px;
            flex-shrink: 0;
        }
        .detail-value {
            flex: 1;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending { background: #ffc107; color: #212529; }
        .status-approved { background: #28a745; color: white; }
        .status-rejected { background: #dc3545; color: white; }
        
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .error { color: #721c24; background: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        
        .no-applications {
            text-align: center;
            color: #666;
            padding: 40px;
            font-size: 18px;
        }
        
        @media (max-width: 768px) {
            .application-summary {
                flex-direction: column;
                align-items: stretch;
            }
            .action-buttons {
                text-align: center;
            }
            .detail-row {
                flex-direction: column;
            }
            .detail-label {
                width: auto;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>REPUBLIC OF KENYA</h2>
            <h3>MINISTRY OF INFORMATION, COMMUNICATIONS AND THE DIGITAL ECONOMY</h3>
            <h3>STATE DEPARTMENT FOR ICT AND DIGITAL ECONOMY</h3>
            <h4>LEAVE APPLICATION APPROVAL</h4>
            <p>Role: <?php echo htmlspecialchars($user_role); ?> | User: <?php echo htmlspecialchars($_SESSION['username']); ?></p>
        </div>

        <!-- Display messages -->
        <?php if (isset($_GET['success'])): ?>
            <div class="success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <?php if ($application): ?>
            <div class="application-details">
                <h3>Leave Application Details</h3>
                
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
                
                <div class="detail-row">
                    <span class="detail-label">Current Status:</span>
                    <span class="detail-value">
                        <span class="status-badge status-<?php echo strtolower($application['status']); ?>">
                            <?php echo htmlspecialchars($application['status']); ?>
                        </span>
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Submitted:</span>
                    <span class="detail-value"><?php echo format_datetime($application['created_at']); ?></span>
                </div>
            </div>

            <?php if ($application['status'] === 'Pending'): ?>
                <?php if (canApproveApplication($user_role, $application['username'])): ?>
                    <!-- Approval Form - Part II -->
                    <form method="POST" class="approval-form">
                        <h4>PART II - APPROVAL SECTION</h4>
                        <p><strong>To be completed by the <?php echo htmlspecialchars($user_role); ?></strong></p>
                        
                        <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($application['id']); ?>">
                        
                        <div class="form-group">
                            <label for="status">Decision:</label>
                            <select name="status" id="status" required>
                                <option value="">Select Decision</option>
                                <option value="Approved">Approve Application</option>
                                <option value="Rejected">Reject Application</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="authority_comments">Comments/Remarks:</label>
                            <textarea name="authority_comments" id="authority_comments" required 
                                      placeholder="Enter your comments, conditions, or reasons for the decision..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="approval_date">Date:</label>
                            <input type="date" name="approval_date" id="approval_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="authority_signature">Signature (Full Name):</label>
                            <input type="text" name="authority_signature" id="authority_signature" 
                                   placeholder="Type your full name as signature" required>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="approve_application" class="btn btn-primary">
                                Submit Decision
                            </button>
                            <a href="approval.php" class="btn btn-secondary">Back to List</a>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="error">You cannot approve this application. Please wait for the Principal Secretary's approval.</div>
                    <div class="form-group">
                        <a href="approval.php" class="btn btn-secondary">Back to List</a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- Already processed application -->
                <div class="application-details">
                    <h4>Approval Details</h4>
                    
                    <div class="detail-row">
                        <span class="detail-label">Decision:</span>
                        <span class="detail-value">
                            <span class="status-badge status-<?php echo strtolower($application['status']); ?>">
                                <?php echo htmlspecialchars($application['status']); ?>
                            </span>
                        </span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Comments:</span>
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
                        <span class="detail-label">Approved By:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($application['approved_by']); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Processed At:</span>
                        <span class="detail-value"><?php echo format_datetime($application['approved_at']); ?></span>
                    </div>
                </div>
                
                <div class="form-group">
                    <a href="print.php?id=<?php echo htmlspecialchars($application['id']); ?>" class="btn btn-success" target="_blank">
                        Print Complete Application
                    </a>
                    <a href="approval.php" class="btn btn-secondary">Back to List</a>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Applications List -->
            <div class="applications-list">
                <h3>Pending Leave Applications</h3>
                
                <?php if (empty($pending_applications)): ?>
                    <div class="no-applications">
                        <p>No pending applications found.</p>
                         <?php if ($user_role === 'Principal Secretary'): ?>
                        <a href="principal.php" class="btn btn-secondary">Back to Dashboard</a>
                        <?php elseif ($user_role === 'HOD'): ?>
                        <a href="hod.php" class="btn btn-secondary">Back to Dashboard</a>
                    <?php else: ?>
                        <?php if ($user_role == 'staff'): ?>
                            <a href="staff.php" class="btn btn-secondary">Back to Dashboard</a>
                        <?php endif; ?>
                    <?php endif; ?>
                        
                    </div>
                <?php else: ?>
                    <?php foreach ($pending_applications as $app): ?>
                        <div class="application-card">
                            <div class="application-summary">
                                <div class="applicant-info">
                                    <strong><?php echo htmlspecialchars($app['name']); ?></strong><br>
                                    <small>P/NO: <?php echo htmlspecialchars($app['personal_number']); ?></small><br>
                                    <small><?php echo htmlspecialchars($app['designation']); ?></small>
                                </div>
                                <div class="leave-info">
                                    <strong><?php echo htmlspecialchars($app['leave_type']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($app['leave_days']); ?> days</small><br>
                                    <small><?php echo format_date($app['leave_start_date']) . ' - ' . format_date($app['leave_end_date']); ?></small>
                                </div>
                                <div class="action-buttons">
                                    <span class="status-badge status-pending">Pending</span><br>
                                    <small>Submitted: <?php echo format_datetime($app['created_at']); ?></small><br>
                                    <?php if (canApproveApplication($user_role, $app['username'])): ?>
                                        <a href="approval.php?id=<?php echo htmlspecialchars($app['id']); ?>" class="btn btn-primary">
                                            Review & Approve
                                        </a>
                                    <?php else: ?>
                                        <span class="btn btn-secondary" style="cursor: not-allowed;">
                                            Cannot Approve
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <div style="text-align: center; margin-top: 20px;">
                    <?php if ($user_role === 'Principal Secretary'): ?>
                        <a href="principal.php" class="btn btn-secondary">Back to Dashboard</a>
                        <?php elseif ($user_role === 'HOD'): ?>
                        <a href="hod.php" class="btn btn-secondary">Back to Dashboard</a>
                    <?php else: ?>
                        <?php if ($user_role == 'staff'): ?>
                            <a href="staff.php" class="btn btn-secondary">Back to Dashboard</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>