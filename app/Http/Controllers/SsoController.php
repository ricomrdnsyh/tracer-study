<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SsoController extends Controller
{
    public function sso(Request $request)
    {
        $url = config('services.sso.me_url');
        $accessToken = $request->access_token;
        $xToken = config('services.sso.x_token');
        $userAgent = $request->header('User-Agent') ?: 'Mozilla/5.0';

        if (!$accessToken) {
            return redirect()->route('login')->with('error', 'Access token is missing.');
        }

        $response = $this->makeCurlRequest($url, $accessToken, $xToken, $userAgent);

        if (isset($response['success']) && $response['success'] && !empty($response['data'])) {
            $responseData = $response['data'];

            // Skenario 1: Login Mahasiswa (Response memiliki nim)
            if (!empty($responseData['nim'])) {
                $nim = trim((string) $responseData['nim']);
                $status = (!empty($responseData['id_jenis_keluar']) && (int) $responseData['id_jenis_keluar'] === 1)
                    ? 'alumni'
                    : 'aktif';

                $save = [
                    'nama'            => $responseData['nama'] ?? '',
                    'prodi_id'        => $responseData['id_prodi'] ?? null,
                    'email'           => $responseData['email'] ?? ($nim . '@student.unuja.ac.id'),
                    'jenis_kelamin'   => isset($responseData['jenis_kelamin']) ? strtoupper(trim($responseData['jenis_kelamin'])) : null,
                    'id_jenis_keluar' => $responseData['id_jenis_keluar'] ?? null,
                    'status'          => $status,
                ];

                if (!empty($responseData['no_hp'])) {
                    $save['no_hp'] = $responseData['no_hp'];
                }

                $mahasiswa = Mahasiswa::where('nim', $nim)->first();

                if (!$mahasiswa) {
                    $defaultAkademik = $responseData['tahun_keluar']
                        ?? TahunAkademik::where('aktif', 'y')->value('id_smt')
                        ?? '20261';

                    $save['nim']         = $nim;
                    $save['akademik_id'] = $defaultAkademik;
                    $save['password']    = Hash::make($nim);

                    $mahasiswa = Mahasiswa::create($save);
                } else {
                    $mahasiswa->update($save);
                }

                Auth::guard('mahasiswa')->login($mahasiswa);

                $this->registerSsoSessionCallback($request, $responseData, $accessToken, $xToken, $userAgent);

                return redirect()->route('mahasiswa.dashboard');
            }
            // Skenario 2: Login Pegawai / Staff / Dosen (Response memiliki id_penduduk)
            elseif (!empty($responseData['id_penduduk'])) {
                $idPenduduk = (string) $responseData['id_penduduk'];
                $user = User::where('username', $idPenduduk)->first();

                if (!$user) {
                    return redirect()->route('login')->with('error', 'User belum terdaftar atau tidak punya akses.');
                }

                Auth::guard('web')->login($user);

                $this->registerSsoSessionCallback($request, $responseData, $accessToken, $xToken, $userAgent);

                if ($user->role === 'Admin') {
                    return redirect()->route('admin.dashboard');
                } elseif ($user->role === 'Fakultas') {
                    return redirect()->route('fakultas.dashboard');
                }

                return redirect()->route('dashboard');
            } else {
                return redirect()->route('login')->with('error', 'Data pengguna tidak valid dari SSO.');
            }
        } else {
            $errorMessage = $response['error'] ?? 'Gagal terhubung dengan SSO.';
            Log::warning('SSO connection error: ' . json_encode($response));
            return redirect()->route('login')->with('error', 'Gagal terhubung dengan SSO: ' . $errorMessage);
        }
    }

    public function logout(string $sessionId)
    {
        Auth::guard('web')->logout();
        Auth::guard('mahasiswa')->logout();

        try {
            app('session')->driver()->getHandler()->destroy($sessionId);
        } catch (\Throwable $e) {
            $sessionPath = config('session.files');
            if ($sessionPath) {
                $sessionFile = rtrim($sessionPath, '/\\') . '/' . $sessionId;
                if (file_exists($sessionFile)) {
                    @unlink($sessionFile);
                }
            }
        }

        session()->flush();
        session()->invalidate();
        session()->regenerateToken();

        return response()->json(['message' => 'Logout successful'], 200);
    }

    private function registerSsoSessionCallback(Request $request, array $responseData, string $accessToken, string $xToken, string $userAgent): void
    {
        if (empty($responseData['callback_session'])) {
            return;
        }

        $publicUrl = config('services.sso.public_url');
        $apiUrl = config('services.sso.api_url');

        $callbackUrl = str_replace($publicUrl, $apiUrl, $responseData['callback_session']);
        $logoutUrl = !empty($responseData['logout_session'])
            ? str_replace($publicUrl, $apiUrl, $responseData['logout_session'])
            : null;

        $phpSessionId = $request->session()->getId();

        $data = [
            'logout' => url('/sso/logout/' . $phpSessionId),
        ];

        $this->makeCurlRequest($callbackUrl, $accessToken, $xToken, $userAgent, $data);

        if ($logoutUrl) {
            $request->session()->put('logout_session', $logoutUrl);
        }
    }

    public function makeCurlRequest($url, $authorizationToken, $xToken, $userAgent, $data = null)
    {
        $curl = curl_init();

        $headers = [
            "Authorization: Bearer {$authorizationToken}",
            "X-Token: {$xToken}",
            "User-Agent: {$userAgent}",
        ];

        if ($data !== null) {
            $headers[] = 'Content-Type: application/json';
        }

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POST => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ];

        if (config('services.sso.force_ipv4', true)) {
            $options[CURLOPT_IPRESOLVE] = CURL_IPRESOLVE_V4;
        }
        if (config('services.sso.force_http_1_1', true)) {
            $options[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1;
        }

        $options[CURLOPT_SSLVERSION] = 6; // CURL_SSLVERSION_TLSv1_2
        $options[CURLOPT_SSL_CIPHER_LIST] = 'DEFAULT@SECLEVEL=1';

        if ($data !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return ['success' => false, 'error' => 'cURL Error: ' . $err];
        }

        $decodedResponse = json_decode($response, true);
        return is_array($decodedResponse) ? $decodedResponse : ['success' => false, 'error' => 'Invalid JSON response', 'raw' => $response];
    }
}
