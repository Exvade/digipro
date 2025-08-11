// OEE Gauge Chart
const ctx = document.getElementById('oeeGauge').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        datasets: [{
            data: [50, 50], // OEE, sisa
            backgroundColor: ['#1976d2', '#222'], // biru, abu
            borderWidth: 0
        }]
    },
    options: {
        cutout: '80%',
        plugins: { legend: { display: false } }
    }
});
// Downtime Bar Chart
const ctx2 = document.getElementById('downtimeBar').getContext('2d');
new Chart(ctx2, {
    type: 'bar',
    data: {
        labels: ['Uncommented', 'Break', 'Material Shortage'],
        datasets: [{
            data: [14, 1, 8],
            backgroundColor: ['#1976d2', '#90caf9', '#b0bec5'] // biru, biru muda, abu
        }]
    },
    options: {
        indexAxis: 'y',
        plugins: { legend: { display: false } }
    }
});
// Tambah chart lain di bawah ini
// OEE Bar Chart Bulanan
const ctx3 = document.getElementById('oeeBarMonth').getContext('2d');
new Chart(ctx3, {
    type: 'bar',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        datasets: [{
            label: 'OEE',
            data: [60, 62, 65, 70, 68, 72, 75, 73, 70, 68, 65, 63],
            backgroundColor: '#1976d2'
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, max: 100 }
        }
    }
});
// OEE Line Chart Harian
const ctx4 = document.getElementById('oeeLineDay').getContext('2d');
new Chart(ctx4, {
    type: 'line',
    data: {
        labels: Array.from({length: 30}, (_, i) => i+1),
        datasets: [{
            label: 'OEE',
            data: [60, 62, 65, 70, 68, 72, 75, 73, 70, 68, 65, 63, 62, 64, 66, 68, 70, 72, 74, 76, 75, 74, 73, 72, 71, 70, 69, 68, 67, 66],
            borderColor: '#1976d2',
            backgroundColor: 'rgba(25, 118, 210, 0.2)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, max: 100 }
        }
    }
});
// jQuery event handler
$(function() {
    // Tambah Data
    $('#tambahBtn').on('click', function() {
        $('#oeeForm')[0].reset();
        $('#oee_id').val('');
        $('#oeeModalLabel').text('Tambah Data OEE');
        $('#oeeModal').modal('show');
    });
    // Edit Data
    $('.editBtn').on('click', function() {
        let data = $(this).data();
        $('#oee_id').val(data.id);
        $('#nama_mesin').val(data.nama_mesin);
        $('#availability').val(data.availability);
        $('#performance').val(data.performance);
        $('#quality').val(data.quality);
        $('#down_time').val(data.down_time);
        $('#running_time').val(data.running_time);
        $('#stop_count').val(data.stop_count);
        $('#oeeModalLabel').text('Edit Data OEE');
        $('#oeeModal').modal('show');
    });
    // Submit Form
    $('#oeeForm').on('submit', function(e) {
        e.preventDefault();
        let id = $('#oee_id').val();
        let url = id ? '/oee/' + id : '/oee';
        let method = id ? 'PUT' : 'POST';
        $.ajax({
            url: url,
            type: method,
            data: $(this).serialize(),
            success: function(res) {
                if(res.success) {
                    location.href = $(location).attr('pathname');
                }
            }
        });
    });
}); 