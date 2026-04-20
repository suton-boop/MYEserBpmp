@extends('layouts.app')

@section('title', 'Dashboard Overview')

@section('content')
<div class="container-fluid py-4">
    <!-- Welcome Banner / Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-soft border-0" style="background: linear-gradient(135deg, #0d6efd 0%, #00d2ff 100%); color: white; border-radius: 20px;">
                <div class="card-body p-5 position-relative overflow-hidden">
                    <div style="position: relative; z-index: 2;">
                        <h2 class="fw-bold mb-2">Selamat Datang, {{ auth()->user()->name }} 👋</h2>
                        <p class="mb-4" style="font-size: 1.1rem; opacity: 0.9;">Pantau aktivitas sertifikat, status penandatanganan, dan kelola event BPMP Prov Kaltim secara real-time.</p>
                        <a href="{{ route('admin.certificates.index') }}" class="btn btn-light rounded-pill px-4 py-2 fw-semibold shadow-sm text-primary">Lihat Sertifikat</a>
                        <a href="{{ route('admin.system.documentation.pdf') }}" class="btn btn-outline-light rounded-pill px-4 py-2 fw-semibold shadow-sm ms-2">
                            <i class="fa-solid fa-file-pdf me-1"></i> Dokumentasi PDF
                        </a>
                    </div>
                    <!-- Decorative Element -->
                    <i class="fa-solid fa-award position-absolute" style="font-size: 15rem; right: -20px; bottom: -40px; opacity: 0.2; transform: rotate(-15deg);"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Metrics row -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card card-soft h-100 py-3" style="border-radius: 18px; border-left: 5px solid #0d6efd;">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-calendar-days fs-4"></i>
                        </div>
                        <h6 class="text-muted fw-semibold mb-0 text-uppercase" style="letter-spacing: 1px;">Total Event</h6>
                    </div>
                    <h2 class="fw-bold mb-0 display-6">{{ number_format($totalEvents) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-soft h-100 py-3" style="border-radius: 18px; border-left: 5px solid #198754;">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-users fs-4"></i>
                        </div>
                        <h6 class="text-muted fw-semibold mb-0 text-uppercase" style="letter-spacing: 1px;">Total Peserta</h6>
                    </div>
                    <h2 class="fw-bold mb-0 display-6">{{ number_format($totalParticipants) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-soft h-100 py-3" style="border-radius: 18px; border-left: 5px solid #ffca2c;">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-stamp fs-4"></i>
                        </div>
                        <h6 class="text-muted fw-semibold mb-0 text-uppercase" style="letter-spacing: 1px;">Total Sertifikat</h6>
                    </div>
                    <h2 class="fw-bold mb-0 display-6">{{ number_format($totalCertificates) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Bar Chart -->
        <div class="col-lg-8">
            <div class="card card-soft h-100">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-chart-column text-primary me-2"></i>Sertifikat per Event</h5>
                </div>
                <div class="card-body px-4">
                    <div id="eventChart" style="min-height: 300px;"></div>
                </div>
            </div>
        </div>
        <!-- Donut Chart -->
        <div class="col-lg-4">
            <div class="card card-soft h-100">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-chart-pie text-secondary me-2"></i>Status Sertifikat</h5>
                </div>
                <div class="card-body px-4 d-flex align-items-center justify-content-center">
                    <div id="statusChart" style="width: 100%; min-height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ApexCharts Setup -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // --- DONUT CHART (STATUS SERTIFIKAT) ---
        var statusOptions = {
            series: [{{ $approved }}, {{ $signed }}, {{ $pending }}, {{ $rejected }}],
            chart: {
                type: 'donut',
                height: 350,
                fontFamily: 'inherit'
            },
            labels: ['Approved', 'Signed (TTE)', 'Pending/Draft', 'Rejected'],
            colors: ['#0d6efd', '#20c997', '#ffc107', '#dc3545'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            name: {
                                show: true,
                                fontSize: '14px',
                                color: '#6c757d',
                                offsetY: -10
                            },
                            value: {
                                show: true,
                                fontSize: '28px',
                                fontWeight: 700,
                                color: '#212529',
                                offsetY: 10,
                            },
                            total: {
                                show: true,
                                showAlways: true,
                                label: 'Total Status',
                                fontSize: '14px',
                                color: '#6c757d',
                                formatter: function (w) {
                                  return w.globals.seriesTotals.reduce((a, b) => {
                                    return a + b
                                  }, 0)
                                }
                            }
                        }
                    }
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                width: 0
            },
            legend: {
                position: 'bottom',
                markers: {
                    radius: 12
                }
            },
            tooltip: {
                theme: 'light'
            }
        };

        var statusChart = new ApexCharts(document.querySelector("#statusChart"), statusOptions);
        statusChart.render();


        // --- BAR CHART (SERTIFIKAT PER EVENT) ---
        var eventNames = {!! json_encode($certPerEvent->pluck('event.name')->map(function($name) { return \Illuminate\Support\Str::limit($name, 15); })->toArray()) !!};
        var eventCounts = {!! json_encode($certPerEvent->pluck('total')->toArray()) !!};
        
        // Handle empty placeholder if no data
        if(eventNames.length === 0) {
            eventNames = ['Kosong'];
            eventCounts = [0];
        }

        var eventOptions = {
            series: [{
                name: 'Jumlah Sertifikat',
                data: eventCounts
            }],
            chart: {
                type: 'bar',
                height: 350,
                toolbar: { show: false },
                fontFamily: 'inherit'
            },
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    columnWidth: '40%',
                    distributed: true,
                }
            },
            colors: ['#0d6efd', '#6f42c1', '#198754', '#fd7e14', '#20c997', '#0dcaf0'],
            dataLabels: {
                enabled: true,
                style: {
                    fontSize: '14px',
                    colors: ['#fff']
                },
                offsetY: -20
            },
            stroke: {
                show: false,
            },
            xaxis: {
                categories: eventNames,
                labels: {
                    style: {
                        fontSize: '12px',
                        colors: '#6c757d',
                        fontWeight: 500,
                    },
                    trim: true
                },
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                }
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '13px',
                        colors: '#6c757d'
                    }
                }
            },
            grid: {
                borderColor: '#e9ecef',
                strokeDashArray: 4,
                xaxis: { lines: { show: false } },   
                yaxis: { lines: { show: true } }
            },
            legend: {
                show: false
            },
            tooltip: {
                theme: 'light',
                y: {
                    formatter: function (val) {
                        return val + " Sertifikat"
                    }
                }
            }
        };

        var eventChart = new ApexCharts(document.querySelector("#eventChart"), eventOptions);
        eventChart.render();
    });
</script>
@endsection
