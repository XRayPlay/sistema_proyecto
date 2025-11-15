<?php
function showNotification($message, $type = 'error') {
    $icon = $type === 'error' ? 'exclamation-circle' : 'check-circle';
    $class = $type === 'error' ? 'alert-danger' : 'alert-success';
    
    return "
    <div id='global-notification' class='alert {$class} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3' role='alert' style='z-index: 9999; max-width: 90%;'>
        <i class='fas fa-{$icon} me-2'></i>
        {$message}
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>
    <script>
        // Auto-hide after 5 seconds
        setTimeout(() => {
            const notification = document.getElementById('global-notification');
            if (notification) {
                const bsAlert = new bootstrap.Alert(notification);
                bsAlert.close();
            }
        }, 5000);
    </script>
    ";
}
?>
