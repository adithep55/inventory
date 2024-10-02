<div class="row">
    <div class="col-lg-12 col-sm-12 col-12 mb-4">
        <div class="card bg-danger text-white" id="low-stock-notification" style="display: none;">
            <div class="card-body">
                <button id="close-notification" class="btn-close" aria-label="Close"></button>
                <h4 class="card-title mb-0">
                    <i class="fas fa-exclamation-triangle"></i> แจ้งเตือน: สินค้าใกล้หมด
                </h4>
                <p class="mt-2 mb-0">มีสินค้า <span id="low-stock-count" class="font-weight-bold">0</span> รายการที่ใกล้หมด</p>
                <button id="show-low-stock-details" class="btn btn-light mt-3">ดูรายละเอียด</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal สำหรับแสดงรายละเอียดสินค้าใกล้หมด -->
<div class="modal fade" id="lowStockModal" tabindex="-1" role="dialog" aria-labelledby="lowStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="lowStockModalLabel">สินค้าใกล้หมด</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-2">
                <div class="table-responsive d-none d-sm-block">
                    <table class="table table-striped" id="low-stock-table">
                        <thead>
                            <tr>
                                <th>รหัสสินค้า</th>
                                <th>ชื่อสินค้า</th>
                                <th>จำนวนคงเหลือ</th>
                                <th>ระดับต่ำสุด</th>
                                <th>สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="list-group d-sm-none" id="low-stock-list">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

    <script>
$(document).ready(function () {
    loadNotifications();
    setInterval(loadNotifications, 500); 

    $('#show-low-stock-details').click(function () {
        $('#lowStockModal').modal('show');
    });

    $('.close, .btn-secondary').click(function () {
        $('#lowStockModal').modal('hide');
    });

    $('#close-notification').click(function() {
        dismissNotification();
    });

    $(window).on('resize', function () {
        if ($('#lowStockModal').hasClass('show')) {
            updateLowStockModal(window.lowStockProducts);
        }
    });
});

function loadNotifications() {
    if (isNotificationDismissed()) {
        $('#low-stock-notification').hide();
        return;
    }

    $.ajax({
        url: '../api/dashboard.php',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                updateNotifications(response.data);
            } else {
                console.error('Error:', response.message);
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', status, error);
        }
    });
}

function updateNotifications(data) {
    $('#low-stock-count').text(data.low_stock_count);
    updateLowStockModal(data.low_stock_products);
    window.lowStockProducts = data.low_stock_products;

    if (data.low_stock_count > 0) {
        $('#low-stock-notification').fadeIn();
    } else {
        $('#low-stock-notification').fadeOut();
    }
}

function dismissNotification() {
    localStorage.setItem('notificationDismissedAt', Date.now());
    $('#low-stock-notification').fadeOut();
}

function isNotificationDismissed() {
    const dismissedAt = localStorage.getItem('notificationDismissedAt');
    if (!dismissedAt) return false;
    
    const currentTime = Date.now();
    const dismissDuration = 5 * 60 * 1000; 
    return (currentTime - dismissedAt) < dismissDuration;
}


function updateLowStockModal(products) {
    var table = $('#low-stock-table tbody');
    var list = $('#low-stock-list');
    table.empty();
    list.empty();

    products.forEach(function (product) {
        var isLowStock = parseInt(product.total_quantity) <= parseInt(product.low_level);
        var status = isLowStock ? 'ต่ำกว่าเกณฑ์' : 'ปกติ';
        var statusClass = isLowStock ? 'text-danger' : 'text-success';
        var statusBadge = isLowStock ? '<span class="badge bg-danger">ใกล้หมด</span>' : '<span class="badge bg-success">ปกติ</span>';

        table.append(`
            <tr>
                <td>${product.product_id}</td>
                <td>${product.name_th}</td>
                <td>${product.total_quantity}</td>
                <td>${product.low_level}</td>
                <td>${statusBadge}</td>
            </tr>
        `);

        list.append(`
            <div class="list-group-item m-1">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">${product.name_th}</h5>
                    <small class="text-muted">${product.product_id}</small>
                </div>
                <p class="mb-1">จำนวนคงเหลือ: <strong>${product.total_quantity}</strong> / ระดับต่ำสุด: ${product.low_level}</p>
                <small class="${statusClass}">${status}</small>
            </div>
        `);
    });
}
</script>

<style>
#low-stock-notification {
    transition: all 0.3s ease;
    position: relative;
}

#close-notification {
    position: absolute;
    top: 10px;
    right: 10px;
    opacity: 0.8;
}

#close-notification:hover {
    opacity: 1;
}

#low-stock-table .badge {
    font-size: 0.9em;
    padding: 0.4em 0.6em;
}

@media (max-width: 575.98px) {
    #lowStockModal .modal-dialog {
        margin: 0.5rem;
        max-width: none;
    }

    #lowStockModal .list-group-item h5 {
        font-size: 1rem;
    }

    #lowStockModal .list-group-item p,
    #lowStockModal .list-group-item small {
        font-size: 0.875rem;
    }
}
</style>