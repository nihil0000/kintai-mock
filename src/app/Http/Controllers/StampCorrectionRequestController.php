<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceCorrectionRequest;

class StampCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $correctionRequests = AttendanceCorrectionRequest::with('user', 'attendance')
            ->where('user_id', auth()->id())
            ->where('status', $status)
            ->orderBy('requested_clock_in')
            ->get();

        return view('stamp_correction_request.index', compact('correctionRequests', 'status'));
    }
}
