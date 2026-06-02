<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PHPFrista\FacialRecognition;
use App\Services\PHPFrista\StatusCode;

class FaceVerificationController extends Controller
{
    /**
     * API endpoint untuk verifikasi wajah
     */
    public function verify(Request $request)
    {
        $request->validate([
            'nik' => 'required|string',
            'encoding' => 'required|array',
        ]);

        $frista = new FacialRecognition();
        $frista->init(
            config('biometric.username'),
            config('biometric.password')
        );

        $result = $frista->verify(
            $request->input('nik'),
            $request->input('encoding')
        );

        return response()->json([
            'success' => in_array($result['status'] ?? null, [
                StatusCode::OK,
                StatusCode::ALREADY_REGISTERED
            ]),
            'message' => $result['message'] ?? '',
            'status_code' => $result['status'] ?? null,
            'confidence' => $result['confidence'] ?? null
        ]);
    }
}
