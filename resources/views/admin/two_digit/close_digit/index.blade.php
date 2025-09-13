@extends('layouts.master')
@section('style')
<style>
    .digits-flex-container {
    display: flex;
    flex-direction: row;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
    margin-bottom: 20px;
    /* Optional: hide scrollbar for a cleaner look */
    scrollbar-width: thin;
    scrollbar-color: #ccc #f8f9fa;
}
.digits-flex-container::-webkit-scrollbar {
    height: 8px;
}
.digits-flex-container::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 4px;
}
.digit-item {
    min-width: 80px;
    min-height: 100px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 10px;
    font-size: 1.5rem;
    font-weight: bold;
    transition: background 0.2s, color 0.2s, border 0.2s;
    position: relative;
}
.digit-number {
    font-size: 2rem;
    margin-bottom: 10px;
}
.digit-toggle {
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.digit-status {
    font-size: 1rem;
    font-weight: 600;
    text-align: center;
}
.horizontal-bar {
    display: flex;
    flex-direction: row;
    align-items: center;
    border: 1px solid #fff;
    background: #222;
    width: fit-content;
    margin: 0 auto 4px auto;
    overflow-x: auto;
    max-width: 100%;
}
.digit-box {
    border: 1px solid #fff;
    color: #fff;
    background: #222;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
}
.digit-box.active {
    background: #28a745;
    color: #fff;
    border-color: #28a745;
}
.horizontal-bar-group {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 8px;
}
.choose-digit-section {
    padding: 20px 0;
}
.choose-digit-title {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 12px;
    color: #333;
}
.horizontal-bar-group {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 12px;
}
.horizontal-bar-modern {
    display: flex;
    flex-direction: row;
    gap: 10px;
    justify-content: center;
    margin-bottom: 2px;
}
.digit-box-modern {
    background: #23272f;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border: 2px solid #444;
    color: #fff;
    width: 100px;
    height: 100px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    font-weight: 800;
    cursor: pointer;
    transition: background 0.2s, border 0.2s, color 0.2s, box-shadow 0.2s;
    position: relative;
    user-select: none;
}
.digit-box-modern:hover {
    background: #2d323c;
    border-color: #007bff;
    color: #fff;
    box-shadow: 0 4px 16px rgba(0,123,255,0.10);
}
.digit-box-modern.active {
    background:rgb(129, 29, 142);
    border-color: #28a745;
    border-width: 3px;
    color: #fff;
}
.digit-box-modern.inactive {
    background: #222 !important;
    border-color: #222 !important;
    color: #fff;
}
.digit-label {
    font-size: 1.2rem;
    letter-spacing: 1px;
}
.toggle-indicator {
    margin-top: 4px;
    width: 30px;
    height: 15px;
    background: #444;
    border-radius: 6px;
    position: relative;
    transition: background 0.2s;
    display: flex;
    align-items: center;
}
.digit-box-modern.active .toggle-indicator {
    background: #fff;
}
.toggle-dot {
    width: 12px;
    height: 12px;
    background: #bbb;
    border-radius: 50%;
    transition: background 0.2s, transform 0.2s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.10);
}
.digit-box-modern.active .toggle-dot {
    background: #28a745;
    transform: translateX(10px);
}
.digit-item.inactive {
    background: #222 !important;
    border-color: #222 !important;
    color: #fff;
}
.digit-item.active {
    background: #28a745;
    color: #fff;
    border-color: #28a745;
}


    .horizontal-bar-group {
        display: flex;
        flex-wrap: wrap;
        gap: 10px; /* Space between rows/chunks */
        justify-content: center;
        max-width: 800px; /* Adjust as needed */
        margin: 0 auto;
    }
    .horizontal-bar-modern {
        display: flex;
        gap: 10px; /* Space between individual digit boxes */
        flex-wrap: wrap; /* Allow wrapping if many items in a chunk */
    }
    .digit-box-modern {
        background-color:rgb(22, 9, 31); /* Purple for inactive */
        border: 2px solidrgb(90, 8, 8);
        border-radius: 10px;
        color: #fff;
        padding: 15px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-width: 150px; /* Make them wider for battle names */
        height: 120px; /* Adjust height */
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    .digit-box-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
    }
    .digit-box-modern.active {
        background-color:rgb(10, 111, 61); /* Green for active */
        border-color:rgb(194, 23, 151);
    }
    .digit-label {
        font-size: 1.1em;
        font-weight: bold;
        line-height: 1.3;
    }
    .toggle-indicator {
        position: absolute;
        bottom: 5px;
        right: 5px;
        width: 25px;
        height: 15px;
        background-color: rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        display: flex;
        align-items: center;
        transition: background-color 0.3s ease;
    }
    .toggle-dot {
        width: 11px;
        height: 11px;
        background-color: white;
        border-radius: 50%;
        transition: transform 0.3s ease;
        transform: translateX(2px); /* Default to left for inactive */
    }
    .digit-box-modern.active .toggle-dot {
        transform: translateX(12px); /* Move to right for active */
    }

