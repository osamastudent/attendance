in controller


    // Time In: Marks the user's time in and updates attendance status
    public function timeIn(Request $request)
    {
        $attendance = Attendance::create([
            'user_id' => Auth::id(),
            'date' => Carbon::now()->format('Y-m-d'),
            'check_in' => Carbon::now(),
            'attendance_status' => 'time_in',
        ]);

        return redirect()->back()->with('success', 'Time In recorded successfully.');
    }




//     public function timeOut(Request $request)
// {
//     // Fetch the latest attendance record for the user
//     $attendance = Attendance::where('user_id', Auth::id())
//         ->whereNull('check_out')
//         ->orderBy('created_at', 'desc')
//         ->first();

//     if (!$attendance) {
//         return redirect()->back()->with('error', 'Attendance record not found!');
//     }

//     $checkInDate = Carbon::parse($attendance->check_in);
//     $checkOutDate = Carbon::now();

//     // Calculate total hours (check_in to check_out)
//     $totalSeconds = 0;
//     $totalSeconds = $checkInDate->diffInSeconds($checkOutDate);

//     // Convert total seconds to days, hours, minutes aur seconds
//     $days = floor($totalSeconds / 86400);
//     $hours = floor(($totalSeconds % 86400) / 3600);
//     $minutes = floor(($totalSeconds % 3600) / 60);
//     $seconds = $totalSeconds % 60;

//     // Store total hours ki record sahi format mein
//     if ($days > 0) {
//         $totalHoursRecord = $days . 'd ' . $hours . 'h ' . $minutes . 'm ' . $seconds . 's';
//     } else {
//         $totalHoursRecord = $hours . 'h ' . $minutes . 'm ' . $seconds . 's';
//     }

//     // Calculate break hours
//     $breakSeconds = 0;
//     if ($attendance->break_start) {
//         $breakStart = Carbon::parse($attendance->break_start);
//         if ($attendance->break_end) {
//             $breakEnd = Carbon::parse($attendance->break_end);
//             $breakSeconds = $breakStart->diffInSeconds($breakEnd);
//         } else {
//             $breakSeconds = $breakStart->diffInSeconds($checkOutDate);
//         }
//     }

//     $breakHours = gmdate('H:i:s', $breakSeconds);

//     // Calculate working hours
//     $workingSeconds = $totalSeconds - $breakSeconds;
//     $workingHours = gmdate('H:i:s', max(0, $workingSeconds));

//     // Update attendance record with correct values
//     $attendance->update([
//         'check_out' => $checkOutDate,
//         'attendance_status' => 'time_out',
//         'total_hours' => $totalHoursRecord,
//         'break_hours' => $breakHours,
//         'working_hours' => $workingHours,
//     ]);

//     return redirect()->back()->with('success', 'Time Out recorded successfully.');
// }
public function timeOut(Request $request)
{
    // Fetch the latest attendance record for the user
    $attendance = Attendance::where('user_id', Auth::id())
        ->whereNull('check_out')
        ->orderBy('created_at', 'desc')
        ->first();

    if (!$attendance) {
        return redirect()->back()->with('error', 'Attendance record not found!');
    }

    $checkInDate = Carbon::parse($attendance->check_in);
    $checkOutDate = Carbon::now();

    // Calculate total hours (check_in to check_out)
    $totalSeconds = 0;
    $totalSeconds = $checkInDate->diffInSeconds($checkOutDate);

    // Convert total seconds to hours, minutes aur seconds
    $hours = floor($totalSeconds / 3600);
    $minutes = floor(($totalSeconds % 3600) / 60);
    $seconds = $totalSeconds % 60;

    // Store total hours ki record sahi format mein
    $totalHoursRecord = sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);

    // Calculate break hours
    $breakSeconds = 0;
    if ($attendance->break_start) {
        $breakStart = Carbon::parse($attendance->break_start);
        if ($attendance->break_end) {
            $breakEnd = Carbon::parse($attendance->break_end);
            $breakSeconds = $breakStart->diffInSeconds($breakEnd);
        } else {
            $breakSeconds = $breakStart->diffInSeconds($checkOutDate);
        }
    }

    $breakHours = gmdate('H:i:s', $breakSeconds);

    // Calculate working hours
    $workingSeconds = $totalSeconds - $breakSeconds;
    $workingHours = gmdate('H:i:s', max(0, $workingSeconds));

    // Update attendance record with correct values
    $attendance->update([
        'check_out' => $checkOutDate,
        'attendance_status' => 'time_out',
        'total_hours' => $totalHoursRecord,
        'break_hours' => $breakHours,
        'working_hours' => $workingHours,
    ]);

    return redirect()->back()->with('success', 'Time Out recorded successfully.');
}

    




    public function breakStart(Request $request)
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->first();

        if ($attendance) {
            $attendance->update([
                'break_start' => Carbon::now(),
                'attendance_status' => 'on_break',
            ]);
        }

        return redirect()->back()->with('success', 'Break Start recorded successfully.');
    }

    public function breakEnd(Request $request)
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->first();

        if ($attendance) {
            $attendance->update([
                'break_end' => Carbon::now(), // Store in database-compatible format
                'attendance_status' => 'break_end',
            ]);
        }

        return redirect()->back()->with('success', 'Break End recorded successfully.');
    }


