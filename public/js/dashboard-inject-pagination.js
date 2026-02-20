document.addEventListener('DOMContentLoaded', function() {
    // --- Configuration ---
    // These values should be set in the global window object before this script runs
    const config = window.dashboardPaginationConfig || { lastPage: 1, currentPage: 1 };
    const lastPage = config.lastPage;
    let currentPage = config.currentPage;
    const intervalTime = 10000; // 10 seconds
    let autoPageTimer;
    let isPaused = false;

    // --- Tooltip Logic ---
    const tooltip = document.createElement('div');
    tooltip.id = 'machine-hover-info';
    // Styles are now handled by CSS class 'machine-tooltip' (defined in dashboard-inject-tooltip.css)
    tooltip.className = 'machine-tooltip'; 
    document.body.appendChild(tooltip);

    window.initTooltips = function() {
        const cards = document.querySelectorAll('.machine-card-simple');
        cards.forEach(card => {
            card.addEventListener('mouseenter', (e) => {
                isPaused = true;
                window.currentHoveredCard = card;
                
                const nama = card.getAttribute('data-mesin-nama') || '-';
                const brand = card.getAttribute('data-mesin-brand') || '-';
                const tonase = card.getAttribute('data-mesin-tonase') || '-';
                const status = card.getAttribute('data-mesin-status') || '-';
                const stopReason = card.getAttribute('data-stop-reason') || '';
                
                let statusColor = '#ccc';
                let statusLabel = status.toUpperCase();
                
                const statusStr = (status || '').toLowerCase().trim();
                
                if (statusStr === 'aktif' || statusStr === 'running') {
                    statusColor = '#28a745';
                    statusLabel = 'RUNNING';
                } else if (statusStr === 'maintenance' || statusStr === 'breakdown') {
                    statusColor = '#dc3545';
                    statusLabel = 'BREAKDOWN';
                } else if (statusStr === 'offline' || statusStr === 'tidak aktif') {
                    statusColor = '#6c757d';
                    statusLabel = 'OFF';
                } else if (statusStr === 'stop' || statusStr === 'setup' || statusStr === 'setdown' || statusStr === 'standby') {
                    statusColor = '#f1c40f';
                    statusLabel = 'STAND BY';
                }

                if (stopReason) {
                    statusLabel += ` (${stopReason})`;
                }

                let historyData = [];
                try {
                    const rawHistory = card.getAttribute('data-mesin-history');
                    if (rawHistory) {
                        historyData = JSON.parse(rawHistory);
                    }
                } catch (err) {
                    console.error("Error parsing history data:", err);
                    historyData = [];
                }

                let summaryHtml = '';
                
                if (historyData && Array.isArray(historyData) && historyData.length > 0) {
                    let historyLines = historyData.map(event => {
                        let eventColor = '#ccc';
                        let typeLabel = (event.type || 'SYSTEM').toUpperCase();
                        
                        const eventType = (event.type || '').toLowerCase().trim();
                        
                        if (eventType === 'aktif' || eventType === 'running') {
                            eventColor = '#28a745';
                            typeLabel = 'RUNNING';
                        } else if (eventType === 'maintenance' || eventType === 'breakdown') {
                            eventColor = '#dc3545';
                            typeLabel = 'BREAKDOWN';
                        } else if (eventType === 'offline' || eventType === 'tidak aktif') {
                            eventColor = '#6c757d';
                            typeLabel = 'OFF';
                        } else {
                            eventColor = '#f1c40f';
                            typeLabel = 'STAND BY';
                        }

                        // Generate Duration Label
                        let durationLabel = '0s';
                        if (event.duration_sec !== undefined) {
                            let sec = parseInt(event.duration_sec);
                            if (sec < 60) {
                                durationLabel = `${sec}s`;
                            } else if (sec < 3600) {
                                durationLabel = `${Math.floor(sec / 60)}m`;
                            } else {
                                let h = Math.floor(sec / 3600);
                                let m = Math.floor((sec % 3600) / 60);
                                durationLabel = m > 0 ? `${h}h ${m}m` : `${h}h`;
                            }
                        }

                        const reasonText = event.reason && event.reason.trim() !== '' ? event.reason : (typeLabel === 'RUNNING' ? 'Mesin Berjalan' : 'Tidak ada keterangan');
                        
                        return `
                            <div style="margin-bottom: 8px; line-height: 1.3; border-left: 3px solid ${eventColor}; padding-left: 10px;">
                                <div style="font-size: 0.8rem; color: #fff; font-weight: 500;">
                                    <span style="font-family: 'Courier New', monospace; color: #aaa; background: rgba(255,255,255,0.05); padding: 1px 4px; border-radius: 3px; font-weight: normal;">
                                        ${event.start || '??:??'} - ${event.end === 'Sekarang' ? '<span class="live-clock">--:--:--</span>' : (event.end || '??:??')}
                                    </span> 
                                    <span style="color: ${eventColor}; margin-left: 6px; text-transform: uppercase;">${typeLabel}</span>
                                </div>
                                <div style="font-size: 0.7rem; color: #bbb; margin-top: 3px; display: flex; align-items: flex-start;">
                                    <span style="color: ${eventColor}; margin-right: 5px;">&bull;</span>
                                    <span>${reasonText} <span style="color: #e67e22; font-weight: bold; margin-left: 4px;">(${durationLabel})</span></span>
                                </div>
                            </div>
                        `;
                    }).join('');

                    summaryHtml = `
                        <div class="tooltip-divider" style="margin: 8px 0; border-top: 1px solid rgba(255,255,255,0.15);"></div>
                        <div class="tooltip-history-title" style="font-size: 0.7rem; color: #aaa; font-weight: bold; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">SUMMARY MESIN HARI INI</div>
                        <div class="tooltip-summary-list" style="padding-right: 5px;">
                            ${historyLines}
                        </div>
                    `;
                }

                const statusSince = card.getAttribute('data-status-since');
                
                let durationStr = '00:00:00';
                if (statusSince) {
                    const start = new Date(statusSince);
                    if (!isNaN(start.getTime())) {
                        const now = new Date();
                        const diff = Math.max(0, now - start);
                        const seconds = Math.floor((diff / 1000) % 60);
                        const minutes = Math.floor((diff / (1000 * 60)) % 60);
                        const hours = Math.floor(diff / (1000 * 60 * 60));
                        durationStr = String(hours).padStart(2, '0') + ':' + 
                                      String(minutes).padStart(2, '0') + ':' + 
                                      String(seconds).padStart(2, '0');
                    }
                }

                tooltip.innerHTML = `
                    <div class="tooltip-close">&times;</div>
                    <div class="tooltip-header" style="flex-direction: column; align-items: center; text-align: center;">
                        <span style="font-size: 1.1rem; font-weight: bold; width: 100%; color: #fff;">${nama}</span>
                        <div class="tooltip-status-top" style="margin-top: 4px; display: flex; align-items: center; justify-content: center;">
                            <span class="status-dot" style="background-color: ${statusColor}; width: 8px; height: 8px; margin-right: 6px;"></span>
                            <span class="status-text" style="color: ${statusColor}; font-weight: bold; font-size: 0.75rem;">
                                ${statusLabel}
                            </span>
                        </div>
                    </div>
                    <div class="tooltip-body">
                        <div class="tooltip-row" style="margin-bottom: 8px; background: rgba(255,255,255,0.08); padding: 6px 10px; border-radius: 6px; display: flex; align-items: center; justify-content: space-between;">
                            <span class="tooltip-label" style="width: auto; margin-right: 10px; font-size: 0.75rem; color: #aaa;">DURASI:</span>
                            <span style="font-family: 'Courier New', monospace; font-weight: bold; color: #fff; font-size: 1.2rem; letter-spacing: 1px;">${durationStr}</span>
                        </div>
                        <div class="tooltip-row" style="margin-bottom: 4px;">
                            <span class="tooltip-label" style="font-size: 0.7rem; color: #888; width: 60px;">Brand:</span>
                            <strong style="font-size: 0.75rem; color: #eee;">${brand}</strong>
                        </div>
                        <div class="tooltip-row" style="margin-bottom: 4px;">
                            <span class="tooltip-label" style="font-size: 0.7rem; color: #888; width: 60px;">Tonase:</span>
                            <strong style="font-size: 0.75rem; color: #eee;">${tonase} Ton</strong>
                        </div>
                        ${summaryHtml}
                    </div>
                `;
                tooltip.style.display = 'block';
            });

            card.addEventListener('mousemove', (e) => {
                let top = e.clientY + 15;
                let left = e.clientX + 15;
                if (left + tooltip.offsetWidth > window.innerWidth) left = e.clientX - tooltip.offsetWidth - 10;
                if (top + tooltip.offsetHeight > window.innerHeight) top = e.clientY - tooltip.offsetHeight - 10;
                tooltip.style.top = top + 'px';
                tooltip.style.left = left + 'px';
            });

            card.addEventListener('mouseleave', () => {
                isPaused = false;
                window.currentHoveredCard = null;
                tooltip.style.display = 'none';
            });
        });
    }

    // --- AJAX Pagination Logic ---
    async function loadPage(page) {
        try {
            const grid = document.getElementById('mesinGrid');
            // Subtle fade effect
            grid.style.transition = 'opacity 0.3s ease';
            grid.style.opacity = '0.4'; 

            // Fetch new content
            const response = await fetch(`${window.location.pathname}?page=${page}`);
            const text = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(text, 'text/html');

            // Extract new content
            const newGridContent = doc.getElementById('mesinGrid').innerHTML;
            const newPaginationInfo = doc.getElementById('paginationInfo') ? doc.getElementById('paginationInfo').innerHTML : '';

            // Wait a tiny bit for the fade out to be noticeable (optional, but smoother)
            setTimeout(() => {
                grid.innerHTML = newGridContent;
                grid.style.opacity = '1';
                
                if (newPaginationInfo) {
                    const pageInfoEl = document.getElementById('paginationInfo');
                    if(pageInfoEl) pageInfoEl.innerHTML = newPaginationInfo;
                }

                initTooltips(); // Re-bind hover events to new elements
                currentPage = page;
            }, 300);

        } catch (error) {
            console.error('Error loading page:', error);
            // Fallback to reload if fetch fails
            window.location.reload();
        }
    }

    // --- Click Handler for History Redirect (REMOVED) ---
    // User requested to disable navigation when clicking machine cards.
    console.log("Dashboard Inject Pagination: Machine card click redirection is DISABLED.");

    // --- Initialization ---
    initTooltips();

    function updateTooltipTimer() {
        if (tooltip.style.display === 'block' && window.currentHoveredCard) {
            const statusSince = window.currentHoveredCard.getAttribute('data-status-since');
            // Look for the specific duration counter span more reliably
            const timerEl = tooltip.querySelector('.tooltip-body .tooltip-row:first-child span:last-child');
            
            if (statusSince && timerEl) {
                const start = new Date(statusSince);
                if (isNaN(start.getTime())) return;

                const now = new Date();
                
                // Logic: Start counting from today 00:00:00 if status started before today
                const todayMidnight = new Date();
                todayMidnight.setHours(0, 0, 0, 0);

                let effectiveStart = start;
                if (start < todayMidnight) {
                    effectiveStart = todayMidnight;
                }

                const diff = Math.max(0, now - effectiveStart);
                const seconds = Math.floor((diff / 1000) % 60);
                const minutes = Math.floor((diff / (1000 * 60)) % 60);
                const hours = Math.floor(diff / (1000 * 60 * 60));
                
                const timeStr = String(hours).padStart(2, '0') + ':' + 
                                String(minutes).padStart(2, '0') + ':' + 
                                String(seconds).padStart(2, '0');
                
                timerEl.textContent = timeStr;
            }

            // Update live clocks in tooltip history
            const liveClocks = tooltip.querySelectorAll('.live-clock');
            if (liveClocks.length > 0) {
                const now = new Date();
                const clockStr = String(now.getHours()).padStart(2, '0') + ':' + 
                                 String(now.getMinutes()).padStart(2, '0') + ':' + 
                                 String(now.getSeconds()).padStart(2, '0');
                liveClocks.forEach(el => el.textContent = clockStr);
            }
        }
    }

    if (lastPage > 1 || true) { // Always run timer for tooltip even if only 1 page
        setInterval(() => {
            // Logic for auto-pagination
            if (lastPage > 1 && !isPaused) {
                // This will be handled by a separate counter or check
                // For simplicity, we just keep the tooltip update here
            }
            updateTooltipTimer();
        }, 1000);
    }

    // Separate timer for pagination to keep interval logic clean
    if (lastPage > 1) {
        console.log('Auto-pagination enabled. Total pages:', lastPage);
        autoPageTimer = setInterval(() => {
            if (!isPaused) {
                let nextPage = currentPage + 1;
                if (nextPage > lastPage) nextPage = 1;
                console.log('Navigating to page:', nextPage);
                loadPage(nextPage);
            }
        }, intervalTime);
    }
});
