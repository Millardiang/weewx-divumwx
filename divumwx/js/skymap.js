document.addEventListener('DOMContentLoaded', function() {
    const fullscreenToggle = document.getElementById('fullscreenToggle');
    const exitFullscreenBtn = document.getElementById('exitFullscreenBtn');
    const skymapBody = document.getElementById('skymapBody');
    const skymapContainer = document.getElementById('skymapContainer');
    const skymapImageContainer = document.getElementById('skymapImageContainer');
    const skymapImage = skymapImageContainer.querySelector('img');
    const autoFullscreenMessage = document.getElementById('autoFullscreenMessage');
    const enterFullscreenBtn = document.getElementById('enterFullscreenBtn');
    
    let autoFullscreenAttempted = false;
    let isInFullscreenMode = false;
    
    // Function to update image size for fullscreen
    function updateFullscreenImageSize() {
        if (skymapImage) {
            // Remove any fixed dimensions and let CSS handle it
            skymapImage.removeAttribute('width');
            skymapImage.removeAttribute('height');
            skymapImage.style.width = '';
            skymapImage.style.height = '';
        }
    }
    
    // Function to restore normal image size
    function restoreNormalImageSize() {
        if (skymapImage) {
            // Restore original dimensions
            skymapImage.width = 500;
            skymapImage.style.maxWidth = '100%';
            skymapImage.style.height = 'auto';
        }
    }
    
    // Function to enter fullscreen
    function enterFullscreen() {
        if (!isInFullscreenMode) {
            // Apply fullscreen styles immediately for visual feedback
            skymapBody.classList.add('fullscreen-active');
            updateFullscreenImageSize();
            fullscreenToggle.textContent = 'Exit Full Screen';
            
            // Show exit button (X) and hide regular fullscreen toggle
            exitFullscreenBtn.style.display = 'block';
            fullscreenToggle.style.display = 'none';
            
            isInFullscreenMode = true;
            
            // Hide the auto fullscreen message if visible
            autoFullscreenMessage.style.display = 'none';
            
            // Attempt to enter browser fullscreen
            if (skymapBody.requestFullscreen) {
                skymapBody.requestFullscreen().catch(err => {
                    console.log(`Fullscreen error: ${err.message}`);
                    // Browser blocked automatic fullscreen, but we've already applied styles
                    // so the user still gets a fullscreen-like experience
                });
            } else if (skymapBody.webkitRequestFullscreen) { /* Safari */
                skymapBody.webkitRequestFullscreen();
            } else if (skymapBody.msRequestFullscreen) { /* IE11 */
                skymapBody.msRequestFullscreen();
            }
        }
    }
    
    // Function to exit fullscreen and return to index.php
    function exitFullscreenAndReturn() {
        if (isInFullscreenMode) {
            // First exit browser fullscreen if active
            if (document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement) {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
            }
            
            // Remove fullscreen styles
            skymapBody.classList.remove('fullscreen-active');
            restoreNormalImageSize();
            
            // Reset button states
            exitFullscreenBtn.style.display = 'none';
            fullscreenToggle.style.display = 'block';
            fullscreenToggle.textContent = 'Full Screen';
            
            isInFullscreenMode = false;
            
            // Redirect to index.php after a brief delay for smooth transition
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 300);
        } else {
            // If not in fullscreen, just redirect to index.php
            window.location.href = 'index.php';
        }
    }
    
    // Function to check if we're in fullscreen mode
    function checkFullscreenStatus() {
        return !!(document.fullscreenElement || 
                  document.webkitFullscreenElement || 
                  document.msFullscreenElement ||
                  skymapBody.classList.contains('fullscreen-active'));
    }
    
    // AUTO-ENTER FULLSCREEN ON PAGE LOAD
    function attemptAutoFullscreen() {
        if (!autoFullscreenAttempted) {
            autoFullscreenAttempted = true;
            
            // Try to enter fullscreen after a short delay
            setTimeout(() => {
                enterFullscreen();
                
                // Check if fullscreen was successful after another delay
                setTimeout(() => {
                    if (!checkFullscreenStatus()) {
                        // If not in fullscreen, show the message prompting user action
                        autoFullscreenMessage.style.display = 'block';
                    } else {
                        isInFullscreenMode = true;
                    }
                }, 500);
            }, 300);
        }
    }
    
    // Call auto fullscreen attempt
    attemptAutoFullscreen();
    
    // Fullscreen toggle button click handler
    fullscreenToggle.addEventListener('click', function() {
        if (!isInFullscreenMode) {
            enterFullscreen();
        } else {
            exitFullscreenAndReturn();
        }
    });
    
    // Exit fullscreen button click handler (returns to index.php) - now just "X"
    exitFullscreenBtn.addEventListener('click', exitFullscreenAndReturn);
    
    // Enter fullscreen button in the message
    enterFullscreenBtn.addEventListener('click', function() {
        enterFullscreen();
        autoFullscreenMessage.style.display = 'none';
    });
    
    // Handle fullscreen change events (when user exits via browser controls)
    document.addEventListener('fullscreenchange', handleFullscreenChange);
    document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
    document.addEventListener('msfullscreenchange', handleFullscreenChange);
    
    function handleFullscreenChange() {
        if (!document.fullscreenElement && !document.webkitFullscreenElement && !document.msFullscreenElement) {
            // User exited fullscreen via browser controls
            if (isInFullscreenMode) {
                // Only remove styles if we were in our fullscreen mode
                skymapBody.classList.remove('fullscreen-active');
                restoreNormalImageSize();
                
                // Reset button states
                exitFullscreenBtn.style.display = 'none';
                fullscreenToggle.style.display = 'block';
                fullscreenToggle.textContent = 'Full Screen';
                
                isInFullscreenMode = false;
                
                // Auto-return to index.php when exiting via browser controls
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 300);
            }
        } else {
            // User entered fullscreen via browser controls
            if (!isInFullscreenMode) {
                // Apply our fullscreen styles
                skymapBody.classList.add('fullscreen-active');
                updateFullscreenImageSize();
                
                // Show exit button (X) and hide regular fullscreen toggle
                exitFullscreenBtn.style.display = 'block';
                fullscreenToggle.style.display = 'none';
                
                isInFullscreenMode = true;
            }
        }
    }
    
    // Adjust on window resize (for fullscreen)
    window.addEventListener('resize', function() {
        if (isInFullscreenMode) {
            updateFullscreenImageSize();
        }
    });
    
    // Ensure image has ID for easier reference
    if (skymapImage && !skymapImage.id) {
        skymapImage.id = 'skymapImage';
    }
    
    // Close message if user clicks outside of it
    document.addEventListener('click', function(event) {
        if (autoFullscreenMessage.style.display === 'block' && 
            !autoFullscreenMessage.contains(event.target) && 
            event.target !== enterFullscreenBtn) {
            autoFullscreenMessage.style.display = 'none';
        }
    });
    
    // Handle Escape key to exit fullscreen and return to index
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && isInFullscreenMode) {
            event.preventDefault(); // Prevent default browser exit behavior
            exitFullscreenAndReturn();
        }
    });
});