<!-- ****************** in routes ************** -->
// For attendance
Route::post('/attendance/timeIn', [AttendanceController::class, 'timeIn'])->name('attendance.timeIn');
Route::post('/attendance/timeOut', [AttendanceController::class, 'timeOut'])->name('attendance.timeOut');
Route::post('/attendance/breakStart', [AttendanceController::class, 'breakStart'])->name('attendance.breakStart');
Route::post('/attendance/breakEnd', [AttendanceController::class, 'breakEnd'])->name('attendance.breakEnd');
Route::post('/attendance/submitReport', [AttendanceController::class, 'timeOut'])->name('attendance.submitReport');
Route::get('/attendance/reports', [AttendanceController::class, 'timeOut'])->name('attendance.reports');

<!-- ********** in blade file ************** -->


            <div class="row   mt-3 ">





                @php
                // Fetch attendance for the logged-in user on the current date
                $attendance = App\Models\Attendance::where('user_id', Auth::id())->orderBy("created_at",'desc')->first();
                @endphp

                @if ($attendance)
                @if ($attendance->attendance_status == 'time_in')
                <!-- Show Time Out and Break Start -->
                <div class="col-md-4 mt-2 px-1">
                    <form action="{{ route('attendance.timeOut') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg buttons py-4 rounded-3 form-control">Time Out</button>
                    </form>
                </div>
                <div class="col-md-4 mt-2 px-1 ">
                    <form action="{{ route('attendance.breakStart') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg buttons py-4 rounded-3  form-control">Break</button>
                    </form>
                </div>
                @elseif ($attendance->attendance_status == 'on_break')
                <!-- Show Break End -->
                <div class="col-md-4 mt-2 px-1">
                    <form action="{{ route('attendance.breakEnd') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg buttons py-4 rounded-3 form-control">Break Off</button>
                    </form>
                </div>
                @elseif ($attendance->attendance_status == 'break_end')
                <!-- Show Break End -->
                <div class="col-md-4 mt-2 px-1">
                    <form action="{{ route('attendance.breakStart') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg buttons py-4 rounded-3 form-control">Break</button>
                    </form>
                </div>
                <!-- Show Time Out and Report -->
                <div class="col-md-4 mt-2 px-1">
                    <form action="{{ route('attendance.timeOut') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg buttons py-4 rounded-3 form-control">Time Out</button>
                    </form>
                </div>

                @else
                <!-- Default for unknown attendance status -->
                <div class="col-md-4 mt-2 px-1">
                    <form action="{{ route('attendance.timeIn') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg buttons py-4 form-control">Time In</button>
                    </form>
                </div>
                @endif
                @else
                <!-- Show Time In if no attendance record exists for today -->
                <div class="col-md-4 mt-2 px-1">
                    <form action="{{ route('attendance.timeIn') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg buttons py-4 rounded-3 form-control">Time In</button>
                    </form>
                </div>
                @endif


                <div class="col-md-4 mt-2 px-1">
                    
                    <form action="{{ route('attendance.submitReport') }}" method="POST">
                                @csrf
                        <button type="submit" class="btn btn-primary btn-lg buttons py-4 rounded-3 form-control">Submit Report</button>
                              </form>
                </div>
            </div>




<span id="currentDateTime" class="text-muted">{{$currentDate}}</span>
<script>
    function updateDateTime() {
        // Fetch current time from the server
        const currentDateTimeElement = document.getElementById('currentDateTime');
        const currentDate = new Date();

        // Format the date as MM/DD/YYYY
        const formattedDate = currentDate.toLocaleDateString('en-US'); // MM/DD/YYYY
        // Format the time as HH:MM:SS AM/PM
        const formattedTime = currentDate.toLocaleTimeString('en-US');

        // Update the displayed time
        currentDateTimeElement.textContent = `${formattedDate} - ${formattedTime}`;
    }

    // Update the time every second
    setInterval(updateDateTime, 1000);
</script>












