<?php
// เพิ่มบรรทัดนี้ที่ด้านบนสุดของไฟล์ index.php
require_once 'config/permission.php';
?>
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
    console.log('Updating dashboard with data:', data);

    $('#total-products').text(data.total_products || 0);
    $('#total-inventory').text(data.total_inventory || 0);
    $('#total-issues').text(data.total_issues || 0);
    $('#total-receives').text(data.total_receives || 0);

    if (data.inventory_stats && Array.isArray(data.inventory_stats) && data.inventory_stats.length > 0) {
        createInventoryChart(data.inventory_stats);
    } else {
        console.error('Invalid or missing inventory_stats data');
        // อาจจะแสดงข้อความแจ้งเตือนให้ผู้ใช้ทราบว่าไม่สามารถแสดงกราฟได้
    }

    updateRecentProductsTable(data.recent_products);
    updateRecentTransactionsTable(data.recent_transactions);

    $('.card.bg-danger')[data.low_stock_count > 0 ? 'show' : 'hide']();
}

function createInventoryChart(stats) {
    console.log('Inventory stats:', stats);
    if (!stats || !Array.isArray(stats) || stats.length === 0) {
        console.error('Invalid or empty stats data');
        return;
    }

    var currentYear = new Date().getFullYear();
    var thaiMonths = [
        'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
        'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
    ];
    
    var categories = [];
    var issueData = [];
    var receiveData = [];
    var startYear = parseInt(stats[0].month.split('-')[0]);
    var endYear = parseInt(stats[stats.length - 1].month.split('-')[0]);

    stats.forEach(item => {
        const [year, month] = item.month.split('-');
        const monthIndex = parseInt(month) - 1;
        const yearThai = parseInt(year) + 543;
        categories.push(`${thaiMonths[monthIndex]} ${yearThai}`);
        issueData.push(parseInt(item.issue_quantity) || 0);
        receiveData.push(parseInt(item.receive_quantity) || 0);
    });

    var titleText = startYear === endYear 
        ? `สถิติจำนวนสินค้าที่เบิกและรับปี ${startYear + 543}`
        : `สถิติจำนวนสินค้าที่เบิกและรับตั้งแต่ ${thaiMonths[parseInt(stats[0].month.split('-')[1]) - 1]} ${startYear + 543} ถึง ${thaiMonths[parseInt(stats[stats.length - 1].month.split('-')[1]) - 1]} ${endYear + 543}`;

    var options = {
        series: [{
            name: 'เบิก',
            data: issueData
        }, {
            name: 'รับ',
            data: receiveData
        }],
        chart: {
            type: 'bar',
            height: 350,
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                endingShape: 'rounded'
            },
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        title: {
            text: titleText,
            align: 'left'
        },
        xaxis: {
            categories: categories,
            title: {
                text: 'เดือน'
            }
        },
        yaxis: {
            title: {
                text: 'จำนวนสินค้า'
            },
            min: 0,
            forceNiceScale: true,
            labels: {
                formatter: function (value) {
                    return Math.round(value);
                }
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val + " ชิ้น"
                }
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'left'
        },
        colors: ['#008FFB', '#00E396']
    };

    console.log('Chart options:', options);

    try {
        new ApexCharts(document.querySelector("#inventory_chart"), options).render();
    } catch (error) {
        console.error('Error rendering chart:', error);
    }
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