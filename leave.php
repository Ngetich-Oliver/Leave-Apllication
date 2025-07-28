<?php
session_start();
require 'functions.php';


if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}


$user = getUserByUsername($_SESSION['username']);
$username = $user['username'];
$personal_number = $user['personal_number'];
$designation = $user['designation'] ?? '';

$user_role = $user['role'];

// Check application status
$pending_application = Application::CheckPendingApplication($_SESSION['username']);
$approved_application = Application::GetApprovedApplication($_SESSION['username']);
$can_apply = Application::CanApplyForLeave($_SESSION['username']);

// Kenyan holidays for 2025
$kenyan_holidays = [
    '2025-01-01', // New Year's Day
    '2025-04-18', // Good Friday
    '2025-04-21', // Easter Monday
    '2025-05-01', // Labour Day
    '2025-06-02', // Madaraka Day
    '2025-10-20', // Mashujaa Day
    '2025-12-12', // Jamhuri Day
    '2025-12-25', // Christmas Day
    '2025-12-26',// Boxing Day
    '2025-06-15' ,// Gen-Z revolution
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Application Form</title>
    <link rel="stylesheet" href="leaver.css">
</head>

<body>
    <div class="form-container">
    
        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div class="success-container">
                <div class="success-message">
                    <div class="success-icon">✓</div>
                    <h3>Application Submitted Successfully!</h3>
                    <p>Your leave application has been submitted successfully.<br>
                    Please wait for approval before proceeding to leave.</p>
                    <div class="success-actions">
                    <?php if($user_role=='staff'): ?>
                        <a href="staff.php" class="dashboard-btn">Back to Dashboard</a>
                    <?php else: ?>
                        <a href="hod.php" class="dashboard-btn">Back to Admin Dashboard</a>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
            
        <?php elseif ($approved_application && ($approved_application['viewed'] == 0 || $approved_application['printed'] == 0)): ?>
            <div class="success-container">
                <div class="success-message">
                    <div class="success-icon">✓</div>
                    <h3>Your leave application has been approved!</h3>
                    <p>Application ID: #<?php echo htmlspecialchars($approved_application['id']); ?><br>
                    You can now view and print your approved application.</p>
                    <div class="success-actions">
                        <a href="view_approved.php?id=<?php echo htmlspecialchars($approved_application['id']); ?>" class="view-btn">
                            <span style="font-size:1.2em;">👁️</span> View Application
                        </a>
                        <a href="print.php?id=<?php echo htmlspecialchars($approved_application['id']); ?>" class="print-btn" target="_blank">
                            <span style="font-size:1.2em;">🖨️</span> Print Application
                        </a>
                         <?php if($user_role=='staff'): ?>
                        <a href="staff.php" class="dashboard-btn">Back to Dashboard</a>
                    <?php else: ?>
                        <a href="hod.php" class="dashboard-btn">Back to HOD Dashboard</a>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php elseif ($approved_application && $approved_application['status'] === 'Rejected'): ?>
                <div class="error-container">
                <div class="error-message">
                    <h3>Your leave application was rejected</h3>
                    <p>Application ID: #<?php echo htmlspecialchars($approved_application['id']); ?><br>
                    Reason: <?php echo htmlspecialchars($approved_application['rejection_reason'] ?? 'No reason provided'); ?></p>
                    <div class="info-actions">
                    <?php if($user_role ==='STAFF'): ?>
                        <a href="staff.php" class="dashboard-btn">Back to staff dashboard</a>
                    <?php else: ?>
                        <a href="hod.php" class="dashboard-btn">Back to HOD Dashboard</a>
                    <?php endif; ?>
                    </div>
                </div>
                </div>
            <?php endif; ?>
            
        <?php if (isset($_GET['error'])): ?>
            <div class="error-container">
                <div class="error-message">
                    <p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
                     <?php if($user_role ==='STAFF'): ?>
                        <a href="staff.php" class="dashboard-btn">Back to staff dashboard</a>
                    <?php else: ?>
                        <a href="hod.php" class="dashboard-btn">Back to HOD Dashboard</a>
                    <?php endif; ?>
                </div>
            </div>
            
        <?php elseif ($pending_application && $pending_application['status'] === 'Pending'): ?>
            <div class="pending-container">
                <div class="pending-message">
                    <div class="pending-icon">⏳</div>
                    <h3>You have a pending leave application</h3>
                    <p>Application ID: #<?php echo htmlspecialchars($pending_application['id']); ?><br>
                    Submitted on: <?php echo date('d/m/Y H:i', strtotime($pending_application['created_at'])); ?><br>
                    Leave Type: <?php echo htmlspecialchars($pending_application['leave_type']); ?><br>
                    Leave Days: <?php echo htmlspecialchars($pending_application['leave_days']); ?> days</p>
                    <p>Please wait for approval before submitting another application.</p>
                    <div class="pending-actions">
                     <?php if($user_role=='staff'): ?>
                        <a href="staff.php" class="dashboard-btn">Back to  Staff Dashboard</a>
                    <?php else: ?>
                        <a href="hod.php" class="dashboard-btn">Back to HOD Dashboard</a>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
            
        <?php elseif (!$can_apply): ?>
            <div class="info-container">
                <div class="info-message">
                    <div class="info-icon">ℹ️</div>
                    <h3>Cannot Apply for Leave</h3>
                    <p>You cannot apply for new leave at this time. This may be due to:</p>
                    <ul>
                        <li>You have a recently approved application that hasn’t been printed</li>
                        <li>You need to wait for the current leave period to complete</li>
                    </ul>
                    <div class="info-actions">
                        <?php if($user_role=='staff'): ?>
                        <a href="staff.php" class="dashboard-btn">Back to Staff Dashboard</a>
                    <?php else: ?>
                        <a href="hod.php" class="dashboard-btn">Back to HOD Dashboard</a>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <div class="header">
                <img src="download.png" alt="coat-of-arms">
                <h2>REPUBLIC OF KENYA</h2>
                <h3>MINISTRY OF INFORMATION, COMMUNICATIONS AND THE DIGITAL ECONOMY</h3>
                <h3>STATE DEPARTMENT FOR ICT AND DIGITAL ECONOMY</h3>
                <h4>LEAVE APPLICATION FORM</h4>
                <p>The Principal Secretary<br>State Department for ICT & Digital Economy<br>P.O. Box 30025<br>NAIROBI.</p>
                <h4>APPLICATION FOR LEAVE</h4>
                <p>(To be submitted at least 30 days before the leave is due to begin)</p>
            </div>

            <form action="submit.php" method="POST">
                <div class="section">
                    <h4>PART I</h4>
                    <p>(To be completed by the applicant)</p>

                    <div>
                        <label for="name">1. NAME:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                    </div>
                    
                    <div>
                        <label for="personal_number">P/NO.:</label>
                        <input type="text" id="personal_number" name="personal_number" value="<?php echo htmlspecialchars($personal_number); ?>" readonly>
                    </div>
                    
                    <div>
                        <label for="designation">Designation:</label>
                        <input type="text" id="designation" name="designation" value="<?php echo htmlspecialchars($designation); ?>" readonly>
                    </div>
                    
                    <div>
                        <label for="leave_type">Type of Leave:</label>
                        <select id="leave_type" name="leave_type" onchange="calculateLeaveBalance();" required>
                            <option value="">Select Leave Type</option>
                            <option value="Annual Leave">Annual Leave (30 Days)</option>
                            <option value="Maternity Leave">Maternity Leave (90 Days)</option>
                            <option value="Paternity Leave">Paternity Leave (14 Days)</option>
                            <option value="Sick Leave">Sick Leave (30 Days)</option>
                        </select>
                    </div>
                    
                    <div>
                        <label>I hereby apply for:</label>
                        <input type="number" id="leave_days" name="leave_days" min="1" max="30" style="width: 60px;" oninput="calculateEndDate(); calculateLeaveBalance();" required> days leave
                    </div>
                    
                    <div>
                        <label>Beginning on:</label>
                        <input type="date" id="leave_start_date" name="leave_start_date" oninput="calculateEndDate();" required>
                    </div>
                    
                    <div>
                        <label>To:</label>
                        <input type="date" id="leave_end_date" name="leave_end_date" readonly>
                    </div>
                    
                    <div>
                        <label>Last leave was from:</label>
                        <input type="date" id="last_leave_start_date" name="last_leave_start_date" onchange="updateLastLeaveDays();">
                        
                    </div>
                    <div>
                        <label>To:</label>
                        <input type="date" id="last_leave_end_date" name="last_leave_end_date" onchange="updateLastLeaveDays();">
                    </div>
                    <div>
                        <label>Total leave days taken this year:</label>
                        <input type="number" id="leave_taken" name="leave_taken" min="0" max="30" style="width: 60px;" oninput="calculateLeaveBalance();" value="0">
                    </div>
                    
                    <div>
                        <label>Total leave days balance to date is:</label>
                        <input type="number" id="leave_balance" name="leave_balance" style="width: 60px;" readonly> days
                    </div>
                    
                    <div class="section">
                        <label>2. While on leave, my contact will be:</label>
                     <div>
                            <label for="contact_address">Address:</label>
                        <input type="text" id="contact_address" name="contact_address"   required >
                    </div>
                    <div>
                        <label for="telephone">Tel.:</label>
                        <input type="tel" id="telephone" name="telephone" required>
                    </div>
                    </div>
                   
                    
                    <div class="section">
                        <label>3. During the period of my leave salary should:</label><br>
                        <input type="radio" id="bank_account" name="salary_payment" value="bank_account" checked required>
                        <label for="bank_account">Continue to be paid into my bank account</label><br>
        
                         <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="radio" id="other_address" name="salary_payment" value="other_address">
                            <label for="other_address">Be paid at the following address:</label>
                            <textarea name="salary_payment_address" rows="2" style="resize: none; width: 250px; height: 50px; " placeholder="Only fill if selecting 'other address' option above"></textarea>
                        </div>
                    </div>
                    
                    <div class="section">
                        <label>4. I understand that I will require permission should I desire to spend leave outside Kenya in accordance with Human Resource Policies and Procedures Manual 2016.</label><br>
                        <input type="radio" id="outside_kenya_yes" name="outside_kenya_permission" value="Yes" required>
                        <label for="outside_kenya_yes">Yes, I understand</label><br>
                        <input type="radio" id="outside_kenya_no" name="outside_kenya_permission" value="No">
                        <label for="outside_kenya_no">No, I don't plan to travel outside Kenya</label>
                    </div>
                    
                    <div>
                        <label>5. While on leave:</label>
                        <input type="text" name="acting_officer" style="width: 200px;" placeholder="Name of acting officer" required> will handle duties of my office.
                    </div>
                    
                    <div>
                        <label for="applicant_date">Date:</label>
                        <input type="date" id="applicant_date" name="applicant_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div>
                        <label for="signature">Signature:</label>
                        <input type="text" id="applicant_signature" name="signature" placeholder="Type your full name as signature" required>
                    </div>
                </div>

                <div class="submit-btn">
                    <input type="submit" value="Submit Application">
                </div>
            </form>
            
            <div class="print-link">
                
                     <?php if($user_role ==='HOD'): ?>
                        <a href="hod.php" class="dashboard-btn">Back to staff dashboard</a>
                    <?php else: ?>
                        <a href="staff.php" class="dashboard-btn">Back to  Staff Dashboard</a>
                    <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <style>
        .success-container, .pending-container, .info-container, .error-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        
        .success-message, .pending-message, .info-message, .error-message {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 600px;
            width: 90%;
        }
        
        .success-icon, .pending-icon, .info-icon {
            font-size: 60px;
            margin-bottom: 20px;
            display: block;
        }
        
        .success-icon { color: #28a745; }
        .pending-icon { color: #ffc107; }
        .info-icon { color: #17a2b8; }
        
        .success-message h3, .pending-message h3, .info-message h3, .error-message h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 24px;
        }
        
        .success-message p, .pending-message p, .info-message p, .error-message p {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .info-message ul {
            text-align: left;
            color: #666;
            margin-bottom: 20px;
        }
        
        .success-actions, .pending-actions, .info-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
        }
        
        .dashboard-btn, .print-btn, .view-btn {
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .dashboard-btn {
            background: #007bff;
            color: white;
        }
        
        .dashboard-btn:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }
        
        .print-btn {
            background: #28a745;
            color: white;
        }
        
        .print-btn:hover {
            background: #1e7e34;
            transform: translateY(-2px);
        }
        
        .view-btn {
            background: #17a2b8;
            color: white;
        }
        
        .view-btn:hover {
            background: #138496;
            transform: translateY(-2px);
        }
        
        .error {
            color: #721c24;
            background: #f8d7da;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #f5c6cb;
        }
    </style>

    <script>
        // Kenyan holidays
        const holidays = <?php echo json_encode($kenyan_holidays); ?>;

        function isWeekendOrHoliday(date) {
            const day = date.getDay();
            const dateString = date.toISOString().split('T')[0];
            return day === 0 || day === 6 || holidays.includes(dateString);
        }
        function updateLastLeaveDays() {
    const start = document.getElementById('last_leave_start_date').value;
    const end = document.getElementById('last_leave_end_date').value;
    const leaveTakenInput = document.getElementById('leave_taken');

    if (start && end) {
        const startDate = new Date(start);
        const endDate = new Date(end);
        let days = 0;
        let current = new Date(startDate);

        // Count days, skipping weekends and holidays
        while (current <= endDate) {
            if (!isWeekendOrHoliday(current)) {
                days++;
            }
            current.setDate(current.getDate() + 1);
        }

        leaveTakenInput.value = days;
    } else {
        leaveTakenInput.value = 0;
    }
    calculateLeaveBalance();
}

        function calculateEndDate() {
            const startDateInput = document.getElementById('leave_start_date');
            const leaveDaysInput = document.getElementById('leave_days');
            const endDateInput = document.getElementById('leave_end_date');
            
            if (!startDateInput.value || !leaveDaysInput.value) {
                endDateInput.value = '';
                return;
            }

            const startDate = new Date(startDateInput.value);
            const leaveDays = parseInt(leaveDaysInput.value);
            
            validateLeaveDays();

            if (leaveDays > 0 && !isNaN(startDate.getTime())) {
                let currentDate = new Date(startDate);
                let daysCounted = 0;

                while (daysCounted < leaveDays) {
                    currentDate.setDate(currentDate.getDate() + 1);
                    if (!isWeekendOrHoliday(currentDate)) {
                        daysCounted++;
                    }
                }

                endDateInput.value = currentDate.toISOString().split('T')[0];
            }
        }

        function validateLeaveDays() {
            const leaveType = document.getElementById('leave_type').value;
            const leaveDays = parseInt(document.getElementById('leave_days').value) || 0;
            const leaveDaysInput = document.getElementById('leave_days');
            
            const maxDays = {
                'Annual Leave': 30,
                'Maternity Leave': 90,
                'Paternity Leave': 14,
                'Sick Leave': 30
            };

            if (leaveType && leaveDays > maxDays[leaveType]) {
                alert(`Leave days cannot exceed ${maxDays[leaveType]} for ${leaveType}`);
                leaveDaysInput.value = '';
                document.getElementById('leave_end_date').value = '';
                calculateLeaveBalance();
                return false;
            }
            
            if (leaveType) {
                leaveDaysInput.max = maxDays[leaveType];
            }
            
            return true;
        }

        function calculateLeaveBalance() {
            const leaveType = document.getElementById('leave_type').value;
            const leaveTaken = parseInt(document.getElementById('leave_taken').value) || 0;
            const leaveDays = parseInt(document.getElementById('leave_days').value) || 0;
            
            if (!leaveType) {
                document.getElementById('leave_balance').value = '';
                return;
            }
            
            const maxDays = {
                'Annual Leave': 30,
                'Maternity Leave': 90,
                'Paternity Leave': 14,
                'Sick Leave': 30
            };

            const totalUsed = leaveTaken + leaveDays;
            const balance = maxDays[leaveType] - totalUsed;
            document.getElementById('leave_balance').value = Math.max(0, balance);
            
            if (balance < 0) {
                alert(`Warning: This application exceeds your available leave balance by ${Math.abs(balance)} days.`);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            const startDateInput = document.getElementById('leave_start_date');
            const applicantDateInput = document.getElementById('applicant_date');
            
            if (startDateInput) {
                startDateInput.min = today;
            }
            if (applicantDateInput) {
                applicantDateInput.max = today;
            }
            
            calculateLeaveBalance();
        });

        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const leaveBalanceInput = document.getElementById('leave_balance');
                if (leaveBalanceInput) {
                    const leaveBalance = parseInt(leaveBalanceInput.value);
                    if (leaveBalance < 0) {
                        if (!confirm('Your leave balance will be negative. Do you want to proceed?')) {
                            e.preventDefault();
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>