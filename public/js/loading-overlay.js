// First, let's create the CSS for our loading overlay
const style = document.createElement('style');
style.textContent = `
  .loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    transition: opacity 0.3s ease;
  }

  .loader {
    width: 50px;
    height: 50px;
    border: 5px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 1s ease-in-out infinite;
  }

  @keyframes spin {
    to { transform: rotate(360deg); }
  }
`;
document.head.appendChild(style);

// Create the overlay and loader elements
const overlay = document.createElement('div');
overlay.className = 'loading-overlay';

const loader = document.createElement('div');
loader.className = 'loader';

overlay.appendChild(loader);
document.body.appendChild(overlay);

// Function to remove the loading overlay
function hideLoadingOverlay() {
    overlay.style.opacity = '0';
    setTimeout(() => {
        if (overlay.parentNode) {
            overlay.parentNode.removeChild(overlay);
        }
    }, 300); // Wait for the fade out transition to complete
}

// Hide the overlay when the page is fully loaded
if (document.readyState === 'complete') {
    hideLoadingOverlay();
} else {
    window.addEventListener('load', hideLoadingOverlay);
}

// Optional: Add a fallback to hide the overlay after a certain time
// in case the load event doesn't fire properly
setTimeout(hideLoadingOverlay, 10000); // 10 seconds timeout
