// ============================================================================
// SAVE THIS AS: lightbox.js (Generic - Reusable across all pages)
// ============================================================================

(function() {
‘use strict’;

const Lightbox = {
overlay: null,
container: null,
closeBtn: null,
contentArea: null,
isOpen: false,

```
init: function() {
  // Create lightbox HTML structure
  this.createLightbox();
  // Listen for clicks on data-lightbox links
  this.bindEvents();
},

createLightbox: function() {
  // Create overlay
  this.overlay = document.createElement('div');
  this.overlay.id = 'lightbox-overlay';
  this.overlay.style.cssText = `
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    z-index: 9998;
    opacity: 0;
    transition: opacity 0.3s ease;
  `;

  // Create container
  this.container = document.createElement('div');
  this.container.id = 'lightbox-container';
  this.container.style.cssText = `
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.9);
    max-width: 90vw;
    max-height: 90vh;
    z-index: 9999;
    opacity: 0;
    transition: all 0.3s ease;
  `;

  // Create close button
  this.closeBtn = document.createElement('button');
  this.closeBtn.innerHTML = '×';
  this.closeBtn.setAttribute('aria-label', 'Close lightbox');
  this.closeBtn.style.cssText = `
    position: absolute;
    top: -48px;
    right: 0;
    background: rgba(255, 255, 255, 0.1);
    border: 2px solid white;
    border-radius: 50%;
    color: white;
    font-size: 32px;
    width: 40px;
    height: 40px;
    cursor: pointer;
    padding: 0;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    font-weight: 300;
  `;
  this.closeBtn.onmouseover = () => {
    this.closeBtn.style.background = 'white';
    this.closeBtn.style.color = '#000';
  };
  this.closeBtn.onmouseout = () => {
    this.closeBtn.style.background = 'rgba(255, 255, 255, 0.1)';
    this.closeBtn.style.color = 'white';
  };

  // Create content area
  this.contentArea = document.createElement('div');
  this.contentArea.id = 'lightbox-content';
  this.contentArea.style.cssText = `
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    max-width: 90vw;
    max-height: 85vh;
    overflow: auto;
  `;

  // Assemble lightbox
  this.container.appendChild(this.closeBtn);
  this.container.appendChild(this.contentArea);
  document.body.appendChild(this.overlay);
  document.body.appendChild(this.container);
},

bindEvents: function() {
  const self = this;

  // Handle clicks on data-lightbox links
  document.addEventListener('click', function(e) {
    const link = e.target.closest('[data-lightbox]');
    if (!link) return;

    e.preventDefault();
    const type = link.getAttribute('data-lightbox');
    const src = link.getAttribute('href') || link.getAttribute('data-src');
    self.open(type, src);
  });

  // Close button
  this.closeBtn.addEventListener('click', () => this.close());

  // Click outside to close
  this.overlay.addEventListener('click', () => this.close());

  // ESC key to close
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && this.isOpen) {
      this.close();
    }
  });
},

open: function(type, src) {
  this.isOpen = true;
  this.contentArea.innerHTML = '';

  // Create content based on type
  if (type === 'image') {
    const img = document.createElement('img');
    img.src = src;
    img.alt = 'Lightbox image';
    img.style.cssText = 'display: block; max-width: 100%; max-height: 85vh; width: auto; height: auto;';
    this.contentArea.appendChild(img);
    this.show();
  } 
  else if (type === 'video') {
    const iframe = document.createElement('iframe');
    iframe.src = src;
    iframe.setAttribute('frameborder', '0');
    iframe.setAttribute('allowfullscreen', 'true');
    iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
    iframe.style.cssText = 'width: 80vw; height: 45vw; max-width: 1200px; max-height: 70vh;';
    this.contentArea.appendChild(iframe);
    this.show();
  } 
  else if (type === 'html') {
    this.contentArea.innerHTML = src;
    this.show();
  }
  else if (type === 'ajax' || type === 'php') {
    // Show loading indicator
    this.contentArea.innerHTML = '<div style="padding: 3rem; text-align: center; color: #64748b;">Loading...</div>';
    this.show();
    
    // Fetch PHP file content
    fetch(src)
      .then(response => {
        if (!response.ok) {
          throw new Error('Failed to load content');
        }
        return response.text();
      })
      .then(html => {
        this.contentArea.innerHTML = html;
        // Execute any scripts in the loaded content
        const scripts = this.contentArea.querySelectorAll('script');
        scripts.forEach(script => {
          const newScript = document.createElement('script');
          if (script.src) {
            newScript.src = script.src;
          } else {
            newScript.textContent = script.textContent;
          }
          document.body.appendChild(newScript);
        });
      })
      .catch(error => {
        this.contentArea.innerHTML = '<div style="padding: 3rem; text-align: center; color: #ef4444;">Error loading content: ' + error.message + '</div>';
      });
  }
},

show: function() {
  // Show lightbox
  this.overlay.style.display = 'block';
  this.container.style.display = 'block';
  document.body.style.overflow = 'hidden';

  // Trigger animation
  setTimeout(() => {
    this.overlay.style.opacity = '1';
    this.container.style.opacity = '1';
    this.container.style.transform = 'translate(-50%, -50%) scale(1)';
  }, 10);
},

close: function() {
  this.isOpen = false;
  
  // Fade out
  this.overlay.style.opacity = '0';
  this.container.style.opacity = '0';
  this.container.style.transform = 'translate(-50%, -50%) scale(0.9)';

  // Hide after animation
  setTimeout(() => {
    this.overlay.style.display = 'none';
    this.container.style.display = 'none';
    this.contentArea.innerHTML = '';
    document.body.style.overflow = '';
  }, 300);
}
```

};

// Initialize when DOM is ready
if (document.readyState === ‘loading’) {
document.addEventListener(‘DOMContentLoaded’, () => Lightbox.init());
} else {
Lightbox.init();
}

})();