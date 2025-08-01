<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Configuration
$admin_email = 'beatrice@experiencerecruitment.com';
$upload_dir = 'uploads/applications/';
$max_file_size = 10 * 1024 * 1024; // 10MB

// Create upload directory if it doesn't exist
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Sanitize and validate input data
    $data = [];
    $required_fields = ['position', 'first_name', 'last_name', 'address', 'dob', 'ni_number', 'telephone', 'email', 'own_car', 'driving_licence', 'criminal_conviction'];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Required field '$field' is missing");
        }
        $data[$field] = sanitize_input($_POST[$field]);
    }
    
    // Validate email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }
    
    // Validate date of birth
    $dob = DateTime::createFromFormat('Y-m-d', $data['dob']);
    if (!$dob || $dob->format('Y-m-d') !== $data['dob']) {
        throw new Exception('Invalid date of birth');
    }
    
    // Handle file uploads
    $uploaded_files = [];
    $file_fields = ['cv', 'cover_letter', 'certificates'];
    
    foreach ($file_fields as $field) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $uploaded_file = handle_file_upload($_FILES[$field], $upload_dir, $max_file_size);
            if ($uploaded_file) {
                $uploaded_files[$field] = $uploaded_file;
            }
        }
    }
    
    // Validate CV upload (required)
    if (empty($uploaded_files['cv'])) {
        throw new Exception('CV upload is required');
    }
    
    // Generate application ID
    $application_id = 'APP_' . date('Ymd') . '_' . uniqid();
    
    // Save application to database (if you have one) or log file
    save_application($application_id, $data, $uploaded_files);
    
    // Send email notification to admin
    send_admin_notification($admin_email, $application_id, $data, $uploaded_files);
    
    // Send confirmation email to applicant
    send_applicant_confirmation($data['email'], $data['first_name'], $application_id);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Application submitted successfully',
        'application_id' => $application_id
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function sanitize_input($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function handle_file_upload($file, $upload_dir, $max_size) {
    // Check file size
    if ($file['size'] > $max_size) {
        throw new Exception('File size exceeds limit (10MB max)');
    }
    
    // Check file type
    $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
    $file_type = mime_content_type($file['tmp_name']);
    
    if (!in_array($file_type, $allowed_types)) {
        throw new Exception('Invalid file type. Only PDF, DOC, DOCX, JPG, and PNG files are allowed.');
    }
    
    // Generate unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $file_extension;
    $filepath = $upload_dir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
            'original_name' => $file['name'],
            'filename' => $filename,
            'filepath' => $filepath,
            'size' => $file['size']
        ];
    }
    
    throw new Exception('Failed to upload file: ' . $file['name']);
}

function save_application($application_id, $data, $files) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'application_id' => $application_id,
        'data' => $data,
        'files' => $files
    ];
    
    $log_file = 'logs/applications.log';
    
    // Create logs directory if it doesn't exist
    if (!file_exists('logs')) {
        mkdir('logs', 0755, true);
    }
    
    file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
}

function send_admin_notification($admin_email, $application_id, $data, $files) {
    $subject = "New Job Application Received - {$application_id}";
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .header { background-color: #20d34a; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .field { margin-bottom: 10px; }
            .label { font-weight: bold; color: #333; }
            .value { color: #666; }
            .section { margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h2>New Job Application</h2>
            <p>Application ID: {$application_id}</p>
        </div>
        <div class='content'>
            <h3>Personal Details</h3>
            <div class='field'><span class='label'>Position Applied for:</span> <span class='value'>{$data['position']}</span></div>
            <div class='field'><span class='label'>Name:</span> <span class='value'>{$data['first_name']} {$data['last_name']}</span></div>
            <div class='field'><span class='label'>Email:</span> <span class='value'>{$data['email']}</span></div>
            <div class='field'><span class='label'>Phone:</span> <span class='value'>{$data['telephone']}</span></div>
            <div class='field'><span class='label'>Address:</span> <span class='value'>{$data['address']}</span></div>
            <div class='field'><span class='label'>Date of Birth:</span> <span class='value'>{$data['dob']}</span></div>
            <div class='field'><span class='label'>National Insurance:</span> <span class='value'>{$data['ni_number']}</span></div>
            
            <div class='section'>
                <h3>Additional Information</h3>
                <div class='field'><span class='label'>Owns Car:</span> <span class='value'>{$data['own_car']}</span></div>
                <div class='field'><span class='label'>Driving License:</span> <span class='value'>{$data['driving_licence']}</span></div>
                <div class='field'><span class='label'>Criminal Conviction:</span> <span class='value'>{$data['criminal_conviction']}</span></div>
            </div>
            
            <div class='section'>
                <h3>Uploaded Files</h3>";
                
    foreach ($files as $type => $file) {
        $message .= "<div class='field'><span class='label'>" . ucfirst($type) . ":</span> <span class='value'>{$file['original_name']}</span></div>";
    }
    
    $message .= "
            </div>
            <div class='section'>
                <p><strong>Note:</strong> Please review the full application details in your admin panel and contact the applicant within 2-3 business days.</p>
            </div>
        </div>
    </body>
    </html>";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@experiencerecruitment.com" . "\r\n";
    
    mail($admin_email, $subject, $message, $headers);
}

function send_applicant_confirmation($email, $name, $application_id) {
    $subject = "Application Received - Experience Agency Ltd";
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .header { background-color: #20d34a; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .footer { background-color: #f8f9fa; padding: 15px; text-align: center; color: #666; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h2>Thank You for Your Application!</h2>
        </div>
        <div class='content'>
            <p>Dear {$name},</p>
            
            <p>Thank you for submitting your job application to Experience Agency Ltd. We have successfully received your application.</p>
            
            <p><strong>Application Reference:</strong> {$application_id}</p>
            
            <h3>What happens next?</h3>
            <ul>
                <li>Our recruitment team will review your application within 2-3 business days</li>
                <li>If your skills match our current opportunities, we will contact you to discuss next steps</li>
                <li>We may arrange a phone or video interview to learn more about your experience</li>
                <li>We'll help match you with the perfect healthcare role</li>
            </ul>
            
            <p>If you have any questions or need to update your application, please contact us:</p>
            <ul>
                <li><strong>Email:</strong> beatrice@experiencerecruitment.com</li>
                <li><strong>Phone:</strong> 01452 238415</li>
                <li><strong>Address:</strong> 1 Elmfield Park, Bromley, BR1 1LU</li>
            </ul>
            
            <p>Thank you for considering Experience Agency Ltd for your healthcare career.</p>
            
            <p>Best regards,<br>
            The Experience Agency Recruitment Team</p>
        </div>
        <div class='footer'>
            <p>Experience Agency Ltd | Healthcare Recruitment Specialists</p>
        </div>
    </body>
    </html>";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Experience Agency Ltd <noreply@experiencerecruitment.com>" . "\r\n";
    
    mail($email, $subject, $message, $headers);
}
?>
