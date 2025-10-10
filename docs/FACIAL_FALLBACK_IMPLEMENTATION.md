# Facial Recognition Fallback Implementation

## Overview
This feature implements a security mechanism that prompts users to verify their identity using facial recognition after multiple failed login attempts, provided they have facial authentication already set up.

## Implementation Summary

### 1. Database Changes
- **Migration**: `2025_10_09_211828_add_failed_login_tracking_to_users_table.php`
- **New Fields**:
  - `failed_login_attempts` (integer, default 0)
  - `locked_until` (timestamp, nullable)
  - `is_facial_registered` (boolean, default false)

### 2. Model Updates
- **User Model** (`app/Models/User.php`):
  - Added new fillable fields and casts
  - Added helper methods:
    - `isLockedOut()`: Check if user is temporarily locked
    - `incrementFailedLoginAttempts()`: Increment failed attempts counter
    - `resetFailedLoginAttempts()`: Reset counter and lockout
    - `lockForDuration($minutes)`: Lock user for specified duration
    - `shouldTriggerFacialFallback()`: Check if facial fallback should be triggered

### 3. Service Layer
- **FacialRecognitionService** (`app/Services/FacialRecognitionService.php`):
  - `verifyFacialIdentity()`: Compare face descriptors with stored data
  - `canUseFacialFallback()`: Check eligibility for facial fallback
  - `processFacialFallbackSuccess()`: Handle successful verification
  - `processFacialFallbackFailure()`: Handle failed verification and lockout

### 4. Controller Updates
- **ApiAuthController** (`app/Http/Controllers/ApiAuthController.php`):
  - Modified `login()` method to track failed attempts
  - Added `facialFallback()` method for verification endpoint
  - Added failed login tracking and facial fallback trigger logic

- **FaceAuthController** (`app/Http/Controllers/FaceAuthController.php`):
  - Updated `enroll()` method to set `is_facial_registered` flag

### 5. Route Configuration
- **New API Endpoint**: `POST /api/auth/facial-fallback`
  - Accepts email and face descriptor
  - Returns authentication result or lockout information

### 6. Frontend Implementation
- **Modal UI** (`resources/views/auth/auth.blade.php`):
  - Added facial verification fallback modal
  - Includes camera access, face detection, and verification UI
  - Status messages and result display

- **JavaScript Logic** (`resources/js/auth.js`):
  - Modified login handler to detect facial fallback requirement
  - Added facial verification functionality:
    - `showFacialFallbackModal()`: Display verification modal
    - `startFallbackCamera()`: Initialize camera access
    - `verifyFallbackIdentity()`: Process facial verification
    - Camera cleanup and error handling

### 7. Configuration
- **Auth Config** (`config/auth.php`):
  - `max_failed_attempts`: Maximum failed attempts before fallback (default: 3)
  - `lockout_duration_minutes`: Lockout duration after failed facial verification (default: 30)
  - `facial_recognition_threshold`: Face matching threshold (default: 0.6)

### 8. Notification System
- **FailedFacialVerificationNotification** (`app/Notifications/FailedFacialVerificationNotification.php`):
  - Sends email and database notifications to admins
  - Includes user details, IP address, user agent, and security context
  - Queued for performance

## Workflow

1. **Normal Login Attempt**: User enters email/password
2. **Failed Login**: System increments `failed_login_attempts` counter
3. **Threshold Reached**: After N failed attempts (configurable), if user has facial recognition enabled:
   - API returns status 423 with `requires_facial_fallback: true`
   - Frontend displays facial verification modal
4. **Facial Verification**: User activates camera and verifies identity
5. **Success**: Failed attempts reset, user authenticated normally
6. **Failure**: Account locked temporarily, admins notified

## Security Features

- **Configurable Thresholds**: Adjustable max attempts and lockout duration
- **Admin Notifications**: Automatic alerts for security incidents
- **IP and User Agent Logging**: Track verification attempts
- **Temporary Lockouts**: Prevent brute force attacks
- **Face Matching Confidence**: Configurable similarity threshold

## Environment Variables

Add to `.env` file for configuration:

```env
# Failed Login Settings
AUTH_MAX_FAILED_ATTEMPTS=3
AUTH_LOCKOUT_DURATION_MINUTES=30
AUTH_FACIAL_RECOGNITION_THRESHOLD=0.6
```

## Database Migrations

Run migrations to apply database changes:

```bash
php artisan migrate
```

## Testing Checklist

1. ✅ Failed login attempts are tracked per user
2. ✅ Facial fallback triggers after configured threshold
3. ✅ Facial verification compares against stored descriptors  
4. ✅ Successful verification resets failed attempts
5. ✅ Failed verification locks account temporarily
6. ✅ Admin notifications sent on security incidents
7. ✅ Modal UI provides clear user feedback
8. ✅ Camera access and face detection works properly

## Notes

- Face-api.js library handles face detection and descriptor generation
- Facial recognition only works for users who have enrolled during onboarding
- System gracefully handles users without facial registration
- All security events are logged for audit purposes
- Notification emails include actionable admin links