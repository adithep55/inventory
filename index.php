<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>ระบบจัดการสินค้าคงคลัง - Dashboard</title>

    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.jpg">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<style>
    #inventory_chart {
        max-width: 100%;
        margin: 35px auto;
    }

    .modal-header.bg-warning {
        color: #333;
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

<body>
    <?php require_once 'includes/header.php'; ?>
    <?php require_once 'includes/sidebar.php'; ?>

    <div class="page-wrapper">
        <div class="content container-fluid">
    
<?php require_once 'includes/notification.php'; ?>

                <div class="row">
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="dash-widget">
                            <div class="dash-widgetimg">
                                <span><i class="fas fa-cubes fa-2x"></i></span>
                            </div>
                            <div class="dash-widgetcontent">
                                <h5><span class="counters" id="total-products"></span></h5>
                                <h6>จำนวนสินค้าทั้งหมด</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="dash-widget dash1">
                            <div class="dash-widgetimg">
                                <span><i class="fas fa-warehouse fa-2x"></i></span>
                            </div>
                            <div class="dash-widgetcontent">
                                <h5><span class="counters" id="total-inventory"></span></h5>
                                <h6>จำนวนสินค้าในคลัง</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="dash-widget dash2">
                            <div class="dash-widgetimg">
                                <span><i class="fas fa-shopping-cart fa-2x"></i></span>
                            </div>
                            <div class="dash-widgetcontent">
                                <h5><span class="counters" id="total-issues"></span></h5>
                                <h6>จำนวนการเบิกสินค้า</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="dash-widget dash3">
                            <div class="dash-widgetimg">
                                <span><i class="fas fa-truck-loading fa-2x"></i></span>
                            </div>
                            <div class="dash-widgetcontent">
                                <h5><span class="counters" id="total-receives"></span></h5>
                                <h6>จำนวนการรับสินค้า</h6>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-7 col-sm-12 col-12 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0" id="chart-title">สถิติการเบิกและรับสินค้า</h5>
                            </div>
                            <div class="card-body">
                                <div id="inventory_chart"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5 col-sm-12 col-12 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0">สินค้าที่มีการเคลื่อนไหวล่าสุด</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="recent-products-table">
                                        <thead>
                                            <tr>
                                                <th>รหัสสินค้า</th>
                                                <th>ชื่อสินค้า</th>
                                                <th>จำนวนคงเหลือ</th>
                                                <th>สถานะ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-0">
                    <div class="card-body">
                        <h4 class="card-title">รายการเบิก-รับล่าสุด</h4>
                        <div class="table-responsive dataview">
                            <table class="table datatable" id="recent-transactions-table">
                                <thead>
                                    <tr>
                                        <th>เลขที่เอกสาร</th>
                                        <th>ประเภท</th>
                                        <th>วันที่</th>
                                        <th>สินค้า</th>
                                        <th>จำนวน</th>
                                        <th>ผู้ดำเนินการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="lowStockModal" tabindex="-1" role="dialog" aria-labelledby="lowStockModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="lowStockModalLabel">สินค้าใกล้หมด</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-2">
                    <!-- สำหรับหน้าจอขนาดใหญ่ -->
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
                    <!-- สำหรับมือถือ -->
                    <div class="list-group d-sm-none" id="low-stock-list">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/feather.min.js"></script>
    <script src="assets/js/jquery.slimscroll.min.js"></script>
    <script src="assets/js/jquery.dataTables.min.js"></script>
    <script src="assets/js/dataTables.bootstrap4.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/plugins/apexchart/apexcharts.min.js"></script>
    <script src="assets/js/script.js"></script>

    <script>
$(document).ready(function () {
    loadDashboardData();
});

function loadDashboardData() {
    $.ajax({
        url: 'api/dashboard.php',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                updateDashboard(response.data);
            } else {
                console.error('Error:', response.error);
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', status, error);
        }
    });
}

function updateDashboard(data) {
    $('#total-products').text(data.total_products);
    $('#total-inventory').text(data.total_inventory);
    $('#total-issues').text(data.total_issues);
    $('#total-receives').text(data.total_receives);

    createInventoryChart(data.inventory_stats);
    updateRecentProductsTable(data.recent_products);
    updateRecentTransactionsTable(data.recent_transactions);

    $('.card.bg-danger')[data.low_stock_count > 0 ? 'show' : 'hide']();
}

function createInventoryChart(stats) {
    var currentYear = new Date().getFullYear();
    var options = {
        series: [{
            name: 'เบิก',
            type: 'column',
            data: stats.map(item => item.issue_count)
        }, {
            name: 'รับ',
            type: 'column',
            data: stats.map(item => item.receive_count)
        }],
        chart: {
            height: 350,
            type: 'line',
            stacked: false
        },
        dataLabels: { enabled: false },
        stroke: { width: [1, 1] },
        title: {
            text: `สถิติการเบิกและรับสินค้าปี ${currentYear}`,
            align: 'left'
        },
        xaxis: { categories: stats.map(item => item.month) },
        yaxis: [{
            axisTicks: { show: true },
            axisBorder: { show: true, color: '#008FFB' },
            labels: { style: { colors: '#008FFB' } },
            title: {
                text: "จำนวนรายการ (เบิก/รับ)",
                style: { color: '#008FFB' }
            },
            tooltip: { enabled: true }
        }],
        tooltip: {
            fixed: {
                enabled: true,
                position: 'topLeft',
                offsetY: 30,
                offsetX: 60
            },
        },
        legend: { horizontalAlign: 'left', offsetX: 40 }
    };

    new ApexCharts(document.querySelector("#inventory_chart"), options).render();
}

function updateRecentProductsTable(products) {
    var table = $('#recent-products-table tbody');
    table.empty();
    products.forEach(function (product) {
        table.append(`
            <tr>
                <td>${product.product_id}</td>
                <td>${product.name_th}</td>
                <td>${product.quantity}</td>
                <td>${product.status}</td>
            </tr>
        `);
    });
}

function updateRecentTransactionsTable(transactions) {
    var table = $('#recent-transactions-table tbody');
    table.empty();
    transactions.forEach(function (transaction) {
        table.append(`
            <tr>
                <td>${transaction.bill_number}</td>
                <td>${transaction.type}</td>
                <td>${transaction.date}</td>
                <td>${transaction.product_id}</td>
                <td>${transaction.quantity}</td>
                <td>${transaction.user}</td>
            </tr>
        `);
    });
}


    </script>
</body>
</html>