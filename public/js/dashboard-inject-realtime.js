/**
 * Dashboard Inject - Realtime Update
 * Auto-refresh production data every 1 second
 */

(function() {
    'use strict';
    
    // Configuration
    const REFRESH_INTERVAL = 10000; // 10 seconds for realtime updates
    const API_URL = '/dashboard/inject/realtime-production';
    let refreshTimer = null;
    
    /**
     * Update machine card with new production data - OEE Style
     */
    function updateMachineCard(mesinId, data) {
        const $card = $(`.machine-card[data-mesin-id="${mesinId}"], .machine-card-simple[data-mesin-id="${mesinId}"]`);
        if (!$card.length) {
            console.log(`Card not found for mesin ID: ${mesinId}`);
            return;
        }

        // Update summary data for tooltips
        if (data.summary) {
            $card.attr('data-mesin-summary', JSON.stringify(data.summary));
        }

        // Update Status Attributes (Important for Tooltip)
        if (data.status) {
            $card.attr('data-mesin-status', data.status);
            $card.attr('data-stop-reason', data.stop_reason || '');
            if (data.status_since) {
                $card.attr('data-status-since', data.status_since);
            }
        }

        // Update history data for tooltips
        if (data.history) {
            // Ensure data is stringified if it's an object/array
            const historyStr = typeof data.history === 'string' ? data.history : JSON.stringify(data.history);
            $card.attr('data-mesin-history', historyStr);
        }
        
        // Tidak menambahkan efek animasi/getar pada kartu saat update
        
        // Update Part Name
        if (data.part_name) {
            const $partName = $card.find('.oee-part-name');
            if ($partName.length) {
                $partName.text(data.part_name);
                $partName.attr('title', data.part_name);
            }
        }

        // Update Customer Name
        if (data.customer) {
            const $customerName = $card.find('.customer-name');
            if ($customerName.length) {
                // Keep the icon
                $customerName.html('<i class="fas fa-user-tag mr-1"></i>' + data.customer);
                $customerName.attr('title', data.customer);
            }
        }


        
        // Update Target
        const $targetBox = $card.find('.oee-target-value');
        if ($targetBox.length) {
            $targetBox.text(formatNumber(data.target_hari_ini));
        }
        
        // Update Actual
        const $actualBox = $card.find('.oee-actual-value');
        if ($actualBox.length) {
            $actualBox.text(formatNumber(data.actual_hari_ini));
        }
        
        // Update Achievement Percentage Text
        const $achievementText = $card.find('.oee-progress-text');
        if ($achievementText.length) {
            $achievementText.text(data.progress_percentage + '%');
        }
        
        // Update Progress Bar
        const $progressBar = $card.find('.oee-progress-bar');
        if ($progressBar.length) {
            $progressBar.css('width', data.progress_percentage + '%');
            $progressBar.attr('aria-valuenow', data.progress_percentage);
            
            // Update progress bar color based on achievement
            $progressBar.removeClass('bg-success bg-warning bg-danger');
            if (data.progress_percentage >= 100) {
                $progressBar.addClass('bg-success');
            } else if (data.progress_percentage >= 80) {
                $progressBar.addClass('bg-warning');
            } else {
                $progressBar.addClass('bg-danger');
            }
        }
        
        // Update Shift Performance (if elements exist, though currently hidden in design)
        const shifts = ['pagi', 'siang', 'malam'];
        
        shifts.forEach(shiftName => {
            const $shiftBox = $card.find(`.shift-box[data-shift="${shiftName}"]`);
            if ($shiftBox.length) {
                // Update target
                const $target = $shiftBox.find('.shift-target');
                if ($target.length) {
                    $target.text(formatNumber(data[`target_shift_${shiftName}`]));
                }
                
                // Update actual
                const $actual = $shiftBox.find('.shift-actual');
                if ($actual.length) {
                    $actual.text(formatNumber(data[`actual_shift_${shiftName}`]));
                }
                
                // Highlight current shift
                if (data.current_shift === shiftName) {
                    $shiftBox.addClass('active-shift');
                } else {
                    $shiftBox.removeClass('active-shift');
                }
            }
        });
        
        // Update keterangan if no planning
        if (!data.has_planning && data.keterangan) {
            const $keteranganText = $card.find('.oee-keterangan-text');
            if ($keteranganText.length) {
                $keteranganText.text(data.keterangan);
            }
        }
        
        console.log(`Updated mesin ${mesinId}: Target=${data.target_hari_ini}, Actual=${data.actual_hari_ini}, Progress=${data.progress_percentage}%`);
    }
    
    /**
     * Format number with thousand separator
     */
    function formatNumber(num) {
        if (num === null || num === undefined || num === '') return '0';
        // Remove any existing formatting first
        const cleanNum = num.toString().replace(/,/g, '');
        // Add thousand separators
        return cleanNum.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
    
    /**
     * Fetch and update production data
     */
    function fetchProductionData() {
        $.ajax({
            url: API_URL,
            method: 'GET',
            dataType: 'json',
            cache: false, // Prevent caching
            success: function(response) {
                if (response.success && response.data) {
                    const machineData = response.data.machines;
                    const aggregates = response.data.aggregates;

                    // Update each machine card
                    if (machineData) {
                        Object.keys(machineData).forEach(mesinId => {
                            updateMachineCard(mesinId, machineData[mesinId]);
                        });
                    }

                    // Update aggregate totals in info boxes
                    if (aggregates) {
                        $('#agg-running-jam').text(aggregates.running);
                        $('#agg-standby-jam').text(aggregates.standby);
                        $('#agg-downtime-jam').text(aggregates.downtime);
                        $('#agg-offline-jam').text(aggregates.offline);
                    }
                    
                    // Update last update time (quick-stats card)
                    const now = new Date();
                    const timeString = now.toLocaleTimeString('id-ID', { 
                        hour: '2-digit', 
                        minute: '2-digit',
                        second: '2-digit'
                    });
                    
                    // Hanya update span last-update-time agar tidak mengganggu elemen lain
                    $('#last-update-time').text(timeString);
                    
                    // Log for debugging
                    console.log(`[${timeString}] Production data updated successfully`);
                } else {
                    console.warn('Invalid response format:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to fetch production data:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
            }
        });
    }
    
    /**
     * Start auto-refresh
     */
    function startAutoRefresh() {
        // Clear existing timer if any
        if (refreshTimer) {
            clearInterval(refreshTimer);
        }
        
        // Fetch immediately
        fetchProductionData();
        
        // Set up interval
        refreshTimer = setInterval(fetchProductionData, REFRESH_INTERVAL);
        
        console.log(`🚀 Auto-refresh started (interval: ${REFRESH_INTERVAL}ms = ${REFRESH_INTERVAL/1000}s)`);
    }
    
    /**
     * Stop auto-refresh
     */
    function stopAutoRefresh() {
        if (refreshTimer) {
            clearInterval(refreshTimer);
            refreshTimer = null;
            console.log('⏸️ Auto-refresh stopped');
        }
    }
    
    /**
     * Initialize on page load
     */
    $(document).ready(function() {
        // Only start if we're on the dashboard inject page
        if ($('.machine-card, .machine-card-simple').length > 0) {
            console.log(`Found ${$('.machine-card').length} machine cards`);
            startAutoRefresh();
            
            // Stop refresh when page is hidden (to save resources)
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    stopAutoRefresh();
                } else {
                    startAutoRefresh();
                }
            });
        } else {
            console.log('No machine cards found, auto-refresh not started');
        }
    });
    
    // Expose functions globally if needed
    window.dashboardInjectRealtime = {
        start: startAutoRefresh,
        stop: stopAutoRefresh,
        refresh: fetchProductionData
    };
    
})();