</style>
@endsection

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">2D Settings</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card justify-content-center">
                        <div class="card-header">
                        <div class="row mt-4"> {{-- Added margin-top for spacing --}}
                        <div class="col-4">
                            <h4 class="mb-3">Manage Battle Times</h4>
                            <div class="horizontal-bar-group">
                                @foreach($battles->chunk(2) as $chunk) {{-- Chunk by 2 since you have two main periods --}}
                                    <div class="horizontal-bar-modern">
                                        @foreach($chunk as $battle)
                                            <div class="digit-box-modern {{ $battle->status ? 'active' : '' }}"
                                                data-id="{{ $battle->id }}"
                                                data-status="{{ $battle->status }}"
                                                onclick="toggleBattleStatus(this)"
                                                title="Click to toggle status for {{ $battle->battle_name }}">
                                                <span class="digit-label">
                                                    {{ $battle->battle_name }}<br>
                                                    ({{ \Carbon\Carbon::parse($battle->start_time)->format('h:i A') }} -
                                                    {{ \Carbon\Carbon::parse($battle->end_time)->format('h:i A') }})
                                                </span>
                                                <span class="toggle-indicator">
                                                    <span class="toggle-dot"></span>
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-4">
                        <h4 class="mb-3">Manage TwoD Limit (Break)</h4>
                        <div class="horizontal-bar-group">
                            @if($twoDLimit) {{-- Check if $twoDLimit is not null --}}
                                <div class="digit-box-modern">
                                    <h6>TwoD Limit (Break)</h6>
                                    <p>{{ number_format($twoDLimit->two_d_limit, 0, '.', ',') }}</p>
                                </div>
                            @else
                                <div class="digit-box-modern">
                                    No 2D Limit set yet.
                                    {{-- You might add a link/button here to create one --}}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-4"> 
                        <h4 class="mb-3">Manage TwoD Result</h4>
                        <div class="d-flex flex-wrap justify-content-center align-items-end gap-3 mb-3">
                            <div class="digit-box-modern text-center py-4 px-2 mx-2 mb-2" style="background: #1a1a2e; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); max-width: 200px; min-width: 160px; flex: 1 1 180px;">
                                <h6 class="text-secondary mb-2">Win Number</h6>
                                <p class="font-weight-bold h3 mb-0" style="color: #00ffb3; word-break: break-all;">
                                    @if($twoDResult)
                                        {{ number_format($twoDResult->win_number, 0, '.', ',') }}
                                    @else
                                        <span class="text-light">No result yet</span>
                                    @endif
                                </p>
                            </div>
                            <div class="digit-box-modern text-center py-4 px-2 mx-2 mb-2" style="background: #16213e; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); max-width: 200px; min-width: 160px; flex: 1 1 180px;">
                                <h6 class="text-secondary mb-2">Session</h6>
                                <p class="font-weight-bold h3 mb-0" style="color: #fddb3a; word-break: break-all;">
                                    @if($twoDResult)
                                        {{ ucfirst($twoDResult->session) }}
                                    @else
                                        <span class="text-light">No result yet</span>
                                    @endif
                                </p>
                            </div>
                            <div class="digit-box-modern text-center py-4 px-2 mx-2 mb-2" style="background: #0f3460; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); max-width: 200px; min-width: 160px; flex: 1 1 180px;">
                                <h6 class="text-secondary mb-2">Result Date</h6>
                                <p class="font-weight-bold h3 mb-0" style="color: #e94560; word-break: break-all;">
                                    @if($twoDResult)
                                        {{ $twoDResult->result_date }}
                                    @else
                                        <span class="text-light">No result yet</span>
                                    @endif
                                </p>
                        </div>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            <button type="button" class="btn btn-success mx-2 px-4 py-2" data-toggle="modal" data-target="#headCloseDigitModal">
                                <i class="fas fa-plus text-white mr-2"></i> Add TwoD Limit (Break)
                            </button>
                            <button type="button" class="btn btn-primary mx-2 px-4 py-2" data-toggle="modal" data-target="#resultDigitModal">
                                <i class="fas fa-plus text-white mr-2"></i> Add TwoD Result
                            </button>
                         </div>                      
                    </div>

                            </div>
                        </div>
                        <div class="card-body justify-content-center">
                            <!-- Flex UI for Head Close Digits -->
                            <div class="head-digits-container">
                                <h5 class="mb-3">Toggle Head Close Digits Status</h5>
                                <div class="digits-flex-container">
                                    @foreach($headCloseDigits as $digit)
                                        <div class="digit-item {{ $digit->status ? 'active' : 'inactive' }}" data-id="{{ $digit->id }}">
                                            <div class="digit-number">{{ $digit->head_close_digit }}</div>
                                            <div class="digit-toggle">
                                                <label class="switch">
                                                    <input type="checkbox" 
                                                           class="status-toggle" 
                                                           data-id="{{ $digit->id }}"
                                                           {{ $digit->status ? 'checked' : '' }}>
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                            <div class="digit-status">
                                                <span class="status-text {{ $digit->status ? 'text-success' : 'text-danger' }}">
                                                    {{ $digit->status ? 'ON' : 'OFF' }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                           

                            <div class="horizontal-bar">
                                @foreach($headCloseDigits as $digit)
                                    <div class="digit-box">
                                        {{ $digit->head_close_digit }}
                                    </div>
                                @endforeach
                        </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <h3 class="card-title">Choose Close Digit</h3>
                        </div>
                        <div class="card-body">
                            <div class="choose-digit-section">
                                <div class="choose-digit-title">Choose Close Digit</div>
                               

                                <div class="horizontal-bar-group">
                        @foreach($chooseCloseDigits->chunk(10) as $chunk)
                            <div class="horizontal-bar-modern">
                                @foreach($chunk as $digit)
                                    <div class="digit-box-modern {{ $digit->status ? 'active' : 'inactive' }}"
                                        data-id="{{ $digit->id }}"
                                        data-status="{{ $digit->status }}"
                                        onclick="toggleChooseDigitStatus(this)"
                                        title="Click to toggle status">
                                        <span class="digit-label">{{ $digit->choose_close_digit }}</span>
                                        <span class="toggle-indicator">
                                            <span class="toggle-dot"></span>
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                            </div>
                        </div>
                    </div>

        </div>
    </section>

    <!-- TwoD Result Modal -->
    <div class="modal fade" id="resultDigitModal" tabindex="-1" role="dialog" aria-labelledby="resultDigitModalLabel" aria-modal="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resultDigitModalLabel">Add TwoD Result</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.two-d-result.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="two_d_result">TwoD Result</label>
                            <input type="text" class="form-control @error('two_d_result') is-invalid @enderror" 
                                   id="two_d_result" name="two_d_result" 
                                   placeholder="Enter TwoD Result" required aria-required="true">
                            @error('two_d_result')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="session">Session</label>
                            <select class="form-control @error('session') is-invalid @enderror" 
                                   id="session" name="session" required aria-required="true">
                                <option value="">Select session</option>
                                <option value="morning">Morning</option>
                                <option value="evening">Evening</option>
                            </select>
                            @error('session')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="result_date">Result Date</label>
                            <input type="date" class="form-control @error('result_date') is-invalid @enderror" 
                                   id="result_date" name="result_date" required aria-required="true">    
                            @error('result_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="result_time">Result Time</label>
                            <input type="time" class="form-control @error('result_time') is-invalid @enderror" 
                                   id="result_time" name="result_time" required aria-required="true">
                            @error('result_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                       
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Add TwoD Result</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    

    <!-- Head Close Digit Modal -->
    <div class="modal fade" id="headCloseDigitModal" tabindex="-1" role="dialog" aria-labelledby="headCloseDigitModalLabel" aria-modal="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="headCloseDigitModalLabel">Add TwoD Limit (Break)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.two-d-limit.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="two_d_limit">TwoD Limit (Break)</label>
                            <input type="number" class="form-control @error('two_d_limit') is-invalid @enderror" 
                                   id="two_d_limit" name="two_d_limit" 
                                   placeholder="Enter 2D Limit (Break)" required aria-required="true">
                            @error('two_d_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Add TwoD Limit (Break)</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
<link href="{{ asset('plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
<script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<link href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}" rel="stylesheet" />
<script src="{{ asset('plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
<script>
$(document).ready(function() {
    // Handle status toggle
    $('.status-toggle').on('change', function() {
        const digitId = $(this).data('id');
        const isChecked = $(this).is(':checked');
        const digitItem = $(this).closest('.digit-item');
        const statusText = digitItem.find('.status-text');

        // Update UI immediately
        if (isChecked) {
            digitItem.addClass('active').removeClass('inactive');
            statusText.removeClass('text-danger').addClass('text-success').text('ON');
        } else {
            digitItem.removeClass('active').addClass('inactive');
            statusText.removeClass('text-success').addClass('text-danger').text('OFF');
        }

        // Send AJAX request to update status
        $.ajax({
            url: '{{ route("admin.head-close-digit.toggle-status") }}',
            method: 'POST',
            data: {
                id: digitId,
                status: isChecked ? 1 : 0,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Status Updated!',
                        text: 'Head close digit status has been updated successfully.',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            },
            error: function(xhr) {
                // Revert UI if request fails
                $(this).prop('checked', !isChecked);
                if (!isChecked) {
                    digitItem.addClass('active').removeClass('inactive');
                    statusText.removeClass('text-danger').addClass('text-success').text('ON');
                } else {
                    digitItem.removeClass('active').addClass('inactive');
                    statusText.removeClass('text-success').addClass('text-danger').text('OFF');
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to update status. Please try again.',
                });
            }
        });
    });

    // Handle delete confirmation
    $('.delete-digit').on('click', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // Clear form when modal is closed
    $('#headCloseDigitModal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
});

// Function to show a custom message box instead of alert()
function showMessageBox(message, type = 'info') {
    const messageBox = document.createElement('div');
    messageBox.style.cssText = `
        position: fixed;
        top: 100px;
        right: 10px;
        background-color: ${type === 'success' ? '#4CAF50' : '#f44336'};
        color: white;
        padding: 15px;
        border-radius: 5px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.5s ease-in-out;
    `;
    messageBox.textContent = message;
    document.body.appendChild(messageBox);

    // Fade in
    setTimeout(() => messageBox.style.opacity = '1', 10);

    // Fade out and remove after 3 seconds
    setTimeout(() => {
        messageBox.style.opacity = '0';
        messageBox.addEventListener('transitionend', () => messageBox.remove());
    }, 3000);
}

function toggleChooseDigitStatus(element) {
    const digitId = element.getAttribute('data-id');
    const currentStatus = parseInt(element.getAttribute('data-status'));
    const newStatus = currentStatus === 1 ? 0 : 1;

    // Optimistically update UI
    element.setAttribute('data-status', newStatus);
    element.classList.toggle('active', newStatus === 1);
    element.classList.toggle('inactive', newStatus === 0);

    // Optionally show a loading spinner or overlay here
    // For example: element.style.pointerEvents = 'none'; // Disable clicks during fetch

    // Send AJAX request to update status in DB
    fetch('{{ route('admin.choose-close-digit.toggle-status') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            id: digitId,
            status: newStatus
        })
    })
    .then(response => {
        if (!response.ok) { // Check if response status is not 2xx
            // If the response is not OK, try to read the error message
            return response.json().then(errorData => {
                throw new Error(errorData.message || 'Server error occurred.');
            });
        }
        return response.json();
    })
    .then(data => {
        if (!data.success) {
            // Revert UI if failed
            element.setAttribute('data-status', currentStatus);
            element.classList.toggle('active', currentStatus === 1);
            element.classList.toggle('inactive', currentStatus === 0);
            showMessageBox(data.message || 'Failed to update status!', 'error');
        } else {
            showMessageBox(data.message || 'စိတ်ကြိုက် ပိတ်ဂဏန်းပိတ်သိမ်းမှု့အောင်မြင်ပါသည် | Status updated successfully!', 'success');
        }
        // Hide loading spinner/re-enable clicks
        // element.style.pointerEvents = 'auto';
    })
    .catch(error => {
        // Revert UI if network error or other exception
        element.setAttribute('data-status', currentStatus);
        element.classList.toggle('active', currentStatus === 1);
        element.classList.toggle('inactive', currentStatus === 0);
        showMessageBox('Error: ' + error.message, 'error');
        // Hide loading spinner/re-enable clicks
        // element.style.pointerEvents = 'auto';
    });
}


function toggleBattleStatus(element) {
        const battleId = element.getAttribute('data-id');
        const currentStatus = parseInt(element.getAttribute('data-status'));
        const newStatus = currentStatus === 1 ? 0 : 1;

        // Optimistically update UI
        element.setAttribute('data-status', newStatus);
        element.classList.toggle('active', newStatus === 1);

        fetch('{{ route('admin.battle.toggle-status') }}', { // Points to the new route
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                id: battleId,
                status: newStatus
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(errorData => {
                    throw new Error(errorData.message || 'Server error occurred.');
                });
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                // Revert UI if failed
                element.setAttribute('data-status', currentStatus);
                element.classList.toggle('active', currentStatus === 1);
                showMessageBox(data.message || 'Failed to update battle status!', 'error');
            } else {
                showMessageBox(data.message || 'Battle status updated successfully!', 'success');
            }
        })
        .catch(error => {
            // Revert UI if network error or other exception
            element.setAttribute('data-status', currentStatus);
            element.classList.toggle('active', currentStatus === 1);
            showMessageBox('Error: ' + error.message, 'error');
        });
    }

document.addEventListener('DOMContentLoaded', function() {
    const sessionSelect = document.getElementById('session');
    const timeInput = document.getElementById('result_time');
    function setTimeBySession() {
        if (sessionSelect.value === 'morning') {
            timeInput.value = '12:00';
        } else if (sessionSelect.value === 'evening') {
            timeInput.value = '16:30';
        } else {
            timeInput.value = '';
        }
    }
    if (sessionSelect && timeInput) {
        sessionSelect.addEventListener('change', setTimeBySession);
        setTimeBySession(); // set on load
    }
});
</script>

@if (session()->has('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: '{{ session('success') }}',
            showConfirmButton: false,
            timer: 1500
        });
    </script>
@endif
@endsection
