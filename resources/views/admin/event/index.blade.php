@extends('layouts.admin')

@section('title', 'Kelola Agenda Kegiatan')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Kelola Agenda Kegiatan</h1>
        <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#createEventModal">
            <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Agenda
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body p-0">
            <!-- Wrapper with explicit relative positioning and z-index -->
            <div class="calendar-wrapper p-3" style="position: relative; z-index: 1;">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('modals')
<!-- Create Event Modal -->
<div class="modal fade" id="createEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Tambah Agenda Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createEventForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="create_title" class="form-label">Judul Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="create_title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_location" class="form-label">Lokasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="create_location" name="location" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mulai <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="create_start_date_d" name="start_date_d" required>
                                <input type="time" class="form-control" id="create_start_date_t" name="start_date_t">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Selesai (Opsional)</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="create_end_date_d" name="end_date_d">
                                <input type="time" class="form-control" id="create_end_date_t" name="end_date_t">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="create_description" class="form-label">Deskripsi <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="create_description" name="description" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Agenda</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Event Modal -->
<div class="modal fade" id="editEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">Edit Agenda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editEventForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_event_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_title" class="form-label">Judul Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_location" class="form-label">Lokasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_location" name="location" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mulai <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="edit_start_date_d" name="start_date_d" required>
                                <input type="time" class="form-control" id="edit_start_date_t" name="start_date_t">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Selesai (Opsional)</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="edit_end_date_d" name="end_date_d">
                                <input type="time" class="form-control" id="edit_end_date_t" name="end_date_t">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Deskripsi <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger" id="deleteEventBtn">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush


@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 'auto', // Adjust height to content
            contentHeight: 600, // Limit maximum height
            aspectRatio: 1.8, // Make it wider and shorter
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listMonth' // Removed timeGridWeek for compactness
            },
            themeSystem: 'bootstrap5',
            locale: 'id',
            // Fetch events with explicit AJAX header to ensure controller returns JSON
            events: function(info, successCallback, failureCallback) {
                fetch('{{ route("admin.events.index") }}?start=' + info.startStr + '&end=' + info.endStr, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    successCallback(data);
                })
                .catch(error => {
                    console.error('Error fetching events:', error);
                    failureCallback(error);
                });
            },
            editable: true,
            selectable: true,
            dayMaxEvents: true, // allow "more" link when too many events
            
            // Customize event content
            eventContent: function(arg) {
                let time = '';
                if (!arg.event.allDay) {
                    time = new Date(arg.event.start).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                }
                return {
                    html: `<div class="fc-content p-1">
                            ${time ? '<small class="fw-bold">'+time+'</small><br>' : ''}
                            <span class="fw-bold">${arg.event.title}</span>
                           </div>`
                };
            },

            // Select date range (or single click) to add event
            select: function(info) {
                document.getElementById('createEventForm').reset();
                
                // Handle Start Date
                // info.startStr is YYYY-MM-DD (for dayGrid) or ISO (for timeGrid)
                let startStr = info.startStr.slice(0, 10);
                document.getElementById('create_start_date_d').value = startStr;
                document.getElementById('create_start_date_t').value = '08:00'; // Default start time
                
                // Handle End Date
                // info.end is exclusive, so we subtract 1 day to get the inclusive end date
                let endDate = new Date(info.end);
                endDate.setDate(endDate.getDate() - 1);
                let endDateStr = endDate.toISOString().slice(0, 10);
                
                // Only set end date if it's a multi-day selection
                if (endDateStr !== startStr) {
                    document.getElementById('create_end_date_d').value = endDateStr;
                    document.getElementById('create_end_date_t').value = '16:00'; // Default end time
                } else {
                    document.getElementById('create_end_date_d').value = '';
                    document.getElementById('create_end_date_t').value = '';
                }

                // Open modal
                var myModal = new bootstrap.Modal(document.getElementById('createEventModal'));
                myModal.show();
            },

            // Click on event to edit
            eventClick: function(info) {
                let event = info.event;
                let props = event.extendedProps;
                
                document.getElementById('edit_event_id').value = event.id;
                document.getElementById('edit_title').value = event.title;
                document.getElementById('edit_location').value = props.location;
                document.getElementById('edit_description').value = props.description;
                
                // Format dates for input type="date" and "time"
                const splitDateTime = (dateStr) => {
                    if (!dateStr) return { date: '', time: '' };
                    let dateObj = new Date(dateStr);
                    // Handle timezone
                    const offset = dateObj.getTimezoneOffset() * 60000;
                    const localISOTime = (new Date(dateObj - offset)).toISOString();
                    
                    return {
                        date: localISOTime.slice(0, 10),
                        time: localISOTime.slice(11, 16)
                    };
                };

                const start = splitDateTime(event.start);
                document.getElementById('edit_start_date_d').value = start.date;
                document.getElementById('edit_start_date_t').value = start.time;

                const end = splitDateTime(event.end);
                document.getElementById('edit_end_date_d').value = end.date;
                document.getElementById('edit_end_date_t').value = end.time;

                var myModal = new bootstrap.Modal(document.getElementById('editEventModal'));
                myModal.show();
            },

            // Drag and drop event (Update date)
            eventDrop: function(info) {
                updateEventDate(info.event);
            },
            eventResize: function(info) {
                updateEventDate(info.event);
            }
        });
        
        calendar.render();

        // Helper to update date on drag/resize
        function updateEventDate(event) {
            const formatDate = (date) => {
                if (!date) return null;
                const offset = date.getTimezoneOffset() * 60000;
                return (new Date(date - offset)).toISOString().slice(0, 19).replace('T', ' ');
            };
            
            let data = {
                title: event.title,
                location: event.extendedProps.location,
                description: event.extendedProps.description,
                start_date: formatDate(event.start),
                end_date: formatDate(event.end),
                _token: '{{ csrf_token() }}',
                _method: 'PUT'
            };

            fetch(`/admin/agenda/${event.id}`, {
                method: 'POST', // Method spoofing
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    Toast.fire({
                        icon: 'success',
                        title: 'Jadwal diperbarui'
                    });
                } else {
                    calendar.refetchEvents(); // Revert
                    Swal.fire('Error', 'Gagal memperbarui jadwal', 'error');
                }
            })
            .catch(error => {
                calendar.refetchEvents(); // Revert
                console.error('Error:', error);
            });
        }

        // Create Event Form Submit
        document.getElementById('createEventForm').addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            
            fetch('{{ route("admin.events.store") }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('createEventModal')).hide();
                    calendar.refetchEvents();
                    Swal.fire('Sukses', 'Agenda berhasil ditambahkan', 'success');
                    this.reset();
                } else {
                    Swal.fire('Error', data.message || 'Terjadi kesalahan', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            });
        });

        // Edit Event Form Submit
        document.getElementById('editEventForm').addEventListener('submit', function(e) {
            e.preventDefault();
            let id = document.getElementById('edit_event_id').value;
            let formData = new FormData(this);
            // FormData sends as multipart/form-data, but we need PUT. Laravel supports _method field in FormData.
            
            fetch(`/admin/agenda/${id}`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editEventModal')).hide();
                    calendar.refetchEvents();
                    Swal.fire('Sukses', 'Agenda berhasil diperbarui', 'success');
                } else {
                    Swal.fire('Error', data.message || 'Terjadi kesalahan', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            });
        });

        // Delete Event
        document.getElementById('deleteEventBtn').addEventListener('click', function() {
            let id = document.getElementById('edit_event_id').value;
            
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Agenda ini akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/admin/agenda/${id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            _method: 'DELETE'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            bootstrap.Modal.getInstance(document.getElementById('editEventModal')).hide();
                            calendar.refetchEvents();
                            Swal.fire('Terhapus!', 'Agenda berhasil dihapus.', 'success');
                        } else {
                            Swal.fire('Error', 'Gagal menghapus agenda', 'error');
                        }
                    });
                }
            });
        });

        // Recalculate size when sidebar toggled (important!)
        const sidebarToggleBtn = document.getElementById('sidebarToggle');
        if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', () => {
            setTimeout(() => calendar.updateSize(), 350);
        });
        }

        // Also when window resized
        window.addEventListener('resize', () => calendar.updateSize());

    });
