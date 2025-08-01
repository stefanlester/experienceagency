# Experience Agency - Healthcare Recruitment Website

## New Features Added

### 🚀 Online Job Application System

We've successfully converted the PDF application form into a fully functional online application system that provides a much better user experience.

#### ✅ Features Implemented:

1. **Online Application Form** (`application.html`)
   - Matches the original PDF exactly
   - Mobile-responsive design
   - Real-time form validation
   - File upload support (CV, cover letter, certificates)
   - Auto-save draft functionality
   - Professional styling consistent with the site

2. **Navigation Integration**
   - Added "Apply Online" link to all pages
   - Updated contact page to promote online applications
   - Enhanced PDF downloads section with online option

3. **Form Submission System**
   - JavaScript validation and enhancement (`js/application.js`)
   - PHP backend processing (`submit_application.php`)
   - Email notifications to admin and applicants
   - File upload handling with security validation
   - Application logging system

#### 📋 Form Sections Include:

- **Position Applied for**
- **Personal Details** (including car ownership and driving license)
- **Employment History** (current and previous employer)
- **Criminal Conviction** (with full legal disclaimers)
- **Education & Training** (table format matching PDF)
- **Professional Body Membership**
- **Right to Work in UK**
- **References** (2 professional references)
- **Document Uploads** (CV required, others optional)
- **Additional Information**
- **Declaration & Data Protection**

#### 🔧 Technical Features:

- **Real-time validation** with visual feedback
- **File size and type validation** (10MB max, PDF/DOC/DOCX/JPG/PNG)
- **Email notifications** to both admin and applicants
- **Secure file uploads** with unique filename generation
- **Application logging** for record keeping
- **Mobile-responsive design**
- **Accessibility features** with proper form labels
- **Professional success/error messaging**

#### 📁 Files Added/Modified:

- `application.html` - New online application form
- `js/application.js` - Form validation and submission handling
- `submit_application.php` - Backend form processing
- `contact.html` - Updated to promote online applications
- `index.html` - Updated navigation menu

#### 🚀 Benefits:

1. **Better User Experience**: No need to download, print, fill, and scan PDFs
2. **Faster Processing**: Instant submission and email notifications
3. **Mobile-Friendly**: Works perfectly on all devices
4. **Professional**: Matches website design and branding
5. **Efficient**: Automatic file handling and data processing
6. **Secure**: File validation and secure upload handling

#### 📧 Email Configuration:

The system sends automatic emails to:
- **Admin**: `beatrice@experiencerecruitment.com` (new application notifications)
- **Applicants**: Confirmation emails with application reference

#### 📂 Directory Structure:

```
uploads/applications/    # Uploaded files storage
logs/                   # Application logs
js/application.js       # Frontend JavaScript
submit_application.php  # Backend processing
```

#### 🔐 Security Features:

- File type validation (only safe formats allowed)
- File size limits (10MB maximum)
- Input sanitization and validation
- Secure file naming to prevent conflicts
- Email validation and formatting

#### 📱 Responsive Design:

The application form works perfectly on:
- Desktop computers
- Tablets
- Mobile phones
- All modern browsers

---

## Setup Instructions:

1. Ensure PHP is enabled on your web server
2. Set appropriate permissions for upload directories
3. Configure email settings if needed
4. Test the form submission process
5. Monitor the logs for any issues

The online application system is now live and ready to provide a superior experience for job applicants!
