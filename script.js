// Toggle Panel Notifikasi
document.getElementById('notifButton').addEventListener('click', function() {
    document.getElementById('notifPanel').classList.toggle('show');
});

// Stock Chart
const stockChartOptions = {
    series: [{
        name: 'Stok',
        data: stockData.map(item => item.data)
    }],
    chart: {
        type: 'bar',
        height: 350,
        background: 'transparent',
        toolbar: {
            show: false
        }
    },
    colors: ['#2962ff'],
    plotOptions: {
        bar: {
            borderRadius: 4,
            horizontal: false,
            columnWidth: '40%'
        }
    },
    dataLabels: {
        enabled: false
    },
    grid: {
        borderColor: '#55596e',
        yaxis: {
            lines: {
                show: true
            }
        },
        xaxis: {
            lines: {
                show: true
            }
        }
    },
    legend: {
        labels: {
            colors: '#f5f7ff'
        },
        show: true,
        position: 'top'
    },
    stroke: {
        colors: ['transparent'],
        show: true,
        width: 2
    },
    tooltip: {
        shared: true,
        intersect: false,
        theme: 'dark'
    },
    xaxis: {
        categories: stockData.map(item => item.name),
        title: {
            style: {
                color: '#f5f7ff'
            }
        },
        axisBorder: {
            show: true,
            color: '#55596e'
        },
        axisTicks: {
            show: true,
            color: '#55596e'
        },
        labels: {
            style: {
                colors: '#f5f7ff'
            }
        }
    },
    yaxis: {
        title: {
            text: 'Jumlah Stok',
            style: {
                color: '#f5f7ff'
            }
        },
        axisBorder: {
            show: true,
            color: '#55596e'
        },
        axisTicks: {
            show: true,
            color: '#55596e'
        },
        labels: {
            style: {
                colors: '#f5f7ff'
            }
        }
    }
};

// Stock Movement Chart
const movementChartOptions = {
    series: [
        {
            name: 'Stok Masuk',
            data: movementData.incoming
        },
        {
            name: 'Stok Keluar',
            data: movementData.outgoing
        }
    ],
    chart: {
        type: 'area',
        height: 350,
        background: 'transparent',
        stacked: false,
        toolbar: {
            show: false
        }
    },
    colors: ['#00ab57', '#d50000'],
    labels: movementData.dates,
    dataLabels: {
        enabled: false
    },
    fill: {
        gradient: {
            opacityFrom: 0.4,
            opacityTo: 0.1,
            shadeIntensity: 1,
            stops: [0, 100],
            type: 'vertical'
        },
        type: 'gradient'
    },
    grid: {
        borderColor: '#55596e',
        yaxis: {
            lines: {
                show: true
            }
        },
        xaxis: {
            lines: {
                show: true
            }
        }
    },
    legend: {
        labels: {
            colors: '#f5f7ff'
        },
        show: true,
        position: 'top'
    },
    markers: {
        size: 6,
        strokeColors: '#1b2635',
        strokeWidth: 3
    },
    stroke: {
        curve: 'smooth'
    },
    xaxis: {
        axisBorder: {
            color: '#55596e',
            show: true
        },
        axisTicks: {
            color: '#55596e',
            show: true
        },
        labels: {
            style: {
                colors: '#f5f7ff'
            },
            rotate: -45,
            rotateAlways: true
        }
    },
    yaxis: [
        {
            title: {
                text: 'Stok Masuk',
                style: {
                    color: '#f5f7ff'
                }
            },
            labels: {
                style: {
                    colors: ['#f5f7ff']
                }
            }
        },
        {
            opposite: true,
            title: {
                text: 'Stok Keluar',
                style: {
                    color: '#f5f7ff'
                }
            },
            labels: {
                style: {
                    colors: ['#f5f7ff']
                }
            }
        }
    ],
    tooltip: {
        shared: true,
        intersect: false,
        theme: 'dark'
    }
};

// Inisialisasi Charts
const stockChart = new ApexCharts(
    document.querySelector('#stock-chart'),
    stockChartOptions
);
stockChart.render();

const movementChart = new ApexCharts(
    document.querySelector('#movement-chart'),
    movementChartOptions
);
movementChart.render();

// PDF Download Function
function downloadPDF() {
    window.jsPDF = window.jspdf.jsPDF;
    const doc = new jsPDF({
        orientation: 'l',
        unit: 'px',
        format: [595, 1000]
    });
    
    const content = document.querySelector('.main-container');
    
    html2canvas(content, {
        scale: 1,
        useCORS: true,
        logging: true,
        backgroundColor: '#1d2634',
        onclone: function(clonedDoc) {
            clonedDoc.querySelector('.download-section').style.display = 'none';
            clonedDoc.querySelector('.main-container').style.backgroundColor = '#1d2634';
            clonedDoc.querySelector('.main-container').style.padding = '20px';
        }
    }).then(canvas => {
        const imgWidth = 960;
        const pageWidth = 1000;
        const pageHeight = 595;
        const imgHeight = canvas.height * imgWidth / canvas.width;
        let heightLeft = imgHeight;
        let position = 20;
        
        doc.setFillColor(29, 38, 52);
        doc.rect(0, 0, doc.internal.pageSize.getWidth(), doc.internal.pageSize.getHeight(), 'F');
        
        const imgData = canvas.toDataURL('image/png');
        const xPosition = (pageWidth - imgWidth) / 2;
        doc.addImage(imgData, 'PNG', xPosition, position, imgWidth, imgHeight);
        heightLeft -= pageHeight;
        
        while (heightLeft >= 0) {
            position = heightLeft - imgHeight;
            doc.addPage();
            doc.setFillColor(29, 38, 52);
            doc.rect(0, 0, doc.internal.pageSize.getWidth(), doc.internal.pageSize.getHeight(), 'F');
            doc.addImage(imgData, 'PNG', xPosition, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;
        }
        
        doc.save('dashboard-report.pdf');
    });
}

function openAddModal() {
    document.getElementById('addModal').style.display = 'block';
}

function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
}

function openEditModal(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_nama_buah').value = data.nama_buah;
    document.getElementById('edit_stok_masuk').value = data.stok_masuk;
    document.getElementById('edit_stok_keluar').value = data.stok_keluar;
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

function openAddFruitModal() {
    document.getElementById('addFruitModal').style.display = 'block';
}

function closeAddFruitModal() {
    document.getElementById('addFruitModal').style.display = 'none';
}

// Tutup Modal Saat Mengklik Diluar
window.onclick = function(event) {
    if (event.target.className === 'modal') {
        event.target.style.display = 'none';
    }
}

// Menangani Pesan Sukses dan Error
document.addEventListener('DOMContentLoaded', function() {
    let urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('error')) {
        alert(urlParams.get('error'));
    }
    if (urlParams.has('success')) {
        alert(urlParams.get('success'));
    }
});

// Menutup Notifikasi Saat Mengklik Di Luar
document.addEventListener('click', function(event) {
    const notifPanel = document.getElementById('notifPanel');
    const notifButton = document.getElementById('notifButton');
    
    if (!notifPanel.contains(event.target) && event.target !== notifButton) {
        notifPanel.classList.remove('show');
    }
});