</script>

<style>
    /* Global Calendar Sizing */
    .fc {
        font-size: 0.8rem !important; /* Smaller base font */
        max-width: 100%;
    }

    /* Toolbar Compactness */
    .fc-header-toolbar {
        margin-bottom: 1rem !important;
        flex-wrap: wrap; /* Allow wrapping on small screens */
        gap: 0.5rem;
        /* z-index: 0 !important; Ensure it stays under navbar */
    }
    .fc-toolbar-title {
        font-size: 1.25rem !important;
    }
    .fc-button {
        padding: 0.2rem 0.6rem !important;
        font-size: 0.85rem !important;
    }

    /* Header Cells (Days) */
    .fc-col-header-cell-cushion {
        padding: 4px !important;
        font-size: 0.85rem !important;
        text-decoration: none !important;
        color: #4e73df;
    }

    /* Day Cells & Numbers */
    .fc-daygrid-day-number {
        font-size: 0.75rem !important;
        padding: 2px 4px !important;
        text-decoration: none !important;
        color: #858796;
    }

    /* Events Styling */
    .fc-event {
        cursor: pointer;
        transition: transform 0.1s;
        border: none;
        border-radius: 3px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        margin-bottom: 2px !important;
        font-size: 0.75rem !important;
    }
    .fc-event:hover {
        transform: scale(1.02);
        z-index: 50;
        box-shadow: 0 4px 6px rgba(0,0,0,0.15);
    }
    .fc-daygrid-event {
        white-space: nowrap; 
        overflow: hidden;
        text-overflow: ellipsis;
        padding: 2px 4px;
        background-color: #7fe1fa !important; /* Custom light blue */
        border-left: 3px solid #2c9faf !important; /* Darker teal accent */
        color: #000 !important; /* Black text for best contrast */
    }
    .fc-event-main {
        color: #333 !important; /* Ensure text is dark */
        font-weight: 600;
        padding: 0;
    }
    
    /* SweetAlert Z-Index Fix */
    .swal2-container {
        z-index: 99999 !important; /* Above everything */
    }

    /* Layout Wrapper Fixes */
    .calendar-wrapper {
        position: relative;
        width: 100%;
        /* overflow-x: hidden; Prevent horizontal scroll overlap */
        background: #fff;
        z-index: 0;
        overflow-x: auto;      /* jangan hidden */
        overflow-y: visible;
    }
    
    /* Fix for Sidebar Overlap (Ensure no negative margins) */
    #calendar {
        width: 100%;
        margin: 0;
    }

    #calendar, .fc {
        width: 100% !important;
        max-width: 100% !important;
    }
</style>
@endpush
