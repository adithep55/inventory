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
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    loadNotifications();
    setInterval(loadNotifications, 30000); // ทุก 30 วินาที

    document.getElementById('show-low-stock-details').addEventListener('click', function () {
        var myModal = new bootstrap.Modal(document.getElementById('lowStockModal'));
        myModal.show();
    });

    document.getElementById('close-notification').addEventListener('click', function() {
        dismissNotification();
    });

    window.addEventListener('resize', function () {
        if (document.getElementById('lowStockModal').classList.contains('show')) {
            updateLowStockModal(window.lowStockProducts);
        }
    });
});

function loadNotifications() {
    if (isNotificationDismissed()) {
        document.getElementById('low-stock-notification').style.display = 'none';
        return;
    }

    fetch('../api/dashboard.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                updateNotifications(data.data);
            } else {
                console.error('Error:', data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

function updateNotifications(data) {
    document.getElementById('low-stock-count').textContent = data.low_stock_count;
    updateLowStockModal(data.low_stock_products);
    window.lowStockProducts = data.low_stock_products;

    const notification = document.getElementById('low-stock-notification');
    if (data.low_stock_count > 0) {
        notification.style.display = 'block';
    } else {
        notification.style.display = 'none';
    }
}

function dismissNotification() {
    localStorage.setItem('notificationDismissedAt', Date.now());
    document.getElementById('low-stock-notification').style.display = 'none';
}

function isNotificationDismissed() {
    const dismissedAt = localStorage.getItem('notificationDismissedAt');
    if (!dismissedAt) return false;
    
    const currentTime = Date.now();
    const dismissDuration = 5 * 60 * 1000; // 5 นาที
    return (currentTime - dismissedAt) < dismissDuration;
}

function updateLowStockModal(products) {
    const table = document.querySelector('#low-stock-table tbody');
    const list = document.getElementById('low-stock-list');
    table.innerHTML = '';
    list.innerHTML = '';

    products.forEach(function (product) {
        const isLowStock = parseInt(product.total_quantity) <= parseInt(product.low_level);
        const status = isLowStock ? 'ต่ำกว่าเกณฑ์' : 'ปกติ';
        const statusClass = isLowStock ? 'text-danger' : 'text-success';
        const statusBadge = isLowStock ? '<span class="badge bg-danger">ใกล้หมด</span>' : '<span class="badge bg-success">ปกติ</span>';

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${escapeHtml(product.product_id)}</td>
            <td>${escapeHtml(product.name_th)}</td>
            <td>${escapeHtml(product.total_quantity)}</td>
            <td>${escapeHtml(product.low_level)}</td>
            <td>${statusBadge}</td>
        `;
        table.appendChild(tr);

        const listItem = document.createElement('div');
        listItem.className = 'list-group-item m-1';
        listItem.innerHTML = `
            <div class="d-flex w-100 justify-content-between">
                <h5 class="mb-1">${escapeHtml(product.name_th)}</h5>
                <small class="text-muted">${escapeHtml(product.product_id)}</small>
            </div>
            <p class="mb-1">จำนวนคงเหลือ: <strong>${escapeHtml(product.total_quantity)}</strong> / ระดับต่ำสุด: ${escapeHtml(product.low_level)}</p>
            <small class="${statusClass}">${status}</small>
        `;
        list.appendChild(listItem);
    });
}

function escapeHtml(unsafe) {
    return unsafe
         .replace(/&/g, "&amp;")
         .replace(/</g, "&lt;")
         .replace(/>/g, "&gt;")
         .replace(/"/g, "&quot;")
         .replace(/'/g, "&#039;");
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