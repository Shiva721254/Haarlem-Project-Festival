<?php
declare(strict_types=1);

use App\Framework\Csrf;

/** @var array $user */
/** @var int $remaining_time */

$hours = intdiv($remaining_time, 3600);
$minutes = intdiv($remaining_time % 3600, 60);
$seconds = $remaining_time % 60;
?>

<h1>Your Profile</h1>

<div style="max-width:600px; padding:20px; background:#f5f5f5; border-radius:8px;">
    <h2>Account Information</h2>
    
    <p>
        <strong>Email:</strong> <?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>
    </p>
    
    <p>
        <strong>Role:</strong> 
        <span style="text-transform:capitalize;">
            <?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?>
        </span>
    </p>
    
    <p>
        <strong>Session Remaining:</strong> 
        <code><?= sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds) ?></code>
        <br>
        <small style="color:#666;">
            Your session will expire after 1 hour of inactivity.
        </small>
    </p>
    
    <p>
        <strong>Member Since:</strong> 
        <span id="member-since">Today</span>
    </p>
    
    <hr style="margin:20px 0;">
    
    <h3>Security</h3>
    
    <p>
        <a href="/profile/change-password" style="color:#0066cc; text-decoration:none;">
            Change Password
        </a>
    </p>
    
    <p>
        <form method="POST" action="/logout" style="display:inline;">
            <?= Csrf::field() ?>
            <button type="submit" style="background:#dc3545; color:white; padding:8px 16px; border:none; border-radius:4px; cursor:pointer;">
                Logout
            </button>
        </form>
    </p>
</div>

<script>
// Update session timer in real-time (optional)
let remainingSeconds = <?= $remaining_time ?>;
setInterval(() => {
    remainingSeconds--;
    if (remainingSeconds < 0) {
        window.location.href = '/login';
    }
}, 1000);
</script>
