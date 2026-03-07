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

<div class="profile-box">
    <h2>Account Information</h2>
    
    <p>
        <strong>Email:</strong> <?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>
    </p>
    
    <p>
        <strong>Role:</strong> 
        <span class="text-capitalize">
            <?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?>
        </span>
    </p>
    
    <p>
        <strong>Session Remaining:</strong> 
        <code><?= sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds) ?></code>
        <br>
        <small class="form-hint">
            Your session will expire after 1 hour of inactivity.
        </small>
    </p>
    
    <p>
        <strong>Member Since:</strong> 
        <span id="member-since">Today</span>
    </p>
    
    <hr class="hr-divider">
    
    <h3>Security</h3>
    
    <p>
        <a href="/profile/change-password" class="link-primary">
            Change Password
        </a>
    </p>
    
    <p>
        <form method="POST" action="/logout" class="d-inline">
            <?= Csrf::field() ?>
            <button type="submit" class="btn btn-danger">
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
