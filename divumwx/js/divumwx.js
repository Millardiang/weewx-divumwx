
(function() {
    'use strict';
    
    console.log('🚀 Initializing DivumWX Dashboard v<?= $appVersion ?>');
    
    // ===== GLOBAL STATE =====
    const state = {
        modal: {
            isOpen: false,
            isClosing: false,
            currentModal: null
        },
        theme: localStorage.getItem('theme') || <?= json_encode($theme_pref, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) ?> || 'auto'
    };
    
    // ===== CONFIGURATION =====
    const CONFIG = {
        modalsPath: './modals/', // Default path for modal content
        defaultSize: 'md',
        defaultButtons: true,
        animationSpeed: 300,
        enableResize: true,
        debug: true
    };
    
    // ===== UTILITY FUNCTIONS =====
    const setCookie = (name, value) => {
        document.cookie = `${name}=${encodeURIComponent(value)}; Max-Age=31536000; Path=/; SameSite=Lax`;
    };
    
    const formatTitle = (str) => {
        if (!str) return 'Modal';
        return str.replace(/[-_]/g, ' ')
            .replace(/\b\w/g, l => l.toUpperCase());
    };
    
    // ===== MODAL SYSTEM =====
    
    // Update modal theme colors
    function updateModalTheme() {
        const isDark = document.documentElement.classList.contains('dark');
        const modalContainer = document.getElementById('enhancedModalContainer');
        const modalHeader = document.querySelector('.modal-header');
        const closeBtn = document.getElementById('enhancedModalClose');
        
        if (modalContainer) {
            if (isDark) {
                modalContainer.style.backgroundColor = 'var(--modal-bg-dark, #1a1a1a)';
                modalContainer.style.color = 'var(--text-primary-dark, #f0f0f0)';
                modalContainer.style.borderColor = 'var(--border-color-dark, #444)';
            } else {
                modalContainer.style.backgroundColor = 'var(--modal-bg-light, #ffffff)';
                modalContainer.style.color = 'var(--text-primary-light, #333)';
                modalContainer.style.borderColor = 'var(--border-color-light, #ddd)';
            }
        }
        
        // Fix header border (remove extra border)
        if (modalHeader) {
            modalHeader.style.borderBottom = 'none';
        }
        
        // Fix close button positioning
        if (closeBtn) {
            closeBtn.style.position = 'absolute';
            closeBtn.style.top = '1rem';
            closeBtn.style.right = '1rem';
            closeBtn.style.margin = '0';
            closeBtn.style.padding = '0.5rem';
            closeBtn.style.zIndex = '1000';
        }
    }
    
    // Open modal function with full configuration support
    function openModal(config) {
        if (state.modal.isOpen || state.modal.isClosing) {
            console.log('Modal busy, retrying...');
            setTimeout(() => openModal(config), 100);
            return;
        }
        
        console.log('📂 Opening modal with config:', config);
        
        const backdrop = document.getElementById('enhancedModalBackdrop');
        const container = document.getElementById('enhancedModalContainer');
        const title = document.getElementById('enhancedModalTitle');
        const body = document.getElementById('enhancedModalBody');
        
        if (!backdrop || !container || !title || !body) {
            console.error('❌ Modal elements not found');
            return;
        }
        
        // Set current modal
        state.modal.currentModal = config.name || 'unknown';
        state.modal.isOpen = true;
        
        // Set title
        title.textContent = config.title || formatTitle(config.name) || 'Modal';
        
        // Clear previous classes
        container.className = 'modal-container';
        
        // Apply size class
        if (config.size) {
            container.classList.add('modal-' + config.size);
        } else {
            container.classList.add('modal-' + CONFIG.defaultSize);
        }
        
        // Apply fullscreen class if needed
        if (config.full === 'true' || config.full === true) {
            container.classList.add('modal-full');
        }
        
        // Apply custom dimensions
        if (config.modalWidth) {
            container.style.width = config.modalWidth;
            console.log(`📏 Set modal width: ${config.modalWidth}`);
        }
        
        if (config.modalHeight) {
            container.style.height = config.modalHeight;
            console.log(`📏 Set modal height: ${config.modalHeight}`);
        }
        
        if (config.modalMaxWidth) {
            container.style.maxWidth = config.modalMaxWidth;
        }
        
        if (config.modalMaxHeight) {
            container.style.maxHeight = config.modalMaxHeight;
        }
        
        // Show modal
        backdrop.style.display = 'block';
        container.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        // Force reflow for animation
        void backdrop.offsetWidth;
        
        // Animate in
        backdrop.classList.add('show');
        container.classList.add('show');
        
        // Load content based on type
        if (config.url) {
            loadModalContent(config, body);
        } else if (config.content) {
            console.log('📝 Using inline content');
            body.innerHTML = config.content;
            updateModalTheme();
        } else {
            showError(body, 'No content or URL provided for modal');
        }
        
        // Update theme immediately
        updateModalTheme();
    }
    
    // Load modal content with type detection
    function loadModalContent(config, bodyElement) {
        console.log('📥 Loading URL:', config.url, 'Type:', config.type);
        
        // Show loading indicator
        bodyElement.innerHTML = `
            <div class="modal-loading">
                <div class="modal-spinner"></div>
                <p>Loading content...</p>
                <p style="font-size: 0.9rem; color: #666; margin-top: 10px; font-family: monospace;">
                    ${config.url}
                </p>
            </div>
        `;
        
        // Handle different content types
        if (config.type === 'iframe') {
            loadIframeContent(config, bodyElement);
        } else if (config.type === 'image') {
            loadImageContent(config, bodyElement);
        } else {
            // Default to HTML fetch
            loadHtmlContent(config, bodyElement);
        }
    }
    
    function loadIframeContent(config, bodyElement) {
        const iframeHeight = config.height || '600px';
        const showButtons = config.buttons !== undefined ? 
            (config.buttons === 'true' || config.buttons === true) : 
            CONFIG.defaultButtons;
        
        const sandboxAttr = config.sandbox ? `sandbox="${config.sandbox}"` : '';
        
        let iframeHtml = `
            <div style="width: 100%; height: ${iframeHeight};">
                <iframe 
                    src="${config.url}"
                    style="width: 100%; height: 100%; border: none;"
                    frameborder="0"
                    ${sandboxAttr}
                    allowfullscreen
                    title="${config.title}"
                ></iframe>
            </div>
        `;
        
        if (showButtons) {
            iframeHtml += `
                <div style="padding: 15px; border-top: 1px solid #eee; text-align: center;">
                    <button onclick="window.open('${config.url}', '_blank')" 
                            style="padding: 8px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;">
                        Open in New Tab
                    </button>
                    <button onclick="closeModal()" 
                            style="padding: 8px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        Close
                    </button>
                </div>
            `;
        }
        
        bodyElement.innerHTML = iframeHtml;
        updateModalTheme();
    }
    
    function loadImageContent(config, bodyElement) {
        const imageHtml = `
            <div style="text-align: center; padding: 20px;">
                <img src="${config.url}" 
                     alt="${config.title}"
                     style="max-width: 100%; max-height: 80vh; border-radius: 8px;">
                <div style="margin-top: 20px;">
                    <button onclick="closeModal()" 
                            style="padding: 8px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;">
                        Close
                    </button>
                    <a href="${config.url}" 
                       download="${config.title.replace(/\s+/g, '-').toLowerCase()}.jpg"
                       style="padding: 8px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 4px;">
                        Download
                    </a>
                </div>
            </div>
        `;
        
        bodyElement.innerHTML = imageHtml;
        updateModalTheme();
    }
    
    function loadHtmlContent(config, bodyElement) {
        // Fetch content with timeout
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000);
        
        fetch(config.url, { signal: controller.signal })
            .then(response => {
                clearTimeout(timeoutId);
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.text();
            })
            .then(html => {
                console.log('✅ HTML content loaded successfully');
                bodyElement.innerHTML = html;
                updateModalTheme();
            })
            .catch(error => {
                clearTimeout(timeoutId);
                console.error('❌ Failed to load modal content:', error);
                showError(bodyElement, error.message, config.url);
            });
    }
    
    function showError(bodyElement, message, url = '') {
        bodyElement.innerHTML = `
            <div style="padding: 40px; text-align: center;">
                <div style="font-size: 3rem; color: #dc3545;">⚠️</div>
                <h3 style="color: #dc3545;">Load Failed</h3>
                <p>${message}</p>
                ${url ? `<p style="font-size: 0.9rem; color: #666; background: #f8f9fa; padding: 10px; border-radius: 4px; margin: 15px 0;">
                    URL: ${url}
                </p>` : ''}
                <button onclick="closeModal()" 
                        style="padding: 10px 25px; background: #dc3545; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                    Close Modal
                </button>
            </div>
        `;
    }
    
    // Close modal function
    function closeModal() {
        if (!state.modal.isOpen || state.modal.isClosing) return;
        
        console.log('📭 Closing modal');
        state.modal.isClosing = true;
        
        const backdrop = document.getElementById('enhancedModalBackdrop');
        const container = document.getElementById('enhancedModalContainer');
        const body = document.getElementById('enhancedModalBody');
        
        backdrop.classList.remove('show');
        container.classList.remove('show');
        
        setTimeout(() => {
            backdrop.style.display = 'none';
            container.style.display = 'none';
            document.body.style.overflow = 'auto';
            
            // Reset content
            if (body) {
                body.innerHTML = `
                    <div class="modal-loading">
                        <div class="modal-spinner"></div>
                        <p>Loading content...</p>
                    </div>
                `;
            }
            
            // Reset container styles
            container.style.width = '';
            container.style.height = '';
            container.style.maxWidth = '';
            container.style.maxHeight = '';
            container.className = 'modal-container';
            
            // Reset state
            state.modal.isOpen = false;
            state.modal.isClosing = false;
            state.modal.currentModal = null;
        }, CONFIG.animationSpeed);
    }
    
    // Initialize modal system
    function initModalSystem() {
        console.log('🛠️ Initializing modal system...');
        
        // Bind close button
        const closeBtn = document.getElementById('enhancedModalClose');
        if (closeBtn) {
            closeBtn.addEventListener('click', closeModal);
        }
        
        // Bind backdrop click
        const backdrop = document.getElementById('enhancedModalBackdrop');
        if (backdrop) {
            backdrop.addEventListener('click', function(e) {
                if (e.target === backdrop) {
                    closeModal();
                }
            });
        }
        
        // Bind escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && state.modal.isOpen && !state.modal.isClosing) {
                closeModal();
            }
        });
        
        // Expose to global scope
        window.openModal = openModal;
        window.closeModal = closeModal;
        window.updateModalTheme = updateModalTheme;
        
        console.log('✅ Modal system ready');
    }
    
    // ===== DATA-MODAL TRIGGER HANDLER =====
    function setupDataModalTriggers() {
        console.log('🔗 Setting up data-modal triggers...');
        
        // Handle clicks on elements with data-modal attribute
        document.addEventListener('click', function(e) {
            // Find the closest element with data-modal attribute
            let target = e.target;
            let modalTrigger = null;
            
            // Traverse up the DOM to find data-modal attribute
            while (target && target !== document.body) {
                if (target.hasAttribute && target.hasAttribute('data-modal')) {
                    modalTrigger = target;
                    break;
                }
                target = target.parentElement;
            }
            
            if (modalTrigger) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('🖱️ Modal trigger clicked:', modalTrigger);
                
                // Get ALL data attributes
                const modalConfig = {
                    // Basic attributes
                    name: modalTrigger.getAttribute('data-modal'),
                    title: modalTrigger.getAttribute('data-title') || 
                           formatTitle(modalTrigger.getAttribute('data-modal')),
                    
                    // Size and layout
                    size: modalTrigger.getAttribute('data-size') || CONFIG.defaultSize,
                    full: modalTrigger.getAttribute('data-full'),
                    
                    // Content
                    url: modalTrigger.getAttribute('data-url') || 
                        `${CONFIG.modalsPath}${modalTrigger.getAttribute('data-modal')}.php`,
                    type: modalTrigger.getAttribute('data-type') || 'html',
                    content: modalTrigger.getAttribute('data-content'),
                    
                    // Dimensions
                    modalWidth: modalTrigger.getAttribute('data-modal-width'),
                    modalHeight: modalTrigger.getAttribute('data-modal-height'),
                    modalMaxWidth: modalTrigger.getAttribute('data-modal-max-width'),
                    modalMaxHeight: modalTrigger.getAttribute('data-modal-max-height'),
                    width: modalTrigger.getAttribute('data-width'),
                    height: modalTrigger.getAttribute('data-height'),
                    
                    // Options
                    buttons: modalTrigger.getAttribute('data-buttons'),
                    sandbox: modalTrigger.getAttribute('data-sandbox'),
                    params: modalTrigger.getAttribute('data-params')
                };
                
                // Handle href attribute if present
                const href = modalTrigger.getAttribute('href');
                if (href && href !== '#' && !href.startsWith('javascript:') && !modalConfig.url) {
                    modalConfig.url = href;
                }
                
                // Add params to URL if specified
                if (modalConfig.params && modalConfig.url) {
                    const separator = modalConfig.url.includes('?') ? '&' : '?';
                    modalConfig.url += separator + modalConfig.params;
                }
                
                console.log('Modal config parsed:', modalConfig);
                
                // Open the modal
                openModal(modalConfig);
            }
        });
        
        // Log all data-modal elements on page
        const allModalTriggers = document.querySelectorAll('[data-modal]');
        console.log(`📊 Found ${allModalTriggers.length} data-modal triggers on page`);
        
        // Display trigger info in console
        allModalTriggers.forEach((trigger, index) => {
            console.log(`  ${index + 1}. ${trigger.tagName} "${trigger.textContent.trim()}"`);
            console.log(`     data-modal: ${trigger.getAttribute('data-modal')}`);
            console.log(`     data-title: ${trigger.getAttribute('data-title') || '(default)'}`);
            console.log(`     data-type: ${trigger.getAttribute('data-type') || 'html'}`);
            console.log(`     data-url: ${trigger.getAttribute('data-url') || '(default)'}`);
        });
    }
    
    // ===== THEME MANAGEMENT =====
    const root = document.documentElement;
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
    
    const getResolvedTheme = (pref) => {
        return (pref === 'dark' || (pref === 'auto' && prefersDark.matches)) ? 'dark' : 'light';
    };
    
    const applyTheme = (pref) => {
        const resolved = getResolvedTheme(pref);
        
        // Update DOM
        root.setAttribute('data-theme', resolved);
        root.classList.toggle('dark', resolved === 'dark');
        
        // Update theme toggle button
        const themeBtn = document.getElementById('themeToggle');
        if (themeBtn) {
            themeBtn.textContent = resolved === 'dark' ? '☀️' : '🌙';
            themeBtn.title = `Switch to ${resolved === 'dark' ? 'light' : 'dark'} theme`;
        }
        
        // Update modal theme
        updateModalTheme();
        
        // Save preferences
        localStorage.setItem('theme', resolved);
        setCookie('theme', resolved);
        setCookie('theme_pref', pref);
        
        state.theme = pref;
    };
    
    // Initialize theme
    applyTheme(state.theme);
    
    // ===== EVENT LISTENERS =====
    
    // Theme toggle
    const themeBtn = document.getElementById('themeToggle');
    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            const isDark = root.classList.contains('dark');
            const nextTheme = isDark ? 'light' : 'dark';
            localStorage.setItem('theme', nextTheme);
            applyTheme(nextTheme);
        });
    }
    
    // Info button - opens modal
    const infoBtn = document.getElementById('infoBtn');
    if (infoBtn) {
        infoBtn.addEventListener('click', () => {
            openModal({
                name: 'station-info',
                title: '<?= htmlspecialchars($stationlocation) ?> Weather Station',
                content: `
                    <div style="padding: 20px;">
                        <h3><?= htmlspecialchars($stationlocation) ?> Weather Station</h3>
                        <p>This is a test modal showing station information.</p>
                        <ul>
                            <li><strong>Station:</strong> <?= htmlspecialchars($stationlocation) ?></li>
                            <li><strong>Operational Since:</strong> <?= htmlspecialchars($divum['since']) ?></li>
                            <li><strong>WeeWX Version:</strong> <?= htmlspecialchars($divum['swversion']) ?></li>
                            <li><strong>PHP Version:</strong> <?= htmlspecialchars(substr($phpVersion, 0, 7)) ?></li>
                            <li><strong>DivumWX Version:</strong> <?= $appVersion ?></li>
                            <?php if ($gitVersion): ?>
                            <li><strong>Git Commit:</strong> <?= htmlspecialchars($gitVersion) ?></li>
                            <?php endif; ?>
                        </ul>
                        <p>Click the close button or press ESC to close.</p>
                    </div>
                `,
                size: 'md'
            });
        });
    }
    
    // Test modal button (optional - remove in production)
    const testBtn = document.getElementById('testModalBtn');
    if (testBtn) {
        testBtn.addEventListener('click', () => {
            openModal({
                name: 'aurora-test',
                title: 'Geomagnetic Disturbance (Test)',
                type: 'iframe',
                modalWidth: '800px',
                modalHeight: '800px',
                url: 'dvmAuroraTerminatorModal.php',
                height: '800px',
                buttons: false,
                content: `
                    <div style="padding: 30px; text-align: center;">
                        <h2 style="color: #3498db;">Test Aurora Modal</h2>
                        <p>This simulates what your aurora modal would display.</p>
                        <p>Your actual link would load: <code>dvmAuroraTerminatorModal.php</code></p>
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                            <h4>Link Configuration:</h4>
                            <pre style="text-align: left; background: white; padding: 15px; border-radius: 5px; overflow: auto;">
&lt;a href="#"
   data-modal="aurora"
   data-title="Geomagnetic Disturbance"
   data-type="iframe"
   data-modal-width="800px"
   data-modal-height="800px"
   data-url="dvmAuroraTerminatorModal.php"
   data-height="800px"
   data-buttons="false"&gt;
    Aurora
&lt;/a&gt;</pre>
                        </div>
                        <button onclick="closeModal()" 
                                style="padding: 12px 30px; background: #3498db; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                            Close Modal
                        </button>
                    </div>
                `
            });
        });
    }
    
    // Gauges button
    const gaugesBtn = document.getElementById('gaugesBtn');
    if (gaugesBtn) {
        gaugesBtn.addEventListener('click', () => {
            window.location.assign('./gauges');
        });
    }
    
    // Astronomy button
    const astronomyBtn = document.getElementById('astronomyBtn');
    if (astronomyBtn) {
        astronomyBtn.addEventListener('click', () => {
            window.location.assign('./astronomy.php');
        });
    }
    
    // System theme change listener
    if (prefersDark.addEventListener) {
        prefersDark.addEventListener('change', () => {
            const currentPref = localStorage.getItem('theme') || 'auto';
            if (currentPref === 'auto') {
                applyTheme('auto');
            }
        });
    }
    
    // Sidebar menu
    const menuCheckbox = document.getElementById('sidebarmenu');
    if (menuCheckbox) {
        menuCheckbox.checked = false;
    }
    
    // Add close button to sidebar if not present
    const sidebar = document.getElementById('divumwxsidebarMenu');
    if (menuCheckbox && sidebar && !sidebar.querySelector('.closePlain')) {
        const closeBtn = document.createElement('label');
        closeBtn.className = 'closePlain';
        closeBtn.setAttribute('for', 'sidebarmenu');
        closeBtn.setAttribute('aria-label', 'Close menu');
        closeBtn.textContent = '×';
        sidebar.insertBefore(closeBtn, sidebar.firstChild);
    }
    
    // ===== INITIALIZATION =====
    console.log('⚡ Initializing systems...');
    
    // Initialize modal system
    initModalSystem();
    
    // Setup data-modal triggers (delayed to ensure DOM is ready)
    setTimeout(setupDataModalTriggers, 100);
    
    // Force modal theme update
    setTimeout(updateModalTheme, 200);
    
    // Log initialization complete
    setTimeout(() => {
        console.log('🎉 DivumWX Dashboard initialized successfully!');
        console.log('👉 Supported data attributes:');
        console.log('   • data-modal="name" (required)');
        console.log('   • data-title="Title"');
        console.log('   • data-type="html|iframe|image"');
        console.log('   • data-url="path/to/content.php"');
        console.log('   • data-size="sm|md|lg|xl"');
        console.log('   • data-modal-width="800px"');
        console.log('   • data-modal-height="600px"');
        console.log('   • data-width="100%"');
        console.log('   • data-height="500px"');
        console.log('   • data-buttons="true|false"');
        console.log('   • data-sandbox="allow-scripts allow-same-origin"');
        console.log('   • data-params="key=value&key2=value2"');
        console.log('   • data-full="true"');
    }, 300);
    
})();